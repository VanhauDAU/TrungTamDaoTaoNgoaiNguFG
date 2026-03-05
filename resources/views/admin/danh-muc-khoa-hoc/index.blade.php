@extends('layouts.admin')
@section('title', 'Quản lý Danh Mục Khóa Học')
@section('page-title', 'Danh Mục Khóa Học')
@section('breadcrumb', 'Quản lý · Danh mục khóa học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/danh-muc-khoa-hoc/index.css') }}">
@endsection

@section('content')

    <div class="dm-page-header">
        <div class="dm-page-title">
            <i class="fas fa-sitemap" style="color:#0f766e"></i>
            Danh mục khóa học
            <span>{{ $tongSo }} danh mục</span>
        </div>
        <a href="{{ route('admin.danh-muc-khoa-hoc.create') }}" class="btn-add-dm">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>

    {{-- Stats --}}
    <div class="dm-stats">
        <div class="dm-stat-card">
            <div class="dm-stat-icon total"><i class="fas fa-sitemap"></i></div>
            <div>
                <div class="dm-stat-value">{{ $tongSo }}</div>
                <div class="dm-stat-label">Tổng danh mục</div>
            </div>
        </div>
        <div class="dm-stat-card">
            <div class="dm-stat-icon active"><i class="fas fa-folder"></i></div>
            <div>
                <div class="dm-stat-value">{{ $tongCha }}</div>
                <div class="dm-stat-label">Danh mục cha</div>
            </div>
        </div>
        <div class="dm-stat-card">
            <div class="dm-stat-icon" style="background:rgba(245,158,11,.12);color:#d97706"><i
                    class="fas fa-folder-open"></i></div>
            <div>
                <div class="dm-stat-value">{{ $tongCon }}</div>
                <div class="dm-stat-label">Danh mục con</div>
            </div>
        </div>
        <div class="dm-stat-card">
            <div class="dm-stat-icon course"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <div class="dm-stat-value">{{ $tongKhoaHoc }}</div>
                <div class="dm-stat-label">Tổng khóa học</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form action="{{ route('admin.danh-muc-khoa-hoc.index') }}" method="GET" class="dm-filter-bar" id="dm-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm tên danh mục..."
                value="{{ request('q') }}" autocomplete="off">
        </div>
        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
        </select>
        <button type="submit" class="dm-btn-filter dm-btn-filter-primary"><i class="fas fa-filter"></i> Lọc</button>
        <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="dm-btn-filter dm-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- Tree Table --}}
    @if ($roots->isEmpty())
        <div class="dm-empty">
            <i class="fas fa-sitemap"></i>
            <p>Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!</p>
            <a href="{{ route('admin.danh-muc-khoa-hoc.create') }}" class="btn-add-dm"
                style="margin-top:16px;display:inline-flex">
                <i class="fas fa-plus"></i> Thêm danh mục
            </a>
        </div>
    @else
        <div class="dm-table-wrap">
            <table class="dm-table">
                <thead>
                    <tr>
                        <th style="width:36px"></th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th style="text-align:center">Khóa học</th>
                        <th style="text-align:center">Trạng thái</th>
                        <th style="text-align:center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roots as $root)
                        {{-- ── ROW CHA ────────────────────────────── --}}
                        <tr class="dm-row-parent {{ $root->childrenRecursive->isNotEmpty() ? 'has-children' : '' }}"
                            data-tree-id="{{ $root->danhMucId }}">
                            <td style="text-align:center">
                                @if ($root->childrenRecursive->isNotEmpty())
                                    <button type="button" class="dm-toggle-tree"
                                        onclick="toggleChildren({{ $root->danhMucId }}, this)" title="Thu gọn/Mở rộng">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                @else
                                    <span style="color:#d1d5db;font-size:.8rem"><i class="fas fa-minus"></i></span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:.5rem">
                                    <span class="dm-node-icon dm-node-root"><i class="fas fa-folder"></i></span>
                                    <span style="font-weight:700;color:#1e293b">{{ $root->tenDanhMuc }}</span>
                                    @if ($root->childrenRecursive->isNotEmpty())
                                        <span class="dm-children-count">{{ $root->childrenRecursive->count() }} con</span>
                                    @endif
                                </div>
                            </td>
                            <td style="color:#0f766e;font-family:monospace;font-size:.83rem">{{ $root->slug }}</td>
                            <td style="color:#64748b;max-width:260px">{{ Str::limit($root->moTa, 60) ?: '—' }}</td>
                            <td style="text-align:center">
                                @php
                                    $totalKhoa =
                                        $root->khoaHocs_count +
                                        $root->childrenRecursive->sum(fn($c) => $c->khoaHocs->count());
                                @endphp
                                <span class="dm-count-badge">{{ $totalKhoa }} khóa</span>
                            </td>
                            <td style="text-align:center">
                                @if ($root->trangThai)
                                    <span class="dm-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Hoạt
                                        động</span>
                                @else
                                    <span class="dm-badge-inactive"><i class="fas fa-circle" style="font-size:.4em"></i>
                                        Ngừng</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <div class="dm-actions" style="justify-content:center">
                                    <a href="{{ route('admin.danh-muc-khoa-hoc.edit', $root->slug) }}"
                                        class="dm-btn-action dm-btn-edit" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="dm-btn-action dm-btn-del" title="Xóa"
                                        onclick="confirmDeleteDM({{ $root->danhMucId }}, '{{ addslashes($root->tenDanhMuc) }}', {{ $root->khoaHocs_count }}, {{ $root->childrenRecursive->count() }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- ── CÁC ROW CON (đệ quy nhiều cấp) ──── --}}
                        @foreach ($root->childrenRecursive as $child)
                            @include('admin.danh-muc-khoa-hoc._tree-row', [
                                'node' => $child,
                                'depth' => 1,
                                'parentId' => $root->danhMucId,
                            ])
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

<form id="delete-dm-form" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

@section('script')
    <script>
        // Thu gọn / mở rộng cây đệ quy
        function setSubtreeVisible(parentId, visible) {
            // Tìm tất cả hàng con trực tiếp
            const rows = document.querySelectorAll(`.dm-row-child[data-parent="${parentId}"]`);
            rows.forEach(row => {
                row.style.display = visible ? '' : 'none';
                const childId = row.getAttribute('data-tree-id');
                if (childId) {
                    if (!visible) {
                        // Thu gọn: đóng luôn button toggle bên trong
                        const innerBtn = row.querySelector('.dm-toggle-tree i');
                        if (innerBtn) {
                            innerBtn.classList.remove('fa-chevron-down');
                            innerBtn.classList.add('fa-chevron-right');
                        }
                    }
                    // Đệ quy vào các con cháu
                    setSubtreeVisible(childId, visible);
                }
            });
        }

        function toggleChildren(parentId, btn) {
            const icon = btn.querySelector('i');
            const isOpen = icon.classList.contains('fa-chevron-down');
            setSubtreeVisible(parentId, !isOpen);
            icon.classList.toggle('fa-chevron-down', !isOpen);
            icon.classList.toggle('fa-chevron-right', isOpen);
        }

        function confirmDeleteDM(id, name, soKH, soKon) {
            if (soKH > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `<strong>${name}</strong> còn <strong>${soKH} khóa học</strong>.<br><small>Hãy chuyển khóa học sang danh mục khác trước.</small>`,
                    icon: 'warning',
                    confirmButtonColor: '#6c757d',
                    confirmButtonText: 'Đóng'
                });
                return;
            }
            if (soKon > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `<strong>${name}</strong> còn <strong>${soKon} danh mục con</strong>.<br><small>Hãy xóa hoặc chuyển danh mục con trước.</small>`,
                    icon: 'warning',
                    confirmButtonColor: '#6c757d',
                    confirmButtonText: 'Đóng'
                });
                return;
            }
            Swal.fire({
                title: 'Xóa danh mục?',
                html: `Xóa <strong>${name}</strong>?<br><small style="color:#64748b">Hành động này không thể hoàn tác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('delete-dm-form');
                    form.action = `/admin/danh-muc-khoa-hoc/${id}`;
                    form.submit();
                }
            });
        }

        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('dm-filter-form').submit();
        });
    </script>
@endsection
