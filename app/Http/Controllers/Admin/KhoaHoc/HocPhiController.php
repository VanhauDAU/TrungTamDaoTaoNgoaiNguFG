<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Course\HocPhi;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;

class HocPhiController extends Controller
{
    /** Thêm gói học phí mới cho khóa học */
    public function store(Request $request)
    {
        $data = $request->validate([
            'khoaHocId' => 'required|exists:khoahoc,khoaHocId',
            'soBuoi'    => 'required|integer|min:1',
            'donGia'    => 'required|numeric|min:0',
            'trangThai' => 'required|in:0,1',
        ], [
            'soBuoi.required' => 'Vui lòng nhập số buổi.',
            'soBuoi.min'      => 'Số buổi phải tối thiểu là 1.',
            'donGia.required' => 'Vui lòng nhập đơn giá.',
            'donGia.min'      => 'Đơn giá không được âm.',
        ]);

        HocPhi::create($data);

        return redirect()
            ->route('admin.khoa-hoc.show', $request->khoaHocId)
            ->with('success', "Đã thêm gói học phí {$data['soBuoi']} buổi thành công.");
    }

    /** Cập nhật gói học phí */
    public function update(Request $request, int $id)
    {
        $hocPhi = HocPhi::findOrFail($id);

        $data = $request->validate([
            'soBuoi'    => 'required|integer|min:1',
            'donGia'    => 'required|numeric|min:0',
            'trangThai' => 'required|in:0,1',
        ]);

        $hocPhi->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật gói học phí.']);
        }

        return redirect()
            ->route('admin.khoa-hoc.show', $hocPhi->khoaHocId)
            ->with('success', "Đã cập nhật gói học phí {$hocPhi->soBuoi} buổi thành công.");
    }

    /** Xóa gói học phí */
    public function destroy(int $id)
    {
        $hocPhi = HocPhi::findOrFail($id);

        // Kiểm tra còn lớp học đang dùng gói này không
        $soLopDung = $hocPhi->lopHocs()->count();
        if ($soLopDung > 0) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => "Gói này đang được dùng bởi {$soLopDung} lớp học, không thể xóa."], 422);
            }
            return redirect()
                ->route('admin.khoa-hoc.show', $hocPhi->khoaHocId)
                ->with('error', "Gói này đang được dùng bởi {$soLopDung} lớp học, không thể xóa.");
        }

        $khoaHocId = $hocPhi->khoaHocId;
        $soBuoi    = $hocPhi->soBuoi;
        $hocPhi->delete();

        return redirect()
            ->route('admin.khoa-hoc.show', $khoaHocId)
            ->with('success', "Đã xóa gói học phí {$soBuoi} buổi thành công.");
    }

    /** Bật/tắt trạng thái nhanh (AJAX) */
    public function toggleStatus(int $id)
    {
        $hocPhi = HocPhi::findOrFail($id);
        $hocPhi->update(['trangThai' => $hocPhi->trangThai ? 0 : 1]);
        return response()->json([
            'success'   => true,
            'trangThai' => $hocPhi->trangThai,
        ]);
    }
}
