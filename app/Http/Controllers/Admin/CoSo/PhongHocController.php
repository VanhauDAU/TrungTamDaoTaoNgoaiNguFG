<?php

namespace App\Http\Controllers\Admin\CoSo;

use App\Contracts\Admin\PhongHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhongHocController extends Controller
{
    public function __construct(
        protected PhongHocServiceInterface $phongHocService
    ) {}

    public function index(Request $request)
    {
        return view('admin.co-so.phong-hoc.index', $this->phongHocService->getList($request));
    }

    public function store(Request $request)
    {
        $phong = $this->phongHocService->store($request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã thêm phòng «' . $phong->tenPhong . '» thành công.', 'room' => $phong->fresh()]);
        }
        return redirect()->route('admin.co-so.show', $request->coSoId)
            ->with('success', 'Đã thêm phòng «' . $phong->tenPhong . '» thành công.');
    }

    public function update(Request $request, int $id)
    {
        $phong = $this->phongHocService->update($request, $id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật phòng «' . $phong->tenPhong . '» thành công.', 'room' => $phong]);
        }
        return redirect()->route('admin.co-so.show', $phong->coSoId)
            ->with('success', 'Đã cập nhật phòng «' . $phong->tenPhong . '».');
    }

    public function destroy(Request $request, int $id)
    {
        $phong  = \App\Models\Facility\PhongHoc::findOrFail($id);
        $coSoId = $phong->coSoId;

        try {
            $ten = $this->phongHocService->destroy($request, $id);
        } catch (\RuntimeException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->route('admin.co-so.show', $coSoId)->with('error', $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Đã xóa phòng «{$ten}» thành công."]);
        }
        return redirect()->route('admin.co-so.show', $coSoId)->with('success', "Đã xóa phòng «{$ten}».");
    }

    public function toggleStatus(Request $request, int $id)
    {
        return response()->json($this->phongHocService->toggleStatus($request, $id));
    }

    public function lichSu(int $id)
    {
        return response()->json($this->phongHocService->lichSu($id));
    }
}
