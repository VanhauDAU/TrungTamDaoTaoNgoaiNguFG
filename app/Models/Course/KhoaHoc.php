<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class KhoaHoc extends Model
{
    //
    protected $table = 'khoahoc'; 
    protected $primaryKey = 'khoaHocId'; 

    public function loaiKhoaHoc(){
        return $this->belongsTo(LoaiKhoaHoc::class, 'loaiKhoaHocId', 'loaiKhoaHocId');
    }
}
