<?php

namespace App\Jobs;

use App\Services\Maintenance\BatchMaintenanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpirePendingRegistrationHoldJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $dangKyLopHocId)
    {
        $this->onQueue('maintenance');
    }

    public function handle(BatchMaintenanceService $service): void
    {
        $result = $service->processExpiredPendingRegistration($this->dangKyLopHocId);

        Log::info('maintenance.expire-hold.processed', [
            'dang_ky_lop_hoc_id' => $this->dangKyLopHocId,
            'status' => $result['status'] ?? 'unknown',
        ]);
    }
}
