@extends('layouts.admin')

@section('title', $baiViet->tieuDe)
@section('page-title', 'Bài Viết / Blog')
@section('breadcrumb', 'Nội dung · Bài viết · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/bai-viet/show.css') }}">
@endsection

@section('content')

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="bs-page-header">
        <div>
            <div class="bs-breadcrumb">
                <a href="{{ route('admin.bai-viet.index') }}"><i class="fas fa-newspaper me-1"></i> Bài viết</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Chi tiết
            </div>
            <div class="bs-page-title" style="margin-top:4px">
                {{ $baiViet->tieuDe }}
            </div>
        </div>
        <div class="bs-header-actions">
            <a href="{{ route('admin.bai-viet.edit', $baiViet->baiVietId) }}" class="bs-btn bs-btn-primary">
                <i class="fas fa-pen"></i> Chỉnh sửa
            </a>
            <a href="{{ route('home.blog.show', $baiViet->slug) }}" class="bs-btn bs-btn-warning" target="_blank">
                <i class="fas fa-external-link-alt"></i> Xem trên web
            </a>
            <a href="{{ route('admin.bai-viet.index') }}" class="bs-btn bs-btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if (session('success'))
        <div class="bv-alert-success"
            style="background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:12px 16px;border-radius:8px;font-size:.88rem;display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Info grid ─────────────────────────────────────────── --}}
    <div class="bs-info-grid">
        {{-- Main content --}}
        <div>
            {{-- Hero image --}}
            @if ($baiViet->anhDaiDien)
                <img src="{{ asset('storage/' . $baiViet->anhDaiDien) }}" alt="{{ $baiViet->tieuDe }}" class="bs-hero-img">
            @else
                <div class="bs-hero-placeholder">
                    <i class="fas fa-image"></i>
                </div>
            @endif

            {{-- Meta --}}
            <div class="bs-meta">
                <div class="bs-meta-item">
                    <i class="fas fa-user"></i>
                    {{ $baiViet->taiKhoan->hoSoNguoiDung->hoTen ?? ($baiViet->taiKhoan->taiKhoan ?? 'N/A') }}
                </div>
                <div class="bs-meta-item">
                    <i class="fas fa-calendar"></i>
                    {{ $baiViet->created_at ? $baiViet->created_at->format('d/m/Y H:i') : '—' }}
                </div>
                <div class="bs-meta-item">
                    <i class="fas fa-eye"></i>
                    {{ number_format($baiViet->luotXem) }} lượt xem
                </div>
                <div class="bs-meta-item">
                    @if ($baiViet->trangThai)
                        <span class="bs-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Đã xuất bản</span>
                    @else
                        <span class="bs-badge-draft"><i class="fas fa-circle" style="font-size:.4em"></i> Bản nháp</span>
                    @endif
                </div>
            </div>

            {{-- Summary --}}
            @if ($baiViet->tomTat)
                <div class="bs-card" style="background:#f8fafc;border-color:#e2e8f0">
                    <div style="font-size:.85rem;color:#475569;font-style:italic">
                        <i class="fas fa-quote-left" style="color:#94a3b8;margin-right:4px"></i>
                        {{ $baiViet->tomTat }}
                    </div>
                </div>
            @endif

            {{-- Content preview --}}
            <div class="bs-card">
                <div class="bs-card-title"><i class="fas fa-file-alt"></i> Nội dung bài viết</div>
                <div class="bs-content-preview">
                    {!! $baiViet->noiDung !!}
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Thống kê --}}
            <div class="bs-card">
                <div class="bs-card-title"><i class="fas fa-chart-bar"></i> Thông tin</div>

                <div class="bs-sidebar-stat">
                    <div class="bs-sidebar-stat-icon" style="background:#eff6ff;color:#1d4ed8">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <div class="bs-sidebar-stat-label">Lượt xem</div>
                        <div class="bs-sidebar-stat-value">{{ number_format($baiViet->luotXem) }}</div>
                    </div>
                </div>

                <div class="bs-sidebar-stat">
                    <div class="bs-sidebar-stat-icon" style="background:#f0fdf4;color:#059669">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div>
                        <div class="bs-sidebar-stat-label">Ngày tạo</div>
                        <div class="bs-sidebar-stat-value">
                            {{ $baiViet->created_at ? $baiViet->created_at->format('d/m/Y') : '—' }}</div>
                    </div>
                </div>

                <div class="bs-sidebar-stat">
                    <div class="bs-sidebar-stat-icon" style="background:#fefce8;color:#ca8a04">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="bs-sidebar-stat-label">Cập nhật lần cuối</div>
                        <div class="bs-sidebar-stat-value">
                            {{ $baiViet->updated_at ? $baiViet->updated_at->format('d/m/Y H:i') : '—' }}</div>
                    </div>
                </div>

                <div class="bs-sidebar-stat">
                    <div class="bs-sidebar-stat-icon" style="background:#faf5ff;color:#7c3aed">
                        <i class="fas fa-link"></i>
                    </div>
                    <div>
                        <div class="bs-sidebar-stat-label">Slug</div>
                        <div class="bs-sidebar-stat-value" style="font-size:.8rem;word-break:break-all">{{ $baiViet->slug }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh mục --}}
            <div class="bs-card">
                <div class="bs-card-title"><i class="fas fa-folder-open"></i> Danh mục</div>
                @if ($baiViet->danhMucs->isNotEmpty())
                    <div class="bs-tag-list">
                        @foreach ($baiViet->danhMucs as $dm)
                            <span class="bs-cat">{{ $dm->tenDanhMuc }}</span>
                        @endforeach
                    </div>
                @else
                    <p style="color:#94a3b8;font-size:.85rem">Chưa có danh mục</p>
                @endif
            </div>

            {{-- Tags --}}
            <div class="bs-card">
                <div class="bs-card-title"><i class="fas fa-tags"></i> Tags</div>
                @if ($baiViet->tags->isNotEmpty())
                    <div class="bs-tag-list">
                        @foreach ($baiViet->tags as $tag)
                            <span class="bs-tag">{{ $tag->tenTag }}</span>
                        @endforeach
                    </div>
                @else
                    <p style="color:#94a3b8;font-size:.85rem">Chưa có tag</p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bs-card">
                <div class="bs-card-title"><i class="fas fa-cog"></i> Thao tác</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="{{ route('admin.bai-viet.edit', $baiViet->baiVietId) }}" class="bs-btn bs-btn-primary"
                        style="justify-content:center">
                        <i class="fas fa-pen"></i> Chỉnh sửa
                    </a>
                    <button type="button" class="bs-btn bs-btn-danger" style="justify-content:center"
                        onclick="confirmDeleteBV()">
                        <i class="fas fa-trash"></i> Xóa bài viết
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- Hidden delete form --}}
<form id="delete-bv-form" action="{{ route('admin.bai-viet.destroy', $baiViet->baiVietId) }}" method="POST"
    style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDeleteBV() {
            Swal.fire({
                title: 'Xóa bài viết?',
                html: `Xóa <strong>{{ addslashes($baiViet->tieuDe) }}</strong>?<br><small style="color:#64748b">Hành động này không thể hoàn tác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('delete-bv-form').submit();
                }
            });
        }
    </script>
@endsection