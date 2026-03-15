<?php

namespace App\Models\Education;

use App\Models\Finance\HoaDon;
use Illuminate\Database\Eloquent\Model;

class LopHocDotThu extends Model
{
    protected $table = 'lophoc_dotthu';
    protected $primaryKey = 'lopHocDotThuId';

    protected $fillable = [
        'lopHocChinhSachGiaId',
        'tenDotThu',
        'thuTu',
        'soTien',
        'hanThanhToan',
        'trangThai',
    ];

    protected $casts = [
        'thuTu' => 'integer',
        'soTien' => 'decimal:2',
        'hanThanhToan' => 'date',
        'trangThai' => 'integer',
    ];

    public function chinhSachGia()
    {
        return $this->belongsTo(LopHocChinhSachGia::class, 'lopHocChinhSachGiaId', 'lopHocChinhSachGiaId');
    }

    public function hoaDons()
    {
        return $this->hasMany(HoaDon::class, 'lopHocDotThuId', 'lopHocDotThuId');
    }
}
