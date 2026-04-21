<?php

namespace App\Http\Controllers\Teacher\LopHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\LopHocTaiLieu;
use App\Services\Education\LopHocTaiLieuService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LopHocTaiLieuController extends Controller
{
    public function __construct(
        protected LopHocTaiLieuService $service
    ) {}

    /* ── Danh sách ──────────────────────────────────────────────────────────── */

    public function index(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieus  = $this->service->list($lopHoc);

        return view('teacher.lop-hoc.materials.index', compact('lopHoc', 'taiLieus'));
    }

    /* ── Tạo mới ────────────────────────────────────────────────────────────── */

    public function create(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);

        return view('teacher.lop-hoc.materials.create', [
            'lopHoc'       => $lopHoc,
            'nhomOptions'  => LopHocTaiLieu::nhomOptions(),
            'trangThaiOptions' => LopHocTaiLieu::trangThaiOptions(),
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);

        $this->service->store($request, $lopHoc);

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', 'Đã tải tài liệu lên thành công.');
    }

    /* ── Chỉnh sửa ──────────────────────────────────────────────────────────── */

    public function edit(Request $request, string $slug, int $id)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieu   = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        return view('teacher.lop-hoc.materials.edit', [
            'lopHoc'       => $lopHoc,
            'taiLieu'      => $taiLieu,
            'nhomOptions'  => LopHocTaiLieu::nhomOptions(),
            'trangThaiOptions' => LopHocTaiLieu::trangThaiOptions(),
        ]);
    }

    public function update(Request $request, string $slug, int $id)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieu   = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        $this->service->update($request, $taiLieu);

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', 'Đã cập nhật tài liệu thành công.');
    }

    /* ── Xóa ────────────────────────────────────────────────────────────────── */

    public function destroy(Request $request, string $slug, int $id)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieu   = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        $this->service->destroy($taiLieu);

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', 'Đã xóa tài liệu.');
    }

    /* ── Download (private) ─────────────────────────────────────────────────── */

    public function download(Request $request, string $slug, int $id): BinaryFileResponse
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieu   = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        return $this->service->downloadForTeacher($taiLieu);
    }
}
