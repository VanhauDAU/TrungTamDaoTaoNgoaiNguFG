<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\DanhMucKhoaHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DanhMucKhoaHocController extends Controller
{
    public function __construct(
        protected DanhMucKhoaHocServiceInterface $danhMucKhoaHocService
        )
    {
    }

    public function index(Request $request)
    {
        return view('admin.danh-muc-khoa-hoc.index', $this->danhMucKhoaHocService->getList($request));
    }

    public function create()
    {
        return view('admin.danh-muc-khoa-hoc.create', $this->danhMucKhoaHocService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $danhMuc = $this->danhMucKhoaHocService->store($request);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã thêm danh mục «' . $danhMuc->tenDanhMuc . '» thành công.');
    }

    public function edit(string $slug)
    {
        return view('admin.danh-muc-khoa-hoc.edit', $this->danhMucKhoaHocService->getEditFormData($slug));
    }

    public function update(Request $request, string $slug)
    {
        $danhMuc = $this->danhMucKhoaHocService->update($request, $slug);

        return redirect()->route('admin.danh-muc-khoa-hoc.index')
            ->with('success', 'Đã cập nhật danh mục «' . $danhMuc->tenDanhMuc . '» thành công.');
    }

    public function destroy(string $slug)
    {
        try {
            $ten = $this->danhMucKhoaHocService->destroy($slug);

            return redirect()->route('admin.danh-muc-khoa-hoc.index')
                ->with('success', "Đã xóa danh mục «{$ten}» thành công.");
        }
        catch (\Throwable $e) {
            return redirect()->route('admin.danh-muc-khoa-hoc.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    public function reorder(Request $request)
    {
        $result = $this->danhMucKhoaHocService->reorder($request);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status']);
    }
}