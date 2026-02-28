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

        // Stats
        $tongSo = LienHe::count();
        $daXuLy = LienHe::where('trangThai', 1)->count();
        $chuaXuLy = LienHe::where('trangThai', 0)->count();

        return view('admin.lien-he.index', compact('lienHes', 'tongSo', 'daXuLy', 'chuaXuLy'));
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lienHe = LienHe::findOrFail($id);
        $lienHe->delete();

        return redirect()->back()->with('success', 'Đã xóa liên hệ thành công.');
    }
}
