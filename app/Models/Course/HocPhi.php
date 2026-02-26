<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class HocPhi extends Model
{
    protected $table = 'hocphi';
    protected $primaryKey = 'hocPhiId';

    protected $fillable = [
        'khoaHocId',
        'soBuoi',   // Số buổi trong gói học phí (học viên mua gói này được học N buổi)
        'donGia',   // Đơn giá mỗi buổi (VNĐ) – thu từ HỌC VIÊN
        'trangThai',
    ];

    protected $casts = [
        'soBuoi'    => 'integer',
        'donGia'    => 'decimal:0',
        'trangThai' => 'integer',
    ];

    /**
     * Tổng học phí = soBuoi × donGia
     * Đây là số tiền HỌC VIÊN phải đóng khi đăng ký gói này
     */
    public function getTongHocPhiAttribute(): float
    {
        return (float) ($this->soBuoi * $this->donGia);
    }

    /** Định dạng tiền tổng */
    public function getTongHocPhiFormatAttribute(): string
    {
        return number_format($this->tongHocPhi, 0, ',', '.') . ' đ';
    }

    /** Định dạng đơn giá */
    public function getDonGiaFormatAttribute(): string
    {
        return number_format((float)$this->donGia, 0, ',', '.') . ' đ/buổi';
    }

    // ── Relationships ─────────────────────────────────────────────
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoaHocId', 'khoaHocId');
    }

    /** Các lớp học dùng gói học phí này */
    public function lopHocs()
    {
        return $this->hasMany(\App\Models\Education\LopHoc::class, 'hocPhiId', 'hocPhiId');
    }
}
