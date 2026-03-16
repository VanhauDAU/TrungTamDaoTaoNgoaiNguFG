<?php

namespace App\Console\Commands;

use App\Jobs\ExpirePendingRegistrationHoldJob;
use App\Models\Education\DangKyLopHoc;
use App\Services\Maintenance\BatchMaintenanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingRegistrationHolds extends Command
{
    protected $signature = 'registration:expire-holds {--dry-run : Liệt kê kết quả mà không thay đổi dữ liệu}';
    protected $description = 'Tự động hủy giữ chỗ của đăng ký chờ thanh toán đã quá hạn';

    public function handle(BatchMaintenanceService $service): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $now = now();

        $registrations = $service->getExpiredPendingRegistrations($now);

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

        $registrationIds = $service->getExpiredPendingRegistrationIds($now);

        foreach ($registrationIds as $dangKyLopHocId) {
            ExpirePendingRegistrationHoldJob::dispatch((int) $dangKyLopHocId)->afterCommit();
        }

        $this->newLine();
        $this->info('✅ Đã đưa batch giữ chỗ quá hạn vào queue maintenance:');
        $this->line("   • Đăng ký quá hạn tìm thấy : {$registrations->count()}");
        $this->line("   • Đăng ký được queue : {$registrationIds->count()}");

        Log::info('registration:expire-holds queued', [
            'dry_run' => $isDryRun,
            'checked_at' => $now->toDateTimeString(),
            'expired_hold_count' => $registrations->count(),
            'queued_registration_count' => $registrationIds->count(),
        ]);

        return self::SUCCESS;
    }
}
