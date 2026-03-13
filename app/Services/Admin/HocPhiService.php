<?php

namespace App\Services\Admin;

use App\Contracts\Admin\HocPhiServiceInterface;
use App\Models\Course\HocPhi;
use Illuminate\Http\Request;

class HocPhiService implements HocPhiServiceInterface
{
    public function store(Request $request): HocPhi
    {
        $data = $this->validatePayload($request, true);

        return HocPhi::create($data)->load('khoaHoc');
    }

    public function update(Request $request, int $id): HocPhi
    {
        $hocPhi = HocPhi::findOrFail($id);
        $hocPhi->update($this->validatePayload($request, false));

        return $hocPhi->fresh('khoaHoc');
    }

    public function destroy(int $id): array
    {
        $hocPhi = HocPhi::with('khoaHoc')->findOrFail($id);
        $soLopDung = $hocPhi->lopHocs()->count();

        if ($soLopDung > 0) {
            return [
                'success' => false,
                'message' => "Gói này đang được dùng bởi {$soLopDung} lớp học, không thể xóa.",
                'slug' => $hocPhi->khoaHoc->slug,
                'status' => 422,
            ];
        }

        $slug = $hocPhi->khoaHoc->slug;
        $soBuoi = $hocPhi->soBuoi;
        $hocPhi->delete();

        return [
            'success' => true,
            'message' => "Đã xóa gói học phí {$soBuoi} buổi thành công.",
            'slug' => $slug,
            'status' => 200,
        ];
    }

    public function toggleStatus(int $id): array
    {
        $hocPhi = HocPhi::findOrFail($id);
        $hocPhi->update(['trangThai' => $hocPhi->trangThai ? 0 : 1]);

        return [
            'success' => true,
            'trangThai' => $hocPhi->trangThai,
            'status' => 200,
        ];
    }

    private function validatePayload(Request $request, bool $isCreate): array
    {
        $rules = [
            'soBuoi' => 'required|integer|min:1',
            'donGia' => 'required|numeric|min:0',
            'trangThai' => 'required|in:0,1',
        ];

        if ($isCreate) {
            $rules['khoaHocId'] = 'required|exists:khoahoc,khoaHocId';
        }

        return $request->validate($rules, [
            'soBuoi.required' => 'Vui lòng nhập số buổi.',
            'soBuoi.min' => 'Số buổi phải tối thiểu là 1.',
            'donGia.required' => 'Vui lòng nhập đơn giá.',
            'donGia.min' => 'Đơn giá không được âm.',
        ]);
    }
}
