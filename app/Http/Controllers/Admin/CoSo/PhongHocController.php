<?php

namespace App\Http\Controllers\Admin\CoSo;

use App\Http\Controllers\Controller;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Http\Request;

class PhongHocController extends Controller
{
    /**
     * Danh sách phòng học (có thể lọc theo cơ sở).
     */
    public function index(Request $request)
    {
        $query = PhongHoc::with('coSoDaoTao');

        if ($search = $request->q) {
            $query->where('tenPhong', 'like', "%{$search}%");
        }

        if ($request->filled('coSoId')) {
            $query->where('coSoId', $request->coSoId);
        }

        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        $phongHocs = $query->orderBy('coSoId')->orderBy('tenPhong')->paginate(20)->withQueryString();
        $coSos     = CoSoDaoTao::orderBy('maCoSo')->get();
        $tongSo    = PhongHoc::count();
        $hoatDong  = PhongHoc::where('trangThai', 1)->count();

        return view('admin.co-so.phong-hoc.index', compact('phongHocs', 'coSos', 'tongSo', 'hoatDong'));
    }

    /**
     * Lưu phòng mới (gọi từ trang show của cơ sở).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenPhong'     => 'required|string|max:50',
            'coSoId'       => 'required|exists:cosodaotao,coSoId',
            'sucChua'      => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai'    => 'required|in:0,1',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
            'coSoId.required'   => 'Vui lòng chọn cơ sở.',
            'coSoId.exists'     => 'Cơ sở không tồn tại.',
        ]);

        $phong = PhongHoc::create($request->only(['tenPhong', 'coSoId', 'sucChua', 'trangThietBi', 'trangThai']));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm phòng «' . $phong->tenPhong . '» thành công.',
                'room'    => $phong
            ]);
        }

        return redirect()->route('admin.co-so.show', $request->coSoId)
            ->with('success', 'Đã thêm phòng «' . $request->tenPhong . '» thành công.');
    }

    /**
     * Cập nhật phòng (AJAX-friendly, redirect về show cơ sở).
     */
    public function update(Request $request, int $id)
    {
        $phong = PhongHoc::findOrFail($id);

        $request->validate([
            'tenPhong'     => 'required|string|max:50',
            'sucChua'      => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai'    => 'required|in:0,1',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
        ]);

        $phong->update($request->only(['tenPhong', 'sucChua', 'trangThietBi', 'trangThai']));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật phòng «' . $phong->tenPhong . '» thành công.',
                'room'    => $phong->fresh()
            ]);
        }

        return redirect()->route('admin.co-so.show', $phong->coSoId)
            ->with('success', 'Đã cập nhật phòng «' . $phong->tenPhong . '».');
    }

    /**
     * Xóa phòng học.
     */
    public function destroy(Request $request, int $id)
    {
        $phong  = PhongHoc::findOrFail($id);
        $coSoId = $phong->coSoId;
        $ten    = $phong->tenPhong;
        $phong->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa phòng «' . $ten . '» thành công.'
            ]);
        }

        return redirect()->route('admin.co-so.show', $coSoId)
            ->with('success', "Đã xóa phòng «{$ten}».");
    }
}
