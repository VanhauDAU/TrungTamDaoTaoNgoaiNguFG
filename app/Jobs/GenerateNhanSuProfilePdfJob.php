<?php

namespace App\Jobs;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Services\Support\QueuedExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateNhanSuProfilePdfJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public int $taiKhoanId,
        public string $role,
        public string $filename
    ) {
        $this->onQueue('exports');
    }

    public function handle(
        NhanSuServiceInterface $nhanSuService,
        QueuedExportService $queuedExportService
    ): void {
        try {
            $taiKhoan = TaiKhoan::query()->findOrFail($this->taiKhoanId);
            $artifact = $nhanSuService->buildProfilePdfArtifact($taiKhoan, $this->role);
            $path = $queuedExportService->buildStoragePath('exports/nhan-su', $artifact['filename'] ?? $this->filename);

            Storage::disk(config('cache.queued_exports.disk', 'local'))
                ->put($path, $artifact['content']);

            $queuedExportService->markReady('nhan-su.profile-pdf', [
                'taiKhoanId' => $this->taiKhoanId,
                'role' => $this->role,
            ], [
                'path' => $path,
                'filename' => $artifact['filename'] ?? $this->filename,
                'mime' => $artifact['mime'] ?? 'application/pdf',
            ]);
        } catch (Throwable $exception) {
            $queuedExportService->markFailed(
                'nhan-su.profile-pdf',
                [
                    'taiKhoanId' => $this->taiKhoanId,
                    'role' => $this->role,
                ],
                'Tạo file hồ sơ nhân sự thất bại: ' . $exception->getMessage()
            );

            throw $exception;
        }
    }
}
