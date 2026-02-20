<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\NhomQuyen;
use App\Models\Auth\PhanQuyen;
use Illuminate\Http\Request;

class NhomQuyenController extends Controller
{
    /** Danh sách tính năng trong hệ thống */
    const TINH_NANG = [
        'khoa_hoc'  => 'Khoá học',
        'lop_hoc'   => 'Lớp học',
        'hoc_vien'  => 'Học viên',
        'giao_vien' => 'Giáo viên',
        'nhan_vien' => 'Nhân viên',
        'tai_chinh' => 'Tài chính',
        'dang_ky'   => 'Đăng ký học',
        'tai_khoan' => 'Tài khoản',
        'cai_dat'   => 'Cài đặt',
    ];

    /** Danh sách nhóm quyền */
    public function index()
    {
        $nhomQuyens = NhomQuyen::withCount('taiKhoans')->orderBy('nhomQuyenId')->get();
        return view('admin.phan-quyen.index', compact('nhomQuyens'));
    }

    /** Form tạo nhóm mới */
    public function create()
    {
        $tinhNangs = self::TINH_NANG;
        return view('admin.phan-quyen.create', compact('tinhNangs'));
    }

    /** Lưu nhóm mới */
    public function store(Request $request)
    {
        $request->validate([
            'tenNhom' => 'required|string|max:100|unique:nhomQuyen,tenNhom',
            'moTa'    => 'nullable|string|max:255',
        ], [
            'tenNhom.required' => 'Vui lòng nhập tên nhóm.',
            'tenNhom.unique'   => 'Tên nhóm đã tồn tại.',
        ]);

        $nhom = NhomQuyen::create([
            'tenNhom' => $request->tenNhom,
            'moTa'    => $request->moTa,
        ]);

        // Lưu quyền
        $this->savePermissions($nhom, $request);

        return redirect()->route('admin.phan-quyen.index')
            ->with('success', 'Đã tạo nhóm quyền "' . $nhom->tenNhom . '".');
    }

    /** Form sửa nhóm */
    public function edit($id)
    {
        $nhom      = NhomQuyen::with('phanQuyens')->findOrFail($id);
        $tinhNangs = self::TINH_NANG;

        // Chuyển phanQuyens thành map dễ dùng trong Blade
        $quyenHienTai = [];
        foreach ($nhom->phanQuyens as $pq) {
            $quyenHienTai[$pq->tinhNang] = [
                'xem'  => $pq->coXem,
                'them' => $pq->coThem,
                'sua'  => $pq->coSua,
                'xoa'  => $pq->coXoa,
            ];
        }

        return view('admin.phan-quyen.edit', compact('nhom', 'tinhNangs', 'quyenHienTai'));
    }

    /** Cập nhật nhóm */
    public function update(Request $request, $id)
    {
        $nhom = NhomQuyen::findOrFail($id);

        $request->validate([
            'tenNhom' => 'required|string|max:100|unique:nhomQuyen,tenNhom,' . $id . ',nhomQuyenId',
            'moTa'    => 'nullable|string|max:255',
        ], [
            'tenNhom.required' => 'Vui lòng nhập tên nhóm.',
            'tenNhom.unique'   => 'Tên nhóm đã tồn tại.',
        ]);

        $nhom->update([
            'tenNhom' => $request->tenNhom,
            'moTa'    => $request->moTa,
        ]);

        // Xóa quyền cũ rồi lưu lại
        $nhom->phanQuyens()->delete();
        $this->savePermissions($nhom, $request);

        return redirect()->route('admin.phan-quyen.index')
            ->with('success', 'Đã cập nhật nhóm quyền "' . $nhom->tenNhom . '".');
    }

    /** Xóa nhóm */
    public function destroy($id)
    {
        $nhom = NhomQuyen::findOrFail($id);
        $ten  = $nhom->tenNhom;

        // Gỡ nhóm khỏi các user đang dùng
        $nhom->taiKhoans()->update(['nhomQuyenId' => null]);
        $nhom->delete();

        return redirect()->route('admin.phan-quyen.index')
            ->with('success', 'Đã xóa nhóm quyền "' . $ten . '".');
    }

    /** Helper: lưu ma trận quyền từ request */
    private function savePermissions(NhomQuyen $nhom, Request $request): void
    {
        $permissions = $request->input('quyen', []);

        foreach (array_keys(self::TINH_NANG) as $feature) {
            $data = $permissions[$feature] ?? [];

            PhanQuyen::updateOrCreate(
                ['nhomQuyenId' => $nhom->nhomQuyenId, 'tinhNang' => $feature],
                [
                    'coXem'  => isset($data['xem'])  ? 1 : 0,
                    'coThem' => isset($data['them']) ? 1 : 0,
                    'coSua'  => isset($data['sua'])  ? 1 : 0,
                    'coXoa'  => isset($data['xoa'])  ? 1 : 0,
                ]
            );
        }
    }
}
