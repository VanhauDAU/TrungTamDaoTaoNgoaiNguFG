<?php

namespace App\Jobs;

use App\Services\Maintenance\BatchMaintenanceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOverdueRegistrationJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $dangKyLopHocId,
        public string $asOfDate
    ) {
        $this->onQueue('maintenance');
    }

    public function handle(BatchMaintenanceService $service): void
    {
        $result = $service->processOverdueRegistration(
            $this->dangKyLopHocId,
            Carbon::parse($this->asOfDate)
        );

        Log::info('maintenance.overdue-registration.processed', [
            'dang_ky_lop_hoc_id' => $this->dangKyLopHocId,
            'as_of_date' => $this->asOfDate,
            'status' => $result['status'] ?? 'unknown',
            'attendance_locked' => $result['attendance_locked'] ?? 0,
        ]);
    }
}
