@extends('layouts.admin')

@section('title', 'Thùng rác · Giáo viên')
@section('page-title', 'Giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/giao-vien/index.css') }}">
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

        .gv-empty {
            padding: 56px 0;
            text-align: center;
            color: #8899a6;
        }

        .gv-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: .35;
            display: block;
            color: #16a34a;
        }

        .gv-empty p {
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')

    {{-- ── Banner ──────────────────────────────────────────────────────── --}}
    <div class="trash-banner">
        <div class="trash-banner-icon"><i class="fas fa-trash-can"></i></div>
        <div>
            <div class="trash-banner-title">Thùng rác · Giáo viên đã xóa</div>
            <div class="trash-banner-desc">
                Các giáo viên bên dưới đã bị xóa mềm. Dữ liệu lớp học vẫn được giữ nguyên.
                Bạn có thể khôi phục bất kỳ lúc nào.
            </div>
        </div>
        <span class="trash-count-badge">{{ $tongXoa }} đã xóa</span>
    </div>

    {{-- ── Header + back button ─────────────────────────────────────── --}}
    <div class="gv-page-header" style="margin-bottom:16px">
        <div class="gv-page-title">
            <i class="fas fa-trash-can me-2" style="color:#dc2626"></i>Thùng rác
            <span>{{ $giaoViens->total() }} kết quả</span>
        </div>
        <a href="{{ route('admin.giao-vien.index') }}" class="btn-back-list">
            <i class="fas fa-arrow-left"></i> Danh sách giáo viên
        </a>
    </div>

    {{-- ── Search bar ───────────────────────────────────────────────── --}}
    <form action="{{ route('admin.giao-vien.trash') }}" method="GET" class="gv-filter-bar" id="gv-filter-form"
        style="margin-bottom:20px">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email..."
                value="{{ request('q') }}" autocomplete="off">
        </div>
        <button type="submit" class="gv-btn-filter gv-btn-filter-primary">
            <i class="fas fa-filter"></i> Tìm
        </button>
        <a href="{{ route('admin.giao-vien.trash') }}" class="gv-btn-filter gv-btn-filter-reset">
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
    <div class="gv-card">
        <div class="gv-table-header">
            <div class="gv-table-title">
                <i class="fas fa-list me-2"></i>
                Giáo viên trong thùng rác
            </div>
            <div class="gv-table-count">
                Hiển thị {{ $giaoViens->firstItem() ?? 0 }}–{{ $giaoViens->lastItem() ?? 0 }}
                / {{ $giaoViens->total() }} bản ghi
            </div>
        </div>

        @if ($giaoViens->isEmpty())
            <div class="gv-empty">
                <i class="fas fa-circle-check"></i>
                <p>Thùng rác trống — không có giáo viên nào đã bị xóa.</p>
                <a href="{{ route('admin.giao-vien.index') }}" class="btn-back-list" style="margin-top:10px">
                    <i class="fas fa-arrow-left"></i> Về danh sách
                </a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="gv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Giáo viên</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Thời điểm xóa</th>
                            <th style="text-align:center">Khôi phục</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($giaoViens as $gv)
                            @php
                                $profile = $gv->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? $gv->taiKhoan;
                                $initials = strtoupper(mb_substr($hoTen, 0, 1));
                            @endphp
                            <tr style="opacity:.82">
                                <td style="color:#8899a6;font-size:.78rem">
                                    {{ $giaoViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="gv-info">
                                        <div class="gv-avatar" style="background:#fca5a5;color:#7f1d1d">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="gv-name">{{ $hoTen }}</div>
                                            <div class="gv-username">{{ $gv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $gv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    <span class="deleted-at-badge">
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($gv->deleted_at)->format('d/m/Y H:i') }}
                                    </span>
                                    <div style="font-size:.72rem;color:#aab8c2;margin-top:3px">
                                        {{ \Carbon\Carbon::parse($gv->deleted_at)->diffForHumans() }}
                                    </div>
                                </td>

                                <td style="text-align:center">
                                    <button type="button" class="btn-restore"
                                        onclick="confirmRestore('{{ $gv->taiKhoan }}', '{{ addslashes($hoTen) }}')">
                                        <i class="fas fa-rotate-left"></i> Khôi phục
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($giaoViens->hasPages())
                <div class="gv-pagination">
                    <div class="gv-pagination-info">
                        Trang {{ $giaoViens->currentPage() }} / {{ $giaoViens->lastPage() }}
                    </div>
                    {{ $giaoViens->links() }}
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
                title: 'Khôi phục giáo viên?',
                html: `Bạn có muốn khôi phục giáo viên <strong>${name}</strong>?<br>
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
                    form.action = `/admin/giao-vien/${id}/khoi-phuc`;
                    form.submit();
                }
            });
        }

        // Enter → submit search
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('gv-filter-form').submit();
        });
    </script>
@endsection
