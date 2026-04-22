@extends('layouts.internal')

@section('title', 'Tạo mẫu báo cáo')
@section('page-title', 'Tạo mẫu báo cáo')
@section('breadcrumb', 'Nhân viên · Tạo mẫu báo cáo học tập')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Create Template</span>
                        <h2 class="report-title">Tạo mẫu báo cáo học tập mới</h2>
                        <p class="report-subtitle">Thiết kế mẫu theo cách trực quan hơn: phần thông tin nền rõ ràng, studio tiêu chí dễ quét và thao tác thêm nhanh theo đúng nhu cầu.</p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Builder thông minh</span>
                            <span class="report-chip">Preset thêm nhanh</span>
                        </div>
                        <a href="{{ route('staff.evaluations.templates.index') }}" class="report-button report-button--secondary">Danh sách mẫu</a>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('staff.evaluations.templates.store') }}">
                @csrf
                @include('staff.evaluations.templates._form', ['submitLabel' => 'Lưu mẫu mới'])
            </form>
        </div>
    </div>
@endsection
