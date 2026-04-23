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
        $taiLieuGroups = $this->service->groupForDisplay($taiLieus);

        return view('teacher.lop-hoc.materials.index', compact('lopHoc', 'taiLieus', 'taiLieuGroups'));
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
            'giaoVienTaiLieuId' => ['nullable', 'integer', 'exists:giao_vien_tai_lieu,giaoVienTaiLieuId'],
            'giaoVienTaiLieuIds' => ['nullable', 'array', 'min:1'],
            'giaoVienTaiLieuIds.*' => ['integer', 'distinct', 'exists:giao_vien_tai_lieu,giaoVienTaiLieuId'],
            'tieuDe'            => ['nullable', 'string', 'max:255'],
            'dotChiaSeTieuDe'   => ['nullable', 'string', 'max:255'],
            'moTa'              => ['nullable', 'string'],
            'sortOrder'         => ['nullable', 'integer', 'min:0', 'max:9999'],
            'trangThai'         => ['nullable', Rule::in(array_keys(LopHocTaiLieu::trangThaiOptions()))],
        ], [
            'giaoVienTaiLieuId.required' => 'Vui lòng chọn tài liệu từ thư viện.',
            'giaoVienTaiLieuIds.required' => 'Vui lòng chọn ít nhất một tài liệu từ thư viện.',
            'giaoVienTaiLieuIds.min'      => 'Vui lòng chọn ít nhất một tài liệu từ thư viện.',
            'giaoVienTaiLieuId.exists'   => 'Tài liệu không tồn tại.',
            'giaoVienTaiLieuIds.*.exists'=> 'Một trong các tài liệu đã chọn không tồn tại.',
        ]);

        $ids = collect($validated['giaoVienTaiLieuIds'] ?? []);
        if ($ids->isEmpty() && !empty($validated['giaoVienTaiLieuId'])) {
            $ids = collect([(int) $validated['giaoVienTaiLieuId']]);
        }

        if ($ids->isEmpty()) {
            return back()
                ->withErrors(['giaoVienTaiLieuIds' => 'Vui lòng chọn ít nhất một tài liệu từ thư viện.'])
                ->withInput();
        }

        if ($ids->count() === 1 && blank($validated['tieuDe'] ?? null)) {
            return back()
                ->withErrors(['tieuDe' => 'Vui lòng nhập tiêu đề.'])
                ->withInput();
        }

        $batchMeta = $this->service->makeShareBatchMeta($validated['dotChiaSeTieuDe'] ?? null);
        $isSingleShare = $ids->count() === 1;

        $result = $ids->values()->reduce(function (array $carry, int $id, int $index) use ($teacherId, $lopHoc, $validated, $batchMeta, $isSingleShare) {
            $nguon = $this->libraryService->findForTeacher($id, $teacherId);
            $title = $index === 0 && $isSingleShare
                ? ($validated['tieuDe'] ?? $nguon->tieuDe)
                : $nguon->tieuDe;

            $sharedItem = $this->shareLibraryItemToClass($lopHoc->lopHocId, $teacherId, $nguon, [
                'tieuDe' => $title,
                'moTa' => $validated['moTa'] ?? null,
                'sortOrder' => isset($validated['sortOrder']) ? ((int) $validated['sortOrder'] + $index) : $index,
                'trangThai' => $validated['trangThai'] ?? LopHocTaiLieu::TRANG_THAI_ACTIVE,
                'dotChiaSeKey' => $batchMeta['key'],
                'dotChiaSeTieuDe' => $batchMeta['title'],
                'dotChiaSeAt' => $batchMeta['sent_at'],
            ]);

            if ($sharedItem->wasRecentlyCreated) {
                $carry['created']++;
            } else {
                $carry['updated']++;
            }

            return $carry;
        }, ['created' => 0, 'updated' => 0]);

        return redirect()
            ->route('teacher.classes.materials.index', $slug)
            ->with('success', $this->buildShareSuccessMessage($result['created'], $result['updated']));
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

    private function shareLibraryItemToClass(int $lopHocId, int $teacherId, GiaoVienTaiLieu $nguon, array $payload): LopHocTaiLieu
    {
        return LopHocTaiLieu::updateOrCreate(
            [
                'lopHocId'          => $lopHocId,
                'giaoVienTaiLieuId' => $nguon->giaoVienTaiLieuId,
            ],
            [
                'dotChiaSeKey'  => $payload['dotChiaSeKey'],
                'dotChiaSeTieuDe' => $payload['dotChiaSeTieuDe'],
                'dotChiaSeAt'   => $payload['dotChiaSeAt'],
                'tieuDe'        => $payload['tieuDe'],
                'moTa'          => $payload['moTa'],
                'nhomTaiLieu'   => $nguon->nhomTaiLieu,
                'disk'          => $nguon->disk,
                'duongDan'      => $nguon->duongDan,
                'tenGoc'        => $nguon->tenGoc,
                'mime'          => $nguon->mime,
                'kichThuoc'     => $nguon->kichThuoc,
                'nguoiTaiLenId' => $teacherId,
                'publishedAt'   => now(),
                'sortOrder'     => $payload['sortOrder'],
                'trangThai'     => $payload['trangThai'],
            ]
        );
    }

    private function buildShareSuccessMessage(int $created, int $updated): string
    {
        if ($created > 0 && $updated === 0) {
            return $created === 1
                ? 'Đã chia sẻ tài liệu vào lớp học thành công.'
                : "Đã chia sẻ {$created} tài liệu vào lớp học thành công.";
        }

        if ($created === 0 && $updated > 0) {
            return $updated === 1
                ? 'Tài liệu đã có trong lớp, thông tin chia sẻ đã được cập nhật.'
                : "Đã cập nhật {$updated} tài liệu đã có sẵn trong lớp.";
        }

        return "Đã chia sẻ {$created} tài liệu mới và cập nhật {$updated} tài liệu đã có trong lớp.";
    }
}
