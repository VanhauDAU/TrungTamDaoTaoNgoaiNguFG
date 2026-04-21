@extends('layouts.internal')

@section('title', 'Lịch dạy theo tuần')
@section('page-title', 'Lịch dạy')
@section('breadcrumb', 'Thời khóa biểu giảng dạy')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/teacher/css/lich-day.css') }}">
@endsection

@section('content')
    @php
        $today = \Carbon\Carbon::today()->toDateString();

        $hasSessions = false;
        foreach ($weekDays as $wd) {
            if (!empty($schedule[$wd['thu']])) {
                $hasSessions = true;
                break;
            }
        }
    @endphp

    {{-- Input ẩn để JS đọc baseDate --}}
    <input type="hidden" id="base-date" value="{{ $baseDate->toDateString() }}">

    {{-- ─── TOOLBAR ─────────────────────────────────────────── --}}
    <div class="ld-toolbar">
        <div class="ld-toolbar__title">
            <i class="fas fa-chalkboard-teacher" style="color:#3b7dd8"></i>
            Lịch dạy tuần
            <span class="week-range">
                {{ $startOfWeek->format('d/m') }} – {{ $endOfWeek->format('d/m/Y') }}
            </span>
        </div>

        <div class="ld-toolbar__nav">
            <button id="btn-prev-week" class="ld-btn-nav btn-prev">
                <i class="fas fa-chevron-left"></i> Tuần trước
            </button>
            <button id="btn-today" class="ld-btn-nav btn-today">
                <i class="fas fa-dot-circle"></i> Hôm nay
            </button>
            <button id="btn-next-week" class="ld-btn-nav btn-next">
                Tuần tiếp <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- ─── PROPOSAL BAR ───────────────────────────────────────── --}}
    <div class="ld-proposal-bar">
        <span class="proposal-label">
            <i class="fas fa-paper-plane me-1"></i> Đề xuất nhanh:
        </span>
        <button class="ld-proposal-btn btn-compensation" id="btn-global-bu" disabled
            title="Chọn một buổi học để đề xuất dạy bù">
            <i class="fas fa-calendar-plus"></i> Đề xuất dạy bù
        </button>
        <button class="ld-proposal-btn btn-suspend" id="btn-global-ngung" disabled
            title="Chọn một buổi học để đề xuất tạm ngưng">
            <i class="fas fa-pause-circle"></i> Đề xuất tạm ngưng
        </button>
        <button class="ld-proposal-btn btn-reschedule" id="btn-global-doi-lich" disabled
            title="Chọn một buổi học để đề xuất đổi lịch">
            <i class="fas fa-calendar-days"></i> Đề xuất đổi lịch
        </button>
        <small class="text-muted ms-auto" id="proposal-hint">
            <i class="fas fa-info-circle me-1"></i>Nhấp vào ô buổi dạy để chọn, sau đó sử dụng các nút bên trái.
        </small>
    </div>

    {{-- ─── BẢNG THỜI KHÓA BIỂU ───────────────────────────────── --}}
    <div class="ld-table-wrapper">
        <table class="ld-table">
            <thead>
                <tr>
                    <th class="col-ca">Ca học</th>
                    @foreach ($weekDays as $wd)
                        <th data-date="{{ $wd['date']->toDateString() }}"
                            class="{{ $wd['date']->toDateString() === $today ? 'today' : '' }}">
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
                                            $lop     = $buoi->lopHoc;
                                            $khoaHoc = $lop?->khoaHoc;
                                            $phong   = $buoi->phongHoc;
                                            $coSo    = $lop?->coSo;
                                        @endphp

                                        <div class="lesson-card {{ $buoi->trangThaiKey }}"
                                            data-buoi-id="{{ $buoi->buoiHocId }}"
                                            data-ten-lop="{{ $lop?->tenLopHoc ?? '' }}"
                                            data-khoa-hoc="{{ $khoaHoc?->tenKhoaHoc ?? '' }}"
                                            data-ngay-hoc="{{ \Carbon\Carbon::parse($buoi->ngayHoc)->format('d/m/Y') }} ({{ $wd['label'] }})"
                                            data-ca-hoc="{{ $ca->tenCa }} ({{ \Carbon\Carbon::parse($ca->gioBatDau)->format('H:i') }} – {{ \Carbon\Carbon::parse($ca->gioKetThuc)->format('H:i') }})"
                                            data-phong="{{ $phong?->tenPhong ?? 'Chưa xếp phòng' }}"
                                            data-co-so="{{ $coSo?->tenCoSo ?? '' }}"
                                            data-ghi-chu="{{ $buoi->ghiChu ?? '' }}"
                                            data-status-key="{{ $buoi->trangThaiKey }}"
                                            data-status-label="{{ $buoi->trangThaiLabel }}"
                                            data-da-hoan-thanh="{{ $buoi->daHoanThanh ? '1' : '0' }}"
                                            data-da-diem-danh="{{ $buoi->daDiemDanh ? '1' : '0' }}"
                                            title="Nhấp để xem chi tiết & đề xuất">

                                            <div class="lesson-title">
                                                {{ $lop?->tenLopHoc ?? 'N/A' }}
                                            </div>
                                            <div class="lesson-meta">
                                                @if ($khoaHoc)
                                                    <span><i class="fas fa-book"></i>{{ Str::limit($khoaHoc->tenKhoaHoc, 22) }}</span>
                                                @endif
                                                @if ($phong)
                                                    <span><i class="fas fa-door-open"></i>{{ $phong->tenPhong }}</span>
                                                @endif
                                                @if ($coSo)
                                                    <span><i class="fas fa-map-marker-alt"></i>{{ Str::limit($coSo->tenCoSo, 20) }}</span>
                                                @endif
                                            </div>

                                            {{-- Quick actions --}}
                                            <div class="lesson-actions">
                                                <button class="lesson-action-btn btn-detail"
                                                    data-action="detail" data-buoi-id="{{ $buoi->buoiHocId }}">
                                                    <i class="fas fa-info"></i> Chi tiết
                                                </button>
                                                @if (!$buoi->daHoanThanh && $buoi->trangThaiKey !== 'da-huy')
                                                    <button class="lesson-action-btn btn-bu"
                                                        data-action="bu" data-buoi-id="{{ $buoi->buoiHocId }}">
                                                        <i class="fas fa-calendar-plus"></i> Dạy bù
                                                    </button>
                                                    <button class="lesson-action-btn btn-ngung"
                                                        data-action="ngung" data-buoi-id="{{ $buoi->buoiHocId }}">
                                                        <i class="fas fa-pause"></i> Tạm ngưng
                                                    </button>
                                                    <button class="lesson-action-btn btn-doi-lich"
                                                        data-action="doi-lich" data-buoi-id="{{ $buoi->buoiHocId }}">
                                                        <i class="fas fa-calendar-days"></i> Đổi lịch
                                                    </button>
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
                        <td colspan="8" class="ld-empty">
                            <i class="far fa-calendar-times"></i>
                            <p>Chưa có ca học nào được thiết lập trong hệ thống.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tuần trống --}}
    @if ($caHocs->count() > 0 && !$hasSessions)
        <div class="ld-empty mt-3" style="background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);">
            <i class="far fa-calendar-check"></i>
            <p>Bạn không có buổi dạy nào trong tuần này.<br>
                <a href="{{ route('teacher.schedule.index') }}" style="color:#3b7dd8;font-weight:600;">
                    <i class="fas fa-dot-circle"></i> Quay về tuần hiện tại
                </a>
            </p>
        </div>
    @endif

    {{-- Legend --}}
    <div class="ld-legend">
        <span class="legend-item"><span class="legend-dot sap-dien-ra"></span> Sắp diễn ra</span>
        <span class="legend-item"><span class="legend-dot dang-dien-ra"></span> Đang diễn ra</span>
        <span class="legend-item"><span class="legend-dot da-hoan-thanh"></span> Đã hoàn thành</span>
        <span class="legend-item"><span class="legend-dot da-huy"></span> Đã hủy</span>
        <span class="legend-item"><span class="legend-dot doi-lich"></span> Đổi lịch</span>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ MODAL CHI TIẾT ━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="modal fade ld-modal" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">
                        <i class="fas fa-info-circle me-2"></i>Chi tiết buổi dạy
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <span id="detail-status-badge" class="modal-badge"></span>
                        <span id="detail-diem-danh" class="small text-muted"></span>
                    </div>
                    <hr class="modal-divider">
                    <div class="modal-detail-grid">
                        <div class="modal-detail-item">
                            <span class="detail-label">Lớp học</span>
                            <span class="detail-value"><i class="fas fa-graduation-cap"></i><span id="detail-ten-lop"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Khóa học</span>
                            <span class="detail-value"><i class="fas fa-book"></i><span id="detail-khoa-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Ngày học</span>
                            <span class="detail-value"><i class="fas fa-calendar-day"></i><span id="detail-ngay-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Ca học & Giờ</span>
                            <span class="detail-value"><i class="far fa-clock"></i><span id="detail-ca-hoc"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Phòng học</span>
                            <span class="detail-value"><i class="fas fa-door-open"></i><span id="detail-phong"></span></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Cơ sở đào tạo</span>
                            <span class="detail-value"><i class="fas fa-map-marker-alt"></i><span id="detail-co-so"></span></span>
                        </div>
                        <div class="modal-detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Ghi chú</span>
                            <span class="detail-value" style="font-weight:400;color:#718096" id="detail-ghi-chu"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" id="detail-action-area">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="detail-btn-bu"
                        style="display:none">
                        <i class="fas fa-calendar-plus me-1"></i>Đề xuất dạy bù
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="detail-btn-ngung"
                        style="display:none">
                        <i class="fas fa-pause-circle me-1"></i>Tạm ngưng
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="detail-btn-doi-lich"
                        style="display:none">
                        <i class="fas fa-calendar-days me-1"></i>Đổi lịch
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ MODAL DẠY BÙ ━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="modal fade ld-modal" id="modalDayBu" tabindex="-1" aria-labelledby="modalDayBuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDayBuLabel">
                        <i class="fas fa-calendar-plus me-2"></i>Đề xuất dạy bù
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Đề xuất sẽ được gửi đến quản lý và chờ phê duyệt.
                        Lớp: <strong id="bu-ten-lop">–</strong>
                    </p>
                    <form id="form-day-bu">
                        @csrf
                        <input type="hidden" id="bu-buoi-id" name="buoi_id" value="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ngày dạy bù (dự kiến)</label>
                            <input type="date" class="form-control" id="bu-ngay-bu" name="ngay_bu"
                                min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lý do <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="bu-ly-do" name="ly_do" rows="3"
                                placeholder="Mô tả lý do cần dạy bù..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-submit-bu">
                        <i class="fas fa-paper-plane me-1"></i>Gửi đề xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ MODAL TẠM NGƯNG ━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="modal fade ld-modal" id="modalTamNgung" tabindex="-1" aria-labelledby="modalTamNgungLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header--orange">
                    <h5 class="modal-title" id="modalTamNgungLabel">
                        <i class="fas fa-pause-circle me-2"></i>Đề xuất tạm ngưng buổi học
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex gap-2 align-items-start" role="alert">
                        <i class="fas fa-triangle-exclamation mt-1"></i>
                        <span>Đề xuất tạm ngưng sẽ được chuyển đến quản lý để xem xét. Buổi học chưa bị hủy ngay lập tức.</span>
                    </div>
                    <p class="small text-muted mb-3">Lớp: <strong id="ngung-ten-lop">–</strong></p>
                    <form id="form-tam-ngung">
                        @csrf
                        <input type="hidden" id="ngung-buoi-id" name="buoi_id" value="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lý do tạm ngưng <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="ngung-ly-do" name="ly_do" rows="3"
                                placeholder="Ví dụ: Giáo viên bận đột xuất, học viên vắng nhiều..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-warning btn-sm text-white" id="btn-submit-ngung">
                        <i class="fas fa-paper-plane me-1"></i>Gửi đề xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━ MODAL ĐỔI LỊCH ━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="modal fade ld-modal" id="modalDoiLich" tabindex="-1" aria-labelledby="modalDoiLichLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header--green">
                    <h5 class="modal-title" id="modalDoiLichLabel">
                        <i class="fas fa-calendar-days me-2"></i>Đề xuất đổi lịch
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Lớp: <strong id="doi-ten-lop">–</strong></p>
                    <form id="form-doi-lich">
                        @csrf
                        <input type="hidden" id="doi-buoi-id" name="buoi_id" value="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ngày mới (dự kiến) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="doi-ngay-moi" name="ngay_moi"
                                min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lý do đổi lịch <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="doi-ly-do" name="ly_do" rows="3"
                                placeholder="Mô tả lý do cần đổi lịch..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-submit-doi-lich">
                        <i class="fas fa-paper-plane me-1"></i>Gửi đề xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
(() => {
    'use strict';

    /* ── ROUTES ─────────────────────────────────────────── */
    const ROUTE_INDEX       = '{{ route("teacher.schedule.index") }}';
    const CSRF              = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── WEEK NAVIGATION ────────────────────────────────── */
    const baseDate = new Date('{{ $baseDate->toDateString() }}');

    function navigateTo(date) {
        const iso = date.toISOString().slice(0,10);
        window.location.href = ROUTE_INDEX + '?week=' + iso;
    }

    document.getElementById('btn-prev-week')?.addEventListener('click', () => {
        const d = new Date(baseDate);
        d.setDate(d.getDate() - 7);
        navigateTo(d);
    });

    document.getElementById('btn-next-week')?.addEventListener('click', () => {
        const d = new Date(baseDate);
        d.setDate(d.getDate() + 7);
        navigateTo(d);
    });

    document.getElementById('btn-today')?.addEventListener('click', () => {
        window.location.href = ROUTE_INDEX;
    });

    /* ── HIGHLIGHT TODAY COLUMN ─────────────────────────── */
    const todayStr = new Date().toISOString().slice(0, 10);
    document.querySelectorAll('.ld-table thead th[data-date]').forEach(th => {
        if (th.dataset.date === todayStr) th.classList.add('today');
    });

    /* ── SELECTED CARD STATE ────────────────────────────── */
    let selectedCard = null;

    function selectCard(card) {
        if (selectedCard) selectedCard.style.outline = '';
        selectedCard = card;
        if (card) {
            card.style.outline = '3px solid #3b7dd8';
            card.style.outlineOffset = '2px';
        }
        const hasCard = !!card;
        ['btn-global-bu','btn-global-ngung','btn-global-doi-lich'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.disabled = !hasCard;
        });
    }

    /* ── LESSON CARD CLICK ──────────────────────────────── */
    document.querySelectorAll('.lesson-card').forEach(card => {
        card.addEventListener('click', (e) => {
            // Nếu click vào action button, không mở modal detail
            if (e.target.closest('.lesson-action-btn')) return;
            selectCard(card);
            openDetailModal(card);
        });
    });

    /* Lessons action buttons (inside card) */
    document.querySelectorAll('.lesson-action-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.lesson-card');
            selectCard(card);
            const action = btn.dataset.action;
            if (action === 'detail') openDetailModal(card);
            else if (action === 'bu')       openBuModal(card);
            else if (action === 'ngung')    openNgungModal(card);
            else if (action === 'doi-lich') openDoiLichModal(card);
        });
    });

    /* Global proposal buttons */
    document.getElementById('btn-global-bu')?.addEventListener('click', () => {
        if (selectedCard) openBuModal(selectedCard);
    });
    document.getElementById('btn-global-ngung')?.addEventListener('click', () => {
        if (selectedCard) openNgungModal(selectedCard);
    });
    document.getElementById('btn-global-doi-lich')?.addEventListener('click', () => {
        if (selectedCard) openDoiLichModal(selectedCard);
    });

    /* ── DETAIL MODAL ───────────────────────────────────── */
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));

    function openDetailModal(card) {
        const d = card.dataset;
        document.getElementById('detail-ten-lop').textContent  = d.tenLop  || '—';
        document.getElementById('detail-khoa-hoc').textContent = d.khoaHoc || '—';
        document.getElementById('detail-ngay-hoc').textContent = d.ngayHoc || '—';
        document.getElementById('detail-ca-hoc').textContent   = d.caHoc   || '—';
        document.getElementById('detail-phong').textContent    = d.phong   || '—';
        document.getElementById('detail-co-so').textContent    = d.coSo    || '—';
        document.getElementById('detail-ghi-chu').textContent  = d.ghiChu  || 'Không có ghi chú.';

        const badge = document.getElementById('detail-status-badge');
        badge.textContent = d.statusLabel;
        badge.className   = 'modal-badge ' + (d.statusKey || '');

        const dimDanh = d.daDiemDanh === '1';
        document.getElementById('detail-diem-danh').textContent = dimDanh
            ? '✅ Đã điểm danh' : '⏳ Chưa điểm danh';

        const isActionable = d.daHoanThanh !== '1' && d.statusKey !== 'da-huy';
        const buId = d.buoiId;

        const btnBu      = document.getElementById('detail-btn-bu');
        const btnNgung   = document.getElementById('detail-btn-ngung');
        const btnDoiLich = document.getElementById('detail-btn-doi-lich');

        [btnBu, btnNgung, btnDoiLich].forEach(b => b.style.display = isActionable ? 'inline-flex' : 'none');

        btnBu.onclick      = () => { modalDetail.hide(); openBuModal(card); };
        btnNgung.onclick   = () => { modalDetail.hide(); openNgungModal(card); };
        btnDoiLich.onclick = () => { modalDetail.hide(); openDoiLichModal(card); };

        modalDetail.show();
    }

    /* ── DẠY BÙ MODAL ───────────────────────────────────── */
    const modalDayBu = new bootstrap.Modal(document.getElementById('modalDayBu'));

    function openBuModal(card) {
        document.getElementById('bu-ten-lop').textContent = card.dataset.tenLop || '—';
        document.getElementById('bu-buoi-id').value       = card.dataset.buoiId;
        document.getElementById('bu-ly-do').value         = '';
        document.getElementById('bu-ngay-bu').value       = '';
        modalDayBu.show();
    }

    document.getElementById('btn-submit-bu')?.addEventListener('click', async () => {
        await submitProposal(
            '{{ route("teacher.schedule.propose.compensation", ["buoiHocId" => "__ID__"]) }}',
            document.getElementById('bu-buoi-id').value,
            {
                ly_do:   document.getElementById('bu-ly-do').value,
                ngay_bu: document.getElementById('bu-ngay-bu').value,
            },
            modalDayBu
        );
    });

    /* ── TẠM NGƯNG MODAL ────────────────────────────────── */
    const modalTamNgung = new bootstrap.Modal(document.getElementById('modalTamNgung'));

    function openNgungModal(card) {
        document.getElementById('ngung-ten-lop').textContent = card.dataset.tenLop || '—';
        document.getElementById('ngung-buoi-id').value       = card.dataset.buoiId;
        document.getElementById('ngung-ly-do').value         = '';
        modalTamNgung.show();
    }

    document.getElementById('btn-submit-ngung')?.addEventListener('click', async () => {
        await submitProposal(
            '{{ route("teacher.schedule.propose.suspension", ["buoiHocId" => "__ID__"]) }}',
            document.getElementById('ngung-buoi-id').value,
            { ly_do: document.getElementById('ngung-ly-do').value },
            modalTamNgung
        );
    });

    /* ── ĐỔI LỊCH MODAL ─────────────────────────────────── */
    const modalDoiLich = new bootstrap.Modal(document.getElementById('modalDoiLich'));

    function openDoiLichModal(card) {
        document.getElementById('doi-ten-lop').textContent = card.dataset.tenLop || '—';
        document.getElementById('doi-buoi-id').value       = card.dataset.buoiId;
        document.getElementById('doi-ly-do').value         = '';
        document.getElementById('doi-ngay-moi').value      = '';
        modalDoiLich.show();
    }

    document.getElementById('btn-submit-doi-lich')?.addEventListener('click', async () => {
        await submitProposal(
            '{{ route("teacher.schedule.propose.reschedule", ["buoiHocId" => "__ID__"]) }}',
            document.getElementById('doi-buoi-id').value,
            {
                ly_do:    document.getElementById('doi-ly-do').value,
                ngay_moi: document.getElementById('doi-ngay-moi').value,
            },
            modalDoiLich
        );
    });

    /* ── SUBMIT HELPER ──────────────────────────────────── */
    async function submitProposal(routeTemplate, buoiId, payload, modal) {
        if (!buoiId) return;
        if (!payload.ly_do?.trim()) {
            alert('Vui lòng nhập lý do đề xuất.');
            return;
        }

        const url = routeTemplate.replace('__ID__', buoiId);

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (data.success) {
                modal.hide();
                showToast(data.message, 'success');
            } else {
                showToast(data.message ?? 'Đã xảy ra lỗi. Vui lòng thử lại.', 'danger');
            }
        } catch (err) {
            showToast('Không thể kết nối đến máy chủ.', 'danger');
        }
    }

    /* ── TOAST ──────────────────────────────────────────── */
    function showToast(message, type = 'success') {
        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"
                role="alert" aria-live="assertive" style="min-width:260px;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Đóng"></button>
                </div>
            </div>`;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('beforeend', html);

        setTimeout(() => {
            document.getElementById(id)?.remove();
        }, 4500);
    }
})();
</script>
@endsection
