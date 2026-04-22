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
                    <div class="account-content">
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Báo cáo học tập', 'url' => route('home.student.reports.index')],
                            ['label' => $report->dotDanhGia?->tenDot],
                        ]" />

                        <div class="content-header d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                            <div>
                                <h2 class="page-title">{{ $report->dotDanhGia?->tenDot }}</h2>
                                <p class="page-subtitle mb-0">{{ data_get($metadata, 'class_name') }} · {{ data_get($metadata, 'course_name') }}</p>
                            </div>
                            <a href="{{ route('home.student.reports.download', $report->baoCaoHocTapId) }}" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Tải PDF
                            </a>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><div class="border rounded-4 p-3 bg-white shadow-sm h-100"><div class="small text-muted">Giáo viên</div><div class="fw-semibold">{{ data_get($metadata, 'teacher_name', '—') }}</div></div></div>
                            <div class="col-md-4"><div class="border rounded-4 p-3 bg-white shadow-sm h-100"><div class="small text-muted">Level hiện tại</div><div class="fw-semibold">{{ data_get($metadata, 'current_level', '—') }}</div></div></div>
                            <div class="col-md-4"><div class="border rounded-4 p-3 bg-white shadow-sm h-100"><div class="small text-muted">Tỷ lệ tham gia</div><div class="fw-semibold">{{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div></div></div>
                        </div>

                        @foreach ($groupedCriteria as $group => $items)
                            <div class="border rounded-4 p-4 bg-white shadow-sm mb-4">
                                <h4 class="mb-3">{{ $group }}</h4>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tiêu chí</th>
                                                <th>Giá trị</th>
                                                <th>Nhận xét</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $item)
                                                <tr>
                                                    <td>{{ $item->tenTieuChi }}</td>
                                                    <td>{{ $item->giaTriMucDanhGia ?: ($item->giaTriSo ?? $item->noiDungNhanXet ?? '—') }}</td>
                                                    <td>{{ $item->loaiDuLieu === 'text' ? ($item->noiDungNhanXet ?: '—') : ($item->noiDungNhanXet ?: '—') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
