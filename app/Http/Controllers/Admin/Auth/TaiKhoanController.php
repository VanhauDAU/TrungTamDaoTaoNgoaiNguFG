<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\NhomQuyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TaiKhoanController extends Controller
{
    public function __construct()
    {
        // View accounts list
        $this->middleware('permission:tai_khoan,xem')->only('index');
        // Update role
        $this->middleware('permission:tai_khoan,sua')->only('updateNhomQuyen', 'toggleStatus', 'resetPassword');
    }

    /** Danh sách tài khoản hệ thống */
    public function index(Request $request)
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhomQuyen']);

        // ── Tìm kiếm (tên, email, sđt) ──────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', function ($q2) use ($search) {
                        $q2->where('hoTen', 'like', "%{$search}%")
                            ->orWhere('soDienThoai', 'like', "%{$search}%");
                    });
            });
        }

        // ── Lọc theo vai trò ──────────────────────────────────
        if ($request->filled('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // ── Lọc trạng thái ──────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'taiKhoanId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'])) {
            $query->orderBy($orderBy, $dir);
        }

        $taiKhoans = $query->paginate(15)->withQueryString();

        $tongSo = TaiKhoan::count();
        $dangHoatDong = TaiKhoan::where('trangThai', 1)->count();
        $nhomQuyens = NhomQuyen::orderBy('tenNhom')->get();

        return view('admin.tai-khoan.index', compact(
            'taiKhoans',
            'tongSo',
            'dangHoatDong',
            'nhomQuyens'
        ));
    }

    /** Cập nhật nhóm quyền cho nhân sự */
    public function updateNhomQuyen(Request $request, int $id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        // Chỉ cho phép gán quyền cho Admin / Giao Vien / Nhan Vien
        if (!$taiKhoan->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Học viên không có nhóm quyền quản trị.'
            ], 403);
        }

        $request->validate([
            'nhomQuyenId' => 'required|exists:nhomquyen,nhomQuyenId'
        ]);
 
        $taiKhoan->update([
            'nhomQuyenId' => $request->nhomQuyenId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật nhóm quyền cho tài khoản ' . $taiKhoan->taiKhoan
        ]);
    }

    /** Khóa / Mở khóa tài khoản */
    public function toggleStatus(int $id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        // Không cho phép tự khóa Admin chính mình
        if ($taiKhoan->taiKhoanId === auth()->user()->taiKhoanId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự khóa tài khoản của mình!'
            ], 403);
        }

        $newStatus = (int) $taiKhoan->trangThai === 1 ? 0 : 1;

        $taiKhoan->update([
            'trangThai' => $newStatus
        ]);

        if ($newStatus === 0) {
            $taiKhoan->rotateRememberToken('account_locked');
        }

        return response()->json([
            'success' => true,
            'message' => $newStatus === 1 ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản thành công.'
        ]);
    }

    /** Reset mật khẩu */
    public function resetPassword(Request $request, int $id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        $request->validate([
            'matKhau' => 'required|string|min:8|confirmed'
        ], [
            'matKhau.required' => 'Vui lòng nhập mật khẩu mới.',
            'matKhau.min' => 'Mật khẩu phải từ 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $taiKhoan->update([
            'matKhau' => Hash::make($request->matKhau),
            'phaiDoiMatKhau' => 1,
        ]);
        $taiKhoan->rotateRememberToken('admin_password_reset');

        return response()->json([
            'success' => true,
            'message' => 'Đã reset mật khẩu cho tài khoản ' . $taiKhoan->taiKhoan
        ]);
    }
}
