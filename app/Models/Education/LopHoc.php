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
    protected $primarykey = 'lopHocId';
    protected $fillable = [
        'lopHocId',
        'slug',
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
        'trangThai'
    ];
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
}
