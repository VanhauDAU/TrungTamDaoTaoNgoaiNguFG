<?php

namespace App\Services\Finance;

use App\Jobs\ProcessThongBaoDelivery;
use App\Models\Finance\PhieuThu;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoTepDinh;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptNotificationService
{
    public function __construct(
        private FinanceDocumentService $financeDocumentService
    ) {
    }

    public function sendReceiptCreatedNotification(PhieuThu $receipt): ?ThongBao
    {
        $receipt = $this->financeDocumentService->findReceiptForAdmin($receipt->phieuThuId);
        $recipientId = (int) ($receipt->taiKhoanId ?: $receipt->hoaDon?->taiKhoanId ?: 0);

        if ($recipientId <= 0) {
            return null;
        }

        $artifact = $this->financeDocumentService->buildReceiptArtifact($receipt);

        $notification = DB::transaction(function () use ($artifact, $receipt, $recipientId) {
            $notification = ThongBao::create([
                'tieuDe' => $this->buildTitle($receipt),
                'noiDung' => $this->buildContent($receipt),
                'nguoiGuiId' => $receipt->nguoiDuyetId,
                'loaiGui' => ThongBao::LOAI_TAI_CHINH,
                'doiTuongGui' => ThongBao::DOI_TUONG_CA_NHAN,
                'doiTuongId' => $recipientId,
                'ngayGui' => null,
                'trangThai' => 1,
                'uuTien' => ThongBao::UU_TIEN_BINH_THUONG,
                'ghim' => false,
                'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
                'sent_at' => null,
            ]);

            $this->storeArtifactAttachment($notification, $artifact);

            return $notification;
        });

        ProcessThongBaoDelivery::dispatch(
            $notification->thongBaoId,
            $receipt->nguoiDuyetId,
            'receipt_created'
        )->afterCommit();

        return $notification;
    }

    private function storeArtifactAttachment(ThongBao $notification, array $artifact): void
    {
        $extension = pathinfo((string) $artifact['filename'], PATHINFO_EXTENSION) ?: 'pdf';
        $storedName = (string) Str::uuid() . '.' . $extension;
        $path = 'finance/receipts/notifications/' . now()->format('Y/m') . '/' . $storedName;

        Storage::disk('local')->put($path, $artifact['content']);

        ThongBaoTepDinh::create([
            'thongBaoId' => $notification->thongBaoId,
            'tenFile' => (string) $artifact['filename'],
            'tenFileLuu' => $storedName,
            'duongDan' => $path,
            'loaiFile' => (string) ($artifact['mime'] ?? 'application/pdf'),
            'kichThuoc' => strlen((string) $artifact['content']),
        ]);
    }

    private function buildTitle(PhieuThu $receipt): string
    {
        $receiptCode = $receipt->maPhieuThu ?: 'PT-' . str_pad((string) $receipt->phieuThuId, 6, '0', STR_PAD_LEFT);

        return "Trung tâm đã ghi nhận phiếu thu {$receiptCode}";
    }

    private function buildContent(PhieuThu $receipt): string
    {
        $receiptCode = $receipt->maPhieuThu ?: 'PT-' . str_pad((string) $receipt->phieuThuId, 6, '0', STR_PAD_LEFT);
        $invoiceCode = $receipt->hoaDon?->maHoaDon ?: 'HD-' . str_pad((string) ($receipt->hoaDon?->hoaDonId ?? 0), 6, '0', STR_PAD_LEFT);
        $amount = number_format((float) $receipt->soTien, 0, ',', '.') . 'đ';
        $remaining = number_format((float) ($receipt->hoaDon?->conNo ?? 0), 0, ',', '.') . 'đ';
        $date = $receipt->ngayThu ? \Carbon\Carbon::parse($receipt->ngayThu)->format('d/m/Y') : '—';
        $isPaidOff = (int) ($receipt->hoaDon?->trangThai ?? -1) === \App\Models\Finance\HoaDon::TRANG_THAI_DA_TT;
        $closingNote = $isPaidOff
            ? '<p>Hóa đơn của bạn đã được thanh toán đủ.</p>'
            : "<p>Công nợ còn lại sau lần thu này là <strong>{$remaining}</strong>.</p>";

        return implode('', [
            "<p>Trung tâm đã ghi nhận khoản thanh toán cho hóa đơn <strong>{$invoiceCode}</strong>.</p>",
            '<ul>',
            "<li>Mã phiếu thu: <strong>{$receiptCode}</strong></li>",
            "<li>Số tiền: <strong>{$amount}</strong></li>",
            "<li>Ngày thu: <strong>{$date}</strong></li>",
            "<li>Phương thức: <strong>{$receipt->phuongThucLabel}</strong></li>",
            '</ul>',
            $closingNote,
            '<p>Bạn có thể tải phiếu thu đính kèm trong thông báo này để đối soát.</p>',
        ]);
    }
}
