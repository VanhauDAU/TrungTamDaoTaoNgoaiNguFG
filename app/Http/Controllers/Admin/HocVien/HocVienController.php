<?php

namespace App\Http\Controllers\Admin\HocVien;

use App\Exports\HocViensExport;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\HoSoNguoiDung;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class HocVienController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:hoc_vien,xem')->only('index', 'export', 'trash');
        $this->middleware('permission:hoc_vien,them')->only('create', 'store', 'restore');
        $this->middleware('permission:hoc_vien,sua')->only('edit', 'update');
        $this->middleware('permission:hoc_vien,xoa')->only('destroy');
    }

    /** Danh sách học viên */
    public function index(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        $hocViens = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ──────────────────────────────────
        $tongSo       = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count();
        $dangHoatDong = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->where('trangThai', 1)->count();
        $thangNay     = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.hoc-vien.index', compact(
            'hocViens',
            'tongSo',
            'dangHoatDong',
            'thangNay'
        ));
    }

    /** Xuất danh sách học viên ra Excel theo bộ lọc hiện tại */
    public function export(Request $request)
    {
        $fileName = 'hoc-vien-' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new HocViensExport($this->buildIndexQuery($request)),
            $fileName
        );
    }

    /** Form thêm học viên mới */
    public function create()
    {
        return view('admin.hoc-vien.create');
    }

    /** Lưu học viên mới */
    public function store(Request $request)
    {
        $request->validate([
            'taiKhoan'      => 'required|string|max:50',
            'email'         => 'required|email|max:100|unique:taikhoan,email',
            'matKhau'       => 'required|string|min:8|confirmed',
            'hoTen'         => 'required|string|max:100',
            'soDienThoai'   => 'nullable|string|max:20',
            'zalo'          => 'nullable|string|max:20',
            'ngaySinh'      => 'nullable|date',
            'gioiTinh'      => 'nullable|in:0,1,2',
            'diaChi'        => 'nullable|string|max:255',
            'cccd'          => 'nullable|string|max:20|unique:hosonguoidung,cccd',
            'nguoiGiamHo'   => 'nullable|string|max:100',
            'sdtGuardian'   => 'nullable|string|max:20',
            'moiQuanHe'     => 'nullable|string|max:50',
            'trinhDoHienTai'=> 'nullable|string|max:30',
            'ngonNguMucTieu'=> 'nullable|string|max:50',
            'nguonBietDen'  => 'nullable|string|max:50',
            'ghiChu'        => 'nullable|string',
        ], [
            'taiKhoan.required'  => 'Vui lòng nhập tên đăng nhập.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.unique'       => 'Email đã được sử dụng.',
            'matKhau.required'   => 'Vui lòng nhập mật khẩu.',
            'matKhau.min'        => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required'     => 'Vui lòng nhập họ và tên.',
            'cccd.unique'        => 'CCCD/CMND này đã được đăng ký.',
        ]);

        DB::transaction(function () use ($request) {
            $tenDangNhap = $this->generateUniqueUsername($request->taiKhoan);

            $taiKhoan = TaiKhoan::create([
                'taiKhoan'  => $tenDangNhap,
                'email'     => $request->email,
                'matKhau'   => Hash::make($request->matKhau),
                'role'      => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1,
            ]);

            HoSoNguoiDung::create([
                'taiKhoanId'    => $taiKhoan->taiKhoanId,
                'hoTen'         => $request->hoTen,
                'soDienThoai'   => $request->soDienThoai,
                'zalo'          => $request->zalo,
                'ngaySinh'      => $request->ngaySinh ?: null,
                'gioiTinh'      => $request->gioiTinh,
                'diaChi'        => $request->diaChi,
                'cccd'          => $request->cccd,
                'nguoiGiamHo'   => $request->nguoiGiamHo,
                'sdtGuardian'   => $request->sdtGuardian,
                'moiQuanHe'     => $request->moiQuanHe,
                'trinhDoHienTai'=> $request->trinhDoHienTai,
                'ngonNguMucTieu'=> $request->ngonNguMucTieu,
                'nguonBietDen'  => $request->nguonBietDen,
                'ghiChu'        => $request->ghiChu,
            ]);
        });

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', 'Đã tạo học viên «' . $request->hoTen . '» thành công.');
    }

    /** Form chỉnh sửa học viên */
    public function edit(string $taiKhoan)
    {
        $hocVien = TaiKhoan::with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        return view('admin.hoc-vien.edit', compact('hocVien'));
    }

    /** Cập nhật học viên */
    public function update(Request $request, string $taiKhoan)
    {
        $hocVien = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        $request->validate([
            'email'         => ['required', 'email', 'max:100',
                                Rule::unique('taikhoan', 'email')->ignore($taiKhoan, 'taiKhoan')],
            'hoTen'         => 'required|string|max:100',
            'trangThai'     => 'required|in:0,1',
            'matKhau'       => 'nullable|string|min:8|confirmed',
            'soDienThoai'   => 'nullable|string|max:20',
            'zalo'          => 'nullable|string|max:20',
            'ngaySinh'      => 'nullable|date',
            'gioiTinh'      => 'nullable|in:0,1,2',
            'diaChi'        => 'nullable|string|max:255',
            'cccd'          => ['nullable', 'string', 'max:20',
                                Rule::unique('hosonguoidung', 'cccd')->ignore($taiKhoan, 'taiKhoan')],
            'nguoiGiamHo'   => 'nullable|string|max:100',
            'sdtGuardian'   => 'nullable|string|max:20',
            'moiQuanHe'     => 'nullable|string|max:50',
            'trinhDoHienTai'=> 'nullable|string|max:30',
            'ngonNguMucTieu'=> 'nullable|string|max:50',
            'nguonBietDen'  => 'nullable|string|max:50',
            'ghiChu'        => 'nullable|string',
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.unique'      => 'Email đã được sử dụng bởi tài khoản khác.',
            'hoTen.required'    => 'Vui lòng nhập họ và tên.',
            'matKhau.min'       => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'cccd.unique'       => 'CCCD/CMND đã được đăng ký bởi học viên khác.',
        ]);

        DB::transaction(function () use ($request, $hocVien, $taiKhoan) {
            // Cập nhật tài khoản
            $taiKhoanData = [
                'email'     => $request->email,
                'trangThai' => $request->trangThai,
            ];
            // Đổi mật khẩu chỉ khi nhập
            if ($request->filled('matKhau')) {
                $taiKhoanData['matKhau'] = Hash::make($request->matKhau);
            }
            $hocVien->update($taiKhoanData);

            // Cập nhật hồ sơ
            $hocVien->hoSoNguoiDung()->updateOrCreate(
                ['taiKhoanId' => $taiKhoan],
                [
                    'hoTen'         => $request->hoTen,
                    'soDienThoai'   => $request->soDienThoai,
                    'zalo'          => $request->zalo,
                    'ngaySinh'      => $request->ngaySinh ?: null,
                    'gioiTinh'      => $request->gioiTinh,
                    'diaChi'        => $request->diaChi,
                    'cccd'          => $request->cccd,
                    'nguoiGiamHo'   => $request->nguoiGiamHo,
                    'sdtGuardian'   => $request->sdtGuardian,
                    'moiQuanHe'     => $request->moiQuanHe,
                    'trinhDoHienTai'=> $request->trinhDoHienTai,
                    'ngonNguMucTieu'=> $request->ngonNguMucTieu,
                    'nguonBietDen'  => $request->nguonBietDen,
                    'ghiChu'        => $request->ghiChu,
                ]
            );
        });

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', 'Đã cập nhật thông tin học viên thành công.');
    }

    /**
     * Thùng rác: danh sách học viên đã xóa mềm.
     */
    public function trash(Request $request)
    {
        $query = TaiKhoan::onlyTrashed()
            ->with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        $hocViens   = $query->orderByDesc('deleted_at')->paginate(15)->withQueryString();
        $tongXoa    = TaiKhoan::onlyTrashed()->where('role', TaiKhoan::ROLE_HOC_VIEN)->count();

        return view('admin.hoc-vien.trash', compact('hocViens', 'tongXoa'));
    }

    /**
     * Khôi phục học viên đã xóa mềm (xóa deleted_at).
     */
    public function restore(string $taiKhoan)
    {
        $hocVien = TaiKhoan::onlyTrashed()
            ->where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        $hoTen = $hocVien->hoSoNguoiDung->hoTen ?? $hocVien->taiKhoan;
        $hocVien->restore(); // xóa deleted_at

        return redirect()->route('admin.hoc-vien.trash')
            ->with('success', "Đã khôi phục học viên «{$hoTen}» thành công.");
    }

    /**
     * Xóa mềm học viên (SoftDelete — set deleted_at).
     * Không vi phạm FK với dangkylophoc, hoadon, ...
     */
    public function destroy(string $taiKhoan)
    {
        $hocVien = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
        $hoTen   = $hocVien->hoSoNguoiDung->hoTen ?? $hocVien->taiKhoan;

        $hocVien->delete(); // SoftDelete: chỉ set deleted_at

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', "Đã xóa học viên «{$hoTen}».");
    }

    /**
     * Tạo tên đăng nhập duy nhất:
     * User_123456 → nếu trùng → User_123456_1 → User_123456_2 ...
     */
    private function generateUniqueUsername(string $base): string
    {
        $candidate = $base;
        $counter   = 1;

        while (TaiKhoan::where('taiKhoan', $candidate)->exists()) {
            $candidate = $base . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }

    /** Tạo query chung cho danh sách và export */
    private function buildIndexQuery(Request $request): Builder
    {
        $query = TaiKhoan::query()
            ->with('hoSoNguoiDung')
            ->withCount('dangKyLopHocs')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN);

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

        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'taiKhoanId');
        $dir     = $request->get('dir', 'desc');

        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'], true)) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        return $query;
    }
}
