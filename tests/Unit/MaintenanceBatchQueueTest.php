<?php

namespace Tests\Unit;

use App\Jobs\ExpirePendingRegistrationHoldJob;
use App\Jobs\ProcessOverdueRegistrationJob;
use App\Models\Education\DangKyLopHoc;
use App\Models\Finance\HoaDon;
use App\Services\Maintenance\BatchMaintenanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MaintenanceBatchQueueTest extends TestCase
{
    public function test_overdue_invoice_command_dispatches_one_job_per_unique_registration(): void
    {
        Queue::fake();

        $today = Carbon::today();
        $invoiceA = new HoaDon([
            'hoaDonId' => 1,
            'maHoaDon' => 'HD-001',
            'ngayHetHan' => $today->copy()->subDay(),
        ]);
        $invoiceB = new HoaDon([
            'hoaDonId' => 2,
            'maHoaDon' => 'HD-002',
            'ngayHetHan' => $today->copy()->subDays(2),
        ]);

        $service = $this->mock(BatchMaintenanceService::class);
        $service->shouldReceive('getOverdueInvoices')
            ->once()
            ->withArgs(fn (Carbon $arg) => $arg->isSameDay($today))
            ->andReturn(new Collection([$invoiceA, $invoiceB]));
        $service->shouldReceive('getUniqueOverdueRegistrationIds')
            ->once()
            ->withArgs(fn (Carbon $arg) => $arg->isSameDay($today))
            ->andReturn(collect([10, 11]));

        $this->artisan('invoice:check-overdue')
            ->expectsOutput('⚙️  Bắt đầu xử lý hóa đơn quá hạn...')
            ->assertSuccessful();

        Queue::assertPushed(ProcessOverdueRegistrationJob::class, 2);
        Queue::assertPushed(ProcessOverdueRegistrationJob::class, fn (ProcessOverdueRegistrationJob $job) => $job->dangKyLopHocId === 10 && $job->queue === 'maintenance');
        Queue::assertPushed(ProcessOverdueRegistrationJob::class, fn (ProcessOverdueRegistrationJob $job) => $job->dangKyLopHocId === 11 && $job->queue === 'maintenance');
    }

    public function test_expire_pending_holds_command_dispatches_one_job_per_registration(): void
    {
        Queue::fake();

        $now = now();
        $registrationA = new DangKyLopHoc([
            'dangKyLopHocId' => 21,
            'ngayHetHanGiuCho' => $now->copy()->subHour(),
        ]);
        $registrationB = new DangKyLopHoc([
            'dangKyLopHocId' => 22,
            'ngayHetHanGiuCho' => $now->copy()->subHours(2),
        ]);

        $service = $this->mock(BatchMaintenanceService::class);
        $service->shouldReceive('getExpiredPendingRegistrations')
            ->once()
            ->andReturn(new Collection([$registrationA, $registrationB]));
        $service->shouldReceive('getExpiredPendingRegistrationIds')
            ->once()
            ->andReturn(collect([21, 22]));

        $this->artisan('registration:expire-holds')
            ->expectsOutputToContain('Đã đưa batch giữ chỗ quá hạn vào queue maintenance')
            ->assertSuccessful();

        Queue::assertPushed(ExpirePendingRegistrationHoldJob::class, 2);
        Queue::assertPushed(ExpirePendingRegistrationHoldJob::class, fn (ExpirePendingRegistrationHoldJob $job) => $job->dangKyLopHocId === 21 && $job->queue === 'maintenance');
        Queue::assertPushed(ExpirePendingRegistrationHoldJob::class, fn (ExpirePendingRegistrationHoldJob $job) => $job->dangKyLopHocId === 22 && $job->queue === 'maintenance');
    }

    public function test_maintenance_jobs_target_maintenance_queue(): void
    {
        $overdueJob = new ProcessOverdueRegistrationJob(10, now()->toDateString());
        $expireJob = new ExpirePendingRegistrationHoldJob(22);

        $this->assertSame('maintenance', $overdueJob->queue);
        $this->assertSame('maintenance', $expireJob->queue);
        $this->assertSame(3, $overdueJob->tries);
        $this->assertSame(120, $expireJob->timeout);
    }
}
