@extends('layouts.internal')

@section('title', 'Lịch sử báo cáo')
@section('page-title', 'Lịch sử báo cáo')
@section('breadcrumb', 'Giáo viên · Nhật ký xử lý báo cáo')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">History</span>
                        <h2 class="report-title">{{ $report->dangKyLopHoc?->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</h2>
                        <p class="report-subtitle">{{ $report->dotDanhGia?->tenDot }} · {{ $report->dotDanhGia?->lopHoc?->tenLopHoc }}</p>
                    </div>
                    <div class="report-hero__aside">
                        <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}" class="report-button report-button--secondary">Quay lại báo cáo</a>
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Nhật ký xử lý</h4>
                    <p>Mọi thay đổi trạng thái và ghi chú được trình bày theo timeline để truy vết nhanh hơn.</p>
                </div>
                <div class="report-panel__body">
                    @if ($history->isNotEmpty())
                        <div class="report-timeline">
                            @foreach ($history as $event)
                                <article class="report-timeline__item">
                                <div class="report-timeline__title">{{ $event->hanhDong }}</div>
                                <div class="report-meta mt-1">
                                    {{ optional($event->created_at)->format('d/m/Y H:i') }} ·
                                    {{ $event->nguoiThucHien?->hoSoNguoiDung?->hoTen ?? $event->nguoiThucHien?->taiKhoan ?? 'Hệ thống' }}
                                </div>
                                @if ($event->trangThaiTruoc || $event->trangThaiSau)
                                    <div class="report-note mt-2">Trạng thái: {{ $event->trangThaiTruoc ?? '—' }} → {{ $event->trangThaiSau ?? '—' }}</div>
                                @endif
                                @if ($event->ghiChu)
                                    <div class="mt-2">{{ $event->ghiChu }}</div>
                                @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="report-empty">
                            <strong>Chưa có lịch sử xử lý.</strong>
                            <p class="mb-0">Khi có lưu nháp, gửi duyệt hoặc staff phản hồi, timeline này sẽ được cập nhật.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
