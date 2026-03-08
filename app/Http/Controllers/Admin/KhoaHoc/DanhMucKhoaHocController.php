<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DanhMucKhoaHocController extends Controller
{
    // ── INDEX (tree view) ──────────────────────────────────────────
    public function index(Request $request)
    {
        $q = $request->q;

        // Lấy danh mục gốc kèm con, withCount khóa học
        $roots = DanhMucKhoaHoc::with(['childrenRecursive.khoaHocs'])
            ->withCount('khoaHocs')
            ->whereNull('parent_id')
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('tenDanhMuc', 'like', "%{$q}%")
                   ->orWhereHas('children', fn($c) => $c->where('tenDanhMuc', 'like', "%{$q}%"));
            }))
            ->when($request->filled('trangThai'), fn($query) =>
                $query->where('trangThai', $request->trangThai))
            ->ordered()
            ->get();

        $tongSo       = DanhMucKhoaHoc::count();
        $tongCha      = DanhMucKhoaHoc::whereNull('parent_id')->count();
        $tongCon      = DanhMucKhoaHoc::whereNotNull('parent_id')->count();
        $tongKhoaHoc  = KhoaHoc::count();

        return view('admin.danh-muc-khoa-hoc.index', compact(
            'roots', 'tongSo', 'tongCha', 'tongCon', 'tongKhoaHoc'
        ));
    }

    // ── CREATE ─────────────────────────────────────────────────────
    public function create()
    {
        $flatTree = DanhMucKhoaHoc::buildFlatTree();
        return view('admin.danh-muc-khoa-hoc.create', compact('flatTree'));
    }

    // ── STORE ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmuckhoahoc,tenDanhMuc',
            'moTa'       => 'nullable|string|max:1000',
            'trangThai'  => 'required|in:0,1',
            'parent_id'  => 'nullable|integer|exists:danhmuckhoahoc,danhMucId',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique'   => 'Tên danh mục này đã tồn tại.',
            'parent_id.exists'    => 'Danh mục cha không hợp lệ.',
        ]);

        // Không cho tạo vòng lặp: không thể chọn chính mình làm cha
        // (không giới hạn cấp — hỗ trợ cây sâu tùy ý)

        $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc);
        $data['maDanhMuc'] = DanhMucKhoaHoc::generateMaDanhMuc($request->tenDanhMuc);
        $data['sort_order'] = DanhMucKhoaHoc::nextSortOrder($data['parent_id'] ?? null);
        DanhMucKhoaHoc::create($data);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã thêm danh mục «' . $request->tenDanhMuc . '» thành công.');
    }

    // ── EDIT ───────────────────────────────────────────────────────
    public function edit(string $slug)
    {
        $danhMuc  = DanhMucKhoaHoc::where('slug', $slug)->firstOrFail();
        $id = $danhMuc->danhMucId;
        $flatTree = DanhMucKhoaHoc::buildFlatTree(excludeId: $id);
        return view('admin.danh-muc-khoa-hoc.edit', compact('danhMuc', 'flatTree'));
    }

    // ── UPDATE ─────────────────────────────────────────────────────
    public function update(Request $request, string $slug)
    {
        $danhMuc = DanhMucKhoaHoc::where('slug', $slug)->firstOrFail();
        $id = $danhMuc->danhMucId;

        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmuckhoahoc,tenDanhMuc,' . $id . ',danhMucId',
            'moTa'       => 'nullable|string|max:1000',
            'trangThai'  => 'required|in:0,1',
            'parent_id'  => 'nullable|integer|exists:danhmuckhoahoc,danhMucId',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique'   => 'Tên danh mục này đã tồn tại.',
        ]);

        // Không cho đặt chính nó làm cha
        if (!empty($data['parent_id']) && $data['parent_id'] == $id) {
            return back()->withInput()->withErrors(['parent_id' => 'Không thể chọn chính nó làm cha.']);
        }

        // Không cho chọn descendant làm cha (gây vòng lặp)
        if (!empty($data['parent_id'])) {
            $candidate = DanhMucKhoaHoc::with('childrenRecursive')->find($data['parent_id']);
            if ($candidate) {
                $descendantIds = $candidate->allDescendantIds();
                if (in_array($id, $descendantIds)) {
                    return back()->withInput()
                        ->withErrors(['parent_id' => 'Không thể chọn danh mục con/cháu của danh mục này làm cha (gây vòng lặp).']);
                }
            }
        }

        if ($danhMuc->tenDanhMuc !== $request->tenDanhMuc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc, $id);
        }

        if (($data['parent_id'] ?? null) != $danhMuc->parent_id) {
            $data['sort_order'] = DanhMucKhoaHoc::nextSortOrder($data['parent_id'] ?? null);
        }

        $danhMuc->update($data);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã cập nhật danh mục «' . $danhMuc->tenDanhMuc . '» thành công.');
    }

    // ── DESTROY ────────────────────────────────────────────────────
    public function destroy(string $slug)
    {
        try {
            $danhMuc = DanhMucKhoaHoc::with('children')->withCount('khoaHocs')->where('slug', $slug)->firstOrFail();
            $id = $danhMuc->danhMucId;

            if ($danhMuc->khoaHocs_count > 0) {
                return redirect()->route('admin.danh-muc-khoa-hoc.index')
                    ->with('error', "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->khoaHocs_count} khóa học.");
            }
            if ($danhMuc->children->isNotEmpty()) {
                return redirect()->route('admin.danh-muc-khoa-hoc.index')
                    ->with('error', "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->children->count()} danh mục con. Hãy xóa hoặc chuyển danh mục con trước.");
            }

            $ten = $danhMuc->tenDanhMuc;
            $danhMuc->delete();

            return redirect()->route('admin.danh-muc-khoa-hoc.index')
                ->with('success', "Đã xóa danh mục «{$ten}» thành công.");

        } catch (\Exception $e) {
            return redirect()->route('admin.danh-muc-khoa-hoc.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:danhmuckhoahoc,danhMucId',
            'ordered_ids' => 'required|array|min:1',
            'ordered_ids.*' => 'required|integer|exists:danhmuckhoahoc,danhMucId',
        ]);

        $expectedIds = DanhMucKhoaHoc::where('parent_id', $data['parent_id'] ?? null)
            ->ordered()
            ->pluck('danhMucId')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $orderedIds = collect($data['ordered_ids'])
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        sort($expectedIds);
        $sortedOrderedIds = $orderedIds;
        sort($sortedOrderedIds);

        if ($expectedIds !== $sortedOrderedIds) {
            return response()->json([
                'success' => false,
                'message' => 'Danh sách sắp xếp không hợp lệ hoặc đang bị thiếu danh mục do bộ lọc.',
            ], 422);
        }

        foreach ($orderedIds as $index => $id) {
            DanhMucKhoaHoc::where('danhMucId', $id)->update([
                'sort_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thứ tự hiển thị danh mục.',
        ]);
    }

    // ── HELPERS ────────────────────────────────────────────────────
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = $candidate = Str::slug($name, '-');
        $i    = 1;
        while (true) {
            $q = DanhMucKhoaHoc::where('slug', $candidate);
            if ($excludeId) $q->where('danhMucId', '!=', $excludeId);
            if (!$q->exists()) break;
            $candidate = $slug . '-' . $i++;
        }
        return $candidate;
    }
}
