<?php

namespace App\Http\Controllers\Teacher\LopHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\GiaoVienTaiLieu;
use App\Models\Education\LopHocTaiLieu;
use App\Services\Education\GiaoVienTaiLieuService;
use App\Services\Education\LopHocTaiLieuService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LopHocTaiLieuController extends Controller
{
    public function __construct(
        protected LopHocTaiLieuService   $service,
        protected GiaoVienTaiLieuService $libraryService
    ) {}

    /* ── Danh sách tài liệu của lớp ─────────────────────────────────────────── */

    public function index(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieus  = $this->service->list($lopHoc);

        return view('teacher.lop-hoc.materials.index', compact('lopHoc', 'taiLieus'));
    }

    /* ── Chọn từ thư viện để chia sẻ vào lớp ────────────────────────────────── */

    /**
     * Hiển thị danh sách thư viện cá nhân để giáo viên chọn chia sẻ vào lớp.
     * GET /teacher/lop-hoc-cua-toi/{slug}/tai-lieu/chon-tu-thu-vien
     */
    public function selectFromLibrary(Request $request, string $slug)
    {
        $teacherId   = $request->user()->getAuthIdentifier();
        $lopHoc      = $this->service->findLopHocForTeacher($slug, $teacherId);
        $nhom        = $request->query('nhom');
        $search      = $request->query('q');

        $thuVien = $this->libraryService->list($teacherId, $nhom ?: null, $search ?: null);
        $nhomOptions = GiaoVienTaiLieu::nhomOptions();

        // IDs đã chia sẻ vào lớp này (để đánh dấu UI)
        $sharedIds = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->whereNotNull('giaoVienTaiLieuId')
            ->pluck('giaoVienTaiLieuId')
            ->toArray();

        return view('teacher.lop-hoc.materials.select-from-library', compact(
            'lopHoc', 'thuVien', 'nhomOptions', 'nhom', 'search', 'sharedIds'
        ));
    }

    /**
     * Chia sẻ tài liệu từ thư viện vào lớp học.
     * POST /teacher/lop-hoc-cua-toi/{slug}/tai-lieu/chia-se
     */
    public function storeShared(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);

        $validated = $request->validate([
            'giaoVienTaiLieuId' => ['required', 'integer', 'exists:giao_vien_tai_lieu,giaoVienTaiLieuId'],
            'tieuDe'            => ['required', 'string', 'max:255'],
            'moTa'              => ['nullable', 'string'],
            'nhomTaiLieu'       => ['required', Rule::in(array_keys(LopHocTaiLieu::nhomOptions()))],
            'sortOrder'         => ['nullable', 'integer', 'min:0', 'max:9999'],
            'trangThai'         => ['nullable', Rule::in(array_keys(LopHocTaiLieu::trangThaiOptions()))],
        ], [
            'giaoVienTaiLieuId.required' => 'Vui lòng chọn tài liệu từ thư viện.',
            'giaoVienTaiLieuId.exists'   => 'Tài liệu không tồn tại.',
            'tieuDe.required'            => 'Vui lòng nhập tiêu đề.',
            'nhomTaiLieu.required'       => 'Vui lòng chọn nhóm tài liệu.',
        ]);

        // Lấy tài liệu gốc (phải thuộc giáo viên này)
        $nguon = $this->libraryService->findForTeacher(
            (int) $validated['giaoVienTaiLieuId'],
            $teacherId
        );

        // Tạo bản ghi chia sẻ (sao chép metadata file, giữ reference)
        LopHocTaiLieu::create([
            'lopHocId'          => $lopHoc->lopHocId,
            'giaoVienTaiLieuId' => $nguon->giaoVienTaiLieuId,
            'tieuDe'            => $validated['tieuDe'],
            'moTa'              => $validated['moTa'] ?? null,
            'nhomTaiLieu'       => $validated['nhomTaiLieu'],
            'disk'              => $nguon->disk,
            'duongDan'          => $nguon->duongDan,
            'tenGoc'            => $nguon->tenGoc,
            'mime'              => $nguon->mime,
            'kichThuoc'         => $nguon->kichThuoc,
            'nguoiTaiLenId'     => $teacherId,
            'publishedAt'       => now(),
            'sortOrder'         => $validated['sortOrder'] ?? 0,
            'trangThai'         => $validated['trangThai'] ?? LopHocTaiLieu::TRANG_THAI_ACTIVE,
        ]);

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', 'Đã chia sẻ tài liệu vào lớp học thành công.');
    }

    /* ── Tạo mới (upload thẳng vào lớp – giữ tương thích) ─────────────────── */

    public function create(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);

        return view('teacher.lop-hoc.materials.create', [
            'lopHoc'          => $lopHoc,
            'nhomOptions'     => LopHocTaiLieu::nhomOptions(),
            'trangThaiOptions'=> LopHocTaiLieu::trangThaiOptions(),
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
            'lopHoc'          => $lopHoc,
            'taiLieu'         => $taiLieu,
            'nhomOptions'     => LopHocTaiLieu::nhomOptions(),
            'trangThaiOptions'=> LopHocTaiLieu::trangThaiOptions(),
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

        // Nếu là bản ghi chia sẻ từ thư viện → chỉ xóa record, KHÔNG xóa file vật lý
        // vì file gốc vẫn còn trong thư viện cá nhân
        if ($taiLieu->giaoVienTaiLieuId) {
            $taiLieu->delete();
        } else {
            // Upload thẳng → xóa cả file vật lý
            $this->service->destroy($taiLieu);
        }

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', 'Đã xóa tài liệu khỏi lớp học.');
    }

    /* ── Download (private) ─────────────────────────────────────────────────── */

    public function download(Request $request, string $slug, int $id): StreamedResponse
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $lopHoc    = $this->service->findLopHocForTeacher($slug, $teacherId);
        $taiLieu   = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->where('lopHocTaiLieuId', $id)
            ->firstOrFail();

        return $this->service->downloadForTeacher($taiLieu);
    }
}
