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
                    <div class="account-content">
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Báo cáo học tập'],
                        ]" />

                        <div class="content-header mb-4">
                            <h2 class="page-title"><i class="fas fa-file-lines me-2"></i>Báo cáo học tập</h2>
                            <p class="page-subtitle">Danh sách báo cáo đã được phát hành chính thức từ giáo viên và staff.</p>
                        </div>

                        @if ($reports->isEmpty())
                            <div class="alert alert-light border">Hiện chưa có báo cáo học tập nào được phát hành cho tài khoản của bạn.</div>
                        @else
                            <div class="d-grid gap-3">
                                @foreach ($reports as $report)
                                    <div class="border rounded-4 p-4 bg-white shadow-sm">
                                        <div class="d-flex flex-wrap justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold fs-5">{{ $report->dotDanhGia?->tenDot }}</div>
                                                <div class="text-muted">
                                                    {{ data_get($report->metadataSnapshot, 'class_name') }} · {{ data_get($report->metadataSnapshot, 'course_name') }}
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    Phát hành: {{ optional($report->publishedAt)->format('d/m/Y H:i') ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('home.student.reports.show', $report->baoCaoHocTapId) }}" class="btn btn-outline-primary">Xem bản web</a>
                                                <a href="{{ route('home.student.reports.download', $report->baoCaoHocTapId) }}" class="btn btn-primary">Tải PDF</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
