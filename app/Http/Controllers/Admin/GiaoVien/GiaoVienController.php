<?php

namespace App\Http\Controllers\Admin\GiaoVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

class GiaoVienController extends Controller
{
    public function __construct(
        protected NhanSuServiceInterface $nhanSuService
        )
    {
    }

    public function index(Request $request)
    {
        $data = $this->nhanSuService->getList($request, TaiKhoan::ROLE_GIAO_VIEN);

        return view('admin.giao-vien.index', [
            'giaoViens' => $data['items'],
            'tongSo' => $data['tongSo'],
            'dangHoatDong' => $data['dangHoatDong'],
            'thangNay' => $data['thangNay'],
        ]);
    }

    public function create()
    {
        return view('admin.giao-vien.create', $this->nhanSuService->getCreateFormData());
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

        $taiKhoan = $this->nhanSuService->store($request, TaiKhoan::ROLE_GIAO_VIEN);

        return redirect()->route('admin.giao-vien.index')
            ->with('success', 'Đã tạo giáo viên «' . $request->hoTen . '» thành công.');
    }

    public function edit(string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        $formData = $this->nhanSuService->getCreateFormData();
        $formData['giaoVien'] = $giaoVien;
        return view('admin.giao-vien.edit', $formData);
    }

    public function update(Request $request, string $taiKhoan)
    {
        $giaoVien = $this->nhanSuService->findByUsername($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);
        
        $request->validate([
            'email' => 'required|email|max:100|unique:taikhoan,email,' . $giaoVien->taiKhoanId . ',taiKhoanId',
            'matKhau' => 'nullable|string|min:8|confirmed',
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:20',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20|unique:hosonguoidung,cccd,' . $giaoVien->taiKhoanId . ',taiKhoanId',
            'chucVu' => 'nullable|string|max:50',
            'chuyenMon' => 'nullable|string|max:50',
            'bangCap' => 'nullable|string|max:50',
            'hocVi' => 'nullable|string|max:50',
            'loaiHopDong' => 'nullable|string|max:50',
            'ngayVaoLam' => 'nullable|date',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'ghiChu' => 'nullable|string',
            'trangThai' => 'required|in:0,1',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng bởi người khác.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'cccd.unique' => 'CCCD/CMND này đã được người khác sử dụng.',
            'coSoId.required' => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists' => 'Cơ sở làm việc không hợp lệ.',
            'trangThai.required' => 'Vui lòng chọn trạng thái hoạt động.',
            'trangThai.in' => 'Trạng thái hoạt động không hợp lệ.',
        ]);

        $this->nhanSuService->update($request, $giaoVien);

        return redirect()->route('admin.giao-vien.index')
            ->with('success', 'Đã cập nhật thông tin giáo viên thành công.');
    }

    public function destroy(string $taiKhoan)
    {
        try {
            $hoTen = $this->nhanSuService->destroy($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

            return redirect()->route('admin.giao-vien.index')
                ->with('success', "Đã xóa giáo viên «{$hoTen}».");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function trash(Request $request)
    {
        $data = $this->nhanSuService->getTrashList($request, TaiKhoan::ROLE_GIAO_VIEN);

        return view('admin.giao-vien.trash', [
            'giaoViens' => $data['items'],
            'tongXoa' => $data['tongXoa'],
        ]);
    }

    public function restore(string $taiKhoan)
    {
        $hoTen = $this->nhanSuService->restore($taiKhoan, TaiKhoan::ROLE_GIAO_VIEN);

        return redirect()->route('admin.giao-vien.trash')
            ->with('success', "Đã khôi phục giáo viên «{$hoTen}» thành công.");
    }
}