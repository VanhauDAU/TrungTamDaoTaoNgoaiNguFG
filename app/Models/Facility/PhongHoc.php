<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class PhongHoc extends Model
{
    //
    protected $primaryKey = 'phongHocId';
    protected $table = 'phonghoc';
    protected $fillable = [
        'tenPhong',
        'sucChua',
        'trangThietBi',
        'coSoId',
        'trangThai'
    ];
    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }
}
