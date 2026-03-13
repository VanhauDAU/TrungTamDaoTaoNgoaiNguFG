<?php

namespace App\Contracts\Admin;

use App\Models\Interaction\LienHe;
use Illuminate\Http\Request;

interface LienHeServiceInterface
{
    public function getList(Request $request): array;

    public function getDetail(string $id): array;

    public function update(Request $request, string $id): LienHe;

    public function assign(Request $request, string $id): array;

    public function storeReply(Request $request, string $id): array;

    public function destroy(string $id): void;

    public function getTrash(Request $request): array;

    public function bulkDestroy(Request $request): int;

    public function bulkUpdateStatus(Request $request): int;

    public function restore(string $id): void;
}
