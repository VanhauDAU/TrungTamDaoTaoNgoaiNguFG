<?php

namespace App\Services\Teacher\Evaluation;

use App\Contracts\Teacher\Evaluation\TeacherEvaluationServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTap;
use App\Services\Education\ProgressReportManager;
use Illuminate\Http\Request;

class TeacherEvaluationService implements TeacherEvaluationServiceInterface
{
    public function __construct(
        private readonly ProgressReportManager $manager
    ) {
    }

    public function getDashboardData(TaiKhoan $teacher, Request $request): array
    {
        return $this->manager->getTeacherDashboard($teacher, $request);
    }

    public function getPeriodList(TaiKhoan $teacher, Request $request): array
    {
        return $this->manager->getTeacherPeriods($teacher, $request);
    }

    public function getPeriodDetail(TaiKhoan $teacher, int $periodId): array
    {
        return $this->manager->getTeacherPeriodDetail($teacher, $periodId);
    }

    public function getReportEditor(TaiKhoan $teacher, int $reportId): array
    {
        return $this->manager->getTeacherReportEditor($teacher, $reportId);
    }

    public function saveDraft(TaiKhoan $teacher, int $reportId, array $payload): BaoCaoHocTap
    {
        return $this->manager->saveTeacherDraft($teacher, $reportId, $payload);
    }

    public function submit(TaiKhoan $teacher, int $reportId): void
    {
        $this->manager->submitTeacherReport($teacher, $reportId);
    }

    public function createDraftsForPeriod(TaiKhoan $teacher, int $periodId): int
    {
        return $this->manager->createTeacherDraftsForPeriod($teacher, $periodId);
    }

    public function copyFromPrevious(TaiKhoan $teacher, int $reportId): void
    {
        $this->manager->copyFromPreviousReport($teacher, $reportId);
    }

    public function getHistory(TaiKhoan $teacher, int $reportId): array
    {
        return $this->manager->getReportHistoryForTeacher($teacher, $reportId);
    }
}
