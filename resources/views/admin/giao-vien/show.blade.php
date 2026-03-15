@extends('layouts.admin')

@section('title', 'Hồ sơ giáo viên')
@section('page-title', 'Hồ sơ giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Hồ sơ chi tiết')

@section('stylesheet')
    @include('admin.nhan-su.partials.profile-styles')
@endsection

@section('content')
    @include('admin.nhan-su.partials.profile', [
        'routePrefix' => 'admin.giao-vien',
    ])
@endsection

@section('script')
    @include('admin.nhan-su.partials.profile-script')
@endsection
