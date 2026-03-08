<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DiemDanh;
use App\Models\Facility\CoSoDaoTao;
use Carbon\Carbon;

class HoaDon extends Model
{
    // Loại hóa đơn
    const LOAI_DANG_KY_MOI = 0;
    const LOAI_GIA_HAN = 1;
    const LOAI_KHAC = 2;

    // Trạng thái thanh toán
    const TRANG_THAI_CHUA_TT = 0;
    const TRANG_THAI_MOT_PHAN = 1;
    const TRANG_THAI_DA_TT = 2;

    // Số ngày cảnh báo trước khi hết hạn
    const NGAY_CANH_BAO = 7;

    protected $table = 'hoadon';
    protected $primaryKey = 'hoaDonId';
    public $timestamps = false;

    protected $fillable = [
        'maHoaDon',
        'ngayLap',
        'ngayHetHan',
        'tongTien',
        'giamGia',
        'thue',
        'tongTienSauThue',
        'daTra',
        'taiKhoanId',
        'nguoiLapId',
        'dangKyLopHocId',
        'phuongThucThanhToan',
        'loaiHoaDon',
        'coSoId',
        'trangThai',
        'ghiChu',
    ];

    protected $casts = [
        'tongTien' => 'decimal:2',
        'giamGia' => 'decimal:2',
        'thue' => 'decimal:2',
        'tongTienSauThue' => 'decimal:2',
        'daTra' => 'decimal:2',
        'trangThai' => 'integer',
        'loaiHoaDon' => 'integer',
        'phuongThucThanhToan' => 'integer',
    ];

    /* ── Relationships ─────────────────────────────────────── */

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nguoiLap()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiLapId', 'taiKhoanId');
    }

    public function dangKyLopHoc()
    {
        return $this->belongsTo(DangKyLopHoc::class, 'dangKyLopHocId', 'dangKyLopHocId');
    }

    public function coSo()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }

    public function phieuThus()
    {
        return $this->hasMany(PhieuThu::class, 'hoaDonId', 'hoaDonId');
    }

    /** Chỉ phiếu thu hợp lệ (chưa hủy) */
    public function phieuThusHopLe()
    {
        return $this->hasMany(PhieuThu::class, 'hoaDonId', 'hoaDonId')
            ->where('trangThai', 1);
    }

    /* ── Accessors ─────────────────────────────────────────── */

    /** Số tiền còn nợ (đã trừ giảm giá) */
    public function getConNoAttribute()
    {
        $thucThu = $this->tongTien - $this->giamGia;
        return max(0, $thucThu - $this->daTra);
    }

    /** Nhãn loại hóa đơn */
    public function getLoaiHoaDonLabelAttribute()
    {
        return match ((int) $this->loaiHoaDon) {
            self::LOAI_DANG_KY_MOI => 'Đăng ký mới',
            self::LOAI_GIA_HAN => 'Gia hạn',
            self::LOAI_KHAC => 'Khác',
            default => 'Không xác định',
        };
    }

    /** Nhãn trạng thái */
    public function getTrangThaiLabelAttribute()
    {
        return match ((int) $this->trangThai) {
            self::TRANG_THAI_CHUA_TT => 'Chưa thanh toán',
            self::TRANG_THAI_MOT_PHAN => 'Thanh toán một phần',
            self::TRANG_THAI_DA_TT => 'Đã thanh toán đủ',
            default => 'Không xác định',
        };
    }

    /** Số ngày còn lại đến hạn (âm = quá hạn). NULL nếu không có hạn. */
    public function getSoNgayConLaiAttribute(): ?int
    {
        if (! $this->ngayHetHan) {
            return null;
        }
        return (int) Carbon::today()->diffInDays(Carbon::parse($this->ngayHetHan), false);
    }

    /** True nếu hóa đơn chưa TT đủ và sắp hết hạn (≤ NGAY_CANH_BAO ngày) */
    public function getIsSapHetHanAttribute(): bool
    {
        if ($this->trangThai === self::TRANG_THAI_DA_TT || ! $this->ngayHetHan) {
            return false;
        }
        $soNgay = $this->soNgayConLai;
        return $soNgay !== null && $soNgay >= 0 && $soNgay <= self::NGAY_CANH_BAO;
    }

    /** True nếu hóa đơn chưa TT đủ và đã quá hạn */
    public function getIsQuaHanAttribute(): bool
    {
        if ($this->trangThai === self::TRANG_THAI_DA_TT || ! $this->ngayHetHan) {
            return false;
        }
        return Carbon::today()->greaterThan(Carbon::parse($this->ngayHetHan));
    }

    /** Nhãn tình trạng hạn thanh toán để hiển thị badge */
    public function getTinhTrangHanLabelAttribute(): ?string
    {
        if ($this->isQuaHan) {
            return 'Quá hạn';
        }
        if ($this->isSapHetHan) {
            $ngay = $this->soNgayConLai;
            return $ngay === 0 ? 'Hôm nay hết hạn' : "Còn {$ngay} ngày";
        }
        return null;
    }

    /* ── Helpers ────────────────────────────────────────────── */

    /** Tạo mã hóa đơn duy nhất: HD-YYYYMM-XXXXXX */
    public static function generateMaHoaDon(): string
    {
        $prefix = 'HD-' . now()->format('Ym') . '-';
        $lastOrder = static::where('maHoaDon', 'like', $prefix . '%')
            ->orderByDesc('maHoaDon')
            ->value('maHoaDon');

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /** Tính lại daTra + trangThai từ phiếu thu hợp lệ, phục hồi DangKyLopHoc khi TT đủ */
    public function recalculate(): void
    {
        $totalPaid = $this->phieuThusHopLe()->sum('soTien');
        $this->daTra = $totalPaid;

        $thucThu = $this->tongTien - $this->giamGia;

        if ($totalPaid <= 0) {
            $this->trangThai = self::TRANG_THAI_CHUA_TT;
        } elseif ($totalPaid >= $thucThu) {
            $this->trangThai = self::TRANG_THAI_DA_TT;
        } else {
            $this->trangThai = self::TRANG_THAI_MOT_PHAN;
        }

        $this->save();

        // ── Phục hồi đăng ký lớp khi thanh toán đủ ──────────────────
        if ($this->trangThai === self::TRANG_THAI_DA_TT && $this->dangKyLopHocId) {
            $dangKy = DangKyLopHoc::find($this->dangKyLopHocId);
            if ($dangKy && in_array((int) $dangKy->trangThai, [
                DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN,
                DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
            ], true)) {
                $dangKy->update(['trangThai' => DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN]);

                // Xóa các bản ghi DiemDanh tương lai đã bị khóa (nợ HP)
                DiemDanh::where('dangKyLopHocId', $dangKy->dangKyLopHocId)
                    ->where('trangThai', DiemDanh::BI_KHOA_NO_HP)
                    ->delete();
            }
        }
    }
}
