<?php

namespace App\Http\Controllers\Teacher\TaiLieu;

use App\Http\Controllers\Controller;
use App\Models\Education\GiaoVienTaiLieu;
use App\Models\Education\LopHoc;
use App\Services\Education\GiaoVienTaiLieuService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Quản lý thư viện tài liệu cá nhân của giáo viên (teacher.materials.*).
 * File được upload vào đây trước, sau đó chia sẻ vào từng lớp.
 */
class TaiLieuController extends Controller
{
    public function __construct(
        protected GiaoVienTaiLieuService $service
    ) {}

    /* ── Danh sách thư viện ──────────────────────────────────────────────────── */

    public function index(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $nhom      = $request->query('nhom');
        $search    = $request->query('q');

        $taiLieus    = $this->service->list($teacherId, $nhom ?: null, $search ?: null);
        $nhomOptions = GiaoVienTaiLieu::nhomOptions();

        // Danh sách lớp để dùng trong modal chia sẻ
        $classes = LopHoc::where('taiKhoanId', $teacherId)
            ->orderBy('tenLopHoc')
            ->get(['lopHocId', 'tenLopHoc', 'slug']);

        return view('teacher.tai-lieu.index', compact('taiLieus', 'nhomOptions', 'nhom', 'search', 'classes'));
    }

    /* ── Tạo mới ─────────────────────────────────────────────────────────────── */

    public function create()
    {
        $nhomOptions = GiaoVienTaiLieu::nhomOptions();
        return view('teacher.tai-lieu.create', compact('nhomOptions'));
    }

    public function store(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $this->service->store($request, $teacherId);

        return redirect()
            ->route('teacher.materials.index')
            ->with('success', 'Đã tải tài liệu lên thư viện thành công.');
    }

    /* ── Chỉnh sửa ───────────────────────────────────────────────────────────── */

    public function edit(Request $request, int $id)
    {
        $teacherId   = $request->user()->getAuthIdentifier();
        $taiLieu     = $this->service->findForTeacher($id, $teacherId);
        $nhomOptions = GiaoVienTaiLieu::nhomOptions();

        return view('teacher.tai-lieu.edit', compact('taiLieu', 'nhomOptions'));
    }

    public function update(Request $request, int $id)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $taiLieu   = $this->service->findForTeacher($id, $teacherId);

        $this->service->update($request, $taiLieu);

        return redirect()
            ->route('teacher.materials.index')
            ->with('success', 'Đã cập nhật thông tin tài liệu.');
    }

    /* ── Xóa ─────────────────────────────────────────────────────────────────── */

    public function destroy(Request $request, int $id)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $taiLieu   = $this->service->findForTeacher($id, $teacherId);

        $this->service->destroy($taiLieu);

        return redirect()
            ->route('teacher.materials.index')
            ->with('success', 'Đã xóa tài liệu khỏi thư viện.');
    }

    /* ── Private download ────────────────────────────────────────────────────── */

    public function download(Request $request, int $id): StreamedResponse
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $taiLieu   = $this->service->findForTeacher($id, $teacherId);

        return $this->service->download($taiLieu, $teacherId);
    }
}
