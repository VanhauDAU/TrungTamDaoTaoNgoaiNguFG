<?php

namespace App\Console\Commands;

use App\Jobs\ProcessThongBaoDelivery;
use App\Models\Interaction\ThongBao;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GuiThongBaoLenLich extends Command
{
    protected $signature   = 'thongbao:gui-lich';
    protected $description = 'Gửi các thông báo đã lên lịch đến thời điểm gửi';

    public function handle(): int
    {
        $now = Carbon::now();

        $thongBaos = ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_LEN_LICH)
            ->where('scheduled_at', '<=', $now)
            ->whereNull('deleted_at')
            ->get();

        if ($thongBaos->isEmpty()) {
            $this->info('Không có thông báo nào cần gửi lúc này.');
            return self::SUCCESS;
        }

        $this->info("Tìm thấy {$thongBaos->count()} thông báo cần gửi...");
        $queued = 0;

        foreach ($thongBaos as $tb) {
            DB::transaction(function () use ($tb, &$queued) {
                $locked = ThongBao::query()
                    ->whereKey($tb->thongBaoId)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || (int) $locked->sendTrangThai !== ThongBao::SEND_TRANG_THAI_DA_LEN_LICH) {
                    return;
                }

                $locked->update([
                    'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
                    'failed_at' => null,
                    'failure_reason' => null,
                ]);

                ProcessThongBaoDelivery::dispatch($locked->thongBaoId, $locked->nguoiGuiId, 'legacy_schedule_command')->afterCommit();
                $queued++;
            });
        }

        $this->info("Hoàn tất! Đã đưa {$queued} thông báo vào queue.");
        return self::SUCCESS;
    }
}
