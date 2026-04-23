@extends('layouts.internal')

@section('title', 'Duyệt báo cáo học tập')
@section('page-title', 'Duyệt báo cáo học tập')
@section('breadcrumb', 'Nhân viên · Hàng chờ duyệt')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Staff Review Queue</span>
                        <h2 class="report-title">Hàng chờ duyệt gọn hơn, chuyên nghiệp hơn</h2>
                        <p class="report-subtitle">Ưu tiên hiển thị đúng người học, đợt đánh giá, giáo viên và trạng thái để staff xử lý nhanh mà không phải mở nhiều màn hình.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-actions">
                            <a href="{{ route('staff.evaluations.periods.index') }}" class="report-button report-button--secondary">Quản lý đợt đánh giá</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-stat-grid">
                <article class="report-stat report-stat--info"><span class="report-stat__label">Chờ duyệt</span><div class="report-stat__value">{{ $summary['submitted'] }}</div><div class="report-stat__hint">Cần staff đọc và quyết định</div></article>
                <article class="report-stat report-stat--danger"><span class="report-stat__label">Cần chỉnh sửa</span><div class="report-stat__value">{{ $summary['needs_revision'] }}</div><div class="report-stat__hint">Đã trả lại giáo viên</div></article>
                <article class="report-stat report-stat--primary"><span class="report-stat__label">Đã duyệt</span><div class="report-stat__value">{{ $summary['approved'] }}</div><div class="report-stat__hint">Sẵn sàng phát hành</div></article>
                <article class="report-stat report-stat--success"><span class="report-stat__label">Đã phát hành</span><div class="report-stat__value">{{ $summary['published'] }}</div><div class="report-stat__hint">Học viên đã xem được</div></article>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Bộ lọc duyệt báo cáo</h4>
                    <p>Giữ thao tác ngắn gọn nhưng trực diện, không còn cảm giác form quản trị cũ.</p>
                </div>
                <div class="report-panel__body">
                    <form method="GET" class="report-filter-grid">
                        <div class="report-field">
                            <label>Trạng thái báo cáo</label>
                            <select name="trangThai" class="form-select">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="report-actions">
                            <button class="report-button report-button--primary">Áp dụng</button>
                            <a href="{{ route('staff.evaluations.index') }}" class="report-button report-button--secondary">Đặt lại</a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Danh sách báo cáo cần xử lý</h4>
                    <p>Dùng thẻ dạng task card thay cho bảng để thao tác và đọc trạng thái nhanh hơn.</p>
                </div>
                <div class="report-panel__body">
                    @if ($reports->isEmpty())
                        <div class="report-empty">
                            <strong>Không có báo cáo nào trong hàng chờ hiện tại.</strong>
                            <p class="mb-0">Khi giáo viên gửi duyệt hoặc cần chỉnh sửa, báo cáo sẽ xuất hiện tại đây.</p>
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
                                        <div class="report-kv"><div class="report-kv__label">Lớp / đợt</div><div class="report-kv__value">{{ $report->dotDanhGia?->lopHoc?->tenLopHoc }}<br>{{ $report->dotDanhGia?->tenDot }}</div></div>
                                        <div class="report-kv"><div class="report-kv__label">Giáo viên</div><div class="report-kv__value">{{ $report->giaoVien?->hoSoNguoiDung?->hoTen ?? $report->giaoVien?->taiKhoan ?? '—' }}</div></div>
                                        <div class="report-kv"><div class="report-kv__label">Cập nhật</div><div class="report-kv__value">{{ optional($report->updated_at)->format('d/m/Y H:i') ?? '—' }}</div></div>
                                    </div>
                                    <div class="report-row__bottom">
                                        <span class="report-note">Mở để đọc toàn bộ rubric, phản hồi chỉnh sửa hoặc duyệt phát hành.</span>
                                        <a href="{{ route('staff.evaluations.reports.show', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Mở duyệt</a>
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
