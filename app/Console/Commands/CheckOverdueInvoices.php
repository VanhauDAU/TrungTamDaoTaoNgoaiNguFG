<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOverdueRegistrationJob;
use Illuminate\Console\Command;
use App\Services\Maintenance\BatchMaintenanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function handle(BatchMaintenanceService $service): int
    {
        $isDryRun = $this->option('dry-run');
        $today    = Carbon::today();

        $this->info($isDryRun ? '🔍 [DRY-RUN] Chế độ xem trước – không thay đổi dữ liệu' : '⚙️  Bắt đầu xử lý hóa đơn quá hạn...');
        $this->newLine();

        // ── Tìm hóa đơn quá hạn chưa thanh toán đủ ─────────────────
        $overdueInvoices = $service->getOverdueInvoices($today);

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ Không có hóa đơn nào quá hạn.');
            Log::info('invoice:check-overdue completed with no overdue invoices', [
                'dry_run' => $isDryRun,
                'date' => $today->toDateString(),
            ]);
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

        $registrationIds = $service->getUniqueOverdueRegistrationIds($today);

        foreach ($registrationIds as $dangKyLopHocId) {
            ProcessOverdueRegistrationJob::dispatch((int) $dangKyLopHocId, $today->toDateString())->afterCommit();
        }

        $this->newLine();
        $this->info('✅ Đã đưa batch xử lý vào queue maintenance:');
        $this->line("   • Hóa đơn quá hạn tìm thấy : {$overdueInvoices->count()}");
        $this->line("   • Đăng ký lớp được queue : {$registrationIds->count()}");

        Log::info('invoice:check-overdue queued', [
            'dry_run' => $isDryRun,
            'date' => $today->toDateString(),
            'overdue_invoice_count' => $overdueInvoices->count(),
            'queued_registration_count' => $registrationIds->count(),
        ]);

        return self::SUCCESS;
    }
}
