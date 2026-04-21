<?php

namespace App\Services\Staff\KhoaHoc;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\DangKyLopHoc;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ClassEnrollmentDocumentService
{
    public function streamEnrollmentContract(
        DangKyLopHoc $registration,
        TaiKhoan $student,
        string $temporaryPassword,
        string $disposition = 'inline'
    ): Response {
        $registration->loadMissing([
            'lopHoc.khoaHoc',
            'lopHoc.coSo',
            'hoaDons.lopHocDotThu',
            'taiKhoan.hoSoNguoiDung',
        ]);

        $artifact = $this->renderPdfArtifact(
            'staff.lop-hoc.pdf.enrollment-contract',
            [
                'registration' => $registration,
                'student' => $student->loadMissing('hoSoNguoiDung'),
                'temporaryPassword' => $temporaryPassword,
            ],
            'phieu-hop-dong-ghi-danh-' . Str::slug($student->taiKhoan, '-') . '.pdf'
        );

        return response($artifact['content'], 200, [
            'Content-Type' => $artifact['mime'],
            'Content-Disposition' => $disposition . '; filename="' . $artifact['filename'] . '"',
        ]);
    }

    private function renderPdfArtifact(string $view, array $data, string $filename): array
    {
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView($view, $data);
            $pdf->setPaper('a4');

            return [
                'content' => $pdf->output(),
                'mime' => 'application/pdf',
                'filename' => $filename,
            ];
        }

        $html = view($view, $data)->render();

        return [
            'content' => $html,
            'mime' => 'text/html; charset=UTF-8',
            'filename' => Str::replaceLast('.pdf', '.html', $filename),
        ];
    }
}
