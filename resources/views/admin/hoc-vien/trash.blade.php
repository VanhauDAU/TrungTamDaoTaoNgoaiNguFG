@extends('layouts.admin')

@section('title', 'Thùng rác · Học viên')
@section('page-title', 'Học viên')
@section('breadcrumb', 'Quản lý học viên · Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoc-vien/index.css') }}">
    <style>
        .trash-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #fff7ed, #fef2f2);
            border: 1.5px solid #fca5a5;
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
            background: #d1fae5;
            color: #065f46;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .18s;
        }

        .btn-restore:hover {
            background: #a7f3d0;
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

        .hv-empty {
            padding: 56px 0;
            text-align: center;
            color: #8899a6;
        }

        .hv-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: .35;
            display: block;
            color: #16a34a;
        }

        .hv-empty p {
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')

    {{-- ── Banner ──────────────────────────────────────────────────────── --}}
    <div class="trash-banner">
        <div class="trash-banner-icon"><i class="fas fa-trash-can"></i></div>
        <div>
            <div class="trash-banner-title">Thùng rác · Học viên đã xóa</div>
            <div class="trash-banner-desc">
                Các học viên bên dưới đã bị xóa mềm. Dữ liệu lớp học và hóa đơn vẫn được giữ nguyên.
                Bạn có thể khôi phục bất kỳ lúc nào.
            </div>
        </div>
        <span class="trash-count-badge">{{ $tongXoa }} đã xóa</span>
    </div>

    {{-- ── Header + back button ─────────────────────────────────────── --}}
    <div class="hv-page-header" style="margin-bottom:16px">
        <div class="hv-page-title">
            <i class="fas fa-trash-can me-2" style="color:#dc2626"></i>Thùng rác
            <span>{{ $hocViens->total() }} kết quả</span>
        </div>
        <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back-list">
            <i class="fas fa-arrow-left"></i> Danh sách học viên
        </a>
    </div>

    {{-- ── Search bar ───────────────────────────────────────────────── --}}
    <form action="{{ route('admin.hoc-vien.trash') }}" method="GET" class="hv-filter-bar" id="filter-form"
        style="margin-bottom:20px">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email..."
                value="{{ request('q') }}" autocomplete="off">
        </div>
        <button type="submit" class="btn-filter btn-filter-primary">
            <i class="fas fa-filter"></i> Tìm
        </button>
        <a href="{{ route('admin.hoc-vien.trash') }}" class="btn-filter btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Flash ───────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;
                    padding:12px 18px;margin-bottom:18px;font-size:0.86rem;color:#065f46;
                    display:flex;align-items:center;gap:9px">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Table ───────────────────────────────────────────────────── --}}
    <div class="hv-card">
        <div class="hv-table-header">
            <div class="hv-table-title">
                <i class="fas fa-list me-2"></i>
                Học viên trong thùng rác
            </div>
            <div class="hv-table-count">
                Hiển thị {{ $hocViens->firstItem() ?? 0 }}–{{ $hocViens->lastItem() ?? 0 }}
                / {{ $hocViens->total() }} bản ghi
            </div>
        </div>

        @if ($hocViens->isEmpty())
            <div class="hv-empty">
                <i class="fas fa-circle-check"></i>
                <p>Thùng rác trống — không có học viên nào đã bị xóa.</p>
                <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back-list" style="margin-top:10px">
                    <i class="fas fa-arrow-left"></i> Về danh sách
                </a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="hv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Học viên</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Thời điểm xóa</th>
                            <th style="text-align:center">Khôi phục</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hocViens as $hv)
                            @php
                                $profile = $hv->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? $hv->taiKhoan;
                                $initials = strtoupper(mb_substr($hoTen, 0, 1));
                            @endphp
                            <tr style="opacity:.82">
                                <td style="color:#8899a6;font-size:.78rem">
                                    {{ $hocViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="hv-info">
                                        <div class="hv-avatar" style="background:#fca5a5;color:#7f1d1d">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="hv-name">{{ $hoTen }}</div>
                                            <div class="hv-username">{{ $hv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $hv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    <span class="deleted-at-badge">
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($hv->deleted_at)->format('d/m/Y H:i') }}
                                    </span>
                                    <div style="font-size:.72rem;color:#aab8c2;margin-top:3px">
                                        {{ \Carbon\Carbon::parse($hv->deleted_at)->diffForHumans() }}
                                    </div>
                                </td>

                                <td style="text-align:center">
                                    <button type="button" class="btn-restore"
                                        onclick="confirmRestore({{ $hv->taiKhoanId }}, '{{ addslashes($hoTen) }}')">
                                        <i class="fas fa-rotate-left"></i> Khôi phục
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($hocViens->hasPages())
                <div class="hv-pagination">
                    <div class="hv-pagination-info">
                        Trang {{ $hocViens->currentPage() }} / {{ $hocViens->lastPage() }}
                    </div>
                    {{ $hocViens->links() }}
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
                title: 'Khôi phục học viên?',
                html: `Bạn có muốn khôi phục học viên <strong>${name}</strong>?<br>
                   <small style="color:#8899a6">Tài khoản sẽ hoạt động bình thường trở lại.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-rotate-left me-1"></i> Khôi phục',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('restore-form');
                    form.action = `/admin/hoc-vien/${id}/khoi-phuc`;
                    form.submit();
                }
            });
        }

        // Enter → submit search
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection
