<?php

namespace App\Http\Controllers\Admin\LienHe;

use App\Contracts\Admin\LienHe\LienHeServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LienHeController extends Controller
{
    public function __construct(
        protected LienHeServiceInterface $lienHeService
        )
    {
    }

    public function index(Request $request)
    {
        return view('admin.lien-he.index', $this->lienHeService->getList($request));
    }

    public function show(string $id)
    {
        return view('admin.lien-he.show', $this->lienHeService->getDetail($id));
    }

    public function update(Request $request, string $id)
    {
        $this->lienHeService->update($request, $id);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật thành công.']);
        }
        return redirect()->route('admin.lien-he.show', $id)->with('success', 'Cập nhật liên hệ thành công.');
    }

    public function assign(Request $request, string $id)
    {
        $result = $this->lienHeService->assign($request, $id);
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        return redirect()->route('admin.lien-he.show', $id)->with('success', 'Đã cập nhật người phụ trách.');
    }

    public function storeReply(Request $request, string $id)
    {
        $result = $this->lienHeService->storeReply($request, $id);
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        return redirect()->route('admin.lien-he.show', $id)->with('success', 'Đã thêm phản hồi nội bộ.');
    }

    public function destroy(string $id)
    {
        $this->lienHeService->destroy($id);
        return redirect()->back()->with('success', 'Đã chuyển liên hệ vào thùng rác.');
    }

    public function trash(Request $request)
    {
        return view('admin.lien-he.trash', $this->lienHeService->getTrash($request));
    }

    public function bulkDestroy(Request $request)
    {
        $count = $this->lienHeService->bulkDestroy($request);
        if ($count === 0) {
            return redirect()->back()->with('error', 'Chưa chọn liên hệ nào để xóa.');
        }
        return redirect()->back()->with('success', "Đã chuyển {$count} liên hệ vào thùng rác.");
    }

    public function bulkUpdateStatus(Request $request)
    {
        $count = $this->lienHeService->bulkUpdateStatus($request);
        if ($count === 0) {
            return redirect()->back()->with('error', 'Chưa chọn liên hệ nào.');
        }
        $newLabel = \App\Models\Interaction\LienHe::TRANG_THAI_LABELS[$request->trangThai] ?? $request->trangThai;
        return redirect()->back()->with('success', "Đã chuyển {$count} liên hệ sang \"{$newLabel}\".");
    }

    public function restore(string $id)
    {
        $this->lienHeService->restore($id);
        return redirect()->back()->with('success', 'Đã khôi phục liên hệ thành công.');
    }
}