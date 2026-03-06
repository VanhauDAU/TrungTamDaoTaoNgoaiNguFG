@extends('layouts.admin')

@section('title', 'Thùng rác Thông Báo')
@section('page-title', 'Thùng rác Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2">
        <div style="display:flex;align-items:center;gap:.75rem;justify-content:space-between;flex-wrap:wrap;margin-bottom:1rem;">
            <div style="font-size:1.05rem;font-weight:700;color:#111827;">
                <i class="fas fa-trash-can" style="color:#dc2626;"></i> Thùng rác
                <span style="font-size:.9rem;color:#6b7280;">({{ $thongBaos->total() }} thông báo)</span>
            </div>
            <a href="{{ route('admin.thong-bao.index') }}" class="nb-btn nb-btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        @if (session('success'))
            <div class="locked-notice" style="background:#ecfdf5;border-color:#bbf7d0;color:#166534;">
                <i class="fas fa-check-circle" style="color:#10b981;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="nb-alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.thong-bao.trash') }}" style="margin-bottom:1rem;">
            <div class="nb-toolbar">
                <div class="nb-filter-field nb-filter-search">
                    <label for="trash-q" class="nb-filter-label">Từ khóa</label>
                    <div class="input-search">
                        <i class="fas fa-search icon-search"></i>
                        <input id="trash-q" type="text" name="q" value="{{ request('q') }}"
                            placeholder="Tìm tiêu đề, nội dung đã xóa...">
                    </div>
                </div>
                <button type="submit" class="nb-btn nb-btn-primary nb-btn-sm">
                    <i class="fas fa-search"></i> Tìm
                </button>
                @if (request()->filled('q'))
                    <a href="{{ route('admin.thong-bao.trash') }}" class="nb-btn nb-btn-secondary nb-btn-sm">
                        <i class="fas fa-times"></i> Xóa lọc
                    </a>
                @endif
            </div>
        </form>

        @if ($thongBaos->isEmpty())
            <div class="nb-empty nb-table-card">
                <div class="icon-empty"><i class="fas fa-trash"></i></div>
                <p>Thùng rác đang trống.</p>
            </div>
        @else
            <div class="nb-table-card">
                <table class="nb-table">
                    <thead>
                        <tr>
                            <th>THÔNG BÁO</th>
                            <th>NGƯỜI GỬI</th>
                            <th>LOẠI</th>
                            <th>TRẠNG THÁI</th>
                            <th>TỆP ĐÍNH KÈM</th>
                            <th>NGÀY XÓA</th>
                            <th>THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($thongBaos as $tb)
                            @php
                                $g = $tb->nguoiGui;
                                $ten = $g ? $g->hoSoNguoiDung->hoTen ?? ($g->nhanSu->hoTen ?? $g->taiKhoan) : 'Hệ thống';
                            @endphp
                            <tr>
                                <td class="nb-title-cell">
                                    <div class="tb-title">{{ \Illuminate\Support\Str::limit($tb->tieuDe, 70) }}</div>
                                    <div class="tb-preview">{{ \Illuminate\Support\Str::limit(strip_tags($tb->noiDung), 85) }}</div>
                                </td>
                                <td class="nb-date">{{ $ten }}</td>
                                <td>
                                    <span class="nb-badge {{ $tb->getLoaiBadgeClass() }}">{{ $tb->getLoaiLabel() }}</span>
                                </td>
                                <td>
                                    <span class="nb-badge {{ $tb->getSendTrangThaiBadgeClass() }}">
                                        {{ $tb->getSendTrangThaiLabel() }}
                                    </span>
                                </td>
                                <td class="nb-date">{{ $tb->tep_dinhs_count ?? 0 }} file</td>
                                <td class="nb-date">{{ optional($tb->deleted_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="nb-actions">
                                        <form method="POST" action="{{ route('admin.thong-bao.restore', $tb->thongBaoId) }}"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="nb-action-btn unpin" title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <button class="nb-action-btn del" title="Xóa vĩnh viễn"
                                            onclick="confirmForceDelete({{ $tb->thongBaoId }}, @js($tb->tieuDe))">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($thongBaos->hasPages())
                    <div class="nb-pagination">
                        <div class="page-info">
                            Hiển thị {{ $thongBaos->firstItem() }}–{{ $thongBaos->lastItem() }} / {{ $thongBaos->total() }}
                            thông báo đã xóa
                        </div>
                        {{ $thongBaos->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <form id="force-delete-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('script')
    <script>
        function confirmForceDelete(id, title) {
            Swal.fire({
                title: 'Xóa vĩnh viễn thông báo?',
                html: `<strong>${title}</strong><br><small style="color:#dc2626;">Hành động này không thể hoàn tác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa vĩnh viễn',
                confirmButtonColor: '#dc2626',
                cancelButtonText: 'Huỷ',
            }).then((result) => {
                if (!result.isConfirmed) return;
                const form = document.getElementById('force-delete-form');
                form.action = `/admin/thong-bao/${id}/xoa-vinh-vien`;
                form.submit();
            });
        }
    </script>
@endsection
