<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LopHocController extends Controller
{
    public function __construct(
        protected LopHocServiceInterface $lopHocService
    ) {}

    public function index(Request $request)
    {
        return view('admin.lop-hoc.index', $this->lopHocService->getList($request));
    }

    public function trash(Request $request)
    {
        return view('admin.lop-hoc.trash', $this->lopHocService->getTrashList($request));
    }

    public function create(Request $request)
    {
        return view('admin.lop-hoc.create', $this->lopHocService->getCreateFormData($request));
    }

    public function store(Request $request)
    {
        $lopHoc = $this->lopHocService->store($request);

        return redirect()->route('admin.lop-hoc.index')
            ->with('success', 'Đã thêm lớp học «' . $lopHoc->tenLopHoc . '» thành công.');
    }

    public function show(string $slug)
    {
        return view('admin.lop-hoc.show', $this->lopHocService->getDetail($slug));
    }

    public function edit(string $slug)
    {
        return view('admin.lop-hoc.edit', $this->lopHocService->getEditFormData($slug));
    }

    public function update(Request $request, string $slug)
    {
        $lopHoc = $this->lopHocService->update($request, $slug);

        return redirect()->route('admin.lop-hoc.show', $lopHoc->slug)
            ->with('success', 'Đã cập nhật lớp học «' . $lopHoc->tenLopHoc . '» thành công.');
    }

    public function destroy(string $slug)
    {
        try {
            $ten = $this->lopHocService->destroy($slug);
            return redirect()->route('admin.lop-hoc.index')
                ->with('success', "Đã chuyển lớp học «{$ten}» vào trạng thái xóa mềm.");
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.lop-hoc.index')
                ->with('error', $e->getMessage());
        }
    }

    public function restore(string $slug)
    {
        $lopHoc = $this->lopHocService->restore($slug);

        return redirect()->route('admin.lop-hoc.show', $lopHoc->slug)
            ->with('success', "Đã khôi phục lớp học «{$lopHoc->tenLopHoc}» thành công.");
    }

    public function getHocPhiByKhoaHoc(int $khoaHocId)
    {
        return response()->json($this->lopHocService->getHocPhiByKhoaHoc($khoaHocId));
    }

    public function getPhongByCoso(int $coSoId)
    {
        return response()->json($this->lopHocService->getPhongByCoso($coSoId));
    }

    public function getGiaoVienByCoso(int $coSoId)
    {
        return response()->json($this->lopHocService->getGiaoVienByCoso($coSoId));
    }
}
