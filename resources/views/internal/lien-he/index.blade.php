@extends('layouts.admin')

@section('title', 'Danh sách liên hệ')
@section('page-title', $canManage ? 'Xử lý liên hệ' : 'Theo dõi liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Danh sách liên hệ')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/index.css') }}">
    <style>
        .lh-notice {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            color: #1e3a8a;
            font-size: 0.84rem;
        }

        .lh-notice i {
            font-size: 1rem;
        }

        .lh-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .lh-stat-card {
            background: #fff;
            border: 1.5px solid #e9eef5;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .lh-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .lh-stat-icon.total { background: #e0f2fe; color: #0284c7; }
        .lh-stat-icon.orange { background: #fff7ed; color: #ea580c; }
        .lh-stat-icon.blue { background: #eff6ff; color: #2563eb; }
        .lh-stat-icon.green { background: #f0fdf4; color: #16a34a; }
        .lh-stat-icon.red { background: #fef2f2; color: #dc2626; }

        .lh-stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a2b3c;
            line-height: 1;
        }

        .lh-stat-label {
            font-size: 0.72rem;
            color: #8899a6;
            margin-top: 3px;
        }

        .badge-loai,
        .badge-ts {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-loai.blue { background: #dbeafe; color: #1d4ed8; }
        .badge-loai.green { background: #dcfce7; color: #15803d; }
        .badge-loai.red { background: #fee2e2; color: #b91c1c; }
        .badge-loai.gray { background: #f1f5f9; color: #475569; }
        .badge-ts.orange { background: #fff7ed; color: #c2410c; }
        .badge-ts.blue { background: #eff6ff; color: #1d4ed8; }
        .badge-ts.green { background: #f0fdf4; color: #15803d; }
        .badge-ts.red { background: #fee2e2; color: #b91c1c; }

        .badge-new {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 8px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            vertical-align: middle;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.18);
        }

        .lh-bulk-bar {
            display: none;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #eff6ff, #f0f4f8);
            border: 1.5px solid #93c5fd;
            border-radius: 10px;
            padding: 10px 18px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            color: #1e40af;
            font-weight: 600;
        }

        .lh-bulk-bar.active { display: flex; }

        .bulk-count {
            background: #3b82f6;
            color: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
        }

        .btn-bulk-delete,
        .btn-bulk-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 8px;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-bulk-delete { margin-left: auto; background: #dc2626; }
        .btn-bulk-status { background: #2563eb; }
        .lh-checkbox { width: 17px; height: 17px; accent-color: #3b82f6; cursor: pointer; }

        .lh-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0f4d7f, #27c4b5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }
    </style>
@endsection

@section('content')
    @if (!$canManage)
        <div class="lh-notice">
            <i class="fas fa-eye"></i>
            <div>Admin chỉ theo dõi và xem lịch sử liên hệ. Bộ phận nhân viên là đơn vị trực tiếp xử lý, phản hồi và gán phụ trách.</div>
        </div>
    @endif

    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-envelope-open-text me-2" style="color:#27c4b5"></i>Danh sách liên hệ
            <span>{{ $lienHes->total() }} kết quả</span>
        </div>
        <a href="{{ route($portalRoutePrefix . '.lien-he.trash') }}" class="btn-filter btn-filter-reset" style="gap:8px;font-weight:600">
            <i class="fas fa-trash-can" style="color:#dc2626"></i> Thùng rác
            @if ($tongXoa > 0)
                <span style="background:#dc2626;color:#fff;font-size:0.72rem;padding:2px 8px;border-radius:20px;font-weight:700">{{ $tongXoa }}</span>
            @endif
        </a>
    </div>

    <div class="lh-stats">
        <div class="lh-stat-card"><div class="lh-stat-icon total"><i class="fas fa-inbox"></i></div><div><div class="lh-stat-value">{{ number_format($tongSo) }}</div><div class="lh-stat-label">Tổng liên hệ</div></div></div>
        <div class="lh-stat-card"><div class="lh-stat-icon orange"><i class="fas fa-hourglass-half"></i></div><div><div class="lh-stat-value">{{ number_format($chuaXuLy) }}</div><div class="lh-stat-label">Chưa xử lý</div></div></div>
        <div class="lh-stat-card"><div class="lh-stat-icon blue"><i class="fas fa-spinner"></i></div><div><div class="lh-stat-value">{{ number_format($dangXuLy) }}</div><div class="lh-stat-label">Đang xử lý</div></div></div>
        <div class="lh-stat-card"><div class="lh-stat-icon green"><i class="fas fa-check-circle"></i></div><div><div class="lh-stat-value">{{ number_format($daXuLy) }}</div><div class="lh-stat-label">Đã xử lý</div></div></div>
        <div class="lh-stat-card"><div class="lh-stat-icon red"><i class="fas fa-ban"></i></div><div><div class="lh-stat-value">{{ number_format($daTuChoi) }}</div><div class="lh-stat-label">Đã từ chối</div></div></div>
    </div>

    <form action="{{ route($portalRoutePrefix . '.lien-he.index') }}" method="GET" class="lh-filter-bar" id="filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email, SĐT, tiêu đề..." value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Chưa xử lý</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang xử lý</option>
            <option value="2" {{ request('trangThai') === '2' ? 'selected' : '' }}>Đã xử lý</option>
            <option value="3" {{ request('trangThai') === '3' ? 'selected' : '' }}>Đã từ chối</option>
        </select>

        <select name="loaiLienHe" onchange="this.form.submit()">
            <option value="">Tất cả loại</option>
            @foreach (\App\Models\Interaction\LienHe::LOAI_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('loaiLienHe') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <select name="nguoiPhuTrachId" onchange="this.form.submit()">
            <option value="">Tất cả phụ trách</option>
            <option value="__null__" {{ request('nguoiPhuTrachId') === '__null__' ? 'selected' : '' }}>Chưa gán</option>
            @foreach ($nhanVienList as $nv)
                <option value="{{ $nv->taiKhoanId }}" {{ request('nguoiPhuTrachId') == $nv->taiKhoanId ? 'selected' : '' }}>
                    {{ $nv->hoSoNguoiDung?->hoTen ?? $nv->taiKhoan }}
                </option>
            @endforeach
        </select>

        <select name="orderBy" onchange="this.form.submit()">
            <option value="created_at" {{ request('orderBy', 'created_at') === 'created_at' ? 'selected' : '' }}>Mới nhất</option>
            <option value="lienHeId" {{ request('orderBy') === 'lienHeId' ? 'selected' : '' }}>ID mới nhất</option>
            <option value="hoTen" {{ request('orderBy') === 'hoTen' ? 'selected' : '' }}>A-Z</option>
            <option value="trangThai" {{ request('orderBy') === 'trangThai' ? 'selected' : '' }}>Trạng thái</option>
        </select>
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

        <button type="submit" class="btn-filter btn-filter-primary"><i class="fas fa-filter"></i> Lọc</button>
        <a href="{{ route($portalRoutePrefix . '.lien-he.index') }}" class="btn-filter btn-filter-reset"><i class="fas fa-times"></i> Đặt lại</a>
    </form>

    @if ($canManage)
        <div class="lh-bulk-bar" id="bulk-bar">
            <i class="fas fa-check-double"></i>
            Đã chọn <span class="bulk-count" id="bulk-count">0</span> liên hệ
            <button type="button" class="btn-bulk-status" onclick="confirmBulkStatus()"><i class="fas fa-arrows-rotate"></i> Đổi trạng thái</button>
            <button type="button" class="btn-bulk-delete" onclick="confirmBulkDelete()"><i class="fas fa-trash"></i> Xóa đã chọn</button>
        </div>
    @endif

    <div class="lh-card">
        <div class="lh-table-header">
            <div class="lh-table-title"><i class="fas fa-list me-2"></i> Danh sách liên hệ</div>
            <div class="lh-table-count">Hiển thị {{ $lienHes->firstItem() ?? 0 }}-{{ $lienHes->lastItem() ?? 0 }} / {{ $lienHes->total() }} bản ghi</div>
        </div>

        @if ($lienHes->isEmpty())
            <div class="lh-empty">
                <i class="fas fa-envelope-open"></i>
                <p>Không tìm thấy liên hệ nào.</p>
                @if (request()->anyFilled(['q', 'trangThai', 'loaiLienHe', 'nguoiPhuTrachId']))
                    <a href="{{ route($portalRoutePrefix . '.lien-he.index') }}" class="btn-filter btn-filter-reset">Xóa bộ lọc</a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            @if ($canManage)
                                <th style="width:36px"><input type="checkbox" class="lh-checkbox" id="check-all"></th>
                            @endif
                            <th style="width:44px">#</th>
                            <th>Người gửi</th>
                            <th>Email & SĐT</th>
                            <th>Loại</th>
                            <th>Tiêu đề</th>
                            <th>Phụ trách</th>
                            <th>Thời gian gửi</th>
                            <th>Trạng thái</th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lienHes as $lh)
                            @php
                                $loaiColor = \App\Models\Interaction\LienHe::LOAI_COLORS[$lh->loaiLienHe] ?? 'gray';
                                $tsColor = \App\Models\Interaction\LienHe::TRANG_THAI_COLORS[$lh->trangThai] ?? 'gray';
                                $tsLabel = \App\Models\Interaction\LienHe::TRANG_THAI_LABELS[$lh->trangThai] ?? '?';
                                $loaiLabel = \App\Models\Interaction\LienHe::LOAI_LABELS[$lh->loaiLienHe] ?? 'Khác';
                                $isNew = $lh->created_at?->gte(now()->subDay());
                            @endphp
                            <tr>
                                @if ($canManage)
                                    <td><input type="checkbox" class="lh-checkbox row-check" value="{{ $lh->lienHeId }}"></td>
                                @endif
                                <td style="color:#8899a6;font-size:0.78rem">{{ $lienHes->firstItem() + $loop->index }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div class="lh-avatar">{{ mb_strtoupper(mb_substr($lh->hoTen, 0, 1)) }}</div>
                                        <div class="lh-name">
                                            {{ $lh->hoTen }}
                                            @if ($isNew)
                                                <span class="badge-new">NEW</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="lh-info-sub" style="color:#2d3748">{{ $lh->email ?? '—' }}</div>
                                    <div class="lh-info-sub"><i class="fas fa-phone-alt me-1" style="font-size:0.7rem;color:#aab8c2"></i>{{ $lh->soDienThoai ?? '—' }}</div>
                                </td>
                                <td><span class="badge-loai {{ $loaiColor }}">{{ $loaiLabel }}</span></td>
                                <td><span style="font-weight:500;">{{ \Illuminate\Support\Str::limit($lh->tieuDe, 35) }}</span></td>
                                <td>
                                    @if ($lh->nguoiPhuTrach)
                                        <span style="font-size:0.8rem;color:#374151;font-weight:600">
                                            <i class="fas fa-user-check me-1" style="color:#10b981;font-size:0.7rem"></i>
                                            {{ $lh->nguoiPhuTrach->hoSoNguoiDung?->hoTen ?? $lh->nguoiPhuTrach->taiKhoan }}
                                        </span>
                                    @else
                                        <span style="font-size:0.78rem;color:#94a3b8;font-style:italic">Chưa gán</span>
                                    @endif
                                </td>
                                <td style="color:#8899a6;font-size:0.8rem">
                                    {{ $lh->created_at->format('d/m/Y H:i') }}
                                    <div class="lh-info-sub">{{ $lh->created_at->diffForHumans() }}</div>
                                </td>
                                <td><span class="badge-ts {{ $tsColor }}">{{ $tsLabel }}</span></td>
                                <td>
                                    <div class="lh-actions">
                                        <a href="{{ route($portalRoutePrefix . '.lien-he.show', $lh->lienHeId) }}" class="btn-action btn-action-edit" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($canManage)
                                            <button type="button" class="btn-action btn-action-del" title="Xóa" onclick="confirmDelete({{ $lh->lienHeId }}, '{{ addslashes($lh->hoTen) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
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
        <form id="delete-form" method="POST" style="display:none">@csrf @method('DELETE')</form>
        <form id="bulk-delete-form" method="POST" action="{{ route($portalRoutePrefix . '.lien-he.bulk-destroy') }}" style="display:none">
            @csrf @method('DELETE')
            <input type="hidden" name="ids" id="bulk-ids">
        </form>
        <form id="bulk-status-form" method="POST" action="{{ route($portalRoutePrefix . '.lien-he.bulk-status') }}" style="display:none">
            @csrf @method('PATCH')
            <input type="hidden" name="ids" id="bulk-status-ids">
            <input type="hidden" name="trangThai" id="bulk-trangThai">
        </form>
    @endif
@endsection

@section('script')
    @if ($canManage)
        <script>
            const CONTACT_BASE_URL = @json(url($portalRoutePrefix . '/lien-he'));

            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Xóa liên hệ?',
                    html: `Liên hệ từ <strong>${name}</strong> sẽ được chuyển vào thùng rác.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(r => {
                    if (r.isConfirmed) {
                        const form = document.getElementById('delete-form');
                        form.action = `${CONTACT_BASE_URL}/${id}`;
                        form.submit();
                    }
                });
            }

            function confirmBulkStatus() {
                const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(c => c.value);
                if (!ids.length) return;
                Swal.fire({
                    title: `Đổi trạng thái ${ids.length} liên hệ?`,
                    icon: 'question',
                    input: 'select',
                    inputOptions: {'0': 'Chưa xử lý', '1': 'Đang xử lý', '2': 'Đã xử lý', '3': 'Đã từ chối'},
                    inputPlaceholder: '-- Chọn trạng thái --',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    inputValidator: v => !v && 'Vui lòng chọn trạng thái!',
                }).then(r => {
                    if (r.isConfirmed) {
                        document.getElementById('bulk-status-ids').value = ids.join(',');
                        document.getElementById('bulk-trangThai').value = r.value;
                        document.getElementById('bulk-status-form').submit();
                    }
                });
            }

            function confirmBulkDelete() {
                const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(c => c.value);
                if (!ids.length) return;
                Swal.fire({
                    title: `Xóa ${ids.length} liên hệ?`,
                    html: `<strong>${ids.length}</strong> liên hệ sẽ vào thùng rác.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                }).then(r => {
                    if (r.isConfirmed) {
                        document.getElementById('bulk-ids').value = ids.join(',');
                        document.getElementById('bulk-delete-form').submit();
                    }
                });
            }

            const checkAll = document.getElementById('check-all');
            const rowChecks = document.querySelectorAll('.row-check');
            const bulkBar = document.getElementById('bulk-bar');
            const bulkCount = document.getElementById('bulk-count');

            function updateBulkBar() {
                const checked = document.querySelectorAll('.row-check:checked');
                bulkCount.textContent = checked.length;
                bulkBar.classList.toggle('active', checked.length > 0);
            }

            checkAll?.addEventListener('change', function() {
                rowChecks.forEach(cb => cb.checked = this.checked);
                updateBulkBar();
            });

            rowChecks.forEach(cb => cb.addEventListener('change', function() {
                checkAll.checked = document.querySelectorAll('.row-check:checked').length === rowChecks.length;
                updateBulkBar();
            }));
        </script>
    @endif

    <script>
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection
