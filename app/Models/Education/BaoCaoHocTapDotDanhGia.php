<?php

namespace App\Models\Education;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTapDotDanhGia extends Model
{
    public const TRANG_THAI_DRAFT = 'draft';
    public const TRANG_THAI_COLLECTING = 'collecting';
    public const TRANG_THAI_TEACHER_SUBMITTED = 'teacher_submitted';
    public const TRANG_THAI_STAFF_REVIEWING = 'staff_reviewing';
    public const TRANG_THAI_PUBLISHED = 'published';
    public const TRANG_THAI_CLOSED = 'closed';

    protected $table = 'bao_cao_hoc_tap_dot_danh_gia';
    protected $primaryKey = 'dotDanhGiaId';

    protected $fillable = [
        'lopHocId',
        'baoCaoHocTapMauId',
        'tenDot',
        'tuNgay',
        'denNgay',
        'hanNop',
        'hanDuyet',
        'trangThai',
        'createdById',
        'publishedAt',
        'closedAt',
    ];

    protected $casts = [
        'tuNgay' => 'date',
        'denNgay' => 'date',
        'hanNop' => 'date',
        'hanDuyet' => 'date',
        'publishedAt' => 'datetime',
        'closedAt' => 'datetime',
    ];

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_DRAFT => 'Nháp',
            self::TRANG_THAI_COLLECTING => 'Đang thu thập',
            self::TRANG_THAI_TEACHER_SUBMITTED => 'GV đã gửi',
            self::TRANG_THAI_STAFF_REVIEWING => 'Staff đang duyệt',
            self::TRANG_THAI_PUBLISHED => 'Đã phát hành',
            self::TRANG_THAI_CLOSED => 'Đã khóa',
        ];
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[$this->trangThai] ?? 'Không xác định';
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function mau()
    {
        return $this->belongsTo(BaoCaoHocTapMau::class, 'baoCaoHocTapMauId', 'baoCaoHocTapMauId');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(TaiKhoan::class, 'createdById', 'taiKhoanId');
    }

    public function baoCaos()
    {
        return $this->hasMany(BaoCaoHocTap::class, 'dotDanhGiaId', 'dotDanhGiaId');
    }
}
