<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DanhMucKhoaHoc extends Model
{
    protected $table      = 'danhmuckhoahoc';
    protected $primaryKey = 'danhMucId';

    protected $fillable = [
        'maDanhMuc',
        'tenDanhMuc',
        'slug',
        'moTa',
        'trangThai',
        'parent_id',
        'sort_order',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function parent()
    {
        return $this->belongsTo(DanhMucKhoaHoc::class, 'parent_id', 'danhMucId');
    }

    public function children()
    {
        return $this->hasMany(DanhMucKhoaHoc::class, 'parent_id', 'danhMucId')
                    ->orderBy('sort_order')
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('tenDanhMuc');
    }

    // ── Generator ──────────────────────────────────────────────────
    public static function generateMaDanhMuc($tenDanhMuc)
    {
        $words = explode(' ', $tenDanhMuc);
        $abbr = '';
        foreach ($words as $word) {
            $abbr .= mb_substr($word, 0, 1);
        }
        $abbr = strtoupper(\Illuminate\Support\Str::ascii($abbr));
        $abbr = preg_replace('/[^A-Z]/', '', $abbr);
        if (empty($abbr)) $abbr = 'DM';

        $count = self::where('maDanhMuc', 'LIKE', $abbr . '%')->count();
        if ($count == 0) {
            return $abbr;
        }

        $so = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        return $abbr . $so; 
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
                    ->ordered()
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

    public static function nextSortOrder(?int $parentId = null): int
    {
        return (int) static::where('parent_id', $parentId)->max('sort_order') + 1;
    }
}
