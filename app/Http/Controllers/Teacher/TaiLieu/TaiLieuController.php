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

        // Chỉ cho phép chia sẻ vào các lớp còn hiệu lực của giáo viên.
        $classes = LopHoc::where('taiKhoanId', $teacherId)
            ->with('khoaHoc')
            ->whereIn('trangThai', [
                LopHoc::TRANG_THAI_SAP_MO,
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                LopHoc::TRANG_THAI_DANG_HOC,
            ])
            ->orderBy('tenLopHoc')
            ->get(['lopHocId', 'khoaHocId', 'tenLopHoc', 'slug', 'trangThai']);

        $courses = $classes
            ->groupBy(fn (LopHoc $lopHoc) => (string) ($lopHoc->khoaHocId ?? 0))
            ->map(function ($items, string $courseId) {
                $first = $items->first();

                return [
                    'id' => $courseId,
                    'name' => $first?->khoaHoc?->tenKhoaHoc ?? 'Chưa gắn khóa học',
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('teacher.tai-lieu.index', compact('taiLieus', 'nhomOptions', 'nhom', 'search', 'classes', 'courses'));
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
        $createdItems = $this->service->store($request, $teacherId);
        $count = $createdItems->count();

        return redirect()
            ->route('teacher.materials.index')
            ->with('success', $count > 1
                ? "Đã tải {$count} tài liệu lên thư viện thành công."
                : 'Đã tải tài liệu lên thư viện thành công.');
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
