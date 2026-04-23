@extends('layouts.internal')

@section('title', 'Chỉnh sửa mẫu báo cáo')
@section('page-title', 'Chỉnh sửa mẫu báo cáo')
@section('breadcrumb', 'Nhân viên · Chỉnh sửa mẫu rubric')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Edit Template</span>
                        <h2 class="report-title">{{ $template->tenMau }}</h2>
                        <p class="report-subtitle">Phiên bản {{ $template->phienBan ?: '1.0' }} · Cập nhật {{ optional($template->updated_at)->format('d/m/Y H:i') ?? '—' }} · Chỉnh trực tiếp cấu trúc rubric với builder rõ ràng và dễ thao tác hơn.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            @if ($template->macDinh)
                                <span class="report-chip">Mặc định</span>
                            @endif
                            <span class="report-chip">{{ $template->kichHoat ? 'Đang kích hoạt' : 'Đã tắt' }}</span>
                            <span class="report-chip">Studio chỉnh sửa</span>
                        </div>
                        <div class="report-actions">
                            <form method="POST" action="{{ route('staff.evaluations.templates.duplicate', $template->baoCaoHocTapMauId) }}">
                                @csrf
                                <button class="report-button report-button--secondary">Nhân bản mẫu</button>
                            </form>
                            <a href="{{ route('staff.evaluations.templates.index') }}" class="report-button report-button--soft">Danh sách mẫu</a>
                        </div>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('staff.evaluations.templates.update', $template->baoCaoHocTapMauId) }}">
                @csrf
                @method('PUT')
                @include('staff.evaluations.templates._form', ['submitLabel' => 'Lưu thay đổi'])
            </form>
        </div>
    </div>
@endsection
