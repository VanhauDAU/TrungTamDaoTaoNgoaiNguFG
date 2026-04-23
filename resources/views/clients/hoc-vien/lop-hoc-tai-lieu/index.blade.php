@extends('layouts.client')

@section('title', 'Tài liệu lớp – ' . $lopHoc->tenLopHoc)

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">📂 Tài liệu lớp học</h3>
        <p class="text-muted mb-0">{{ $lopHoc->tenLopHoc }}</p>
    </div>

    @if($taiLieus->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-30"></i>
            <h5>Chưa có tài liệu nào được chia sẻ</h5>
        </div>
    @else
        <div class="row g-3">
            @foreach($taiLieus as $tl)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary-subtle rounded-3 p-3 text-primary fs-4">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $tl->tieuDe }}</div>
                                @if($tl->moTa)
                                    <div class="text-muted small text-truncate">{{ $tl->moTa }}</div>
                                @endif
                                <div class="text-muted small mt-1">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill me-1">{{ $tl->nhom_label }}</span>
                                    {{ $tl->kich_thuoc_readable }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <a href="{{ route('home.student.classes.materials.download', [$lopHoc->lopHocId, $tl->lopHocTaiLieuId]) }}"
                               class="btn btn-sm btn-primary rounded-pill w-100">
                                <i class="fas fa-download me-2"></i>Tải xuống
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
