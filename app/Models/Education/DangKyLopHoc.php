<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;

class DangKyLopHoc extends Model
{
    // ── Trạng thái đăng ký lớp học ──────────────────────────────────
    const TRANG_THAI_CHO_DUYET   = 0; // Chờ duyệt
    const TRANG_THAI_DANG_HOC    = 1; // Đang học (active)
    const TRANG_THAI_TAM_DUNG    = 2; // Tạm dừng – nợ học phí
    const TRANG_THAI_HUY         = 3; // Đã hủy

    protected $table      = 'dangKyLopHoc';
    protected $primaryKey = 'dangKyLopHocId';
    protected $fillable   = [
        'taiKhoanId',
        'lopHocId',
        'ngayDangKy',
        'trangThai',
    ];
    public $timestamps = false;

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
            self::TRANG_THAI_CHO_DUYET => 'Chờ duyệt',
            self::TRANG_THAI_DANG_HOC  => 'Đang học',
            self::TRANG_THAI_TAM_DUNG  => '⏸ Tạm dừng (Nợ học phí)',
            self::TRANG_THAI_HUY       => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getIsNoHocPhiAttribute(): bool
    {
        return $this->trangThai === self::TRANG_THAI_TAM_DUNG;
    }
}
