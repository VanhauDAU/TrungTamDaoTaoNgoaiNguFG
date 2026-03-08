<?php

use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $today = Carbon::today()->toDateString();

        DB::table('lophoc')
            ->select(['lopHocId', 'trangThai', 'ngayKetThuc'])
            ->orderBy('lopHocId')
            ->get()
            ->each(function ($lopHoc) use ($today) {
                $currentStatus = (int) $lopHoc->trangThai;
                $nextStatus = $currentStatus;

                if (
                    $currentStatus !== LopHoc::TRANG_THAI_DA_HUY
                    && !empty($lopHoc->ngayKetThuc)
                    && $lopHoc->ngayKetThuc < $today
                ) {
                    $nextStatus = LopHoc::TRANG_THAI_DA_KET_THUC;
                }

                if ($nextStatus !== $currentStatus) {
                    DB::table('lophoc')
                        ->where('lopHocId', $lopHoc->lopHocId)
                        ->update(['trangThai' => $nextStatus]);
                }
            });

        $classStatuses = DB::table('lophoc')
            ->pluck('trangThai', 'lopHocId');

        DB::table('dangKyLopHoc')
            ->select(['dangKyLopHocId', 'lopHocId', 'trangThai'])
            ->orderBy('dangKyLopHocId')
            ->get()
            ->each(function ($dangKy) use ($classStatuses) {
                $currentStatus = (int) $dangKy->trangThai;
                $classStatus = $classStatuses[$dangKy->lopHocId] ?? null;

                $nextStatus = match ($currentStatus) {
                    0 => DangKyLopHoc::TRANG_THAI_HUY,
                    1 => DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN,
                    2 => match ((int) $classStatus) {
                        LopHoc::TRANG_THAI_DANG_HOC => DangKyLopHoc::TRANG_THAI_DANG_HOC,
                        LopHoc::TRANG_THAI_DA_KET_THUC => DangKyLopHoc::TRANG_THAI_HOAN_THANH,
                        LopHoc::TRANG_THAI_DA_HUY => DangKyLopHoc::TRANG_THAI_HUY,
                        default => DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                    },
                    3 => (int) $classStatus === LopHoc::TRANG_THAI_DA_HUY
                        ? DangKyLopHoc::TRANG_THAI_HUY
                        : DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
                    default => $currentStatus,
                };

                if ($nextStatus !== $currentStatus) {
                    DB::table('dangKyLopHoc')
                        ->where('dangKyLopHocId', $dangKy->dangKyLopHocId)
                        ->update(['trangThai' => $nextStatus]);
                }
            });
    }

    public function down(): void
    {
        DB::table('lophoc')
            ->where('trangThai', LopHoc::TRANG_THAI_DA_KET_THUC)
            ->update(['trangThai' => LopHoc::TRANG_THAI_DANG_HOC]);

        DB::table('dangKyLopHoc')
            ->select(['dangKyLopHocId', 'trangThai'])
            ->orderBy('dangKyLopHocId')
            ->get()
            ->each(function ($dangKy) {
                $currentStatus = (int) $dangKy->trangThai;

                $nextStatus = match ($currentStatus) {
                    DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN => 1,
                    DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                    DangKyLopHoc::TRANG_THAI_DANG_HOC,
                    DangKyLopHoc::TRANG_THAI_BAO_LUU,
                    DangKyLopHoc::TRANG_THAI_HOAN_THANH => 2,
                    DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 3,
                    DangKyLopHoc::TRANG_THAI_HUY => 0,
                    default => $currentStatus,
                };

                if ($nextStatus !== $currentStatus) {
                    DB::table('dangKyLopHoc')
                        ->where('dangKyLopHocId', $dangKy->dangKyLopHocId)
                        ->update(['trangThai' => $nextStatus]);
                }
            });
    }
};
