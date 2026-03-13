<?php

namespace App\Contracts\Admin\TaiChinh;

use App\Models\Finance\HoaDon;
use Illuminate\Http\Request;

interface HoaDonServiceInterface
{
    public function getList(Request $request): array;

    public function getDetail(int $id): array;

    public function update(Request $request, int $id): HoaDon;

    public function storePhieuThu(Request $request, int $hoaDonId): void;

    public function destroyPhieuThu(int $id): int;
}