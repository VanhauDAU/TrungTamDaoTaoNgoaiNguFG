<?php

namespace App\Contracts\Teacher\Evaluation;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTap;
use Illuminate\Http\Request;

interface TeacherEvaluationServiceInterface
{
    public function getDashboardData(TaiKhoan $teacher, Request $request): array;

    public function getPeriodList(TaiKhoan $teacher, Request $request): array;

    public function getPeriodDetail(TaiKhoan $teacher, int $periodId): array;

    public function getReportEditor(TaiKhoan $teacher, int $reportId): array;

    public function saveDraft(TaiKhoan $teacher, int $reportId, array $payload): BaoCaoHocTap;

    public function submit(TaiKhoan $teacher, int $reportId): void;
}
