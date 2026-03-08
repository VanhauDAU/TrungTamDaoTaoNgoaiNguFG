<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Auth\TaiKhoan;

class LienHe extends Model
{
    use SoftDeletes;

    protected $table = 'lienhe';
    protected $primaryKey = 'lienHeId';

    protected $fillable = [
        'hoTen',
        'email',
        'soDienThoai',
        'tieuDe',
        'noiDung',
        'trangThai', 
        'taiKhoanId',
        // CRM fields
        'loaiLienHe',
        'ghiChuNoiBo',
        'nguoiPhuTrachId',
        'thoiGianXuLy',
    ];

    protected $casts = [
        'trangThai'     => 'integer',
        'thoiGianXuLy'  => 'datetime',
    ];

    // ─── Constants ─────────────────────────────────────────────────────────────

    const LOAI_LABELS = [
        'tu_van'    => 'Tư vấn',
        'ho_tro'    => 'Hỗ trợ',
        'khieu_nai' => 'Khiếu nại',
        'khac'      => 'Khác',
    ];

    const LOAI_COLORS = [
        'tu_van'    => 'blue',
        'ho_tro'    => 'green',
        'khieu_nai' => 'red',
        'khac'      => 'gray',
    ];

    const TRANG_THAI_LABELS = [
        0 => 'Chưa xử lý',
        1 => 'Đang xử lý',
        2 => 'Đã xử lý',
        3 => 'Đã từ chối',
    ];

    const TRANG_THAI_COLORS = [
        0 => 'orange',
        1 => 'blue',
        2 => 'green',
        3 => 'red',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function lichSu(): HasMany
    {
        return $this->hasMany(LienHeLichSu::class, 'lienHeId', 'lienHeId')
                    ->orderBy('created_at', 'desc');
    }

    public function phanHoi(): HasMany
    {
        return $this->hasMany(LienHePhanHoi::class, 'lienHeId', 'lienHeId')
                    ->orderBy('created_at', 'asc');
    }

    public function nguoiPhuTrach(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiPhuTrachId', 'taiKhoanId');
    }

    // ─── Accessors ──────────────────────────────────────────────────────────────

    public function getLoaiLabelAttribute(): string
    {
        return self::LOAI_LABELS[$this->loaiLienHe] ?? 'Khác';
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::TRANG_THAI_LABELS[$this->trangThai] ?? 'Không xác định';
    }

    public function getTrangThaiColorAttribute(): string
    {
        return self::TRANG_THAI_COLORS[$this->trangThai] ?? 'gray';
    }
}
