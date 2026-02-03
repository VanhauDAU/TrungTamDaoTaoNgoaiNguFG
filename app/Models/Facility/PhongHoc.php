<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class PhongHoc extends Model
{
    //
    protected $primaryKey = 'phongHocId';
    protected $table = 'phongHoc';
    protected $fillable = [
        'phongHocId',
        'tenPhong',
        'sucChua',
        'trangThietBi',
        'coSoId',
        'trangThai'
    ];
}
