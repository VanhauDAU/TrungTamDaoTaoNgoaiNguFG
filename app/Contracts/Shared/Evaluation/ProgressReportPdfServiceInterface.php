<?php

namespace App\Contracts\Shared\Evaluation;

use Symfony\Component\HttpFoundation\Response;

interface ProgressReportPdfServiceInterface
{
    public function renderPdf(int $reportId): string;

    public function downloadResponse(int $reportId, string $disposition = 'attachment'): Response;
}
