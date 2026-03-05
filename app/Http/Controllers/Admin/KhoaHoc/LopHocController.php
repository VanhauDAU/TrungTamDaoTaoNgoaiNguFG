<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Course\KhoaHoc;
use App\Models\Course\HocPhi;
use App\Models\Education\LopHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\TinhThanh;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\NhanSu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LopHocController extends Controller
{
    /** Danh sách lớp học */
    public function index(Request $request)
    {
        $query = LopHoc::with([
            'khoaHoc',
            'coSo',
            'caHoc',
            'taiKhoan.hoSoNguoiDung',
            'dangKyLopHocs',
        ]);

        // ── Tìm kiếm ─────────────────────────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenLopHoc', 'like', "%{$search}%")
                    ->orWhereHas('khoaHoc', fn($q2) => $q2->where('tenKhoaHoc', 'like', "%{$search}%"));
            });
        }

        // ── Lọc khóa học ─────────────────────────────────────
        if ($request->filled('khoaHocId')) {
            $query->where('khoaHocId', $request->khoaHocId);
        }

        // ── Lọc cơ sở ────────────────────────────────────────
        if ($request->filled('coSoId')) {
            $query->where('coSoId', $request->coSoId);
        }

        // ── Lọc trạng thái ───────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ──────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'lopHocId');
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['lopHocId', 'tenLopHoc', 'ngayBatDau'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $lopHocs = $query->paginate(15)->withQueryString();

        // ── Stats ─────────────────────────────────────────────
        $tongLop = LopHoc::count();
        $dangHoc = LopHoc::where('trangThai', 4)->count();
        $sapMo = LopHoc::where('trangThai', 0)->count();

        // ── Data cho filter ───────────────────────────────────
        $khoaHocs = KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get();
        $coSos = CoSoDaoTao::where('trangThai', 1)->orderBy('tenCoSo')->get();

        return view('admin.lop-hoc.index', compact(
            'lopHocs',
            'tongLop',
            'dangHoc',
            'sapMo',
            'khoaHocs',
            'coSos'
        ));
    }

    /** Form thêm lớp học */
    public function create(Request $request)
    {
        $khoaHocs = KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get();
        $caHocs = CaHoc::where('trangThai', 1)->orderBy('tenCa')->get();
        $tinhThanhs = TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
            ->orderBy('tenTinhThanh')
            ->get(); // Chỉ lấy tỉnh có cơ sở hoạt động
        $selectedKhoaHocId = $request->get('khoaHocId');

        // Load gói học phí theo khóa nếu đã chọn trước
        $hocPhis = $selectedKhoaHocId
            ? HocPhi::where('khoaHocId', $selectedKhoaHocId)->where('trangThai', 1)->get()
            : collect();

        return view('admin.lop-hoc.create', compact(
            'khoaHocs',
            'caHocs',
            'tinhThanhs',
            'selectedKhoaHocId',
            'hocPhis'
        ));
    }

    /** Lưu lớp học */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenLopHoc' => 'required|string|max:255',
            'khoaHocId' => 'required|exists:khoahoc,khoaHocId',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'caHocId' => 'required|exists:cahoc,caHocId',
            'taiKhoanId' => 'nullable|exists:taikhoan,taiKhoanId',
            'phongHocId' => 'nullable|exists:phonghoc,phongHocId',
            'ngayBatDau' => 'required|date',
            'ngayKetThuc' => 'required|date|after:ngayBatDau',
            'soBuoiDuKien' => 'nullable|integer|min:1',
            'soHocVienToiDa' => 'nullable|integer|min:1',
            'donGiaDay' => 'nullable|numeric|min:0',
            'hocPhiId' => 'nullable|exists:hocphi,hocPhiId',
            'lichHoc' => 'nullable|string|max:20',
            'trangThai' => 'required|in:0,1,2,3,4',
        ], [
            'tenLopHoc.required' => 'Vui lòng nhập tên lớp học.',
            'khoaHocId.required' => 'Vui lòng chọn khóa học.',
            'coSoId.required' => 'Vui lòng chọn cơ sở.',
            'caHocId.required' => 'Vui lòng chọn ca học.',
            'ngayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'ngayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'ngayKetThuc.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ]);

        $data['slug'] = $this->generateUniqueSlug($request->tenLopHoc);

        // Kiểm tra sĩ số không vượt sức chứa phòng học
        if (!empty($data['phongHocId']) && !empty($data['soHocVienToiDa'])) {
            $phong = PhongHoc::find($data['phongHocId']);
            if ($phong && $data['soHocVienToiDa'] > $phong->sucChua) {
                return back()->withInput()
                    ->withErrors(['soHocVienToiDa' => 'Sĩ số tối đa (' . $data['soHocVienToiDa'] . ') không được vượt quá sức chứa phòng học ' . $phong->tenPhong . ' (' . $phong->sucChua . ' chỗ).']);
            }
        }

        LopHoc::create($data);

        return redirect()->route('admin.lop-hoc.index')
            ->with('success', 'Đã thêm lớp học «' . $request->tenLopHoc . '» thành công.');
    }

    /** Chi tiết lớp học */
    public function show(string $slug)
    {
        $lopHoc = LopHoc::with([
            'khoaHoc',
            'coSo',
            'caHoc',
            'phongHoc',
            'taiKhoan.hoSoNguoiDung',
            'buoiHocs.caHoc',
            'buoiHocs.phongHoc',
            'buoiHocs.taiKhoan.hoSoNguoiDung',
            'dangKyLopHocs.taiKhoan.hoSoNguoiDung',
        ])->where('slug', $slug)->firstOrFail();

        $caHocs = CaHoc::where('trangThai', 1)->orderBy('tenCa')->get();
        $phongHocs = PhongHoc::where('coSoId', $lopHoc->coSoId)->get();
        $coSoId = $lopHoc->coSoId;
        $giaoVienCoSo = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->whereHas('nhanSu', fn($q) => $q->where('coSoId', $coSoId))
            ->get();

        $giaoVienKhac = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->where(function($q) use ($coSoId) {
                $q->whereDoesntHave('nhanSu')
                  ->orWhereHas('nhanSu', fn($sq) => $sq->where('coSoId', '!=', $coSoId));
            })
            ->get();

        $soHocVienDangKy = $lopHoc->dangKyLopHocs->count();
        $soBuoiDaHoc = $lopHoc->buoiHocs->where('daHoanThanh', 1)->count();
        $soBuoiChuaHoc = $lopHoc->buoiHocs->where('daHoanThanh', 0)->count();

        return view('admin.lop-hoc.show', compact(
            'lopHoc',
            'caHocs',
            'phongHocs',
            'giaoVienCoSo',
            'giaoVienKhac',
            'soHocVienDangKy',
            'soBuoiDaHoc',
            'soBuoiChuaHoc'
        ));
    }

    /** Form chỉnh sửa lớp học */
    public function edit(string $slug)
    {
        $lopHoc = LopHoc::with('coSo')->where('slug', $slug)->firstOrFail();
        $khoaHocs = KhoaHoc::where('trangThai', 1)->orderBy('tenKhoaHoc')->get();
        $caHocs = CaHoc::where('trangThai', 1)->orderBy('tenCa')->get();
        $tinhThanhs = TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
            ->orderBy('tenTinhThanh')
            ->get();
        $phongHocs = PhongHoc::where('coSoId', $lopHoc->coSoId)->get();
        $coSoId = $lopHoc->coSoId;
        $giaoVienCoSo = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->whereHas('nhanSu', fn($q) => $q->where('coSoId', $coSoId))
            ->get();

        $giaoVienKhac = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->where(function($q) use ($coSoId) {
                $q->whereDoesntHave('nhanSu')
                  ->orWhereHas('nhanSu', fn($sq) => $sq->where('coSoId', '!=', $coSoId));
            })
            ->get();
        // Gói học phí của khóa học này
        $hocPhis = HocPhi::where('khoaHocId', $lopHoc->khoaHocId)->where('trangThai', 1)->get();

        // Thông tin vị trí hiện tại của cơ sở để pre-populate cascade
        $currentCoSo = $lopHoc->coSo;

        return view('admin.lop-hoc.edit', compact(
            'lopHoc',
            'khoaHocs',
            'caHocs',
            'tinhThanhs',
            'phongHocs',
            'giaoVienCoSo',
            'giaoVienKhac',
            'hocPhis',
            'currentCoSo'
        ));
    }

    /** Cập nhật lớp học */
    public function update(Request $request, string $slug)
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'tenLopHoc' => 'required|string|max:255',
            'khoaHocId' => 'required|exists:khoahoc,khoaHocId',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'caHocId' => 'required|exists:cahoc,caHocId',
            'taiKhoanId' => 'nullable|exists:taikhoan,taiKhoanId',
            'phongHocId' => 'nullable|exists:phonghoc,phongHocId',
            'ngayBatDau' => 'required|date',
            'ngayKetThuc' => 'required|date|after:ngayBatDau',
            'soBuoiDuKien' => 'nullable|integer|min:1',
            'soHocVienToiDa' => 'nullable|integer|min:1',
            'donGiaDay' => 'nullable|numeric|min:0',
            'hocPhiId' => 'nullable|exists:hocphi,hocPhiId',
            'lichHoc' => 'nullable|string|max:20',
            'trangThai' => 'required|in:0,1,2,3,4',
        ]);

        // Kiểm tra sĩ số không vượt sức chứa phòng học
        if (!empty($data['phongHocId']) && !empty($data['soHocVienToiDa'])) {
            $phong = PhongHoc::find($data['phongHocId']);
            if ($phong && $data['soHocVienToiDa'] > $phong->sucChua) {
                return back()->withInput()
                    ->withErrors(['soHocVienToiDa' => 'Sĩ số tối đa (' . $data['soHocVienToiDa'] . ') không được vượt quá sức chứa phòng học ' . $phong->tenPhong . ' (' . $phong->sucChua . ' chỗ).']);
            }
        }

        $lopHoc->update($data);

        return redirect()->route('admin.lop-hoc.show', $slug)
            ->with('success', 'Đã cập nhật lớp học «' . $lopHoc->tenLopHoc . '» thành công.');
    }

    /** Xóa lớp học */
    public function destroy(string $slug)
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();

        if ($lopHoc->dangKyLopHocs()->count() > 0) {
            return redirect()->route('admin.lop-hoc.index')
                ->with('error', 'Không thể xóa lớp học đã có học viên đăng ký.');
        }

        $ten = $lopHoc->tenLopHoc;
        $lopHoc->buoiHocs()->delete();
        $lopHoc->delete();

        return redirect()->route('admin.lop-hoc.index')
            ->with('success', "Đã xóa lớp học «{$ten}» thành công.");
    }

    /** API: Lấy HocPhi theo khóa học */
    public function getHocPhiByKhoaHoc(int $khoaHocId)
    {
        $hocPhis = HocPhi::where('khoaHocId', $khoaHocId)
            ->where('trangThai', 1)
            ->get()
            ->map(fn($hp) => [
                'hocPhiId' => $hp->hocPhiId,
                'soBuoi' => $hp->soBuoi,
                'donGia' => $hp->donGia,
                'tongHocPhi' => $hp->tongHocPhi,
                'label' => 'Gói ' . $hp->soBuoi . ' buổi – ' . $hp->tongHocPhiFormat,
            ]);
        return response()->json($hocPhis);
    }

    /** API: Lấy phòng học theo cơ sở */
    public function getPhongByCoso(int $coSoId)
    {
        $phongs = PhongHoc::where('coSoId', $coSoId)
            ->where('trangThai', 1)
            ->get(['phongHocId', 'tenPhong', 'sucChua']);
        return response()->json($phongs);
    }

    /** API: Lấy giáo viên theo cơ sở (phân nhóm) */
    public function getGiaoVienByCoso(int $coSoId)
    {
        // 1. Giáo viên thuộc cơ sở này
        $giaoVienCoSo = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->whereHas('nhanSu', fn($q) => $q->where('coSoId', $coSoId))
            ->get()
            ->map(fn($gv) => [
                'taiKhoanId' => $gv->taiKhoanId,
                'hoTen' => $gv->hoSoNguoiDung->hoTen ?? $gv->taiKhoan,
            ]);

        // 2. Giáo viên thuộc cơ sở KHÁC (hoặc không gắn cơ sở)
        $giaoVienKhac = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('trangThai', 1)
            ->where(function($q) use ($coSoId) {
                $q->whereDoesntHave('nhanSu')
                  ->orWhereHas('nhanSu', fn($sq) => $sq->where('coSoId', '!=', $coSoId));
            })
            ->get()
            ->map(fn($gv) => [
                'taiKhoanId' => $gv->taiKhoanId,
                'hoTen' => $gv->hoSoNguoiDung->hoTen ?? $gv->taiKhoan,
            ]);

        return response()->json([
            'cung_co_so' => $giaoVienCoSo,
            'khac_co_so' => $giaoVienKhac
        ]);
    }

    /** Tạo slug duy nhất */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name, '-');
        $candidate = $slug;
        $counter = 1;
        while (true) {
            $q = LopHoc::where('slug', $candidate);
            if ($excludeId)
                $q->where('lopHocId', '!=', $excludeId);
            if (!$q->exists())
                break;
            $candidate = $slug . '-' . $counter++;
        }
        return $candidate;
    }
}
