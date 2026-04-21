@extends('layouts.internal')

@section('title', 'Tài liệu lớp học')
@section('page-title', 'Tài liệu lớp học')
@section('breadcrumb', 'Tài liệu')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h4 class="fw-bold mb-0">📂 Tổng hợp tài liệu lớp học</h4>
        <p class="text-muted mt-1 mb-0 small">Chọn lớp để quản lý tài liệu tương ứng.</p>
    </div>

    @if($classes->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-30"></i>
            <h5>Bạn chưa phụ trách lớp học nào.</h5>
        </div>
    @else
        <div class="row g-3">
            @foreach($classes as $lop)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                        <div class="fw-bold text-dark mb-1">{{ $lop->tenLopHoc }}</div>
                        <div class="text-muted small mb-3">{{ $lop->khoaHoc->tenKhoaHoc ?? 'Chưa có khóa học' }}</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                {{ $lop->lop_hoc_tai_lieus_count ?? 0 }} tài liệu
                            </span>
                            <a href="{{ route('teacher.classes.materials.index', $lop->slug) }}"
                               class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fas fa-folder-open me-1"></i>Xem tài liệu
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
