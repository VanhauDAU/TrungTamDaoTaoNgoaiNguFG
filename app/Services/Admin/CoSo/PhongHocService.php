<?php

namespace App\Services\Admin\CoSo;

use App\Contracts\Admin\CoSo\PhongHocServiceInterface;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PhongHocService implements PhongHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = PhongHoc::with('coSoDaoTao');

        if ($search = $request->q)
            $query->where('tenPhong', 'like', "%{$search}%");
        if ($request->filled('coSoId'))
            $query->where('coSoId', $request->coSoId);
        if ($request->filled('trangThai'))
            $query->where('trangThai', $request->trangThai);

        return [
            'phongHocs' => $query->orderBy('coSoId')->orderBy('tenPhong')->paginate(20)->withQueryString(),
            'coSos' => CoSoDaoTao::orderBy('maCoSo')->get(),
            'tongSo' => PhongHoc::count(),
            'hoatDong' => PhongHoc::where('trangThai', PhongHoc::TRANG_THAI_SAN_SANG)->count(),
        ];
    }

    public function store(Request $request): PhongHoc
    {
        $request->validate([
            'tenPhong' => 'required|string|max:50',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'sucChua' => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1,3',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
            'coSoId.required' => 'Vui lòng chọn cơ sở.',
            'coSoId.exists' => 'Cơ sở không tồn tại.',
        ]);

        return PhongHoc::create($request->only(['tenPhong', 'coSoId', 'sucChua', 'trangThietBi', 'trangThai']));
    }

    public function update(Request $request, int $id): PhongHoc
    {
        $phong = PhongHoc::findOrFail($id);

        $request->validate([
            'tenPhong' => 'required|string|max:50',
            'sucChua' => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1,3',
            'ghiChuBaoTri' => 'nullable|string|max:500',
        ], ['tenPhong.required' => 'Vui lòng nhập tên phòng.']);

        $data = $request->only(['tenPhong', 'sucChua', 'trangThietBi', 'trangThai', 'ghiChuBaoTri']);

        if ((int)$request->trangThai === PhongHoc::TRANG_THAI_BAO_TRI && (int)$phong->trangThai !== PhongHoc::TRANG_THAI_BAO_TRI) {
            $data['ngayBaoTri'] = Carbon::now();
        }
        if ((int)$request->trangThai === PhongHoc::TRANG_THAI_SAN_SANG) {
            $data['ngayBaoTri'] = null;
        }

        $phong->update($data);
        return $phong->fresh();
    }

    public function destroy(Request $request, int $id): string
    {
        $phong = PhongHoc::withCount(['lopHocDangHoc'])->findOrFail($id);

        if ($phong->lop_hoc_dang_hoc_count > 0) {
            throw new \RuntimeException("Không thể xóa phòng «{$phong->tenPhong}» vì còn {$phong->lop_hoc_dang_hoc_count} lớp học đang hoạt động trong phòng này.");
        }

        $ten = $phong->tenPhong;
        $phong->delete();
        return $ten;
    }

    public function toggleStatus(Request $request, int $id): array
    {
        $phong = PhongHoc::findOrFail($id);
        $request->validate(['ghiChuBaoTri' => 'nullable|string|max:500']);

        if ($phong->isAvailable()) {
            $phong->update(['trangThai' => PhongHoc::TRANG_THAI_BAO_TRI, 'ngayBaoTri' => Carbon::now(), 'ghiChuBaoTri' => $request->ghiChuBaoTri]);
            $msg = "Phòng «{$phong->tenPhong}» đã chuyển sang trạng thái bảo trì.";
        }
        else {
            $phong->update(['trangThai' => PhongHoc::TRANG_THAI_SAN_SANG, 'ngayBaoTri' => null, 'ghiChuBaoTri' => null]);
            $msg = "Phòng «{$phong->tenPhong}» đã sẵn sàng sử dụng.";
        }

        return ['success' => true, 'message' => $msg, 'trangThai' => $phong->fresh()->trangThai, 'room' => $phong->fresh()];
    }

    public function lichSu(int $id): array
    {
        $phong = PhongHoc::findOrFail($id);
        $lichSu = $phong->lopHocs()
            ->with(['khoaHoc:khoaHocId,tenKhoaHoc', 'taiKhoan.hoSoNguoiDung', 'taiKhoan.nhanSu'])
            ->orderByDesc('ngayBatDau')->take(20)->get()
            ->map(function ($lop) {
            $tenGV = optional($lop->taiKhoan->hoSoNguoiDung ?? null)->hoTen
                ?? optional($lop->taiKhoan->nhanSu ?? null)->hoTen ?? '—';
            return [
            'lopHocId' => $lop->lopHocId,
            'maLopHoc' => $lop->maLopHoc,
            'tenLopHoc' => $lop->tenLopHoc,
            'tenKhoaHoc' => $lop->khoaHoc->tenKhoaHoc ?? '—',
            'tenGiaoVien' => $tenGV,
            'ngayBatDau' => $lop->ngayBatDau ?Carbon::parse($lop->ngayBatDau)->format('d/m/Y') : '—',
            'ngayKetThuc' => $lop->ngayKetThuc ?Carbon::parse($lop->ngayKetThuc)->format('d/m/Y') : '—',
            'trangThai' => $lop->trangThai,
            'trangThaiLabel' => $lop->trangThaiLabel,
            ];
        });

        return ['success' => true, 'data' => $lichSu, 'total' => $phong->lopHocs()->count()];
    }
}