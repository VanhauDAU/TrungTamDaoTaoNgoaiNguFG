<?php

namespace App\Http\Controllers\Client\Blog;

use App\Http\Controllers\Controller;
use App\Models\Content\BaiViet;
use App\Models\Content\DanhMucBaiViet;
use App\Services\Client\PublicContentCacheService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected PublicContentCacheService $publicContentCache
    ) {
    }

    public function index(Request $request)
    {
        $payload = $this->publicContentCache->remember(
            'blog.index',
            $request->only(['s', 'category', 'tag', 'sort', 'page']),
            fn(): array => $this->buildIndexPayload($request)
        );
        return view('clients.bai-viet.index', $payload);
    }

    public function show($slug)
    {
        $blog = BaiViet::where('slug', $slug)
            ->where('trangThai', 1)
            ->with(['danhMucs', 'tags', 'taiKhoan.hoSoNguoiDung'])
            ->firstOrFail();

        // Tăng lượt xem
        $blog->increment('luotXem');

        // Bài viết liên quan: cùng danh mục, trừ bài hiện tại
        $danhMucIds = $blog->danhMucs->pluck('danhMucId');
        $relatedPosts = BaiViet::where('trangThai', 1)
            ->where('baiVietId', '!=', $blog->baiVietId)
            ->whereHas('danhMucs', function ($q) use ($danhMucIds) {
                $q->whereIn('BaiViet_DanhMuc.danhMucId', $danhMucIds);
            })
            ->with(['danhMucs'])
            ->latest()
            ->take(3)
            ->get();

        // Tất cả danh mục (cho sidebar)
        $categories = DanhMucBaiViet::withCount([
            'baiViets' => function ($q) {
                $q->where('trangThai', 1);
            }
        ])->get();

        // Bài viết khác (mới nhất, loại trừ bài hiện tại + bài liên quan)
        $excludeIds = $relatedPosts->pluck('baiVietId')->push($blog->baiVietId)->toArray();
        $otherPosts = BaiViet::where('trangThai', 1)
            ->whereNotIn('baiVietId', $excludeIds)
            ->with(['danhMucs'])
            ->latest()
            ->take(6)
            ->get();

        return view('clients.bai-viet.show', compact('blog', 'relatedPosts', 'otherPosts', 'categories'));
    }

    private function buildIndexPayload(Request $request): array
    {
        $query = BaiViet::query()->where('trangThai', 1);

        if ($request->filled('s')) {
            $search = (string) $request->input('s');
            $query->where(function ($builder) use ($search) {
                $builder->where('tieuDe', 'like', "%{$search}%")
                    ->orWhere('tomTat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categorySlug = (string) $request->input('category');
            $query->whereHas('danhMucs', function ($builder) use ($categorySlug) {
                $builder->where('slug', $categorySlug);
            });
        }

        if ($request->filled('tag')) {
            $tagSlug = (string) $request->input('tag');
            $query->whereHas('tags', function ($builder) use ($tagSlug) {
                $builder->where('slug', $tagSlug);
            });
        }

        switch ($request->input('sort', 'latest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'popular':
                $query->orderByDesc('luotXem');
                break;
            case 'az':
                $query->orderBy('tieuDe');
                break;
            default:
                $query->latest();
                break;
        }

        return [
            'blogs' => $query->with(['danhMucs', 'tags'])->paginate(9)->withQueryString(),
            'categories' => DanhMucBaiViet::query()
                ->where('trangThai', 1)
                ->withCount([
                    'baiViets' => fn($builder) => $builder->where('trangThai', 1),
                ])
                ->having('bai_viets_count', '>', 0)
                ->orderBy('tenDanhMuc')
                ->get(),
            'totalPosts' => BaiViet::query()->where('trangThai', 1)->count(),
        ];
    }
}
