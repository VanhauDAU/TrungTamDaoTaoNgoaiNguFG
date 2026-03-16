<?php

namespace App\Http\Controllers\Admin\NhanVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateNhanSuProfilePdfJob;
use App\Models\Auth\TaiKhoan;
use App\Services\Support\QueuedExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class NhanVienController extends Controller
{
    public function __construct(
        protected NhanSuServiceInterface $nhanSuService,
        protected QueuedExportService $queuedExportService
        )
    {
        $this->middleware('permission:nhan_vien,xem')->only('index', 'trash', 'show');
        $this->middleware('permission:nhan_vien,them')->only('create', 'store', 'restore');
        $this->middleware('permission:nhan_vien,sua')->only('edit', 'update');
        $this->middleware('permission:nhan_vien,xoa')->only('destroy');
        $this->middleware('permission:nhan_su,sua')->only('storeDocument', 'storeSalaryPackage', 'archiveDocument');
        $this->middleware('permission:nhan_su,xem')->only('downloadDocument', 'downloadProfilePdf', 'downloadHandoverPdf');
    }

    public function index(Request $request)
    {
        $data = $this->nhanSuService->getList($request, TaiKhoan::ROLE_NHAN_VIEN);

        return view('admin.nhan-vien.index', [
            'nhanViens' => $data['items'],
            'tongSo' => $data['tongSo'],
            'dangHoatDong' => $data['dangHoatDong'],
            'thangNay' => $data['thangNay'],
        ]);
    }

    public function create()
    {
        return view('admin.nhan-vien.create', $this->nhanSuService->getCreateFormData(TaiKhoan::ROLE_NHAN_VIEN));
    }

    public function store(Request $request)
    {
        $result = $this->nhanSuService->store($request, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.show', [
            'taiKhoan' => $result->taiKhoan->taiKhoan,
            'handover' => $result->oneTimeToken,
        ])->with('success', 'Đã tạo nhân viên «' . $request->hoTen . '» thành công.');
    }

    public function show(Request $request, string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $handoverToken = $request->query('handover');
        $sessionKey = $handoverToken ? 'handover_seen:' . $handoverToken : null;

        if ($sessionKey && $request->session()->has($sessionKey)) {
            $handoverToken = null;
        } elseif ($sessionKey) {
            $request->session()->put($sessionKey, now()->toIso8601String());
        }

        return view('admin.nhan-vien.show', $this->nhanSuService->getProfileData(
            $nhanVien,
            TaiKhoan::ROLE_NHAN_VIEN,
            $handoverToken
        ));
    }

    public function edit(string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        return view('admin.nhan-vien.edit', $this->nhanSuService->getEditFormData($nhanVien, TaiKhoan::ROLE_NHAN_VIEN));
    }

    public function update(Request $request, string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $this->nhanSuService->update($request, $nhanVien);

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', 'Đã cập nhật nhân viên «' . $request->hoTen . '» thành công.');
    }

    public function destroy(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->destroy($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', "Đã xóa nhân viên «{$hoTen}».");
    }

    public function trash(Request $request)
    {
        $data = $this->nhanSuService->getTrashList($request, TaiKhoan::ROLE_NHAN_VIEN);

        return view('admin.nhan-vien.trash', [
            'nhanViens' => $data['items'],
            'tongXoa' => $data['tongXoa'],
        ]);
    }

    public function restore(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->restore($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.trash')
            ->with('success', "Đã khôi phục nhân viên «{$hoTen}» thành công.");
    }

    public function storeDocument(Request $request, string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $this->nhanSuService->uploadDocument($request, $nhanVien);

        return redirect()->route('admin.nhan-vien.show', $nhanVien->taiKhoan)
            ->with('success', 'Đã tải tài liệu nhân sự lên thành công.');
    }

    public function downloadDocument(string $taiKhoan, int $documentId): BinaryFileResponse
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return $this->nhanSuService->downloadDocument($nhanVien, $documentId);
    }

    public function archiveDocument(string $taiKhoan, int $documentId)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $this->nhanSuService->archiveDocument($nhanVien, $documentId);

        return redirect()->route('admin.nhan-vien.show', $nhanVien->taiKhoan)
            ->with('success', 'Đã lưu trữ tài liệu cũ.');
    }

    public function storeSalaryPackage(Request $request, string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $this->nhanSuService->saveSalaryPackage($request, $nhanVien);

        return redirect()->route('admin.nhan-vien.show', $nhanVien->taiKhoan)
            ->with('success', 'Đã cập nhật gói lương hiện hành.');
    }

    public function downloadProfilePdf(string $taiKhoan): Response
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $context = [
            'taiKhoanId' => $nhanVien->taiKhoanId,
            'role' => TaiKhoan::ROLE_NHAN_VIEN,
        ];

        if ($response = $this->queuedExportService->downloadIfReady('nhan-su.profile-pdf', $context)) {
            return $response;
        }

        $state = $this->queuedExportService->get('nhan-su.profile-pdf', $context);
        if (($state['status'] ?? null) === 'queued') {
            return redirect()
                ->route('admin.nhan-vien.show', $nhanVien->taiKhoan)
                ->with('info', 'PDF hồ sơ đang được tạo ở nền. Tải lại sau ít phút để nhận file.');
        }

        $filename = 'ho-so-nhan-su-' . $nhanVien->taiKhoan . '-' . now()->format('Ymd') . '.pdf';
        $this->queuedExportService->markQueued('nhan-su.profile-pdf', $context, [
            'filename' => $filename,
        ]);

        GenerateNhanSuProfilePdfJob::dispatch(
            $nhanVien->taiKhoanId,
            (string) TaiKhoan::ROLE_NHAN_VIEN,
            $filename
        )->afterCommit();

        return redirect()
            ->route('admin.nhan-vien.show', $nhanVien->taiKhoan)
            ->with('success', 'Đã đưa PDF hồ sơ vào hàng chờ xuất. Worker queue sẽ tạo file ở nền.');
    }

    public function downloadHandoverPdf(string $taiKhoan, Request $request): Response
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return $this->nhanSuService->downloadHandoverPdf(
            $nhanVien,
            TaiKhoan::ROLE_NHAN_VIEN,
            (string) $request->query('token')
        );
    }
}
