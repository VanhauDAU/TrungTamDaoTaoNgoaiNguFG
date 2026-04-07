<?php

namespace App\Services\Admin\NhanVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Data\Admin\NhanVien\CreatedStaffAccountResult;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;
use App\Models\Auth\NhanSuGoiLuong;
use App\Models\Auth\NhanSuGoiLuongChiTiet;
use App\Models\Auth\NhanSuHoSo;
use App\Models\Auth\NhanSuMauQuyDinh;
use App\Models\Auth\NhanSuTaiLieu;
use App\Models\Auth\NhomQuyen;
use App\Models\Auth\TaiKhoan;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class NhanSuService implements NhanSuServiceInterface
{
    private const HANDOVER_CACHE_PREFIX = 'staff-handover:';
    private const HANDOVER_TTL_MINUTES = 10;

    public function getList(Request $request, string $role): array
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu.coSoDaoTao'])->where('role', $role);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2
                        ->where('hoTen', 'like', "%{$search}%")
                        ->orWhere('soDienThoai', 'like', "%{$search}%")
                        ->orWhere('cccd', 'like', "%{$search}%"))
                    ->orWhereHas('nhanSu', fn($q2) => $q2
                        ->where('chuyenMon', 'like', "%{$search}%")
                        ->orWhere('chucVu', 'like', "%{$search}%")
                        ->orWhere('loaiHopDong', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'taiKhoanId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'], true)) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        return [
            'items' => $query->paginate(15)->withQueryString(),
            'tongSo' => TaiKhoan::where('role', $role)->count(),
            'dangHoatDong' => TaiKhoan::where('role', $role)->where('trangThai', 1)->count(),
            'thangNay' => TaiKhoan::where('role', $role)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    public function getTrashList(Request $request, string $role): array
    {
        $query = TaiKhoan::onlyTrashed()->with('hoSoNguoiDung')->where('role', $role);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        return [
            'items' => $query->orderByDesc('deleted_at')->paginate(15)->withQueryString(),
            'tongXoa' => TaiKhoan::onlyTrashed()->where('role', $role)->count(),
        ];
    }

    public function getCreateFormData(string $role): array
    {
        return $this->buildFormData($role);
    }

    public function getEditFormData(TaiKhoan $taiKhoan, string $role): array
    {
        $taiKhoan->loadMissing([
            'hoSoNguoiDung',
            'nhanSu.coSoDaoTao.tinhThanh',
            'nhanSuHoSo.nhanSuMauQuyDinh',
        ]);

        return $this->buildFormData($role, $taiKhoan);
    }

    public function store(Request $request, string $role): CreatedStaffAccountResult
    {
        $validated = $this->validateStoreRequest($request, $role);

        return DB::transaction(function () use ($validated, $request, $role) {
            $temporaryPassword = Str::password(12, true, true, false, false);
            $nhom = $this->resolveDefaultPermissionGroup($role);

            $taiKhoan = TaiKhoan::create([
                'taiKhoan' => TaiKhoan::generateTemporaryUsername((int) $role),
                'email' => $validated['email'],
                'matKhau' => Hash::make($temporaryPassword),
                'role' => $role,
                'trangThai' => 1,
                'phaiDoiMatKhau' => 1,
                'nhomQuyenId' => $nhom?->nhomQuyenId,
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]);

            $taiKhoan->assignSystemUsername();

            $this->upsertCoreProfile($taiKhoan, $validated, true);
            $this->upsertProfileSnapshot($taiKhoan, $validated['nhanSuMauQuyDinhId'], $validated['ghiChuHoSo'] ?? null);
            $this->createSalaryPackage($taiKhoan, $validated, true);

            if ($request->hasFile('cvXinViec')) {
                $this->storeDocument(
                    $request->file('cvXinViec'),
                    $taiKhoan,
                    NhanSuTaiLieu::LOAI_CV,
                    $validated['cvTenHienThi'] ?? 'CV xin viec',
                    $validated['cvGhiChu'] ?? null,
                    archiveCurrentType: true,
                );
            }

            $token = $this->createHandoverToken($taiKhoan, $temporaryPassword);

            return new CreatedStaffAccountResult(
                taiKhoan: $taiKhoan->fresh([
                    'hoSoNguoiDung',
                    'nhanSu.coSoDaoTao.tinhThanh',
                    'nhanSuHoSo.nhanSuMauQuyDinh',
                ]),
                plainTemporaryPassword: $temporaryPassword,
                oneTimeToken: $token,
            );
        });
    }

    public function findByUsername(string $taiKhoan, string $role): TaiKhoan
    {
        return TaiKhoan::with([
            'hoSoNguoiDung',
            'nhanSu.coSoDaoTao.tinhThanh',
            'nhanSuHoSo.nhanSuMauQuyDinh',
            'nhanSuGoiLuongs.chiTiets',
            'nhanSuTaiLieus.nguoiTaiLen',
        ])
            ->where('role', $role)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
    }

    public function getProfileData(TaiKhoan $taiKhoan, string $role, ?string $handoverToken = null): array
    {
        $taiKhoan->loadMissing([
            'hoSoNguoiDung',
            'nhanSu.coSoDaoTao.tinhThanh',
            'nhanSuHoSo.nhanSuMauQuyDinh',
            'nhanSuGoiLuongs.chiTiets',
            'nhanSuTaiLieus.nguoiTaiLen',
        ]);

        $activeSalary = $taiKhoan->nhanSuGoiLuongs
            ->sortByDesc(fn(NhanSuGoiLuong $goiLuong) => optional($goiLuong->hieuLucTu)->timestamp ?? 0)
            ->first(fn(NhanSuGoiLuong $goiLuong) => $goiLuong->isActive());

        return [
            'record' => $taiKhoan,
            'role' => $role,
            'roleLabel' => $this->roleLabel($role),
            'hoSoNhanSu' => $taiKhoan->nhanSuHoSo,
            'goiLuongHienHanh' => $activeSalary,
            'lichSuLuong' => $taiKhoan->nhanSuGoiLuongs
                ->sortByDesc(fn(NhanSuGoiLuong $goiLuong) => optional($goiLuong->hieuLucTu)->timestamp ?? 0)
                ->values(),
            'taiLieuHoatDong' => $taiKhoan->nhanSuTaiLieus
                ->where('trangThai', NhanSuTaiLieu::TRANG_THAI_ACTIVE)
                ->sortByDesc('created_at')
                ->values(),
            'taiLieuDaLuuTru' => $taiKhoan->nhanSuTaiLieus
                ->where('trangThai', NhanSuTaiLieu::TRANG_THAI_ARCHIVED)
                ->sortByDesc('created_at')
                ->values(),
            'handover' => $this->getHandoverPayload($handoverToken, $taiKhoan),
            'loaiHopDongOptions' => $this->contractOptions(),
            'loaiTaiLieuOptions' => NhanSuTaiLieu::loaiOptions(),
            'loaiLuongOptions' => NhanSuGoiLuong::loaiLuongOptions(),
            'loaiLuongChiTietOptions' => NhanSuGoiLuongChiTiet::loaiOptions(),
        ];
    }

    public function update(Request $request, TaiKhoan $nhanSu): void
    {
        $validated = $this->validateUpdateRequest($request, $nhanSu);

        DB::transaction(function () use ($validated, $nhanSu) {
            $oldStatus = (int) $nhanSu->trangThai;
            $passwordChanged = !empty($validated['matKhau']);

            $nhanSu->update([
                'email' => $validated['email'],
                'trangThai' => (int) $validated['trangThai'],
                ...($passwordChanged ? ['matKhau' => Hash::make($validated['matKhau'])] : []),
            ]);

            if ($passwordChanged) {
                $nhanSu->rotateRememberToken('admin_password_reset');
            } elseif ($oldStatus === 1 && (int) $validated['trangThai'] === 0) {
                $nhanSu->rotateRememberToken('account_locked');
            }

            $this->upsertCoreProfile($nhanSu, $validated, false);
        });
    }

    public function updateAvatar(Request $request, TaiKhoan $taiKhoan): void
    {
        Validator::make($request->all(), [
            'anhDaiDien' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'anhDaiDien.required'  => 'Vui lòng chọn ảnh đại diện.',
            'anhDaiDien.image'     => 'File phải là ảnh.',
            'anhDaiDien.mimes'     => 'Chỉ chấp nhận định dạng JPG, PNG, WEBP.',
            'anhDaiDien.max'       => 'Ảnh không được vượt quá 2MB.',
        ])->validate();

        $hoSo = $taiKhoan->hoSoNguoiDung;

        // Xóa ảnh cũ nếu tồn tại trên disk public
        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        $path = $request->file('anhDaiDien')->store('nhan-su/avatar', 'public');

        HoSoNguoiDung::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            ['anhDaiDien' => $path]
        );
    }

    public function uploadDocument(Request $request, TaiKhoan $taiKhoan): void
    {
        $validated = Validator::make($request->all(), [
            'loaiTaiLieu' => ['required', Rule::in(array_keys(NhanSuTaiLieu::loaiOptions()))],
            'tenHienThi' => ['nullable', 'string', 'max:150'],
            'ghiChu' => ['nullable', 'string'],
            'replaceDocumentId' => ['nullable', 'integer'],
            'tep' => ['required', 'file', 'max:15360', 'mimes:pdf,doc,docx,png,jpg,jpeg,xls,xlsx'],
        ], [
            'loaiTaiLieu.required' => 'Vui lòng chọn loại tài liệu.',
            'loaiTaiLieu.in' => 'Loại tài liệu không hợp lệ.',
            'tep.required' => 'Vui lòng chọn file tải lên.',
            'tep.mimes' => 'Định dạng file chưa được hỗ trợ.',
            'tep.max' => 'Tài liệu không được vượt quá 15MB.',
        ])->validate();

        DB::transaction(function () use ($validated, $request, $taiKhoan) {
            $replaceDocument = null;
            if (!empty($validated['replaceDocumentId'])) {
                $replaceDocument = NhanSuTaiLieu::where('taiKhoanId', $taiKhoan->taiKhoanId)
                    ->where('nhanSuTaiLieuId', $validated['replaceDocumentId'])
                    ->first();
            }

            $this->storeDocument(
                $request->file('tep'),
                $taiKhoan,
                $validated['loaiTaiLieu'],
                $validated['tenHienThi'] ?: pathinfo($request->file('tep')->getClientOriginalName(), PATHINFO_FILENAME),
                $validated['ghiChu'] ?? null,
                replaceDocument: $replaceDocument,
                archiveCurrentType: $validated['loaiTaiLieu'] === NhanSuTaiLieu::LOAI_CV,
            );
        });
    }

    public function downloadDocument(TaiKhoan $taiKhoan, int $documentId): BinaryFileResponse
    {
        $document = NhanSuTaiLieu::where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->where('nhanSuTaiLieuId', $documentId)
            ->firstOrFail();

        return Storage::disk($document->disk)->download($document->duongDan, $document->tenGoc);
    }

    public function archiveDocument(TaiKhoan $taiKhoan, int $documentId): void
    {
        $document = NhanSuTaiLieu::where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->where('nhanSuTaiLieuId', $documentId)
            ->where('trangThai', NhanSuTaiLieu::TRANG_THAI_ACTIVE)
            ->firstOrFail();

        $document->update([
            'trangThai' => NhanSuTaiLieu::TRANG_THAI_ARCHIVED,
            'archivedAt' => now(),
        ]);
    }

    public function saveSalaryPackage(Request $request, TaiKhoan $taiKhoan): void
    {
        $validated = Validator::make($request->all(), $this->salaryRules(), $this->salaryMessages())->validate();

        DB::transaction(function () use ($validated, $taiKhoan) {
            $this->createSalaryPackage($taiKhoan, $validated, false);
        });
    }

    public function downloadHandoverPdf(TaiKhoan $taiKhoan, string $role, string $token): Response
    {
        $handover = Cache::pull($this->handoverCacheKey($token));

        abort_unless(
            is_array($handover)
            && (int) ($handover['taiKhoanId'] ?? 0) === (int) $taiKhoan->taiKhoanId
            && (string) ($handover['role'] ?? '') === (string) $role,
            404
        );

        return $this->renderPdfResponse(
            'admin.nhan-su.pdf.handover',
            [
                'record' => $taiKhoan->loadMissing('hoSoNguoiDung'),
                'roleLabel' => $this->roleLabel($role),
                'handover' => $handover,
            ],
            'ban-giao-tai-khoan-' . $taiKhoan->taiKhoan . '-' . now()->format('Ymd') . '.pdf',
        );
    }

    public function downloadProfilePdf(TaiKhoan $taiKhoan, string $role): Response
    {
        $artifact = $this->buildProfilePdfArtifact($taiKhoan, $role);

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => 'attachment; filename="' . $artifact['filename'] . '"',
        ]);
    }

    public function buildProfilePdfArtifact(TaiKhoan $taiKhoan, string $role): array
    {
        $data = $this->getProfileData($taiKhoan, $role);

        return $this->renderPdfArtifact(
            'admin.nhan-su.pdf.profile',
            $data,
            'ho-so-nhan-su-' . $taiKhoan->taiKhoan . '-' . now()->format('Ymd') . '.pdf',
        );
    }

    public function destroy(string $taiKhoan, string $role): string
    {
        $user = TaiKhoan::where('role', $role)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $user->hoSoNguoiDung->hoTen ?? $user->taiKhoan;
        $user->delete();

        return $hoTen;
    }

    public function restore(string $taiKhoan, string $role): string
    {
        $user = TaiKhoan::onlyTrashed()->where('role', $role)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $user->hoSoNguoiDung->hoTen ?? $user->taiKhoan;
        $user->restore();

        return $hoTen;
    }

    private function buildFormData(string $role, ?TaiKhoan $taiKhoan = null): array
    {
        $this->ensureDefaultPolicyTemplates();

        $coSos = CoSoDaoTao::with('tinhThanh')->where('trangThai', 1)->orderBy('tenCoSo')->get();
        $record = $taiKhoan;
        $selectedBranch = $record?->nhanSu?->coSoDaoTao;

        return [
            'record' => $record,
            'role' => $role,
            'roleLabel' => $this->roleLabel($role),
            'coSos' => $coSos,
            'coSosData' => $coSos->map(fn(CoSoDaoTao $c) => [
                'coSoId' => $c->coSoId,
                'tenCoSo' => $c->tenCoSo,
                'diaChi' => $c->diaChi,
                'tinhThanhId' => $c->tinhThanhId,
                'maPhuongXa' => $c->maPhuongXa,
                'tenPhuongXa' => $c->tenPhuongXa,
            ])->values()->toArray(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
                ->orderBy('tenTinhThanh')
                ->get(),
            'loaiHopDongOptions' => $this->contractOptions(),
            'loaiLuongOptions' => NhanSuGoiLuong::loaiLuongOptions(),
            'loaiLuongChiTietOptions' => NhanSuGoiLuongChiTiet::loaiOptions(),
            'mauQuyDinhs' => $this->getPolicyTemplatesForRole($role),
            'selectedTinhThanhId' => old('selectedTinhThanhId', $selectedBranch?->tinhThanhId),
            'selectedPhuongXa' => old('selectedPhuongXa', $selectedBranch?->maPhuongXa),
            'selectedCoSoId' => old('coSoId', $selectedBranch?->coSoId),
            'selectedTemplateId' => old('nhanSuMauQuyDinhId', $record?->nhanSuHoSo?->nhanSuMauQuyDinhId),
            'defaultSalaryStartDate' => old('hieuLucTu', $record?->nhanSu?->ngayVaoLam?->format('Y-m-d') ?? now()->toDateString()),
        ];
    }

    private function validateStoreRequest(Request $request, string $role): array
    {
        $this->ensureDefaultPolicyTemplates();

        return Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'max:100', 'unique:taikhoan,email'],
                'hoTen' => ['required', 'string', 'max:100'],
                'soDienThoai' => ['nullable', 'string', 'max:20'],
                'zalo' => ['nullable', 'string', 'max:20'],
                'ngaySinh' => ['nullable', 'date'],
                'gioiTinh' => ['nullable', Rule::in(['0', '1', '2', 0, 1, 2])],
                'diaChi' => ['nullable', 'string', 'max:255'],
                'cccd' => ['nullable', 'string', 'max:20', 'unique:hosonguoidung,cccd'],
                'chucVu' => ['nullable', 'string', 'max:50'],
                'chuyenMon' => ['nullable', 'string', 'max:80'],
                'bangCap' => ['nullable', 'string', 'max:80'],
                'hocVi' => ['nullable', 'string', 'max:80'],
                'loaiHopDong' => ['required', Rule::in(array_keys($this->contractOptions()))],
                'ngayVaoLam' => ['nullable', 'date'],
                'coSoId' => ['required', 'exists:cosodaotao,coSoId'],
                'ghiChu' => ['nullable', 'string'],
                'ghiChuHoSo' => ['nullable', 'string'],
                'nhanSuMauQuyDinhId' => ['required', 'exists:nhansu_mau_quydinh,nhanSuMauQuyDinhId'],
                'loaiLuong' => ['required', Rule::in(array_keys(NhanSuGoiLuong::loaiLuongOptions()))],
                'luongChinh' => ['required', 'numeric', 'min:0'],
                'hieuLucTu' => ['required', 'date'],
                'ghiChuLuong' => ['nullable', 'string'],
                'cvXinViec' => ['nullable', 'file', 'max:15360', 'mimes:pdf,doc,docx'],
                'cvTenHienThi' => ['nullable', 'string', 'max:150'],
                'cvGhiChu' => ['nullable', 'string'],
                'salary_details.type.*' => ['nullable', Rule::in(array_keys(NhanSuGoiLuongChiTiet::loaiOptions()))],
                'salary_details.name.*' => ['nullable', 'string', 'max:100'],
                'salary_details.amount.*' => ['nullable', 'numeric', 'min:0'],
                'salary_details.note.*' => ['nullable', 'string'],
            ],
            $this->messages()
        )->validate();
    }

    private function validateUpdateRequest(Request $request, TaiKhoan $nhanSu): array
    {
        return Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'max:100', Rule::unique('taikhoan', 'email')->ignore($nhanSu->taiKhoanId, 'taiKhoanId')],
                'trangThai' => ['required', Rule::in(['0', '1', 0, 1])],
                'matKhau' => ['nullable', 'string', 'min:8', 'confirmed'],
                'hoTen' => ['required', 'string', 'max:100'],
                'soDienThoai' => ['nullable', 'string', 'max:20'],
                'zalo' => ['nullable', 'string', 'max:20'],
                'ngaySinh' => ['nullable', 'date'],
                'gioiTinh' => ['nullable', Rule::in(['0', '1', '2', 0, 1, 2])],
                'diaChi' => ['nullable', 'string', 'max:255'],
                'cccd' => ['nullable', 'string', 'max:20', Rule::unique('hosonguoidung', 'cccd')->ignore($nhanSu->taiKhoanId, 'taiKhoanId')],
                'chucVu' => ['nullable', 'string', 'max:50'],
                'chuyenMon' => ['nullable', 'string', 'max:80'],
                'bangCap' => ['nullable', 'string', 'max:80'],
                'hocVi' => ['nullable', 'string', 'max:80'],
                'loaiHopDong' => ['required', Rule::in(array_keys($this->contractOptions()))],
                'ngayVaoLam' => ['nullable', 'date'],
                'coSoId' => ['required', 'exists:cosodaotao,coSoId'],
                'ghiChu' => ['nullable', 'string'],
            ],
            $this->messages()
        )->validate();
    }

    private function upsertCoreProfile(TaiKhoan $taiKhoan, array $validated, bool $syncStatusFromAccount): void
    {
        HoSoNguoiDung::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            [
                'hoTen' => $validated['hoTen'],
                'soDienThoai' => $validated['soDienThoai'] ?? null,
                'zalo' => $validated['zalo'] ?? null,
                'ngaySinh' => $validated['ngaySinh'] ?? null,
                'gioiTinh' => $validated['gioiTinh'] ?? null,
                'diaChi' => $validated['diaChi'] ?? null,
                'cccd' => $validated['cccd'] ?? null,
                'ghiChu' => $validated['ghiChu'] ?? null,
            ]
        );

        NhanSu::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            [
                'chucVu' => $validated['chucVu'] ?? null,
                'chuyenMon' => $validated['chuyenMon'] ?? null,
                'bangCap' => $validated['bangCap'] ?? null,
                'hocVi' => $validated['hocVi'] ?? null,
                'loaiHopDong' => $validated['loaiHopDong'],
                'ngayVaoLam' => $validated['ngayVaoLam'] ?? now()->toDateString(),
                'coSoId' => $validated['coSoId'],
                'luongCoBan' => $validated['luongChinh'] ?? $taiKhoan->nhanSu?->luongCoBan ?? 0,
                'trangThai' => $syncStatusFromAccount ? (int) $taiKhoan->trangThai : ((int) ($validated['trangThai'] ?? $taiKhoan->trangThai)),
            ]
        );
    }

    private function upsertProfileSnapshot(TaiKhoan $taiKhoan, int|string $templateId, ?string $ghiChuHoSo = null): void
    {
        $template = NhanSuMauQuyDinh::findOrFail($templateId);

        NhanSuHoSo::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            [
                'maHoSo' => $this->buildProfileCode($taiKhoan),
                'nhanSuMauQuyDinhId' => $template->nhanSuMauQuyDinhId,
                'tieuDeMauSnapshot' => $template->tieuDe,
                'noiDungQuyDinhSnapshot' => $template->noiDung,
                'trangThaiHoSo' => NhanSuHoSo::TRANG_THAI_HOAN_TAT,
                'ghiChuHoSo' => $ghiChuHoSo,
            ]
        );
    }

    private function createSalaryPackage(TaiKhoan $taiKhoan, array $validated, bool $initial): void
    {
        $startDate = Carbon::parse($validated['hieuLucTu'] ?? now()->toDateString());
        $currentActive = NhanSuGoiLuong::where('taiKhoanId', $taiKhoan->taiKhoanId)->active()->latest('hieuLucTu')->first();

        if ($currentActive) {
            $currentActive->update([
                'trangThai' => 0,
                'hieuLucDen' => $startDate->copy(),
            ]);
        }

        $salaryPackage = NhanSuGoiLuong::create([
            'taiKhoanId' => $taiKhoan->taiKhoanId,
            'loaiLuong' => $validated['loaiLuong'],
            'luongChinh' => $validated['luongChinh'],
            'hieuLucTu' => $startDate->toDateString(),
            'hieuLucDen' => null,
            'ghiChu' => $validated['ghiChuLuong'] ?? null,
            'trangThai' => 1,
        ]);

        foreach ($this->collectSalaryDetails($validated['salary_details'] ?? []) as $index => $detail) {
            $salaryPackage->chiTiets()->create([
                'loai' => $detail['type'],
                'tenKhoan' => $detail['name'],
                'soTien' => $detail['amount'],
                'ghiChu' => $detail['note'],
                'sortOrder' => $index,
            ]);
        }

        NhanSu::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            [
                'luongCoBan' => $validated['luongChinh'],
                'trangThai' => $initial ? 1 : (int) $taiKhoan->trangThai,
            ]
        );
    }

    private function collectSalaryDetails(array $salaryDetails): array
    {
        $types = $salaryDetails['type'] ?? [];
        $names = $salaryDetails['name'] ?? [];
        $amounts = $salaryDetails['amount'] ?? [];
        $notes = $salaryDetails['note'] ?? [];

        $rows = [];
        $max = max(count($types), count($names), count($amounts), count($notes));

        for ($index = 0; $index < $max; $index++) {
            $type = $types[$index] ?? null;
            $name = trim((string) ($names[$index] ?? ''));
            $amount = $amounts[$index] ?? null;
            $note = trim((string) ($notes[$index] ?? ''));

            if (!$type && $name === '' && ($amount === null || $amount === '') && $note === '') {
                continue;
            }

            if (!$type || $name === '' || $amount === null || $amount === '') {
                throw ValidationException::withMessages([
                    'salary_details' => 'Mỗi dòng chi tiết lương cần đủ loại khoản, tên khoản và số tiền.',
                ]);
            }

            $rows[] = [
                'type' => $type,
                'name' => $name,
                'amount' => $amount,
                'note' => $note !== '' ? $note : null,
            ];
        }

        return $rows;
    }

    private function storeDocument(
        $uploadedFile,
        TaiKhoan $taiKhoan,
        string $type,
        string $displayName,
        ?string $note = null,
        ?NhanSuTaiLieu $replaceDocument = null,
        bool $archiveCurrentType = false,
    ): NhanSuTaiLieu {
        if ($replaceDocument) {
            $replaceDocument->update([
                'trangThai' => NhanSuTaiLieu::TRANG_THAI_ARCHIVED,
                'archivedAt' => now(),
            ]);
        }

        if ($archiveCurrentType) {
            NhanSuTaiLieu::where('taiKhoanId', $taiKhoan->taiKhoanId)
                ->where('loaiTaiLieu', $type)
                ->where('trangThai', NhanSuTaiLieu::TRANG_THAI_ACTIVE)
                ->update([
                    'trangThai' => NhanSuTaiLieu::TRANG_THAI_ARCHIVED,
                    'archivedAt' => now(),
                ]);
        }

        $versionBaseQuery = NhanSuTaiLieu::where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->where('loaiTaiLieu', $type)
            ->where('tenHienThi', $displayName);

        $version = ((int) $versionBaseQuery->max('phienBan')) + 1;
        $originalName = $uploadedFile->getClientOriginalName();
        $safeName = $this->sanitizeFilename(pathinfo($originalName, PATHINFO_FILENAME));
        $timestamp = now()->format('Ymd_His');
        $extension = $uploadedFile->getClientOriginalExtension();
        $storedName = $timestamp . '_' . $safeName . ($extension ? '.' . $extension : '');
        $storedPath = $uploadedFile->storeAs(
            'nhan-su/' . $taiKhoan->taiKhoanId . '/' . strtolower($type),
            $storedName,
            'local'
        );

        return NhanSuTaiLieu::create([
            'taiKhoanId' => $taiKhoan->taiKhoanId,
            'loaiTaiLieu' => $type,
            'tenHienThi' => $displayName,
            'tenGoc' => $originalName,
            'duongDan' => $storedPath,
            'disk' => 'local',
            'mime' => $uploadedFile->getMimeType(),
            'kichThuoc' => $uploadedFile->getSize(),
            'checksum' => hash_file('sha256', $uploadedFile->getRealPath()),
            'phienBan' => $version,
            'duocTaiLenBoiId' => auth()->id(),
            'trangThai' => NhanSuTaiLieu::TRANG_THAI_ACTIVE,
            'ghiChu' => $note,
        ]);
    }

    private function createHandoverToken(TaiKhoan $taiKhoan, string $temporaryPassword): string
    {
        $token = (string) Str::ulid();

        Cache::put(
            $this->handoverCacheKey($token),
            [
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'role' => (string) $taiKhoan->role,
                'username' => $taiKhoan->taiKhoan,
                'password' => $temporaryPassword,
                'issued_at' => now()->toIso8601String(),
                'expires_at' => now()->addMinutes(self::HANDOVER_TTL_MINUTES)->toIso8601String(),
            ],
            now()->addMinutes(self::HANDOVER_TTL_MINUTES)
        );

        return $token;
    }

    private function getHandoverPayload(?string $token, TaiKhoan $taiKhoan): ?array
    {
        if (!$token) {
            return null;
        }

        $payload = Cache::get($this->handoverCacheKey($token));
        if (!is_array($payload) || (int) ($payload['taiKhoanId'] ?? 0) !== (int) $taiKhoan->taiKhoanId) {
            return null;
        }

        return $payload;
    }

    private function handoverCacheKey(string $token): string
    {
        return self::HANDOVER_CACHE_PREFIX . $token;
    }

    private function resolveDefaultPermissionGroup(string $role): ?NhomQuyen
    {
        $tenNhom = (int) $role === TaiKhoan::ROLE_GIAO_VIEN ? 'giáo viên' : 'nhân viên';

        return NhomQuyen::where('tenNhom', 'like', '%' . $tenNhom . '%')->first();
    }

    private function contractOptions(): array
    {
        return [
            'FULL_TIME' => 'Toàn thời gian',
            'PART_TIME' => 'Bán thời gian',
            'PROBATION' => 'Thử việc',
            'VISITING' => 'Thỉnh giảng',
        ];
    }

    private function roleLabel(string $role): string
    {
        return (int) $role === TaiKhoan::ROLE_GIAO_VIEN ? 'Giáo viên' : 'Nhân viên';
    }

    private function getPolicyTemplatesForRole(string $role)
    {
        $scope = (int) $role === TaiKhoan::ROLE_GIAO_VIEN
            ? NhanSuMauQuyDinh::PHAM_VI_GIAO_VIEN
            : NhanSuMauQuyDinh::PHAM_VI_NHAN_VIEN;

        return NhanSuMauQuyDinh::active()
            ->where(function ($query) use ($scope) {
                $query->where('phamViApDung', $scope)
                    ->orWhere('phamViApDung', NhanSuMauQuyDinh::PHAM_VI_BOTH);
            })
            ->orderBy('tieuDe')
            ->get();
    }

    private function ensureDefaultPolicyTemplates(): void
    {
        if (NhanSuMauQuyDinh::query()->exists()) {
            return;
        }

        NhanSuMauQuyDinh::create([
            'maMau' => 'QD-GV-001',
            'tieuDe' => 'Quy định tiếp nhận giáo viên chuẩn',
            'phamViApDung' => NhanSuMauQuyDinh::PHAM_VI_GIAO_VIEN,
            'loaiHopDongApDung' => 'ALL',
            'noiDung' => '<h3>Quy định áp dụng cho giáo viên</h3><p>Giáo viên cần tuân thủ nội quy giảng dạy, thời lượng lên lớp, báo cáo chuyên môn và quy chế bảo mật dữ liệu học viên.</p>',
            'phienBan' => 1,
            'trangThai' => 1,
            'createdById' => auth()->id(),
            'updatedById' => auth()->id(),
        ]);

        NhanSuMauQuyDinh::create([
            'maMau' => 'QD-NV-001',
            'tieuDe' => 'Quy định tiếp nhận nhân viên chuẩn',
            'phamViApDung' => NhanSuMauQuyDinh::PHAM_VI_NHAN_VIEN,
            'loaiHopDongApDung' => 'ALL',
            'noiDung' => '<h3>Quy định áp dụng cho nhân viên</h3><p>Nhân viên cần tuân thủ nội quy làm việc, quy trình nghiệp vụ, quy chế phối hợp nội bộ và cam kết bảo mật.</p>',
            'phienBan' => 1,
            'trangThai' => 1,
            'createdById' => auth()->id(),
            'updatedById' => auth()->id(),
        ]);
    }

    private function buildProfileCode(TaiKhoan $taiKhoan): string
    {
        return 'HS' . TaiKhoan::prefixForRole((int) $taiKhoan->role) . str_pad((string) $taiKhoan->taiKhoanId, 6, '0', STR_PAD_LEFT);
    }

    private function sanitizeFilename(string $filename): string
    {
        $slug = Str::slug($filename, '-');

        return $slug !== '' ? $slug : 'tai-lieu';
    }

    private function salaryRules(): array
    {
        return [
            'loaiLuong' => ['required', Rule::in(array_keys(NhanSuGoiLuong::loaiLuongOptions()))],
            'luongChinh' => ['required', 'numeric', 'min:0'],
            'hieuLucTu' => ['required', 'date'],
            'ghiChuLuong' => ['nullable', 'string'],
            'salary_details.type.*' => ['nullable', Rule::in(array_keys(NhanSuGoiLuongChiTiet::loaiOptions()))],
            'salary_details.name.*' => ['nullable', 'string', 'max:100'],
            'salary_details.amount.*' => ['nullable', 'numeric', 'min:0'],
            'salary_details.note.*' => ['nullable', 'string'],
        ];
    }

    private function salaryMessages(): array
    {
        return [
            'loaiLuong.required' => 'Vui lòng chọn loại lương.',
            'loaiLuong.in' => 'Loại lương không hợp lệ.',
            'luongChinh.required' => 'Vui lòng nhập lương chính.',
            'luongChinh.numeric' => 'Lương chính phải là số.',
            'luongChinh.min' => 'Lương chính phải lớn hơn hoặc bằng 0.',
            'hieuLucTu.required' => 'Vui lòng chọn ngày hiệu lực.',
            'hieuLucTu.date' => 'Ngày hiệu lực không hợp lệ.',
        ];
    }

    private function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được sử dụng.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'cccd.unique' => 'CCCD/CMND này đã được đăng ký.',
            'coSoId.required' => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists' => 'Cơ sở làm việc không hợp lệ.',
            'loaiHopDong.required' => 'Vui lòng chọn loại hợp đồng.',
            'loaiHopDong.in' => 'Loại hợp đồng không hợp lệ.',
            'nhanSuMauQuyDinhId.required' => 'Vui lòng chọn mẫu quy định áp dụng.',
            'nhanSuMauQuyDinhId.exists' => 'Mẫu quy định không hợp lệ.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'trangThai.required' => 'Vui lòng chọn trạng thái tài khoản.',
            'trangThai.in' => 'Trạng thái tài khoản không hợp lệ.',
            'cvXinViec.mimes' => 'CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.',
            'cvXinViec.max' => 'CV không được vượt quá 15MB.',
        ] + $this->salaryMessages();
    }

    private function renderPdfResponse(string $view, array $data, string $filename): Response
    {
        $artifact = $this->renderPdfArtifact($view, $data, $filename);

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => 'attachment; filename="' . $artifact['filename'] . '"',
        ]);
    }

    private function renderPdfArtifact(string $view, array $data, string $filename): array
    {
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView($view, $data);

            return [
                'content' => $pdf->output(),
                'mime' => 'application/pdf',
                'filename' => $filename,
            ];
        }

        $html = view($view, $data)->render();
        $fallbackName = Str::replaceLast('.pdf', '.html', $filename);

        return [
            'content' => $html,
            'mime' => 'text/html; charset=UTF-8',
            'filename' => $fallbackName,
        ];
    }
}
