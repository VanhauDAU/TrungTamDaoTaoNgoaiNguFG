<?php

namespace App\Models\Education;

use App\Models\Finance\HoaDon;
use Illuminate\Database\Eloquent\Model;

class DangKyLopHocPhuPhi extends Model
{
    public const TRANG_THAI_HIEU_LUC = 1;
    public const TRANG_THAI_HUY = 0;

    protected $table = 'dangkylophoc_phuphi';
    protected $primaryKey = 'dangKyLopHocPhuPhiId';

    protected $fillable = [
        'dangKyLopHocId',
        'lopHocPhuPhiId',
        'tenKhoanThuSnapshot',
        'nhomPhiSnapshot',
        'soTienSnapshot',
        'hanThanhToan',
        'trangThai',
        'ngayApDung',
    ];

    protected $casts = [
        'soTienSnapshot' => 'decimal:2',
        'hanThanhToan' => 'date',
        'ngayApDung' => 'datetime',
        'trangThai' => 'integer',
    ];

    public function dangKyLopHoc()
    {
        return $this->belongsTo(DangKyLopHoc::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function lopHocPhuPhi()
    {
        return $this->belongsTo(LopHocPhuPhi::class, 'lopHocPhuPhiId', 'lopHocPhuPhiId');
    }

    public function hoaDons()
    {
        return $this->hasMany(HoaDon::class, 'dangKyLopHocPhuPhiId', 'dangKyLopHocPhuPhiId');
    }

    public function getNhomPhiLabelAttribute(): string
    {
        return LopHocPhuPhi::nhomPhiOptions()[$this->nhomPhiSnapshot] ?? 'Khoản khác';
    }
}
