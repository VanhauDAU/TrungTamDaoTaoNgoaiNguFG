<?php

namespace App\Services\Education;

use App\Models\Education\GiaoVienTaiLieu;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GiaoVienTaiLieuService
{
    /* ── CRUD ───────────────────────────────────────────────────────────────── */

    /**
     * Danh sách tài liệu cá nhân của giáo viên, có thể lọc theo nhóm.
     */
    public function list(int $teacherId, ?string $nhom = null, ?string $search = null)
    {
        $q = GiaoVienTaiLieu::ofTeacher($teacherId)
            ->orderBy('giaoVienTaiLieuId', 'desc');

        if ($nhom) {
            $q->byNhom($nhom);
        }

        if ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('tieuDe', 'like', '%' . $search . '%')
                   ->orWhere('tenGoc', 'like', '%' . $search . '%');
            });
        }

        return $q->get();
    }

    /**
     * Upload file mới vào thư viện cá nhân.
     */
    public function store(Request $request, int $teacherId): GiaoVienTaiLieu
    {
        $validated = $this->validateStoreRequest($request);

        return DB::transaction(function () use ($validated, $request, $teacherId) {
            $file = $request->file('tep');
            [$disk, $path, $tenGoc, $mime, $size] = $this->uploadFile($file, $teacherId);

            return GiaoVienTaiLieu::create([
                'nguoiTaiLenId' => $teacherId,
                'tieuDe'        => $validated['tieuDe'],
                'moTa'          => $validated['moTa'] ?? null,
                'nhomTaiLieu'   => $validated['nhomTaiLieu'],
                'disk'          => $disk,
                'duongDan'      => $path,
                'tenGoc'        => $tenGoc,
                'mime'          => $mime,
                'kichThuoc'     => $size,
            ]);
        });
    }

    /**
     * Cập nhật metadata hoặc thay thế file.
     */
    public function update(Request $request, GiaoVienTaiLieu $taiLieu): void
    {
        $validated = $this->validateUpdateRequest($request, $taiLieu);

        DB::transaction(function () use ($validated, $request, $taiLieu) {
            $updateData = [
                'tieuDe'      => $validated['tieuDe'],
                'moTa'        => $validated['moTa'] ?? null,
                'nhomTaiLieu' => $validated['nhomTaiLieu'],
            ];

            // Nếu upload file mới thì thay thế
            if ($request->hasFile('tep')) {
                $this->deleteFilePhysically($taiLieu);
                [$disk, $path, $tenGoc, $mime, $size] = $this->uploadFile(
                    $request->file('tep'),
                    (int) $taiLieu->nguoiTaiLenId
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

    /**
     * Xóa tài liệu + file vật lý.
     */
    public function destroy(GiaoVienTaiLieu $taiLieu): void
    {
        DB::transaction(function () use ($taiLieu) {
            $this->deleteFilePhysically($taiLieu);
            $taiLieu->delete();
        });
    }

    /* ── Download ────────────────────────────────────────────────────────────── */

    public function download(GiaoVienTaiLieu $taiLieu, int $teacherId): StreamedResponse
    {
        abort_unless(
            (int) $taiLieu->nguoiTaiLenId === $teacherId,
            403,
            'Bạn không có quyền tải file này.'
        );

        abort_unless(
            Storage::disk($taiLieu->disk)->exists($taiLieu->duongDan),
            404,
            'File không tồn tại trên server.'
        );

        return Storage::disk($taiLieu->disk)->download($taiLieu->duongDan, $taiLieu->tenGoc);
    }

    /* ── Find / Auth ─────────────────────────────────────────────────────────── */

    public function findForTeacher(int $id, int $teacherId): GiaoVienTaiLieu
    {
        return GiaoVienTaiLieu::where('giaoVienTaiLieuId', $id)
            ->where('nguoiTaiLenId', $teacherId)
            ->firstOrFail();
    }

    /* ── Private helpers ─────────────────────────────────────────────────────── */

    /**
     * Lưu file vào disk=local, thư mục giao-vien/{teacherId}/...
     */
    private function uploadFile(UploadedFile $file, int $teacherId): array
    {
        $disk      = 'local';
        $tenGoc    = $file->getClientOriginalName();
        $mime      = $file->getMimeType();
        $size      = $file->getSize();
        $extension = $file->getClientOriginalExtension();
        $safeName  = Str::slug(pathinfo($tenGoc, PATHINFO_FILENAME));
        $timestamp = now()->format('Ymd_His');
        $storedName = $timestamp . '_' . $safeName . ($extension ? '.' . $extension : '');

        $storedPath = $file->storeAs(
            'giao-vien/' . $teacherId,
            $storedName,
            $disk
        );

        return [$disk, $storedPath, $tenGoc, $mime, $size];
    }

    private function deleteFilePhysically(GiaoVienTaiLieu $taiLieu): void
    {
        try {
            if (Storage::disk($taiLieu->disk)->exists($taiLieu->duongDan)) {
                Storage::disk($taiLieu->disk)->delete($taiLieu->duongDan);
            }
        } catch (\Throwable) {
            // Bỏ qua nếu file không còn trên disk
        }
    }

    /* ── Validation ──────────────────────────────────────────────────────────── */

    private function validateStoreRequest(Request $request): array
    {
        return Validator::make($request->all(), [
            'tieuDe'      => ['required', 'string', 'max:255'],
            'moTa'        => ['nullable', 'string'],
            'nhomTaiLieu' => ['required', Rule::in(array_keys(GiaoVienTaiLieu::nhomOptions()))],
            'tep'         => ['required', 'file', 'max:51200',
                              'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,mp3,mp4,zip'],
        ], $this->messages())->validate();
    }

    private function validateUpdateRequest(Request $request, GiaoVienTaiLieu $taiLieu): array
    {
        return Validator::make($request->all(), [
            'tieuDe'      => ['required', 'string', 'max:255'],
            'moTa'        => ['nullable', 'string'],
            'nhomTaiLieu' => ['required', Rule::in(array_keys(GiaoVienTaiLieu::nhomOptions()))],
            'tep'         => ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,mp3,mp4,zip'],
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
        ];
    }
}
