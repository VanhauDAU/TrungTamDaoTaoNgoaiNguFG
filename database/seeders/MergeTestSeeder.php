<?php

namespace Database\Seeders;

use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MergeTestSeeder extends Seeder
{
    public function run()
    {
        $coSo = CoSoDaoTao::first() ?: CoSoDaoTao::create(['tenCoSo' => 'Cơ sở Test', 'trangThai' => 1]);
        $caHoc = CaHoc::first();
        $phong = PhongHoc::where('coSoId', $coSo->coSoId)->first();
        $giaoVien = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)->first();

        if (!$caHoc) { return; }

        $tenKhoaHoc = 'Khóa học Test Gộp';
        $khoaHoc = KhoaHoc::create([
            'tenKhoaHoc' => $tenKhoaHoc,
            'maKhoaHoc' => 'TEST-' . Str::upper(Str::random(4)),
            'slug' => Str::slug($tenKhoaHoc) . '-' . Str::random(5),
            'trangThai' => 1
        ]);

        $lopNguon = LopHoc::create([
            'tenLopHoc' => 'Lớp TEST NGUỒN (Gộp)',
            'slug' => 'lop-test-nguon-' . Str::random(5),
            'khoaHocId' => $khoaHoc->khoaHocId,
            'coSoId' => $coSo->coSoId,
            'caHocId' => $caHoc->caHocId,
            'taiKhoanId' => $giaoVien ? $giaoVien->taiKhoanId : null,
            'phongHocId' => $phong ? $phong->phongHocId : null,
            'soHocVienToiDa' => 20,
            'trangThai' => LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
            'ngayBatDau' => now()->addDays(5)->toDateString(),
            'lichHoc' => '2,4,6', 'soBuoiDuKien' => 10,
        ]);

        $lopDich = LopHoc::create([
            'tenLopHoc' => 'Lớp TEST ĐÍCH (Nhận)',
            'slug' => 'lop-test-dich-' . Str::random(5),
            'khoaHocId' => $khoaHoc->khoaHocId,
            'coSoId' => $coSo->coSoId,
            'caHocId' => $caHoc->caHocId,
            'taiKhoanId' => $giaoVien ? $giaoVien->taiKhoanId : null,
            'phongHocId' => $phong ? $phong->phongHocId : null,
            'soHocVienToiDa' => 30,
            'trangThai' => LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
            'ngayBatDau' => now()->addDays(6)->toDateString(),
            'lichHoc' => '2,4,6', 'soBuoiDuKien' => 10,
        ]);

        $policyData = [
            'loaiThu' => LopHocChinhSachGia::LOAI_THU_TRON_GOI,
            'hocPhiNiemYet' => 1500000,
            'trangThai' => 1,
            'hanThanhToanHocPhi' => now()->addDays(10)->toDateString(),
        ];
        $lopNguon->chinhSachGia()->create($policyData);
        $lopDich->chinhSachGia()->create($policyData);

        $student = TaiKhoan::create([
            'taiKhoan' => 'hv_test_' . Str::random(3),
            'role' => TaiKhoan::ROLE_HOC_VIEN,
            'trangThai' => 1,
            'matKhau' => bcrypt('password')
        ]);
        DangKyLopHoc::create([
            'lopHocId' => $lopNguon->lopHocId,
            'taiKhoanId' => $student->taiKhoanId,
            'trangThai' => DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
        ]);

        echo "SUCCESS! Lần này chắc chắn được. Tìm lớp 'Lớp TEST NGUỒN (Gộp)' trên web nhé.\n";
    }
}
