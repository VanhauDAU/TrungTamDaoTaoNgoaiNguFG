@extends('layouts.internal')

@section('title', 'Báo cáo học tập')
@section('page-title', 'Báo cáo học tập')
@section('breadcrumb', 'Giáo viên · Báo cáo học tập / nhận xét tiến độ')

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Nháp', 'value' => $summary['draft'] ?? 0, 'variant' => 'warning', 'hint' => 'Báo cáo đang soạn'],
            ['label' => 'Chờ duyệt', 'value' => $summary['submitted'] ?? 0, 'variant' => 'info', 'hint' => 'Đã gửi staff'],
            ['label' => 'Cần chỉnh sửa', 'value' => $summary['needs_revision'] ?? 0, 'variant' => 'danger', 'hint' => 'Cần cập nhật lại'],
            ['label' => 'Đã phát hành', 'value' => $summary['published'] ?? 0, 'variant' => 'success', 'hint' => 'Đã tới học viên'],
            ['label' => 'Quá hạn', 'value' => $summary['overdue'] ?? 0, 'variant' => 'primary', 'hint' => 'Ưu tiên xử lý ngay'],
        ];
    @endphp

    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Teacher Workspace</span>
                        <h2 class="report-title">Báo cáo học tập theo phong cách làm việc rõ ràng và dễ xử lý hơn</h2>
                        <p class="report-subtitle">
                            Theo dõi nhanh báo cáo đang soạn, trạng thái cần xử lý và tiến độ từng đợt đánh giá trong một màn hình duy nhất.
                        </p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Tổng báo cáo: {{ number_format($reports->count()) }}</span>
                            <span class="report-chip">Đợt phụ trách: {{ number_format($periods->count()) }}</span>
                        </div>
                        <div class="report-actions">
                            <a href="{{ route('teacher.evaluations.periods.index') }}" class="report-button report-button--secondary">Xem đợt đánh giá</a>
                            <a href="{{ route('teacher.evaluations.index') }}" class="report-button report-button--soft">Làm mới dashboard</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-stat-grid">
                @foreach ($summaryCards as $card)
                    <article class="report-stat report-stat--{{ $card['variant'] }}">
                        <span class="report-stat__label">{{ $card['label'] }}</span>
                        <div class="report-stat__value">{{ number_format($card['value']) }}</div>
                        <div class="report-stat__hint">{{ $card['hint'] }}</div>
                    </article>
                @endforeach
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Bộ lọc làm việc</h4>
                    <p>Thu hẹp danh sách theo lớp học và trạng thái để thao tác nhanh hơn.</p>
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
                            <label>Trạng thái</label>
                            <select name="trangThai" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="report-actions">
                            <button class="report-button report-button--primary">Áp dụng bộ lọc</button>
                            <a href="{{ route('teacher.evaluations.index') }}" class="report-button report-button--secondary">Đặt lại</a>
                        </div>
                    </form>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-xl-7">
                    <section class="report-panel h-100">
                        <div class="report-panel__head">
                            <h4>Báo cáo cần quan tâm</h4>
                            <p>Hiển thị theo kiểu workspace, ưu tiên thông tin người học, lớp và thao tác tiếp theo.</p>
                        </div>
                        <div class="report-panel__body">
                            @if ($reports->isEmpty())
                                <div class="report-empty">
                                    <strong>Chưa có báo cáo phù hợp bộ lọc.</strong>
                                    <p class="mb-0">Thử đổi trạng thái hoặc lớp học để mở rộng danh sách.</p>
                                </div>
                            @else
                                <div class="report-list">
                                    @foreach ($reports as $report)
                                        <article class="report-row">
                                            <div class="report-row__top">
                                                <div class="report-persona">
                                                    <strong>{{ $report->dangKyLopHoc?->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</strong>
                                                    <span>{{ $report->dangKyLopHoc?->taiKhoan?->taiKhoan }}</span>
                                                </div>
                                                <span class="report-badge report-badge--info">{{ $report->trangThaiLabel }}</span>
                                            </div>
                                            <div class="report-meta-grid">
                                                <div class="report-kv">
                                                    <div class="report-kv__label">Lớp học</div>
                                                    <div class="report-kv__value">{{ $report->dotDanhGia?->lopHoc?->tenLopHoc ?: '—' }}</div>
                                                </div>
                                                <div class="report-kv">
                                                    <div class="report-kv__label">Đợt đánh giá</div>
                                                    <div class="report-kv__value">{{ $report->dotDanhGia?->tenDot ?: '—' }}</div>
                                                </div>
                                                <div class="report-kv">
                                                    <div class="report-kv__label">Cập nhật</div>
                                                    <div class="report-kv__value">{{ optional($report->updated_at)->format('d/m/Y H:i') ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="report-row__bottom">
                                                <span class="report-note">Mở để soạn, bổ sung hoặc kiểm tra trước khi gửi duyệt.</span>
                                                <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Mở báo cáo</a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                </div>

                <div class="col-xl-5">
                    <section class="report-panel h-100">
                        <div class="report-panel__head">
                            <h4>Đợt đánh giá đang phụ trách</h4>
                            <p>Tiến độ từng lớp được đẩy lên rõ hơn để biết đợt nào còn tồn đọng.</p>
                        </div>
                        <div class="report-panel__body">
                            <div class="report-list">
                        @forelse ($periods as $period)
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
                                    <div>
                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                            <span class="report-meta">Hoàn thành {{ $done }}/{{ $total }} báo cáo</span>
                                            <span class="report-meta">{{ $progress }}%</span>
                                        </div>
                                        <div class="report-progress"><span style="width: {{ $progress }}%"></span></div>
                                    </div>
                                    <div class="report-row__bottom">
                                        <span class="report-note">Hạn nộp {{ optional($period->hanNop)->format('d/m/Y') ?? 'chưa thiết lập' }}</span>
                                        <a href="{{ route('teacher.evaluations.periods.show', $period->dotDanhGiaId) }}" class="report-button report-button--secondary">Chi tiết đợt</a>
                                    </div>
                                </article>
                        @empty
                                <div class="report-empty">
                                    <strong>Chưa có đợt đánh giá nào.</strong>
                                    <p class="mb-0">Khi staff tạo đợt mới cho lớp bạn phụ trách, màn hình này sẽ tự xuất hiện dữ liệu.</p>
                                </div>
                        @endforelse
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
