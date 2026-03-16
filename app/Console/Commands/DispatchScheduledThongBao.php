<?php

namespace App\Console\Commands;

use App\Jobs\ProcessThongBaoDelivery;
use App\Models\Interaction\ThongBao;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchScheduledThongBao extends Command
{
    protected $signature = 'thongbao:dispatch-scheduled {--limit=100 : So luong thong bao xu ly toi da moi lan chay}';
    protected $description = 'Gui cac thong bao da den gio hen';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $now = Carbon::now();

        $ids = ThongBao::query()
            ->where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_LEN_LICH)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->whereNull('deleted_at')
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->pluck('thongBaoId');

        if ($ids->isEmpty()) {
            $this->line('Khong co thong bao den gio gui.');
            return self::SUCCESS;
        }

        $queued = 0;

        foreach ($ids as $id) {
            DB::transaction(function () use ($id, $now, &$queued) {
                $tb = ThongBao::query()
                    ->where('thongBaoId', $id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$tb ||
                    (int) $tb->sendTrangThai !== ThongBao::SEND_TRANG_THAI_DA_LEN_LICH ||
                    !$tb->scheduled_at ||
                    $tb->scheduled_at->greaterThan($now) ||
                    $tb->deleted_at
                ) {
                    return;
                }

                $tb->update([
                    'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
                    'failed_at' => null,
                    'failure_reason' => null,
                ]);
                ProcessThongBaoDelivery::dispatch($tb->thongBaoId, null, 'scheduled_dispatch')->afterCommit();
                $queued++;
            });
        }

        $this->info("Da dua {$queued}/{$ids->count()} thong bao den han vao queue notifications.");
        return self::SUCCESS;
    }
}
