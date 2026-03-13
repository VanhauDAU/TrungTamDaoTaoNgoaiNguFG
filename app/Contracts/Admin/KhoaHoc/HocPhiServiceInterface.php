<?php

namespace App\Contracts\Admin\KhoaHoc;

use App\Models\Course\HocPhi;
use Illuminate\Http\Request;

interface HocPhiServiceInterface
{
    public function store(Request $request): HocPhi;

    public function update(Request $request, int $id): HocPhi;

    public function destroy(int $id): array;

    public function toggleStatus(int $id): array;
}
