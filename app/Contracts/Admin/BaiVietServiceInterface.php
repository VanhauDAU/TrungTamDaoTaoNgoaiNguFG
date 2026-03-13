<?php

namespace App\Contracts\Admin;

use App\Models\Content\BaiViet;
use Illuminate\Http\Request;

interface BaiVietServiceInterface
{
    public function getList(Request $request): array;

    public function getCreateFormData(): array;

    public function getDetail(int $id): array;

    public function getEditFormData(int $id): array;

    public function store(Request $request): BaiViet;

    public function update(Request $request, int $id): BaiViet;

    public function destroy(int $id): string;

    /** Xóa mềm nhiều bài viết (AJAX) */
    public function bulkDestroy(Request $request): int;

    public function getTrash(Request $request): array;

    public function restore(int $id): BaiViet;

    public function bulkRestore(Request $request): int;

    public function forceDestroy(int $id): string;

    public function toggleStatus(int $id): array;

    /** Upload ảnh cho TinyMCE */
    public function uploadImage(Request $request): string;
}
