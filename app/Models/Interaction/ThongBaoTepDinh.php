<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThongBaoTepDinh extends Model
{
    protected $table      = 'thongbao_tepdinh';
    protected $primaryKey = 'tepDinhId';

    protected $fillable = [
        'thongBaoId',
        'tenFile',
        'tenFileLuu',
        'duongDan',
        'loaiFile',
        'kichThuoc',
    ];

    // ── Relationships ────────────────────────────────────────

    public function thongBao(): BelongsTo
    {
        return $this->belongsTo(ThongBao::class, 'thongBaoId', 'thongBaoId');
    }

    // ── Accessors ────────────────────────────────────────────

    /** URL công khai để tải file */
    public function getUrlAttribute(): string
    {
        return route('home.thong-bao.attachments.download', $this->tepDinhId);
    }

    /** Kích thước dạng đọc được (KB / MB) */
    public function getKichThuocHienThiAttribute(): string
    {
        $bytes = $this->kichThuoc;
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1) . ' MB';
        }
        return number_format($bytes / 1_024, 1) . ' KB';
    }

    /** Icon Font Awesome theo loại MIME */
    public function getIconClassAttribute(): string
    {
        $mime = strtolower($this->loaiFile ?? '');

        if (Str::contains($mime, 'pdf'))  return 'fa-file-pdf text-danger';
        if (Str::contains($mime, 'word') || Str::contains($mime, 'doc'))
                                           return 'fa-file-word text-primary';
        if (Str::contains($mime, 'sheet') || Str::contains($mime, 'excel') || Str::contains($mime, 'xls'))
                                           return 'fa-file-excel text-success';
        if (Str::contains($mime, 'presentation') || Str::contains($mime, 'powerpoint'))
                                           return 'fa-file-powerpoint text-warning';
        if (Str::contains($mime, 'image')) return 'fa-file-image text-info';
        if (Str::contains($mime, 'zip') || Str::contains($mime, 'rar') || Str::contains($mime, 'archive'))
                                           return 'fa-file-archive text-secondary';
        if (Str::contains($mime, 'text')) return 'fa-file-alt text-muted';

        return 'fa-file text-secondary';
    }
}
