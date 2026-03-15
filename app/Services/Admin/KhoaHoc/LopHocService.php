<?php

namespace App\Services\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use App\Models\Education\LopHocPhuPhi;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\TinhThanh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LopHocService implements LopHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = LopHoc::with([
            'khoaHoc', 'coSo', 'caHoc', 'chinhSachGia',
            'taiKhoan.hoSoNguoiDung', 'dangKyLopHocs',
        ]);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenLopHoc', 'like', "%{$search}%")
                    ->orWhereHas('khoaHoc', fn($q2) => $q2->where('tenKhoaHoc', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('khoaHocId')) {
            $query->where('khoaHocId', $request->khoaHocId);
        }
        if ($request->filled('coSoId')) {
            $query->where('coSoId', $request->coSoId);
        }
        if ($request->filled('namBatDau')) {
            $query->whereYear('ngayBatDau', $request->namBatDau);
        }
        if ($request->filled('thangBatDau')) {
            $query->whereMonth('ngayBatDau', $request->thangBatDau);
        }
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'lopHocId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['lopHocId', 'tenLopHoc', 'ngayBatDau'], true)) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $lopHocs = $query->paginate(15)->withQueryString();

        $namBatDauOptions = LopHoc::query()
            ->whereNotNull('ngayBatDau')
            ->orderByDesc('ngayBatDau')
            ->get(['ngayBatDau'])
            ->map(fn($lopHoc) => Carbon::parse($lopHoc->ngayBatDau)->year)
            ->unique()
            ->values();

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
            'khoaHoc', 'coSo', 'caHoc', 'chinhSachGia',
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

    public function getCreateFormData(Request $request): array
    {
        $selectedKhoaHocId = $request->get('khoaHocId');

        return [
            'khoaHocs' => KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get(),
            'caHocs' => CaHoc::where('trangThai', 1)->orderBy('tenCa')->get(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))->orderBy('tenTinhThanh')->get(),
            'selectedKhoaHocId' => $selectedKhoaHocId,
            'loaiThuOptions' => LopHocChinhSachGia::loaiThuOptions(),
            'nhomPhiOptions' => LopHocPhuPhi::nhomPhiOptions(),
        ];
    }

    public function getDetail(string $slug): array
    {
        $lopHoc = LopHoc::with([
            'khoaHoc', 'coSo', 'caHoc', 'phongHoc',
            'taiKhoan.hoSoNguoiDung',
            'chinhSachGia.dotThus',
            'phuPhis',
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
        $lopHoc = LopHoc::with(['coSo', 'chinhSachGia.dotThus', 'phuPhis'])->where('slug', $slug)->firstOrFail();
        $coSoId = $lopHoc->coSoId;

        return [
            'lopHoc' => $lopHoc,
            'khoaHocs' => KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get(),
            'caHocs' => CaHoc::where('trangThai', 1)->orderBy('tenCa')->get(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))->orderBy('tenTinhThanh')->get(),
            'phongHocs' => PhongHoc::where('coSoId', $coSoId)->get(),
            'giaoVienCoSo' => $this->giaoVienTheoCoSo($coSoId, true),
            'giaoVienKhac' => $this->giaoVienTheoCoSo($coSoId, false),
            'currentCoSo' => $lopHoc->coSo,
            'loaiThuOptions' => LopHocChinhSachGia::loaiThuOptions(),
            'nhomPhiOptions' => LopHocPhuPhi::nhomPhiOptions(),
        ];
    }

    public function store(Request $request): LopHoc
    {
        $data = $this->validateLopHoc($request);
        $lopHocData = $this->extractLopHocData($data);
        $pricingPayload = $this->buildMainTuitionPayload($request, $data);
        $supplementalFeesPayload = $this->buildSupplementalFeePayload($request);

        $this->checkRoomCapacity($lopHocData);
        $this->ensurePricingBusinessRules(null, $lopHocData, $pricingPayload);

        $lopHocData['slug'] = $this->generateUniqueSlug($request->tenLopHoc);
        $lopHocData['maLopHoc'] = LopHoc::generateMaLopHoc($request->khoaHocId);

        $lopHoc = LopHoc::create($lopHocData);
        $this->syncPricingPolicy($lopHoc, $pricingPayload);
        $this->syncSupplementalFees($lopHoc, $supplementalFeesPayload);
        $this->syncRegistrationStatuses($lopHoc);

        return $lopHoc->fresh(['chinhSachGia.dotThus', 'phuPhis']);
    }

    public function update(Request $request, string $slug): LopHoc
    {
        $lopHoc = LopHoc::with(['chinhSachGia.dotThus', 'dangKyLopHocs', 'phuPhis'])->where('slug', $slug)->firstOrFail();
        $data = $this->validateLopHoc($request);
        $lopHocData = $this->extractLopHocData($data);
        $pricingPayload = $this->buildMainTuitionPayload($request, $data);
        $supplementalFeesPayload = $this->buildSupplementalFeePayload($request);

        $this->checkRoomCapacity($lopHocData);
        $this->ensurePricingBusinessRules($lopHoc, $lopHocData, $pricingPayload);

        $lopHoc->update($lopHocData);
        $this->syncPricingPolicy($lopHoc->fresh(), $pricingPayload);
        $this->syncSupplementalFees($lopHoc->fresh(), $supplementalFeesPayload);
        $this->syncRegistrationStatuses($lopHoc->fresh());

        return $lopHoc->fresh(['chinhSachGia.dotThus', 'phuPhis']);
    }

    public function updateStatus(string $slug, int $trangThai): LopHoc
    {
        $lopHoc = LopHoc::with(['chinhSachGia', 'dangKyLopHocs'])->where('slug', $slug)->firstOrFail();

        if (!array_key_exists($trangThai, LopHoc::trangThaiOptions())) {
            throw ValidationException::withMessages([
                'trangThai' => 'Trạng thái lớp học không hợp lệ.',
            ]);
        }

        if ((int) $lopHoc->trangThai === $trangThai) {
            return $lopHoc;
        }

        $this->ensurePricingBusinessRules($lopHoc, [
            'trangThai' => $trangThai,
            'ngayBatDau' => $lopHoc->ngayBatDau,
        ], $lopHoc->hasValidPricingPolicy()
            ? [
                'hocPhiNiemYet' => (float) $lopHoc->chinhSachGia?->hocPhiNiemYet,
                'loaiThu' => (int) ($lopHoc->chinhSachGia?->loaiThu ?? LopHocChinhSachGia::LOAI_THU_TRON_GOI),
                'hanThanhToanHocPhi' => optional($lopHoc->chinhSachGia?->hanThanhToanHocPhi)->toDateString(),
            ]
            : []);

        $lopHoc->update(['trangThai' => $trangThai]);
        $this->syncRegistrationStatuses($lopHoc->fresh());

        return $lopHoc->fresh(['chinhSachGia', 'dangKyLopHocs', 'phuPhis']);
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

    public function getPhongByCoso(int $coSoId): Collection
    {
        return PhongHoc::where('coSoId', $coSoId)
            ->where('trangThai', 1)
            ->get(['phongHocId', 'tenPhong', 'sucChua']);
    }

    public function getGiaoVienByCoso(int $coSoId): array
    {
        return [
            'cung_co_so' => $this->giaoVienTheoCoSo($coSoId, true)->map(fn($giaoVien) => [
                'taiKhoanId' => $giaoVien->taiKhoanId,
                'hoTen' => $giaoVien->hoSoNguoiDung->hoTen ?? $giaoVien->taiKhoan,
            ])->values(),
            'khac_co_so' => $this->giaoVienTheoCoSo($coSoId, false)->map(fn($giaoVien) => [
                'taiKhoanId' => $giaoVien->taiKhoanId,
                'hoTen' => $giaoVien->hoSoNguoiDung->hoTen ?? $giaoVien->taiKhoan,
            ])->values(),
        ];
    }

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
            'soBuoiDuKien' => 'nullable|integer|min:1',
            'soHocVienToiDa' => 'nullable|integer|min:1',
            'donGiaDay' => 'nullable|numeric|min:0',
            'lichHoc' => 'nullable|string|max:20',
            'trangThai' => ['required', Rule::in(array_map('strval', array_keys(LopHoc::trangThaiLabels())))],
            'hocPhiNiemYet' => 'nullable|numeric|min:0',
            'soBuoiCamKet' => 'nullable|integer|min:1',
            'hanThanhToanHocPhi' => 'nullable|date',
            'loaiThu' => ['nullable', Rule::in(array_map('strval', array_keys(LopHocChinhSachGia::loaiThuOptions())))],
            'ghiChuChinhSach' => 'nullable|string',
            'trangThaiChinhSachGia' => 'nullable|in:0,1',
            'dotThu' => 'nullable|array',
        ], [
            'tenLopHoc.required' => 'Vui lòng nhập tên lớp học.',
            'khoaHocId.required' => 'Vui lòng chọn khóa học.',
            'coSoId.required' => 'Vui lòng chọn cơ sở.',
            'caHocId.required' => 'Vui lòng chọn ca học.',
            'ngayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'hocPhiNiemYet.min' => 'Học phí niêm yết không được âm.',
            'soBuoiCamKet.min' => 'Số buổi cam kết phải tối thiểu là 1.',
        ]);
    }

    private function extractLopHocData(array $data): array
    {
        return Arr::only($data, [
            'tenLopHoc',
            'khoaHocId',
            'coSoId',
            'caHocId',
            'taiKhoanId',
            'phongHocId',
            'ngayBatDau',
            'soBuoiDuKien',
            'soHocVienToiDa',
            'donGiaDay',
            'lichHoc',
            'trangThai',
        ]);
    }

    private function buildMainTuitionPayload(Request $request, array $validatedData): array
    {
        $hocPhiNiemYet = $validatedData['hocPhiNiemYet'] ?? null;
        $soBuoiCamKet = $validatedData['soBuoiCamKet'] ?? null;
        $soBuoiDuKien = isset($validatedData['soBuoiDuKien']) ? (int) $validatedData['soBuoiDuKien'] : null;
        $hanThanhToanHocPhi = $validatedData['hanThanhToanHocPhi'] ?? null;
        $loaiThu = isset($validatedData['loaiThu']) && $validatedData['loaiThu'] !== ''
            ? (int) $validatedData['loaiThu']
            : LopHocChinhSachGia::LOAI_THU_TRON_GOI;
        $ghiChuChinhSach = trim((string) ($validatedData['ghiChuChinhSach'] ?? ''));
        $trangThai = (int) ($validatedData['trangThaiChinhSachGia'] ?? 1);
        $dotThus = $this->normalizeDotThuRows($request->input('dotThu', []), $loaiThu);

        $hasAnyPricingInput = $hocPhiNiemYet !== null
            || $soBuoiCamKet !== null
            || !empty($hanThanhToanHocPhi)
            || $ghiChuChinhSach !== ''
            || !empty($dotThus)
            || $request->filled('loaiThu')
            || $request->filled('hanThanhToanHocPhi');

        if (!$hasAnyPricingInput) {
            return [];
        }

        if ($hocPhiNiemYet === null || (float) $hocPhiNiemYet <= 0) {
            throw ValidationException::withMessages([
                'hocPhiNiemYet' => 'Vui lòng nhập học phí niêm yết lớn hơn 0 khi cấu hình chính sách giá.',
            ]);
        }

        if ($loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_DOT && empty($dotThus)) {
            throw ValidationException::withMessages([
                'dotThu' => 'Loại thu theo đợt phải có ít nhất một đợt thu.',
            ]);
        }

        if ($loaiThu === LopHocChinhSachGia::LOAI_THU_TRON_GOI && empty($hanThanhToanHocPhi)) {
            throw ValidationException::withMessages([
                'hanThanhToanHocPhi' => 'Thu học phí một lần phải có hạn thanh toán mẫu.',
            ]);
        }

        if ($loaiThu !== LopHocChinhSachGia::LOAI_THU_THEO_DOT) {
            $dotThus = [];
        }

        if (!empty($dotThus)) {
            $tongDotThu = collect($dotThus)->sum(fn(array $dotThu) => (float) $dotThu['soTien']);
            if (round($tongDotThu, 2) !== round((float) $hocPhiNiemYet, 2)) {
                throw ValidationException::withMessages([
                    'dotThu' => 'Tổng các đợt thu phải bằng học phí niêm yết của lớp.',
                ]);
            }
        }

        if ($soBuoiCamKet !== null) {
            $soBuoiCamKet = (int) $soBuoiCamKet;
            if ($soBuoiDuKien !== null && $soBuoiCamKet === $soBuoiDuKien) {
                $soBuoiCamKet = null;
            }
        }

        return [
            'loaiThu' => $loaiThu,
            'hocPhiNiemYet' => (float) $hocPhiNiemYet,
            'soBuoiCamKet' => $soBuoiCamKet,
            'hanThanhToanHocPhi' => $loaiThu === LopHocChinhSachGia::LOAI_THU_TRON_GOI ? $hanThanhToanHocPhi : null,
            'ghiChuChinhSach' => $ghiChuChinhSach !== '' ? $ghiChuChinhSach : null,
            'trangThai' => $trangThai,
            'dotThus' => $dotThus,
        ];
    }

    private function normalizeDotThuRows(array $rows, int $loaiThu): array
    {
        if ($loaiThu !== LopHocChinhSachGia::LOAI_THU_THEO_DOT) {
            return [];
        }

        $normalizedRows = [];
        $previousDueDate = null;

        foreach ($rows as $index => $row) {
            $tenDotThu = trim((string) ($row['tenDotThu'] ?? ''));
            $soTien = $row['soTien'] ?? null;
            $hanThanhToan = $row['hanThanhToan'] ?? null;
            $hasAnyValue = $tenDotThu !== '' || $soTien !== null || !empty($hanThanhToan);

            if (!$hasAnyValue) {
                continue;
            }

            if ($tenDotThu === '') {
                throw ValidationException::withMessages([
                    "dotThu.{$index}.tenDotThu" => 'Mỗi đợt thu phải có tên đợt thu.',
                ]);
            }

            if (!is_numeric($soTien) || (float) $soTien <= 0) {
                throw ValidationException::withMessages([
                    "dotThu.{$index}.soTien" => 'Số tiền mỗi đợt thu phải lớn hơn 0.',
                ]);
            }

            if (empty($hanThanhToan)) {
                throw ValidationException::withMessages([
                    "dotThu.{$index}.hanThanhToan" => 'Mỗi đợt thu phải có hạn thanh toán.',
                ]);
            }

            $dueDate = Carbon::parse($hanThanhToan)->startOfDay();

            if ($previousDueDate && $dueDate->lt($previousDueDate)) {
                throw ValidationException::withMessages([
                    "dotThu.{$index}.hanThanhToan" => 'Hạn thanh toán các đợt phải tăng dần theo thứ tự đợt thu.',
                ]);
            }

            $normalizedRows[] = [
                'tenDotThu' => $tenDotThu,
                'thuTu' => count($normalizedRows) + 1,
                'soTien' => (float) $soTien,
                'hanThanhToan' => $dueDate->toDateString(),
                'trangThai' => 1,
            ];

            $previousDueDate = $dueDate;
        }

        return $normalizedRows;
    }

    private function buildSupplementalFeePayload(Request $request): array
    {
        $rows = $request->input('phuPhi', []);
        $normalizedRows = [];

        foreach ($rows as $index => $row) {
            $tenKhoanThu = trim((string) ($row['tenKhoanThu'] ?? ''));
            $nhomPhi = trim((string) ($row['nhomPhi'] ?? LopHocPhuPhi::NHOM_PHI_KHAC));
            $soTien = $row['soTien'] ?? null;
            $hanThanhToanMau = $row['hanThanhToanMau'] ?? null;
            $apDungMacDinh = !empty($row['apDungMacDinh']) ? 1 : 0;
            $hasAnyValue = $tenKhoanThu !== '' || $soTien !== null || !empty($hanThanhToanMau) || $apDungMacDinh === 1;

            if (!$hasAnyValue) {
                continue;
            }

            if ($tenKhoanThu === '') {
                throw ValidationException::withMessages([
                    "phuPhi.{$index}.tenKhoanThu" => 'Mỗi khoản bổ sung phải có tên khoản thu.',
                ]);
            }

            if (!array_key_exists($nhomPhi, LopHocPhuPhi::nhomPhiOptions())) {
                throw ValidationException::withMessages([
                    "phuPhi.{$index}.nhomPhi" => 'Nhóm phí không hợp lệ.',
                ]);
            }

            if (!is_numeric($soTien) || (float) $soTien <= 0) {
                throw ValidationException::withMessages([
                    "phuPhi.{$index}.soTien" => 'Số tiền khoản bổ sung phải lớn hơn 0.',
                ]);
            }

            $normalizedRows[] = [
                'tenKhoanThu' => $tenKhoanThu,
                'nhomPhi' => $nhomPhi,
                'soTien' => (float) $soTien,
                'hanThanhToanMau' => !empty($hanThanhToanMau) ? Carbon::parse($hanThanhToanMau)->toDateString() : null,
                'apDungMacDinh' => $apDungMacDinh,
                'trangThai' => 1,
            ];
        }

        return $normalizedRows;
    }

    private function ensurePricingBusinessRules(?LopHoc $existingClass, array $lopHocData, array $pricingPayload): void
    {
        $trangThai = (int) $lopHocData['trangThai'];
        $requiresPricing = in_array($trangThai, [
            LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
            LopHoc::TRANG_THAI_CHOT_DANH_SACH,
            LopHoc::TRANG_THAI_DANG_HOC,
            LopHoc::TRANG_THAI_DA_KET_THUC,
        ], true);

        $hasPricing = !empty($pricingPayload) && (float) ($pricingPayload['hocPhiNiemYet'] ?? 0) > 0;

        if ($requiresPricing && !$hasPricing) {
            throw ValidationException::withMessages([
                'hocPhiNiemYet' => 'Lớp học phải có chính sách giá hợp lệ trước khi mở tuyển sinh hoặc vận hành.',
            ]);
        }

        if ($existingClass && $existingClass->dangKyLopHocs()->count() > 0 && !$hasPricing) {
            throw ValidationException::withMessages([
                'hocPhiNiemYet' => 'Không thể gỡ chính sách giá của lớp khi đã có học viên đăng ký.',
            ]);
        }

        $this->ensureStatusTransitionRules($existingClass, $lopHocData);

        if (!empty($pricingPayload) && (int) $pricingPayload['loaiThu'] === LopHocChinhSachGia::LOAI_THU_TRON_GOI && empty($pricingPayload['hanThanhToanHocPhi'])) {
            throw ValidationException::withMessages([
                'hanThanhToanHocPhi' => 'Thu học phí một lần phải có hạn thanh toán mẫu.',
            ]);
        }
    }

    private function ensureStatusTransitionRules(?LopHoc $existingClass, array $lopHocData): void
    {
        if (!$existingClass) {
            $this->ensureStatusDateRules(null, (int) $lopHocData['trangThai'], $lopHocData);
            return;
        }

        $targetStatus = (int) $lopHocData['trangThai'];

        if (!$existingClass->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'trangThai' => 'Không thể chuyển lớp học từ trạng thái "' . $existingClass->trangThaiLabel . '" sang "' . (LopHoc::trangThaiLabels()[$targetStatus] ?? 'không xác định') . '".',
            ]);
        }

        if ($existingClass->isCancelled() && $targetStatus !== LopHoc::TRANG_THAI_DA_HUY && $existingClass->dangKyLopHocs()->count() > 0) {
            throw ValidationException::withMessages([
                'trangThai' => 'Không thể mở lại lớp đã hủy khi lớp này đã có đăng ký học viên. Hãy tạo lớp mới nếu cần mở lại.',
            ]);
        }

        $this->ensureStatusDateRules($existingClass, $targetStatus, $lopHocData);
    }

    private function ensureStatusDateRules(?LopHoc $existingClass, int $targetStatus, array $lopHocData): void
    {
        $today = Carbon::today();
        $ngayBatDau = !empty($lopHocData['ngayBatDau']) ? Carbon::parse($lopHocData['ngayBatDau'])->startOfDay() : null;

        if ($targetStatus === LopHoc::TRANG_THAI_DANG_HOC && $ngayBatDau && $ngayBatDau->gt($today)) {
            throw ValidationException::withMessages([
                'trangThai' => 'Không thể chuyển lớp sang trạng thái đang học trước ngày bắt đầu.',
            ]);
        }

        if ($targetStatus === LopHoc::TRANG_THAI_DA_KET_THUC && $existingClass && !$existingClass->isInProgress()) {
            throw ValidationException::withMessages([
                'trangThai' => 'Chỉ có thể kết thúc lớp khi lớp đang ở trạng thái đang học.',
            ]);
        }
    }

    private function syncPricingPolicy(LopHoc $lopHoc, array $pricingPayload): void
    {
        $existingPolicy = $lopHoc->chinhSachGia()->first();

        if (empty($pricingPayload)) {
            if ($existingPolicy) {
                $existingPolicy->dotThus()->delete();
                $existingPolicy->delete();
            }
            return;
        }

        $policyData = Arr::except($pricingPayload, ['dotThus']);

        $policy = $existingPolicy
            ? tap($existingPolicy)->update($policyData)
            : $lopHoc->chinhSachGia()->create($policyData);

        $policy->dotThus()->delete();

        foreach ($pricingPayload['dotThus'] as $dotThu) {
            $policy->dotThus()->create($dotThu);
        }
    }

    private function syncSupplementalFees(LopHoc $lopHoc, array $supplementalFeesPayload): void
    {
        $lopHoc->phuPhis()->delete();

        foreach ($supplementalFeesPayload as $supplementalFee) {
            $lopHoc->phuPhis()->create($supplementalFee);
        }
    }

    private function checkRoomCapacity(array $data): void
    {
        if (!empty($data['phongHocId']) && !empty($data['soHocVienToiDa'])) {
            $phong = PhongHoc::find($data['phongHocId']);
            if ($phong && $data['soHocVienToiDa'] > $phong->sucChua) {
                throw ValidationException::withMessages([
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
        } else {
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
            $query = LopHoc::where('slug', $candidate);
            if ($excludeId) {
                $query->where('lopHocId', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }
}
