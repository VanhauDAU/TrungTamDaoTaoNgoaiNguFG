<?php

namespace App\Services\Admin;

use App\Contracts\Admin\DanhMucKhoaHocServiceInterface;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DanhMucKhoaHocService implements DanhMucKhoaHocServiceInterface
{
    public function getList(Request $request): array
    {
        $q = $request->q;

        return [
            'roots' => DanhMucKhoaHoc::with(['childrenRecursive.khoaHocs'])
                ->withCount('khoaHocs')
                ->whereNull('parent_id')
                ->when($q, fn ($query) => $query->where(function ($sq) use ($q) {
                    $sq->where('tenDanhMuc', 'like', "%{$q}%")
                        ->orWhereHas('children', fn ($childQuery) => $childQuery->where('tenDanhMuc', 'like', "%{$q}%"));
                }))
                ->when($request->filled('trangThai'), fn ($query) => $query->where('trangThai', $request->trangThai))
                ->ordered()
                ->get(),
            'tongSo' => DanhMucKhoaHoc::count(),
            'tongCha' => DanhMucKhoaHoc::whereNull('parent_id')->count(),
            'tongCon' => DanhMucKhoaHoc::whereNotNull('parent_id')->count(),
            'tongKhoaHoc' => KhoaHoc::count(),
        ];
    }

    public function getCreateFormData(): array
    {
        return ['flatTree' => DanhMucKhoaHoc::buildFlatTree()];
    }

    public function getEditFormData(string $slug): array
    {
        $danhMuc = DanhMucKhoaHoc::where('slug', $slug)->firstOrFail();

        return [
            'danhMuc' => $danhMuc,
            'flatTree' => DanhMucKhoaHoc::buildFlatTree(excludeId: $danhMuc->danhMucId),
        ];
    }

    public function store(Request $request): DanhMucKhoaHoc
    {
        $data = $this->validateDanhMuc($request);

        $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc);
        $data['maDanhMuc'] = DanhMucKhoaHoc::generateMaDanhMuc($request->tenDanhMuc);
        $data['sort_order'] = DanhMucKhoaHoc::nextSortOrder($data['parent_id'] ?? null);

        return DanhMucKhoaHoc::create($data);
    }

    public function update(Request $request, string $slug): DanhMucKhoaHoc
    {
        $danhMuc = DanhMucKhoaHoc::where('slug', $slug)->firstOrFail();
        $data = $this->validateDanhMuc($request, $danhMuc->danhMucId);

        $this->ensureValidParentSelection($danhMuc, $data['parent_id'] ?? null);

        if ($danhMuc->tenDanhMuc !== $request->tenDanhMuc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc, $danhMuc->danhMucId);
        }

        if (($data['parent_id'] ?? null) != $danhMuc->parent_id) {
            $data['sort_order'] = DanhMucKhoaHoc::nextSortOrder($data['parent_id'] ?? null);
        }

        $danhMuc->update($data);

        return $danhMuc->fresh();
    }

    public function destroy(string $slug): string
    {
        $danhMuc = DanhMucKhoaHoc::with('children')
            ->withCount('khoaHocs')
            ->where('slug', $slug)
            ->firstOrFail();

        if ($danhMuc->khoaHocs_count > 0) {
            throw new \RuntimeException(
                "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->khoaHocs_count} khóa học."
            );
        }

        if ($danhMuc->children->isNotEmpty()) {
            throw new \RuntimeException(
                "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->children->count()} danh mục con. Hãy xóa hoặc chuyển danh mục con trước."
            );
        }

        $ten = $danhMuc->tenDanhMuc;
        $danhMuc->delete();

        return $ten;
    }

    public function reorder(Request $request): array
    {
        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:danhmuckhoahoc,danhMucId',
            'ordered_ids' => 'required|array|min:1',
            'ordered_ids.*' => 'required|integer|exists:danhmuckhoahoc,danhMucId',
        ]);

        $expectedIds = DanhMucKhoaHoc::where('parent_id', $data['parent_id'] ?? null)
            ->ordered()
            ->pluck('danhMucId')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $orderedIds = collect($data['ordered_ids'])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        sort($expectedIds);
        $sortedOrderedIds = $orderedIds;
        sort($sortedOrderedIds);

        if ($expectedIds !== $sortedOrderedIds) {
            return [
                'success' => false,
                'message' => 'Danh sách sắp xếp không hợp lệ hoặc đang bị thiếu danh mục do bộ lọc.',
                'status' => 422,
            ];
        }

        foreach ($orderedIds as $index => $id) {
            DanhMucKhoaHoc::where('danhMucId', $id)->update([
                'sort_order' => $index + 1,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Đã cập nhật thứ tự hiển thị danh mục.',
            'status' => 200,
        ];
    }

    private function validateDanhMuc(Request $request, ?int $excludeId = null): array
    {
        $uniqueRule = 'required|string|max:255|unique:danhmuckhoahoc,tenDanhMuc';
        if ($excludeId !== null) {
            $uniqueRule .= ',' . $excludeId . ',danhMucId';
        }

        return $request->validate([
            'tenDanhMuc' => $uniqueRule,
            'moTa' => 'nullable|string|max:1000',
            'trangThai' => 'required|in:0,1',
            'parent_id' => 'nullable|integer|exists:danhmuckhoahoc,danhMucId',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique' => 'Tên danh mục này đã tồn tại.',
            'parent_id.exists' => 'Danh mục cha không hợp lệ.',
        ]);
    }

    private function ensureValidParentSelection(DanhMucKhoaHoc $danhMuc, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === (int) $danhMuc->danhMucId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_id' => 'Không thể chọn chính nó làm cha.',
            ]);
        }

        $candidate = DanhMucKhoaHoc::with('childrenRecursive')->find($parentId);
        if (! $candidate) {
            return;
        }

        if (in_array($danhMuc->danhMucId, $candidate->allDescendantIds(), true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_id' => 'Không thể chọn danh mục con/cháu của danh mục này làm cha (gây vòng lặp).',
            ]);
        }
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        $candidate = $slug;
        $counter = 1;

        while (true) {
            $query = DanhMucKhoaHoc::where('slug', $candidate);
            if ($excludeId !== null) {
                $query->where('danhMucId', '!=', $excludeId);
            }

            if (! $query->exists()) {
                return $candidate;
            }

            $candidate = $slug . '-' . $counter++;
        }
    }
}
