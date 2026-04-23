@extends('layouts.client')
@section('title', 'Báo cáo học tập')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row">
                @include('components.client.account-sidebar')
                <div class="col-lg-9">
                    @include('evaluations._theme')

                    <div class="account-content report-ui">
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Báo cáo học tập'],
                        ]" />

                        <div class="report-shell">
                            <section class="report-hero">
                                <div class="report-hero__content">
                                    <div>
                                        <span class="report-overline">Student Reports</span>
                                        <h2 class="report-title">Báo cáo học tập</h2>
                                    </div>
                                    <div class="report-hero__aside">
                                        <span class="report-chip">Tổng báo cáo: {{ $reports->count() }}</span>
                                    </div>
                                </div>
                            </section>

                            @if ($reports->isEmpty())
                                <div class="report-empty">
                                    <strong>Hiện chưa có báo cáo học tập nào.</strong>
                                    <p class="mb-0">Khi có báo cáo được phát hành, danh sách sẽ xuất hiện tại đây.</p>
                                </div>
                            @else
                                <div class="report-list">
                                    @foreach ($reports as $report)
                                        <article class="report-row">
                                            <div class="report-row__top">
                                                <div class="report-persona">
                                                    <strong>{{ $report->dotDanhGia?->tenDot }}</strong>
                                                    <span>{{ data_get($report->metadataSnapshot, 'class_name') }} · {{ data_get($report->metadataSnapshot, 'course_name') }}</span>
                                                </div>
                                                <span class="report-badge report-badge--success">Đã phát hành</span>
                                            </div>
                                            <div class="report-meta-grid">
                                                <div class="report-kv">
                                                    <div class="report-kv__label">Phát hành</div>
                                                    <div class="report-kv__value">{{ optional($report->publishedAt)->format('d/m/Y H:i') ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="report-row__bottom">
                                                <div class="report-actions">
                                                    <a href="{{ route('home.student.reports.show', $report->baoCaoHocTapId) }}" class="report-button report-button--secondary">Xem bản web</a>
                                                    <a href="{{ route('home.student.reports.download', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Tải PDF</a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
