@extends('layouts.admin')

@section('title', 'Sửa mẫu quy định')
@section('page-title', 'Cập nhật mẫu quy định nhân sự')
@section('breadcrumb', 'Hồ sơ nhân sự · Cập nhật mẫu quy định')

@section('content')
    <form action="{{ route('admin.nhan-su.mau-quy-dinh.update', $template->nhanSuMauQuyDinhId) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.nhan-su.mau-quy-dinh._form')
    </form>
@endsection
