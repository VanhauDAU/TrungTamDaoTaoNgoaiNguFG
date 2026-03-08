<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\CoSoDaoTao as CoSo;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\HocPhi;

class LopHoc extends Model
{
    //
    protected $table = 'lophoc';
    protected $primaryKey = 'lopHocId';
    protected $fillable = [
        'slug',
        'maLopHoc',
        'khoaHocId',
        'tenLopHoc',
        'phongHocId',
        'taiKhoanId',
        'hocPhiId', 
        'ngayBatDau',
        'ngayKetThuc',
        'soBuoiDuKien',
        'soHocVienToiDa',
        'donGiaDay',
        'coSoId',
        'caHocId',
        'lichHoc', // Lịch học trong tuần: "2,4,6" (Thứ 2, 4, 6)
        'trangThai' // 0: sắp mở, 1: đang mở, 2: đã đóng, 3: đã hủy, 4: đang học
    ];

    public static function generateMaLopHoc($khoaHocId)
    {
        $khoaHoc = KhoaHoc::find($khoaHocId);
        $maVietTatKhoa = 'KH';

        if ($khoaHoc && $khoaHoc->maKhoaHoc) {
            $parts = explode('-', $khoaHoc->maKhoaHoc);
            // Prefix dựa vào maDanhMuc hoặc ký tự đầu của maKhoaHoc
            $maVietTatKhoa = $parts[0] ?? substr($khoaHoc->maKhoaHoc, 0, 2);
        }

        $namCuoi = substr(date('Y'), -2); // vd 2026 -> "26"
        $prefix = $namCuoi . strtoupper(substr($maVietTatKhoa, 0, 2));

        $count = self::where('maLopHoc', 'LIKE', $prefix . '%')->count();
        $so = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $so;
    }

    public function khoaHoc(){
        return $this->belongsTo(KhoaHoc::class, 'khoaHocId', 'khoaHocId');
    }
    public function phongHoc(){
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }
    public function taiKhoan(){
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
    public function hocPhi(){
        return $this->belongsTo(HocPhi::class, 'hocPhiId', 'hocPhiId');
    }
    public function coSo(){
        return $this->belongsTo(CoSo::class, 'coSoId', 'coSoId');
    }
    public function caHoc(){
        return $this->belongsTo(CaHoc::class, 'caHocId', 'caHocId');
    }
    public function buoiHocs(){
        return $this->hasMany(BuoiHoc::class, 'lopHocId', 'lopHocId');
    }
    public function dangKyLopHocs(){
        return $this->hasMany(DangKyLopHoc::class, 'lopHocId', 'lopHocId');
    }
}
