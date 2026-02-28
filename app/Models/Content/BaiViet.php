<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BaiViet extends Model
{
    use SoftDeletes;

    protected $table = 'baiviet';
    protected $primaryKey = 'baiVietId';
    protected $fillable = [
        'tieuDe',
        'slug',
        'tomTat',
        'noiDung',
        'anhDaiDien',
        'taiKhoanId',
        'luotXem',
        'trangThai',
    ];

    protected $casts = [
        'trangThai' => 'integer',
        'luotXem' => 'integer',
    ];

    /* ── Scopes ─────────────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('trangThai', 1);
    }

    public function scopeDraft($query)
    {
        return $query->where('trangThai', 0);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (!$keyword)
            return $query;
        return $query->where(function ($q) use ($keyword) {
            $q->where('tieuDe', 'like', "%{$keyword}%")
                ->orWhere('tomTat', 'like', "%{$keyword}%");
        });
    }

    /* ── Accessors ──────────────────────────────────────────── */

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y H:i')
            : '—';
    }

    public function getTomTatNganAttribute(): string
    {
        return Str::limit(strip_tags($this->tomTat ?? ''), 100);
    }

    /* ── Relationships ──────────────────────────────────────── */

    public function danhMucs()
    {
        return $this->belongsToMany(
            DanhMucBaiViet::class,
            'BaiViet_DanhMuc',
            'baiVietId',
            'danhMucId'
        );
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'BaiViet_Tag',
            'baiVietId',
            'tagId'
        );
    }

    public function taiKhoan()
    {
        return $this->belongsTo(\App\Models\Auth\TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
