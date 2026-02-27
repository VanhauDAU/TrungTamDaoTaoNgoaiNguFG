<?php

namespace App\Http\Controllers\Client;

use App\Models\Content\BaiViet;
use App\Models\Content\DanhMucBaiViet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BaiViet::where('trangThai', 1);

        // Tìm kiếm theo tiêu đề hoặc tóm tắt
        if ($request->filled('s')) {
            $search = $request->input('s');
            $query->where(function ($q) use ($search) {
                $q->where('tieuDe', 'like', "%{$search}%")
                    ->orWhere('tomTat', 'like', "%{$search}%");
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('danhMucs', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Lọc theo tag
        if ($request->filled('tag')) {
            $tagSlug = $request->input('tag');
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Sắp xếp
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'popular':
                $query->orderBy('luotXem', 'desc');
                break;
            case 'az':
                $query->orderBy('tieuDe', 'asc');
                break;
            default: // latest
                $query->latest();
                break;
        }

        $blogs = $query->with(['danhMucs', 'tags'])->paginate(9)->withQueryString();
        $categories = DanhMucBaiViet::all();
        $totalPosts = BaiViet::where('trangThai', 1)->count();

        return view('clients.blog.index', compact('blogs', 'categories', 'totalPosts'));
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

        return view('clients.blog.show', compact('blog', 'relatedPosts', 'otherPosts', 'categories'));
    }
}
