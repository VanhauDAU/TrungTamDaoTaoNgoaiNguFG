<?php

namespace App\Contracts\Admin;

use Illuminate\Http\Request;

interface CaHocServiceInterface
{
    public function getList(Request $request): array;

    public function store(Request $request): array;

    public function update(Request $request, int $id): array;

    public function destroy(int $id): array;

    public function toggleStatus(int $id): array;
}
