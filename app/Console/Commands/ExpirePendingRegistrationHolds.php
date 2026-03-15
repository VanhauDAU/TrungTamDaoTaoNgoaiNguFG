<?php

namespace App\Console\Commands;

use App\Models\Education\DangKyLopHoc;
use App\Models\Finance\HoaDon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingRegistrationHolds extends Command
{
    protected $signature = 'registration:expire-holds {--dry-run : Liệt kê kết quả mà không thay đổi dữ liệu}';
    protected $description = 'Tự động hủy giữ chỗ của đăng ký chờ thanh toán đã quá hạn';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $now = now();

        $registrations = DangKyLopHoc::with([
            'taiKhoan.hoSoNguoiDung',
            'lopHoc',
            'hoaDons',
        ])
            ->where('trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)
            ->whereNotNull('ngayHetHanGiuCho')
            ->where('ngayHetHanGiuCho', '<', $now)
            ->orderBy('ngayHetHanGiuCho')
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('✅ Không có đăng ký giữ chỗ nào quá hạn.');
            Log::info('registration:expire-holds completed with no expired holds', [
                'dry_run' => $isDryRun,
                'checked_at' => $now->toDateTimeString(),
            ]);

            return self::SUCCESS;
        }

        $this->info("📋 Tìm thấy {$registrations->count()} đăng ký giữ chỗ quá hạn:");
        $this->table(
            ['ĐK lớp', 'Học viên', 'Lớp', 'Giữ chỗ đến', 'Đã thu'],
            $registrations->map(function (DangKyLopHoc $registration) {
                $paidAmount = $registration->hoaDons->sum('daTra');

                return [
                    $registration->dangKyLopHocId,
                    $registration->taiKhoan?->hoSoNguoiDung?->hoTen ?? $registration->taiKhoan?->email ?? '—',
                    $registration->lopHoc?->tenLopHoc ?? '—',
                    optional($registration->ngayHetHanGiuCho)->format('d/m/Y H:i'),
                    number_format((float) $paidAmount, 0, ',', '.') . 'đ',
                ];
            })
        );

        if ($isDryRun) {
            $this->warn('ℹ️  Chế độ dry-run: không có thay đổi nào được thực hiện.');
            return self::SUCCESS;
        }

        $cancelledCount = 0;
        $skippedPaidCount = 0;

        foreach ($registrations as $registration) {
            DB::transaction(function () use ($registration, &$cancelledCount, &$skippedPaidCount) {
                $lockedRegistration = DangKyLopHoc::whereKey($registration->dangKyLopHocId)
                    ->lockForUpdate()
                    ->with(['hoaDons'])
                    ->first();

                if (!$lockedRegistration) {
                    return;
                }

                $lockedRegistration->recalculatePaymentStatus();
                $lockedRegistration->refresh();
                $lockedRegistration->load('hoaDons');

                if (!$lockedRegistration->isPendingPayment() || !$lockedRegistration->isHoldExpired()) {
                    return;
                }

                $totalPaid = (float) $lockedRegistration->hoaDons->sum('daTra');

                if ($totalPaid > 0) {
                    $skippedPaidCount++;
                    $this->line("  ↪ Bỏ qua ĐK {$lockedRegistration->dangKyLopHocId} vì đã phát sinh thu tiền.");
                    return;
                }

                $lockedRegistration->update([
                    'trangThai' => DangKyLopHoc::TRANG_THAI_HUY,
                    'ngayHetHanGiuCho' => null,
                ]);

                foreach ($lockedRegistration->hoaDons as $invoice) {
                    $note = trim((string) $invoice->ghiChu);
                    $systemNote = 'Tự động hủy giữ chỗ do quá hạn thanh toán';
                    if (!str_contains($note, $systemNote)) {
                        $invoice->update([
                            'ghiChu' => trim($note !== '' ? $note . ' | ' . $systemNote : $systemNote),
                        ]);
                    }
                }

                $cancelledCount++;
                $this->line("  ✖ Đã hủy giữ chỗ ĐK {$lockedRegistration->dangKyLopHocId}");
            });
        }

        $this->newLine();
        $this->info('✅ Hoàn tất xử lý giữ chỗ quá hạn:');
        $this->line("   • Đăng ký đã hủy : {$cancelledCount}");
        $this->line("   • Bỏ qua do đã thu tiền : {$skippedPaidCount}");

        Log::info('registration:expire-holds completed', [
            'dry_run' => $isDryRun,
            'checked_at' => $now->toDateTimeString(),
            'expired_hold_count' => $registrations->count(),
            'cancelled_count' => $cancelledCount,
            'skipped_paid_count' => $skippedPaidCount,
        ]);

        return self::SUCCESS;
    }
}
