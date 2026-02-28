<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DanhMucKhoaHocController extends Controller
{
    /** Danh sách danh mục khóa học */
    public function index(Request $request)
    {
        $query = DanhMucKhoaHoc::withCount('khoaHocs');

        // ── Tìm kiếm ──────────────────────────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenDanhMuc', 'like', "%{$search}%")
                  ->orWhere('moTa',      'like', "%{$search}%");
            });
        }

        // ── Lọc trạng thái ────────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ───────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'danhMucId');
        $dir     = $request->get('dir', 'asc');
        if (in_array($orderBy, ['danhMucId', 'tenDanhMuc', 'khoaHocs_count'])) {
            $query->orderBy($orderBy, $dir === 'desc' ? 'desc' : 'asc');
        }

        $danhMucs = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ────────────────────────────────────
        $tongSo       = DanhMucKhoaHoc::count();
        $dangHoatDong = DanhMucKhoaHoc::where('trangThai', 1)->count();
        $tongKhoaHoc  = KhoaHoc::count();

        return view('admin.danh-muc-khoa-hoc.index', compact(
            'danhMucs',
            'tongSo',
            'dangHoatDong',
            'tongKhoaHoc'
        ));
    }

    /** Form thêm danh mục mới */
    public function create()
    {
        return view('admin.danh-muc-khoa-hoc.create');
    }

    /** Lưu danh mục mới */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmuckhoahoc,tenDanhMuc',
            'moTa'       => 'nullable|string|max:1000',
            'trangThai'  => 'required|in:0,1',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique'   => 'Tên danh mục này đã tồn tại.',
            'tenDanhMuc.max'      => 'Tên danh mục không được vượt quá 255 ký tự.',
            'trangThai.required'  => 'Vui lòng chọn trạng thái.',
        ]);

        $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc);

        DanhMucKhoaHoc::create($data);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã thêm danh mục «' . $request->tenDanhMuc . '» thành công.');
    }

    /** Form chỉnh sửa danh mục */
    public function edit(int $id)
    {
        $danhMuc = DanhMucKhoaHoc::findOrFail($id);
        return view('admin.danh-muc-khoa-hoc.edit', compact('danhMuc'));
    }

    /** Cập nhật danh mục */
    public function update(Request $request, int $id)
    {
        $danhMuc = DanhMucKhoaHoc::findOrFail($id);

        $data = $request->validate([
            'tenDanhMuc' => 'required|string|max:255|unique:danhmuckhoahoc,tenDanhMuc,' . $id . ',danhMucId',
            'moTa'       => 'nullable|string|max:1000',
            'trangThai'  => 'required|in:0,1',
        ], [
            'tenDanhMuc.required' => 'Vui lòng nhập tên danh mục.',
            'tenDanhMuc.unique'   => 'Tên danh mục này đã tồn tại.',
            'trangThai.required'  => 'Vui lòng chọn trạng thái.',
        ]);

        if ($danhMuc->tenDanhMuc !== $request->tenDanhMuc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenDanhMuc, $id);
        }

        $danhMuc->update($data);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã cập nhật danh mục «' . $danhMuc->tenDanhMuc . '» thành công.');
    }

    /** Xóa danh mục */
    public function destroy(int $id)
    {
        try {
            $danhMuc = DanhMucKhoaHoc::withCount('khoaHocs')->findOrFail($id);

            if ($danhMuc->khoaHocs_count > 0) {
                return redirect()
                    ->route('admin.danh-muc-khoa-hoc.index')
                    ->with('error', "Không thể xóa «{$danhMuc->tenDanhMuc}» — còn {$danhMuc->khoaHocs_count} khóa học đang thuộc danh mục này. Hãy chuyển các khóa học sang danh mục khác trước.");
            }

            $ten = $danhMuc->tenDanhMuc;
            $danhMuc->delete();

            return redirect()
                ->route('admin.danh-muc-khoa-hoc.index')
                ->with('success', "Đã xóa danh mục «{$ten}» thành công.");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.danh-muc-khoa-hoc.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /** Tạo slug duy nhất */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug      = Str::slug($name, '-');
        $candidate = $slug;
        $counter   = 1;

        while (true) {
            $q = DanhMucKhoaHoc::where('slug', $candidate);
            if ($excludeId) $q->where('danhMucId', '!=', $excludeId);
            if (!$q->exists()) break;
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
