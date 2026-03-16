<?php

namespace App\Http\Controllers\Admin\GiaoVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateNhanSuProfilePdfJob;
use App\Models\Auth\TaiKhoan;
use App\Services\Support\QueuedExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class GiaoVienController extends Controller
{
    public function __construct(
        protected NhanSuServiceInterface $nhanSuService,
        protected QueuedExportService $queuedExportService
        )
    {
        $this->middleware('permission:giao_vien,xem')->only('index', 'trash', 'show');
        $this->middleware('permission:giao_vien,them')->only('create', 'store', 'restore');
        $this->middleware('permission:giao_vien,sua')->only('edit', 'update');
        $this->middleware('permission:giao_vien,xoa')->only('destroy');
        $this->middleware('permission:nhan_su,sua')->only('storeDocument', 'storeSalaryPackage', 'archiveDocument');
        $this->middleware('permission:nhan_su,xem')->only('downloadDocument', 'downloadProfilePdf', 'downloadHandoverPdf');
    }

    public function index(Request $request)
    {
        $data = $this->nhanSuService->getList($request, TaiKhoan::ROLE_GIAO_VIEN);

        return view('admin.giao-vien.index', [
            'giaoViens' => $data['items'],
            'tongSo' => $data['tongSo'],
            'dangHoatDong' => $data['dangHoatDong'],
            'thangNay' => $data['thangNay'],
        ]);
    }

    public function create()
    {
        return view('admin.giao-vien.create', $this->nhanSuService->getCreateFormData(TaiKhoan::ROLE_GIAO_VIEN));
    }

    public function store(Request $request)
    {
        $result = $this->nhanSuService->store($request, TaiKhoan::ROLE_GIAO_VIEN);

        return redirect()->route('admin.giao-vien.show', [
            'taiKhoan' => $result->taiKhoan->taiKhoan,
            'handover' => $result->oneTimeToken,
        ])->with('success', 'Đã tạo giáo viên «' . $request->hoTen . '» thành công.');
    }

    public function show(Request $request, string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $handoverToken = $request->query('handover');
        $sessionKey = $handoverToken ? 'handover_seen:' . $handoverToken : null;

        if ($sessionKey && $request->session()->has($sessionKey)) {
            $handoverToken = null;
        } elseif ($sessionKey) {
            $request->session()->put($sessionKey, now()->toIso8601String());
        }

        return view('admin.giao-vien.show', $this->nhanSuService->getProfileData(
            $giaoVien,
            TaiKhoan::ROLE_GIAO_VIEN,
            $handoverToken
        ));
    }

    public function edit(string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        return view('admin.giao-vien.edit', $this->nhanSuService->getEditFormData($giaoVien, TaiKhoan::ROLE_GIAO_VIEN));
    }

    public function update(Request $request, string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $this->nhanSuService->update($request, $giaoVien);

        return redirect()->route('admin.giao-vien.index')
            ->with('success', 'Đã cập nhật thông tin giáo viên thành công.');
    }

    public function destroy(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->destroy($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

        return redirect()->route('admin.giao-vien.index')
            ->with('success', "Đã xóa giáo viên «{$hoTen}».");
    }

    public function trash(Request $request)
    {
        $data = $this->nhanSuService->getTrashList($request, TaiKhoan::ROLE_GIAO_VIEN);

        return view('admin.giao-vien.trash', [
            'giaoViens' => $data['items'],
            'tongXoa' => $data['tongXoa'],
        ]);
    }

    public function restore(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->restore($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

        return redirect()->route('admin.giao-vien.trash')
            ->with('success', "Đã khôi phục giáo viên «{$hoTen}» thành công.");
    }

    public function storeDocument(Request $request, string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $this->nhanSuService->uploadDocument($request, $giaoVien);

        return redirect()->route('admin.giao-vien.show', $giaoVien->taiKhoan)
            ->with('success', 'Đã tải tài liệu nhân sự lên thành công.');
    }

    public function downloadDocument(string $taiKhoan, int $documentId): BinaryFileResponse
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

        return $this->nhanSuService->downloadDocument($giaoVien, $documentId);
    }

    public function archiveDocument(string $taiKhoan, int $documentId)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $this->nhanSuService->archiveDocument($giaoVien, $documentId);

        return redirect()->route('admin.giao-vien.show', $giaoVien->taiKhoan)
            ->with('success', 'Đã lưu trữ tài liệu cũ.');
    }

    public function storeSalaryPackage(Request $request, string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $this->nhanSuService->saveSalaryPackage($request, $giaoVien);

        return redirect()->route('admin.giao-vien.show', $giaoVien->taiKhoan)
            ->with('success', 'Đã cập nhật gói lương hiện hành.');
    }

    public function downloadProfilePdf(string $taiKhoan): Response
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $context = [
            'taiKhoanId' => $giaoVien->taiKhoanId,
            'role' => TaiKhoan::ROLE_GIAO_VIEN,
        ];

        if ($response = $this->queuedExportService->downloadIfReady('nhan-su.profile-pdf', $context)) {
            return $response;
        }

        $state = $this->queuedExportService->get('nhan-su.profile-pdf', $context);
        if (($state['status'] ?? null) === 'queued') {
            return redirect()
                ->route('admin.giao-vien.show', $giaoVien->taiKhoan)
                ->with('info', 'PDF hồ sơ đang được tạo ở nền. Tải lại sau ít phút để nhận file.');
        }

        $filename = 'ho-so-nhan-su-' . $giaoVien->taiKhoan . '-' . now()->format('Ymd') . '.pdf';
        $this->queuedExportService->markQueued('nhan-su.profile-pdf', $context, [
            'filename' => $filename,
        ]);

        GenerateNhanSuProfilePdfJob::dispatch(
            $giaoVien->taiKhoanId,
            (string) TaiKhoan::ROLE_GIAO_VIEN,
            $filename
        )->afterCommit();

        return redirect()
            ->route('admin.giao-vien.show', $giaoVien->taiKhoan)
            ->with('success', 'Đã đưa PDF hồ sơ vào hàng chờ xuất. Worker queue sẽ tạo file ở nền.');
    }

    public function downloadHandoverPdf(string $taiKhoan, Request $request): Response
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

        return $this->nhanSuService->downloadHandoverPdf(
            $giaoVien,
            TaiKhoan::ROLE_GIAO_VIEN,
            (string) $request->query('token')
        );
    }
}
