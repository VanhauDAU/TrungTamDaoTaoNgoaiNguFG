<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class DanhMucBaiViet extends Model
{
    protected $table = 'danhmucbaiviet';
    protected $primaryKey = 'danhMucId';
    protected $fillable = [
        'danhMucId',
        'tenDanhMuc',
        'slug',
        'moTa',
        'trangThai',
    ];

    protected $casts = [
        'trangThai' => 'integer',
    ];

    /* ── Scopes ─────────────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('trangThai', 1);
    }

    /* ── Relationships ──────────────────────────────────────── */

    public function baiViets()
    {
        return $this->belongsToMany(
            BaiViet::class,
            'BaiViet_DanhMuc',
            'danhMucId',
            'baiVietId'
        );
    }
}
