<?php

namespace App\Services\Admin\CoSo;

use App\Contracts\Admin\CoSo\CoSoServiceInterface;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CoSoService implements CoSoServiceInterface
{
    public function getList(Request $request): array
    {
        $query = CoSoDaoTao::with('tinhThanh')->withCount('phongHocs');

        if ($search = $request->q) {
            $query->where(fn($q) => $q
            ->where('tenCoSo', 'like', "%{$search}%")
            ->orWhere('maCoSo', 'like', "%{$search}%")
            ->orWhere('diaChi', 'like', "%{$search}%")
            );
        }
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        return [
            'coSos' => $query->orderBy('maCoSo')->paginate(15)->withQueryString(),
            'tongSo' => CoSoDaoTao::count(),
            'hoatDong' => CoSoDaoTao::where('trangThai', 1)->count(),
        ];
    }

    public function getCreateFormData(): array
    {
        return ['tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get()];
    }

    public function getDetail(int $id): array
    {
        return [
            'coSo' => CoSoDaoTao::with(['tinhThanh', 'phongHocs', 'nhanSus.taiKhoan.hoSoNguoiDung'])->findOrFail($id),
            'tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get(),
        ];
    }

    public function getEditFormData(int $id): array
    {
        return [
            'coSo' => CoSoDaoTao::findOrFail($id),
            'tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get(),
        ];
    }

    public function store(Request $request): CoSoDaoTao
    {
        $request->validate([
            'maCoSo' => 'required|string|max:20|unique:cosodaotao,maCoSo',
            'tenCoSo' => 'required|string|max:150',
            'diaChi' => 'required|string|max:255',
            'soDienThoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'tinhThanhId' => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa' => 'nullable|integer',
            'tenPhuongXa' => 'nullable|string|max:150',
            'viDo' => 'nullable|numeric|between:-90,90',
            'kinhDo' => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1',
        ], [
            'maCoSo.required' => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique' => 'Mã cơ sở đã tồn tại.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only(['maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email', 'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo', 'ngayKhaiTruong', 'banDoGoogle', 'trangThai']);
        $data['slug'] = Str::slug($request->tenCoSo);

        return CoSoDaoTao::create($data);
    }

    public function update(Request $request, int $id): CoSoDaoTao
    {
        $coSo = CoSoDaoTao::findOrFail($id);

        $request->validate([
            'maCoSo' => "required|string|max:20|unique:cosodaotao,maCoSo,{$id},coSoId",
            'tenCoSo' => 'required|string|max:150',
            'diaChi' => 'required|string|max:255',
            'soDienThoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'tinhThanhId' => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa' => 'nullable|integer',
            'tenPhuongXa' => 'nullable|string|max:150',
            'viDo' => 'nullable|numeric|between:-90,90',
            'kinhDo' => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1',
        ], [
            'maCoSo.required' => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique' => 'Mã cơ sở đã được dùng bởi cơ sở khác.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only(['maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email', 'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo', 'ngayKhaiTruong', 'banDoGoogle', 'trangThai']);
        $data['slug'] = Str::slug($request->tenCoSo);

        $coSo->update($data);
        return $coSo;
    }

    public function destroy(int $id): string
    {
        $coSo = CoSoDaoTao::withCount(['phongHocs', 'nhanSus'])->findOrFail($id);

        if ($coSo->phong_hocs_count > 0) {
            throw new \RuntimeException('Không thể xóa — cơ sở này còn ' . $coSo->phong_hocs_count . ' phòng học.');
        }
        if ($coSo->nhan_sus_count > 0) {
            throw new \RuntimeException('Không thể xóa — cơ sở này đang có ' . $coSo->nhan_sus_count . ' nhân sự làm việc.');
        }

        $ten = $coSo->tenCoSo;
        $coSo->delete();
        return $ten;
    }

    public function getPhuongXa(int $maTinh): array
    {
        try {
            /** @var HttpResponse $response */
            $response = Http::timeout(8)->retry(1, 200)
                ->get("https://provinces.open-api.vn/api/p/{$maTinh}?depth=3");

            if ($response->successful()) {
                $data = $response->json();
                $wards = collect($data['districts'] ?? [])
                    ->flatMap(fn($d) => $d['wards'] ?? [])
                    ->map(fn($w) => ['code' => (int)($w['code'] ?? 0), 'name' => $w['name'] ?? null])
                    ->filter(fn($w) => $w['code'] > 0 && !empty($w['name']))
                    ->unique('code')->sortBy('name')->values();

                return ['success' => true, 'wards' => $wards, 'source' => 'open-api'];
            }
        }
        catch (\Exception) {
        }

        $wards = CoSoDaoTao::query()
            ->whereHas('tinhThanh', fn($q) => $q->where('maAPI', $maTinh))
            ->whereNotNull('maPhuongXa')->whereNotNull('tenPhuongXa')
            ->selectRaw('maPhuongXa as code, tenPhuongXa as name')
            ->groupBy('maPhuongXa', 'tenPhuongXa')->orderBy('tenPhuongXa')->get();

        return ['success' => $wards->isNotEmpty(), 'wards' => $wards, 'source' => 'local-db'];
    }

    public function apiList(Request $request): Collection
    {
        $query = CoSoDaoTao::with('tinhThanh')->where('trangThai', 1);

        if ($request->filled('tinhThanhId'))
            $query->where('tinhThanhId', $request->tinhThanhId);
        if ($request->filled('maPhuongXa'))
            $query->where('maPhuongXa', $request->maPhuongXa);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('tenCoSo', 'like', "%{$s}%")->orWhere('diaChi', 'like', "%{$s}%")->orWhere('tenPhuongXa', 'like', "%{$s}%"));
        }

        return $query->get()->map(fn($c) => [
        'coSoId' => $c->coSoId,
        'tenCoSo' => $c->tenCoSo,
        'diaChi' => $c->diaChi,
        'tenPhuongXa' => $c->tenPhuongXa,
        'tinhThanh' => optional($c->tinhThanh)->tenTinhThanh,
        'tinhThanhId' => $c->tinhThanhId,
        'maPhuongXa' => $c->maPhuongXa,
        'soDienThoai' => $c->soDienThoai,
        'email' => $c->email,
        'viDo' => $c->viDo,
        'kinhDo' => $c->kinhDo,
        'banDoGoogle' => $c->banDoGoogle,
        'hasCoords' => $c->hasCoordinates(),
        'diaChiDayDu' => $c->diaChi_day_du,
        ]);
    }

    public function getPhuongXaCoCoSo(int $tinhThanhId): Collection
    {
        return CoSoDaoTao::where('tinhThanhId', $tinhThanhId)
            ->where('trangThai', 1)->whereNotNull('maPhuongXa')
            ->select('maPhuongXa', 'tenPhuongXa')
            ->groupBy('maPhuongXa', 'tenPhuongXa')->orderBy('tenPhuongXa')->get();
    }

    public function getCoSoByLocation(Request $request): Collection
    {
        $query = CoSoDaoTao::where('trangThai', 1);
        if ($request->filled('tinhThanhId'))
            $query->where('tinhThanhId', $request->tinhThanhId);
        if ($request->filled('maPhuongXa'))
            $query->where('maPhuongXa', $request->maPhuongXa);

        return $query->orderBy('tenCoSo')->get()->map(fn($c) => [
        'coSoId' => $c->coSoId,
        'tenCoSo' => $c->tenCoSo,
        'diaChi' => $c->diaChi,
        'tenPhuongXa' => $c->tenPhuongXa,
        ]);
    }
}