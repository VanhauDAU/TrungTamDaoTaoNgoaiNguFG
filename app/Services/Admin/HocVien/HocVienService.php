<?php

namespace App\Services\Admin\HocVien;

use App\Contracts\Admin\HocVien\HocVienServiceInterface;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HocVienService implements HocVienServiceInterface
{
    public function getList(Request $request): array
    {
        $hocViens = $this->buildIndexQuery($request)->paginate(15)->withQueryString();

        return [
            'hocViens' => $hocViens,
            'tongSo' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
            'dangHoatDong' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->where('trangThai', 1)->count(),
            'thangNay' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function buildIndexQuery(Request $request): Builder
    {
        $query = TaiKhoan::query()
            ->with('hoSoNguoiDung')
            ->withCount('dangKyLopHocs')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%")->orWhere('soDienThoai', 'like', "%{$search}%"));
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

        return $query;
    }

    public function getTrashList(Request $request): array
    {
        $query = TaiKhoan::onlyTrashed()->with('hoSoNguoiDung')->where('role', TaiKhoan::ROLE_HOC_VIEN);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        return [
            'hocViens' => $query->orderByDesc('deleted_at')->paginate(15)->withQueryString(),
            'tongXoa' => TaiKhoan::onlyTrashed()->where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
        ];
    }

    public function store(Request $request): TaiKhoan
    {
        $request->validate([
            'email' => 'required|email|max:100|unique:taikhoan,email',
            'matKhau' => 'required|string|min:8|confirmed',
            'hoTen' => ['required', 'string', 'max:100', 'regex:/^[^0-9]*$/'],
            'anhDaiDien' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'soDienThoai' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'zalo' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20|unique:hosonguoidung,cccd',
            'nguoiGiamHo' => 'nullable|string|max:100',
            'sdtGuardian' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'moiQuanHe' => 'nullable|string|max:50',
            'trinhDoHienTai' => 'nullable|string|max:30',
            'ngonNguMucTieu' => 'nullable|string|max:50',
            'nguonBietDen' => 'nullable|string|max:50',
            'ghiChu' => 'nullable|string',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng.',
            'matKhau.required' => 'Vui lòng nhập mật khẩu.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'hoTen.regex' => 'Họ và tên không được chứa chữ số.',
            'soDienThoai.regex' => 'Số điện thoại phải có đúng 10 chữ số.',
            'zalo.regex' => 'Số Zalo phải có đúng 10 chữ số.',
            'sdtGuardian.regex' => 'Số điện thoại người giám hộ phải có đúng 10 chữ số.',
            'cccd.unique' => 'CCCD/CMND này đã được đăng ký.',
            'anhDaiDien.image' => 'File đại diện phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận định dạng JPG, PNG, WEBP.',
            'anhDaiDien.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        return DB::transaction(function () use ($request) {
            $taiKhoan = TaiKhoan::create([
                'taiKhoan' => TaiKhoan::generateTemporaryUsername(TaiKhoan::ROLE_HOC_VIEN),
                'email' => $request->email,
                'matKhau' => Hash::make($request->matKhau),
                'role' => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1,
                'phaiDoiMatKhau' => 1,
                'auth_provider' => 'local',
                'email_verified_at' => now(),
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh,
                'diaChi' => $request->diaChi,
                'cccd' => $request->cccd,
                'nguoiGiamHo' => $request->nguoiGiamHo,
                'sdtGuardian' => $request->sdtGuardian,
                'moiQuanHe' => $request->moiQuanHe,
                'trinhDoHienTai' => $request->trinhDoHienTai,
                'ngonNguMucTieu' => $request->ngonNguMucTieu,
                'nguonBietDen' => $request->nguonBietDen,
                'ghiChu' => $request->ghiChu,
            ]);

            $this->handleAvatarUpload($request, $taiKhoan);

            return $taiKhoan;
        });
    }

    public function findByUsername(string $taiKhoan): TaiKhoan
    {
        return TaiKhoan::with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
    }

    public function update(Request $request, TaiKhoan $hocVien): void
    {
        $request->validate([
            'email' => ['required', 'email', 'max:100', Rule::unique('taikhoan', 'email')->ignore($hocVien->taiKhoanId, 'taiKhoanId')],
            'hoTen' => ['required', 'string', 'max:100', 'regex:/^[^0-9]*$/'],
            'anhDaiDien' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'trangThai' => 'required|in:0,1',
            'matKhau' => 'nullable|string|min:8|confirmed',
            'soDienThoai' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'zalo' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => ['nullable', 'string', 'max:20', Rule::unique('hosonguoidung', 'cccd')->ignore($hocVien->taiKhoanId, 'taiKhoanId')],
            'nguoiGiamHo' => 'nullable|string|max:100',
            'sdtGuardian' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'moiQuanHe' => 'nullable|string|max:50',
            'trinhDoHienTai' => 'nullable|string|max:30',
            'ngonNguMucTieu' => 'nullable|string|max:50',
            'nguonBietDen' => 'nullable|string|max:50',
            'ghiChu' => 'nullable|string',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng bởi tài khoản khác.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'hoTen.regex' => 'Họ và tên không được chứa chữ số.',
            'soDienThoai.regex' => 'Số điện thoại phải có đúng 10 chữ số.',
            'zalo.regex' => 'Số Zalo phải có đúng 10 chữ số.',
            'sdtGuardian.regex' => 'Số điện thoại người giám hộ phải có đúng 10 chữ số.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'cccd.unique' => 'CCCD/CMND đã được đăng ký bởi học viên khác.',
            'anhDaiDien.image' => 'File đại diện phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận định dạng JPG, PNG, WEBP.',
            'anhDaiDien.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        DB::transaction(function () use ($request, $hocVien) {
            $oldStatus = (int) $hocVien->trangThai;
            $tkData = ['email' => $request->email, 'trangThai' => $request->trangThai];
            if ($request->filled('matKhau')) {
                $tkData['matKhau'] = Hash::make($request->matKhau);
            }
            $hocVien->update($tkData);

            if ($oldStatus === 1 && (int) $request->trangThai === 0) {
                $hocVien->rotateRememberToken('account_locked');
            }

            $hocVien->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $hocVien->taiKhoanId],
            [
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh,
                'diaChi' => $request->diaChi,
                'cccd' => $request->cccd,
                'nguoiGiamHo' => $request->nguoiGiamHo,
                'sdtGuardian' => $request->sdtGuardian,
                'moiQuanHe' => $request->moiQuanHe,
                'trinhDoHienTai' => $request->trinhDoHienTai,
                'ngonNguMucTieu' => $request->ngonNguMucTieu,
                'nguonBietDen' => $request->nguonBietDen,
                'ghiChu' => $request->ghiChu,
            ]
            );

            $this->handleAvatarUpload($request, $hocVien);
        });
    }

    public function destroy(string $taiKhoan): string
    {
        $hocVien = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $hocVien->hoSoNguoiDung->hoTen ?? $hocVien->taiKhoan;
        $hocVien->delete();
        return $hoTen;
    }

    public function restore(string $taiKhoan): string
    {
        $hocVien = TaiKhoan::onlyTrashed()->where('role', TaiKhoan::ROLE_HOC_VIEN)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $hocVien->hoSoNguoiDung->hoTen ?? $hocVien->taiKhoan;
        $hocVien->restore();
        return $hoTen;
    }

    private function handleAvatarUpload(Request $request, TaiKhoan $taiKhoan): void
    {
        if (!$request->hasFile('anhDaiDien')) {
            return;
        }

        $hoSo = $taiKhoan->hoSoNguoiDung;

        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        $path = $request->file('anhDaiDien')->store('hoc-vien/avatar', 'public');

        HoSoNguoiDung::updateOrCreate(
            ['taiKhoanId' => $taiKhoan->taiKhoanId],
            ['anhDaiDien' => $path]
        );
    }
}
