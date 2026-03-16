<?php

namespace App\Jobs;

use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoLichSu;
use App\Services\Admin\ThongBao\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessThongBaoDelivery implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $thongBaoId,
        public ?int $taiKhoanId = null,
        public ?string $source = null
    ) {
        $this->onQueue('notifications');
    }

    public function handle(ThongBaoService $service): void
    {
        DB::transaction(function () use ($service) {
            $thongBao = ThongBao::query()
                ->whereKey($this->thongBaoId)
                ->lockForUpdate()
                ->first();

            if (!$thongBao || $thongBao->deleted_at) {
                return;
            }

            if ((int) $thongBao->sendTrangThai !== ThongBao::SEND_TRANG_THAI_DANG_XU_LY) {
                return;
            }

            $now = Carbon::now();
            $soNguoiNhan = $service->guiThongBao($thongBao);

            if ($soNguoiNhan > 0) {
                $thongBao->update([
                    'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_GUI,
                    'ngayGui' => $now,
                    'sent_at' => $now,
                    'scheduled_at' => null,
                    'failed_at' => null,
                    'failure_reason' => null,
                ]);

                ThongBaoLichSu::create([
                    'thongBaoId' => $thongBao->thongBaoId,
                    'taiKhoanId' => $this->taiKhoanId,
                    'hanhDong' => 'queued_sent',
                    'moTa' => "Queue da gui thong bao den {$soNguoiNhan} nguoi nhan.",
                    'payload' => [
                        'source' => $this->source ?? 'manual',
                        'processed_at' => $now->toDateTimeString(),
                    ],
                    'created_at' => $now,
                ]);

                return;
            }

            $thongBao->update([
                'sendTrangThai' => ThongBao::SEND_TRANG_THAI_GUI_LOI,
                'failed_at' => $now,
                'failure_reason' => 'Khong co nguoi nhan phu hop.',
            ]);

            ThongBaoLichSu::create([
                'thongBaoId' => $thongBao->thongBaoId,
                'taiKhoanId' => $this->taiKhoanId,
                'hanhDong' => 'queued_failed',
                'moTa' => 'Queue gui that bai do khong tim thay nguoi nhan phu hop.',
                'payload' => [
                    'source' => $this->source ?? 'manual',
                    'processed_at' => $now->toDateTimeString(),
                ],
                'created_at' => $now,
            ]);
        });
    }
}
