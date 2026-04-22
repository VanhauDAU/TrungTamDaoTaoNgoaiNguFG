@extends('layouts.internal')

@section('title', 'Đợt đánh giá')
@section('page-title', 'Đợt đánh giá')
@section('breadcrumb', 'Giáo viên · Danh sách đợt đánh giá')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Evaluation Periods</span>
                        <h2 class="report-title">Danh sách đợt đánh giá theo dạng board trực quan</h2>
                        <p class="report-subtitle">Mỗi đợt hiển thị tiến độ, lớp học và hạn nộp rõ ràng để giáo viên biết chính xác nơi cần xử lý tiếp.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Tổng đợt: {{ number_format($periods->count()) }}</span>
                            <span class="report-chip">Lớp đang lọc: {{ $selectedClassId ? '1 lớp' : 'Tất cả' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Bộ lọc đợt đánh giá</h4>
                    <p>Chọn lớp học hoặc trạng thái để tập trung vào đúng nhóm công việc.</p>
                </div>
                <div class="report-panel__body">
                    <form method="GET" class="report-filter-grid">
                        <div class="report-field">
                            <label>Lớp học</label>
                            <select name="lopHocId" class="form-select">
                                <option value="">Tất cả lớp</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->lopHocId }}" @selected($selectedClassId == $class->lopHocId)>
                                        [{{ $class->maLopHoc }}] {{ $class->tenLopHoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="report-field">
                            <label>Trạng thái đợt</label>
                            <select name="trangThai" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="report-actions">
                            <button class="report-button report-button--primary">Lọc danh sách</button>
                            <a href="{{ route('teacher.evaluations.periods.index') }}" class="report-button report-button--secondary">Đặt lại</a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Tất cả đợt bạn đang tham gia</h4>
                    <p>Bố cục mới giúp đọc tiến độ dễ hơn so với bảng truyền thống.</p>
                </div>
                <div class="report-panel__body">
                    @if ($periods->isEmpty())
                        <div class="report-empty">
                            <strong>Không có đợt đánh giá phù hợp.</strong>
                            <p class="mb-0">Thử thay đổi bộ lọc hoặc chờ staff tạo thêm đợt mới.</p>
                        </div>
                    @else
                        <div class="report-list">
                            @foreach ($periods as $period)
                                @php
                                    $total = $period->baoCaos->count();
                                    $done = $period->baoCaos->whereIn('trangThai', ['submitted', 'approved', 'published'])->count();
                                    $progress = $total > 0 ? round(($done / $total) * 100) : 0;
                                @endphp
                                <article class="report-row">
                                    <div class="report-row__top">
                                        <div class="report-persona">
                                            <strong>{{ $period->tenDot }}</strong>
                                            <span>{{ $period->lopHoc?->tenLopHoc }} · {{ $period->lopHoc?->khoaHoc?->tenKhoaHoc }}</span>
                                        </div>
                                        <span class="report-badge report-badge--warning">{{ $period->trangThaiLabel }}</span>
                                    </div>
                                    <div class="report-meta-grid">
                                        <div class="report-kv">
                                            <div class="report-kv__label">Tiến độ</div>
                                            <div class="report-kv__value">{{ $done }}/{{ $total }} báo cáo</div>
                                        </div>
                                        <div class="report-kv">
                                            <div class="report-kv__label">Hạn nộp</div>
                                            <div class="report-kv__value">{{ optional($period->hanNop)->format('d/m/Y') ?? '—' }}</div>
                                        </div>
                                        <div class="report-kv">
                                            <div class="report-kv__label">Tỷ lệ hoàn thành</div>
                                            <div class="report-kv__value">{{ $progress }}%</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="report-progress"><span style="width: {{ $progress }}%"></span></div>
                                    </div>
                                    <div class="report-row__bottom">
                                        <span class="report-note">Theo dõi chi tiết danh sách học viên và báo cáo trong từng đợt.</span>
                                        <a href="{{ route('teacher.evaluations.periods.show', $period->dotDanhGiaId) }}" class="report-button report-button--secondary">Xem chi tiết</a>
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
