<?php

namespace App\Http\Controllers\Admin\HocVien;

use App\Contracts\Admin\HocVienServiceInterface;
use App\Exports\HocViensExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HocVienController extends Controller
{
    public function __construct(
        protected HocVienServiceInterface $hocVienService
    ) {
        $this->middleware('permission:hoc_vien,xem')->only('index', 'export', 'trash');
        $this->middleware('permission:hoc_vien,them')->only('create', 'store', 'restore');
        $this->middleware('permission:hoc_vien,sua')->only('edit', 'update');
        $this->middleware('permission:hoc_vien,xoa')->only('destroy');
    }

    public function index(Request $request)
    {
        return view('admin.hoc-vien.index', $this->hocVienService->getList($request));
    }

    public function export(Request $request)
    {
        $fileName = 'hoc-vien-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new HocViensExport($this->hocVienService->buildIndexQuery($request)), $fileName);
    }

    public function create()
    {
        return view('admin.hoc-vien.create');
    }

    public function store(Request $request)
    {
        $taiKhoan = $this->hocVienService->store($request);

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', 'Đã tạo học viên «' . $request->hoTen . '» thành công.');
    }

    public function edit(string $taiKhoan)
    {
        $hocVien = $this->hocVienService->findByUsername($taiKhoan);
        return view('admin.hoc-vien.edit', compact('hocVien'));
    }

    public function update(Request $request, string $taiKhoan)
    {
        $hocVien = $this->hocVienService->findByUsername($taiKhoan);
        $this->hocVienService->update($request, $hocVien);

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', 'Đã cập nhật thông tin học viên thành công.');
    }

    public function trash(Request $request)
    {
        return view('admin.hoc-vien.trash', $this->hocVienService->getTrashList($request));
    }

    public function restore(string $taiKhoan)
    {
        $hoTen = $this->hocVienService->restore($taiKhoan);

        return redirect()->route('admin.hoc-vien.trash')
            ->with('success', "Đã khôi phục học viên «{$hoTen}» thành công.");
    }

    public function destroy(string $taiKhoan)
    {
        $hoTen = $this->hocVienService->destroy($taiKhoan);

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', "Đã xóa học viên «{$hoTen}».");
    }
}
