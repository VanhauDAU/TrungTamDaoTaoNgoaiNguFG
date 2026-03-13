<?php

namespace App\Contracts\Admin;

use App\Models\Course\DanhMucKhoaHoc;
use Illuminate\Http\Request;

interface DanhMucKhoaHocServiceInterface
{
    public function getList(Request $request): array;

    public function getCreateFormData(): array;

    public function getEditFormData(string $slug): array;

    public function store(Request $request): DanhMucKhoaHoc;

    public function update(Request $request, string $slug): DanhMucKhoaHoc;

    public function destroy(string $slug): string;

    public function reorder(Request $request): array;
}
