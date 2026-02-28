<?php

namespace App\Http\Controllers\Admin\BaiViet;

use App\Http\Controllers\Controller;
use App\Models\Content\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /** API: Danh sách tags (JSON) */
    public function index(Request $request)
    {
        $query = Tag::withCount('baiViets');

        if ($search = $request->q) {
            $query->where('tenTag', 'like', "%{$search}%");
        }

        $tags = $query->orderBy('tenTag')->get();

        return response()->json($tags);
    }

    /** API: Tạo tag mới */
    public function store(Request $request)
    {
        $request->validate([
            'tenTag' => 'required|string|max:100|unique:tags,tenTag',
        ]);

        $tag = Tag::create([
            'tenTag' => $request->tenTag,
            'slug' => Str::slug($request->tenTag),
        ]);

        return response()->json($tag, 201);
    }

    /** API: Xóa tag */
    public function destroy(int $id)
    {
        try {
            $tag = Tag::withCount('baiViets')->findOrFail($id);

            if ($tag->bai_viets_count > 0) {
                return response()->json([
                    'error' => "Không thể xóa tag «{$tag->tenTag}» — còn {$tag->bai_viets_count} bài viết sử dụng.",
                ], 422);
            }

            $tag->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
