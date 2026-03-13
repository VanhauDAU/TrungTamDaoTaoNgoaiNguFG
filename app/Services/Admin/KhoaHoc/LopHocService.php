<?php

namespace App\Services\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Models\Course\HocPhi;
use App\Models\Course\KhoaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\TinhThanh;
use App\Models\Auth\TaiKhoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LopHocService implements LopHocServiceInterface
{
    // ─────────────────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────────────────

    public function getList(Request $request): array
    {
        $query = LopHoc::with([
            'khoaHoc', 'coSo', 'caHoc',
            'taiKhoan.hoSoNguoiDung', 'dangKyLopHocs',
        ]);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenLopHoc', 'like', "%{$search}%")
                    ->orWhereHas('khoaHoc', fn($q2) => $q2->where('tenKhoaHoc', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('khoaHocId'))
            $query->where('khoaHocId', $request->khoaHocId);
        if ($request->filled('coSoId'))
            $query->where('coSoId', $request->coSoId);
        if ($request->filled('namBatDau'))
            $query->whereYear('ngayBatDau', $request->namBatDau);
        if ($request->filled('thangBatDau'))
            $query->whereMonth('ngayBatDau', $request->thangBatDau);
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'lopHocId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['lopHocId', 'tenLopHoc', 'ngayBatDau'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $lopHocs = $query->paginate(15)->withQueryString();

        $namBatDauOptions = LopHoc::query()
            ->whereNotNull('ngayBatDau')
            ->orderByDesc('ngayBatDau')
            ->get(['ngayBatDau'])
            ->map(fn($l) => Carbon::parse($l->ngayBatDau)->year)
            ->unique()->values();

        return [
            'lopHocs' => $lopHocs,
            'tongLop' => LopHoc::count(),
            'dangHoc' => LopHoc::inProgress()->count(),
            'sapMo' => LopHoc::where('trangThai', LopHoc::TRANG_THAI_SAP_MO)->count(),
            'tongDaXoa' => LopHoc::onlyTrashed()->count(),
            'khoaHocs' => KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get(),
            'coSos' => CoSoDaoTao::where('trangThai', 1)->orderBy('tenCoSo')->get(),
            'namBatDauOptions' => $namBatDauOptions,
            'thangBatDauOptions' => collect(range(1, 12)),
        ];
    }

    public function getTrashList(Request $request): array
    {
        $query = LopHoc::onlyTrashed()->with([
            'khoaHoc', 'coSo', 'caHoc',
            'taiKhoan.hoSoNguoiDung', 'dangKyLopHocs',
        ]);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenLopHoc', 'like', "%{$search}%")
                    ->orWhere('maLopHoc', 'like', "%{$search}%")
                    ->orWhereHas('khoaHoc', fn($q2) => $q2->where('tenKhoaHoc', 'like', "%{$search}%"));
            });
        }

        return [
            'lopHocs' => $query->orderByDesc('deleted_at')->paginate(15)->withQueryString(),
            'tongDaXoa' => LopHoc::onlyTrashed()->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM DATA
    // ─────────────────────────────────────────────────────────────────────────

    public function getCreateFormData(Request $request): array
    {
        $selectedKhoaHocId = $request->get('khoaHocId');

        return [
            'khoaHocs' => KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get(),
            'caHocs' => CaHoc::where('trangThai', 1)->orderBy('tenCa')->get(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))->orderBy('tenTinhThanh')->get(),
            'selectedKhoaHocId' => $selectedKhoaHocId,
            'hocPhis' => $selectedKhoaHocId
            ?HocPhi::where('khoaHocId', $selectedKhoaHocId)->where('trangThai', 1)->get()
            : collect(),
        ];
    }

    public function getDetail(string $slug): array
    {
        $lopHoc = LopHoc::with([
            'khoaHoc', 'coSo', 'caHoc', 'phongHoc',
            'taiKhoan.hoSoNguoiDung',
            'buoiHocs.caHoc', 'buoiHocs.phongHoc', 'buoiHocs.taiKhoan.hoSoNguoiDung',
            'dangKyLopHocs.taiKhoan.hoSoNguoiDung',
        ])->where('slug', $slug)->firstOrFail();

        $coSoId = $lopHoc->coSoId;

        return array_merge(compact('lopHoc'), [
            'caHocs' => CaHoc::where('trangThai', 1)->orderBy('tenCa')->get(),
            'phongHocs' => PhongHoc::where('coSoId', $coSoId)->get(),
            'giaoVienCoSo' => $this->giaoVienTheoCoSo($coSoId, true),
            'giaoVienKhac' => $this->giaoVienTheoCoSo($coSoId, false),
            'soHocVienDangKy' => $lopHoc->dangKyLopHocs->count(),
            'soBuoiDaHoc' => $lopHoc->buoiHocs->where('trangThai', BuoiHoc::TRANG_THAI_DA_HOAN_THANH)->count(),
            'soBuoiChuaHoc' => $lopHoc->buoiHocs->where('trangThai', '!=', BuoiHoc::TRANG_THAI_DA_HOAN_THANH)->count(),
        ]);
    }

    public function getEditFormData(string $slug): array
    {
        $lopHoc = LopHoc::with('coSo')->where('slug', $slug)->firstOrFail();
        $coSoId = $lopHoc->coSoId;

        return [
            'lopHoc' => $lopHoc,
            'khoaHocs' => KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get(),
            'caHocs' => CaHoc::where('trangThai', 1)->orderBy('tenCa')->get(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))->orderBy('tenTinhThanh')->get(),
            'phongHocs' => PhongHoc::where('coSoId', $coSoId)->get(),
            'giaoVienCoSo' => $this->giaoVienTheoCoSo($coSoId, true),
            'giaoVienKhac' => $this->giaoVienTheoCoSo($coSoId, false),
            'hocPhis' => HocPhi::where('khoaHocId', $lopHoc->khoaHocId)->where('trangThai', 1)->get(),
            'currentCoSo' => $lopHoc->coSo,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request): LopHoc
    {
        $data = $this->validateLopHoc($request);
        $this->checkRoomCapacity($data);

        $data['slug'] = $this->generateUniqueSlug($request->tenLopHoc);
        $data['maLopHoc'] = LopHoc::generateMaLopHoc($request->khoaHocId);

        $lopHoc = LopHoc::create($data);
        $this->syncRegistrationStatuses($lopHoc);

        return $lopHoc;
    }

    public function update(Request $request, string $slug): LopHoc
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();
        $data = $this->validateLopHoc($request);
        $this->checkRoomCapacity($data);

        $lopHoc->update($data);
        $this->syncRegistrationStatuses($lopHoc->fresh());

        return $lopHoc->fresh();
    }

    public function destroy(string $slug): string
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();

        if ($lopHoc->dangKyLopHocs()->preventingClassDeletion()->exists()) {
            throw new \RuntimeException('Không thể xóa lớp học khi vẫn còn đăng ký có hiệu lực.');
        }

        $ten = $lopHoc->tenLopHoc;
        if (!$lopHoc->isCancelled()) {
            $lopHoc->trangThai = LopHoc::TRANG_THAI_DA_HUY;
            $lopHoc->save();
            $this->syncRegistrationStatuses($lopHoc->fresh());
        }

        $lopHoc->delete();
        return $ten;
    }

    public function restore(string $slug): LopHoc
    {
        $lopHoc = LopHoc::onlyTrashed()->where('slug', $slug)->firstOrFail();
        $lopHoc->restore();
        return $lopHoc;
    }

    public function syncRegistrationStatuses(LopHoc $lopHoc): void
    {
        if ($lopHoc->isInProgress()) {
            DangKyLopHoc::where('lopHocId', $lopHoc->lopHocId)
                ->where('trangThai', DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN)
                ->update(['trangThai' => DangKyLopHoc::TRANG_THAI_DANG_HOC]);
            return;
        }
        if ($lopHoc->isCompleted()) {
            DangKyLopHoc::where('lopHocId', $lopHoc->lopHocId)
                ->where('trangThai', DangKyLopHoc::TRANG_THAI_DANG_HOC)
                ->update(['trangThai' => DangKyLopHoc::TRANG_THAI_HOAN_THANH]);
            return;
        }
        if ($lopHoc->isCancelled()) {
            DangKyLopHoc::where('lopHocId', $lopHoc->lopHocId)
                ->whereNotIn('trangThai', [DangKyLopHoc::TRANG_THAI_HOAN_THANH, DangKyLopHoc::TRANG_THAI_HUY])
                ->update(['trangThai' => DangKyLopHoc::TRANG_THAI_HUY]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function getHocPhiByKhoaHoc(int $khoaHocId): Collection
    {
        return HocPhi::where('khoaHocId', $khoaHocId)
            ->where('trangThai', 1)->get()
            ->map(fn($hp) => [
            'hocPhiId' => $hp->hocPhiId,
            'soBuoi' => $hp->soBuoi,
            'donGia' => $hp->donGia,
            'tongHocPhi' => $hp->tongHocPhi,
            'label' => 'Gói ' . $hp->soBuoi . ' buổi – ' . $hp->tongHocPhiFormat,
            ]);
    }

    public function getPhongByCoso(int $coSoId): Collection
    {
        return PhongHoc::where('coSoId', $coSoId)
            ->where('trangThai', 1)
            ->get(['phongHocId', 'tenPhong', 'sucChua']);
    }

    public function getGiaoVienByCoso(int $coSoId): array
    {
        return [
            'cung_co_so' => $this->giaoVienTheoCoSo($coSoId, true)->map(fn($gv) => [
            'taiKhoanId' => $gv->taiKhoanId,
            'hoTen' => $gv->hoSoNguoiDung->hoTen ?? $gv->taiKhoan,
            ])->values(),
            'khac_co_so' => $this->giaoVienTheoCoSo($coSoId, false)->map(fn($gv) => [
            'taiKhoanId' => $gv->taiKhoanId,
            'hoTen' => $gv->hoSoNguoiDung->hoTen ?? $gv->taiKhoan,
            ])->values(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function validateLopHoc(Request $request): array
    {
        return $request->validate([
            'tenLopHoc' => 'required|string|max:255',
            'khoaHocId' => 'required|exists:khoahoc,khoaHocId',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'caHocId' => 'required|exists:cahoc,caHocId',
            'taiKhoanId' => 'nullable|exists:taikhoan,taiKhoanId',
            'phongHocId' => 'nullable|exists:phonghoc,phongHocId',
            'ngayBatDau' => 'required|date',
            'ngayKetThuc' => 'required|date|after:ngayBatDau',
            'soBuoiDuKien' => 'nullable|integer|min:1',
            'soHocVienToiDa' => 'nullable|integer|min:1',
            'donGiaDay' => 'nullable|numeric|min:0',
            'hocPhiId' => 'nullable|exists:hocphi,hocPhiId',
            'lichHoc' => 'nullable|string|max:20',
            'trangThai' => ['required', Rule::in(array_map('strval', array_keys(LopHoc::trangThaiLabels())))],
        ], [
            'tenLopHoc.required' => 'Vui lòng nhập tên lớp học.',
            'khoaHocId.required' => 'Vui lòng chọn khóa học.',
            'coSoId.required' => 'Vui lòng chọn cơ sở.',
            'caHocId.required' => 'Vui lòng chọn ca học.',
            'ngayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'ngayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'ngayKetThuc.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ]);
    }

    private function checkRoomCapacity(array $data): void
    {
        if (!empty($data['phongHocId']) && !empty($data['soHocVienToiDa'])) {
            $phong = PhongHoc::find($data['phongHocId']);
            if ($phong && $data['soHocVienToiDa'] > $phong->sucChua) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'soHocVienToiDa' => 'Sĩ số tối đa (' . $data['soHocVienToiDa'] . ') không được vượt quá sức chứa phòng học ' . $phong->tenPhong . ' (' . $phong->sucChua . ' chỗ).',
                ]);
            }
        }
    }

    private function giaoVienTheoCoSo(int $coSoId, bool $cungCoSo)
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1);

        if ($cungCoSo) {
            $query->whereHas('nhanSu', fn($q) => $q->where('coSoId', $coSoId));
        }
        else {
            $query->where(function ($q) use ($coSoId) {
                $q->whereDoesntHave('nhanSu')
                    ->orWhereHas('nhanSu', fn($sq) => $sq->where('coSoId', '!=', $coSoId));
            });
        }

        return $query->get();
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        $candidate = $slug;
        $counter = 1;
        while (true) {
            $q = LopHoc::where('slug', $candidate);
            if ($excludeId)
                $q->where('lopHocId', '!=', $excludeId);
            if (!$q->exists())
                break;
            $candidate = $slug . '-' . $counter++;
        }
        return $candidate;
    }
}