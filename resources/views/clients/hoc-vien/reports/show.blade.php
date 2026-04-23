@extends('layouts.client')
@section('title', 'Chi tiết báo cáo học tập')

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
                            ['label' => 'Báo cáo học tập', 'url' => route('home.student.reports.index')],
                            ['label' => $report->dotDanhGia?->tenDot],
                        ]" />

                        <div class="report-shell">
                            <section class="report-hero">
                                <div class="report-hero__content">
                                    <div>
                                        <span class="report-overline">Report Detail</span>
                                        <h2 class="report-title">{{ $report->dotDanhGia?->tenDot }}</h2>
                                        <p class="report-subtitle">{{ data_get($metadata, 'class_name') }} · {{ data_get($metadata, 'course_name') }}</p>
                                    </div>
                                    <div class="report-hero__aside">
                                        <a href="{{ route('home.student.reports.download', $report->baoCaoHocTapId) }}" class="report-button report-button--primary">Tải PDF</a>
                                    </div>
                                </div>
                            </section>

                            <section class="report-panel">
                                <div class="report-panel__body">
                                    <div class="report-meta-grid">
                                        <div class="report-kv"><div class="report-kv__label">Giáo viên</div><div class="report-kv__value">{{ data_get($metadata, 'teacher_name', '—') }}</div></div>
                                        <div class="report-kv"><div class="report-kv__label">Level hiện tại</div><div class="report-kv__value">{{ data_get($metadata, 'current_level', '—') }}</div></div>
                                        <div class="report-kv"><div class="report-kv__label">Tỷ lệ tham gia</div><div class="report-kv__value">{{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div></div>
                                    </div>
                                </div>
                            </section>

                            @foreach ($groupedCriteria as $group => $items)
                                <section class="report-panel">
                                    <div class="report-panel__head">
                                        <h4>{{ $group }}</h4>
                                    </div>
                                    <div class="report-panel__body">
                                        <div class="report-list">
                                            @foreach ($items as $item)
                                                <article class="report-row">
                                                    <div class="report-row__top">
                                                        <div class="report-persona">
                                                            <strong>{{ $item->tenTieuChi }}</strong>
                                                        </div>
                                                        <span class="report-badge report-badge--info">{{ $item->giaTriMucDanhGia ?: ($item->giaTriSo ?? $item->noiDungNhanXet ?? '—') }}</span>
                                                    </div>
                                                    <div class="report-note">{{ $item->noiDungNhanXet ?: '—' }}</div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
