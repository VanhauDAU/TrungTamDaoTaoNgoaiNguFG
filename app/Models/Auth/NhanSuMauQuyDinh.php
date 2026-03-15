<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhanSuMauQuyDinh extends Model
{
    public const PHAM_VI_GIAO_VIEN = 'giao_vien';
    public const PHAM_VI_NHAN_VIEN = 'nhan_vien';
    public const PHAM_VI_BOTH = 'both';

    protected $table = 'nhansu_mau_quydinh';
    protected $primaryKey = 'nhanSuMauQuyDinhId';

    protected $fillable = [
        'maMau',
        'tieuDe',
        'phamViApDung',
        'loaiHopDongApDung',
        'noiDung',
        'phienBan',
        'trangThai',
        'createdById',
        'updatedById',
    ];

    protected $casts = [
        'phienBan' => 'integer',
        'trangThai' => 'integer',
    ];

    public static function phamViOptions(): array
    {
        return [
            self::PHAM_VI_GIAO_VIEN => 'Giáo viên',
            self::PHAM_VI_NHAN_VIEN => 'Nhân viên',
            self::PHAM_VI_BOTH => 'Cả hai',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'createdById', 'taiKhoanId');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'updatedById', 'taiKhoanId');
    }

    public function hoSos(): HasMany
    {
        return $this->hasMany(NhanSuHoSo::class, 'nhanSuMauQuyDinhId', 'nhanSuMauQuyDinhId');
    }

    public function scopeActive($query)
    {
        return $query->where('trangThai', 1);
    }
}
