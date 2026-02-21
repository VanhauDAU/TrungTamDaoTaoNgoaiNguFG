<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class PhongHoc extends Model
{
    protected $primaryKey = 'phongHocId';
    protected $table = 'phonghoc';
    public $timestamps = false;

    protected $fillable = [
        'tenPhong',
        'sucChua',
        'trangThietBi',
        'coSoId',
        'trangThai',
    ];

    protected $casts = [
        'sucChua'   => 'integer',
        'trangThai' => 'integer',
    ];

    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }
}
