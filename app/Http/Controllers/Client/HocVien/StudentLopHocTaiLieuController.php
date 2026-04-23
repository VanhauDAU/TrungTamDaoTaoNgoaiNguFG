<?php

namespace App\Http\Controllers\Client\HocVien;

use App\Http\Controllers\Controller;
use App\Models\Education\LopHocTaiLieu;
use App\Services\Education\LopHocTaiLieuService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;


class StudentLopHocTaiLieuController extends Controller
{
    public function __construct(
        protected LopHocTaiLieuService $service
    ) {}

    /**
     * Danh sách tài liệu của lớp (read-only, học viên xem).
     */
    public function index(Request $request, int $lopHocId)
    {
        $studentId = $request->user()->getAuthIdentifier();
        $this->service->assertStudentCanAccess($lopHocId, $studentId);

        $taiLieus = LopHocTaiLieu::where('lopHocId', $lopHocId)
            ->active()
            ->ordered()
            ->get();
        $taiLieuGroups = $this->service->groupForDisplay($taiLieus);

        $lopHoc = \App\Models\Education\LopHoc::findOrFail($lopHocId);

        return view('clients.hoc-vien.lop-hoc-tai-lieu.index', compact('lopHoc', 'taiLieus', 'taiLieuGroups'));
    }

    /**
     * Download tài liệu – kiểm quyền theo DangKyLopHoc.
     */
    public function download(Request $request, int $lopHocId, int $id): StreamedResponse
    {
        $studentId = $request->user()->getAuthIdentifier();

        $taiLieu = LopHocTaiLieu::where('lopHocId', $lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        return $this->service->downloadForStudent($taiLieu, $studentId);
    }
}
