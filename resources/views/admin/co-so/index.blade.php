@extends('layouts.admin')

@section('title', 'Cơ sở & Phòng học')
@section('page-title', 'Cơ sở Đào tạo')
@section('breadcrumb', 'Cấu hình hệ thống · Cơ sở & Phòng học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/co-so/index.css') }}">
@endsection

@section('content')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div
            style="background:#dcfce7; color:#16a34a; padding:12px 20px; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            style="background:#fee2e2; color:#dc2626; padding:12px 20px; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="cs-header">
        <div class="cs-title">
            <i class="fas fa-building" style="color:#27c4b5;"></i> Danh sách Cơ sở đào tạo
        </div>
        <a href="{{ route('admin.co-so.create') }}" class="btn-add">
            <i class="fas fa-plus"></i> Thêm cơ sở mới
        </a>
    </div>

    {{-- Thống kê --}}
    <div class="cs-stats">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-building-user"></i></div>
            <div>
                <div class="stat-value">{{ $tongSo }}</div>
                <div class="stat-label">Tổng số cơ sở</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $hoatDong }}</div>
                <div class="stat-label">Cơ sở đang hoạt động</div>
            </div>
        </div>
    </div>

    {{-- Lọc --}}
    <form action="{{ route('admin.co-so.index') }}" method="GET" class="cs-filter">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm mã, tên, địa chỉ cơ sở..."
                autocomplete="off">
        </div>

        <select name="trangThai">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
        </select>

        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Lọc</button>
        @if (request()->anyFilled(['q', 'trangThai']))
            <a href="{{ route('admin.co-so.index') }}" class="btn-reset"><i class="fas fa-times"></i> Đặt lại</a>
        @endif
    </form>

    {{-- Danh sách --}}
    @if ($coSos->isEmpty())
        <div class="empty-state">
            <i class="fas fa-city"></i>
            <p>Không tìm thấy cơ sở đào tạo nào phù hợp.</p>
            @if (!request()->anyFilled(['q', 'trangThai']))
                <a href="{{ route('admin.co-so.create') }}" class="btn-add">Thêm cơ sở đầu tiên</a>
            @endif
        </div>
    @else
        <div class="cs-grid">
            @foreach ($coSos as $cs)
                <div class="cs-card">
                    <div class="cs-card-header">
                        <span class="cs-code">{{ $cs->maCoSo }}</span>
                        @if ($cs->trangThai == 1)
                            <span class="cs-badge active"><i class="fas fa-circle" style="font-size:0.5em;"></i> Hoạt
                                động</span>
                        @else
                            <span class="cs-badge inactive"><i class="fas fa-circle" style="font-size:0.5em;"></i> Tạm
                                ngưng</span>
                        @endif
                    </div>
                    <div class="cs-card-body">
                        <a href="{{ route('admin.co-so.show', $cs->coSoId) }}" class="cs-name"
                            title="{{ $cs->tenCoSo }}">
                            {{ $cs->tenCoSo }}
                        </a>

                        <div class="cs-info-row">
                            <i class="fas fa-location-dot"></i>
                            <span>{{ $cs->diaChi }}</span>
                        </div>
                        <div class="cs-info-row">
                            <i class="fas fa-phone"></i>
                            <span>{{ $cs->soDienThoai ?: 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                    <div class="cs-card-footer">
                        <div class="cs-room-count">
                            <i class="fas fa-chalkboard-user" style="color:#64748b;"></i>
                            Số phòng học: <span>{{ $cs->phong_hocs_count }}</span>
                        </div>
                        <div class="cs-actions">
                            <a href="{{ route('admin.co-so.show', $cs->coSoId) }}" class="btn-act view"
                                title="Quản lý phòng học">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.co-so.edit', $cs->coSoId) }}" class="btn-act edit" title="Chỉnh sửa">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button class="btn-act del" title="Xóa"
                                onclick="confirmDelete({{ $cs->coSoId }}, '{{ addslashes($cs->tenCoSo) }}', {{ $cs->phong_hocs_count }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($coSos->hasPages())
            <div style="margin-top: 24px;">
                {{ $coSos->links() }}
            </div>
        @endif
    @endif

@endsection

<form id="delete-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDelete(id, name, roomCount) {
            if (roomCount > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `Cơ sở <strong>${name}</strong> vẫn còn <strong>${roomCount}</strong> phòng học.<br>Vui lòng xóa các phòng học trước.`,
                    icon: 'error',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                title: 'Xóa cơ sở đào tạo?',
                html: `Bạn có chắc chắn muốn xóa cơ sở <strong>${name}</strong>? Hành động này không thể hoàn tác.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Có, Xóa!',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = `/admin/co-so/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endsection
