<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Finance\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /** Trang thông tin cá nhân */
    public function index()
    {
        return view('clients.hoc-vien.profile.index');
    }

    /** Cập nhật thông tin cá nhân */
    public function updateProfile(Request $request)
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
            'cccd.max' => 'Số CCCD/CMND không được quá 20 ký tự.',
            'nguoiGiamHo.max' => 'Tên người giám hộ không quá 100 ký tự.',
        ]);

        $user = auth()->user();

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

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /** Cập nhật ảnh đại diện */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'anhDaiDien' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'anhDaiDien.required' => 'Vui lòng chọn ảnh.',
            'anhDaiDien.image' => 'File phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.',
            'anhDaiDien.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        $user = auth()->user();
        $hoSo = $user->hoSoNguoiDung;

        // Xóa ảnh cũ (nếu có)
        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        // Lưu ảnh mới (giống logic khóa học: DB lưu 'avatars/RandomName.jpg')
        $path = $request->file('anhDaiDien')->store('anh-dai-dien', 'public');

        $user->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $user->taiKhoanId],
            ['anhDaiDien' => $path]
        );

        return back()->with('success_avatar', 'Cập nhật ảnh đại diện thành công!');
    }

    public function changePassword()
    {
        return view('clients.hoc-vien.change-password');
    }

    public function updatePassword(Request $request)
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

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->matKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        $user->update([
            'matKhau' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function invoices()
    {
        $invoices = HoaDon::where('taiKhoanId', auth()->user()->taiKhoanId)
            ->with(['dangKyLopHoc.lopHoc.khoaHoc', 'coSo'])
            ->orderBy('ngayLap', 'desc')
            ->paginate(10);

        return view('clients.hoc-vien.invoices.index', compact('invoices'));
    }

    public function invoiceDetail($id)
    {
        $invoice = HoaDon::where('hoaDonId', $id)
            ->where('taiKhoanId', auth()->user()->taiKhoanId)
            ->with([
                'dangKyLopHoc.lopHoc.khoaHoc',
                'coSo.tinhThanh',
                'phieuThus'
            ])
            ->firstOrFail();

        return view('clients.hoc-vien.invoices.show', compact('invoice'));
    }

    public function myClasses()
    {
        $classes = \App\Models\Education\DangKyLopHoc::where('taiKhoanId', auth()->user()->taiKhoanId)
            ->whereIn('trangThai', [1, 2])
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lopHoc.buoiHocs.caHoc'
            ])
            ->orderBy('ngayDangKy', 'desc')
            ->get();

        return view('clients.hoc-vien.classes.index', compact('classes'));
    }
}
