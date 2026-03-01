<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;

class DiemDanh extends Model
{
    // ── Trạng thái điểm danh ────────────────────────────────────────
    const VANG_KHONG_PHEP = 0; // Absent without excuse
    const CO_MAT          = 1; // Present
    const DI_TRE          = 2; // Late
    const CO_PHEP         = 3; // Excused absence
    const BI_KHOA_NO_HP   = 4; // Suspended – overdue tuition

    // ── Hình thức học ────────────────────────────────────────────────
    const HINH_THUC_TRUC_TIEP = 0;
    const HINH_THUC_ONLINE    = 1;

    protected $table      = 'diemDanh';
    protected $primaryKey = 'diemDanhId';

    protected $fillable = [
        'buoiHocId',
        'taiKhoanId',
        'dangKyLopHocId',
        'trangThai',
        'coMat',
        'phutDiTre',
        'lyDo',
        'hinhThuc',
        'nguoiDiemDanhId',
        'thoiGianDiemDanh',
        'ghiChu',
    ];

    protected $casts = [
        'trangThai'        => 'integer',
        'coMat'            => 'boolean',
        'phutDiTre'        => 'integer',
        'hinhThuc'         => 'integer',
        'thoiGianDiemDanh' => 'datetime',
    ];

    /* ── Relationships ──────────────────────────────────────────────── */

    public function buoiHoc()
    {
        return $this->belongsTo(BuoiHoc::class, 'buoiHocId', 'buoiHocId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function dangKyLopHoc()
    {
        return $this->belongsTo(DangKyLopHoc::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function nguoiDiemDanh()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiDiemDanhId', 'taiKhoanId');
    }

    /* ── Accessors ──────────────────────────────────────────────────── */

    /** Nhãn trạng thái điểm danh */
    public function getTrangThaiLabelAttribute(): string
    {
        return match ((int) $this->trangThai) {
            self::VANG_KHONG_PHEP => 'Vắng không phép',
            self::CO_MAT          => 'Có mặt',
            self::DI_TRE          => 'Đi trễ',
            self::CO_PHEP         => 'Vắng có phép',
            self::BI_KHOA_NO_HP   => '⏸ Bị khóa – Nợ học phí',
            default               => 'Không xác định',
        };
    }

    /** Màu badge theo trạng thái */
    public function getTrangThaiBadgeClassAttribute(): string
    {
        return match ((int) $this->trangThai) {
            self::VANG_KHONG_PHEP => 'badge-danger',
            self::CO_MAT          => 'badge-success',
            self::DI_TRE          => 'badge-warning',
            self::CO_PHEP         => 'badge-info',
            self::BI_KHOA_NO_HP   => 'badge-secondary',
            default               => 'badge-light',
        };
    }

    /** True khi học viên vắng (kể cả bị khóa do nợ HP) */
    public function getIsVangAttribute(): bool
    {
        return in_array((int) $this->trangThai, [
            self::VANG_KHONG_PHEP,
            self::BI_KHOA_NO_HP,
        ]);
    }

    /** True khi học viên thực sự có mặt học */
    public function getIsChuanBiHocAttribute(): bool
    {
        return in_array((int) $this->trangThai, [
            self::CO_MAT,
            self::DI_TRE,
        ]);
    }

    /* ── Scopes ─────────────────────────────────────────────────────── */

    /** Chỉ lấy điểm danh của học viên có mặt */
    public function scopeCoMat($query)
    {
        return $query->whereIn('trangThai', [self::CO_MAT, self::DI_TRE]);
    }

    /** Chỉ lấy điểm danh của học viên bị khóa do nợ HP */
    public function scopeBiKhoa($query)
    {
        return $query->where('trangThai', self::BI_KHOA_NO_HP);
    }
}
