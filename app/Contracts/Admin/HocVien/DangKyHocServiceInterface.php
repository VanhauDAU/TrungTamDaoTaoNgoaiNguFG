<?php

namespace App\Contracts\Admin\HocVien;

use App\Models\Education\DangKyLopHoc;
use Illuminate\Http\Request;

interface DangKyHocServiceInterface
{
    public function getList(Request $request): array;

    public function getCreateFormData(): array;

    public function store(Request $request): DangKyLopHoc;

    public function confirm(int $id): DangKyLopHoc;

    public function cancel(int $id): DangKyLopHoc;

    public function hold(int $id): DangKyLopHoc;

    public function restore(int $id): DangKyLopHoc;

    public function transfer(Request $request, int $id): DangKyLopHoc;
}
