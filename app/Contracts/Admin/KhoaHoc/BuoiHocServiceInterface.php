<?php

namespace App\Contracts\Admin\KhoaHoc;

use Illuminate\Http\Request;

interface BuoiHocServiceInterface
{
    public function store(Request $request): array;

    public function update(Request $request, int $id): array;

    public function destroy(int $id): array;

    public function autoGenerate(Request $request, int $lopHocId): array;
}
