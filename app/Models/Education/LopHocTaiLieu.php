<?php

namespace App\Models\Education;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class LopHocTaiLieu extends Model
{
    // ── Nhóm tài liệu ────────────────────────────────────────────────────────
    public const NHOM_TAI_LIEU         = 'tai_lieu';        // Tài liệu học tập
    public const NHOM_BAI_TAP          = 'bai_tap';         // Bài tập / bài kiểm tra
    public const NHOM_GIAO_TRINH       = 'giao_trinh';      // Giáo trình
    public const NHOM_SLIDE            = 'slide';            // Slide bài giảng
    public const NHOM_AM_THANH         = 'am_thanh';        // File nghe
    public const NHOM_VIDEO            = 'video';            // Video bài giảng
    public const NHOM_KHAC             = 'khac';             // Khác

    // ── Trạng thái tài liệu ──────────────────────────────────────────────────
    public const TRANG_THAI_ACTIVE     = 1;   // Đang hiển thị
    public const TRANG_THAI_HIDDEN     = 0;   // Ẩn

    protected $table      = 'lophoc_tai_lieu';
    protected $primaryKey = 'lopHocTaiLieuId';

    protected $fillable = [
        'lopHocId',
        'giaoVienTaiLieuId',   // nullable: null = upload thẳng, not-null = chia sẻ từ thư viện
        'tieuDe',
        'moTa',
        'nhomTaiLieu',
        'disk',
        'duongDan',
        'tenGoc',
        'mime',
        'kichThuoc',
        'nguoiTaiLenId',
        'publishedAt',
        'sortOrder',
        'trangThai',
    ];

    protected $casts = [
        'lopHocTaiLieuId'    => 'integer',
        'giaoVienTaiLieuId'  => 'integer',
        'trangThai'          => 'integer',
        'kichThuoc'          => 'integer',
        'sortOrder'          => 'integer',
        'publishedAt'        => 'datetime',
    ];

    /* ── Relationships ──────────────────────────────────────────────────────── */

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function nguoiTaiLen()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiTaiLenId', 'taiKhoanId');
    }

    /**
     * Tài liệu gốc trong thư viện cá nhân (nếu được chia sẻ từ thư viện).
     */
    public function giaoVienTaiLieu()
    {
        return $this->belongsTo(GiaoVienTaiLieu::class, 'giaoVienTaiLieuId', 'giaoVienTaiLieuId');
    }

    /* ── Constants helpers ──────────────────────────────────────────────────── */

    public static function nhomOptions(): array
    {
        return [
            self::NHOM_TAI_LIEU   => 'Tài liệu học tập',
            self::NHOM_BAI_TAP    => 'Bài tập',
            self::NHOM_GIAO_TRINH => 'Giáo trình',
            self::NHOM_SLIDE      => 'Slide bài giảng',
            self::NHOM_AM_THANH   => 'File nghe',
            self::NHOM_VIDEO      => 'Video',
            self::NHOM_KHAC       => 'Khác',
        ];
    }

    public static function trangThaiOptions(): array
    {
        return [
            self::TRANG_THAI_ACTIVE => 'Đang hiển thị',
            self::TRANG_THAI_HIDDEN => 'Ẩn',
        ];
    }

    /* ── Accessors ──────────────────────────────────────────────────────────── */

    public function getNhomLabelAttribute(): string
    {
        return self::nhomOptions()[$this->nhomTaiLieu] ?? 'Khác';
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiOptions()[$this->trangThai] ?? 'Không xác định';
    }

    public function getKichThuocReadableAttribute(): string
    {
        $bytes = (int) $this->kichThuoc;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /* ── Scopes ─────────────────────────────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_ACTIVE);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sortOrder')->orderBy('lopHocTaiLieuId');
    }
}
