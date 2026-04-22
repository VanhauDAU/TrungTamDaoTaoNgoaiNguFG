<?php

namespace App\Contracts\Staff\Evaluation;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTapDotDanhGia;
use Illuminate\Http\Request;

interface StaffEvaluationReviewServiceInterface
{
    public function getReviewQueue(Request $request): array;

    public function getReviewDetail(int $reportId): array;

    public function requestRevision(int $reportId, string $note, TaiKhoan $staff): void;

    public function approve(int $reportId, TaiKhoan $staff): void;

    public function publish(int $reportId, TaiKhoan $staff): void;

    public function getPeriodList(Request $request): array;

    public function createPeriod(array $payload, TaiKhoan $staff): BaoCaoHocTapDotDanhGia;
}
