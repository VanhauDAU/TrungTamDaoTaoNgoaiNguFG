<?php

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taikhoan') || !Schema::hasTable('nhansu')) {
            return;
        }

        $now = now();

        $staffs = DB::table('taikhoan')
            ->join('nhansu', 'nhansu.taiKhoanId', '=', 'taikhoan.taiKhoanId')
            ->whereIn('taikhoan.role', [TaiKhoan::ROLE_GIAO_VIEN, TaiKhoan::ROLE_NHAN_VIEN])
            ->select('taikhoan.taiKhoanId', 'taikhoan.role', 'nhansu.loaiHopDong', 'nhansu.luongCoBan', 'nhansu.ngayVaoLam')
            ->get();

        foreach ($staffs as $staff) {
            $maHoSo = sprintf('HS%s%06d', (int) $staff->role === TaiKhoan::ROLE_GIAO_VIEN ? 'GV' : 'NV', (int) $staff->taiKhoanId);

            DB::table('nhansu_hoso')->updateOrInsert(
                ['taiKhoanId' => $staff->taiKhoanId],
                [
                    'maHoSo' => $maHoSo,
                    'trangThaiHoSo' => 'draft',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $mappedContractType = match ($staff->loaiHopDong) {
                'Toàn thời gian' => 'FULL_TIME',
                'Bán thời gian' => 'PART_TIME',
                'Thử việc' => 'PROBATION',
                'Thỉnh giảng' => 'VISITING',
                default => $staff->loaiHopDong,
            };

            if ($mappedContractType !== null) {
                DB::table('nhansu')
                    ->where('taiKhoanId', $staff->taiKhoanId)
                    ->update(['loaiHopDong' => $mappedContractType]);
            }

            if ($staff->luongCoBan !== null && (float) $staff->luongCoBan > 0) {
                $exists = DB::table('nhansu_goi_luong')
                    ->where('taiKhoanId', $staff->taiKhoanId)
                    ->exists();

                if (!$exists) {
                    DB::table('nhansu_goi_luong')->insert([
                        'taiKhoanId' => $staff->taiKhoanId,
                        'loaiLuong' => 'MONTHLY',
                        'luongChinh' => $staff->luongCoBan,
                        'hieuLucTu' => $staff->ngayVaoLam ?: $now->toDateString(),
                        'hieuLucDen' => null,
                        'ghiChu' => 'Khởi tạo tự động từ dữ liệu cũ.',
                        'trangThai' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Không rollback dữ liệu backfill để tránh mất hồ sơ đã tạo từ dữ liệu cũ.
    }
};
