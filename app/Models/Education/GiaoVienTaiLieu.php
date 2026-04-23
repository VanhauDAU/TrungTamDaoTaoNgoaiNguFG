<?php

namespace App\Models\Education;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

/**
 * Thư viện tài liệu cá nhân của giáo viên.
 * File được upload một lần, sau đó có thể chia sẻ vào nhiều lớp khác nhau.
 */
class GiaoVienTaiLieu extends Model
{
    // ── Nhóm tài liệu (dùng chung với LopHocTaiLieu) ─────────────────────────
    public const NHOM_TAI_LIEU   = 'tai_lieu';
    public const NHOM_BAI_TAP    = 'bai_tap';
    public const NHOM_GIAO_TRINH = 'giao_trinh';
    public const NHOM_SLIDE      = 'slide';
    public const NHOM_AM_THANH   = 'am_thanh';
    public const NHOM_VIDEO      = 'video';
    public const NHOM_KHAC       = 'khac';

    protected $table      = 'giao_vien_tai_lieu';
    protected $primaryKey = 'giaoVienTaiLieuId';

    protected $fillable = [
        'nguoiTaiLenId',
        'tieuDe',
        'moTa',
        'nhomTaiLieu',
        'disk',
        'duongDan',
        'tenGoc',
        'mime',
        'kichThuoc',
    ];

    protected $casts = [
        'giaoVienTaiLieuId' => 'integer',
        'nguoiTaiLenId'     => 'integer',
        'kichThuoc'         => 'integer',
    ];

    /* ── Relationships ──────────────────────────────────────────────────────── */

    public function nguoiTaiLen()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiTaiLenId', 'taiKhoanId');
    }

    /**
     * Các lần chia sẻ tài liệu này vào lớp học.
     */
    public function lopHocTaiLieus()
    {
        return $this->hasMany(LopHocTaiLieu::class, 'giaoVienTaiLieuId', 'giaoVienTaiLieuId');
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

    /* ── Accessors ──────────────────────────────────────────────────────────── */

    public function getNhomLabelAttribute(): string
    {
        return self::nhomOptions()[$this->nhomTaiLieu] ?? 'Khác';
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

    public function getMimeIconAttribute(): string
    {
        $map = [
            'pdf'  => 'fa-file-pdf text-danger',
            'doc'  => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls'  => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'ppt'  => 'fa-file-powerpoint text-warning',
            'pptx' => 'fa-file-powerpoint text-warning',
            'zip'  => 'fa-file-archive text-secondary',
            'mp3'  => 'fa-file-audio text-info',
            'mp4'  => 'fa-file-video text-purple',
            'png'  => 'fa-file-image text-success',
            'jpg'  => 'fa-file-image text-success',
            'jpeg' => 'fa-file-image text-success',
        ];

        $ext = strtolower(pathinfo($this->tenGoc, PATHINFO_EXTENSION));
        return $map[$ext] ?? 'fa-file text-muted';
    }

    /* ── Scopes ─────────────────────────────────────────────────────────────── */

    public function scopeOfTeacher($query, int $teacherId)
    {
        return $query->where('nguoiTaiLenId', $teacherId);
    }

    public function scopeByNhom($query, string $nhom)
    {
        return $query->where('nhomTaiLieu', $nhom);
    }
}
