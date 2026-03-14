<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class LopHocChinhSachGia extends Model
{
    public const LOAI_THU_TRON_GOI = 0;
    public const LOAI_THU_THEO_THANG = 1;
    public const LOAI_THU_THEO_DOT = 2;

    protected $table = 'lophoc_chinhsachgia';
    protected $primaryKey = 'lopHocChinhSachGiaId';

    protected $fillable = [
        'lopHocId',
        'loaiThu',
        'hocPhiNiemYet',
        'soBuoiCamKet',
        'ghiChuChinhSach',
        'hieuLucTu',
        'hieuLucDen',
        'trangThai',
    ];

    protected $casts = [
        'loaiThu' => 'integer',
        'hocPhiNiemYet' => 'decimal:2',
        'soBuoiCamKet' => 'integer',
        'trangThai' => 'integer',
        'hieuLucTu' => 'datetime',
        'hieuLucDen' => 'datetime',
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function dotThus()
    {
        return $this->hasMany(LopHocDotThu::class, 'lopHocChinhSachGiaId', 'lopHocChinhSachGiaId')
            ->orderBy('thuTu');
    }

    public function dangKyLopHocs()
    {
        return $this->hasMany(DangKyLopHoc::class, 'lopHocChinhSachGiaId', 'lopHocChinhSachGiaId');
    }

    public static function loaiThuLabels(): array
    {
        return [
            self::LOAI_THU_TRON_GOI => 'Trọn gói',
            self::LOAI_THU_THEO_THANG => 'Theo tháng',
            self::LOAI_THU_THEO_DOT => 'Theo đợt',
        ];
    }

    public static function loaiThuOptions(): array
    {
        return self::loaiThuLabels();
    }

    public function getLoaiThuLabelAttribute(): string
    {
        return self::loaiThuLabels()[(int) $this->loaiThu] ?? 'Không xác định';
    }

    public function getTongHocPhiAttribute(): float
    {
        return (float) $this->hocPhiNiemYet;
    }

    public function getTongHocPhiFormatAttribute(): string
    {
        return number_format((float) $this->hocPhiNiemYet, 0, ',', '.') . ' đ';
    }

    public function getHasDotThuAttribute(): bool
    {
        return $this->dotThus()->where('trangThai', 1)->exists();
    }

    public function isActive(): bool
    {
        return (int) $this->trangThai === 1;
    }
}
