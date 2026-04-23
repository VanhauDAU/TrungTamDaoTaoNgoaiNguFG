@extends('layouts.internal')

@section('title', 'Đợt đánh giá học tập')
@section('page-title', 'Đợt đánh giá học tập')
@section('breadcrumb', 'Nhân viên · Tạo và theo dõi đợt đánh giá')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Operations Center</span>
                        <h2 class="report-title">Điều phối đợt đánh giá và mẫu báo cáo trong một không gian thống nhất</h2>
                        <p class="report-subtitle">Staff có thể tạo đợt mới, chọn mẫu rubric phù hợp và theo dõi tiến độ phát hành của từng lớp ngay trên cùng một màn hình.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-actions">
                            <a href="{{ route('staff.evaluations.templates.index') }}" class="report-button report-button--secondary">Quản lý mẫu báo cáo</a>
                            <a href="{{ route('staff.evaluations.index') }}" class="report-button report-button--soft">Hàng chờ duyệt</a>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-xl-4">
                    <section class="report-panel h-100">
                        <div class="report-panel__head">
                            <h4>Tạo đợt đánh giá mới</h4>
                            <p>Form được chia rõ nhóm dữ liệu để staff thao tác nhanh và ít sai sót hơn.</p>
                        </div>
                        <div class="report-panel__body">
                            <form method="POST" action="{{ route('staff.evaluations.periods.store') }}" class="report-stack">
                                @csrf
                                <div class="report-field">
                                    <label>Lớp học</label>
                                    <select name="lopHocId" class="form-select" required>
                                        <option value="">Chọn lớp học</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->lopHocId }}">
                                                [{{ $class->maLopHoc }}] {{ $class->tenLopHoc }} · {{ $class->khoaHoc?->tenKhoaHoc }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="report-field">
                                    <label>Mẫu rubric</label>
                                    <select name="baoCaoHocTapMauId" class="form-select">
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->baoCaoHocTapMauId }}">{{ $template->tenMau }} (v{{ $template->phienBan }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="report-field">
                                    <label>Tên đợt</label>
                                    <input type="text" name="tenDot" class="form-control" placeholder="Ví dụ: Đợt đánh giá giữa khóa tháng 04/2026" required>
                                </div>
                                <div class="report-form-grid">
                                    <div class="report-field">
                                        <label>Từ ngày</label>
                                        <input type="date" name="tuNgay" class="form-control">
                                    </div>
                                    <div class="report-field">
                                        <label>Đến ngày</label>
                                        <input type="date" name="denNgay" class="form-control">
                                    </div>
                                    <div class="report-field">
                                        <label>Hạn nộp</label>
                                        <input type="date" name="hanNop" class="form-control">
                                    </div>
                                    <div class="report-field">
                                        <label>Hạn duyệt</label>
                                        <input type="date" name="hanDuyet" class="form-control">
                                    </div>
                                </div>
                                <button class="report-button report-button--primary">Tạo đợt đánh giá</button>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xl-8">
                    <section class="report-panel h-100">
                        <div class="report-panel__head">
                            <h4>Đợt đánh giá toàn trung tâm</h4>
                            <p>Theo dõi từng đợt bằng layout card hiện đại thay cho bảng quản trị cũ.</p>
                        </div>
                        <div class="report-panel__body">
                            @if ($periods->isEmpty())
                                <div class="report-empty">
                                    <strong>Chưa có đợt đánh giá nào.</strong>
                                    <p class="mb-0">Sau khi tạo đợt đầu tiên, danh sách tiến độ sẽ xuất hiện ở đây.</p>
                                </div>
                            @else
                                <div class="report-list">
                                    @foreach ($periods as $period)
                                        @php
                                            $total = $period->baoCaos->count();
                                            $published = $period->baoCaos->where('trangThai', 'published')->count();
                                            $progress = $total > 0 ? round(($published / $total) * 100) : 0;
                                        @endphp
                                        <article class="report-row">
                                            <div class="report-row__top">
                                                <div class="report-persona">
                                                    <strong>{{ $period->tenDot }}</strong>
                                                    <span>{{ $period->lopHoc?->tenLopHoc }} · {{ $period->lopHoc?->coSo?->tenCoSo }}</span>
                                                </div>
                                                <span class="report-badge report-badge--warning">{{ $period->trangThaiLabel }}</span>
                                            </div>
                                            <div class="report-meta-grid">
                                                <div class="report-kv"><div class="report-kv__label">Mốc thời gian</div><div class="report-kv__value">{{ optional($period->hanNop)->format('d/m/Y') ?? '—' }} / {{ optional($period->hanDuyet)->format('d/m/Y') ?? '—' }}</div></div>
                                                <div class="report-kv"><div class="report-kv__label">Đã phát hành</div><div class="report-kv__value">{{ $published }}/{{ $total }}</div></div>
                                                <div class="report-kv"><div class="report-kv__label">Mẫu áp dụng</div><div class="report-kv__value">{{ $period->mau?->tenMau ?? 'Mặc định hệ thống' }}</div></div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between gap-3 mb-2">
                                                    <span class="report-meta">Tỷ lệ phát hành</span>
                                                    <span class="report-meta">{{ $progress }}%</span>
                                                </div>
                                                <div class="report-progress"><span style="width: {{ $progress }}%"></span></div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
