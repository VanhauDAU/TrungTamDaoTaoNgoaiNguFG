<?php

namespace App\Http\Controllers\Admin\NhanVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

class NhanVienController extends Controller
{
    public function __construct(
        protected NhanSuServiceInterface $nhanSuService
        )
    {
    }

    public function index(Request $request)
    {
        $data = $this->nhanSuService->getList($request, TaiKhoan::ROLE_NHAN_VIEN);

        return view('admin.nhan-vien.index', [
            'nhanViens' => $data['items'],
            'tongSo' => $data['tongSo'],
            'dangHoatDong' => $data['dangHoatDong'],
            'thangNay' => $data['thangNay'],
        ]);
    }

    public function create()
    {
        return view('admin.nhan-vien.create', $this->nhanSuService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100|unique:taikhoan,email',
            'matKhau' => 'required|string|min:8|confirmed',
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:20',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20|unique:hosonguoidung,cccd',
            'chucVu' => 'nullable|string|max:50',
            'chuyenMon' => 'nullable|string|max:50',
            'bangCap' => 'nullable|string|max:50',
            'hocVi' => 'nullable|string|max:50',
            'loaiHopDong' => 'nullable|string|max:50',
            'ngayVaoLam' => 'nullable|date',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'ghiChu' => 'nullable|string',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng.',
            'matKhau.required' => 'Vui lòng nhập mật khẩu.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'cccd.unique' => 'CCCD/CMND này đã được đăng ký.',
            'coSoId.required' => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists' => 'Cơ sở làm việc không hợp lệ.',
        ]);

        $this->nhanSuService->store($request, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', 'Đã tạo nhân viên «' . $request->hoTen . '» thành công.');
    }

    public function edit(string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        return view('admin.nhan-vien.edit', compact('nhanVien'));
    }

    public function update(Request $request, string $taiKhoan)
    {
        $nhanVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);
        $this->nhanSuService->update($request, $nhanVien);

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', 'Đã cập nhật nhân viên «' . $request->hoTen . '» thành công.');
    }

    public function destroy(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->destroy($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', "Đã xóa nhân viên «{$hoTen}».");
    }

    public function trash(Request $request)
    {
        $data = $this->nhanSuService->getTrashList($request, TaiKhoan::ROLE_NHAN_VIEN);

        return view('admin.nhan-vien.trash', [
            'nhanViens' => $data['items'],
            'tongXoa' => $data['tongXoa'],
        ]);
    }

    public function restore(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->restore($taiKhoan, TaiKhoan::ROLE_NHAN_VIEN);

        return redirect()->route('admin.nhan-vien.trash')
            ->with('success', "Đã khôi phục nhân viên «{$hoTen}» thành công.");
    }
}