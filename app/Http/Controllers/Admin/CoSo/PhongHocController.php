<?php

namespace App\Http\Controllers\Admin\CoSo;

use App\Http\Controllers\Controller;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PhongHocController extends Controller
{
    /**
     * Danh sách phòng học (có thể lọc theo cơ sở).
     */
    public function index(Request $request)
    {
        $query = PhongHoc::with('coSoDaoTao');

        if ($search = $request->q) {
            $query->where('tenPhong', 'like', "%{$search}%");
        }

        if ($request->filled('coSoId')) {
            $query->where('coSoId', $request->coSoId);
        }

        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        $phongHocs = $query->orderBy('coSoId')->orderBy('tenPhong')->paginate(20)->withQueryString();
        $coSos     = CoSoDaoTao::orderBy('maCoSo')->get();
        $tongSo    = PhongHoc::count();
        $hoatDong  = PhongHoc::where('trangThai', PhongHoc::TRANG_THAI_SAN_SANG)->count();

        return view('admin.co-so.phong-hoc.index', compact('phongHocs', 'coSos', 'tongSo', 'hoatDong'));
    }

    /**
     * Lưu phòng mới (gọi từ trang show của cơ sở).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenPhong'     => 'required|string|max:50',
            'coSoId'       => 'required|exists:cosodaotao,coSoId',
            'sucChua'      => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai'    => 'required|in:0,1,3',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
            'coSoId.required'   => 'Vui lòng chọn cơ sở.',
            'coSoId.exists'     => 'Cơ sở không tồn tại.',
        ]);

        $phong = PhongHoc::create($request->only(['tenPhong', 'coSoId', 'sucChua', 'trangThietBi', 'trangThai']));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm phòng «' . $phong->tenPhong . '» thành công.',
                'room'    => $phong->fresh()
            ]);
        }

        return redirect()->route('admin.co-so.show', $request->coSoId)
            ->with('success', 'Đã thêm phòng «' . $request->tenPhong . '» thành công.');
    }

    /**
     * Cập nhật phòng (AJAX-friendly, redirect về show cơ sở).
     */
    public function update(Request $request, int $id)
    {
        $phong = PhongHoc::findOrFail($id);

        $request->validate([
            'tenPhong'     => 'required|string|max:50',
            'sucChua'      => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai'    => 'required|in:0,1,3',
            'ghiChuBaoTri' => 'nullable|string|max:500',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
        ]);

        $data = $request->only(['tenPhong', 'sucChua', 'trangThietBi', 'trangThai', 'ghiChuBaoTri']);

        // Nếu chuyển sang bảo trì → ghi ngày bảo trì
        if ((int) $request->trangThai === PhongHoc::TRANG_THAI_BAO_TRI && (int) $phong->trangThai !== PhongHoc::TRANG_THAI_BAO_TRI) {
            $data['ngayBaoTri'] = Carbon::now();
        }
        // Nếu chuyển về sẵn sàng → xóa ngày bảo trì
        if ((int) $request->trangThai === PhongHoc::TRANG_THAI_SAN_SANG) {
            $data['ngayBaoTri'] = null;
        }

        $phong->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật phòng «' . $phong->tenPhong . '» thành công.',
                'room'    => $phong->fresh()
            ]);
        }

        return redirect()->route('admin.co-so.show', $phong->coSoId)
            ->with('success', 'Đã cập nhật phòng «' . $phong->tenPhong . '».');
    }

    /**
     * Xóa phòng học — Soft Delete.
     * Kiểm tra có lớp đang hoạt động trong phòng không trước khi xóa.
     */
    public function destroy(Request $request, int $id)
    {
        $phong  = PhongHoc::withCount(['lopHocDangHoc'])->findOrFail($id);
        $coSoId = $phong->coSoId;
        $ten    = $phong->tenPhong;

        // Không cho xóa nếu còn lớp đang hoạt động
        if ($phong->lop_hoc_dang_hoc_count > 0) {
            $msg = "Không thể xóa phòng «{$ten}» vì còn {$phong->lop_hoc_dang_hoc_count} lớp học đang hoạt động trong phòng này.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.co-so.show', $coSoId)->with('error', $msg);
        }

        $phong->delete(); // Soft delete (nhờ SoftDeletes trait)

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa phòng «' . $ten . '» thành công.'
            ]);
        }

        return redirect()->route('admin.co-so.show', $coSoId)
            ->with('success', "Đã xóa phòng «{$ten}».");
    }

    /**
     * Bật/tắt trạng thái bảo trì nhanh (AJAX).
     */
    public function toggleStatus(Request $request, int $id)
    {
        $phong = PhongHoc::findOrFail($id);

        $request->validate([
            'ghiChuBaoTri' => 'nullable|string|max:500',
        ]);

        if ($phong->isAvailable()) {
            // Chuyển sang bảo trì
            $phong->update([
                'trangThai'    => PhongHoc::TRANG_THAI_BAO_TRI,
                'ngayBaoTri'   => Carbon::now(),
                'ghiChuBaoTri' => $request->ghiChuBaoTri,
            ]);
            $msg = "Phòng «{$phong->tenPhong}» đã chuyển sang trạng thái bảo trì.";
        } else {
            // Chuyển về sẵn sàng
            $phong->update([
                'trangThai'    => PhongHoc::TRANG_THAI_SAN_SANG,
                'ngayBaoTri'   => null,
                'ghiChuBaoTri' => null,
            ]);
            $msg = "Phòng «{$phong->tenPhong}» đã sẵn sàng sử dụng.";
        }

        return response()->json([
            'success'   => true,
            'message'   => $msg,
            'trangThai' => $phong->fresh()->trangThai,
            'room'      => $phong->fresh(),
        ]);
    }

    /**
     * Lịch sử sử dụng phòng (AJAX).
     */
    public function lichSu(int $id)
    {
        $phong = PhongHoc::findOrFail($id);

        $lichSu = $phong->lopHocs()
            ->with(['khoaHoc:khoaHocId,tenKhoaHoc', 'taiKhoan.hoSoNguoiDung', 'taiKhoan.nhanSu'])
            ->orderByDesc('ngayBatDau')
            ->take(20)
            ->get()
            ->map(function ($lop) {
                $tenGV = optional($lop->taiKhoan->hoSoNguoiDung ?? null)->hoTen
                    ?? optional($lop->taiKhoan->nhanSu ?? null)->hoTen
                    ?? '—';
                return [
                    'lopHocId'    => $lop->lopHocId,
                    'maLopHoc'    => $lop->maLopHoc,
                    'tenLopHoc'   => $lop->tenLopHoc,
                    'tenKhoaHoc'  => $lop->khoaHoc->tenKhoaHoc ?? '—',
                    'tenGiaoVien' => $tenGV,
                    'ngayBatDau'  => $lop->ngayBatDau ? \Carbon\Carbon::parse($lop->ngayBatDau)->format('d/m/Y') : '—',
                    'ngayKetThuc' => $lop->ngayKetThuc ? \Carbon\Carbon::parse($lop->ngayKetThuc)->format('d/m/Y') : '—',
                    'trangThai'   => $lop->trangThai,
                    'trangThaiLabel' => $lop->trangThaiLabel,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $lichSu,
            'total'   => $phong->lopHocs()->count(),
        ]);
    }
}
