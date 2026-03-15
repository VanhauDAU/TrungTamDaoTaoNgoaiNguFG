<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;
use Illuminate\Support\Collection;

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
        'lopHocChinhSachGiaId',
        'loaiThuSnapshot',
        'hocPhiNiemYetSnapshot',
        'giamGiaSnapshot',
        'hocPhiPhaiThuSnapshot',
        'soBuoiCamKetSnapshot',
        'ghiChuGiaSnapshot',
        'ngayDangKy',
        'ngayHetHanGiuCho',
        'trangThai',
    ];
    public $timestamps = false;

    protected $casts = [
        'trangThai' => 'integer',
        'loaiThuSnapshot' => 'integer',
        'hocPhiNiemYetSnapshot' => 'decimal:2',
        'giamGiaSnapshot' => 'decimal:2',
        'hocPhiPhaiThuSnapshot' => 'decimal:2',
        'soBuoiCamKetSnapshot' => 'integer',
        'ngayHetHanGiuCho' => 'datetime',
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
        return $this->hasOne(HoaDon::class, 'dangKyLopHocId', 'dangKyLopHocId')->latestOfMany('hoaDonId');
    }

    public function hoaDons()
    {
        return $this->hasMany(HoaDon::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function chinhSachGia()
    {
        return $this->belongsTo(LopHocChinhSachGia::class, 'lopHocChinhSachGiaId', 'lopHocChinhSachGiaId');
    }

    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function phuPhiSnapshots()
    {
        return $this->hasMany(DangKyLopHocPhuPhi::class, 'dangKyLopHocId', 'dangKyLopHocId');
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

    public static function trangThaiOptions(): array
    {
        return [
            self::TRANG_THAI_CHO_THANH_TOAN => 'Chờ thanh toán',
            self::TRANG_THAI_DA_XAC_NHAN => 'Đã xác nhận',
            self::TRANG_THAI_DANG_HOC => 'Đang học',
            self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 'Tạm dừng do nợ học phí',
            self::TRANG_THAI_BAO_LUU => 'Bảo lưu',
            self::TRANG_THAI_HOAN_THANH => 'Hoàn thành',
            self::TRANG_THAI_HUY => 'Đã hủy',
        ];
    }

    public function getIsNoHocPhiAttribute(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI;
    }

    public function getHocPhiTongTienAttribute(): float
    {
        return (float) ($this->hocPhiPhaiThuSnapshot ?? $this->hocPhiNiemYetSnapshot ?? 0);
    }

    public function getTongDaThuAttribute(): float
    {
        if ($this->relationLoaded('hoaDons')) {
            return (float) $this->resolveTuitionInvoices($this->hoaDons)
                ->sum(fn (HoaDon $hoaDon) => (float) $hoaDon->daTra);
        }

        return (float) $this->hoaDons()
            ->where('nguonThu', HoaDon::NGUON_THU_HOC_PHI)
            ->sum('daTra');
    }

    public function getTongConNoAttribute(): float
    {
        return max(0, $this->hocPhiTongTien - $this->tongDaThu);
    }

    public function isPendingPayment(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_CHO_THANH_TOAN;
    }

    public function isHoldExpired(): bool
    {
        return $this->isPendingPayment()
            && $this->ngayHetHanGiuCho !== null
            && now()->greaterThan($this->ngayHetHanGiuCho);
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

    public function recalculatePaymentStatus(): void
    {
        if (in_array((int) $this->trangThai, [
            self::TRANG_THAI_BAO_LUU,
            self::TRANG_THAI_HOAN_THANH,
            self::TRANG_THAI_HUY,
        ], true)) {
            return;
        }

        $this->loadMissing(['lopHoc', 'hoaDons.lopHocDotThu']);

        $mandatoryInvoices = $this->resolveTuitionInvoices($this->hoaDons);

        if ($mandatoryInvoices->isEmpty() && $this->hoaDons->isNotEmpty()) {
            $mandatoryInvoices = $this->hoaDons->where('nguonThu', HoaDon::NGUON_THU_HOC_PHI)->values();
        }

        $hasOverdueMandatory = $mandatoryInvoices->contains(function (HoaDon $hoaDon) {
            return $hoaDon->isQuaHan && (int) $hoaDon->trangThai !== HoaDon::TRANG_THAI_DA_TT;
        });

        $mandatoryPaid = $mandatoryInvoices->isNotEmpty()
            && $mandatoryInvoices->every(fn (HoaDon $hoaDon) => (int) $hoaDon->trangThai === HoaDon::TRANG_THAI_DA_TT);

        $newStatus = self::TRANG_THAI_CHO_THANH_TOAN;

        if ($hasOverdueMandatory && $this->lopHoc && $this->lopHoc->isInProgress()) {
            $newStatus = self::TRANG_THAI_TAM_DUNG_NO_HOC_PHI;
        } elseif ($mandatoryPaid) {
            $newStatus = $this->lopHoc && $this->lopHoc->isInProgress()
                ? self::TRANG_THAI_DANG_HOC
                : self::TRANG_THAI_DA_XAC_NHAN;
        }

        if ((int) $this->trangThai !== $newStatus) {
            $payload = ['trangThai' => $newStatus];

            if ($newStatus !== self::TRANG_THAI_CHO_THANH_TOAN) {
                $payload['ngayHetHanGiuCho'] = null;
            }

            $this->update($payload);
        } elseif ($newStatus !== self::TRANG_THAI_CHO_THANH_TOAN && $this->ngayHetHanGiuCho !== null) {
            $this->update(['ngayHetHanGiuCho' => null]);
        }
    }

    private function resolveTuitionInvoices(Collection $hoaDons): Collection
    {
        return $hoaDons->filter(function (HoaDon $hoaDon) {
            return $hoaDon->nguonThu === HoaDon::NGUON_THU_HOC_PHI;
        })->values();
    }
}
