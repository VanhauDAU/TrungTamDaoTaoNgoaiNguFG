@extends('layouts.admin')

@section('title', 'Tạo mẫu quy định')
@section('page-title', 'Tạo mẫu quy định nhân sự')
@section('breadcrumb', 'Hồ sơ nhân sự · Tạo mẫu quy định')

@section('content')
    <form action="{{ route('admin.nhan-su.mau-quy-dinh.store') }}" method="POST">
        @csrf
        @include('admin.nhan-su.mau-quy-dinh._form')
    </form>
@endsection
