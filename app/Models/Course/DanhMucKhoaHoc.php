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

    /** Đệ quy: con của con của con... (tất cả cấp) */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
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
     * Danh sách phẳng có depth (cho select dropdown).
     * Hỗ trợ cây nhiều cấp, dựng đệ quy.
     */
    public static function buildFlatTree(?int $excludeId = null): Collection
    {
        $all = static::with('childrenRecursive')
                    ->whereNull('parent_id')
                    ->orderBy('tenDanhMuc')
                    ->get();

        $result = collect();
        foreach ($all as $root) {
            static::flattenNode($root, 0, $excludeId, $result);
        }
        return $result;
    }

    private static function flattenNode(self $node, int $depth, ?int $excludeId, Collection &$result): void
    {
        if ($excludeId && $node->danhMucId === $excludeId) return;
        $result->push(['node' => $node, 'depth' => $depth]);
        foreach ($node->childrenRecursive as $child) {
            static::flattenNode($child, $depth + 1, $excludeId, $result);
        }
    }

    /**
     * Tất cả descendant IDs (cây nhiều cấp) để filter khóa học.
     */
    public function allDescendantIds(): array
    {
        $ids = [$this->danhMucId];
        $this->loadMissing('childrenRecursive');
        foreach ($this->childrenRecursive as $child) {
            foreach ($child->allDescendantIds() as $id) {
                $ids[] = $id;
            }
        }
        return array_unique($ids);
    }
}
