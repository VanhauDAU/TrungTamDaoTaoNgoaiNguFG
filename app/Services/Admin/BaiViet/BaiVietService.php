<?php

namespace App\Services\Admin\BaiViet;

use App\Contracts\Admin\BaiViet\BaiVietServiceInterface;
use App\Models\Content\BaiViet;
use App\Models\Content\DanhMucBaiViet;
use App\Models\Content\Tag;
use App\Services\Client\PublicContentCacheService;
use App\Services\Support\Uploads\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BaiVietService implements BaiVietServiceInterface
{
    public function __construct(
        protected ImageUploadService $imageUploadService,
        protected PublicContentCacheService $publicContentCache
    ) {
    }

    public function getList(Request $request): array
    {
        $query = BaiViet::with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung']);
        if ($search = $request->q)
            $query->search($search);
        if ($request->filled('danhMucId'))
            $query->whereHas('danhMucs', fn($q) => $q->where('danhmucbaiviet.danhMucId', $request->danhMucId));
        if ($request->filled('tagId'))
            $query->whereHas('tags', fn($q) => $q->where('tags.tagId', $request->tagId));
        if ($request->filled('trangThai') && $request->trangThai !== '')
            $query->where('trangThai', $request->trangThai);
        $orderBy = $request->get('orderBy', 'baiVietId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['baiVietId', 'tieuDe', 'luotXem', 'created_at'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }
        return [
            'baiViets' => $query->paginate(12)->withQueryString(),
            'tongSo' => BaiViet::count(),
            'daXuatBan' => BaiViet::active()->count(),
            'banNhap' => BaiViet::draft()->count(),
            'tongLuotXem' => BaiViet::sum('luotXem'),
            'daXoa' => BaiViet::onlyTrashed()->count(),
            'danhMucs' => DanhMucBaiViet::orderBy('tenDanhMuc')->get(),
            'tagList' => Tag::orderBy('tenTag')->get(),
        ];
    }

    public function getCreateFormData(): array
    {
        return [
            'danhMucs' => DanhMucBaiViet::active()->orderBy('tenDanhMuc')->get(),
            'tags' => Tag::orderBy('tenTag')->get(),
        ];
    }

    public function getDetail(int $id): array
    {
        return ['baiViet' => BaiViet::with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung'])->findOrFail($id)];
    }

    public function getEditFormData(int $id): array
    {
        return [
            'baiViet' => BaiViet::with(['danhMucs', 'tags'])->findOrFail($id),
            'danhMucs' => DanhMucBaiViet::active()->orderBy('tenDanhMuc')->get(),
            'tags' => Tag::orderBy('tenTag')->get(),
        ];
    }

    public function store(Request $request): BaiViet
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

        if ($request->hasFile('anhDaiDien')) {
            $data['anhDaiDien'] = $request->file('anhDaiDien')->store('bai-viet', 'public');
        }
        $data['slug'] = $this->generateUniqueSlug($request->tieuDe);
        $data['taiKhoanId'] = Auth::id();
        $data['luotXem'] = 0;

        $baiViet = BaiViet::create(collect($data)->except(['danhMucIds', 'tagNames'])->toArray());
        if ($request->filled('danhMucIds'))
            $baiViet->danhMucs()->sync($request->danhMucIds);
        $this->syncTags($baiViet, $request->input('tagNames', ''));
        $this->bustPublicContentCache();

        return $baiViet;
    }

    public function update(Request $request, int $id): BaiViet
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
        ], ['tieuDe.required' => 'Vui lòng nhập tiêu đề bài viết.', 'noiDung.required' => 'Vui lòng nhập nội dung bài viết.']);

        if ($request->hasFile('anhDaiDien')) {
            if ($baiViet->anhDaiDien && Storage::disk('public')->exists($baiViet->anhDaiDien)) {
                Storage::disk('public')->delete($baiViet->anhDaiDien);
            }
            $data['anhDaiDien'] = $request->file('anhDaiDien')->store('bai-viet', 'public');
        } else {
            unset($data['anhDaiDien']);
        }
        if ($request->tieuDe !== $baiViet->tieuDe)
            $data['slug'] = $this->generateUniqueSlug($request->tieuDe, $id);

        $baiViet->update(collect($data)->except(['danhMucIds', 'tagNames'])->toArray());
        $baiViet->danhMucs()->sync($request->input('danhMucIds', []));
        $this->syncTags($baiViet, $request->input('tagNames', ''));
        $this->bustPublicContentCache();

        return $baiViet;
    }

    public function destroy(int $id): string
    {
        $baiViet = BaiViet::findOrFail($id);
        $ten = $baiViet->tieuDe;
        $baiViet->delete();
        $this->bustPublicContentCache();
        return $ten;
    }

    public function bulkDestroy(Request $request): int
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:baiviet,baiVietId']);
        $count = BaiViet::whereIn('baiVietId', $request->ids)->count();
        BaiViet::whereIn('baiVietId', $request->ids)->delete();
        $this->bustPublicContentCache();
        return $count;
    }

    public function getTrash(Request $request): array
    {
        return [
            'baiViets' => BaiViet::onlyTrashed()
                ->with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung'])
                ->search($request->q)->orderBy('deleted_at', 'desc')
                ->paginate(12)->withQueryString(),
        ];
    }

    public function restore(int $id): BaiViet
    {
        $baiViet = BaiViet::onlyTrashed()->findOrFail($id);
        $baiViet->restore();
        $this->bustPublicContentCache();
        return $baiViet;
    }

    public function bulkRestore(Request $request): int
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $count = BaiViet::onlyTrashed()->whereIn('baiVietId', $request->ids)->count();
        BaiViet::onlyTrashed()->whereIn('baiVietId', $request->ids)->restore();
        $this->bustPublicContentCache();
        return $count;
    }

    public function forceDestroy(int $id): string
    {
        $baiViet = BaiViet::onlyTrashed()->findOrFail($id);
        $ten = $baiViet->tieuDe;
        if ($baiViet->anhDaiDien && Storage::disk('public')->exists($baiViet->anhDaiDien)) {
            Storage::disk('public')->delete($baiViet->anhDaiDien);
        }
        $baiViet->danhMucs()->detach();
        $baiViet->tags()->detach();
        $baiViet->forceDelete();
        $this->bustPublicContentCache();
        return $ten;
    }

    public function toggleStatus(int $id): array
    {
        $baiViet = BaiViet::findOrFail($id);
        $baiViet->trangThai = $baiViet->trangThai ? 0 : 1;
        $baiViet->save();
        $this->bustPublicContentCache();
        return [
            'success' => true,
            'trangThai' => $baiViet->trangThai,
            'message' => $baiViet->trangThai ? "Đã xuất bản «{$baiViet->tieuDe}»." : "Đã chuyển «{$baiViet->tieuDe}» sang bản nháp.",
        ];
    }

    public function uploadImage(Request $request): string
    {
        $upload = $this->imageUploadService->validateAndStore($request, 'content_image', 'file');

        return $upload['url'];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function syncTags(BaiViet $baiViet, string $tagNames): void
    {
        if (empty(trim($tagNames))) {
            $baiViet->tags()->detach();
            return;
        }
        $names = array_unique(array_filter(array_map('trim', explode(',', $tagNames))));
        $tagIds = [];
        foreach ($names as $name) {
            $tag = Tag::firstOrCreate(['tenTag' => $name], ['slug' => Str::slug($name)]);
            $tagIds[] = $tag->tagId;
        }
        $baiViet->tags()->sync($tagIds);
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-') ?: 'bai-viet';
        $candidate = $slug;
        $counter = 1;
        while (true) {
            // withTrashed() để tính cả bản ghi đã soft-delete, tránh duplicate key
            $q = BaiViet::withTrashed()->where('slug', $candidate);
            if ($excludeId)
                $q->where('baiVietId', '!=', $excludeId);
            if (!$q->exists())
                break;
            $candidate = $slug . '-' . $counter++;
        }
        return $candidate;
    }

    private function bustPublicContentCache(): void
    {
        $this->publicContentCache->bust();
    }
}
