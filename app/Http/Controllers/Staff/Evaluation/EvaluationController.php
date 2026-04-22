<?php

namespace App\Http\Controllers\Staff\Evaluation;

use App\Contracts\Shared\Evaluation\ProgressReportPdfServiceInterface;
use App\Contracts\Staff\Evaluation\StaffEvaluationReviewServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Education\ProgressReportManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EvaluationController extends Controller
{
    public function __construct(
        private readonly StaffEvaluationReviewServiceInterface $staffEvaluationReviewService,
        private readonly ProgressReportPdfServiceInterface $progressReportPdfService,
        private readonly ProgressReportManager $progressReportManager,
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

    public function templates()
    {
        return view('staff.evaluations.templates.index', $this->progressReportManager->getTemplateList());
    }

    public function createTemplate()
    {
        return view('staff.evaluations.templates.create', $this->progressReportManager->getTemplateEditor());
    }

    public function storeTemplate(Request $request)
    {
        $payload = $request->all();
        $payload['kichHoat'] = $request->boolean('kichHoat');
        $payload['macDinh'] = $request->boolean('macDinh');

        try {
            $template = $this->progressReportManager->createTemplate($payload);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('staff.evaluations.templates.edit', $template->baoCaoHocTapMauId)
            ->with('success', 'Đã tạo mẫu báo cáo học tập mới.');
    }

    public function editTemplate(int $templateId)
    {
        return view('staff.evaluations.templates.edit', $this->progressReportManager->getTemplateEditor($templateId));
    }

    public function updateTemplate(Request $request, int $templateId)
    {
        $payload = $request->all();
        $payload['kichHoat'] = $request->boolean('kichHoat');
        $payload['macDinh'] = $request->boolean('macDinh');

        try {
            $this->progressReportManager->updateTemplate($templateId, $payload);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('staff.evaluations.templates.edit', $templateId)
            ->with('success', 'Đã cập nhật mẫu báo cáo học tập.');
    }

    public function duplicateTemplate(int $templateId)
    {
        $template = $this->progressReportManager->duplicateTemplate($templateId);

        return redirect()
            ->route('staff.evaluations.templates.edit', $template->baoCaoHocTapMauId)
            ->with('success', 'Đã nhân bản mẫu báo cáo.');
    }

    public function setDefaultTemplate(int $templateId)
    {
        $this->progressReportManager->setDefaultTemplate($templateId);

        return back()->with('success', 'Đã đặt mẫu này làm mặc định.');
    }

    public function toggleTemplateActivation(int $templateId)
    {
        try {
            $template = $this->progressReportManager->toggleTemplateActivation($templateId);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', $template->kichHoat ? 'Đã kích hoạt mẫu.' : 'Đã tắt kích hoạt mẫu.');
    }

    public function destroyTemplate(int $templateId)
    {
        try {
            $this->progressReportManager->deleteTemplate($templateId);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('staff.evaluations.templates.index')
            ->with('success', 'Đã xóa mẫu báo cáo.');
    }
}
