<?php

namespace App\Contracts\Admin;

use App\Models\Facility\CoSoDaoTao;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface CoSoServiceInterface
{
    public function getList(Request $request): array;

    public function getCreateFormData(): array;

    public function getDetail(int $id): array;

    public function getEditFormData(int $id): array;

    public function store(Request $request): CoSoDaoTao;

    public function update(Request $request, int $id): CoSoDaoTao;

    public function destroy(int $id): string;

    /** API: Lấy phường/xã theo mã tỉnh (proxy open-api.vn + fallback DB) */
    public function getPhuongXa(int $maTinh): array;

    /** API: Danh sách cơ sở cho contact page */
    public function apiList(Request $request): Collection;

    /** API: Phường/xã có cơ sở theo tỉnh */
    public function getPhuongXaCoCoSo(int $tinhThanhId): Collection;

    /** API: Cơ sở theo tỉnh + phường */
    public function getCoSoByLocation(Request $request): Collection;
}
