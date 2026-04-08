@extends('layouts.admin')

@section('title', 'Tạo giáo viên')
@section('page-title', 'Tạo hồ sơ giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Tạo hồ sơ')

@section('stylesheet')
    @include('admin.nhan-su.partials.form-styles')
@endsection

@section('content')
    <form action="{{ route('admin.giao-vien.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate data-joi-schema="nhanSu">
        @csrf

        @include('admin.nhan-su.partials.form', [
            'formMode' => 'create',
            'routePrefix' => 'admin.giao-vien',
        ])
    </form>
@endsection

@section('script')
    @include('admin.nhan-su.partials.form-script')
@endsection
