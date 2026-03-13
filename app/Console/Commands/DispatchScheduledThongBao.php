<?php

namespace App\Console\Commands;

use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoLichSu;
use App\Services\Admin\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchScheduledThongBao extends Command
{
    protected $signature = 'thongbao:dispatch-scheduled {--limit=100 : So luong thong bao xu ly toi da moi lan chay}';
    protected $description = 'Gui cac thong bao da den gio hen';

    public function __construct(private readonly ThongBaoService $service)
    {
        parent::__construct();
    }

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

        $sent = 0;
        $failed = 0;

        foreach ($ids as $id) {
            DB::transaction(function () use ($id, $now, &$sent, &$failed) {
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

                $soNguoiNhan = $this->service->guiThongBao($tb);

                if ($soNguoiNhan > 0) {
                    $tb->update([
                        'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_GUI,
                        'ngayGui' => $now,
                        'sent_at' => $now,
                        'failed_at' => null,
                        'failure_reason' => null,
                    ]);

                    ThongBaoLichSu::create([
                        'thongBaoId' => $tb->thongBaoId,
                        'taiKhoanId' => null,
                        'hanhDong' => 'scheduled_sent',
                        'moTa' => "Scheduler da gui thong bao den {$soNguoiNhan} nguoi nhan.",
                        'payload' => [
                            'scheduled_at' => optional($tb->scheduled_at)->toDateTimeString(),
                            'dispatched_at' => $now->toDateTimeString(),
                        ],
                        'created_at' => $now,
                    ]);
                    $sent++;
                    return;
                }

                $tb->update([
                    'sendTrangThai' => ThongBao::SEND_TRANG_THAI_GUI_LOI,
                    'failed_at' => $now,
                    'failure_reason' => 'Khong co nguoi nhan phu hop khi scheduler xu ly.',
                ]);

                ThongBaoLichSu::create([
                    'thongBaoId' => $tb->thongBaoId,
                    'taiKhoanId' => null,
                    'hanhDong' => 'scheduled_failed',
                    'moTa' => 'Scheduler gui that bai do khong tim thay nguoi nhan phu hop.',
                    'payload' => [
                        'scheduled_at' => optional($tb->scheduled_at)->toDateTimeString(),
                        'processed_at' => $now->toDateTimeString(),
                    ],
                    'created_at' => $now,
                ]);
                $failed++;
            });
        }

        $this->info("Da xu ly {$ids->count()} thong bao den han. Thanh cong: {$sent}, that bai: {$failed}.");
        return self::SUCCESS;
    }
}
