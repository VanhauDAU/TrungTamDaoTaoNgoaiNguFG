<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;

class PhieuThu extends Model
{
    // Trạng thái
    const TRANG_THAI_HUY = 0;
    const TRANG_THAI_HOP_LE = 1;

    protected $table = 'phieuthu';
    protected $primaryKey = 'phieuThuId';

    protected $fillable = [
        'maPhieuThu',
        'hoaDonId',
        'soTien',
        'ngayThu',
        'phuongThucThanhToan',
        'taiKhoanId',
        'nguoiDuyetId',
        'ghiChu',
        'trangThai',
    ];

    protected $casts = [
        'soTien' => 'decimal:2',
        'trangThai' => 'integer',
        'phuongThucThanhToan' => 'integer',
    ];

    /* ── Relationships ─────────────────────────────────────── */

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'hoaDonId', 'hoaDonId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nguoiDuyet()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiDuyetId', 'taiKhoanId');
    }

    /* ── Accessors ─────────────────────────────────────────── */

    /** Nhãn phương thức thanh toán */
    public function getPhuongThucLabelAttribute()
    {
        return match ((int) $this->phuongThucThanhToan) {
            1 => 'Tiền mặt',
            2 => 'Chuyển khoản',
            3 => 'VNPay',
            default => 'Khác',
        };
    }

    /* ── Helpers ────────────────────────────────────────────── */

    /** Tạo mã phiếu thu duy nhất: PT-YYYYMM-XXXXXX */
    public static function generateMaPhieuThu(): string
    {
        $prefix = 'PT-' . now()->format('Ym') . '-';
        $last = static::where('maPhieuThu', 'like', $prefix . '%')
            ->orderByDesc('maPhieuThu')
            ->value('maPhieuThu');

        if ($last) {
            $lastNumber = (int) substr($last, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
