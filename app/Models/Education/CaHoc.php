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
        'trangThai'
    ];

    public function buoiHocs()
    {
        return $this->hasMany(BuoiHoc::class, 'caHocId', 'caHocId');
    }
}
