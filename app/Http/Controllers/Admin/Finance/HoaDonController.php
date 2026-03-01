<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoaDonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tai_chinh,xem')->only('index', 'show');
        $this->middleware('permission:tai_chinh,sua')->only('update', 'storePhieuThu', 'destroyPhieuThu');
    }

    /** Danh sách hóa đơn */
    public function index(Request $request)
    {
        $query = HoaDon::with([
            'taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.lopHoc.khoaHoc',
            'coSo',
            'nguoiLap.hoSoNguoiDung',
        ]);

        // ── Tìm kiếm (mã HD, tên học viên, email) ──────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('maHoaDon', 'like', "%{$search}%")
                    ->orWhereHas('taiKhoan', function ($q2) use ($search) {
                        $q2->where('email', 'like', "%{$search}%")
                            ->orWhereHas('hoSoNguoiDung', fn($q3) => $q3->where('hoTen', 'like', "%{$search}%"));
                    });
            });
        }

        // ── Lọc trạng thái ────────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Lọc cơ sở ────────────────────────────────────────
        if ($request->filled('coSoId')) {
            $query->where('coSoId', $request->coSoId);
        }

        // ── Lọc khoảng ngày ──────────────────────────────────
        if ($request->filled('tuNgay')) {
            $query->whereDate('ngayLap', '>=', $request->tuNgay);
        }
        if ($request->filled('denNgay')) {
            $query->whereDate('ngayLap', '<=', $request->denNgay);
        }

        // ── Lọc theo tình trạng hạn thanh toán ────────────────
        if ($request->filled('hanThanhToan')) {
            $today = now()->toDateString();
            $canh  = now()->addDays(HoaDon::NGAY_CANH_BAO)->toDateString();
            match ($request->hanThanhToan) {
                'sap_het_han' => $query
                    ->whereNotNull('ngayHetHan')
                    ->where('ngayHetHan', '>=', $today)
                    ->where('ngayHetHan', '<=', $canh)
                    ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT),
                'qua_han' => $query
                    ->whereNotNull('ngayHetHan')
                    ->whereDate('ngayHetHan', '<', $today)
                    ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT),
                default => null,
            };
        }

        // ── Sắp xếp ──────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'hoaDonId');
        $dir = $request->get('dir', 'desc');
        $allowedSort = ['hoaDonId', 'ngayLap', 'tongTien', 'daTra', 'trangThai'];
        if (in_array($orderBy, $allowedSort)) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $hoaDons = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ────────────────────────────────────
        $tongSo      = HoaDon::count();
        $chuaTT      = HoaDon::where('trangThai', HoaDon::TRANG_THAI_CHUA_TT)->count();
        $motPhan     = HoaDon::where('trangThai', HoaDon::TRANG_THAI_MOT_PHAN)->count();
        $daTT        = HoaDon::where('trangThai', HoaDon::TRANG_THAI_DA_TT)->count();
        $tongDoanhThu= HoaDon::sum('daTra');

        $todayStr = now()->toDateString();
        $canhStr  = now()->addDays(HoaDon::NGAY_CANH_BAO)->toDateString();

        $sapHetHan = HoaDon::whereNotNull('ngayHetHan')
            ->where('ngayHetHan', '>=', $todayStr)
            ->where('ngayHetHan', '<=', $canhStr)
            ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)
            ->count();

        $quaHan = HoaDon::whereNotNull('ngayHetHan')
            ->whereDate('ngayHetHan', '<', $todayStr)
            ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)
            ->count();

        // ── Danh sách cơ sở (cho dropdown filter) ─────────────
        $coSos = CoSoDaoTao::orderBy('tenCoSo')->get();

        return view('admin.hoa-don.index', compact(
            'hoaDons',
            'tongSo',
            'chuaTT',
            'motPhan',
            'daTT',
            'tongDoanhThu',
            'coSos',
            'sapHetHan',
            'quaHan'
        ));
    }

    /** Chi tiết hóa đơn */
    public function show(int $id)
    {
        $hoaDon = HoaDon::with([
            'taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.lopHoc.khoaHoc',
            'coSo.tinhThanh',
            'nguoiLap.hoSoNguoiDung',
            'phieuThus.taiKhoan.hoSoNguoiDung',
            'phieuThus.nguoiDuyet.hoSoNguoiDung',
        ])->findOrFail($id);

        return view('admin.hoa-don.show', compact('hoaDon'));
    }

    /** Cập nhật hóa đơn (ghi chú, hạn, giảm giá) */
    public function update(Request $request, int $id)
    {
        $hoaDon = HoaDon::findOrFail($id);

        $data = $request->validate([
            'ghiChu' => 'nullable|string|max:500',
            'ngayHetHan' => 'nullable|date',
            'giamGia' => 'nullable|numeric|min:0',
            'loaiHoaDon' => 'nullable|in:0,1,2',
        ]);

        $hoaDon->update($data);

        return redirect()
            ->route('admin.hoa-don.show', $id)
            ->with('success', 'Đã cập nhật hóa đơn thành công.');
    }

    /** Tạo phiếu thu mới */
    public function storePhieuThu(Request $request, int $hoaDonId)
    {
        $hoaDon = HoaDon::findOrFail($hoaDonId);

        $data = $request->validate([
            'soTien' => 'required|numeric|min:1000',
            'ngayThu' => 'required|date',
            'phuongThucThanhToan' => 'required|in:1,2,3',
            'ghiChu' => 'nullable|string|max:500',
        ], [
            'soTien.required' => 'Vui lòng nhập số tiền.',
            'soTien.min' => 'Số tiền phải tối thiểu 1.000đ.',
            'ngayThu.required' => 'Vui lòng chọn ngày thu.',
        ]);

        DB::transaction(function () use ($data, $hoaDon) {
            $phieuThu = PhieuThu::create([
                'maPhieuThu' => PhieuThu::generateMaPhieuThu(),
                'hoaDonId' => $hoaDon->hoaDonId,
                'soTien' => $data['soTien'],
                'ngayThu' => $data['ngayThu'],
                'phuongThucThanhToan' => $data['phuongThucThanhToan'],
                'taiKhoanId' => auth()->user()->taiKhoanId,
                'nguoiDuyetId' => auth()->user()->taiKhoanId,
                'ghiChu' => $data['ghiChu'] ?? null,
                'trangThai' => PhieuThu::TRANG_THAI_HOP_LE,
            ]);

            // Tính lại tổng đã trả & trạng thái hóa đơn
            $hoaDon->recalculate();
        });

        return redirect()
            ->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã tạo phiếu thu thành công.');
    }

    /** Hủy phiếu thu (soft: đổi trạng thái = 0) */
    public function destroyPhieuThu(int $id)
    {
        $phieuThu = PhieuThu::findOrFail($id);
        $hoaDonId = $phieuThu->hoaDonId;

        DB::transaction(function () use ($phieuThu) {
            $phieuThu->update(['trangThai' => PhieuThu::TRANG_THAI_HUY]);
            $phieuThu->hoaDon->recalculate();
        });

        return redirect()
            ->route('admin.hoa-don.show', $hoaDonId)
            ->with('success', 'Đã hủy phiếu thu thành công.');
    }
}
