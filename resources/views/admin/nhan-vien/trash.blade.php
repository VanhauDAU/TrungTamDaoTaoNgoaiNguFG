@extends('layouts.admin')

@section('title', 'Thùng rác · Nhân viên')
@section('page-title', 'Nhân viên')
@section('breadcrumb', 'Quản lý nhân viên · Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/nhan-vien/index.css') }}">
    <style>
        .trash-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #eef2ff, #fef2f2);
            border: 1.5px solid #c7d2fe;
            border-radius: 14px;
            padding: 18px 24px;
            margin-bottom: 24px;
        }

        .trash-banner-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .trash-banner-title {
            font-weight: 700;
            font-size: 0.97rem;
            color: #1e293b;
        }

        .trash-banner-desc {
            font-size: 0.8rem;
            color: #8899a6;
            margin-top: 2px;
        }

        .trash-count-badge {
            margin-left: auto;
            background: #dc2626;
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 4px 14px;
            border-radius: 20px;
        }

        .btn-back-list {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 18px;
            border-radius: 8px;
            background: #f0f4f8;
            color: #546e8a;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .18s;
        }

        .btn-back-list:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-restore {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 7px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .18s;
        }

        .btn-restore:hover {
            background: #c7d2fe;
        }

        .deleted-at-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: #dc2626;
            background: #fee2e2;
            padding: 2px 9px;
            border-radius: 20px;
        }

        .nv-empty {
            padding: 56px 0;
            text-align: center;
            color: #8899a6;
        }

        .nv-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: .35;
            display: block;
            color: #6366f1;
        }

        .nv-empty p {
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')

    {{-- ── Banner ──────────────────────────────────────────────────────── --}}
    <div class="trash-banner">
        <div class="trash-banner-icon"><i class="fas fa-trash-can"></i></div>
        <div>
            <div class="trash-banner-title">Thùng rác · Nhân viên đã xóa</div>
            <div class="trash-banner-desc">
                Các nhân viên bên dưới đã bị xóa mềm.
                Bạn có thể khôi phục bất kỳ lúc nào.
            </div>
        </div>
        <span class="trash-count-badge">{{ $tongXoa }} đã xóa</span>
    </div>

    {{-- ── Header + back button ─────────────────────────────────────── --}}
    <div class="nv-page-header" style="margin-bottom:16px">
        <div class="nv-page-title">
            <i class="fas fa-trash-can me-2" style="color:#dc2626"></i>Thùng rác
            <span>{{ $nhanViens->total() }} kết quả</span>
        </div>
        <a href="{{ route('admin.nhan-vien.index') }}" class="btn-back-list">
            <i class="fas fa-arrow-left"></i> Danh sách nhân viên
        </a>
    </div>

    {{-- ── Search bar ───────────────────────────────────────────────── --}}
    <form action="{{ route('admin.nhan-vien.trash') }}" method="GET" class="nv-filter-bar" id="nv-filter-form"
        style="margin-bottom:20px">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email..."
                value="{{ request('q') }}" autocomplete="off">
        </div>
        <button type="submit" class="nv-btn-filter nv-btn-filter-primary">
            <i class="fas fa-filter"></i> Tìm
        </button>
        <a href="{{ route('admin.nhan-vien.trash') }}" class="nv-btn-filter nv-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Flash ───────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            style="background:#e0e7ff;border:1px solid #c7d2fe;border-radius:10px;
                    padding:12px 18px;margin-bottom:18px;font-size:0.86rem;color:#3730a3;
                    display:flex;align-items:center;gap:9px">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Table ───────────────────────────────────────────────────── --}}
    <div class="nv-card">
        <div class="nv-table-header">
            <div class="nv-table-title">
                <i class="fas fa-list me-2"></i>
                Nhân viên trong thùng rác
            </div>
            <div class="nv-table-count">
                Hiển thị {{ $nhanViens->firstItem() ?? 0 }}–{{ $nhanViens->lastItem() ?? 0 }}
                / {{ $nhanViens->total() }} bản ghi
            </div>
        </div>

        @if ($nhanViens->isEmpty())
            <div class="nv-empty">
                <i class="fas fa-circle-check"></i>
                <p>Thùng rác trống — không có nhân viên nào đã bị xóa.</p>
                <a href="{{ route('admin.nhan-vien.index') }}" class="btn-back-list" style="margin-top:10px">
                    <i class="fas fa-arrow-left"></i> Về danh sách
                </a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="nv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Nhân viên</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Thời điểm xóa</th>
                            <th style="text-align:center">Khôi phục</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($nhanViens as $nv)
                            @php
                                $profile = $nv->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? $nv->taiKhoan;
                                $initials = strtoupper(mb_substr($hoTen, 0, 1));
                            @endphp
                            <tr style="opacity:.82">
                                <td style="color:#8899a6;font-size:.78rem">
                                    {{ $nhanViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="nv-info">
                                        <div class="nv-avatar" style="background:#fca5a5;color:#7f1d1d">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="nv-name">{{ $hoTen }}</div>
                                            <div class="nv-username">{{ $nv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $nv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    <span class="deleted-at-badge">
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($nv->deleted_at)->format('d/m/Y H:i') }}
                                    </span>
                                    <div style="font-size:.72rem;color:#aab8c2;margin-top:3px">
                                        {{ \Carbon\Carbon::parse($nv->deleted_at)->diffForHumans() }}
                                    </div>
                                </td>

                                <td style="text-align:center">
                                    <button type="button" class="btn-restore"
                                        onclick="confirmRestore({{ $nv->taiKhoanId }}, '{{ addslashes($hoTen) }}')">
                                        <i class="fas fa-rotate-left"></i> Khôi phục
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($nhanViens->hasPages())
                <div class="nv-pagination">
                    <div class="nv-pagination-info">
                        Trang {{ $nhanViens->currentPage() }} / {{ $nhanViens->lastPage() }}
                    </div>
                    {{ $nhanViens->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

{{-- Hidden RESTORE form (PATCH) --}}
<form id="restore-form" method="POST" style="display:none">
    @csrf
    @method('PATCH')
</form>

@section('script')
    <script>
        function confirmRestore(id, name) {
            Swal.fire({
                title: 'Khôi phục nhân viên?',
                html: `Bạn có muốn khôi phục nhân viên <strong>${name}</strong>?<br>
                   <small style="color:#8899a6">Tài khoản sẽ hoạt động bình thường trở lại.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-rotate-left me-1"></i> Khôi phục',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('restore-form');
                    form.action = `/admin/nhan-vien/${id}/khoi-phuc`;
                    form.submit();
                }
            });
        }

        // Enter → submit search
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('nv-filter-form').submit();
        });
    </script>
@endsection
