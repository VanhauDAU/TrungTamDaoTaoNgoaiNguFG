<?php

namespace App\Services\Client;

use App\Contracts\Client\StudentServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BuoiHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Finance\HoaDon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService implements StudentServiceInterface
{
    public function updateProfile(Request $request, TaiKhoan $user): void
    {
        $request->validate([
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:15',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
            'nguoiGiamHo' => 'nullable|string|max:100',
            'sdtGuardian' => 'nullable|string|max:20',
            'moiQuanHe' => 'nullable|string|max:50',
            'trinhDoHienTai' => 'nullable|string|max:30',
            'ngonNguMucTieu' => 'nullable|string|max:50',
            'nguonBietDen' => 'nullable|string|max:50',
            'ghiChu' => 'nullable|string',
        ], [
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'hoTen.max' => 'Họ và tên không được quá 100 ký tự.',
            'soDienThoai.max' => 'Số điện thoại không được quá 15 ký tự.',
            'ngaySinh.date' => 'Ngày sinh không hợp lệ.',
            'gioiTinh.in' => 'Giới tính không hợp lệ.',
            'diaChi.max' => 'Địa chỉ không được quá 255 ký tự.',
            'nguoiGiamHo.max' => 'Tên người giám hộ không quá 100 ký tự.',
        ]);

        $user->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $user->taiKhoanId],
            [
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh !== '' ? $request->gioiTinh : null,
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
    }

    public function updateAvatar(Request $request, TaiKhoan $user): void
    {
        $request->validate([
            'anhDaiDien' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'anhDaiDien.required' => 'Vui lòng chọn ảnh.',
            'anhDaiDien.image' => 'File phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.',
            'anhDaiDien.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        $hoSo = $user->hoSoNguoiDung;
        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        $path = $request->file('anhDaiDien')->store('anh-dai-dien', 'public');

        $user->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $user->taiKhoanId],
            ['anhDaiDien' => $path]
        );
    }

    public function updatePassword(Request $request, TaiKhoan $user): void
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if (!Hash::check($request->current_password, $user->matKhau)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        $user->update(['matKhau' => Hash::make($request->new_password)]);
        $user->rotateRememberToken('password_changed', (string) $request->session()->getId());
    }

    public function getInvoices(TaiKhoan $user): array
    {
        return [
            'invoices' => HoaDon::where('taiKhoanId', $user->taiKhoanId)
                ->with(['dangKyLopHoc.lopHoc.khoaHoc', 'coSo'])
                ->orderBy('ngayLap', 'desc')->paginate(10),
        ];
    }

    public function getInvoiceDetail(TaiKhoan $user, int $id): array
    {
        return [
            'invoice' => HoaDon::where('hoaDonId', $id)->where('taiKhoanId', $user->taiKhoanId)
                ->with(['dangKyLopHoc.lopHoc.khoaHoc', 'coSo.tinhThanh', 'phieuThus'])->firstOrFail(),
        ];
    }

    public function getMyClasses(TaiKhoan $user): array
    {
        return [
            'classes' => DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
                ->visibleToStudent()
                ->with(['lopHoc.khoaHoc', 'lopHoc.coSo', 'lopHoc.taiKhoan.hoSoNguoiDung', 'lopHoc.buoiHocs.caHoc'])
                ->orderBy('ngayDangKy', 'desc')->get(),
        ];
    }

    public function getSchedule(Request $request, TaiKhoan $user): array
    {
        $baseDate = $request->get('tuan') ? Carbon::parse($request->get('tuan')) : Carbon::now();
        $startOfWeek = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $baseDate->copy()->endOfWeek(Carbon::SUNDAY);

        $lopHocIds = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->eligibleForSchedule()
            ->whereHas('lopHoc', fn($q) => $q->where('trangThai', LopHoc::TRANG_THAI_DANG_HOC))
            ->pluck('lopHocId');

        $buoiHocs = BuoiHoc::whereIn('lopHocId', $lopHocIds)
            ->whereBetween('ngayHoc', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->with(['caHoc', 'phongHoc', 'lopHoc.khoaHoc', 'lopHoc.taiKhoan.hoSoNguoiDung', 'lopHoc.coSo'])
            ->orderBy('ngayHoc')->orderBy('caHocId')->get();

        $caHocs = CaHoc::where('trangThai', 1)->orderBy('gioBatDau')->get();
        $schedule = [];
        foreach ($buoiHocs as $buoi) {
            $ngay = Carbon::parse($buoi->ngayHoc);
            $thu = $ngay->dayOfWeek === 0 ? 8 : $ngay->dayOfWeek + 1;
            $schedule[$thu][$buoi->caHocId][] = $buoi;
        }

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $thu = $i === 6 ? 8 : $i + 2;
            $weekDays[] = ['date' => $day, 'thu' => $thu, 'label' => $i === 6 ? 'Chủ nhật' : 'Thứ ' . ($i + 2)];
        }

        return compact('schedule', 'caHocs', 'weekDays', 'startOfWeek', 'endOfWeek', 'baseDate');
    }
}
