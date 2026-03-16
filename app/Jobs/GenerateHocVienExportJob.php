<?php

namespace App\Jobs;

use App\Contracts\Admin\HocVien\HocVienServiceInterface;
use App\Exports\HocViensExport;
use App\Services\Support\QueuedExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerateHocVienExportJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public array $filters,
        public string $filename
    ) {
        $this->onQueue('exports');
    }

    public function handle(
        HocVienServiceInterface $hocVienService,
        QueuedExportService $queuedExportService
    ): void {
        try {
            $request = Request::create('/admin/hoc-vien/export', 'GET', $this->filters);
            $query = $hocVienService->buildIndexQuery($request);
            $path = $queuedExportService->buildStoragePath('exports/hoc-vien', $this->filename);

            Excel::store(new HocViensExport($query), $path, config('cache.queued_exports.disk', 'local'));

            $queuedExportService->markReady('hoc-vien.export', $this->filters, [
                'path' => $path,
                'filename' => $this->filename,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (Throwable $exception) {
            $queuedExportService->markFailed(
                'hoc-vien.export',
                $this->filters,
                'Tạo file Excel thất bại: ' . $exception->getMessage()
            );

            throw $exception;
        }
    }
}
