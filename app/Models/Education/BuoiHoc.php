<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Facility\PhongHoc;

class BuoiHoc extends Model
{
    public const TRANG_THAI_SAP_DIEN_RA = 0;
    public const TRANG_THAI_DANG_DIEN_RA = 1;
    public const TRANG_THAI_DA_HOAN_THANH = 2;
    public const TRANG_THAI_DA_HUY = 3;
    public const TRANG_THAI_DOI_LICH = 4;

    protected $table = 'buoihoc';
    protected $primaryKey = 'buoiHocId';
    protected $fillable = [
        'lopHocId',
        'tenBuoiHoc',
        'ngayHoc',
        'caHocId',
        'phongHocId',
        'taiKhoanId',
        'ghiChu',
        'daDiemDanh',
        'daHoanThanh',
        'trangThai',
    ];

    protected $casts = [
        'trangThai' => 'integer',
        'daDiemDanh' => 'boolean',
        'daHoanThanh' => 'boolean',
    ];

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_SAP_DIEN_RA => 'Sắp diễn ra',
            self::TRANG_THAI_DANG_DIEN_RA => 'Đang diễn ra',
            self::TRANG_THAI_DA_HOAN_THANH => 'Đã hoàn thành',
            self::TRANG_THAI_DA_HUY => 'Đã hủy',
            self::TRANG_THAI_DOI_LICH => 'Đổi lịch',
        ];
    }

    public static function trangThaiOptions(): array
    {
        return self::trangThaiLabels();
    }

    public static function trangThaiKeys(): array
    {
        return [
            self::TRANG_THAI_SAP_DIEN_RA => 'sap-dien-ra',
            self::TRANG_THAI_DANG_DIEN_RA => 'dang-dien-ra',
            self::TRANG_THAI_DA_HOAN_THANH => 'da-hoan-thanh',
            self::TRANG_THAI_DA_HUY => 'da-huy',
            self::TRANG_THAI_DOI_LICH => 'doi-lich',
        ];
    }

    public static function trangThaiIcons(): array
    {
        return [
            self::TRANG_THAI_SAP_DIEN_RA => 'fa-calendar-day',
            self::TRANG_THAI_DANG_DIEN_RA => 'fa-person-chalkboard',
            self::TRANG_THAI_DA_HOAN_THANH => 'fa-check-circle',
            self::TRANG_THAI_DA_HUY => 'fa-ban',
            self::TRANG_THAI_DOI_LICH => 'fa-calendar-xmark',
        ];
    }

    public static function validTrangThaiValues(): array
    {
        return array_map('strval', array_keys(self::trangThaiLabels()));
    }

    public static function normalizeStatePayload(array $attributes, ?self $current = null): array
    {
        $currentTrangThai = $current ? (int) $current->trangThai : self::TRANG_THAI_SAP_DIEN_RA;

        $incomingTrangThai = array_key_exists('trangThai', $attributes) && $attributes['trangThai'] !== null && $attributes['trangThai'] !== ''
            ? (int) $attributes['trangThai']
            : null;

        $incomingDaHoanThanh = array_key_exists('daHoanThanh', $attributes) && $attributes['daHoanThanh'] !== null && $attributes['daHoanThanh'] !== ''
            ? (int) $attributes['daHoanThanh']
            : null;

        if ($incomingTrangThai !== null) {
            $attributes['trangThai'] = $incomingTrangThai;
            $attributes['daHoanThanh'] = $incomingTrangThai === self::TRANG_THAI_DA_HOAN_THANH ? 1 : 0;
        } elseif ($incomingDaHoanThanh !== null) {
            $attributes['daHoanThanh'] = $incomingDaHoanThanh;

            if ($incomingDaHoanThanh === 1) {
                $attributes['trangThai'] = self::TRANG_THAI_DA_HOAN_THANH;
            } elseif ($currentTrangThai === self::TRANG_THAI_DA_HOAN_THANH) {
                $attributes['trangThai'] = self::TRANG_THAI_SAP_DIEN_RA;
            }
        }

        if ((! array_key_exists('trangThai', $attributes) || $attributes['trangThai'] === null || $attributes['trangThai'] === '')
            && ! $current) {
            $attributes['trangThai'] = self::TRANG_THAI_SAP_DIEN_RA;
        }

        if (! array_key_exists('daHoanThanh', $attributes) || $attributes['daHoanThanh'] === null || $attributes['daHoanThanh'] === '') {
            $resolvedTrangThai = array_key_exists('trangThai', $attributes) && $attributes['trangThai'] !== null && $attributes['trangThai'] !== ''
                ? (int) $attributes['trangThai']
                : $currentTrangThai;

            $attributes['daHoanThanh'] = $resolvedTrangThai === self::TRANG_THAI_DA_HOAN_THANH ? 1 : 0;
        }

        return $attributes;
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function caHoc()
    {
        return $this->belongsTo(CaHoc::class, 'caHocId', 'caHocId');
    }

    public function phongHoc()
    {
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    /** Danh sách điểm danh của từng học viên trong buổi học này */
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'buoiHocId', 'buoiHocId');
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[(int) $this->trangThai] ?? 'Không xác định';
    }

    public function getTrangThaiKeyAttribute(): string
    {
        return self::trangThaiKeys()[(int) $this->trangThai] ?? 'sap-dien-ra';
    }

    public function getTrangThaiIconAttribute(): string
    {
        return self::trangThaiIcons()[(int) $this->trangThai] ?? 'fa-calendar-day';
    }

    public function isUpcoming(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_SAP_DIEN_RA;
    }

    public function isLive(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DANG_DIEN_RA;
    }

    public function isCompleted(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_HOAN_THANH;
    }

    public function isCancelled(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_HUY;
    }

    public function isRescheduled(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DOI_LICH;
    }

    public function scopeUpcoming($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_SAP_DIEN_RA);
    }

    public function scopeOpenForAttendance($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_SAP_DIEN_RA,
            self::TRANG_THAI_DANG_DIEN_RA,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DA_HOAN_THANH);
    }
}
