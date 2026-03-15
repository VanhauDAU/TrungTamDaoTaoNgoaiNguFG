@extends('layouts.admin')

@section('title', 'Hồ sơ nhân viên')
@section('page-title', 'Hồ sơ nhân viên')
@section('breadcrumb', 'Quản lý nhân viên · Hồ sơ chi tiết')

@section('stylesheet')
    @include('admin.nhan-su.partials.profile-styles')
@endsection

@section('content')
    @include('admin.nhan-su.partials.profile', [
        'routePrefix' => 'admin.nhan-vien',
    ])
@endsection

@section('script')
    @include('admin.nhan-su.partials.profile-script')
@endsection
