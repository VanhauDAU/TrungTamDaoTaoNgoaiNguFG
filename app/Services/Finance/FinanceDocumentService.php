<?php

namespace App\Services\Finance;

use App\Mail\FinanceDocumentMail;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FinanceDocumentService
{
    public function findInvoiceForAdmin(int $id): HoaDon
    {
        return HoaDon::with($this->invoiceRelations())->findOrFail($id);
    }

    public function findReceiptForAdmin(int $id): PhieuThu
    {
        return PhieuThu::with($this->receiptRelations())->findOrFail($id);
    }

    public function streamInvoicePdf(HoaDon $invoice, string $disposition = 'inline'): Response
    {
        $artifact = $this->renderInvoiceArtifact($invoice);

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => $disposition . '; filename="' . $artifact['filename'] . '"',
        ]);
    }

    public function streamReceiptPdf(PhieuThu $receipt, string $disposition = 'inline'): Response
    {
        $artifact = $this->renderReceiptArtifact($receipt);

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => $disposition . '; filename="' . $artifact['filename'] . '"',
        ]);
    }

    public function sendInvoiceEmail(HoaDon $invoice, string $email, ?string $message = null): void
    {
        $artifact = $this->renderInvoiceArtifact($invoice);
        $code = $invoice->maHoaDon ?: 'HD-' . str_pad((string) $invoice->hoaDonId, 6, '0', STR_PAD_LEFT);

        Mail::to($email)->send(new FinanceDocumentMail(
            documentType: 'invoice',
            code: $code,
            recipientName: $invoice->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($invoice->taiKhoan?->taiKhoan ?? 'Quý khách'),
            artifact: $artifact,
            note: $message
        ));
    }

    public function sendReceiptEmail(PhieuThu $receipt, string $email, ?string $message = null): void
    {
        $artifact = $this->renderReceiptArtifact($receipt);
        $code = $receipt->maPhieuThu ?: 'PT-' . str_pad((string) $receipt->phieuThuId, 6, '0', STR_PAD_LEFT);

        Mail::to($email)->send(new FinanceDocumentMail(
            documentType: 'receipt',
            code: $code,
            recipientName: $receipt->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($receipt->taiKhoan?->taiKhoan ?? 'Quý khách'),
            artifact: $artifact,
            note: $message
        ));
    }

    public function defaultInvoiceEmail(HoaDon $invoice): ?string
    {
        return $invoice->taiKhoan?->email;
    }

    public function defaultReceiptEmail(PhieuThu $receipt): ?string
    {
        return $receipt->taiKhoan?->email ?? $receipt->hoaDon?->taiKhoan?->email;
    }

    private function renderInvoiceArtifact(HoaDon $invoice): array
    {
        $code = $invoice->maHoaDon ?: 'HD-' . str_pad((string) $invoice->hoaDonId, 6, '0', STR_PAD_LEFT);

        return $this->renderPdfArtifact(
            'admin.hoa-don.pdf.invoice',
            [
                'invoice' => $invoice,
                'code' => $code,
            ],
            'hoa-don-' . Str::slug($code, '-') . '.pdf'
        );
    }

    private function renderReceiptArtifact(PhieuThu $receipt): array
    {
        $code = $receipt->maPhieuThu ?: 'PT-' . str_pad((string) $receipt->phieuThuId, 6, '0', STR_PAD_LEFT);

        return $this->renderPdfArtifact(
            'admin.hoa-don.pdf.receipt',
            [
                'receipt' => $receipt,
                'invoice' => $receipt->hoaDon,
                'code' => $code,
            ],
            'phieu-thu-' . Str::slug($code, '-') . '.pdf'
        );
    }

    private function renderPdfArtifact(string $view, array $data, string $filename): array
    {
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView($view, $data);
            $pdf->setPaper('a4');

            return [
                'content' => $pdf->output(),
                'mime' => 'application/pdf',
                'filename' => $filename,
            ];
        }

        $html = view($view, $data)->render();
        $fallbackName = Str::replaceLast('.pdf', '.html', $filename);

        return [
            'content' => $html,
            'mime' => 'text/html; charset=UTF-8',
            'filename' => $fallbackName,
        ];
    }

    private function invoiceRelations(): array
    {
        return [
            'taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.lopHoc.khoaHoc',
            'lopHocDotThu',
            'dangKyLopHocPhuPhi',
            'coSo.tinhThanh',
            'nguoiLap.hoSoNguoiDung',
            'phieuThus.taiKhoan.hoSoNguoiDung',
            'phieuThus.nguoiDuyet.hoSoNguoiDung',
        ];
    }

    private function receiptRelations(): array
    {
        return [
            'taiKhoan.hoSoNguoiDung',
            'nguoiDuyet.hoSoNguoiDung',
            'hoaDon.taiKhoan.hoSoNguoiDung',
            'hoaDon.dangKyLopHoc.lopHoc.khoaHoc',
            'hoaDon.lopHocDotThu',
            'hoaDon.dangKyLopHocPhuPhi',
            'hoaDon.coSo.tinhThanh',
            'hoaDon.nguoiLap.hoSoNguoiDung',
            'hoaDon.phieuThus.nguoiDuyet.hoSoNguoiDung',
        ];
    }
}
