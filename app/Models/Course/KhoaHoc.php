<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use App\Models\Education\LopHoc;

class KhoaHoc extends Model
{
    //
    protected $table = 'khoahoc'; 
    protected $primaryKey = 'khoaHocId'; 

    public function loaiKhoaHoc(){
        return $this->belongsTo(LoaiKhoaHoc::class, 'loaiKhoaHocId', 'loaiKhoaHocId');
    }
    public function lopHoc(){
        return $this->hasMany(LopHoc::class, 'khoaHocId', 'khoaHocId');
    }
    public function hocPhis(){
        return $this->hasMany(HocPhi::class, 'khoaHocId', 'khoaHocId');
    }
}
