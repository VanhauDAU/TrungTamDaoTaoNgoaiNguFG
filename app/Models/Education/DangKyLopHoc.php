<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;

class DangKyLopHoc extends Model
{
    // ── Trạng thái đăng ký lớp học ──────────────────────────────────
    public const TRANG_THAI_CHO_THANH_TOAN = 0;
    public const TRANG_THAI_DA_XAC_NHAN = 1;
    public const TRANG_THAI_DANG_HOC = 2;
    public const TRANG_THAI_TAM_DUNG_NO_HOC_PHI = 3;
    public const TRANG_THAI_BAO_LUU = 4;
    public const TRANG_THAI_HOAN_THANH = 5;
    public const TRANG_THAI_HUY = 6;

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
            self::TRANG_THAI_DANG_HOC => 'Đang học',
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 'Tạm dừng do nợ học phí',
            self::TRANG_THAI_BAO_LUU => 'Bảo lưu',
            self::TRANG_THAI_HOAN_THANH => 'Hoàn thành',
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

    public function isStudying(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DANG_HOC;
    }

    public function isSuspendedForDebt(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI;
    }

    public function isOnLeave(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_BAO_LUU;
    }

    public function isCompleted(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_HOAN_THANH;
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
            self::TRANG_THAI_DANG_HOC,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
        ], true);
    }

    public function canJoinChat(?LopHoc $lopHoc = null): bool
    {
        $allowed = in_array((int) $this->trangThai, [
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_DANG_HOC,
        ], true);

        if (!$allowed) {
            return false;
        }

        return $lopHoc ? $lopHoc->canStudentJoinChat() : true;
    }

    public function canSendChat(?LopHoc $lopHoc = null): bool
    {
        $allowed = (int) $this->trangThai === self::TRANG_THAI_DANG_HOC;

        if (!$allowed) {
            return false;
        }

        return $lopHoc ? $lopHoc->canStudentSendChat() : true;
    }

    public function canAccessSchedule(?LopHoc $lopHoc = null): bool
    {
        $allowed = (int) $this->trangThai === self::TRANG_THAI_DANG_HOC;

        if (!$allowed) {
            return false;
        }

        return $lopHoc ? $lopHoc->isInProgress() : true;
    }

    public function scopeBlockingSeat($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_CHO_THANH_TOAN,
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_DANG_HOC,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
        ]);
    }

    public function scopeVisibleToStudent($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_CHO_THANH_TOAN,
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_DANG_HOC,
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
            self::TRANG_THAI_BAO_LUU,
            self::TRANG_THAI_HOAN_THANH,
            self::TRANG_THAI_HUY,
        ]);
    }

    public function scopeEligibleForSchedule($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DANG_HOC);
    }

    public function scopeCanJoinChat($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_DA_XAC_NHAN,
            self::TRANG_THAI_DANG_HOC,
        ]);
    }

    public function scopeCanSendChat($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DANG_HOC);
    }

    public function scopeEligibleForChat($query)
    {
        return $query->canJoinChat();
    }

    public function scopePreventingClassDeletion($query)
    {
        return $query->where('trangThai', '!=', self::TRANG_THAI_HUY);
    }
}
