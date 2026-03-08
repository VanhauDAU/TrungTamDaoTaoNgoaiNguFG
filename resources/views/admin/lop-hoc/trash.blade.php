@extends('layouts.admin')

@section('title', 'Thùng rác lớp học')
@section('page-title', 'Lớp Học')
@section('breadcrumb', 'Quản lý · Lớp học · Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lop-hoc/index.css') }}">
    <style>
        .lh-trash-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #fff7ed, #fef2f2);
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 20px;
        }

        .lh-trash-banner-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .lh-trash-banner-title {
            font-size: .95rem;
            font-weight: 700;
            color: #1e293b;
        }

        .lh-trash-banner-desc {
            font-size: .8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .lh-trash-count {
            margin-left: auto;
            background: #dc2626;
            color: #fff;
            border-radius: 999px;
            padding: 5px 14px;
            font-size: .82rem;
            font-weight: 700;
        }

        .lh-trash-deleted-at {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: .74rem;
            font-weight: 600;
        }

        .lh-trash-empty {
            padding: 56px 0;
            text-align: center;
            color: #64748b;
        }

        .lh-trash-empty i {
            font-size: 2.4rem;
            display: block;
            margin-bottom: 12px;
            opacity: .35;
            color: #16a34a;
        }

        .lh-restore-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 8px;
            padding: 7px 12px;
            background: #dcfce7;
            color: #166534;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .lh-restore-btn:hover {
            background: #bbf7d0;
        }
    </style>
@endsection

@section('content')

    <div class="lh-trash-banner">
        <div class="lh-trash-banner-icon"><i class="fas fa-trash-can"></i></div>
        <div>
            <div class="lh-trash-banner-title">Thùng rác lớp học</div>
            <div class="lh-trash-banner-desc">
                Các lớp học bên dưới đã được xóa mềm. Bạn có thể khôi phục lại để tiếp tục quản lý.
            </div>
        </div>
        <div class="lh-trash-count">{{ $tongDaXoa }} lớp</div>
    </div>

    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-trash-can" style="color:#dc2626"></i>
            Lớp học đã xóa
            <span>{{ $lopHocs->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.lop-hoc.index') }}" class="btn-add-lh" style="background:#64748b">
                <i class="fas fa-arrow-left"></i> Danh sách lớp
            </a>
        </div>
    </div>

    @if (session('success'))
        <div
            style="background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:12px 16px;border-radius:8px;margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            style="background:#fff1f2;border:1px solid #fecdd3;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.lop-hoc.trash') }}" method="GET" class="lh-filter-bar" id="lh-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm tên lớp, mã lớp, khóa học..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <button type="submit" class="lh-btn-filter lh-btn-filter-primary">
            <i class="fas fa-filter"></i> Tìm
        </button>
        <a href="{{ route('admin.lop-hoc.trash') }}" class="lh-btn-filter lh-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    <div class="lh-card">
        <div class="lh-table-header">
            <div class="lh-table-title"><i class="fas fa-list me-2"></i> Lớp học trong thùng rác</div>
            <div style="font-size:.82rem;color:#94a3b8">
                Hiển thị {{ $lopHocs->firstItem() ?? 0 }}–{{ $lopHocs->lastItem() ?? 0 }} / {{ $lopHocs->total() }}
            </div>
        </div>

        @if ($lopHocs->isEmpty())
            <div class="lh-trash-empty">
                <i class="fas fa-circle-check"></i>
                <p>Thùng rác đang trống.</p>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Tên lớp</th>
                            <th>Khóa học</th>
                            <th>Cơ sở</th>
                            <th>Giáo viên</th>
                            <th>Thời điểm xóa</th>
                            <th style="text-align:center">Khôi phục</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lopHocs as $lop)
                            <tr style="opacity:.86">
                                <td style="color:#94a3b8;font-size:.78rem">{{ $lopHocs->firstItem() + $loop->index }}</td>
                                <td>
                                    <div style="font-weight:700;color:#334155">
                                        <span class="badge"
                                            style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;font-size:0.75rem;padding:2px 6px;margin-right:5px;border-radius:4px;">
                                            {{ $lop->maLopHoc }}
                                        </span>
                                        {{ $lop->tenLopHoc }}
                                    </div>
                                    <div style="font-size:.75rem;color:#94a3b8;margin-top:4px">
                                        Trạng thái trước khi xóa: {{ $lop->trangThaiLabel }}
                                    </div>
                                </td>
                                <td style="font-size:.82rem">{{ $lop->khoaHoc?->tenKhoaHoc ?? '—' }}</td>
                                <td style="font-size:.82rem">{{ $lop->coSo?->tenCoSo ?? '—' }}</td>
                                <td style="font-size:.82rem">{{ $lop->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</td>
                                <td>
                                    <span class="lh-trash-deleted-at">
                                        <i class="fas fa-clock"></i>
                                        {{ optional($lop->deleted_at)->format('d/m/Y H:i') }}
                                    </span>
                                    <div style="font-size:.72rem;color:#94a3b8;margin-top:5px">
                                        {{ optional($lop->deleted_at)?->diffForHumans() }}
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <button type="button"
                                        class="lh-restore-btn js-restore-lh"
                                        data-restore-url="{{ route('admin.lop-hoc.restore', $lop->slug) }}"
                                        data-name="{{ e($lop->tenLopHoc) }}">
                                        <i class="fas fa-rotate-left"></i> Khôi phục
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($lopHocs->hasPages())
                <div class="lh-pagination">
                    <div class="lh-pagination-info">
                        Trang {{ $lopHocs->currentPage() }} / {{ $lopHocs->lastPage() }}
                    </div>
                    {{ $lopHocs->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

<form id="restore-lh-form" method="POST" style="display:none">
    @csrf
    @method('PATCH')
</form>

@section('script')
    <script>
        document.querySelectorAll('.js-restore-lh').forEach(button => {
            button.addEventListener('click', () => {
                Swal.fire({
                    title: 'Khôi phục lớp học?',
                    html: `Khôi phục <strong>${button.dataset.name}</strong> từ thùng rác?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-rotate-left me-1"></i> Khôi phục',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('restore-lh-form');
                        form.action = button.dataset.restoreUrl;
                        form.submit();
                    }
                });
            });
        });

        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('lh-filter-form').submit();
        });
    </script>
@endsection
