<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class LopHocPhuPhi extends Model
{
    public const NHOM_PHI_TAI_LIEU = 'tai_lieu';
    public const NHOM_PHI_THI_THU = 'thi_thu';
    public const NHOM_PHI_KHAC = 'khac';

    protected $table = 'lophoc_phuphi';
    protected $primaryKey = 'lopHocPhuPhiId';

    protected $fillable = [
        'lopHocId',
        'tenKhoanThu',
        'nhomPhi',
        'soTien',
        'hanThanhToanMau',
        'apDungMacDinh',
        'trangThai',
    ];

    protected $casts = [
        'soTien' => 'decimal:2',
        'hanThanhToanMau' => 'date',
        'apDungMacDinh' => 'integer',
        'trangThai' => 'integer',
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function dangKyPhuPhis()
    {
        return $this->hasMany(DangKyLopHocPhuPhi::class, 'lopHocPhuPhiId', 'lopHocPhuPhiId');
    }

    public static function nhomPhiOptions(): array
    {
        return [
            self::NHOM_PHI_TAI_LIEU => 'Phí tài liệu',
            self::NHOM_PHI_THI_THU => 'Phí thi thử',
            self::NHOM_PHI_KHAC => 'Khoản khác',
        ];
    }

    public function getNhomPhiLabelAttribute(): string
    {
        return self::nhomPhiOptions()[$this->nhomPhi] ?? 'Khoản khác';
    }

    public function isDefaultApplied(): bool
    {
        return (int) $this->apDungMacDinh === 1;
    }

    public function isActive(): bool
    {
        return (int) $this->trangThai === 1;
    }
}
