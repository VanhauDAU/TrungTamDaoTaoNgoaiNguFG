<?php

namespace App\Http\Controllers\Admin\HocVien;

use App\Contracts\Admin\HocVien\HocVienServiceInterface;
use App\Jobs\GenerateHocVienExportJob;
use App\Http\Controllers\Controller;
use App\Services\Support\QueuedExportService;
use Illuminate\Http\Request;

class HocVienController extends Controller
{
    public function __construct(
        protected HocVienServiceInterface $hocVienService,
        protected QueuedExportService $queuedExportService
        )
    {
        $this->middleware('permission:hoc_vien,xem')->only('index', 'export', 'trash');
        $this->middleware('permission:hoc_vien,them')->only('create', 'store', 'restore');
        $this->middleware('permission:hoc_vien,sua')->only('edit', 'update', 'updateAvatar');
        $this->middleware('permission:hoc_vien,xoa')->only('destroy');
    }

    public function index(Request $request)
    {
        return view('admin.hoc-vien.index', $this->hocVienService->getList($request));
    }

    public function export(Request $request)
    {
        if ($response = $this->queuedExportService->downloadIfReady('hoc-vien.export', $request->query())) {
            return $response;
        }

        $state = $this->queuedExportService->get('hoc-vien.export', $request->query());
        if (($state['status'] ?? null) === 'queued') {
            return redirect()
                ->route('admin.hoc-vien.index', $request->query())
                ->with('info', 'File Excel đang được tạo ở nền. Tải lại sau ít phút để nhận file.');
        }

        $fileName = 'hoc-vien-' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $this->queuedExportService->markQueued('hoc-vien.export', $request->query(), [
            'filename' => $fileName,
        ]);

        GenerateHocVienExportJob::dispatch($request->query(), $fileName)->afterCommit();

        return redirect()
            ->route('admin.hoc-vien.index', $request->query())
            ->with('success', 'Đã đưa file Excel vào hàng chờ xuất. Worker queue sẽ tạo file ở nền.');
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

    public function updateAvatar(Request $request, string $taiKhoan)
    {
        $hocVien = $this->hocVienService->findByUsername($taiKhoan);
        $avatarUrl = $this->hocVienService->updateAvatar($request, $hocVien);

        return response()->json([
            'message'   => 'Cập nhật ảnh đại diện học viên thành công.',
            'avatarUrl' => $avatarUrl,
        ]);
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
