<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhanSuGoiLuong extends Model
{
    public const LOAI_LUONG_MONTHLY = 'MONTHLY';
    public const LOAI_LUONG_HOURLY = 'HOURLY';
    public const LOAI_LUONG_PER_SESSION = 'PER_SESSION';
    public const LOAI_LUONG_FIXED_ALLOWANCE = 'FIXED_ALLOWANCE';

    protected $table = 'nhansu_goi_luong';
    protected $primaryKey = 'nhanSuGoiLuongId';

    protected $fillable = [
        'taiKhoanId',
        'loaiLuong',
        'luongChinh',
        'hieuLucTu',
        'hieuLucDen',
        'ghiChu',
        'trangThai',
    ];

    protected $casts = [
        'luongChinh' => 'decimal:2',
        'hieuLucTu' => 'date',
        'hieuLucDen' => 'date',
        'trangThai' => 'integer',
    ];

    public static function loaiLuongOptions(): array
    {
        return [
            self::LOAI_LUONG_MONTHLY => 'Lương tháng',
            self::LOAI_LUONG_HOURLY => 'Lương giờ',
            self::LOAI_LUONG_PER_SESSION => 'Lương theo buổi',
            self::LOAI_LUONG_FIXED_ALLOWANCE => 'Khoán cố định',
        ];
    }

    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(NhanSuGoiLuongChiTiet::class, 'nhanSuGoiLuongId', 'nhanSuGoiLuongId')
            ->orderBy('sortOrder')
            ->orderBy('nhanSuGoiLuongChiTietId');
    }

    public function isActive(): bool
    {
        return (int) $this->trangThai === 1 && $this->hieuLucDen === null;
    }

    public function scopeActive($query)
    {
        return $query->where('trangThai', 1)->whereNull('hieuLucDen');
    }
}
