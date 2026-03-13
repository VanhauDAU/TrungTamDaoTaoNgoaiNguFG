<?php

namespace App\Console\Commands;

use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoLichSu;
use App\Services\Admin\ThongBao\ThongBaoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class GuiThongBaoLenLich extends Command
{
    protected $signature   = 'thongbao:gui-lich';
    protected $description = 'Gửi các thông báo đã lên lịch đến thời điểm gửi';

    public function __construct(private ThongBaoService $service)
    {
        parent::__construct();
    }

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

        foreach ($thongBaos as $tb) {
            $soNguoiNhan = $this->service->guiThongBao($tb);

            if ($soNguoiNhan > 0) {
                $tb->update([
                    'sendTrangThai'  => ThongBao::SEND_TRANG_THAI_DA_GUI,
                    'ngayGui'        => $now,
                    'sent_at'        => $now,
                    'scheduled_at'   => null,
                    'failed_at'      => null,
                    'failure_reason' => null,
                ]);

                ThongBaoLichSu::create([
                    'thongBaoId' => $tb->thongBaoId,
                    'taiKhoanId' => $tb->nguoiGuiId,
                    'hanhDong'   => 'scheduled_sent',
                    'moTa'       => "Tự động gửi theo lịch đến {$soNguoiNhan} người nhận.",
                    'payload'    => null,
                    'created_at' => $now,
                ]);

                $this->line("  ✓ [{$tb->thongBaoId}] {$tb->tieuDe} → {$soNguoiNhan} người nhận");
            } else {
                $tb->update([
                    'sendTrangThai'  => ThongBao::SEND_TRANG_THAI_GUI_LOI,
                    'failed_at'      => $now,
                    'failure_reason' => 'Không có người nhận phù hợp khi gửi theo lịch.',
                ]);

                ThongBaoLichSu::create([
                    'thongBaoId' => $tb->thongBaoId,
                    'taiKhoanId' => $tb->nguoiGuiId,
                    'hanhDong'   => 'scheduled_failed',
                    'moTa'       => 'Gửi theo lịch thất bại: không có người nhận.',
                    'payload'    => null,
                    'created_at' => $now,
                ]);

                $this->warn("  ✗ [{$tb->thongBaoId}] {$tb->tieuDe} → Thất bại (không có người nhận)");
            }
        }

        $this->info('Hoàn tất!');
        return self::SUCCESS;
    }
}
