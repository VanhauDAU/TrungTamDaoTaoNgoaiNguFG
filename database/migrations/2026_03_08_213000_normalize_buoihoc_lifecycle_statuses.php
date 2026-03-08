<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $today = Carbon::today()->toDateString();

        // Ưu tiên các buổi đã được đánh dấu hoàn thành.
        DB::table('buoihoc')
            ->where('daHoanThanh', 1)
            ->update([
                'trangThai' => 2,
            ]);

        // Giá trị cũ = 4 từng được dùng như "tạm ngưng"; chuyển về "đổi lịch".
        DB::table('buoihoc')
            ->where('daHoanThanh', 0)
            ->where('trangThai', 4)
            ->update([
                'trangThai' => 4,
            ]);

        // Buổi học diễn ra hôm nay được xem là "đang diễn ra" nếu chưa hoàn thành.
        DB::table('buoihoc')
            ->where('daHoanThanh', 0)
            ->where('trangThai', '!=', 4)
            ->whereDate('ngayHoc', $today)
            ->update([
                'trangThai' => 1,
            ]);

        // Các buổi chưa hoàn thành còn lại được chuẩn hóa về "sắp diễn ra".
        DB::table('buoihoc')
            ->where('daHoanThanh', 0)
            ->where('trangThai', '!=', 4)
            ->whereDate('ngayHoc', '!=', $today)
            ->update([
                'trangThai' => 0,
            ]);

        // Đồng bộ lại cờ hoàn thành với state machine mới.
        DB::table('buoihoc')
            ->where('trangThai', 2)
            ->update([
                'daHoanThanh' => 1,
            ]);

        DB::table('buoihoc')
            ->where('trangThai', '!=', 2)
            ->update([
                'daHoanThanh' => 0,
            ]);
    }

    public function down(): void
    {
        // Không thể phục hồi chính xác ngữ nghĩa cũ vì trước đây trangThai bị dùng như "loại buổi học".
        // Chỉ đồng bộ lại cờ hoàn thành theo cách an toàn nhất.
        DB::table('buoihoc')
            ->where('trangThai', 2)
            ->update([
                'daHoanThanh' => 1,
            ]);

        DB::table('buoihoc')
            ->where('trangThai', '!=', 2)
            ->update([
                'daHoanThanh' => 0,
            ]);
    }
};
