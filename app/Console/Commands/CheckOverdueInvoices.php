<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Finance\HoaDon;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DiemDanh;
use Carbon\Carbon;

/**
 * Xử lý hóa đơn quá hạn thanh toán hàng ngày.
 *
 * Chạy thủ công:
 *   php artisan invoice:check-overdue
 *   php artisan invoice:check-overdue --dry-run   (chỉ liệt kê, không thay đổi)
 */
class CheckOverdueInvoices extends Command
{
    protected $signature   = 'invoice:check-overdue {--dry-run : Liệt kê kết quả mà không thay đổi dữ liệu}';
    protected $description = 'Kiểm tra hóa đơn quá hạn, tạm dừng đăng ký lớp và đánh dấu buổi học tương lai';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $today    = Carbon::today();

        $this->info($isDryRun ? '🔍 [DRY-RUN] Chế độ xem trước – không thay đổi dữ liệu' : '⚙️  Bắt đầu xử lý hóa đơn quá hạn...');
        $this->newLine();

        // ── Tìm hóa đơn quá hạn chưa thanh toán đủ ─────────────────
        $overdueInvoices = HoaDon::with(['dangKyLopHoc.lopHoc.buoiHocs', 'taiKhoan.hoSoNguoiDung'])
            ->whereNotNull('ngayHetHan')
            ->whereDate('ngayHetHan', '<', $today)
            ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ Không có hóa đơn nào quá hạn.');
            return self::SUCCESS;
        }

        $this->info("📋 Tìm thấy {$overdueInvoices->count()} hóa đơn quá hạn:");
        $this->table(
            ['Mã HĐ', 'Học viên', 'Hạn TT', 'Số ngày quá hạn', 'Trạng thái hiện tại'],
            $overdueInvoices->map(fn($hd) => [
                $hd->maHoaDon ?? 'HD-' . str_pad($hd->hoaDonId, 6, '0', STR_PAD_LEFT),
                $hd->taiKhoan?->hoSoNguoiDung?->hoTen ?? $hd->taiKhoan?->email ?? '?',
                Carbon::parse($hd->ngayHetHan)->format('d/m/Y'),
                $today->diffInDays(Carbon::parse($hd->ngayHetHan)) . ' ngày',
                $hd->trangThaiLabel,
            ])
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('ℹ️  Chế độ dry-run: không có thay đổi nào được thực hiện.');
            return self::SUCCESS;
        }

        // ── Xử lý từng hóa đơn ──────────────────────────────────────
        $countSuspended     = 0;
        $countDiemDanhAdded = 0;

        foreach ($overdueInvoices as $hoaDon) {
            if (! $hoaDon->dangKyLopHocId) {
                continue;
            }

            $dangKy = DangKyLopHoc::find($hoaDon->dangKyLopHocId);
            if (! $dangKy) {
                continue;
            }

            // 1. Tạm dừng đăng ký lớp học
            if ($dangKy->trangThai === DangKyLopHoc::TRANG_THAI_DANG_HOC) {
                $dangKy->update(['trangThai' => DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI]);
                $countSuspended++;
                $this->line("  ⏸  Tạm dừng ĐK lớp ID {$dangKy->dangKyLopHocId} ({$dangKy->lopHoc?->tenLopHoc})");
            }

            // 2. Tạo bản ghi DiemDanh = BI_KHOA_NO_HP cho các buổi học TƯƠNG LAI
            if ($dangKy->lopHoc) {
                $buoiHocsTuongLai = $dangKy->lopHoc->buoiHocs()
                    ->whereDate('ngayHoc', '>=', $today)
                    ->get();

                foreach ($buoiHocsTuongLai as $buoi) {
                    $existing = DiemDanh::where('buoiHocId', $buoi->buoiHocId)
                        ->where('taiKhoanId', $hoaDon->taiKhoanId)
                        ->first();

                    if (! $existing) {
                        DiemDanh::create([
                            'buoiHocId'       => $buoi->buoiHocId,
                            'taiKhoanId'      => $hoaDon->taiKhoanId,
                            'dangKyLopHocId'  => $dangKy->dangKyLopHocId,
                            'trangThai'       => DiemDanh::BI_KHOA_NO_HP,
                            'coMat'           => 0,
                            'lyDo'            => 'Nợ học phí – tự động hệ thống',
                            'thoiGianDiemDanh'=> now(),
                        ]);
                        $countDiemDanhAdded++;
                    } elseif ($existing->trangThai !== DiemDanh::BI_KHOA_NO_HP) {
                        // Nếu đã có bản ghi (Có mặt/Vắng) → cập nhật lý do (tùy chính sách)
                        // Giữ nguyên để không ghi đè điểm danh đã ghi
                    }
                }
            }
        }

        $this->newLine();
        $this->info("✅ Hoàn tất:");
        $this->line("   • Tạm dừng đăng ký lớp : {$countSuspended}");
        $this->line("   • Buổi học tương lai bị khóa : {$countDiemDanhAdded}");

        return self::SUCCESS;
    }
}
