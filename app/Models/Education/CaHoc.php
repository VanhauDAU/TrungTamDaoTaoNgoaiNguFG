<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class CaHoc extends Model
{
    //
    protected $table = 'cahoc';
    protected $primaryKey = 'caHocId';
    protected $fillable = [
        'tenCa',
        'gioBatDau',
        'gioKetThuc',
        'moTa',
        'trangThai'
    ];

    public function buoiHocs()
    {
        return $this->hasMany(BuoiHoc::class, 'caHocId', 'caHocId');
    }
    public function lopHocs()
    {
        return $this->hasMany(LopHoc::class, 'caHocId', 'caHocId');
    }
}
