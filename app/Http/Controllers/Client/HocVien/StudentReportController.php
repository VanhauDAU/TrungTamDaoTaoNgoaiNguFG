<?php

namespace App\Http\Controllers\Client\HocVien;

use App\Contracts\Shared\Evaluation\ProgressReportPdfServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Education\ProgressReportManager;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    public function __construct(
        private readonly ProgressReportManager $progressReportManager,
        private readonly ProgressReportPdfServiceInterface $progressReportPdfService
    ) {
    }

    public function index(Request $request)
    {
        /** @var TaiKhoan $student */
        $student = $request->user();

        return view('clients.hoc-vien.reports.index', [
            'reports' => $this->progressReportManager->getStudentReports($student),
        ]);
    }

    public function show(Request $request, int $reportId)
    {
        /** @var TaiKhoan $student */
        $student = $request->user();
        $report = $this->progressReportManager->findStudentReport($student, $reportId);

        return view('clients.hoc-vien.reports.show', [
            'report' => $report,
            'metadata' => $report->metadataSnapshot ?? [],
            'groupedCriteria' => $report->tieuChis->groupBy('nhom'),
        ]);
    }

    public function download(Request $request, int $reportId)
    {
        /** @var TaiKhoan $student */
        $student = $request->user();
        $this->progressReportManager->findStudentReport($student, $reportId);

        return $this->progressReportPdfService->downloadResponse($reportId, 'attachment');
    }
}
