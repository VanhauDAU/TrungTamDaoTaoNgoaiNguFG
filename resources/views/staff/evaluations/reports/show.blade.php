@extends('layouts.internal')

@section('title', 'Duyệt báo cáo')
@section('page-title', 'Duyệt báo cáo')
@section('breadcrumb', 'Nhân viên · Xem và duyệt báo cáo')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Review Detail</span>
                        <h2 class="report-title">{{ $metadata['student_name'] ?? 'Học viên' }}</h2>
                        <p class="report-subtitle">{{ $metadata['class_name'] ?? '—' }} · {{ $report->dotDanhGia?->tenDot }}</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Mã HV: {{ $metadata['student_code'] ?? '—' }}</span>
                            <span class="report-chip">GV: {{ $metadata['teacher_name'] ?? '—' }}</span>
                            <span class="report-chip">{{ $report->trangThaiLabel }}</span>
                        </div>
                        <a href="{{ route('staff.evaluations.reports.preview', $report->baoCaoHocTapId) }}" target="_blank" class="report-button report-button--secondary">Xem PDF</a>
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__body">
                    <div class="report-meta-grid">
                        <div class="report-kv">
                            <div class="report-kv__label">Tỷ lệ tham gia</div>
                            <div class="report-kv__value">{{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div>
                        </div>
                        <div class="report-kv">
                            <div class="report-kv__label">Vắng không phép</div>
                            <div class="report-kv__value">{{ data_get($metadata, 'attendance.absent_unexcused', 0) }}</div>
                        </div>
                    </div>
                </div>
            </section>

            @foreach ($sections as $section)
                <section class="report-panel">
                    <div class="report-panel__head">
                        <h4>{{ $section['group'] }}</h4>
                    </div>
                    <div class="report-panel__body">
                        <div class="report-list">
                            @foreach ($section['items'] as $item)
                                <article class="report-row">
                                    <div class="report-row__top">
                                        <div class="report-persona">
                                            <strong>{{ $item['title'] }}</strong>
                                            <span>{{ $item['type'] }}</span>
                                        </div>
                                        <span class="report-badge report-badge--info">
                                            {{ $item['type'] === 'rating' ? ($item['rating'] ?: '—') : ($item['number'] ?? $item['value'] ?? '—') }}
                                        </span>
                                    </div>
                                    <div class="report-note">{{ $item['comment'] ?: 'Chưa có nhận xét.' }}</div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Thao tác duyệt</h4>
                    <p>Giữ khu vực quyết định ngắn gọn để thao tác nhanh hơn.</p>
                </div>
                <div class="report-panel__body d-grid gap-3">
                    @if ($report->trangThai === 'submitted')
                        <form method="POST" action="{{ route('staff.evaluations.reports.request-revision', $report->baoCaoHocTapId) }}" class="report-stack">
                            @csrf
                            <div class="report-field">
                                <label>Ghi chú trả chỉnh sửa</label>
                                <textarea name="note" class="form-control" rows="4" placeholder="Nêu rõ phần cần bổ sung hoặc chỉnh sửa..." required></textarea>
                            </div>
                            <div class="report-actions">
                                <button class="report-button report-button--danger">Trả chỉnh sửa</button>
                                <button formaction="{{ route('staff.evaluations.reports.approve', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Duyệt báo cáo</button>
                            </div>
                        </form>
                    @elseif ($report->trangThai === 'approved')
                        <form method="POST" action="{{ route('staff.evaluations.reports.publish', $report->baoCaoHocTapId) }}">
                            @csrf
                            <button class="report-button report-button--primary">Phát hành tới học viên</button>
                        </form>
                    @else
                        <div class="report-empty">
                            <strong>Không có thao tác duyệt khả dụng.</strong>
                            <p class="mb-0">Báo cáo đang ở trạng thái <strong>{{ $report->trangThaiLabel }}</strong>.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
