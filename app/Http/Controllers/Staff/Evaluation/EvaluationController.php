<?php

namespace App\Http\Controllers\Staff\Evaluation;

use App\Contracts\Shared\Evaluation\ProgressReportPdfServiceInterface;
use App\Contracts\Staff\Evaluation\StaffEvaluationReviewServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function __construct(
        private readonly StaffEvaluationReviewServiceInterface $staffEvaluationReviewService,
        private readonly ProgressReportPdfServiceInterface $progressReportPdfService,
    ) {
    }

    public function index(Request $request)
    {
        return view('staff.evaluations.index', $this->staffEvaluationReviewService->getReviewQueue($request));
    }

    public function periods(Request $request)
    {
        return view('staff.evaluations.periods.index', $this->staffEvaluationReviewService->getPeriodList($request));
    }

    public function storePeriod(Request $request)
    {
        /** @var TaiKhoan $staff */
        $staff = $request->user();

        $payload = $request->validate([
            'lopHocId' => 'required|integer',
            'baoCaoHocTapMauId' => 'nullable|integer',
            'tenDot' => 'required|string|max:150',
            'tuNgay' => 'nullable|date',
            'denNgay' => 'nullable|date|after_or_equal:tuNgay',
            'hanNop' => 'nullable|date',
            'hanDuyet' => 'nullable|date|after_or_equal:hanNop',
        ]);

        $period = $this->staffEvaluationReviewService->createPeriod($payload, $staff);

        return redirect()
            ->route('staff.evaluations.periods.index')
            ->with('success', 'Đã tạo đợt đánh giá "' . $period->tenDot . '" và sinh nháp báo cáo theo lớp.');
    }

    public function show(int $reportId)
    {
        return view('staff.evaluations.reports.show', $this->staffEvaluationReviewService->getReviewDetail($reportId));
    }

    public function preview(int $reportId)
    {
        $this->staffEvaluationReviewService->getReviewDetail($reportId);

        return $this->progressReportPdfService->downloadResponse($reportId, 'inline');
    }

    public function requestRevision(Request $request, int $reportId)
    {
        /** @var TaiKhoan $staff */
        $staff = $request->user();
        $validated = $request->validate([
            'note' => 'required|string|max:5000',
        ]);

        $this->staffEvaluationReviewService->requestRevision($reportId, $validated['note'], $staff);

        return back()->with('success', 'Đã trả báo cáo về cho giáo viên chỉnh sửa.');
    }

    public function approve(Request $request, int $reportId)
    {
        /** @var TaiKhoan $staff */
        $staff = $request->user();

        $this->staffEvaluationReviewService->approve($reportId, $staff);

        return back()->with('success', 'Đã duyệt báo cáo học tập.');
    }

    public function publish(Request $request, int $reportId)
    {
        /** @var TaiKhoan $staff */
        $staff = $request->user();

        $this->staffEvaluationReviewService->publish($reportId, $staff);

        return back()->with('success', 'Đã phát hành báo cáo tới cổng học viên.');
    }
}
