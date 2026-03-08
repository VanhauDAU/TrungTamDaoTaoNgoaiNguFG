<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;

class DangKyLopHoc extends Model
{
    // ── Trạng thái đăng ký lớp học ──────────────────────────────────
    public const TRANG_THAI_HUY = 0;
    public const TRANG_THAI_CHO_THANH_TOAN = 1;
    public const TRANG_THAI_DA_XAC_NHAN = 2;
    public const TRANG_THAI_TAM_DUNG_NO_HOC_PHI = 3;

    protected $table      = 'dangKyLopHoc';
    protected $primaryKey = 'dangKyLopHocId';
    protected $fillable   = [
        'taiKhoanId',
        'lopHocId',
        'ngayDangKy',
        'trangThai',
    ];
    public $timestamps = false;

    protected $casts = [
        'trangThai' => 'integer',
    ];

    /* ── Relationships ──────────────────────────────────────────────── */

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function hoaDon()
    {
        return $this->hasOne(HoaDon::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    /* ── Accessors ──────────────────────────────────────────────────── */

    public function getTrangThaiLabelAttribute(): string
    {
        return match ((int) $this->trangThai) {
            self::TRANG_THAI_CHO_THANH_TOAN => 'Chờ thanh toán',
            self::TRANG_THAI_DA_XAC_NHAN => 'Đã xác nhận',
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 'Tạm dừng do nợ học phí',
            self::TRANG_THAI_HUY => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getIsNoHocPhiAttribute(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI;
    }

    public function isPendingPayment(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_CHO_THANH_TOAN;
    }

    public function isConfirmed(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_XAC_NHAN;
    }

    public function isSuspendedForDebt(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI;
    }

    public function isCancelled(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_HUY;
    }

    public function preventsClassDeletion(): bool
    {
        return ! $this->isCancelled();
    }

    public function blocksSeat(): bool
    {
        return in_array((int) $this->trangThai, [
            self::TRANG_THAI_CHO_THANH_TOAN,
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
        ], true);
    }

    public function canJoinChat(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_XAC_NHAN;
    }

    public function canAccessSchedule(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_XAC_NHAN;
    }

    public function scopeBlockingSeat($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_CHO_THANH_TOAN,
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
        ]);
    }

    public function scopeVisibleToStudent($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_CHO_THANH_TOAN,
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
        ]);
    }

    public function scopeEligibleForSchedule($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DA_XAC_NHAN);
    }

    public function scopeEligibleForChat($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DA_XAC_NHAN);
    }

    public function scopePreventingClassDeletion($query)
    {
        return $query->where('trangThai', '!=', self::TRANG_THAI_HUY);
    }
}
