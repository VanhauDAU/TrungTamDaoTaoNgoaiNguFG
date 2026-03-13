<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\HocPhiServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HocPhiController extends Controller
{
    public function __construct(
        protected HocPhiServiceInterface $hocPhiService
    ) {}

    public function store(Request $request)
    {
        $hocPhi = $this->hocPhiService->store($request);

        return redirect()
            ->route('admin.khoa-hoc.show', $hocPhi->khoaHoc->slug)
            ->with('success', "Đã thêm gói học phí {$hocPhi->soBuoi} buổi thành công.");
    }

    public function update(Request $request, int $id)
    {
        $hocPhi = $this->hocPhiService->update($request, $id);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật gói học phí.']);
        }

        return redirect()
            ->route('admin.khoa-hoc.show', $hocPhi->khoaHoc->slug)
            ->with('success', "Đã cập nhật gói học phí {$hocPhi->soBuoi} buổi thành công.");
    }

    public function destroy(int $id)
    {
        $result = $this->hocPhiService->destroy($id);

        if (! $result['success']) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], $result['status']);
            }

            return redirect()
                ->route('admin.khoa-hoc.show', $result['slug'])
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('admin.khoa-hoc.show', $result['slug'])
            ->with('success', $result['message']);
    }

    public function toggleStatus(int $id)
    {
        $result = $this->hocPhiService->toggleStatus($id);

        return response()->json([
            'success' => $result['success'],
            'trangThai' => $result['trangThai'],
        ], $result['status']);
    }
}
