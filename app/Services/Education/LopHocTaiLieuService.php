<?php

namespace App\Services\Education;

use App\Models\Education\LopHoc;
use App\Models\Education\LopHocTaiLieu;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LopHocTaiLieuService
{
    /* ── Lấy lớp thuộc giáo viên (khóa theo owner) ─────────────────────────── */

    public function findLopHocForTeacher(string $slug, int $teacherId): LopHoc
    {
        return LopHoc::where('slug', $slug)
            ->where('taiKhoanId', $teacherId)
            ->firstOrFail();
    }

    public function findLopHocByIdForTeacher(int $lopHocId, int $teacherId): LopHoc
    {
        return LopHoc::where('lopHocId', $lopHocId)
            ->where('taiKhoanId', $teacherId)
            ->firstOrFail();
    }

    /* ── CRUD ───────────────────────────────────────────────────────────────── */

    public function list(LopHoc $lopHoc): \Illuminate\Database\Eloquent\Collection
    {
        return $lopHoc->lopHocTaiLieus()
            ->with('nguoiTaiLen')
            ->get();
    }

    public function store(Request $request, LopHoc $lopHoc): LopHocTaiLieu
    {
        $validated = $this->validateStoreRequest($request);

        return DB::transaction(function () use ($validated, $request, $lopHoc) {
            $file = $request->file('tep');
            [$disk, $path, $tenGoc, $mime, $size] = $this->uploadFile($file, $lopHoc->lopHocId);

            return LopHocTaiLieu::create([
                'lopHocId'     => $lopHoc->lopHocId,
                'tieuDe'       => $validated['tieuDe'],
                'moTa'         => $validated['moTa'] ?? null,
                'nhomTaiLieu'  => $validated['nhomTaiLieu'],
                'disk'         => $disk,
                'duongDan'     => $path,
                'tenGoc'       => $tenGoc,
                'mime'         => $mime,
                'kichThuoc'    => $size,
                'nguoiTaiLenId'=> auth()->id(),
                'publishedAt'  => $validated['publishedAt'] ?? now(),
                'sortOrder'    => $validated['sortOrder'] ?? 0,
                'trangThai'    => $validated['trangThai'] ?? LopHocTaiLieu::TRANG_THAI_ACTIVE,
            ]);
        });
    }

    public function update(Request $request, LopHocTaiLieu $taiLieu): void
    {
        $validated = $this->validateUpdateRequest($request, $taiLieu);

        DB::transaction(function () use ($validated, $request, $taiLieu) {
            $updateData = [
                'tieuDe'      => $validated['tieuDe'],
                'moTa'        => $validated['moTa'] ?? null,
                'nhomTaiLieu' => $validated['nhomTaiLieu'],
                'publishedAt' => $validated['publishedAt'] ?? $taiLieu->publishedAt,
                'sortOrder'   => $validated['sortOrder'] ?? $taiLieu->sortOrder,
                'trangThai'   => $validated['trangThai'],
            ];

            // Nếu upload file mới thì thay thế
            if ($request->hasFile('tep')) {
                $this->deleteFilePhysically($taiLieu);
                [$disk, $path, $tenGoc, $mime, $size] = $this->uploadFile(
                    $request->file('tep'),
                    $taiLieu->lopHocId
                );
                $updateData = array_merge($updateData, [
                    'disk'      => $disk,
                    'duongDan'  => $path,
                    'tenGoc'    => $tenGoc,
                    'mime'      => $mime,
                    'kichThuoc' => $size,
                ]);
            }

            $taiLieu->update($updateData);
        });
    }

    public function destroy(LopHocTaiLieu $taiLieu): void
    {
        DB::transaction(function () use ($taiLieu) {
            $this->deleteFilePhysically($taiLieu);
            $taiLieu->delete();
        });
    }

    /* ── Private Download (teacher) ─────────────────────────────────────────── */

    public function downloadForTeacher(LopHocTaiLieu $taiLieu): StreamedResponse
    {
        abort_unless(
            Storage::disk($taiLieu->disk)->exists($taiLieu->duongDan),
            404,
            'File không tồn tại trên server.'
        );

        return Storage::disk($taiLieu->disk)->download($taiLieu->duongDan, $taiLieu->tenGoc);
    }

    /* ── Private Download (student) ─────────────────────────────────────────── */

    /**
     * Kiểm tra học viên có quyền tải file không (dựa trên DangKyLopHoc).
     * Các trạng thái cho phép: Đã xác nhận, Đang học, Tạm dừng nợ, Bảo lưu, Hoàn thành.
     */
    public function downloadForStudent(LopHocTaiLieu $taiLieu, int $studentId): StreamedResponse
    {
        $this->assertStudentCanAccess($taiLieu->lopHocId, $studentId);

        abort_unless(
            (int) $taiLieu->trangThai === LopHocTaiLieu::TRANG_THAI_ACTIVE,
            403,
            'Tài liệu này hiện không được phép tải.'
        );

        abort_unless(
            Storage::disk($taiLieu->disk)->exists($taiLieu->duongDan),
            404,
            'File không tồn tại trên server.'
        );

        return Storage::disk($taiLieu->disk)->download($taiLieu->duongDan, $taiLieu->tenGoc);
    }

    public function assertStudentCanAccess(int $lopHocId, int $studentId): void
    {
        $allowedStatuses = [
            \App\Models\Education\DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
            \App\Models\Education\DangKyLopHoc::TRANG_THAI_DANG_HOC,
            \App\Models\Education\DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
            \App\Models\Education\DangKyLopHoc::TRANG_THAI_BAO_LUU,
            \App\Models\Education\DangKyLopHoc::TRANG_THAI_HOAN_THANH,
        ];

        $exists = \App\Models\Education\DangKyLopHoc::where('lopHocId', $lopHocId)
            ->where('taiKhoanId', $studentId)
            ->whereIn('trangThai', $allowedStatuses)
            ->exists();

        abort_unless($exists, 403, 'Bạn không có quyền truy cập tài liệu của lớp học này.');
    }

    /* ── Helpers ────────────────────────────────────────────────────────────── */

    /**
     * Upload file vào disk=local, đường dẫn storage/app/private/lop-hoc/{lopHocId}/...
     * Trả về [$disk, $storedPath, $tenGoc, $mime, $size]
     */
    private function uploadFile(UploadedFile $file, int $lopHocId): array
    {
        $disk       = 'local';
        $tenGoc     = $file->getClientOriginalName();
        $mime       = $file->getMimeType();
        $size       = $file->getSize();
        $extension  = $file->getClientOriginalExtension();
        $safeName   = Str::slug(pathinfo($tenGoc, PATHINFO_FILENAME));
        $timestamp  = now()->format('Ymd_His');
        $storedName = $timestamp . '_' . $safeName . ($extension ? '.' . $extension : '');

        $storedPath = $file->storeAs(
            'lop-hoc/' . $lopHocId,
            $storedName,
            $disk
        );

        return [$disk, $storedPath, $tenGoc, $mime, $size];
    }

    /**
     * Xóa file vật lý an toàn (không ném exception nếu đã xóa rồi).
     */
    private function deleteFilePhysically(LopHocTaiLieu $taiLieu): void
    {
        try {
            if (Storage::disk($taiLieu->disk)->exists($taiLieu->duongDan)) {
                Storage::disk($taiLieu->disk)->delete($taiLieu->duongDan);
            }
        } catch (\Throwable) {
            // Bỏ qua nếu file không còn trên disk
        }
    }

    /* ── Validation ─────────────────────────────────────────────────────────── */

    private function validateStoreRequest(Request $request): array
    {
        return Validator::make($request->all(), [
            'tieuDe'       => ['required', 'string', 'max:255'],
            'moTa'         => ['nullable', 'string'],
            'nhomTaiLieu'  => ['required', Rule::in(array_keys(LopHocTaiLieu::nhomOptions()))],
            'publishedAt'  => ['nullable', 'date'],
            'sortOrder'    => ['nullable', 'integer', 'min:0', 'max:9999'],
            'trangThai'    => ['nullable', Rule::in(array_keys(LopHocTaiLieu::trangThaiOptions()))],
            'tep'          => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,mp3,mp4,zip'],
        ], $this->messages())->validate();
    }

    private function validateUpdateRequest(Request $request, LopHocTaiLieu $taiLieu): array
    {
        return Validator::make($request->all(), [
            'tieuDe'       => ['required', 'string', 'max:255'],
            'moTa'         => ['nullable', 'string'],
            'nhomTaiLieu'  => ['required', Rule::in(array_keys(LopHocTaiLieu::nhomOptions()))],
            'publishedAt'  => ['nullable', 'date'],
            'sortOrder'    => ['nullable', 'integer', 'min:0', 'max:9999'],
            'trangThai'    => ['required', Rule::in(array_keys(LopHocTaiLieu::trangThaiOptions()))],
            'tep'          => ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,mp3,mp4,zip'],
        ], $this->messages())->validate();
    }

    private function messages(): array
    {
        return [
            'tieuDe.required'      => 'Vui lòng nhập tiêu đề tài liệu.',
            'tieuDe.max'           => 'Tiêu đề không được vượt quá 255 ký tự.',
            'nhomTaiLieu.required' => 'Vui lòng chọn nhóm tài liệu.',
            'nhomTaiLieu.in'       => 'Nhóm tài liệu không hợp lệ.',
            'tep.required'         => 'Vui lòng chọn file tải lên.',
            'tep.mimes'            => 'Định dạng file chưa được hỗ trợ.',
            'tep.max'              => 'File không được vượt quá 50MB.',
            'trangThai.required'   => 'Vui lòng chọn trạng thái.',
            'trangThai.in'         => 'Trạng thái không hợp lệ.',
        ];
    }
}
