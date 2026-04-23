@extends('layouts.internal')

@section('title', 'Tài liệu lớp: ' . $lopHoc->tenLopHoc)
@section('page-title', 'Tài liệu lớp học')
@section('breadcrumb', 'Lớp học / ' . $lopHoc->tenLopHoc . ' / Tài liệu')

@section('stylesheet')
<style>
    .nhom-badge { font-size: .72rem; letter-spacing: .03em; }
    .source-badge { font-size: .65rem; }
    .batch-card { border: 1px solid #e5e7eb; }
    .batch-header { background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); }
    .file-item { transition: background .15s ease; }
    .file-item:hover { background: #f8fafc; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">📂 Tài liệu lớp học</h4>
            <p class="text-muted mb-0 small mt-1">
                <a href="{{ route('teacher.classes.show', $lopHoc->slug) }}" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại {{ $lopHoc->tenLopHoc }}
                </a>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Nút chính: Chọn từ thư viện --}}
            <a href="{{ route('teacher.classes.materials.select-library', $lopHoc->slug) }}"
               class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-folder-open me-2"></i>Chọn từ thư viện
            </a>
            {{-- Nút phụ: Tải thẳng vào lớp (giữ tương thích) --}}
            <a href="{{ route('teacher.classes.materials.create', $lopHoc->slug) }}"
               class="btn btn-outline-secondary rounded-pill px-3" title="Tải file mới thẳng vào lớp (không qua thư viện)">
                <i class="fas fa-upload me-2"></i>Upload thẳng
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Danh sách theo đợt gửi --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        @if($taiLieuGroups->isEmpty())
            <div class="card-body p-5 text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 opacity-30"></i>
                <h5>Chưa có tài liệu nào</h5>
                <p class="mb-4">Chọn tài liệu từ thư viện cá nhân hoặc tải file mới để chia sẻ với học viên.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('teacher.classes.materials.select-library', $lopHoc->slug) }}"
                       class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-folder-open me-2"></i>Chọn từ thư viện
                    </a>
                    <a href="{{ route('teacher.classes.materials.create', $lopHoc->slug) }}"
                       class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-upload me-2"></i>Upload thẳng
                    </a>
                </div>
            </div>
        @else
            <div class="p-3 p-lg-4">
                <div class="d-flex flex-column gap-4">
                    @foreach($taiLieuGroups as $group)
                        <div class="batch-card rounded-4 overflow-hidden">
                            <div class="batch-header px-4 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $group->title }}</div>
                                        <div class="text-muted small mt-1">
                                            <i class="far fa-clock me-1"></i>{{ $group->sent_at?->format('d/m/Y H:i') ?? 'Chưa rõ thời gian gửi' }}
                                            <span class="mx-2">•</span>
                                            {{ $group->count }} tài liệu
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                        Đợt gửi tài liệu
                                    </span>
                                </div>
                            </div>

                            <div class="divide-y">
                                @foreach($group->items as $tl)
                                    <div class="file-item px-4 py-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-semibold text-dark">{{ $tl->tieuDe }}</span>
                                                    <span class="badge bg-primary-subtle text-primary nhom-badge rounded-pill px-3 py-1">
                                                        {{ $tl->nhom_label }}
                                                    </span>
                                                    @if($tl->giaoVienTaiLieuId)
                                                        <span class="badge bg-info-subtle text-info source-badge rounded-pill px-2">
                                                            <i class="fas fa-link me-1"></i>Từ thư viện
                                                        </span>
                                                    @endif
                                                    <span class="badge {{ $tl->trangThai ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-1">
                                                        {{ $tl->trang_thai_label }}
                                                    </span>
                                                </div>
                                                @if($tl->moTa)
                                                    <div class="text-muted small mt-2">{{ $tl->moTa }}</div>
                                                @endif
                                                <div class="d-flex align-items-center gap-3 mt-2 flex-wrap text-muted small">
                                                    <span class="font-monospace">{{ $tl->tenGoc }}</span>
                                                    <span>{{ $tl->kich_thuoc_readable }}</span>
                                                    <span>Tạo lúc {{ $tl->created_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-1">
                                                <a href="{{ route('teacher.classes.materials.download', [$lopHoc->slug, $tl->lopHocTaiLieuId]) }}"
                                                   class="btn btn-sm btn-light border rounded-circle" title="Tải xuống">
                                                    <i class="fas fa-download text-primary"></i>
                                                </a>
                                                <a href="{{ route('teacher.classes.materials.edit', [$lopHoc->slug, $tl->lopHocTaiLieuId]) }}"
                                                   class="btn btn-sm btn-light border rounded-circle" title="Sửa metadata">
                                                    <i class="fas fa-pen text-warning"></i>
                                                </a>
                                                <form method="POST"
                                                      action="{{ route('teacher.classes.materials.destroy', [$lopHoc->slug, $tl->lopHocTaiLieuId]) }}"
                                                      onsubmit="return confirm('{{ $tl->giaoVienTaiLieuId ? "Gỡ tài liệu này khỏi lớp? (File gốc trong thư viện vẫn giữ nguyên)" : "Xóa tài liệu này? Hành động không thể hoàn tác." }}')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border rounded-circle"
                                                            title="{{ $tl->giaoVienTaiLieuId ? 'Gỡ khỏi lớp' : 'Xóa' }}">
                                                        <i class="fas {{ $tl->giaoVienTaiLieuId ? 'fa-unlink' : 'fa-trash-alt' }} text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
