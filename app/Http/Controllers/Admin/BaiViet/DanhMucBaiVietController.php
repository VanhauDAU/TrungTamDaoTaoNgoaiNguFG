<?php

namespace App\Http\Controllers\Admin\BaiViet;

use App\Http\Controllers\Controller;
use App\Models\Content\BaiViet;
use App\Models\Content\DanhMucBaiViet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DanhMucBaiVietController extends Controller
{
    /** Danh sách danh mục bài viết */
    public function index(Request $request)
    {
        $query = DanhMucBaiViet::withCount('baiViets');

        // ── Tìm kiếm ──────────────────────────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenDanhMuc', 'like', "%{$search}%")
                    ->orWhere('moTa', 'like', "%{$search}%");
            });
        }

        // ── Lọc trạng thái ────────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ───────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'danhMucId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['danhMucId', 'tenDanhMuc', 'bai_viets_count'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $danhMucs = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ────────────────────────────────────
        $tongSo = DanhMucBaiViet::count();
        $dangHoatDong = DanhMucBaiViet::where('trangThai', 1)->count();
        $tongBaiViet = BaiViet::count();

        return view('admin.danh-muc-bai-viet.index', compact(
            'danhMucs',
            'tongSo',
            'dangHoatDong',
            'tongBaiViet'
        ));
    }

    /** Form thêm danh mục */
    public function create()
    {
        return view('admin.danh-muc-bai-viet.create');
    }

    /** Lưu danh mục mới */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmucbaiviet,tenDanhMuc',
            'moTa' => 'nullable|string|max:1000',
            'trangThai' => 'required|in:0,1',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique' => 'Tên danh mục này đã tồn tại.',
            'tenDanhMuc.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'trangThai.required' => 'Vui lòng chọn trạng thái.',
        ]);

        $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc);

        DanhMucBaiViet::create($data);

        return redirect()->route('admin.danh-muc-bai-viet.index')
            ->with('success', 'Đã thêm danh mục «' . $request->tenDanhMuc . '» thành công.');
    }

    /** Form chỉnh sửa */
    public function edit(int $id)
    {
        $danhMuc = DanhMucBaiViet::findOrFail($id);
        return view('admin.danh-muc-bai-viet.edit', compact('danhMuc'));
    }

    /** Cập nhật danh mục */
    public function update(Request $request, int $id)
    {
        $danhMuc = DanhMucBaiViet::findOrFail($id);

        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmucbaiviet,tenDanhMuc,' . $id . ',danhMucId',
            'moTa' => 'nullable|string|max:1000',
            'trangThai' => 'required|in:0,1',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique' => 'Tên danh mục này đã tồn tại.',
            'trangThai.required' => 'Vui lòng chọn trạng thái.',
        ]);

        if ($danhMuc->tenDanhMuc !== $request->tenDanhMuc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc, $id);
        }

        $danhMuc->update($data);

        return redirect()->route('admin.danh-muc-bai-viet.index')
            ->with('success', 'Đã cập nhật danh mục «' . $danhMuc->tenDanhMuc . '» thành công.');
    }

    /** Xóa danh mục */
    public function destroy(int $id)
    {
        try {
            $danhMuc = DanhMucBaiViet::withCount('baiViets')->findOrFail($id);

            if ($danhMuc->bai_viets_count > 0) {
                return redirect()
                    ->route('admin.danh-muc-bai-viet.index')
                    ->with('error', "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->bai_viets_count} bài viết thuộc danh mục này.");
            }

            $ten = $danhMuc->tenDanhMuc;
            $danhMuc->delete();

            return redirect()
                ->route('admin.danh-muc-bai-viet.index')
                ->with('success', "Đã xóa danh mục «{$ten}» thành công.");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.danh-muc-bai-viet.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /** Tạo slug duy nhất */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        if (empty($slug))
            $slug = 'danh-muc';
        $candidate = $slug;
        $counter = 1;

        while (true) {
            $q = DanhMucBaiViet::where('slug', $candidate);
            if ($excludeId)
                $q->where('danhMucId', '!=', $excludeId);
            if (!$q->exists())
                break;
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
