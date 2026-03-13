<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KhoaHocController extends Controller
{
    public function __construct(
        protected KhoaHocServiceInterface $khoaHocService
    ) {}

    public function index(Request $request)
    {
        return view('admin.khoa-hoc.index', $this->khoaHocService->getList($request));
    }

    public function create()
    {
        return view('admin.khoa-hoc.create', $this->khoaHocService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $khoaHoc = $this->khoaHocService->store($request);

        return redirect()->route('admin.khoa-hoc.index')
            ->with('success', 'Đã thêm khóa học «' . $khoaHoc->tenKhoaHoc . '» thành công.');
    }

    public function show(string $slug)
    {
        return view('admin.khoa-hoc.show', $this->khoaHocService->getDetail($slug));
    }

    public function edit(string $slug)
    {
        return view('admin.khoa-hoc.edit', $this->khoaHocService->getEditFormData($slug));
    }

    public function update(Request $request, string $slug)
    {
        $khoaHoc = $this->khoaHocService->update($request, $slug);

        return redirect()->route('admin.khoa-hoc.show', $khoaHoc->slug)
            ->with('success', 'Đã cập nhật khóa học «' . $khoaHoc->tenKhoaHoc . '» thành công.');
    }

    public function destroy(string $slug)
    {
        try {
            $ten = $this->khoaHocService->destroy($slug);
            return redirect()->route('admin.khoa-hoc.index')
                ->with('success', "Đã lưu trữ khóa học «{$ten}». Dữ liệu vẫn được giữ nguyên.");
        } catch (\Exception $e) {
            return redirect()->route('admin.khoa-hoc.index')
                ->with('error', $e->getMessage());
        }
    }

    public function restore(string $slug)
    {
        try {
            $khoaHoc = $this->khoaHocService->restore($slug);
            return redirect()->route('admin.khoa-hoc.show', $slug)
                ->with('success', "Đã khôi phục khóa học «{$khoaHoc->tenKhoaHoc}» thành công.");
        } catch (\Exception $e) {
            return redirect()->route('admin.khoa-hoc.index')
                ->with('error', $e->getMessage());
        }
    }
}
