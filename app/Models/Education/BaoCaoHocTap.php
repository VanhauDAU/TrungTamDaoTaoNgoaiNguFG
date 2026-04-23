<?php

namespace App\Models\Education;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTap extends Model
{
    public const TRANG_THAI_DRAFT = 'draft';
    public const TRANG_THAI_SUBMITTED = 'submitted';
    public const TRANG_THAI_NEEDS_REVISION = 'needs_revision';
    public const TRANG_THAI_APPROVED = 'approved';
    public const TRANG_THAI_PUBLISHED = 'published';

    protected $table = 'bao_cao_hoc_tap';
    protected $primaryKey = 'baoCaoHocTapId';

    protected $fillable = [
        'dotDanhGiaId',
        'dangKyLopHocId',
        'giaoVienId',
        'nguoiDuyetId',
        'parentBaoCaoHocTapId',
        'version',
        'trangThai',
        'staffReviewNote',
        'metadataSnapshot',
        'submittedAt',
        'approvedAt',
        'publishedAt',
    ];

    protected $casts = [
        'version' => 'integer',
        'metadataSnapshot' => 'array',
        'submittedAt' => 'datetime',
        'approvedAt' => 'datetime',
        'publishedAt' => 'datetime',
    ];

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_DRAFT => 'Nháp',
            self::TRANG_THAI_SUBMITTED => 'Chờ duyệt',
            self::TRANG_THAI_NEEDS_REVISION => 'Cần chỉnh sửa',
            self::TRANG_THAI_APPROVED => 'Đã duyệt',
            self::TRANG_THAI_PUBLISHED => 'Đã phát hành',
        ];
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[$this->trangThai] ?? 'Không xác định';
    }

    public function isEditableByTeacher(): bool
    {
        return in_array($this->trangThai, [
            self::TRANG_THAI_DRAFT,
            self::TRANG_THAI_NEEDS_REVISION,
        ], true);
    }

    public function dotDanhGia()
    {
        return $this->belongsTo(BaoCaoHocTapDotDanhGia::class, 'dotDanhGiaId', 'dotDanhGiaId');
    }

    public function dangKyLopHoc()
    {
        return $this->belongsTo(DangKyLopHoc::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function giaoVien()
    {
        return $this->belongsTo(TaiKhoan::class, 'giaoVienId', 'taiKhoanId');
    }

    public function nguoiDuyet()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiDuyetId', 'taiKhoanId');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parentBaoCaoHocTapId', 'baoCaoHocTapId');
    }

    public function revisions()
    {
        return $this->hasMany(self::class, 'parentBaoCaoHocTapId', 'baoCaoHocTapId');
    }

    public function tieuChis()
    {
        return $this->hasMany(BaoCaoHocTapTieuChi::class, 'baoCaoHocTapId', 'baoCaoHocTapId')
            ->orderBy('thuTu');
    }

    public function lichSus()
    {
        return $this->hasMany(BaoCaoHocTapLichSu::class, 'baoCaoHocTapId', 'baoCaoHocTapId')
            ->orderByDesc('created_at');
    }
}
