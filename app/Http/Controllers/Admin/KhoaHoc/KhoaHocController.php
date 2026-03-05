<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Course\KhoaHoc;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\HocPhi;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KhoaHocController extends Controller
{
    /** Danh sách khóa học */
    public function index(Request $request)
    {
        $query = KhoaHoc::with(['danhMuc', 'lopHoc']);

        // ── Tìm kiếm ──────────────────────────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenKhoaHoc', 'like', "%{$search}%")
                  ->orWhere('moTa', 'like', "%{$search}%")
                  ->orWhere('doiTuong', 'like', "%{$search}%");
            });
        }

        // ── Lọc danh mục khóa học ───────────────────────────────────
        if ($request->filled('danhMucId')) {
            $query->where('danhMucId', $request->danhMucId);
        }

        // ── Lọc trạng thái ────────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ───────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'khoaHocId');
        $dir     = $request->get('dir', 'desc');
        if (in_array($orderBy, ['khoaHocId', 'tenKhoaHoc'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $khoaHocs = $query->paginate(12)->withQueryString();

        // ── Thống kê nhanh ────────────────────────────────────
        $tongSo       = KhoaHoc::count();
        $dangHoatDong = KhoaHoc::where('trangThai', 1)->count();
        $tongLopHoc   = LopHoc::count();
        $danhMucs     = DanhMucKhoaHoc::orderBy('tenDanhMuc')->get();

        return view('admin.khoa-hoc.index', compact(
            'khoaHocs',
            'tongSo',
            'dangHoatDong',
            'tongLopHoc',
            'danhMucs'
        ));
    }

    /** Form thêm khóa học mới */
    public function create()
    {
        $flatTree = DanhMucKhoaHoc::buildFlatTree();
        return view('admin.khoa-hoc.create', compact('flatTree'));
    }

    /** Lưu khóa học mới */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenKhoaHoc'    => 'required|string|max:255',
            'danhMucId' => 'required|exists:danhmuckhoahoc,danhMucId',
            'anhKhoaHoc'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'moTa'          => 'nullable|string',
            'doiTuong'      => 'nullable|string|max:255',
            'yeuCauDauVao'  => 'nullable|string',
            'ketQuaDatDuoc' => 'nullable|string',
            'trangThai'     => 'required|in:0,1',
        ], [
            'tenKhoaHoc.required'    => 'Vui lòng nhập tên khóa học.',
            'danhMucId.required' => 'Vui lòng chọn danh mục khóa học.',
            'danhMucId.exists'   => 'Danh mục khóa học không hợp lệ.',
            'anhKhoaHoc.image'       => 'File phải là ảnh.',
            'anhKhoaHoc.max'         => 'Ảnh không được vượt quá 2MB.',
        ]);

        // Upload ảnh
        if ($request->hasFile('anhKhoaHoc')) {
            $data['anhKhoaHoc'] = $request->file('anhKhoaHoc')->store('khoa-hoc', 'public');
        }

        // Tạo slug
        $data['slug'] = $this->generateUniqueSlug($request->tenKhoaHoc);

        KhoaHoc::create($data);

        return redirect()->route('admin.khoa-hoc.index')
            ->with('success', 'Đã thêm khóa học «' . $request->tenKhoaHoc . '» thành công.');
    }

    /** Chi tiết khóa học */
    public function show(string $slug)
    {
        $khoaHoc = KhoaHoc::with([
            'danhMuc',
            'lopHoc.coSo',
            'lopHoc.caHoc',
            'lopHoc.taiKhoan.hoSoNguoiDung',
            'hocPhis',
        ])->where('slug', $slug)->firstOrFail();

        $tongLop        = $khoaHoc->lopHoc->count();
        $lopDangHoc     = $khoaHoc->lopHoc->where('trangThai', 4)->count();
        $lopSapMo       = $khoaHoc->lopHoc->where('trangThai', 0)->count();
        $tongHocVien    = $khoaHoc->lopHoc->sum(fn($l) => $l->dangKyLopHocs()->count() ?? 0);
        $hocPhis        = $khoaHoc->hocPhis->sortBy('soBuoi');

        return view('admin.khoa-hoc.show', compact(
            'khoaHoc',
            'tongLop',
            'lopDangHoc',
            'lopSapMo',
            'tongHocVien',
            'hocPhis'
        ));
    }

    /** Form chỉnh sửa khóa học */
    public function edit(string $slug)
    {
        $khoaHoc      = KhoaHoc::where('slug', $slug)->firstOrFail();
        $id = $khoaHoc->khoaHocId;
        $flatTree = DanhMucKhoaHoc::buildFlatTree();
        return view('admin.khoa-hoc.edit', compact('khoaHoc', 'flatTree'));
    }

    /** Cập nhật khóa học */
    public function update(Request $request, string $slug)
    {
        $khoaHoc = KhoaHoc::where('slug', $slug)->firstOrFail();
        $id = $khoaHoc->khoaHocId;

        $data = $request->validate([
            'tenKhoaHoc'    => 'required|string|max:255',
            'danhMucId' => 'required|exists:danhmuckhoahoc,danhMucId',
            'anhKhoaHoc'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'moTa'          => 'nullable|string',
            'doiTuong'      => 'nullable|string|max:255',
            'yeuCauDauVao'  => 'nullable|string',
            'ketQuaDatDuoc' => 'nullable|string',
            'trangThai'     => 'required|in:0,1',
        ], [
            'tenKhoaHoc.required'    => 'Vui lòng nhập tên khóa học.',
            'danhMucId.required' => 'Vui lòng chọn danh mục khóa học.',
        ]);

        // Upload ảnh mới (xóa ảnh cũ)
        if ($request->hasFile('anhKhoaHoc')) {
            if ($khoaHoc->anhKhoaHoc && Storage::disk('public')->exists($khoaHoc->anhKhoaHoc)) {
                Storage::disk('public')->delete($khoaHoc->anhKhoaHoc);
            }
            $data['anhKhoaHoc'] = $request->file('anhKhoaHoc')->store('khoa-hoc', 'public');
        } else {
            unset($data['anhKhoaHoc']);
        }

        // Cập nhật slug nếu tên đổi
        if ($request->tenKhoaHoc !== $khoaHoc->tenKhoaHoc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenKhoaHoc, $id);
        }

        $khoaHoc->update($data);

        return redirect()->route('admin.khoa-hoc.show', $slug)
            ->with('success', 'Đã cập nhật khóa học «' . $khoaHoc->tenKhoaHoc . '» thành công.');
    }

    /** Xóa mềm (lưu trữ) khóa học */
    public function destroy(string $slug)
    {
        try {
            $khoaHoc = KhoaHoc::where('slug', $slug)->firstOrFail();

            // Kiểm tra lớp học đang hoạt động (trạng thái: 0=sắp mở, 1=đang mở, 4=đang học)
            $lopDangHoatDong = $khoaHoc->lopHoc()
                ->whereIn('trangThai', [0, 1, 4])
                ->count();

            if ($lopDangHoatDong > 0) {
                return redirect()
                    ->route('admin.khoa-hoc.index')
                    ->with('error', "Không thể lưu trữ «{$khoaHoc->tenKhoaHoc}» — còn {$lopDangHoatDong} lớp học đang hoạt động. Hãy đóng hoặc hủy các lớp trước.");
            }

            $ten = $khoaHoc->tenKhoaHoc;
            $khoaHoc->delete(); // soft delete – chỉ set deleted_at

            return redirect()
                ->route('admin.khoa-hoc.index')
                ->with('success', "Đã lưu trữ khóa học «{$ten}». Dữ liệu vẫn được giữ nguyên.");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.khoa-hoc.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /** Khôi phục khóa học đã lưu trữ */
    public function restore(string $slug)
    {
        try {
            $khoaHoc = KhoaHoc::withTrashed()->where('slug', $slug)->firstOrFail();
            $khoaHoc->restore();
            return redirect()
                ->route('admin.khoa-hoc.show', $slug)
                ->with('success', "Đã khôi phục khóa học «{$khoaHoc->tenKhoaHoc}» thành công.");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.khoa-hoc.index')
                ->with('error', 'Lỗi khôi phục: ' . $e->getMessage());
        }
    }

    /** Tạo slug duy nhất */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug      = Str::slug($name, '-');
        $candidate = $slug;
        $counter   = 1;

        while (true) {
            $q = KhoaHoc::where('slug', $candidate);
            if ($excludeId) $q->where('khoaHocId', '!=', $excludeId);
            if (!$q->exists()) break;
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
