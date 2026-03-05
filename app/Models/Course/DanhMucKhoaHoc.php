<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DanhMucKhoaHoc extends Model
{
    protected $table      = 'danhmuckhoahoc';
    protected $primaryKey = 'danhMucId';

    protected $fillable = [
        'tenDanhMuc',
        'slug',
        'moTa',
        'trangThai',
        'parent_id',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function parent()
    {
        return $this->belongsTo(DanhMucKhoaHoc::class, 'parent_id', 'danhMucId');
    }

    public function children()
    {
        return $this->hasMany(DanhMucKhoaHoc::class, 'parent_id', 'danhMucId')
                    ->orderBy('tenDanhMuc');
    }

    public function khoaHocs()
    {
        return $this->hasMany(KhoaHoc::class, 'danhMucId', 'danhMucId');
    }

    // ── Scopes ─────────────────────────────────────────────────────
    /** Chỉ lấy danh mục gốc (không có cha) */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // ── Helpers ────────────────────────────────────────────────────
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /**
     * Xây danh sách phẳng có depth (cho select dropdown).
     * Trả về Collection: [danhMucId, tenDanhMuc, depth]
     * excludeId: loại trừ node (không cho chọn chính nó / các con của nó làm cha)
     */
    public static function buildFlatTree(?int $excludeId = null): Collection
    {
        $all    = static::with('children')->whereNull('parent_id')->orderBy('tenDanhMuc')->get();
        $result = collect();

        foreach ($all as $root) {
            if ($excludeId && $root->danhMucId === $excludeId) continue;
            $result->push(['node' => $root, 'depth' => 0]);
            foreach ($root->children as $child) {
                if ($excludeId && $child->danhMucId === $excludeId) continue;
                $result->push(['node' => $child, 'depth' => 1]);
            }
        }

        return $result;
    }

    /**
     * Lấy tất cả danhMucId trong nhóm (cha + các con) để filter khóa học.
     */
    public function allDescendantIds(): array
    {
        $ids = [$this->danhMucId];
        foreach ($this->children as $child) {
            $ids[] = $child->danhMucId;
        }
        return $ids;
    }
}
