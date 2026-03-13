<?php

namespace App\Http\Controllers\Admin\TaiChinh;

use App\Contracts\Admin\TaiChinh\HoaDonServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HoaDonController extends Controller
{
    public function __construct(
        protected HoaDonServiceInterface $hoaDonService
        )
    {
        $this->middleware('permission:tai_chinh,xem')->only('index', 'show');
        $this->middleware('permission:tai_chinh,sua')->only('update', 'storePhieuThu', 'destroyPhieuThu');
    }

    public function index(Request $request)
    {
        return view('admin.hoa-don.index', $this->hoaDonService->getList($request));
    }

    public function show(int $id)
    {
        return view('admin.hoa-don.show', $this->hoaDonService->getDetail($id));
    }

    public function update(Request $request, int $id)
    {
        $this->hoaDonService->update($request, $id);
        return redirect()->route('admin.hoa-don.show', $id)
            ->with('success', 'Đã cập nhật hóa đơn thành công.');
    }

    public function storePhieuThu(Request $request, int $hoaDonId)
    {
        $this->hoaDonService->storePhieuThu($request, $hoaDonId);
        return redirect()->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã tạo phiếu thu thành công.');
    }

    public function destroyPhieuThu(int $id)
    {
        $hoaDonId = $this->hoaDonService->destroyPhieuThu($id);
        return redirect()->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã hủy phiếu thu thành công.');
    }
}