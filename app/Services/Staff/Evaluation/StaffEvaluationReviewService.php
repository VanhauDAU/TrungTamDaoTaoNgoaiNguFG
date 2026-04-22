<?php

namespace App\Services\Staff\Evaluation;

use App\Contracts\Staff\Evaluation\StaffEvaluationReviewServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTapDotDanhGia;
use App\Services\Education\ProgressReportManager;
use Illuminate\Http\Request;

class StaffEvaluationReviewService implements StaffEvaluationReviewServiceInterface
{
    public function __construct(
        private readonly ProgressReportManager $manager
    ) {
    }

    public function getReviewQueue(Request $request): array
    {
        return $this->manager->getStaffQueue($request);
    }

    public function getReviewDetail(int $reportId): array
    {
        return $this->manager->getStaffReviewDetail($reportId);
    }

    public function requestRevision(int $reportId, string $note, TaiKhoan $staff): void
    {
        $this->manager->requestRevision($reportId, $note, $staff);
    }

    public function approve(int $reportId, TaiKhoan $staff): void
    {
        $this->manager->approve($reportId, $staff);
    }

    public function publish(int $reportId, TaiKhoan $staff): void
    {
        $this->manager->publish($reportId, $staff);
    }

    public function getPeriodList(Request $request): array
    {
        return $this->manager->getStaffPeriodList($request);
    }

    public function createPeriod(array $payload, TaiKhoan $staff): BaoCaoHocTapDotDanhGia
    {
        return $this->manager->createPeriod($payload, $staff);
    }
}
