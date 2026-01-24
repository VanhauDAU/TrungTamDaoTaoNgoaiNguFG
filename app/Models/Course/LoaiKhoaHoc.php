<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class LoaiKhoaHoc extends Model
{
    //
    protected $table = 'loaikhoahoc';
    protected $primaryKey = 'loaiKhoaHocId'; 
    public function khoaHocs(){
        return $this->hasMany(KhoaHoc::class, 'loaiKhoaHocId', 'loaiKhoaHocId');
    }
}
