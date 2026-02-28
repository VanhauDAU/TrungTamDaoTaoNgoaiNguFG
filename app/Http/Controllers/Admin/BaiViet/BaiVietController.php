<?php

namespace App\Http\Controllers\Admin\BaiViet;

use App\Http\Controllers\Controller;
use App\Models\Content\BaiViet;
use App\Models\Content\DanhMucBaiViet;
use App\Models\Content\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BaiVietController extends Controller
{
    /** Danh sách bài viết */
    public function index(Request $request)
    {
        $query = BaiViet::with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung']);

        // ── Tìm kiếm ──────────────────────────────────────────
        if ($search = $request->q) {
            $query->search($search);
        }

        // ── Lọc danh mục ──────────────────────────────────────
        if ($request->filled('danhMucId')) {
            $query->whereHas('danhMucs', function ($q) use ($request) {
                $q->where('danhmucbaiviet.danhMucId', $request->danhMucId);
            });
        }

        // ── Lọc tag ───────────────────────────────────────────
        if ($request->filled('tagId')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.tagId', $request->tagId);
            });
        }

        // ── Lọc trạng thái ────────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ───────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'baiVietId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['baiVietId', 'tieuDe', 'luotXem', 'created_at'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $baiViets = $query->paginate(12)->withQueryString();

        // ── Thống kê nhanh ────────────────────────────────────
        $tongSo = BaiViet::count();
        $daXuatBan = BaiViet::active()->count();
        $banNhap = BaiViet::draft()->count();
        $tongLuotXem = BaiViet::sum('luotXem');
        $danhMucs = DanhMucBaiViet::orderBy('tenDanhMuc')->get();
        $tagList = Tag::orderBy('tenTag')->get();

        return view('admin.bai-viet.index', compact(
            'baiViets',
            'tongSo',
            'daXuatBan',
            'banNhap',
            'tongLuotXem',
            'danhMucs',
            'tagList'
        ));
    }

    /** Form thêm bài viết */
    public function create()
    {
        $danhMucs = DanhMucBaiViet::active()->orderBy('tenDanhMuc')->get();
        $tags = Tag::orderBy('tenTag')->get();
        return view('admin.bai-viet.create', compact('danhMucs', 'tags'));
    }

    /** Lưu bài viết mới */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tieuDe' => 'required|string|max:255',
            'tomTat' => 'nullable|string|max:500',
            'noiDung' => 'required|string',
            'anhDaiDien' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trangThai' => 'required|in:0,1',
            'danhMucIds' => 'nullable|array',
            'danhMucIds.*' => 'exists:danhmucbaiviet,danhMucId',
            'tagNames' => 'nullable|string',
        ], [
            'tieuDe.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'noiDung.required' => 'Vui lòng nhập nội dung bài viết.',
            'anhDaiDien.image' => 'File phải là ảnh.',
            'anhDaiDien.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        // Upload ảnh đại diện
        if ($request->hasFile('anhDaiDien')) {
            $data['anhDaiDien'] = $request->file('anhDaiDien')->store('bai-viet', 'public');
        }

        // Tạo slug
        $data['slug'] = $this->generateUniqueSlug($request->tieuDe);

        // Tài khoản đăng nhập
        $data['taiKhoanId'] = Auth::id();
        $data['luotXem'] = 0;

        // Loại bỏ fields không thuộc bảng
        $baiViet = BaiViet::create(collect($data)->except(['danhMucIds', 'tagNames'])->toArray());

        // Sync danh mục
        if ($request->filled('danhMucIds')) {
            $baiViet->danhMucs()->sync($request->danhMucIds);
        }

        // Sync tags
        $this->syncTags($baiViet, $request->input('tagNames', ''));

        return redirect()->route('admin.bai-viet.index')
            ->with('success', 'Đã thêm bài viết «' . $request->tieuDe . '» thành công.');
    }

    /** Chi tiết bài viết */
    public function show(int $id)
    {
        $baiViet = BaiViet::with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung'])
            ->findOrFail($id);

        return view('admin.bai-viet.show', compact('baiViet'));
    }

    /** Form chỉnh sửa */
    public function edit(int $id)
    {
        $baiViet = BaiViet::with(['danhMucs', 'tags'])->findOrFail($id);
        $danhMucs = DanhMucBaiViet::active()->orderBy('tenDanhMuc')->get();
        $tags = Tag::orderBy('tenTag')->get();

        return view('admin.bai-viet.edit', compact('baiViet', 'danhMucs', 'tags'));
    }

    /** Cập nhật bài viết */
    public function update(Request $request, int $id)
    {
        $baiViet = BaiViet::findOrFail($id);

        $data = $request->validate([
            'tieuDe' => 'required|string|max:255',
            'tomTat' => 'nullable|string|max:500',
            'noiDung' => 'required|string',
            'anhDaiDien' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trangThai' => 'required|in:0,1',
            'danhMucIds' => 'nullable|array',
            'danhMucIds.*' => 'exists:danhmucbaiviet,danhMucId',
            'tagNames' => 'nullable|string',
        ], [
            'tieuDe.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'noiDung.required' => 'Vui lòng nhập nội dung bài viết.',
        ]);

        // Upload ảnh mới
        if ($request->hasFile('anhDaiDien')) {
            if ($baiViet->anhDaiDien && Storage::disk('public')->exists($baiViet->anhDaiDien)) {
                Storage::disk('public')->delete($baiViet->anhDaiDien);
            }
            $data['anhDaiDien'] = $request->file('anhDaiDien')->store('bai-viet', 'public');
        } else {
            unset($data['anhDaiDien']);
        }

        // Cập nhật slug nếu tiêu đề đổi
        if ($request->tieuDe !== $baiViet->tieuDe) {
            $data['slug'] = $this->generateUniqueSlug($request->tieuDe, $id);
        }

        $baiViet->update(collect($data)->except(['danhMucIds', 'tagNames'])->toArray());

        // Sync danh mục
        $baiViet->danhMucs()->sync($request->input('danhMucIds', []));

        // Sync tags
        $this->syncTags($baiViet, $request->input('tagNames', ''));

        return redirect()->route('admin.bai-viet.show', $id)
            ->with('success', 'Đã cập nhật bài viết «' . $baiViet->tieuDe . '» thành công.');
    }

    /** Xóa bài viết */
    public function destroy(int $id)
    {
        try {
            $baiViet = BaiViet::findOrFail($id);
            $ten = $baiViet->tieuDe;

            // Xóa ảnh
            if ($baiViet->anhDaiDien && Storage::disk('public')->exists($baiViet->anhDaiDien)) {
                Storage::disk('public')->delete($baiViet->anhDaiDien);
            }

            // Detach relationships
            $baiViet->danhMucs()->detach();
            $baiViet->tags()->detach();
            $baiViet->delete();

            return redirect()->route('admin.bai-viet.index')
                ->with('success', "Đã xóa bài viết «{$ten}» thành công.");
        } catch (\Exception $e) {
            return redirect()->route('admin.bai-viet.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /** Toggle trạng thái xuất bản (AJAX) */
    public function toggleStatus(int $id)
    {
        $baiViet = BaiViet::findOrFail($id);
        $baiViet->trangThai = $baiViet->trangThai ? 0 : 1;
        $baiViet->save();

        return response()->json([
            'success' => true,
            'trangThai' => $baiViet->trangThai,
            'message' => $baiViet->trangThai
                ? "Đã xuất bản «{$baiViet->tieuDe}»."
                : "Đã chuyển «{$baiViet->tieuDe}» sang bản nháp.",
        ]);
    }

    /** Upload ảnh cho TinyMCE editor */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        ]);

        $path = $request->file('file')->store('bai-viet/content', 'public');

        return response()->json([
            'location' => asset('storage/' . $path),
        ]);
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    /** Sync tags từ chuỗi comma-separated */
    private function syncTags(BaiViet $baiViet, string $tagNames): void
    {
        if (empty(trim($tagNames))) {
            $baiViet->tags()->detach();
            return;
        }

        $names = array_unique(array_filter(array_map('trim', explode(',', $tagNames))));
        $tagIds = [];

        foreach ($names as $name) {
            $tag = Tag::firstOrCreate(
                ['tenTag' => $name],
                ['slug' => Str::slug($name)]
            );
            $tagIds[] = $tag->tagId;
        }

        $baiViet->tags()->sync($tagIds);
    }

    /** Tạo slug duy nhất */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        if (empty($slug))
            $slug = 'bai-viet';
        $candidate = $slug;
        $counter = 1;

        while (true) {
            $q = BaiViet::where('slug', $candidate);
            if ($excludeId)
                $q->where('baiVietId', '!=', $excludeId);
            if (!$q->exists())
                break;
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
