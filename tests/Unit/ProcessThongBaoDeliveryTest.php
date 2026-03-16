<?php

namespace Tests\Unit;

use App\Jobs\ProcessThongBaoDelivery;
use App\Models\Interaction\ThongBao;
use Tests\TestCase;

class ProcessThongBaoDeliveryTest extends TestCase
{
    public function test_notification_delivery_job_targets_notifications_queue(): void
    {
        $job = new ProcessThongBaoDelivery(123, 1, 'admin_create_send');

        $this->assertSame('notifications', $job->queue);
        $this->assertSame(3, $job->tries);
        $this->assertSame(120, $job->timeout);
    }

    public function test_thong_bao_exposes_processing_send_status_label(): void
    {
        $thongBao = new ThongBao([
            'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
        ]);

        $this->assertSame('Đang xử lý', $thongBao->getSendTrangThaiLabel());
        $this->assertSame('send-scheduled', $thongBao->getSendTrangThaiBadgeClass());
    }
}
