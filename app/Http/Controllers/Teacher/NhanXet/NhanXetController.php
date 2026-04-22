<?php

namespace App\Http\Controllers\Teacher\NhanXet;

use App\Contracts\Shared\Evaluation\ProgressReportPdfServiceInterface;
use App\Contracts\Teacher\Evaluation\TeacherEvaluationServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Education\ProgressReportManager;
use App\Services\Teacher\Evaluation\TeacherEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NhanXetController extends Controller
{
    public function __construct(
        private readonly TeacherEvaluationServiceInterface $teacherEvaluationService,
        private readonly TeacherEvaluationService $teacherEvaluationActions,
        private readonly ProgressReportManager $progressReportManager,
        private readonly ProgressReportPdfServiceInterface $progressReportPdfService,
    ) {
    }

    public function index(Request $request)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.evaluations.index', $this->teacherEvaluationService->getDashboardData($teacher, $request));
    }

    public function periods(Request $request)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.evaluations.periods.index', $this->teacherEvaluationService->getPeriodList($teacher, $request));
    }

    public function showPeriod(Request $request, int $periodId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.evaluations.periods.show', $this->teacherEvaluationService->getPeriodDetail($teacher, $periodId));
    }

    public function bulkCreateDrafts(Request $request, int $periodId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $createdCount = $this->teacherEvaluationActions->createDraftsForPeriod($teacher, $periodId);

        return back()->with('success', 'Đã đảm bảo nháp cho đợt đánh giá. Tạo mới thêm ' . $createdCount . ' báo cáo.');
    }

    public function create(Request $request, int $periodId, int $dangKyLopHocId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $report = $this->progressReportManager->ensureTeacherReportForRegistration($teacher, $periodId, $dangKyLopHocId);

        return redirect()->route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId);
    }

    public function edit(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.evaluations.reports.editor', $this->teacherEvaluationService->getReportEditor($teacher, $reportId));
    }

    public function save(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $request->validate([
            'criteria' => 'array',
            'criteria.*.rating' => 'nullable|string|max:100',
            'criteria.*.number' => 'nullable',
            'criteria.*.comment' => 'nullable|string|max:5000',
        ]);

        $report = $this->teacherEvaluationService->saveDraft($teacher, $reportId, $request->all());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã lưu nháp.',
                'savedAt' => now()->format('H:i:s'),
                'reportId' => $report->baoCaoHocTapId,
            ]);
        }

        return back()->with('success', 'Đã lưu nháp báo cáo học tập.');
    }

    public function copyPrevious(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $this->teacherEvaluationActions->copyFromPrevious($teacher, $reportId);

        return back()->with('success', 'Đã sao chép dữ liệu từ báo cáo gần nhất.');
    }

    public function submit(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        try {
            $this->teacherEvaluationService->submit($teacher, $reportId);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Đã gửi báo cáo cho staff duyệt.');
    }

    public function preview(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $this->progressReportManager->findTeacherReport($teacher, $reportId);

        return $this->progressReportPdfService->downloadResponse($reportId, 'inline');
    }

    public function history(Request $request, int $reportId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.evaluations.reports.history', $this->teacherEvaluationActions->getHistory($teacher, $reportId));
    }
}
