<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NhanSuTaiLieu extends Model
{
    public const LOAI_CV = 'CV';
    public const LOAI_IDENTITY = 'IDENTITY';
    public const LOAI_DEGREE = 'DEGREE';
    public const LOAI_CERTIFICATE = 'CERTIFICATE';
    public const LOAI_CONTRACT = 'CONTRACT';
    public const LOAI_DECISION = 'DECISION';
    public const LOAI_OTHER = 'OTHER';

    public const TRANG_THAI_ACTIVE = 'active';
    public const TRANG_THAI_ARCHIVED = 'archived';

    protected $table = 'nhansu_tai_lieu';
    protected $primaryKey = 'nhanSuTaiLieuId';

    protected $fillable = [
        'taiKhoanId',
        'loaiTaiLieu',
        'tenHienThi',
        'tenGoc',
        'duongDan',
        'disk',
        'mime',
        'kichThuoc',
        'checksum',
        'phienBan',
        'duocTaiLenBoiId',
        'trangThai',
        'ghiChu',
        'archivedAt',
    ];

    protected $casts = [
        'kichThuoc' => 'integer',
        'phienBan' => 'integer',
        'archivedAt' => 'datetime',
    ];

    public static function loaiOptions(): array
    {
        return [
            self::LOAI_CV => 'CV',
            self::LOAI_IDENTITY => 'Giấy tờ tùy thân',
            self::LOAI_DEGREE => 'Bằng cấp',
            self::LOAI_CERTIFICATE => 'Chứng chỉ',
            self::LOAI_CONTRACT => 'Hợp đồng',
            self::LOAI_DECISION => 'Quyết định',
            self::LOAI_OTHER => 'Khác',
        ];
    }

    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nguoiTaiLen(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'duocTaiLenBoiId', 'taiKhoanId');
    }

    public function getKichThuocHienThiAttribute(): string
    {
        if ($this->kichThuoc >= 1_048_576) {
            return number_format($this->kichThuoc / 1_048_576, 1) . ' MB';
        }

        return number_format($this->kichThuoc / 1_024, 1) . ' KB';
    }

    public function getIconClassAttribute(): string
    {
        $mime = strtolower((string) $this->mime);

        if (Str::contains($mime, 'pdf')) {
            return 'fa-file-pdf text-danger';
        }
        if (Str::contains($mime, ['word', 'doc'])) {
            return 'fa-file-word text-primary';
        }
        if (Str::contains($mime, ['sheet', 'excel', 'xls'])) {
            return 'fa-file-excel text-success';
        }
        if (Str::contains($mime, 'image')) {
            return 'fa-file-image text-info';
        }

        return 'fa-file text-secondary';
    }

    public function scopeActive($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_ACTIVE);
    }
}
