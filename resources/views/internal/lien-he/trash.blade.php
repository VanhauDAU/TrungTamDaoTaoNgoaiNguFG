@extends('layouts.admin')

@section('title', 'Thùng rác · Liên hệ')
@section('page-title', 'Thùng rác liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Thùng rác liên hệ')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/index.css') }}">
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
    </style>
@endsection

@section('content')
    <div class="trash-banner">
        <div class="trash-banner-icon"><i class="fas fa-trash-can"></i></div>
        <div>
            <div style="font-weight:700;font-size:0.97rem;color:#1e293b">Thùng rác · Liên hệ đã xóa</div>
            <div style="font-size:0.8rem;color:#8899a6;margin-top:2px">Danh sách liên hệ đã xóa mềm. @if(!$canManage) Admin chỉ xem lịch sử xóa và trạng thái phục hồi. @endif</div>
        </div>
        <span class="trash-count-badge">{{ $tongXoa }} đã xóa</span>
    </div>

    <div class="lh-page-header" style="margin-bottom:16px">
        <div class="lh-page-title"><i class="fas fa-trash-can me-2" style="color:#dc2626"></i>Thùng rác <span>{{ $lienHes->total() }} kết quả</span></div>
        <a href="{{ route($portalRoutePrefix . '.lien-he.index') }}" class="btn-back-list"><i class="fas fa-arrow-left"></i> Danh sách liên hệ</a>
    </div>

    <form action="{{ route($portalRoutePrefix . '.lien-he.trash') }}" method="GET" class="lh-filter-bar" id="lh-filter-form" style="margin-bottom:20px">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email, số điện thoại..." value="{{ request('q') }}" autocomplete="off">
        </div>
        <button type="submit" class="btn-filter btn-filter-primary"><i class="fas fa-filter"></i> Tìm</button>
        <a href="{{ route($portalRoutePrefix . '.lien-he.trash') }}" class="btn-filter btn-filter-reset"><i class="fas fa-times"></i> Đặt lại</a>
    </form>

    <div class="lh-card">
        <div class="lh-table-header">
            <div class="lh-table-title"><i class="fas fa-list me-2"></i> Liên hệ trong thùng rác</div>
            <div class="lh-table-count">Hiển thị {{ $lienHes->firstItem() ?? 0 }}-{{ $lienHes->lastItem() ?? 0 }} / {{ $lienHes->total() }} bản ghi</div>
        </div>

        @if ($lienHes->isEmpty())
            <div class="lh-empty">
                <i class="fas fa-circle-check"></i>
                <p>Thùng rác trống.</p>
                <a href="{{ route($portalRoutePrefix . '.lien-he.index') }}" class="btn-back-list" style="margin-top:10px"><i class="fas fa-arrow-left"></i> Về danh sách</a>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Người gửi</th>
                            <th>Email & Phone</th>
                            <th>Tiêu đề</th>
                            <th>Thời điểm xóa</th>
                            <th style="text-align:center">{{ $canManage ? 'Khôi phục' : 'Trạng thái' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lienHes as $lh)
                            <tr style="opacity:.82">
                                <td style="color:#8899a6;font-size:.78rem">{{ $lienHes->firstItem() + $loop->index }}</td>
                                <td><div class="lh-name">{{ $lh->hoTen }}</div></td>
                                <td>
                                    <div class="lh-info-sub" style="color:#2d3748">{{ $lh->email ?? '—' }}</div>
                                    <div class="lh-info-sub"><i class="fas fa-phone-alt me-1" style="font-size:0.7rem;color:#aab8c2"></i>{{ $lh->soDienThoai ?? '—' }}</div>
                                </td>
                                <td><span style="font-weight:500">{{ \Illuminate\Support\Str::limit($lh->tieuDe, 40) }}</span></td>
                                <td>
                                    <span class="deleted-at-badge"><i class="fas fa-clock"></i>{{ \Carbon\Carbon::parse($lh->deleted_at)->format('d/m/Y H:i') }}</span>
                                    <div style="font-size:.72rem;color:#aab8c2;margin-top:3px">{{ \Carbon\Carbon::parse($lh->deleted_at)->diffForHumans() }}</div>
                                </td>
                                <td style="text-align:center">
                                    @if ($canManage)
                                        <button type="button" class="btn-restore" onclick="confirmRestore({{ $lh->lienHeId }}, '{{ addslashes($lh->hoTen) }}')">
                                            <i class="fas fa-rotate-left"></i> Khôi phục
                                        </button>
                                    @else
                                        <span style="font-size:.8rem;color:#94a3b8;font-style:italic">Chỉ xem</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($lienHes->hasPages())
                <div class="lh-pagination">
                    <div class="lh-pagination-info">Trang {{ $lienHes->currentPage() }} / {{ $lienHes->lastPage() }}</div>
                    {{ $lienHes->links() }}
                </div>
            @endif
        @endif
    </div>

    @if ($canManage)
        <form id="restore-form" method="POST" style="display:none">@csrf @method('PATCH')</form>
    @endif
@endsection

@section('script')
    @if ($canManage)
        <script>
            const RESTORE_BASE_URL = @json(url($portalRoutePrefix . '/lien-he'));

            function confirmRestore(id, name) {
                Swal.fire({
                    title: 'Khôi phục liên hệ?',
                    html: `Bạn có muốn khôi phục liên hệ từ <strong>${name}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Khôi phục',
                    cancelButtonText: 'Hủy',
                }).then(result => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('restore-form');
                        form.action = `${RESTORE_BASE_URL}/${id}/khoi-phuc`;
                        form.submit();
                    }
                });
            }
        </script>
    @endif

    <script>
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('lh-filter-form').submit();
        });
    </script>
@endsection
