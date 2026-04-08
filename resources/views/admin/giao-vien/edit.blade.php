@extends('layouts.admin')

@section('title', 'Sửa giáo viên')
@section('page-title', 'Cập nhật giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Cập nhật hồ sơ')

@section('stylesheet')
    @include('admin.nhan-su.partials.form-styles')
@endsection

@section('content')
    <form action="{{ route('admin.giao-vien.update', $record->taiKhoan) }}" method="POST" autocomplete="off" novalidate data-joi-schema="nhanSu">
        @csrf
        @method('PUT')

        @include('admin.nhan-su.partials.form', [
            'formMode' => 'edit',
            'routePrefix' => 'admin.giao-vien',
        ])
    </form>
@endsection

@section('script')
    @include('admin.nhan-su.partials.form-script')
@endsection
