@extends('layouts.client')

@section('title', 'Tài liệu lớp – ' . $lopHoc->tenLopHoc)

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">📂 Tài liệu lớp học</h3>
        <p class="text-muted mb-0">{{ $lopHoc->tenLopHoc }}</p>
    </div>

    @if($taiLieuGroups->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-30"></i>
            <h5>Chưa có tài liệu nào được chia sẻ</h5>
        </div>
    @else
        <div class="d-flex flex-column gap-4">
            @foreach($taiLieuGroups as $group)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-primary-subtle border-0 px-4 py-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h5 class="mb-1 fw-bold text-dark">{{ $group->title }}</h5>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i>{{ $group->sent_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian gửi' }}
                                    <span class="mx-2">•</span>{{ $group->count }} tài liệu
                                </div>
                            </div>
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">Đợt gửi</span>
                        </div>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        <div class="row g-3">
                            @foreach($group->items as $tl)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border rounded-4 p-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="flex-shrink-0 bg-primary-subtle rounded-3 p-3 text-primary fs-4">
                                                <i class="fas {{ $tl->mime_icon }}"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-semibold">{{ $tl->tieuDe }}</div>
                                                @if($tl->moTa)
                                                    <div class="text-muted small mt-1">{{ $tl->moTa }}</div>
                                                @endif
                                                <div class="text-muted small mt-2 d-flex flex-wrap gap-2">
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $tl->nhom_label }}</span>
                                                    <span>{{ $tl->kich_thuoc_readable }}</span>
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
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
