@extends('layouts.internal')

@section('title', 'Tài liệu lớp: ' . $lopHoc->tenLopHoc)
@section('page-title', 'Tài liệu lớp học')
@section('breadcrumb', 'Lớp học / ' . $lopHoc->tenLopHoc . ' / Tài liệu')

@section('stylesheet')
<style>
    .material-row { transition: background .15s; }
    .material-row:hover { background: #f8fafc; }
    .nhom-badge { font-size: .72rem; letter-spacing: .03em; }
    .source-badge { font-size: .65rem; }
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

    {{-- Bảng danh sách --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        @if($taiLieus->isEmpty())
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase" style="font-size:.75rem">Tài liệu</th>
                            <th class="py-3 text-secondary text-uppercase" style="font-size:.75rem">Nhóm</th>
                            <th class="py-3 text-secondary text-uppercase" style="font-size:.75rem">Kích thước</th>
                            <th class="py-3 text-secondary text-uppercase" style="font-size:.75rem">Trạng thái</th>
                            <th class="py-3 text-secondary text-uppercase" style="font-size:.75rem">Ngày chia sẻ</th>
                            <th class="py-3 pe-4 text-end text-secondary text-uppercase" style="font-size:.75rem">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taiLieus as $tl)
                            <tr class="material-row">
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $tl->tieuDe }}</div>
                                    @if($tl->moTa)
                                        <div class="text-muted small text-truncate" style="max-width:260px">{{ $tl->moTa }}</div>
                                    @endif
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="text-muted small font-monospace">{{ $tl->tenGoc }}</span>
                                        @if($tl->giaoVienTaiLieuId)
                                            <span class="badge bg-info-subtle text-info source-badge rounded-pill px-2">
                                                <i class="fas fa-link me-1"></i>Từ thư viện
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary nhom-badge rounded-pill px-3 py-1">
                                        {{ $tl->nhom_label }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $tl->kich_thuoc_readable }}</td>
                                <td>
                                    @if($tl->trangThai)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Hiển thị</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $tl->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex gap-1 justify-content-end">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
