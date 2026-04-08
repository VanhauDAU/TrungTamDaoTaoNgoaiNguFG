@extends('layouts.admin')

@section('title', 'Sửa nhân viên')
@section('page-title', 'Cập nhật nhân viên')
@section('breadcrumb', 'Quản lý nhân viên · Cập nhật hồ sơ')

@section('stylesheet')
    @include('admin.nhan-su.partials.form-styles')
@endsection

@section('content')
    <form action="{{ route('admin.nhan-vien.update', $record->taiKhoan) }}" method="POST" autocomplete="off" novalidate data-joi-schema="nhanSu">
        @csrf
        @method('PUT')

        @include('admin.nhan-su.partials.form', [
            'formMode' => 'edit',
            'routePrefix' => 'admin.nhan-vien',
        ])
    </form>
@endsection

@section('script')
    @include('admin.nhan-su.partials.form-script')
@endsection
