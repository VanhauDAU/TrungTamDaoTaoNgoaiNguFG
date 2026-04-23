@extends('layouts.internal')

@section('title', 'Mẫu báo cáo học tập')
@section('page-title', 'Mẫu báo cáo học tập')
@section('breadcrumb', 'Nhân viên · Quản trị mẫu rubric')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Template Library</span>
                        <h2 class="report-title">Thư viện mẫu báo cáo học tập</h2>
                        <p class="report-subtitle">Giao diện gọn hơn để tập trung vào mẫu nào đang dùng, mẫu nào là mặc định và cần chỉnh sửa ở đâu.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-actions">
                            <a href="{{ route('staff.evaluations.periods.index') }}" class="report-button report-button--secondary">Quay lại đợt đánh giá</a>
                            <a href="{{ route('staff.evaluations.templates.create') }}" class="report-button report-button--primary">Tạo mẫu mới</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-stat-grid">
                <article class="report-stat report-stat--primary"><span class="report-stat__label">Tổng mẫu</span><div class="report-stat__value">{{ $summary['total'] }}</div></article>
                <article class="report-stat report-stat--success"><span class="report-stat__label">Đang kích hoạt</span><div class="report-stat__value">{{ $summary['active'] }}</div></article>
                <article class="report-stat report-stat--info"><span class="report-stat__label">Mặc định</span><div class="report-stat__value">{{ $summary['default'] }}</div></article>
                <article class="report-stat report-stat--warning"><span class="report-stat__label">Đang dùng</span><div class="report-stat__value">{{ $summary['in_use'] }}</div></article>
            </section>

            <div class="row g-3">
                @forelse ($templates as $template)
                    <div class="col-xl-6">
                        <section class="report-panel h-100">
                            <div class="report-panel__body report-stack">
                                <div class="report-row__top">
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <h5 class="mb-0 fw-bold">{{ $template->tenMau }}</h5>
                                            @if ($template->macDinh)
                                                <span class="report-badge report-badge--primary">Mặc định</span>
                                            @endif
                                            <span class="report-badge {{ $template->kichHoat ? 'report-badge--success' : '' }}">
                                                {{ $template->kichHoat ? 'Đang kích hoạt' : 'Đã tắt' }}
                                            </span>
                                        </div>
                                        <div class="report-note mt-1">Phiên bản {{ $template->phienBan ?: '1.0' }}</div>
                                    </div>
                                    <div class="report-note">Cập nhật {{ optional($template->updated_at)->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>

                                <div class="report-note">{{ $template->moTa ?: 'Chưa có mô tả cho mẫu này.' }}</div>

                                <div class="report-meta-grid">
                                    <div class="report-kv"><div class="report-kv__label">Tiêu chí</div><div class="report-kv__value">{{ $template->tieu_chis_count }}</div></div>
                                    <div class="report-kv"><div class="report-kv__label">Đợt đã dùng</div><div class="report-kv__value">{{ $template->dot_danh_gias_count }}</div></div>
                                    <div class="report-kv"><div class="report-kv__label">Khả dụng</div><div class="report-kv__value">{{ $template->kichHoat ? 'Có' : 'Không' }}</div></div>
                                </div>

                                <div class="report-actions">
                                    <a href="{{ route('staff.evaluations.templates.edit', $template->baoCaoHocTapMauId) }}" class="report-button report-button--primary">Chỉnh sửa</a>
                                    <form method="POST" action="{{ route('staff.evaluations.templates.duplicate', $template->baoCaoHocTapMauId) }}">
                                        @csrf
                                        <button class="report-button report-button--secondary">Nhân bản</button>
                                    </form>
                                    @if (! $template->macDinh)
                                        <form method="POST" action="{{ route('staff.evaluations.templates.set-default', $template->baoCaoHocTapMauId) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="report-button report-button--soft">Đặt mặc định</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('staff.evaluations.templates.toggle-activation', $template->baoCaoHocTapMauId) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="report-button report-button--secondary">
                                            {{ $template->kichHoat ? 'Tắt kích hoạt' : 'Kích hoạt' }}
                                        </button>
                                    </form>
                                    @if ($template->dot_danh_gias_count === 0)
                                        <form method="POST" action="{{ route('staff.evaluations.templates.destroy', $template->baoCaoHocTapMauId) }}"
                                            onsubmit="return confirm('Xóa mẫu này? Hành động không thể hoàn tác.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="report-button report-button--danger">Xóa</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="report-empty">
                            <strong>Chưa có mẫu báo cáo nào.</strong>
                            <p class="mb-0">Bạn có thể tạo mẫu đầu tiên ngay bây giờ.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
