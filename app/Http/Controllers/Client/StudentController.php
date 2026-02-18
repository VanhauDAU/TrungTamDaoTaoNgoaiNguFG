<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\HoaDon;

class StudentController extends Controller
{
    //
    public function index(){
        return view('clients.student.index');
    }

    public function changePassword()
    {
        return view('clients.student.change-password');
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
        
        return view('clients.student.invoices.index', compact('invoices'));
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
        
        return view('clients.student.invoices.show', compact('invoice'));
    }

    public function myClasses()
    {
        $classes = \App\Models\Education\DangKyLopHoc::where('taiKhoanId', auth()->user()->taiKhoanId)
            ->whereIn('trangThai', [1, 2]) // Chờ thanh toán hoặc đã xác nhận
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'lopHoc.taiKhoan.hoSoNguoiDung', // Giảng viên
                'lopHoc.buoiHocs.caHoc'
            ])
            ->orderBy('ngayDangKy', 'desc')
            ->get();
        
        return view('clients.student.classes.index', compact('classes'));
    }
}
