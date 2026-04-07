@extends('layouts.admin')

@section('title', 'Tạo nhân viên')
@section('page-title', 'Tạo hồ sơ nhân viên')
@section('breadcrumb', 'Quản lý nhân viên · Tạo hồ sơ')

@section('stylesheet')
    @include('admin.nhan-su.partials.form-styles')
@endsection

@section('content')
    <form action="{{ route('admin.nhan-vien.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate data-joi-schema="nhanSu">
        @csrf

        @include('admin.nhan-su.partials.form', [
            'formMode' => 'create',
            'routePrefix' => 'admin.nhan-vien',
        ])
    </form>
@endsection

@section('script')
    @include('admin.nhan-su.partials.form-script')
@endsection
