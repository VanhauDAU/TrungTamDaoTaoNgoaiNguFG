<?php

namespace App\Models\Facility;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class CoSoNhatKy extends Model
{
    protected $table = 'coso_nhatky';
    protected $primaryKey = 'coSoNhatKyId';

    protected $fillable = [
        'coSoId',
        'phongHocId',
        'taiKhoanId',
        'hanhDong',
        'moTa',
        'duLieu',
    ];

    protected $casts = [
        'coSoId' => 'integer',
        'phongHocId' => 'integer',
        'taiKhoanId' => 'integer',
        'duLieu' => 'array',
    ];

    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }

    public function phongHoc()
    {
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
