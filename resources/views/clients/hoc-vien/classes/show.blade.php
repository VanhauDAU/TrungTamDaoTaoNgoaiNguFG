@extends('layouts.client')

@section('title', 'Chi tiết lớp học - ' . $lopHoc->tenLopHoc)

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
    <style>
        .student-class-detail { background: #f5f7fb; padding: 32px 0 56px; }
        .class-hero { background: linear-gradient(135deg, #0f766e 0%, #155e75 54%, #1e293b 100%); border-radius: 28px; color: #fff; overflow: hidden; position: relative; }
        .class-hero::after { content: ""; position: absolute; inset: auto -80px -120px auto; width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,.12); }
        .class-hero__body { position: relative; z-index: 1; padding: 28px; }
        .class-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 13px; border-radius: 999px; background: rgba(255,255,255,.15); color: #fff; font-size: .86rem; }
        .class-stat-card { border: 0; border-radius: 22px; box-shadow: 0 14px 35px rgba(15,23,42,.07); }
        .class-stat-value { font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .class-section { border: 0; border-radius: 24px; box-shadow: 0 14px 35px rgba(15,23,42,.07); overflow: hidden; }
        .class-section__head { padding: 20px 24px; border-bottom: 1px solid #edf2f7; background: #fff; }
        .class-section__body { padding: 24px; background: #fff; }
        .class-nav { position: sticky; top: 92px; }
        .class-nav a { display: flex; align-items: center; gap: 10px; padding: 12px 14px; color: #475569; text-decoration: none; border-radius: 14px; font-weight: 700; }
        .class-nav a:hover { background: #ecfeff; color: #0f766e; }
        .material-batch { border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; }
        .material-batch__head { background: linear-gradient(135deg, #ecfeff, #f8fafc); padding: 16px 18px; border-bottom: 1px solid #e2e8f0; }
        .material-card { border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px; height: 100%; }
        .timeline-row { border-left: 3px solid #cbd5e1; padding-left: 16px; position: relative; }
        .timeline-row::before { content: ""; position: absolute; left: -7px; top: 4px; width: 11px; height: 11px; border-radius: 50%; background: #14b8a6; border: 2px solid #fff; box-shadow: 0 0 0 3px #ccfbf1; }
        .attendance-pill { border-radius: 999px; padding: 6px 10px; font-size: .78rem; font-weight: 800; }
        .attendance-present { background: #dcfce7; color: #166534; }
        .attendance-absent { background: #fee2e2; color: #991b1b; }
        .attendance-locked { background: #e2e8f0; color: #334155; }
        .attendance-none { background: #fef3c7; color: #92400e; }
        .empty-panel { border: 1px dashed #cbd5e1; border-radius: 20px; padding: 32px; text-align: center; color: #64748b; background: #f8fafc; }
        @media (max-width: 991.98px) { .class-nav { position: static; } .class-hero__body { padding: 22px; } }
    </style>
@endsection

@section('content')
@php
    $course = $lopHoc->khoaHoc;
    $teacherProfile = $lopHoc->taiKhoan?->hoSoNguoiDung;
    $primaryRoom = $lopHoc->phongHoc;
    $primaryShift = $lopHoc->caHoc;
    $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : 'Chưa cập nhật';
    $time = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : '--:--';
    $regBadgeClass = match ((int) $registration->trangThai) {
        \App\Models\Education\DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN => 'bg-warning-subtle text-warning-emphasis',
        \App\Models\Education\DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 'bg-danger-subtle text-danger',
        \App\Models\Education\DangKyLopHoc::TRANG_THAI_BAO_LUU => 'bg-secondary-subtle text-secondary',
        \App\Models\Education\DangKyLopHoc::TRANG_THAI_HOAN_THANH => 'bg-success-subtle text-success',
        \App\Models\Education\DangKyLopHoc::TRANG_THAI_HUY => 'bg-dark-subtle text-dark',
        default => 'bg-primary-subtle text-primary',
    };
    $attendanceBadge = function ($attendance) {
        if (!$attendance) {
            return ['class' => 'attendance-none', 'label' => 'Chưa có dữ liệu'];
        }

        return match ((int) $attendance->trangThai) {
            \App\Models\Education\DiemDanh::CO_MAT => ['class' => 'attendance-present', 'label' => 'Có mặt'],
            \App\Models\Education\DiemDanh::BI_KHOA_NO_HP => ['class' => 'attendance-locked', 'label' => 'Bị khóa - nợ học phí'],
            default => ['class' => 'attendance-absent', 'label' => 'Vắng không phép'],
        };
    };
@endphp

<section class="student-class-detail">
    <div class="custom-container">
        <div class="row">
            @include('components.client.account-sidebar')

            <div class="col-lg-9">
                <x-client.account-breadcrumb :items="[
                    ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                    ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                    ['label' => 'Lớp học của tôi', 'url' => route('home.student.classes')],
                    ['label' => $lopHoc->tenLopHoc],
                ]" />

                <div class="class-hero mb-4">
                    <div class="class-hero__body">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="class-chip"><i class="fas fa-book-open"></i>{{ $course?->tenKhoaHoc ?? 'Khóa học' }}</span>
                                    <span class="class-chip"><i class="fas fa-layer-group"></i>{{ $lopHoc->maLopHoc ?? 'Mã lớp chưa cập nhật' }}</span>
                                </div>
                                <h1 class="h3 fw-bold mb-2">{{ $lopHoc->tenLopHoc }}</h1>
                                <p class="mb-0 opacity-75">
                                    {{ $date($lopHoc->ngayBatDau) }} - {{ $date($lopHoc->ngayKetThuc) }}
                                    @if($teacherProfile)
                                        <span class="mx-2">|</span>Giáo viên: {{ $teacherProfile->hoTen }}
                                    @endif
                                </p>
                            </div>
                            <span class="badge {{ $regBadgeClass }} rounded-pill px-3 py-2">
                                {{ $registration->trangThaiLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card class-stat-card h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-2">Tiến độ buổi học</div>
                                <div class="class-stat-value">{{ $sessionSummary['progress'] }}%</div>
                                <div class="small text-muted mt-2">{{ $sessionSummary['completed'] }}/{{ $sessionSummary['total'] }} buổi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card class-stat-card h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-2">Tỷ lệ có mặt</div>
                                <div class="class-stat-value">{{ $attendanceSummary['rate'] }}%</div>
                                <div class="small text-muted mt-2">{{ $attendanceSummary['present'] }}/{{ $attendanceSummary['checked'] }} buổi đã điểm danh</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card class-stat-card h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-2">Tài liệu</div>
                                <div class="class-stat-value">{{ $taiLieus->count() }}</div>
                                <div class="small text-muted mt-2">{{ $taiLieuGroups->count() }} đợt gửi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card class-stat-card h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-2">Sĩ số lớp</div>
                                <div class="class-stat-value">{{ $classmateCount }}</div>
                                <div class="small text-muted mt-2">học viên đang theo học</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-3">
                        <div class="card class-stat-card class-nav">
                            <div class="card-body p-2">
                                <a href="#overview"><i class="fas fa-info-circle"></i>Tổng quan</a>
                                <a href="#materials"><i class="fas fa-folder-open"></i>Tài liệu</a>
                                <a href="#attendance"><i class="fas fa-user-check"></i>Điểm danh</a>
                                <a href="#schedule"><i class="fas fa-calendar-alt"></i>Lịch buổi học</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9 d-flex flex-column gap-4">
                        <section class="class-section" id="overview">
                            <div class="class-section__head">
                                <h2 class="h5 fw-bold mb-0">Thông tin lớp học</h2>
                            </div>
                            <div class="class-section__body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Cơ sở</div>
                                        <div class="fw-semibold">{{ $lopHoc->coSo?->tenCoSo ?? 'Chưa cập nhật' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Phòng học chính</div>
                                        <div class="fw-semibold">{{ $primaryRoom?->tenPhongHoc ?? 'Theo từng buổi học' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Ca học mặc định</div>
                                        <div class="fw-semibold">
                                            @if($primaryShift)
                                                {{ $primaryShift->tenCaHoc ?? 'Ca học' }}: {{ $time($primaryShift->gioBatDau) }} - {{ $time($primaryShift->gioKetThuc) }}
                                            @else
                                                Theo từng buổi học
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Ngày đăng ký</div>
                                        <div class="fw-semibold">{{ $date($registration->ngayDangKy) }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Học phí phải thu</div>
                                        <div class="fw-semibold">{{ number_format($registration->hocPhiTongTien, 0, ',', '.') }}đ</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Còn nợ</div>
                                        <div class="fw-semibold {{ $registration->tongConNo > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($registration->tongConNo, 0, ',', '.') }}đ
                                        </div>
                                    </div>
                                </div>

                                @if($nextSessions->isNotEmpty())
                                    <div class="mt-4">
                                        <h3 class="h6 fw-bold mb-3">Buổi học sắp tới</h3>
                                        <div class="d-flex flex-column gap-3">
                                            @foreach($nextSessions as $session)
                                                <div class="timeline-row">
                                                    <div class="fw-semibold">{{ $session->tenBuoiHoc ?? 'Buổi học #' . $session->buoiHocId }}</div>
                                                    <div class="text-muted small">
                                                        {{ $date($session->ngayHoc) }}
                                                        @if($session->caHoc)
                                                            <span class="mx-1">|</span>{{ $time($session->caHoc->gioBatDau) }} - {{ $time($session->caHoc->gioKetThuc) }}
                                                        @endif
                                                        @if($session->phongHoc)
                                                            <span class="mx-1">|</span>{{ $session->phongHoc->tenPhongHoc }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="class-section" id="materials">
                            <div class="class-section__head d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div>
                                    <h2 class="h5 fw-bold mb-1">Tài liệu lớp học</h2>
                                    <div class="text-muted small">Tài liệu được nhóm theo từng đợt giáo viên gửi.</div>
                                </div>
                                @if($canAccessMaterials)
                                    <a href="{{ route('home.student.classes.materials.index', $lopHoc->lopHocId) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        Xem trang tài liệu
                                    </a>
                                @endif
                            </div>
                            <div class="class-section__body">
                                @if(!$canAccessMaterials)
                                    <div class="empty-panel">
                                        <i class="fas fa-lock fa-2x mb-2"></i>
                                        <div class="fw-semibold">Tài liệu sẽ mở sau khi đăng ký của bạn đủ điều kiện truy cập.</div>
                                    </div>
                                @elseif($taiLieuGroups->isEmpty())
                                    <div class="empty-panel">
                                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                                        <div class="fw-semibold">Chưa có tài liệu nào được chia sẻ</div>
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($taiLieuGroups as $group)
                                            <div class="material-batch">
                                                <div class="material-batch__head">
                                                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                                                        <div>
                                                            <h3 class="h6 fw-bold mb-1">{{ $group->title }}</h3>
                                                            <div class="text-muted small">
                                                                <i class="far fa-clock me-1"></i>{{ $group->sent_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian gửi' }}
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-white text-primary rounded-pill px-3 py-2">{{ $group->count }} tài liệu</span>
                                                    </div>
                                                </div>
                                                <div class="p-3">
                                                    <div class="row g-3">
                                                        @foreach($group->items as $tl)
                                                            <div class="col-md-6">
                                                                <div class="material-card">
                                                                    <div class="d-flex gap-3">
                                                                        <div class="text-primary fs-4"><i class="fas {{ $tl->mime_icon }}"></i></div>
                                                                        <div class="min-w-0 flex-grow-1">
                                                                            <div class="fw-semibold text-truncate" title="{{ $tl->tieuDe }}">{{ $tl->tieuDe }}</div>
                                                                            @if($tl->moTa)
                                                                                <div class="small text-muted mt-1">{{ $tl->moTa }}</div>
                                                                            @endif
                                                                            <div class="d-flex align-items-center gap-2 flex-wrap mt-2 small text-muted">
                                                                                <span>{{ $tl->kich_thuoc_readable }}</span>
                                                                                <span>|</span>
                                                                                <span>{{ $tl->nhom_label }}</span>
                                                                            </div>
                                                                            <a href="{{ route('home.student.classes.materials.download', [$lopHoc->lopHocId, $tl->lopHocTaiLieuId]) }}"
                                                                               class="btn btn-sm btn-primary rounded-pill mt-3">
                                                                                <i class="fas fa-download me-1"></i>Tải xuống
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="class-section" id="attendance">
                            <div class="class-section__head">
                                <h2 class="h5 fw-bold mb-1">Dữ liệu điểm danh</h2>
                                <div class="text-muted small">Theo dõi số buổi có mặt, vắng và trạng thái điểm danh từng buổi.</div>
                            </div>
                            <div class="class-section__body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-4 bg-success-subtle">
                                            <div class="small text-success-emphasis">Có mặt</div>
                                            <div class="h4 fw-bold mb-0 text-success">{{ $attendanceSummary['present'] }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-4 bg-danger-subtle">
                                            <div class="small text-danger">Vắng</div>
                                            <div class="h4 fw-bold mb-0 text-danger">{{ $attendanceSummary['absent'] }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-4 bg-secondary-subtle">
                                            <div class="small text-secondary">Bị khóa / chưa ghi nhận</div>
                                            <div class="h4 fw-bold mb-0 text-secondary">{{ $attendanceSummary['locked'] + $attendanceSummary['missing'] }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if($sessions->isEmpty())
                                    <div class="empty-panel">Chưa có buổi học nào trong lớp.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Buổi học</th>
                                                    <th>Ngày học</th>
                                                    <th>Ca học</th>
                                                    <th>Điểm danh</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sessions as $session)
                                                    @php
                                                        $attendance = $attendanceRecords->get($session->buoiHocId);
                                                        $badge = $attendanceBadge($attendance);
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-semibold">{{ $session->tenBuoiHoc ?? 'Buổi #' . $loop->iteration }}</td>
                                                        <td>{{ $date($session->ngayHoc) }}</td>
                                                        <td>
                                                            @if($session->caHoc)
                                                                {{ $time($session->caHoc->gioBatDau) }} - {{ $time($session->caHoc->gioKetThuc) }}
                                                            @else
                                                                --
                                                            @endif
                                                        </td>
                                                        <td><span class="attendance-pill {{ $badge['class'] }}">{{ $badge['label'] }}</span></td>
                                                        <td class="text-muted small">{{ $attendance?->ghiChu ?: $session->ghiChu ?: '--' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="class-section" id="schedule">
                            <div class="class-section__head">
                                <h2 class="h5 fw-bold mb-1">Lịch buổi học</h2>
                                <div class="text-muted small">Danh sách toàn bộ buổi học đã được xếp lịch cho lớp.</div>
                            </div>
                            <div class="class-section__body">
                                @if($sessions->isEmpty())
                                    <div class="empty-panel">Lớp chưa có lịch buổi học.</div>
                                @else
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($sessions as $session)
                                            <div class="border rounded-4 p-3">
                                                <div class="d-flex justify-content-between gap-3 flex-wrap">
                                                    <div>
                                                        <div class="fw-bold">{{ $session->tenBuoiHoc ?? 'Buổi học #' . $session->buoiHocId }}</div>
                                                        <div class="text-muted small mt-1">
                                                            {{ $date($session->ngayHoc) }}
                                                            @if($session->caHoc)
                                                                <span class="mx-1">|</span>{{ $time($session->caHoc->gioBatDau) }} - {{ $time($session->caHoc->gioKetThuc) }}
                                                            @endif
                                                            @if($session->phongHoc)
                                                                <span class="mx-1">|</span>{{ $session->phongHoc->tenPhongHoc }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                        <i class="fas {{ $session->trangThaiIcon }} me-1"></i>{{ $session->trangThaiLabel }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
