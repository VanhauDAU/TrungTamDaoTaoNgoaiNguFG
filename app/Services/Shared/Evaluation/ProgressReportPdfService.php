<?php

namespace App\Services\Shared\Evaluation;

use App\Contracts\Shared\Evaluation\ProgressReportPdfServiceInterface;
use App\Models\Education\BaoCaoHocTap;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProgressReportPdfService implements ProgressReportPdfServiceInterface
{
    public function renderPdf(int $reportId): string
    {
        $artifact = $this->buildArtifact($reportId);

        return $artifact['content'];
    }

    public function downloadResponse(int $reportId, string $disposition = 'attachment'): Response
    {
        $artifact = $this->buildArtifact($reportId);

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => $disposition . '; filename="' . $artifact['filename'] . '"',
        ]);
    }

    private function buildArtifact(int $reportId): array
    {
        $report = BaoCaoHocTap::query()
            ->with([
                'dotDanhGia.lopHoc.khoaHoc',
                'dotDanhGia.lopHoc.coSo',
                'giaoVien.hoSoNguoiDung',
                'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
                'tieuChis',
            ])
            ->findOrFail($reportId);

        $code = data_get($report->metadataSnapshot, 'student_code') ?: ('report-' . $report->baoCaoHocTapId);
        $filename = 'bao-cao-hoc-tap-' . Str::slug($code . '-v' . $report->version, '-') . '.pdf';

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('reports.pdf.progress-report', [
                'report' => $report,
                'metadata' => $report->metadataSnapshot ?? [],
                'groupedCriteria' => $report->tieuChis->groupBy('nhom'),
            ]);
            $pdf->setPaper('a4');

            return [
                'content' => $pdf->output(),
                'mime' => 'application/pdf',
                'filename' => $filename,
            ];
        }

        return [
            'content' => view('reports.pdf.progress-report', [
                'report' => $report,
                'metadata' => $report->metadataSnapshot ?? [],
                'groupedCriteria' => $report->tieuChis->groupBy('nhom'),
            ])->render(),
            'mime' => 'text/html; charset=UTF-8',
            'filename' => Str::replaceLast('.pdf', '.html', $filename),
        ];
    }
}
