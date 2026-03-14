<?php

namespace App\Services\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\KhoaHocServiceInterface;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KhoaHocService implements KhoaHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = KhoaHoc::with(['danhMuc', 'lopHoc']);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenKhoaHoc', 'like', "%{$search}%")
                  ->orWhere('moTa', 'like', "%{$search}%")
                  ->orWhere('doiTuong', 'like', "%{$search}%");
            });
        }
        if ($request->filled('danhMucId'))  $query->where('danhMucId', $request->danhMucId);
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'khoaHocId');
        $dir     = $request->get('dir', 'desc');
        if (in_array($orderBy, ['khoaHocId', 'tenKhoaHoc'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        return [
            'khoaHocs'     => $query->paginate(12)->withQueryString(),
            'tongSo'       => KhoaHoc::count(),
            'dangHoatDong' => KhoaHoc::where('trangThai', 1)->count(),
            'tongLopHoc'   => LopHoc::count(),
            'danhMucs'     => DanhMucKhoaHoc::ordered()->get(),
        ];
    }

    public function getCreateFormData(): array
    {
        return ['flatTree' => DanhMucKhoaHoc::buildFlatTree()];
    }

    public function getDetail(string $slug): array
    {
        $khoaHoc = KhoaHoc::with([
            'danhMuc',
            'lopHoc.coSo',
            'lopHoc.caHoc',
            'lopHoc.taiKhoan.hoSoNguoiDung',
        ])->where('slug', $slug)->firstOrFail();

        return [
            'khoaHoc'     => $khoaHoc,
            'tongLop'     => $khoaHoc->lopHoc->count(),
            'lopDangHoc'  => $khoaHoc->lopHoc->where('trangThai', LopHoc::TRANG_THAI_DANG_HOC)->count(),
            'lopSapMo'    => $khoaHoc->lopHoc->where('trangThai', LopHoc::TRANG_THAI_SAP_MO)->count(),
            'tongHocVien' => $khoaHoc->lopHoc->sum(fn($l) => $l->dangKyLopHocs()->count() ?? 0),
        ];
    }

    public function getEditFormData(string $slug): array
    {
        return [
            'khoaHoc'  => KhoaHoc::where('slug', $slug)->firstOrFail(),
            'flatTree' => DanhMucKhoaHoc::buildFlatTree(),
        ];
    }

    public function store(Request $request): KhoaHoc
    {
        $data = $this->validateKhoaHoc($request);

        if ($request->hasFile('anhKhoaHoc')) {
            $data['anhKhoaHoc'] = $request->file('anhKhoaHoc')->store('khoa-hoc', 'public');
        }

        $data['slug']      = $this->generateUniqueSlug($request->tenKhoaHoc);
        $data['maKhoaHoc'] = KhoaHoc::generateMaKhoaHoc($request->danhMucId);

        return KhoaHoc::create($data);
    }

    public function update(Request $request, string $slug): KhoaHoc
    {
        $khoaHoc = KhoaHoc::where('slug', $slug)->firstOrFail();
        $id      = $khoaHoc->khoaHocId;
        $data    = $this->validateKhoaHoc($request);

        if ($request->hasFile('anhKhoaHoc')) {
            if ($khoaHoc->anhKhoaHoc && Storage::disk('public')->exists($khoaHoc->anhKhoaHoc)) {
                Storage::disk('public')->delete($khoaHoc->anhKhoaHoc);
            }
            $data['anhKhoaHoc'] = $request->file('anhKhoaHoc')->store('khoa-hoc', 'public');
        } else {
            unset($data['anhKhoaHoc']);
        }

        if ($request->tenKhoaHoc !== $khoaHoc->tenKhoaHoc) {
            $data['slug'] = $this->generateUniqueSlug($request->tenKhoaHoc, $id);
        }

        $khoaHoc->update($data);
        return $khoaHoc;
    }

    public function destroy(string $slug): string
    {
        $khoaHoc = KhoaHoc::where('slug', $slug)->firstOrFail();

        $lopDangHoatDong = $khoaHoc->lopHoc()
            ->whereIn('trangThai', [
                LopHoc::TRANG_THAI_SAP_MO,
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                LopHoc::TRANG_THAI_DANG_HOC,
            ])->count();

        if ($lopDangHoatDong > 0) {
            throw new \RuntimeException(
                "Không thể lưu trữ «{$khoaHoc->tenKhoaHoc}» — còn {$lopDangHoatDong} lớp học đang hoạt động. Hãy đóng hoặc hủy các lớp trước."
            );
        }

        $ten = $khoaHoc->tenKhoaHoc;
        $khoaHoc->delete();
        return $ten;
    }

    public function restore(string $slug): KhoaHoc
    {
        $khoaHoc = KhoaHoc::withTrashed()->where('slug', $slug)->firstOrFail();
        $khoaHoc->restore();
        return $khoaHoc;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────────────────────────────────

    private function validateKhoaHoc(Request $request): array
    {
        return $request->validate([
            'tenKhoaHoc'    => 'required|string|max:255',
            'danhMucId'     => 'required|exists:danhmuckhoahoc,danhMucId',
            'anhKhoaHoc'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'moTa'          => 'nullable|string',
            'doiTuong'      => 'nullable|string|max:255',
            'yeuCauDauVao'  => 'nullable|string',
            'ketQuaDatDuoc' => 'nullable|string',
            'trangThai'     => 'required|in:0,1',
        ], [
            'tenKhoaHoc.required' => 'Vui lòng nhập tên khóa học.',
            'danhMucId.required'  => 'Vui lòng chọn danh mục khóa học.',
            'danhMucId.exists'    => 'Danh mục khóa học không hợp lệ.',
            'anhKhoaHoc.image'    => 'File phải là ảnh.',
            'anhKhoaHoc.max'      => 'Ảnh không được vượt quá 2MB.',
        ]);
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug      = Str::slug($name, '-');
        $candidate = $slug;
        $counter   = 1;
        while (true) {
            $q = KhoaHoc::where('slug', $candidate);
            if ($excludeId) $q->where('khoaHocId', '!=', $excludeId);
            if (!$q->exists()) break;
            $candidate = $slug . '-' . $counter++;
        }
        return $candidate;
    }
}
