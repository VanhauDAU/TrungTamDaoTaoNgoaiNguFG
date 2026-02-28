<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Education\LopHoc;

class KhoaHoc extends Model
{
    use SoftDeletes;
    protected $table = 'khoahoc'; 
    protected $primaryKey = 'khoaHocId'; 
    protected $fillable = [
        'khoaHocId',
        'danhMucId',
        'tenKhoaHoc',
        'slug',
        'anhKhoaHoc',
        'moTa',
        'doiTuong',
        'yeuCauDauVao',
        'ketQuaDatDuoc',
        'trangThai'
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucKhoaHoc::class, 'danhMucId', 'danhMucId');
    }
    public function lopHoc(){
        return $this->hasMany(LopHoc::class, 'khoaHocId', 'khoaHocId');
    }
    public function hocPhis(){
        return $this->hasMany(HocPhi::class, 'khoaHocId', 'khoaHocId');
    }
}
