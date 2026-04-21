<?php

namespace App\Contracts\Admin\HocVien;

use App\Models\Education\DangKyLopHoc;
use Illuminate\Support\Collection;
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

    public function searchEligibleStudentsForClass(int $lopHocId, ?string $keyword = null, int $limit = 12): Collection;

    public function quickAddStudentsToClass(int $lopHocId, array $studentIds, int $paymentMethod, ?string $notePrefix = null): array;

    public function createStudentAndEnrollInClass(int $lopHocId, array $studentPayload, int $paymentMethod): array;

    public function promoteStudentsToNextClass(int $sourceLopHocId, int $targetLopHocId, array $registrationIds, int $paymentMethod): array;
}
