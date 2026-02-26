<?php

namespace App\Http\Controllers\Admin\Facility;

use App\Http\Controllers\Controller;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CoSoController extends Controller
{
    /** Danh sách cơ sở */
    public function index(Request $request)
    {
        $query = CoSoDaoTao::with('tinhThanh')->withCount('phongHocs');

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenCoSo', 'like', "%{$search}%")
                  ->orWhere('maCoSo', 'like', "%{$search}%")
                  ->orWhere('diaChi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        $coSos    = $query->orderBy('maCoSo')->paginate(15)->withQueryString();
        $tongSo   = CoSoDaoTao::count();
        $hoatDong = CoSoDaoTao::where('trangThai', 1)->count();

        return view('admin.co-so.index', compact('coSos', 'tongSo', 'hoatDong'));
    }

    /** Form thêm mới */
    public function create()
    {
        $tinhThanhs = TinhThanh::orderBy('tenTinhThanh')->get();
        return view('admin.co-so.create', compact('tinhThanhs'));
    }

    /** Lưu cơ sở mới */
    public function store(Request $request)
    {
        $request->validate([
            'maCoSo'         => 'required|string|max:20|unique:cosodaotao,maCoSo',
            'tenCoSo'        => 'required|string|max:150',
            'diaChi'         => 'required|string|max:255',
            'soDienThoai'    => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'tinhThanhId'    => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa'     => 'nullable|integer',
            'tenPhuongXa'    => 'nullable|string|max:150',
            'viDo'           => 'nullable|numeric|between:-90,90',
            'kinhDo'         => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle'    => 'nullable|string|max:500',
            'trangThai'      => 'required|in:0,1',
        ], [
            'maCoSo.required'  => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique'    => 'Mã cơ sở đã tồn tại.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required'  => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only([
            'maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email',
            'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo',
            'ngayKhaiTruong', 'banDoGoogle', 'trangThai',
        ]);
        $data['slug'] = \Illuminate\Support\Str::slug($request->tenCoSo);

        CoSoDaoTao::create($data);

        return redirect()->route('admin.co-so.index')
            ->with('success', 'Đã thêm cơ sở «' . $request->tenCoSo . '» thành công.');
    }

    /** Trang chi tiết cơ sở + danh sách phòng */
    public function show(int $id)
    {
        $coSo       = CoSoDaoTao::with(['tinhThanh', 'phongHocs', 'nhanSus.taiKhoan.hoSoNguoiDung'])->findOrFail($id);
        $tinhThanhs = TinhThanh::orderBy('tenTinhThanh')->get();
        return view('admin.co-so.show', compact('coSo', 'tinhThanhs'));
    }

    /** Form chỉnh sửa */
    public function edit(int $id)
    {
        $coSo       = CoSoDaoTao::findOrFail($id);
        $tinhThanhs = TinhThanh::orderBy('tenTinhThanh')->get();
        return view('admin.co-so.edit', compact('coSo', 'tinhThanhs'));
    }

    /** Cập nhật */
    public function update(Request $request, int $id)
    {
        $coSo = CoSoDaoTao::findOrFail($id);

        $request->validate([
            'maCoSo'         => "required|string|max:20|unique:cosodaotao,maCoSo,{$id},coSoId",
            'tenCoSo'        => 'required|string|max:150',
            'diaChi'         => 'required|string|max:255',
            'soDienThoai'    => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'tinhThanhId'    => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa'     => 'nullable|integer',
            'tenPhuongXa'    => 'nullable|string|max:150',
            'viDo'           => 'nullable|numeric|between:-90,90',
            'kinhDo'         => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle'    => 'nullable|string|max:500',
            'trangThai'      => 'required|in:0,1',
        ], [
            'maCoSo.required'  => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique'    => 'Mã cơ sở đã được dùng bởi cơ sở khác.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required'  => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only([
            'maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email',
            'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo',
            'ngayKhaiTruong', 'banDoGoogle', 'trangThai',
        ]);
        $data['slug'] = \Illuminate\Support\Str::slug($request->tenCoSo);

        $coSo->update($data);

        return redirect()->route('admin.co-so.index')
            ->with('success', 'Đã cập nhật cơ sở «' . $coSo->tenCoSo . '» thành công.');
    }

    /** Xóa */
    public function destroy(int $id)
    {
        $coSo = CoSoDaoTao::withCount(['phongHocs', 'nhanSus'])->findOrFail($id);

        if ($coSo->phong_hocs_count > 0) {
            return redirect()->route('admin.co-so.index')
                ->with('error', 'Không thể xóa — cơ sở này còn ' . $coSo->phong_hocs_count . ' phòng học.');
        }

        if ($coSo->nhan_sus_count > 0) {
            return redirect()->route('admin.co-so.index')
                ->with('error', 'Không thể xóa — cơ sở này đang có ' . $coSo->nhan_sus_count . ' nhân sự làm việc.');
        }

        $ten = $coSo->tenCoSo;
        $coSo->delete();

        return redirect()->route('admin.co-so.index')
            ->with('success', "Đã xóa cơ sở «{$ten}».");
    }

    // ─── API ────────────────────────────────────────────────────────────────

    /**
     * Proxy lấy danh sách phường/xã của 1 tỉnh từ open-api.vn
     * GET /api/phuong-xa/{maTinh}
     */
    public function getPhuongXa(int $maTinh)
    {
        try {
            $response = Http::timeout(8)->get("https://provinces.open-api.vn/api/v2/p/{$maTinh}?depth=2");
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'wards'   => $data['wards'] ?? [],
                ]);
            }
        } catch (\Exception $e) {
            // Trả về rỗng nếu API lỗi
        }

        return response()->json(['success' => false, 'wards' => []]);
    }

    /**
     * Danh sách cơ sở (dùng cho contact page filter)
     * GET /api/co-so?tinhThanhId=&search=
     */
    public function apiList(Request $request)
    {
        $query = CoSoDaoTao::with('tinhThanh')
            ->where('trangThai', 1);

        if ($request->filled('tinhThanhId')) {
            $query->where('tinhThanhId', $request->tinhThanhId);
        }
        if ($request->filled('maPhuongXa')) {
            $query->where('maPhuongXa', $request->maPhuongXa);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('tenCoSo', 'like', "%{$s}%")
                  ->orWhere('diaChi', 'like', "%{$s}%")
                  ->orWhere('tenPhuongXa', 'like', "%{$s}%");
            });
        }

        $coSos = $query->get()->map(function ($c) {
            return [
                'coSoId'       => $c->coSoId,
                'tenCoSo'      => $c->tenCoSo,
                'diaChi'       => $c->diaChi,
                'tenPhuongXa'  => $c->tenPhuongXa,
                'tinhThanh'    => optional($c->tinhThanh)->tenTinhThanh,
                'tinhThanhId'  => $c->tinhThanhId,
                'maPhuongXa'   => $c->maPhuongXa,
                'soDienThoai'  => $c->soDienThoai,
                'email'        => $c->email,
                'viDo'         => $c->viDo,
                'kinhDo'       => $c->kinhDo,
                'banDoGoogle'  => $c->banDoGoogle,
                'hasCoords'    => $c->hasCoordinates(),
                'diaChiDayDu'  => $c->diaChi_day_du,
            ];
        });

        return response()->json(['success' => true, 'coSos' => $coSos]);
    }

    /**
     * Lấy danh sách phường/xã có cơ sở theo tỉnh (dùng cho cascade selector)
     * GET /admin/api/phuong-xa-co-so/{tinhThanhId}
     */
    public function getPhuongXaCoCoSo(int $tinhThanhId)
    {
        $phuongXas = CoSoDaoTao::where('tinhThanhId', $tinhThanhId)
            ->where('trangThai', 1)
            ->whereNotNull('maPhuongXa')
            ->select('maPhuongXa', 'tenPhuongXa')
            ->groupBy('maPhuongXa', 'tenPhuongXa')
            ->orderBy('tenPhuongXa')
            ->get();

        return response()->json(['success' => true, 'phuongXas' => $phuongXas]);
    }

    /**
     * Lấy cơ sở theo tỉnh + phường (dùng cho cascade selector)
     * GET /admin/api/co-so-by-location?tinhThanhId=&maPhuongXa=
     */
    public function getCoSoByLocation(Request $request)
    {
        $query = CoSoDaoTao::where('trangThai', 1);

        if ($request->filled('tinhThanhId')) {
            $query->where('tinhThanhId', $request->tinhThanhId);
        }
        if ($request->filled('maPhuongXa')) {
            $query->where('maPhuongXa', $request->maPhuongXa);
        }

        $coSos = $query->orderBy('tenCoSo')->get()->map(fn($c) => [
            'coSoId'      => $c->coSoId,
            'tenCoSo'     => $c->tenCoSo,
            'diaChi'      => $c->diaChi,
            'tenPhuongXa' => $c->tenPhuongXa,
        ]);

        return response()->json(['success' => true, 'coSos' => $coSos]);
    }
}
