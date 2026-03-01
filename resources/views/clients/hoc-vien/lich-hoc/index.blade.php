@extends('layouts.client')
@section('title', 'Lịch học theo tuần')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/lich-hoc.css') }}">
@endsection

@section('content')
    @php
        /**
         * Mapping trangThai BuoiHoc:
         * 0 = Lý thuyết (mặc định)
         * 1 = Thực hành
         * 2 = Trực tuyến
         * 3 = Lịch thi
         * 4 = Tạm ngưng
         */
        $typeMap = [
            0 => ['class' => 'ly-thuyet', 'label' => '<i class="fas fa-book-open"></i> Lý thuyết'],
            1 => ['class' => 'thuc-hanh', 'label' => '<i class="fas fa-flask"></i> Thực hành'],
            2 => ['class' => 'truc-tuyen', 'label' => '<i class="fas fa-wifi"></i> Trực tuyến'],
            3 => ['class' => 'lich-thi', 'label' => '<i class="fas fa-pencil-alt"></i> Lịch thi'],
            4 => ['class' => 'tam-ngung', 'label' => '<i class="fas fa-pause-circle"></i> Tạm ngưng'],
        ];

        $today = \Carbon\Carbon::today()->toDateString();

        // Kiểm tra tuần có buổi học không
        $hasSessions = false;
        foreach ($weekDays as $wd) {
            if (!empty($schedule[$wd['thu']])) {
                $hasSessions = true;
                break;
            }
        }
    @endphp

    <section class="account-page">
        <div class="custom-container">
            <div class="row g-4">
                {{-- SIDEBAR --}}
                @include('components.client.account-sidebar')

                {{-- MAIN CONTENT --}}
                <div class="col-lg-9">
                    <div class="account-content">

                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Lịch học'],
                        ]" />

                        {{-- TOOLBAR --}}
                        <div class="schedule-toolbar">
                            <div class="toolbar-title">
                                <i class="far fa-calendar-alt me-2" style="color:#3b7dd8"></i>
                                Lịch học tuần
                                <span>{{ $startOfWeek->format('d/m') }} – {{ $endOfWeek->format('d/m/Y') }}</span>
                            </div>

                            <div class="toolbar-nav">
                                <button id="btn-prev-week" class="btn-nav btn-prev">
                                    <i class="fas fa-chevron-left"></i> Tuần trước
                                </button>
                                <button id="btn-today" class="btn-nav btn-today">
                                    <i class="fas fa-dot-circle"></i> Hôm nay
                                </button>
                                <button id="btn-next-week" class="btn-nav btn-next">
                                    Tuần tiếp <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            {{-- Hidden input lưu baseDate để JS đọc --}}
                            <input type="hidden" id="base-date" value="{{ $baseDate->toDateString() }}">
                        </div>

                        {{-- BẢNG THỜI KHÓA BIỂU --}}
                        <div class="schedule-table-wrapper">
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th class="col-ca">Ca học</th>
                                        @foreach ($weekDays as $wd)
                                            <th data-date="{{ $wd['date']->toDateString() }}">
                                                <span class="day-label">{{ $wd['label'] }}</span>
                                                <span class="day-date">{{ $wd['date']->format('d/m') }}</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($caHocs as $ca)
                                        <tr>
                                            {{-- Nhãn ca học --}}
                                            <td class="col-ca-label">
                                                <span class="ca-label-badge">{{ $ca->tenCa }}</span>
                                                <span class="ca-time-label">
                                                    {{ \Carbon\Carbon::parse($ca->gioBatDau)->format('H:i') }}
                                                    –
                                                    {{ \Carbon\Carbon::parse($ca->gioKetThuc)->format('H:i') }}
                                                </span>
                                            </td>

                                            {{-- 7 ô ngày --}}
                                            @foreach ($weekDays as $wd)
                                                <td>
                                                    @if (!empty($schedule[$wd['thu']][$ca->caHocId]))
                                                        @foreach ($schedule[$wd['thu']][$ca->caHocId] as $buoi)
                                                            @php
                                                                $ts = $buoi->trangThai ?? 0;
                                                                $typeKey = array_key_exists($ts, $typeMap) ? $ts : 0;
                                                                $typeCls = $typeMap[$typeKey]['class'];
                                                                $typeLbl = $typeMap[$typeKey]['label'];

                                                                $lop = $buoi->lopHoc;
                                                                $khoaHoc = $lop->khoaHoc ?? null;
                                                                $gv = $lop->taiKhoan->hoSoNguoiDung ?? null;
                                                                $phong = $buoi->phongHoc;
                                                                $coSo = $lop->coSo ?? null;
                                                            @endphp

                                                            <div class="lesson-card {{ $typeCls }}"
                                                                data-ten-lop="{{ $lop->tenLopHoc ?? '' }}"
                                                                data-khoa-hoc="{{ $khoaHoc->tenKhoaHoc ?? '' }}"
                                                                data-ngay-hoc="{{ \Carbon\Carbon::parse($buoi->ngayHoc)->format('d/m/Y') }} ({{ $wd['label'] }})"
                                                                data-ca-hoc="{{ $ca->tenCa }} ({{ \Carbon\Carbon::parse($ca->gioBatDau)->format('H:i') }} – {{ \Carbon\Carbon::parse($ca->gioKetThuc)->format('H:i') }})"
                                                                data-phong="{{ $phong->tenPhong ?? 'Chưa xếp phòng' }}"
                                                                data-giao-vien="{{ $gv->hoTen ?? 'Chưa có GV' }}"
                                                                data-co-so="{{ $coSo->tenCoSo ?? '' }}"
                                                                data-ghi-chu="{{ $buoi->ghiChu ?? '' }}"
                                                                data-da-hoan-thanh="{{ $buoi->daHoanThanh ? '1' : '0' }}"
                                                                data-type-class="{{ $typeCls }}"
                                                                data-type-label="{{ strip_tags($typeLbl) }}"
                                                                title="Click để xem chi tiết">

                                                                <div class="lesson-title">
                                                                    {{ $lop->tenLopHoc ?? 'N/A' }}
                                                                </div>
                                                                <div class="lesson-meta">
                                                                    @if ($khoaHoc)
                                                                        <span><i
                                                                                class="fas fa-book"></i>{{ Str::limit($khoaHoc->tenKhoaHoc, 20) }}</span>
                                                                    @endif
                                                                    @if ($phong)
                                                                        <span><i
                                                                                class="fas fa-door-open"></i>{{ $phong->tenPhong }}</span>
                                                                    @endif
                                                                    @if ($gv)
                                                                        <span><i
                                                                                class="fas fa-chalkboard-teacher"></i>{{ Str::limit($gv->hoTen, 18) }}</span>
                                                                    @endif
                                                                    @if ($coSo)
                                                                        <span><i
                                                                                class="fas fa-map-marker-alt"></i>{{ Str::limit($coSo->tenCoSo, 20) }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="cell-empty"></div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="no-schedule">
                                                <i class="far fa-calendar-times"></i>
                                                <p>Chưa có ca học nào được thiết lập trong hệ thống.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- THÔNG BÁO TUẦN TRỐNG --}}
                        @if ($caHocs->count() > 0 && !$hasSessions)
                            <div class="no-schedule"
                                style="margin-top:16px;background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);">
                                <i class="far fa-calendar-check"></i>
                                <p>Bạn không có buổi học nào trong tuần này.<br>
                                    <a href="{{ route('home.student.schedule') }}" style="color:#3b7dd8;font-weight:600;">
                                        <i class="fas fa-dot-circle"></i> Quay về tuần hiện tại
                                    </a>
                                </p>
                            </div>
                        @endif

                        {{-- LEGEND --}}
                        <div class="schedule-legend">
                            <span class="legend-item">
                                <span class="legend-dot ly-thuyet"></span> Lịch học lý thuyết
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot thuc-hanh"></span> Lịch học thực hành
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot truc-tuyen"></span> Lịch học trực tuyến
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot lich-thi"></span> Lịch thi
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot tam-ngung"></span> Lịch tạm ngưng
                            </span>
                        </div>

                    </div>{{-- /account-content --}}
                </div>{{-- /col-lg-9 --}}
            </div>{{-- /row --}}
        </div>{{-- /custom-container --}}
    </section>

    {{-- MODAL CHI TIẾT BUỔI HỌC --}}
    <div class="modal fade lesson-modal" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lessonModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Chi tiết buổi học
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Badge loại lớp --}}
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <span id="modal-type-badge" class="modal-badge"></span>
                        <span id="modal-status" class="small"></span>
                    </div>
                    <hr class="modal-divider">

                    <div class="modal-detail-grid">
                        <div class="modal-detail-item">
                            <span class="detail-label">Tên lớp học</span>
                            <span class="detail-value"><i class="fas fa-graduation-cap"></i><span
                                    id="modal-ten-lop"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Khóa học</span>
                            <span class="detail-value"><i class="fas fa-book"></i><span
                                    id="modal-khoa-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Ngày học</span>
                            <span class="detail-value"><i class="fas fa-calendar-day"></i><span
                                    id="modal-ngay-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Ca học & Giờ</span>
                            <span class="detail-value"><i class="far fa-clock"></i><span id="modal-ca-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Phòng học</span>
                            <span class="detail-value"><i class="fas fa-door-open"></i><span
                                    id="modal-phong-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Giáo viên</span>
                            <span class="detail-value"><i class="fas fa-chalkboard-teacher"></i><span
                                    id="modal-giao-vien"></span></span>
                        </div>
                        <div class="modal-detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Cơ sở đào tạo</span>
                            <span class="detail-value"><i class="fas fa-map-marker-alt"></i><span
                                    id="modal-co-so"></span></span>
                        </div>
                        <div class="modal-detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Ghi chú</span>
                            <span class="detail-value" style="color:#718096;font-weight:400;" id="modal-ghi-chu"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/client/js/lich-hoc.js') }}"></script>
@endsection
