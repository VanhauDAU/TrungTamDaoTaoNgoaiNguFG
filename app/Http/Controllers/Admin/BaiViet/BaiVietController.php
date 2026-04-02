<?php

namespace App\Http\Controllers\Admin\BaiViet;

use App\Contracts\Admin\BaiViet\BaiVietServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaiVietController extends Controller
{
    public function __construct(
        protected BaiVietServiceInterface $baiVietService
        )
    {
    }

    public function index(Request $request)
    {
        return view('admin.bai-viet.index', $this->baiVietService->getList($request));
    }

    public function create()
    {
        return view('admin.bai-viet.create', $this->baiVietService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $baiViet = $this->baiVietService->store($request);
        return redirect()->route('admin.bai-viet.index')
            ->with('success', 'Đã thêm bài viết «' . $baiViet->tieuDe . '» thành công.');
    }

    public function show(int $id)
    {
        return view('admin.bai-viet.show', $this->baiVietService->getDetail($id));
    }

    public function edit(int $id)
    {
        return view('admin.bai-viet.edit', $this->baiVietService->getEditFormData($id));
    }

    public function update(Request $request, int $id)
    {
        $baiViet = $this->baiVietService->update($request, $id);
        return redirect()->route('admin.bai-viet.show', $id)
            ->with('success', 'Đã cập nhật bài viết «' . $baiViet->tieuDe . '» thành công.');
    }

    public function destroy(int $id)
    {
        try {
            $ten = $this->baiVietService->destroy($id);
            return redirect()->route('admin.bai-viet.index')->with('success', "Đã chuyển bài viết «{$ten}» vào thùng rác.");
        }
        catch (\Exception $e) {
            return redirect()->route('admin.bai-viet.index')->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        $count = $this->baiVietService->bulkDestroy($request);
        return response()->json(['success' => true, 'message' => "Đã chuyển {$count} bài viết vào thùng rác."]);
    }

    public function trash(Request $request)
    {
        return view('admin.bai-viet.trash', $this->baiVietService->getTrash($request));
    }

    public function restore(int $id)
    {
        $baiViet = $this->baiVietService->restore($id);
        return redirect()->route('admin.bai-viet.trash')
            ->with('success', "Đã khôi phục bài viết «{$baiViet->tieuDe}».");
    }

    public function bulkRestore(Request $request)
    {
        $count = $this->baiVietService->bulkRestore($request);
        return response()->json(['success' => true, 'message' => "Đã khôi phục {$count} bài viết."]);
    }

    public function forceDestroy(int $id)
    {
        $ten = $this->baiVietService->forceDestroy($id);
        return redirect()->route('admin.bai-viet.trash')->with('success', "Đã xóa vĩnh viễn bài viết «{$ten}».");
    }

    public function toggleStatus(int $id)
    {
        return response()->json($this->baiVietService->toggleStatus($id));
    }

    public function uploadImage(Request $request)
    {
        $location = $this->baiVietService->uploadImage($request);

        return response()->json([
            'message' => 'Tải ảnh lên thành công.',
            'location' => $location,
            'file' => ['url' => $location],
        ]);
    }
}
