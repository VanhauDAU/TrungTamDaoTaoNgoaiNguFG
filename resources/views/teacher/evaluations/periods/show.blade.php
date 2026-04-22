@extends('layouts.internal')

@section('title', $period->tenDot)
@section('page-title', 'Chi tiết đợt đánh giá')
@section('breadcrumb', 'Giáo viên · Đợt đánh giá')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Period Detail</span>
                        <h2 class="report-title">{{ $period->tenDot }}</h2>
                        <p class="report-subtitle">
                            {{ $period->lopHoc?->tenLopHoc }} · {{ $period->lopHoc?->khoaHoc?->tenKhoaHoc }} · {{ $period->lopHoc?->coSo?->tenCoSo }}
                        </p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Trạng thái: {{ $period->trangThaiLabel }}</span>
                            <span class="report-chip">Hạn nộp: {{ optional($period->hanNop)->format('d/m/Y') ?? '—' }}</span>
                            <span class="report-chip">Hoàn thành: {{ $summary['completed'] }}/{{ $summary['total'] }}</span>
                        </div>
                        <div class="report-actions">
                            <form method="POST" action="{{ route('teacher.evaluations.periods.bulk-create', $period->dotDanhGiaId) }}">
                                @csrf
                                <button class="report-button report-button--secondary">Tạo nháp cho cả lớp</button>
                            </form>
                            <a href="{{ route('teacher.evaluations.periods.index') }}" class="report-button report-button--soft">Quay lại danh sách</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Danh sách học viên trong đợt</h4>
                    <p>Thay bảng trống bằng danh sách tác vụ rõ hơn, tập trung vào trạng thái và hành động tiếp theo.</p>
                </div>
                <div class="report-panel__body">
                    @if ($reports->isEmpty())
                        <div class="report-empty">
                            <strong>Đợt này chưa có báo cáo nào.</strong>
                            <p class="mb-0">Dùng chức năng tạo nháp để sinh báo cáo hàng loạt cho cả lớp.</p>
                        </div>
                    @else
                        <div class="report-list">
                            @foreach ($reports as $report)
                                @php $student = $report->dangKyLopHoc?->taiKhoan; @endphp
                                <article class="report-row">
                                    <div class="report-row__top">
                                        <div class="report-persona">
                                            <strong>{{ $student?->hoSoNguoiDung?->hoTen ?? '—' }}</strong>
                                            <span>{{ $student?->taiKhoan ?: '—' }}</span>
                                        </div>
                                        <span class="report-badge report-badge--info">{{ $report->trangThaiLabel }}</span>
                                    </div>
                                    <div class="report-meta-grid">
                                        <div class="report-kv">
                                            <div class="report-kv__label">Cập nhật gần nhất</div>
                                            <div class="report-kv__value">{{ optional($report->updated_at)->format('d/m/Y H:i') ?? '—' }}</div>
                                        </div>
                                        <div class="report-kv">
                                            <div class="report-kv__label">Đợt</div>
                                            <div class="report-kv__value">{{ $period->tenDot }}</div>
                                        </div>
                                        <div class="report-kv">
                                            <div class="report-kv__label">Lớp học</div>
                                            <div class="report-kv__value">{{ $period->lopHoc?->tenLopHoc ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="report-row__bottom">
                                        <div class="report-actions">
                                            <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Mở báo cáo</a>
                                            <a href="{{ route('teacher.evaluations.reports.history', $report->baoCaoHocTapId) }}" class="report-button report-button--secondary">Lịch sử</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
