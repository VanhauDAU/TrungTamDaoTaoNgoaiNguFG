<?php

namespace App\Services\Admin\TaiChinh;

use App\Contracts\Admin\TaiChinh\HoaDonServiceInterface;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HoaDonService implements HoaDonServiceInterface
{
    public function getList(Request $request): array
    {
        $query = HoaDon::with([
            'taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.lopHoc.khoaHoc',
            'dangKyLopHocPhuPhi',
            'coSo',
            'nguoiLap.hoSoNguoiDung',
        ]);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('maHoaDon', 'like', "%{$search}%")
                  ->orWhereHas('taiKhoan', fn($q2) => $q2
                    ->where('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q3) => $q3->where('hoTen', 'like', "%{$search}%"))
                  );
            });
        }
        if ($request->filled('trangThai') && $request->trangThai !== '') $query->where('trangThai', $request->trangThai);
        if ($request->filled('coSoId'))    $query->where('coSoId', $request->coSoId);
        if ($request->filled('tuNgay'))    $query->whereDate('ngayLap', '>=', $request->tuNgay);
        if ($request->filled('denNgay'))   $query->whereDate('ngayLap', '<=', $request->denNgay);

        if ($request->filled('hanThanhToan')) {
            $today = now()->toDateString();
            $canh  = now()->addDays(HoaDon::NGAY_CANH_BAO)->toDateString();
            match ($request->hanThanhToan) {
                'sap_het_han' => $query->whereNotNull('ngayHetHan')->where('ngayHetHan', '>=', $today)->where('ngayHetHan', '<=', $canh)->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT),
                'qua_han'     => $query->whereNotNull('ngayHetHan')->whereDate('ngayHetHan', '<', $today)->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT),
                default       => null,
            };
        }

        $orderBy = $request->get('orderBy', 'hoaDonId');
        $dir     = $request->get('dir', 'desc');
        if (in_array($orderBy, ['hoaDonId', 'ngayLap', 'tongTien', 'daTra', 'trangThai'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $todayStr = now()->toDateString();
        $canhStr  = now()->addDays(HoaDon::NGAY_CANH_BAO)->toDateString();

        return [
            'hoaDons'       => $query->paginate(15)->withQueryString(),
            'tongSo'        => HoaDon::count(),
            'chuaTT'        => HoaDon::where('trangThai', HoaDon::TRANG_THAI_CHUA_TT)->count(),
            'motPhan'       => HoaDon::where('trangThai', HoaDon::TRANG_THAI_MOT_PHAN)->count(),
            'daTT'          => HoaDon::where('trangThai', HoaDon::TRANG_THAI_DA_TT)->count(),
            'tongDoanhThu'  => HoaDon::sum('daTra'),
            'coSos'         => CoSoDaoTao::orderBy('tenCoSo')->get(),
            'sapHetHan'     => HoaDon::whereNotNull('ngayHetHan')->where('ngayHetHan', '>=', $todayStr)->where('ngayHetHan', '<=', $canhStr)->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)->count(),
            'quaHan'        => HoaDon::whereNotNull('ngayHetHan')->whereDate('ngayHetHan', '<', $todayStr)->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)->count(),
        ];
    }

    public function getDetail(int $id): array
    {
        return [
            'hoaDon' => HoaDon::with([
                'taiKhoan.hoSoNguoiDung',
                'dangKyLopHoc.lopHoc.khoaHoc',
                'dangKyLopHocPhuPhi',
                'coSo.tinhThanh',
                'nguoiLap.hoSoNguoiDung',
                'phieuThus.taiKhoan.hoSoNguoiDung',
                'phieuThus.nguoiDuyet.hoSoNguoiDung',
            ])->findOrFail($id),
        ];
    }

    public function update(Request $request, int $id): HoaDon
    {
        $hoaDon = HoaDon::findOrFail($id);
        $data   = $request->validate([
            'ghiChu'      => 'nullable|string|max:500',
            'ngayHetHan'  => 'nullable|date',
            'giamGia'     => 'nullable|numeric|min:0',
            'loaiHoaDon'  => 'nullable|in:0,1,2',
        ]);
        $hoaDon->update($data);
        return $hoaDon;
    }

    public function storePhieuThu(Request $request, int $hoaDonId): void
    {
        $hoaDon = HoaDon::findOrFail($hoaDonId);
        $data   = $request->validate([
            'soTien'               => 'required|numeric|min:1000',
            'ngayThu'              => 'required|date',
            'phuongThucThanhToan'  => 'required|in:1,2,3',
            'ghiChu'               => 'nullable|string|max:500',
        ], [
            'soTien.required'  => 'Vui lòng nhập số tiền.',
            'soTien.min'       => 'Số tiền phải tối thiểu 1.000đ.',
            'ngayThu.required' => 'Vui lòng chọn ngày thu.',
        ]);

        DB::transaction(function () use ($data, $hoaDon) {
            $user = Auth::user();
            PhieuThu::create([
                'maPhieuThu'           => PhieuThu::generateMaPhieuThu(),
                'hoaDonId'             => $hoaDon->hoaDonId,
                'soTien'               => $data['soTien'],
                'ngayThu'              => $data['ngayThu'],
                'phuongThucThanhToan'  => $data['phuongThucThanhToan'],
                'taiKhoanId'           => $user?->taiKhoanId,
                'nguoiDuyetId'         => $user?->taiKhoanId,
                'ghiChu'               => $data['ghiChu'] ?? null,
                'trangThai'            => PhieuThu::TRANG_THAI_HOP_LE,
            ]);
            $hoaDon->recalculate();
        });
    }

    public function destroyPhieuThu(int $id): int
    {
        $phieuThu = PhieuThu::findOrFail($id);
        $hoaDonId = $phieuThu->hoaDonId;

        DB::transaction(function () use ($phieuThu) {
            $phieuThu->update(['trangThai' => PhieuThu::TRANG_THAI_HUY]);
            $phieuThu->hoaDon->recalculate();
        });

        return $hoaDonId;
    }
}
