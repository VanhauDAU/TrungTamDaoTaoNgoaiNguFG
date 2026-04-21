<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder tạo buổi học mẫu cho giáo viên gv001 (ID=4) và gv_le_hoa (ID=5)
 * để test trang Lịch dạy tuần.
 *
 * Chạy: php artisan db:seed --class=BuoiHocSampleSeeder
 */
class BuoiHocSampleSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Lấy ca học từ DB ────────────────────────────────
        $caHocs = DB::table('cahoc')->where('trangThai', 1)->get();

        if ($caHocs->isEmpty()) {
            $this->command->warn('Không có ca học nào. Tạo ca học mẫu...');
            DB::table('cahoc')->insert([
                ['tenCa' => 'Ca Sáng',  'gioBatDau' => '08:00:00', 'gioKetThuc' => '10:00:00', 'trangThai' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['tenCa' => 'Ca Chiều', 'gioBatDau' => '14:00:00', 'gioKetThuc' => '16:00:00', 'trangThai' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['tenCa' => 'Ca Tối',   'gioBatDau' => '18:00:00', 'gioKetThuc' => '20:00:00', 'trangThai' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $caHocs = DB::table('cahoc')->where('trangThai', 1)->get();
        }

        // ── 2. Lớp học: GV4 phụ trách LopID=15,16 | GV5 phụ trách LopID=13 ──
        $phong = DB::table('phonghoc')->first();
        $phongId = $phong?->phongHocId ?? null;

        // Nếu chưa có phòng thì bỏ qua phongHocId
        $lopSessions = [
            // [lopHocId, taiKhoanId_GV, caHocId, thu_trong_tuan (1=Mon..7=Sun)]
            [15, 4, $caHocs->first()?->caHocId, [1, 3, 5]], // T2,T4,T6
            [16, 4, $caHocs->skip(1)->first()?->caHocId ?? $caHocs->first()->caHocId, [2, 4]], // T3,T5
            [13, 5, $caHocs->last()?->caHocId, [2, 4, 6]], // T3,T5,T7
        ];

        // ── 3. Tạo buổi học cho 4 tuần (2 tuần trước + tuần này + tuần sau) ────
        $mondayThisWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weeks = [
            $mondayThisWeek->copy()->subWeeks(2),
            $mondayThisWeek->copy()->subWeek(),
            $mondayThisWeek->copy(),
            $mondayThisWeek->copy()->addWeek(),
        ];

        $inserted = 0;

        foreach ($weeks as $weekStart) {
            foreach ($lopSessions as [$lopId, $gvId, $caId, $days]) {
                if ($caId === null) continue;

                foreach ($days as $dow) { // DOW: 1=Mon, 7=Sun
                    $date = $weekStart->copy()->addDays($dow - 1);

                    // Tránh tạo bản ghi trùng
                    $exists = DB::table('buoihoc')
                        ->where('lopHocId', $lopId)
                        ->where('ngayHoc', $date->toDateString())
                        ->where('caHocId', $caId)
                        ->exists();

                    if ($exists) continue;

                    // Xác định trạng thái dựa vào ngày
                    $today = Carbon::today();
                    if ($date->lt($today)) {
                        $trangThai = 2; // đã hoàn thành
                        $daHoanThanh = 1;
                        $daDiemDanh  = 1;
                    } elseif ($date->isToday()) {
                        $trangThai = 1; // đang diễn ra
                        $daHoanThanh = 0;
                        $daDiemDanh  = 0;
                    } else {
                        $trangThai = 0; // sắp diễn ra
                        $daHoanThanh = 0;
                        $daDiemDanh  = 0;
                    }

                    DB::table('buoihoc')->insert([
                        'lopHocId'    => $lopId,
                        'tenBuoiHoc'  => null,
                        'ngayHoc'     => $date->toDateString(),
                        'caHocId'     => $caId,
                        'phongHocId'  => $phongId,
                        'taiKhoanId'  => $gvId,
                        'ghiChu'      => null,
                        'daDiemDanh'  => $daDiemDanh,
                        'daHoanThanh' => $daHoanThanh,
                        'trangThai'   => $trangThai,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $inserted++;
                }
            }
        }

        $this->command->info("✅ Đã tạo $inserted buổi học mẫu.");
    }
}
