<?php

namespace Tests\Unit;

use App\Jobs\GenerateHocVienExportJob;
use App\Jobs\GenerateNhanSuProfilePdfJob;
use App\Services\Support\QueuedExportService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QueuedExportServiceTest extends TestCase
{
    private QueuedExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'cache.queued_exports.store' => 'array',
            'cache.queued_exports.ttl' => 30,
            'cache.queued_exports.disk' => 'local',
        ]);

        Cache::store('array')->flush();

        $this->service = app(QueuedExportService::class);
    }

    public function test_it_tracks_queued_and_ready_export_states(): void
    {
        $context = ['role' => 'teacher', 'taiKhoanId' => 5];

        $this->service->markQueued('nhan-su.profile-pdf', $context, [
            'filename' => 'ho-so-nhan-su-GV000005.pdf',
        ]);

        $queued = $this->service->get('nhan-su.profile-pdf', $context);

        $this->assertSame('queued', $queued['status']);
        $this->assertSame('ho-so-nhan-su-GV000005.pdf', $queued['filename']);

        $this->service->markReady('nhan-su.profile-pdf', $context, [
            'path' => 'exports/test/profile.pdf',
            'filename' => 'ho-so-nhan-su-GV000005.pdf',
            'mime' => 'application/pdf',
        ]);

        $ready = $this->service->get('nhan-su.profile-pdf', $context);

        $this->assertSame('ready', $ready['status']);
        $this->assertSame('exports/test/profile.pdf', $ready['path']);
        $this->assertSame('application/pdf', $ready['mime']);
    }

    public function test_export_jobs_target_the_exports_queue(): void
    {
        $excelJob = new GenerateHocVienExportJob(['q' => 'hau'], 'hoc-vien.xlsx');
        $pdfJob = new GenerateNhanSuProfilePdfJob(7, '1', 'ho-so.pdf');

        $this->assertSame('exports', $excelJob->queue);
        $this->assertSame('exports', $pdfJob->queue);
    }
}
