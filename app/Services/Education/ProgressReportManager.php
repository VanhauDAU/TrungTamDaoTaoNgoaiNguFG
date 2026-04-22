<?php

namespace App\Services\Education;

use App\Contracts\Admin\ThongBao\ThongBaoServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTap;
use App\Models\Education\BaoCaoHocTapDotDanhGia;
use App\Models\Education\BaoCaoHocTapLichSu;
use App\Models\Education\BaoCaoHocTapMau;
use App\Models\Education\BaoCaoHocTapMauTieuChi;
use App\Models\Education\BaoCaoHocTapTieuChi;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DiemDanh;
use App\Models\Education\LopHoc;
use App\Models\Interaction\ThongBao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgressReportManager
{
    public function __construct(
        private readonly ThongBaoServiceInterface $thongBaoService
    ) {
    }

    public function getDefaultTemplate(): BaoCaoHocTapMau
    {
        return BaoCaoHocTapMau::query()
            ->with('tieuChis')
            ->where('kichHoat', true)
            ->orderByDesc('macDinh')
            ->orderByDesc('baoCaoHocTapMauId')
            ->firstOrFail();
    }

    public function teacherClassesQuery(TaiKhoan $teacher): Builder
    {
        return LopHoc::query()
            ->with(['khoaHoc', 'coSo', 'taiKhoan.hoSoNguoiDung'])
            ->where('taiKhoanId', $teacher->taiKhoanId);
    }

    public function getTeacherDashboard(TaiKhoan $teacher, Request $request): array
    {
        $reportsQuery = $this->teacherReportsQuery($teacher)
            ->with(['dotDanhGia.lopHoc.khoaHoc', 'dangKyLopHoc.taiKhoan.hoSoNguoiDung']);

        $classId = $request->integer('lopHocId') ?: null;
        $status = trim((string) $request->query('trangThai', ''));

        if ($classId) {
            $reportsQuery->whereHas('dotDanhGia', fn (Builder $query) => $query->where('lopHocId', $classId));
        }

        if ($status !== '') {
            $reportsQuery->where('trangThai', $status);
        }

        $reports = $reportsQuery
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $periods = BaoCaoHocTapDotDanhGia::query()
            ->with(['lopHoc.khoaHoc', 'baoCaos'])
            ->whereHas('lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $allReports = $this->teacherReportsQuery($teacher)->get();

        return [
            'classes' => $this->teacherClassesQuery($teacher)->orderByDesc('ngayBatDau')->get(),
            'selectedClassId' => $classId,
            'selectedStatus' => $status,
            'statusOptions' => BaoCaoHocTap::trangThaiLabels(),
            'periods' => $periods,
            'reports' => $reports,
            'summary' => [
                'draft' => $allReports->where('trangThai', BaoCaoHocTap::TRANG_THAI_DRAFT)->count(),
                'submitted' => $allReports->where('trangThai', BaoCaoHocTap::TRANG_THAI_SUBMITTED)->count(),
                'needs_revision' => $allReports->where('trangThai', BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION)->count(),
                'published' => $allReports->where('trangThai', BaoCaoHocTap::TRANG_THAI_PUBLISHED)->count(),
                'overdue' => $allReports->filter(function (BaoCaoHocTap $report) {
                    $deadline = $report->dotDanhGia?->hanNop;

                    return $deadline !== null
                        && $deadline->isPast()
                        && in_array($report->trangThai, [
                            BaoCaoHocTap::TRANG_THAI_DRAFT,
                            BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION,
                        ], true);
                })->count(),
            ],
        ];
    }

    public function getTeacherPeriods(TaiKhoan $teacher, Request $request): array
    {
        $classId = $request->integer('lopHocId') ?: null;
        $status = trim((string) $request->query('trangThai', ''));

        $periods = BaoCaoHocTapDotDanhGia::query()
            ->with(['lopHoc.khoaHoc', 'baoCaos.dangKyLopHoc'])
            ->whereHas('lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->when($classId, fn (Builder $query) => $query->where('lopHocId', $classId))
            ->when($status !== '', fn (Builder $query) => $query->where('trangThai', $status))
            ->orderByDesc('created_at')
            ->get();

        return [
            'classes' => $this->teacherClassesQuery($teacher)->orderBy('tenLopHoc')->get(),
            'periods' => $periods,
            'selectedClassId' => $classId,
            'selectedStatus' => $status,
            'statusOptions' => BaoCaoHocTapDotDanhGia::trangThaiLabels(),
        ];
    }

    public function getTeacherPeriodDetail(TaiKhoan $teacher, int $periodId): array
    {
        $period = BaoCaoHocTapDotDanhGia::query()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'baoCaos.dangKyLopHoc.taiKhoan.hoSoNguoiDung',
            ])
            ->whereHas('lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->findOrFail($periodId);

        if (! $period->baoCaos()->exists()) {
            $this->generateDraftsForPeriod($period);
            $period->load([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'baoCaos.dangKyLopHoc.taiKhoan.hoSoNguoiDung',
            ]);
        }

        $reports = $period->baoCaos
            ->sortBy(fn (BaoCaoHocTap $report) => $report->dangKyLopHoc?->taiKhoan?->hoSoNguoiDung?->hoTen ?? '');

        return [
            'period' => $period,
            'reports' => $reports,
            'summary' => [
                'total' => $reports->count(),
                'completed' => $reports->whereIn('trangThai', [
                    BaoCaoHocTap::TRANG_THAI_SUBMITTED,
                    BaoCaoHocTap::TRANG_THAI_APPROVED,
                    BaoCaoHocTap::TRANG_THAI_PUBLISHED,
                ])->count(),
                'draft' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_DRAFT)->count(),
                'revision' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION)->count(),
            ],
        ];
    }

    public function getTeacherReportEditor(TaiKhoan $teacher, int $reportId): array
    {
        $report = $this->findTeacherReport($teacher, $reportId);

        return $this->buildEditorPayload($report);
    }

    public function saveTeacherDraft(TaiKhoan $teacher, int $reportId, array $payload): BaoCaoHocTap
    {
        $report = $this->findTeacherReport($teacher, $reportId);

        if (! $report->isEditableByTeacher()) {
            abort(422, 'Báo cáo đã khóa chỉnh sửa sau khi gửi duyệt hoặc phát hành.');
        }

        return DB::transaction(function () use ($report, $payload, $teacher) {
            $metadata = $this->buildMetadataSnapshot($report->fresh(['dotDanhGia.lopHoc.khoaHoc', 'dotDanhGia.lopHoc.coSo', 'dotDanhGia.lopHoc.taiKhoan.hoSoNguoiDung', 'dangKyLopHoc.taiKhoan.hoSoNguoiDung']));
            $criteriaPayload = $payload['criteria'] ?? [];

            foreach ($report->tieuChis as $criterion) {
                $systemValue = $this->systemCriterionValue($report, $criterion->maTieuChi);
                $incoming = $criteriaPayload[$criterion->baoCaoHocTapTieuChiId] ?? [];

                if ($criterion->loaiDuLieu === 'readonly_system') {
                    $criterion->forceFill([
                        'giaTriSo' => is_numeric($systemValue) ? (float) $systemValue : null,
                        'noiDungNhanXet' => is_numeric($systemValue) ? null : ($systemValue !== null ? (string) $systemValue : null),
                    ])->save();
                    continue;
                }

                $criterion->forceFill([
                    'giaTriMucDanhGia' => $this->nullableString($incoming['rating'] ?? null),
                    'giaTriSo' => $this->nullableNumber($incoming['number'] ?? null),
                    'noiDungNhanXet' => $this->nullableString($incoming['comment'] ?? null),
                ])->save();
            }

            $report->forceFill([
                'metadataSnapshot' => $metadata,
            ])->save();

            $this->writeHistory($report, 'save_draft', $teacher, 'Lưu nháp báo cáo học tập.');
            $this->syncPeriodStatus($report->dotDanhGia);

            return $report->fresh(['dotDanhGia.lopHoc.khoaHoc', 'dangKyLopHoc.taiKhoan.hoSoNguoiDung', 'tieuChis']);
        });
    }

    public function submitTeacherReport(TaiKhoan $teacher, int $reportId): void
    {
        $report = $this->findTeacherReport($teacher, $reportId);

        if (! $report->isEditableByTeacher()) {
            abort(422, 'Báo cáo không còn ở trạng thái cho phép gửi duyệt.');
        }

        $this->validateReportBeforeSubmit($report);

        DB::transaction(function () use ($report, $teacher) {
            $before = $report->trangThai;

            $report->forceFill([
                'trangThai' => BaoCaoHocTap::TRANG_THAI_SUBMITTED,
                'submittedAt' => now(),
                'metadataSnapshot' => $this->buildMetadataSnapshot($report),
            ])->save();

            $this->writeHistory($report, 'submit', $teacher, 'Giáo viên gửi báo cáo để staff duyệt.', $before, $report->trangThai);
            $this->syncPeriodStatus($report->dotDanhGia()->first());
        });

        $this->notifyStaffAboutSubmission($report, $teacher);
    }

    public function createTeacherDraftsForPeriod(TaiKhoan $teacher, int $periodId): int
    {
        $period = BaoCaoHocTapDotDanhGia::query()
            ->whereHas('lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->findOrFail($periodId);

        return $this->generateDraftsForPeriod($period);
    }

    public function ensureTeacherReportForRegistration(TaiKhoan $teacher, int $periodId, int $registrationId): BaoCaoHocTap
    {
        $period = BaoCaoHocTapDotDanhGia::query()
            ->with(['lopHoc', 'mau.tieuChis'])
            ->whereHas('lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->findOrFail($periodId);

        $registration = DangKyLopHoc::query()
            ->with(['lopHoc', 'taiKhoan.hoSoNguoiDung'])
            ->where('dangKyLopHocId', $registrationId)
            ->where('lopHocId', $period->lopHocId)
            ->firstOrFail();

        $report = BaoCaoHocTap::query()
            ->where('dotDanhGiaId', $period->dotDanhGiaId)
            ->where('dangKyLopHocId', $registration->dangKyLopHocId)
            ->where('version', 1)
            ->first();

        if ($report) {
            return $report;
        }

        return DB::transaction(function () use ($period, $registration) {
            $report = BaoCaoHocTap::query()->create([
                'dotDanhGiaId' => $period->dotDanhGiaId,
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'giaoVienId' => $period->lopHoc?->taiKhoanId,
                'version' => 1,
                'trangThai' => BaoCaoHocTap::TRANG_THAI_DRAFT,
            ]);

            $this->seedReportCriteria($report, $period->mau?->tieuChis ?? $this->getDefaultTemplate()->tieuChis);
            $report->forceFill([
                'metadataSnapshot' => $this->buildMetadataSnapshot($report),
            ])->save();
            $this->writeHistory($report, 'create_draft', null, 'Tạo nháp báo cáo theo yêu cầu giáo viên.');

            return $report;
        });
    }

    public function copyFromPreviousReport(TaiKhoan $teacher, int $reportId): void
    {
        $report = $this->findTeacherReport($teacher, $reportId);

        if (! $report->isEditableByTeacher()) {
            abort(422, 'Báo cáo không còn ở trạng thái cho phép sao chép dữ liệu.');
        }

        $previous = BaoCaoHocTap::query()
            ->with('tieuChis')
            ->where('dangKyLopHocId', $report->dangKyLopHocId)
            ->where('baoCaoHocTapId', '!=', $report->baoCaoHocTapId)
            ->whereIn('trangThai', [
                BaoCaoHocTap::TRANG_THAI_APPROVED,
                BaoCaoHocTap::TRANG_THAI_PUBLISHED,
            ])
            ->orderByDesc('publishedAt')
            ->orderByDesc('approvedAt')
            ->orderByDesc('baoCaoHocTapId')
            ->first();

        if (! $previous) {
            abort(422, 'Không tìm thấy báo cáo trước đó để sao chép.');
        }

        DB::transaction(function () use ($report, $previous, $teacher) {
            $previousCriteria = $previous->tieuChis->keyBy('maTieuChi');

            foreach ($report->tieuChis as $criterion) {
                if ($criterion->loaiDuLieu === 'readonly_system') {
                    continue;
                }

                $source = $previousCriteria->get($criterion->maTieuChi);

                if (! $source) {
                    continue;
                }

                $criterion->forceFill([
                    'giaTriMucDanhGia' => $source->giaTriMucDanhGia,
                    'giaTriSo' => $source->giaTriSo,
                    'noiDungNhanXet' => $source->noiDungNhanXet,
                ])->save();
            }

            $this->writeHistory($report, 'copy_previous', $teacher, 'Sao chép dữ liệu từ báo cáo gần nhất.');
        });
    }

    public function getReportHistoryForTeacher(TaiKhoan $teacher, int $reportId): array
    {
        $report = $this->findTeacherReport($teacher, $reportId);

        $report->load(['lichSus.nguoiThucHien.hoSoNguoiDung', 'dangKyLopHoc.taiKhoan.hoSoNguoiDung', 'dotDanhGia.lopHoc']);

        return [
            'report' => $report,
            'history' => $report->lichSus,
        ];
    }

    public function getStaffQueue(Request $request): array
    {
        $status = trim((string) $request->query('trangThai', BaoCaoHocTap::TRANG_THAI_SUBMITTED));

        $reports = BaoCaoHocTap::query()
            ->with([
                'dotDanhGia.lopHoc.khoaHoc',
                'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
                'giaoVien.hoSoNguoiDung',
            ])
            ->when($status !== '', fn (Builder $query) => $query->where('trangThai', $status))
            ->orderByRaw("CASE WHEN trangThai = ? THEN 0 WHEN trangThai = ? THEN 1 ELSE 2 END", [
                BaoCaoHocTap::TRANG_THAI_SUBMITTED,
                BaoCaoHocTap::TRANG_THAI_APPROVED,
            ])
            ->orderByDesc('submittedAt')
            ->orderByDesc('updated_at')
            ->get();

        return [
            'reports' => $reports,
            'selectedStatus' => $status,
            'statusOptions' => [
                BaoCaoHocTap::TRANG_THAI_SUBMITTED => BaoCaoHocTap::trangThaiLabels()[BaoCaoHocTap::TRANG_THAI_SUBMITTED],
                BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION => BaoCaoHocTap::trangThaiLabels()[BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION],
                BaoCaoHocTap::TRANG_THAI_APPROVED => BaoCaoHocTap::trangThaiLabels()[BaoCaoHocTap::TRANG_THAI_APPROVED],
                BaoCaoHocTap::TRANG_THAI_PUBLISHED => BaoCaoHocTap::trangThaiLabels()[BaoCaoHocTap::TRANG_THAI_PUBLISHED],
            ],
            'summary' => [
                'submitted' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_SUBMITTED)->count(),
                'needs_revision' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION)->count(),
                'approved' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_APPROVED)->count(),
                'published' => $reports->where('trangThai', BaoCaoHocTap::TRANG_THAI_PUBLISHED)->count(),
            ],
        ];
    }

    public function getStaffPeriodList(Request $request): array
    {
        $status = trim((string) $request->query('trangThai', ''));

        $periods = BaoCaoHocTapDotDanhGia::query()
            ->with(['lopHoc.khoaHoc', 'lopHoc.coSo', 'baoCaos'])
            ->when($status !== '', fn (Builder $query) => $query->where('trangThai', $status))
            ->orderByDesc('created_at')
            ->get();

        $classes = LopHoc::query()
            ->with(['khoaHoc', 'coSo', 'taiKhoan.hoSoNguoiDung'])
            ->orderByDesc('ngayBatDau')
            ->orderBy('tenLopHoc')
            ->get();

        return [
            'periods' => $periods,
            'classes' => $classes,
            'templates' => BaoCaoHocTapMau::query()->where('kichHoat', true)->orderByDesc('macDinh')->get(),
            'selectedStatus' => $status,
            'statusOptions' => BaoCaoHocTapDotDanhGia::trangThaiLabels(),
        ];
    }

    public function createPeriod(array $payload, TaiKhoan $staff): BaoCaoHocTapDotDanhGia
    {
        return DB::transaction(function () use ($payload, $staff) {
            $template = isset($payload['baoCaoHocTapMauId']) && $payload['baoCaoHocTapMauId']
                ? BaoCaoHocTapMau::query()->with('tieuChis')->findOrFail((int) $payload['baoCaoHocTapMauId'])
                : $this->getDefaultTemplate();

            $period = BaoCaoHocTapDotDanhGia::query()->create([
                'lopHocId' => (int) $payload['lopHocId'],
                'baoCaoHocTapMauId' => $template->baoCaoHocTapMauId,
                'tenDot' => trim((string) $payload['tenDot']),
                'tuNgay' => $payload['tuNgay'] ?? null,
                'denNgay' => $payload['denNgay'] ?? null,
                'hanNop' => $payload['hanNop'] ?? null,
                'hanDuyet' => $payload['hanDuyet'] ?? null,
                'trangThai' => BaoCaoHocTapDotDanhGia::TRANG_THAI_COLLECTING,
                'createdById' => $staff->taiKhoanId,
            ]);

            $this->generateDraftsForPeriod($period->fresh(['mau.tieuChis', 'lopHoc']));
            $this->notifyTeacherAboutNewPeriod($period, $staff);

            return $period;
        });
    }

    public function getStaffReviewDetail(int $reportId): array
    {
        $report = BaoCaoHocTap::query()
            ->with([
                'dotDanhGia.lopHoc.khoaHoc',
                'dotDanhGia.lopHoc.coSo',
                'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
                'giaoVien.hoSoNguoiDung',
                'nguoiDuyet.hoSoNguoiDung',
                'tieuChis',
                'lichSus.nguoiThucHien.hoSoNguoiDung',
            ])
            ->findOrFail($reportId);

        return $this->buildEditorPayload($report, true);
    }

    public function requestRevision(int $reportId, string $note, TaiKhoan $staff): void
    {
        $report = BaoCaoHocTap::query()->with(['dotDanhGia', 'giaoVien'])->findOrFail($reportId);

        DB::transaction(function () use ($report, $note, $staff) {
            $before = $report->trangThai;

            $report->forceFill([
                'trangThai' => BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION,
                'nguoiDuyetId' => $staff->taiKhoanId,
                'staffReviewNote' => trim($note),
            ])->save();

            $this->writeHistory($report, 'request_revision', $staff, $note, $before, $report->trangThai);
            $this->syncPeriodStatus($report->dotDanhGia()->first());
        });

        $this->notifyTeacherAboutRevision($report, $staff, $note);
    }

    public function approve(int $reportId, TaiKhoan $staff): void
    {
        $report = BaoCaoHocTap::query()->with('dotDanhGia')->findOrFail($reportId);

        if ($report->trangThai !== BaoCaoHocTap::TRANG_THAI_SUBMITTED) {
            abort(422, 'Chỉ có thể duyệt báo cáo đang chờ duyệt.');
        }

        DB::transaction(function () use ($report, $staff) {
            $before = $report->trangThai;

            $report->forceFill([
                'trangThai' => BaoCaoHocTap::TRANG_THAI_APPROVED,
                'nguoiDuyetId' => $staff->taiKhoanId,
                'approvedAt' => now(),
            ])->save();

            $this->writeHistory($report, 'approve', $staff, 'Staff đã duyệt báo cáo.', $before, $report->trangThai);
            $this->syncPeriodStatus($report->dotDanhGia()->first());
        });
    }

    public function publish(int $reportId, TaiKhoan $staff): void
    {
        $report = BaoCaoHocTap::query()
            ->with(['dotDanhGia', 'dangKyLopHoc.taiKhoan'])
            ->findOrFail($reportId);

        if ($report->trangThai !== BaoCaoHocTap::TRANG_THAI_APPROVED) {
            abort(422, 'Chỉ có thể phát hành báo cáo đã được duyệt.');
        }

        DB::transaction(function () use ($report, $staff) {
            $before = $report->trangThai;

            $report->forceFill([
                'trangThai' => BaoCaoHocTap::TRANG_THAI_PUBLISHED,
                'nguoiDuyetId' => $staff->taiKhoanId,
                'publishedAt' => now(),
                'metadataSnapshot' => $this->buildMetadataSnapshot($report),
            ])->save();

            $period = $report->dotDanhGia()->first();
            $this->writeHistory($report, 'publish', $staff, 'Staff đã phát hành báo cáo cho học viên.', $before, $report->trangThai);
            $this->syncPeriodStatus($period);
        });

        $this->notifyStudentAboutPublication($report, $staff);
    }

    public function findTeacherReport(TaiKhoan $teacher, int $reportId): BaoCaoHocTap
    {
        return $this->teacherReportsQuery($teacher)
            ->with([
                'dotDanhGia.lopHoc.khoaHoc',
                'dotDanhGia.lopHoc.coSo',
                'dotDanhGia.lopHoc.taiKhoan.hoSoNguoiDung',
                'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
                'tieuChis',
                'lichSus.nguoiThucHien.hoSoNguoiDung',
            ])
            ->findOrFail($reportId);
    }

    public function findStudentReport(TaiKhoan $student, int $reportId): BaoCaoHocTap
    {
        return BaoCaoHocTap::query()
            ->with([
                'dotDanhGia.lopHoc.khoaHoc',
                'dotDanhGia.lopHoc.coSo',
                'giaoVien.hoSoNguoiDung',
                'tieuChis',
            ])
            ->where('trangThai', BaoCaoHocTap::TRANG_THAI_PUBLISHED)
            ->whereHas('dangKyLopHoc', fn (Builder $query) => $query->where('taiKhoanId', $student->taiKhoanId))
            ->findOrFail($reportId);
    }

    public function getStudentReports(TaiKhoan $student): Collection
    {
        return BaoCaoHocTap::query()
            ->with(['dotDanhGia.lopHoc.khoaHoc', 'giaoVien.hoSoNguoiDung'])
            ->where('trangThai', BaoCaoHocTap::TRANG_THAI_PUBLISHED)
            ->whereHas('dangKyLopHoc', fn (Builder $query) => $query->where('taiKhoanId', $student->taiKhoanId))
            ->orderByDesc('publishedAt')
            ->orderByDesc('baoCaoHocTapId')
            ->get();
    }

    public function generateDraftsForPeriod(BaoCaoHocTapDotDanhGia $period): int
    {
        $period->loadMissing(['lopHoc.taiKhoan', 'mau.tieuChis']);

        $template = $period->mau ?: $this->getDefaultTemplate();
        $registrations = $this->periodRegistrations($period->lopHocId);
        $created = 0;

        foreach ($registrations as $registration) {
            $existing = BaoCaoHocTap::query()
                ->where('dotDanhGiaId', $period->dotDanhGiaId)
                ->where('dangKyLopHocId', $registration->dangKyLopHocId)
                ->where('version', 1)
                ->exists();

            if ($existing) {
                continue;
            }

            $report = BaoCaoHocTap::query()->create([
                'dotDanhGiaId' => $period->dotDanhGiaId,
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'giaoVienId' => $period->lopHoc?->taiKhoanId,
                'version' => 1,
                'trangThai' => BaoCaoHocTap::TRANG_THAI_DRAFT,
            ]);

            $this->seedReportCriteria($report, $template->tieuChis);
            $report->forceFill([
                'metadataSnapshot' => $this->buildMetadataSnapshot($report->fresh(['dotDanhGia.lopHoc.khoaHoc', 'dotDanhGia.lopHoc.coSo', 'dotDanhGia.lopHoc.taiKhoan.hoSoNguoiDung', 'dangKyLopHoc.taiKhoan.hoSoNguoiDung'])),
            ])->save();

            $this->writeHistory($report, 'create_draft', null, 'Hệ thống sinh nháp báo cáo khi tạo đợt đánh giá.');
            $created++;
        }

        $this->syncPeriodStatus($period->fresh());

        return $created;
    }

    public function buildEditorPayload(BaoCaoHocTap $report, bool $readOnly = false): array
    {
        $report->loadMissing([
            'dotDanhGia.lopHoc.khoaHoc',
            'dotDanhGia.lopHoc.coSo',
            'dotDanhGia.lopHoc.taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
            'tieuChis',
            'lichSus.nguoiThucHien.hoSoNguoiDung',
        ]);

        $metadata = $report->metadataSnapshot ?: $this->buildMetadataSnapshot($report);
        $systemValues = $this->systemValuesForReport($report);
        $sections = $report->tieuChis
            ->groupBy('nhom')
            ->map(function (Collection $items, string $group) use ($systemValues) {
                return [
                    'group' => $group,
                    'items' => $items->sortBy('thuTu')->map(function (BaoCaoHocTapTieuChi $item) use ($systemValues) {
                        $value = $item->loaiDuLieu === 'readonly_system'
                            ? ($systemValues[$item->maTieuChi] ?? $item->giaTriSo ?? $item->noiDungNhanXet)
                            : ($item->giaTriMucDanhGia ?: ($item->giaTriSo ?? $item->noiDungNhanXet));

                        return [
                            'id' => $item->baoCaoHocTapTieuChiId,
                            'code' => $item->maTieuChi,
                            'title' => $item->tenTieuChi,
                            'type' => $item->loaiDuLieu,
                            'required' => (bool) $item->batBuoc,
                            'readonly' => (bool) $item->isReadonly,
                            'rating' => $item->giaTriMucDanhGia,
                            'number' => $item->giaTriSo,
                            'comment' => $item->noiDungNhanXet,
                            'value' => $value,
                            'options' => $item->mauTieuChi?->danhSachMuc ?? data_get($item->tuyChon, 'options', []),
                        ];
                    })->values(),
                ];
            })
            ->values();

        return [
            'report' => $report,
            'metadata' => $metadata,
            'sections' => $sections,
            'readOnly' => $readOnly || ! $report->isEditableByTeacher(),
            'systemData' => $systemValues,
            'previousReportAvailable' => BaoCaoHocTap::query()
                ->where('dangKyLopHocId', $report->dangKyLopHocId)
                ->where('baoCaoHocTapId', '!=', $report->baoCaoHocTapId)
                ->whereIn('trangThai', [BaoCaoHocTap::TRANG_THAI_APPROVED, BaoCaoHocTap::TRANG_THAI_PUBLISHED])
                ->exists(),
        ];
    }

    public function buildMetadataSnapshot(BaoCaoHocTap $report): array
    {
        $report->loadMissing([
            'dotDanhGia.lopHoc.khoaHoc',
            'dotDanhGia.lopHoc.coSo',
            'dotDanhGia.lopHoc.taiKhoan.hoSoNguoiDung',
            'dangKyLopHoc.taiKhoan.hoSoNguoiDung',
        ]);

        $registration = $report->dangKyLopHoc;
        $student = $registration?->taiKhoan;
        $profile = $student?->hoSoNguoiDung;
        $class = $registration?->lopHoc ?: $report->dotDanhGia?->lopHoc;
        $teacherProfile = $class?->taiKhoan?->hoSoNguoiDung;
        $attendance = $this->attendanceSummaryForReport($report);

        return [
            'student_name' => $profile?->hoTen ?? $student?->taiKhoan,
            'student_code' => $student?->taiKhoan,
            'course_name' => $class?->khoaHoc?->tenKhoaHoc,
            'class_name' => $class?->tenLopHoc,
            'class_code' => $class?->maLopHoc,
            'facility_name' => $class?->coSo?->tenCoSo,
            'current_level' => $profile?->trinhDoHienTai,
            'teacher_name' => $teacherProfile?->hoTen ?? $class?->taiKhoan?->taiKhoan,
            'start_date' => $class?->ngayBatDau,
            'period_name' => $report->dotDanhGia?->tenDot,
            'period_range' => trim(collect([
                $report->dotDanhGia?->tuNgay?->format('d/m/Y'),
                $report->dotDanhGia?->denNgay?->format('d/m/Y'),
            ])->filter()->implode(' - ')),
            'attendance' => $attendance,
        ];
    }

    public function attendanceSummaryForReport(BaoCaoHocTap $report): array
    {
        $report->loadMissing(['dotDanhGia', 'dangKyLopHoc']);

        $period = $report->dotDanhGia;
        $registration = $report->dangKyLopHoc;

        $sessionQuery = BuoiHoc::query()
            ->where('lopHocId', $registration->lopHocId)
            ->whereNotIn('trangThai', [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH]);

        if ($period?->tuNgay) {
            $sessionQuery->whereDate('ngayHoc', '>=', $period->tuNgay);
        }

        if ($period?->denNgay) {
            $sessionQuery->whereDate('ngayHoc', '<=', $period->denNgay);
        }

        $sessionIds = $sessionQuery->pluck('buoiHocId');
        $attendance = DiemDanh::query()
            ->where('dangKyLopHocId', $registration->dangKyLopHocId)
            ->whereIn('buoiHocId', $sessionIds)
            ->get();

        $totalSessions = $sessionIds->count();
        $absentUnexcused = $attendance->where('trangThai', DiemDanh::VANG_KHONG_PHEP)->count();
        $present = $attendance->where('trangThai', DiemDanh::CO_MAT)->count();

        return [
            'total_sessions' => $totalSessions,
            'absent_unexcused' => $absentUnexcused,
            'present_sessions' => $present,
            'attendance_rate' => $totalSessions > 0 ? round(($present / $totalSessions) * 100, 1) : 0,
        ];
    }

    public function systemValuesForReport(BaoCaoHocTap $report): array
    {
        $attendance = $this->attendanceSummaryForReport($report);

        return [
            'attendance_total_sessions' => $attendance['total_sessions'],
            'attendance_absent_unexcused' => $attendance['absent_unexcused'],
        ];
    }

    private function validateReportBeforeSubmit(BaoCaoHocTap $report): void
    {
        $report->loadMissing('tieuChis');

        $missing = [];
        foreach ($report->tieuChis as $criterion) {
            if (! $criterion->batBuoc || $criterion->loaiDuLieu === 'readonly_system') {
                continue;
            }

            $isFilled = match ($criterion->loaiDuLieu) {
                'rating' => filled($criterion->giaTriMucDanhGia),
                'number', 'ratio' => $criterion->giaTriSo !== null,
                default => filled($criterion->noiDungNhanXet),
            };

            if (! $isFilled) {
                $missing[] = $criterion->tenTieuChi;
            }
        }

        if (! empty($missing)) {
            throw ValidationException::withMessages([
                'report' => 'Chưa thể gửi duyệt. Thiếu dữ liệu ở các tiêu chí bắt buộc: ' . implode(', ', array_slice($missing, 0, 6)),
            ]);
        }
    }

    private function teacherReportsQuery(TaiKhoan $teacher): Builder
    {
        return BaoCaoHocTap::query()
            ->whereHas('dotDanhGia.lopHoc', fn (Builder $query) => $query->where('taiKhoanId', $teacher->taiKhoanId));
    }

    private function periodRegistrations(int $classId): Collection
    {
        $baseQuery = DangKyLopHoc::query()
            ->with(['taiKhoan.hoSoNguoiDung', 'lopHoc.khoaHoc', 'lopHoc.coSo', 'lopHoc.taiKhoan.hoSoNguoiDung'])
            ->where('lopHocId', $classId)
            ->orderBy('dangKyLopHocId');

        $preferred = (clone $baseQuery)
            ->whereIn('trangThai', [
                DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                DangKyLopHoc::TRANG_THAI_DANG_HOC,
                DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
            ])
            ->get();

        if ($preferred->isNotEmpty()) {
            return $preferred;
        }

        // Fallback cho dữ liệu lớp mới/legacy chưa đồng bộ trạng thái:
        // vẫn sinh nháp cho toàn bộ đăng ký chưa hủy hoặc hoàn thành để giáo viên thấy danh sách học viên.
        return (clone $baseQuery)
            ->whereNotIn('trangThai', [
                DangKyLopHoc::TRANG_THAI_HOAN_THANH,
                DangKyLopHoc::TRANG_THAI_HUY,
            ])
            ->get();
    }

    private function seedReportCriteria(BaoCaoHocTap $report, Collection $criteria): void
    {
        foreach ($criteria as $templateCriterion) {
            $systemValue = $this->systemCriterionValue($report, $templateCriterion->maTieuChi);

            BaoCaoHocTapTieuChi::query()->create([
                'baoCaoHocTapId' => $report->baoCaoHocTapId,
                'baoCaoHocTapMauTieuChiId' => $templateCriterion->baoCaoHocTapMauTieuChiId,
                'nhom' => $templateCriterion->nhom,
                'maTieuChi' => $templateCriterion->maTieuChi,
                'tenTieuChi' => $templateCriterion->tenTieuChi,
                'loaiDuLieu' => $templateCriterion->loaiDuLieu,
                'giaTriSo' => $templateCriterion->loaiDuLieu === 'readonly_system' && is_numeric($systemValue) ? (float) $systemValue : null,
                'noiDungNhanXet' => $templateCriterion->loaiDuLieu === 'readonly_system' && ! is_numeric($systemValue) ? (string) $systemValue : null,
                'tuyChon' => [
                    'options' => $templateCriterion->danhSachMuc ?? [],
                ],
                'batBuoc' => $templateCriterion->batBuoc,
                'isReadonly' => $templateCriterion->isReadonly,
                'thuTu' => $templateCriterion->thuTu,
            ]);
        }
    }

    private function syncPeriodStatus(?BaoCaoHocTapDotDanhGia $period): void
    {
        if (! $period) {
            return;
        }

        $statuses = $period->baoCaos()->pluck('trangThai');

        if ($statuses->isEmpty()) {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_COLLECTING;
        } elseif ($statuses->every(fn (string $status) => $status === BaoCaoHocTap::TRANG_THAI_PUBLISHED)) {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_PUBLISHED;
        } elseif ($statuses->contains(BaoCaoHocTap::TRANG_THAI_APPROVED) || $statuses->contains(BaoCaoHocTap::TRANG_THAI_NEEDS_REVISION)) {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_STAFF_REVIEWING;
        } elseif ($statuses->every(fn (string $status) => in_array($status, [
            BaoCaoHocTap::TRANG_THAI_SUBMITTED,
            BaoCaoHocTap::TRANG_THAI_APPROVED,
            BaoCaoHocTap::TRANG_THAI_PUBLISHED,
        ], true))) {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_TEACHER_SUBMITTED;
        } elseif ($statuses->contains(BaoCaoHocTap::TRANG_THAI_SUBMITTED)) {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_STAFF_REVIEWING;
        } else {
            $next = BaoCaoHocTapDotDanhGia::TRANG_THAI_COLLECTING;
        }

        $attributes = ['trangThai' => $next];
        if ($next === BaoCaoHocTapDotDanhGia::TRANG_THAI_PUBLISHED && ! $period->publishedAt) {
            $attributes['publishedAt'] = now();
        }

        $period->forceFill($attributes)->save();
    }

    private function writeHistory(
        BaoCaoHocTap $report,
        string $action,
        ?TaiKhoan $actor = null,
        ?string $note = null,
        ?string $beforeStatus = null,
        ?string $afterStatus = null
    ): void {
        BaoCaoHocTapLichSu::query()->create([
            'baoCaoHocTapId' => $report->baoCaoHocTapId,
            'hanhDong' => $action,
            'trangThaiTruoc' => $beforeStatus,
            'trangThaiSau' => $afterStatus,
            'nguoiThucHienId' => $actor?->taiKhoanId,
            'ghiChu' => $note,
            'duLieu' => null,
            'created_at' => now(),
        ]);
    }

    private function systemCriterionValue(BaoCaoHocTap $report, string $code): string|int|float|null
    {
        $systemValues = $this->systemValuesForReport($report);

        return $systemValues[$code] ?? null;
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved === '' ? null : $resolved;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function notifyTeacherAboutNewPeriod(BaoCaoHocTapDotDanhGia $period, TaiKhoan $staff): void
    {
        $teacherId = $period->lopHoc?->taiKhoanId;

        if (! $teacherId) {
            return;
        }

        $this->sendNotification(
            title: 'Có đợt báo cáo học tập mới',
            content: 'Lớp ' . ($period->lopHoc?->tenLopHoc ?? '') . ' vừa được mở đợt "' . $period->tenDot . '". Hệ thống đã sinh nháp báo cáo cho từng học viên.',
            senderId: $staff->taiKhoanId,
            targetType: ThongBao::DOI_TUONG_CA_NHAN,
            targetId: $teacherId
        );
    }

    private function notifyStaffAboutSubmission(BaoCaoHocTap $report, TaiKhoan $teacher): void
    {
        $studentName = data_get($report->metadataSnapshot, 'student_name') ?? 'học viên';

        $this->sendNotification(
            title: 'Có báo cáo học tập chờ duyệt',
            content: 'Giáo viên vừa gửi báo cáo của ' . $studentName . ' trong đợt "' . ($report->dotDanhGia?->tenDot ?? '') . '" để staff duyệt.',
            senderId: $teacher->taiKhoanId,
            targetType: ThongBao::DOI_TUONG_THEO_ROLE,
            targetId: TaiKhoan::ROLE_NHAN_VIEN
        );
    }

    private function notifyTeacherAboutRevision(BaoCaoHocTap $report, TaiKhoan $staff, string $note): void
    {
        if (! $report->giaoVienId) {
            return;
        }

        $studentName = data_get($report->metadataSnapshot, 'student_name') ?? 'học viên';
        $message = 'Báo cáo của ' . $studentName . ' cần chỉnh sửa.';

        if (trim($note) !== '') {
            $message .= ' Ghi chú duyệt: ' . trim($note);
        }

        $this->sendNotification(
            title: 'Báo cáo học tập cần chỉnh sửa',
            content: $message,
            senderId: $staff->taiKhoanId,
            targetType: ThongBao::DOI_TUONG_CA_NHAN,
            targetId: $report->giaoVienId
        );
    }

    private function notifyStudentAboutPublication(BaoCaoHocTap $report, TaiKhoan $staff): void
    {
        $studentId = $report->dangKyLopHoc?->taiKhoanId;

        if (! $studentId) {
            return;
        }

        $this->sendNotification(
            title: 'Báo cáo học tập đã được phát hành',
            content: 'Báo cáo học tập đợt "' . ($report->dotDanhGia?->tenDot ?? '') . '" đã sẵn sàng trên cổng học viên.',
            senderId: $staff->taiKhoanId,
            targetType: ThongBao::DOI_TUONG_CA_NHAN,
            targetId: $studentId
        );
    }

    private function sendNotification(string $title, string $content, ?int $senderId, int $targetType, ?int $targetId): void
    {
        $notification = ThongBao::query()->create([
            'tieuDe' => $title,
            'noiDung' => $content,
            'nguoiGuiId' => $senderId,
            'doiTuongGui' => $targetType,
            'doiTuongId' => $targetId,
            'ngayGui' => now(),
            'trangThai' => 1,
            'loaiGui' => ThongBao::LOAI_HOC_TAP,
            'uuTien' => ThongBao::UU_TIEN_BINH_THUONG,
            'ghim' => false,
            'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_GUI,
            'sent_at' => now(),
        ]);

        $this->thongBaoService->guiThongBao($notification);
    }
}
