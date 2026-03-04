<?php

namespace App\Http\Controllers\Admin\LienHe;

use App\Http\Controllers\Controller;
use App\Models\Interaction\LienHe;
use Illuminate\Http\Request;

class LienHeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LienHe::query();

        // Search by keyword
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('hoTen', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%")
                      ->orWhere('soDienThoai', 'LIKE', "%{$q}%")
                      ->orWhere('tieuDe', 'LIKE', "%{$q}%");
            });
        }

        // Filter by status
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        // Sorting
        $orderBy = $request->get('orderBy', 'LienHeId');
        $dir = $request->get('dir', 'desc');

        $allowedSortColumns = ['LienHeId', 'hoTen', 'email', 'created_at', 'trangThai'];
        if (in_array($orderBy, $allowedSortColumns) && in_array($dir, ['asc', 'desc'])) {
            $query->orderBy($orderBy, $dir);
        }

        $lienHes = $query->paginate(15)->withQueryString();

        // Stats (chỉ đếm bản ghi chưa xóa — mặc định SoftDeletes)
        $tongSo = LienHe::count();
        $daXuLy = LienHe::where('trangThai', 1)->count();
        $chuaXuLy = LienHe::where('trangThai', 0)->count();
        $tongXoa = LienHe::onlyTrashed()->count();

        return view('admin.lien-he.index', compact('lienHes', 'tongSo', 'daXuLy', 'chuaXuLy', 'tongXoa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lienHe = LienHe::findOrFail($id);
        return view('admin.lien-he.edit', compact('lienHe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'trangThai' => 'required|in:0,1',
        ]);

        $lienHe = LienHe::findOrFail($id);
        $lienHe->trangThai = $request->trangThai;
        $lienHe->save();

        return redirect()->route('admin.lien-he.index')->with('success', 'Cập nhật trạng thái liên hệ thành công.');
    }

    /**
     * Soft-delete the specified resource.
     */
    public function destroy(string $id)
    {
        $lienHe = LienHe::findOrFail($id);
        $lienHe->delete();

        return redirect()->back()->with('success', 'Đã chuyển liên hệ vào thùng rác.');
    }

    /**
     * Display trashed contacts.
     */
    public function trash(Request $request)
    {
        $query = LienHe::onlyTrashed();

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('hoTen', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%")
                      ->orWhere('soDienThoai', 'LIKE', "%{$q}%")
                      ->orWhere('tieuDe', 'LIKE', "%{$q}%");
            });
        }

        $query->orderBy('deleted_at', 'desc');

        $lienHes = $query->paginate(15)->withQueryString();
        $tongXoa = LienHe::onlyTrashed()->count();

        return view('admin.lien-he.trash', compact('lienHes', 'tongXoa'));
    }

    /**
     * Bulk soft-delete selected contacts.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Chưa chọn liên hệ nào để xóa.');
        }

        LienHe::whereIn('LienHeId', $ids)->delete();

        return redirect()->back()->with('success', 'Đã chuyển ' . count($ids) . ' liên hệ vào thùng rác.');
    }

    /**
     * Restore a soft-deleted contact.
     */
    public function restore(string $id)
    {
        $lienHe = LienHe::onlyTrashed()->findOrFail($id);
        $lienHe->restore();

        return redirect()->back()->with('success', 'Đã khôi phục liên hệ thành công.');
    }
}
