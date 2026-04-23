<?php

namespace App\Services\Teacher\DiemDanh;

use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DiemDanh;
use App\Models\Education\LopHoc;
use App\Models\Finance\HoaDon;
use App\Models\Interaction\ThongBao;
use App\Services\Admin\ThongBao\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherAttendanceService
{
    public function __construct(
        private readonly ThongBaoService $thongBaoService
    ) {}

    public function getIndexData(Request $request, TaiKhoan $teacher): array
    {
        $courseId = $request->integer('khoaHocId') ?: null;
        $classId = $request->integer('lopHocId') ?: null;
        $sessionId = $request->integer('buoiHocId') ?: null;

        $classesBaseQuery = LopHoc::query()
            ->with(['khoaHoc', 'coSo', 'caHoc'])
            ->where('taiKhoanId', $teacher->taiKhoanId)
            ->orderByDesc('ngayBatDau')
            ->orderBy('tenLopHoc');

        $courses = KhoaHoc::query()
            ->whereIn('khoaHocId', (clone $classesBaseQuery)->select('khoaHocId')->distinct())
            ->orderBy('tenKhoaHoc')
            ->get();

        $classes = (clone $classesBaseQuery)
            ->when($courseId, fn ($query) => $query->where('khoaHocId', $courseId))
            ->get();

        $selectedClass = $classId
            ? (clone $classesBaseQuery)->whereKey($classId)->first()
            : null;

        $sessions = collect();
        if ($selectedClass) {
            $sessions = $this->eligibleSessionsQuery($selectedClass->lopHocId)->get();
        }

        $selectedSession = null;
        $attendanceRows = collect();
        $latestAttendanceAt = null;

        if ($selectedClass && $sessionId) {
            $selectedSession = BuoiHoc::query()
                ->with(['caHoc', 'phongHoc', 'lopHoc.khoaHoc', 'lopHoc.coSo'])
                ->where('lopHocId', $selectedClass->lopHocId)
                ->findOrFail($sessionId);

            [$attendanceRows, $latestAttendanceAt] = $this->buildAttendanceRows($selectedClass, $selectedSession);
        }

        return [
            'courses' => $courses,
            'classes' => $classes,
            'sessions' => $sessions,
            'selectedCourseId' => $courseId,
            'selectedClass' => $selectedClass,
            'selectedSession' => $selectedSession,
            'selectedSessionNote' => $selectedSession?->ghiChu,
            'attendanceRows' => $attendanceRows,
            'latestAttendanceAt' => $latestAttendanceAt,
            'isSessionLocked' => $selectedSession ? $this->isSessionLocked($selectedSession) : true,
        ];
    }

    public function getClassesForCourse(TaiKhoan $teacher, ?int $courseId = null): Collection
    {
        return $this->teacherClassesQuery($teacher)
            ->when($courseId, fn ($query) => $query->where('khoaHocId', $courseId))
            ->get()
            ->map(function (LopHoc $class) {
                return [
                    'id' => $class->lopHocId,
                    'code' => $class->maLopHoc,
                    'name' => $class->tenLopHoc,
                    'label' => trim(sprintf('[%s] %s', $class->maLopHoc, $class->tenLopHoc)),
                ];
            })
            ->values();
    }

    public function getSessionsForClass(TaiKhoan $teacher, int $classId): Collection
    {
        $class = $this->teacherClassesQuery($teacher)->whereKey($classId)->firstOrFail();

        return $this->eligibleSessionsQuery($class->lopHocId)
            ->get()
            ->map(function (BuoiHoc $session) {
                $dateLabel = $session->ngayHoc ? Carbon::parse($session->ngayHoc)->format('d/m/Y') : '—';
                $timeLabel = $session->caHoc?->tenCa ? ' · ' . $session->caHoc->tenCa : '';

                return [
                    'id' => $session->buoiHocId,
                    'name' => $session->tenBuoiHoc ?: ('Buổi #' . $session->buoiHocId),
                    'date' => $dateLabel,
                    'status' => $session->trangThaiLabel,
                    'label' => ($session->tenBuoiHoc ?: ('Buổi #' . $session->buoiHocId)) . ' - ' . $dateLabel . $timeLabel,
                ];
            })
            ->values();
    }

    public function saveAttendance(Request $request, TaiKhoan $teacher, int $buoiHocId): array
    {
        $session = $this->findTeacherSession($teacher, $buoiHocId);

        if ($this->isSessionLocked($session)) {
            abort(422, 'Buổi học đã kết thúc hoặc không còn cho phép điểm danh.');
        }

        $session->loadMissing(['lopHoc']);
        $class = $session->lopHoc;

        $request->validate([
            'attendance' => 'array',
            'attendance.*' => 'nullable|in:0,1',
            'ghiChu' => 'array',
            'ghiChu.*' => 'nullable|string|max:500',
            'noiDungDiemDanh' => 'nullable|string|max:1000',
        ]);

        $registrations = $this->registrationsForAttendance($class);
        $submittedAttendance = $request->input('attendance', []);
        $submittedNotes = $request->input('ghiChu', []);
        $sessionNote = trim((string) $request->input('noiDungDiemDanh', '')) ?: null;
        $existingAttendance = DiemDanh::query()
            ->where('buoiHocId', $session->buoiHocId)
            ->whereIn('dangKyLopHocId', $registrations->pluck('dangKyLopHocId'))
            ->get()
            ->keyBy('dangKyLopHocId');
        $newlyAbsentRegistrations = collect();
        $restoredPresentRegistrations = collect();

        foreach ($registrations as $registration) {
            $studentId = (int) $registration->taiKhoanId;
            $isPresent = (int) ($submittedAttendance[$studentId] ?? 0) === 1;
            $previousAttendance = $existingAttendance->get($registration->dangKyLopHocId);
            $previousWasAbsent = $previousAttendance
                ? $this->isAbsentStatus((int) $previousAttendance->trangThai)
                : false;

            DiemDanh::updateOrCreate(
                [
                    'buoiHocId' => $session->buoiHocId,
                    'taiKhoanId' => $studentId,
                ],
                [
                    'dangKyLopHocId' => $registration->dangKyLopHocId,
                    'trangThai' => $isPresent ? DiemDanh::CO_MAT : DiemDanh::VANG_KHONG_PHEP,
                    'nguoiDiemDanhId' => $teacher->taiKhoanId,
                    'ghiChu' => trim((string) ($submittedNotes[$studentId] ?? '')) ?: null,
                ]
            );

            if (!$isPresent && !$previousWasAbsent) {
                $newlyAbsentRegistrations->push($registration);
            }

            if ($isPresent && $previousWasAbsent) {
                $restoredPresentRegistrations->push($registration);
            }
        }

        $session->forceFill([
            'daDiemDanh' => 1,
            'ghiChu' => $sessionNote,
        ])->save();

        if ($newlyAbsentRegistrations->isNotEmpty()) {
            $this->notifyAttendanceStatusChange($session, $teacher, $newlyAbsentRegistrations, 'absent');
        }

        if ($restoredPresentRegistrations->isNotEmpty()) {
            $this->notifyAttendanceStatusChange($session, $teacher, $restoredPresentRegistrations, 'present');
        }

        return [
            'message' => 'Đã lưu điểm danh cho buổi học.',
            'lopHocId' => $class?->lopHocId,
            'khoaHocId' => $class?->khoaHocId,
            'buoiHocId' => $session->buoiHocId,
        ];
    }

    public function exportAttendance(TaiKhoan $teacher, int $buoiHocId): StreamedResponse
    {
        $session = $this->findTeacherSession($teacher, $buoiHocId);
        $session->loadMissing(['caHoc', 'phongHoc', 'lopHoc.khoaHoc', 'lopHoc.coSo']);
        [$rows] = $this->buildAttendanceRows($session->lopHoc, $session);

        $filename = 'diem-danh-' . $session->buoiHocId . '-' . Carbon::parse($session->ngayHoc)->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows, $session) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Danh sach diem danh']);
            fputcsv($handle, ['Lop hoc', $session->lopHoc?->tenLopHoc ?? '']);
            fputcsv($handle, ['Khoa hoc', $session->lopHoc?->khoaHoc?->tenKhoaHoc ?? '']);
            fputcsv($handle, ['Ngay hoc', $session->ngayHoc ? Carbon::parse($session->ngayHoc)->format('d/m/Y') : '']);
            fputcsv($handle, ['Ca hoc', $session->caHoc?->tenCa ?? '']);
            fputcsv($handle, ['Noi dung diem danh', $session->ghiChu ?? '']);
            fputcsv($handle, []);
            fputcsv($handle, ['STT', 'Tai khoan', 'Ho ten', 'Ngay sinh', 'Trang thai', 'Phan tram vang hien tai', 'Ghi chu']);

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['taiKhoan'],
                    $row['hoTen'],
                    $row['ngaySinh'],
                    $row['status_label'],
                    $row['absence_percent'] . '%',
                    $row['ghiChu'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildAttendanceRows(LopHoc $class, BuoiHoc $session): array
    {
        $class->loadMissing(['khoaHoc', 'coSo', 'caHoc']);

        $registrations = $this->registrationsForAttendance($class);
        $historicalSessionIds = BuoiHoc::query()
            ->where('lopHocId', $class->lopHocId)
            ->whereDate('ngayHoc', '<=', $session->ngayHoc)
            ->whereNotIn('trangThai', [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH])
            ->pluck('buoiHocId');

        $attendanceCollection = DiemDanh::query()
            ->whereIn('dangKyLopHocId', $registrations->pluck('dangKyLopHocId'))
            ->whereIn('buoiHocId', $historicalSessionIds)
            ->get()
            ->groupBy('dangKyLopHocId');

        $currentAttendance = DiemDanh::query()
            ->where('buoiHocId', $session->buoiHocId)
            ->whereIn('dangKyLopHocId', $registrations->pluck('dangKyLopHocId'))
            ->get()
            ->keyBy('dangKyLopHocId');

        $latestAttendanceAt = $currentAttendance->max('updated_at');

        $rows = $registrations->values()->map(function (DangKyLopHoc $registration) use ($attendanceCollection, $currentAttendance, $historicalSessionIds) {
            $student = $registration->taiKhoan;
            $profile = $student?->hoSoNguoiDung;
            $history = $attendanceCollection->get($registration->dangKyLopHocId, collect());
            $absenceCount = $history->filter(fn (DiemDanh $attendance) => in_array((int) $attendance->trangThai, [
                DiemDanh::VANG_KHONG_PHEP,
                DiemDanh::BI_KHOA_NO_HP,
            ], true))->count();
            $totalSessions = max(1, $historicalSessionIds->count());
            $current = $currentAttendance->get($registration->dangKyLopHocId);
            $status = $current?->trangThai ?? DiemDanh::CO_MAT;

            return [
                'registration_id' => $registration->dangKyLopHocId,
                'student_id' => $registration->taiKhoanId,
                'taiKhoan' => $student?->taiKhoan ?? '—',
                'hoTen' => $profile?->hoTen ?? ($student?->taiKhoan ?? '—'),
                'ngaySinh' => $profile?->ngaySinh ? Carbon::parse($profile->ngaySinh)->format('d/m/Y') : '—',
                'is_present' => (int) $status === DiemDanh::CO_MAT,
                'status' => (int) $status,
                'status_label' => match ((int) $status) {
                    DiemDanh::VANG_KHONG_PHEP => 'Vắng không phép',
                    DiemDanh::BI_KHOA_NO_HP => 'Bị khóa - Nợ học phí',
                    default => 'Có mặt',
                },
                'absence_percent' => round(($absenceCount / $totalSessions) * 100, 1),
                'ghiChu' => $current?->ghiChu ?? '',
            ];
        });

        return [$rows, $latestAttendanceAt ? Carbon::parse($latestAttendanceAt) : null];
    }

    private function registrationsForAttendance(LopHoc $class): Collection
    {
        return DangKyLopHoc::query()
            ->with(['taiKhoan.hoSoNguoiDung', 'hoaDons.lopHocDotThu'])
            ->where('lopHocId', $class->lopHocId)
            ->whereNotIn('trangThai', [
                DangKyLopHoc::TRANG_THAI_BAO_LUU,
                DangKyLopHoc::TRANG_THAI_HUY,
            ])
            ->orderBy('dangKyLopHocId')
            ->get()
            ->reject(fn (DangKyLopHoc $registration) => $this->isAttendanceBlockedByMainTuition($registration))
            ->values();
    }

    private function isSessionLocked(BuoiHoc $session): bool
    {
        return in_array((int) $session->trangThai, [
            BuoiHoc::TRANG_THAI_DA_HOAN_THANH,
            BuoiHoc::TRANG_THAI_DA_HUY,
            BuoiHoc::TRANG_THAI_DOI_LICH,
        ], true) || (bool) $session->daHoanThanh;
    }

    private function findTeacherSession(TaiKhoan $teacher, int $buoiHocId): BuoiHoc
    {
        return BuoiHoc::query()
            ->with(['caHoc', 'phongHoc', 'lopHoc.khoaHoc', 'lopHoc.coSo', 'lopHoc'])
            ->whereKey($buoiHocId)
            ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $teacher->taiKhoanId))
            ->firstOrFail();
    }

    private function isAttendanceBlockedByMainTuition(DangKyLopHoc $registration): bool
    {
        $registration->loadMissing(['hoaDons.lopHocDotThu', 'lopHoc']);

        if (!$registration->lopHoc?->isInProgress()) {
            return false;
        }

        $mainTuitionInvoices = $registration->hoaDons
            ->filter(fn (HoaDon $invoice) => $invoice->nguonThu === HoaDon::NGUON_THU_HOC_PHI)
            ->values();

        if ($mainTuitionInvoices->isEmpty()) {
            return false;
        }

        return $mainTuitionInvoices->contains(function (HoaDon $invoice) {
            return $invoice->isQuaHan && (int) $invoice->trangThai !== HoaDon::TRANG_THAI_DA_TT;
        });
    }

    private function notifyAttendanceStatusChange(BuoiHoc $session, TaiKhoan $teacher, Collection $registrations, string $type): void
    {
        $session->loadMissing(['lopHoc.khoaHoc', 'caHoc']);
        $sessionDateLabel = $session->ngayHoc
            ? Carbon::parse($session->ngayHoc)->format('d/m/Y')
            : 'chưa xác định';
        $className = $session->lopHoc?->tenLopHoc ?? 'Lớp học';
        $courseName = $session->lopHoc?->khoaHoc?->tenKhoaHoc ?? 'Khóa học';
        $teacherName = $teacher->hoSoNguoiDung?->hoTen
            ?? $teacher->nhanSu?->hoTen
            ?? $teacher->taiKhoan;
        $scheduleUrl = route('home.student.schedule');
        $buttonLabel = 'Xem lịch học của tôi';
        $style = $type === 'present'
            ? ['badgeBg' => '#ecfdf5', 'badgeColor' => '#047857', 'buttonBg' => '#047857']
            : ['badgeBg' => '#fff1f2', 'badgeColor' => '#be123c', 'buttonBg' => '#0f766e'];

        foreach ($registrations as $registration) {
            $registration->loadMissing('taiKhoan.hoSoNguoiDung');
            $studentName = $registration->taiKhoan?->hoSoNguoiDung?->hoTen
                ?? $registration->taiKhoan?->taiKhoan
                ?? 'học viên';
            [$title, $summary] = $type === 'present'
                ? [
                    'Cập nhật điểm danh: đã có mặt',
                    'Điểm danh của bạn đã được cập nhật lại từ vắng sang có mặt.',
                ]
                : [
                    'Thông báo vắng học',
                    'Hệ thống ghi nhận bạn vắng mặt trong buổi học này.',
                ];

            $notification = ThongBao::create([
                'tieuDe' => $title,
                'noiDung' => sprintf(
                    '<div style="font-family:Inter,Segoe UI,Arial,sans-serif;line-height:1.65;color:#0f172a">
                        <div style="margin-bottom:16px">
                            <span style="display:inline-block;padding:6px 12px;border-radius:999px;background:%s;color:%s;font-size:12px;font-weight:700">%s</span>
                        </div>
                        <p style="margin:0 0 12px">Xin chào <strong>%s</strong>,</p>
                        <p style="margin:0 0 12px">%s</p>
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px;margin:16px 0">
                            <div><strong>Lớp học:</strong> %s</div>
                            <div><strong>Khóa học:</strong> %s</div>
                            <div><strong>Ngày học:</strong> %s</div>
                            <div><strong>Ca học:</strong> %s</div>
                            <div><strong>Giáo viên phụ trách:</strong> %s</div>
                        </div>
                        <p style="margin:0 0 16px">Bạn có thể mở lịch học để kiểm tra lại các buổi học liên quan.</p>
                        <p style="margin:0 0 18px">
                            <a href="%s" style="display:inline-block;padding:10px 16px;border-radius:10px;background:%s;color:#ffffff;text-decoration:none;font-weight:700">%s</a>
                        </p>
                        <p style="margin:0;color:#475569">Nếu có sai lệch thông tin, vui lòng liên hệ giáo viên phụ trách hoặc trung tâm để được hỗ trợ.</p>
                    </div>',
                    e($style['badgeBg']),
                    e($style['badgeColor']),
                    e($type === 'present' ? 'Đã cập nhật có mặt' : 'Vắng mặt'),
                    e($studentName),
                    e($summary),
                    e($className),
                    e($courseName),
                    e($sessionDateLabel),
                    e($session->caHoc?->tenCa ?? 'Chưa xác định'),
                    e($teacherName),
                    e($scheduleUrl),
                    e($style['buttonBg']),
                    e($buttonLabel),
                ),
                'nguoiGuiId' => $teacher->taiKhoanId,
                'loaiThongBao' => ThongBao::LOAI_HOC_TAP,
                'doiTuongGui' => ThongBao::DOI_TUONG_CA_NHAN,
                'doiTuongId' => $registration->taiKhoanId,
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

    private function isAbsentStatus(int $status): bool
    {
        return in_array($status, [
            DiemDanh::VANG_KHONG_PHEP,
            DiemDanh::BI_KHOA_NO_HP,
        ], true);
    }

    private function teacherClassesQuery(TaiKhoan $teacher)
    {
        return LopHoc::query()
            ->with(['khoaHoc', 'coSo', 'caHoc'])
            ->where('taiKhoanId', $teacher->taiKhoanId)
            ->orderByDesc('ngayBatDau')
            ->orderBy('tenLopHoc');
    }

    private function eligibleSessionsQuery(int $classId)
    {
        return BuoiHoc::query()
            ->with('caHoc')
            ->where('lopHocId', $classId)
            ->whereDate('ngayHoc', '<=', today())
            ->whereNotIn('trangThai', [
                BuoiHoc::TRANG_THAI_DA_HUY,
                BuoiHoc::TRANG_THAI_DOI_LICH,
            ])
            ->orderByDesc('ngayHoc')
            ->orderByDesc('caHocId');
    }
}
