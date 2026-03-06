@extends('layouts.admin')

@section('title', 'Quản lý Thông Báo')
@section('page-title', 'Quản lý Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2">

        {{-- ── STATS ──────────────────────────────────────────── --}}
        <div class="nb-stats">
            <div class="nb-stat-card">
                <div class="nb-stat-icon indigo"><i class="fas fa-bell"></i></div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['tong']) }}</div>
                    <div class="nb-stat-label">Tổng thông báo</div>
                </div>
            </div>
            <div class="nb-stat-card">
                <div class="nb-stat-icon emerald"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['hom_nay']) }}</div>
                    <div class="nb-stat-label">Hôm nay</div>
                </div>
            </div>
            <div class="nb-stat-card">
                <div class="nb-stat-icon amber"><i class="fas fa-envelope"></i></div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['chua_doc']) }}</div>
                    <div class="nb-stat-label">Chưa được đọc</div>
                </div>
            </div>
            <div class="nb-stat-card">
                <div class="nb-stat-icon rose"><i class="fas fa-thumbtack"></i></div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['ghim']) }}</div>
                    <div class="nb-stat-label">Đang ghim</div>
                </div>
            </div>
            <div class="nb-stat-card">
                <div class="nb-stat-icon" style="background:rgba(148,163,184,.14);color:#475569"><i class="fas fa-file-lines"></i>
                </div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['nhap']) }}</div>
                    <div class="nb-stat-label">Bản nháp</div>
                </div>
            </div>
            <div class="nb-stat-card">
                <div class="nb-stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="nb-stat-num">{{ number_format($stats['gui_loi']) }}</div>
                    <div class="nb-stat-label">Gửi lỗi</div>
                </div>
            </div>
        </div>

        {{-- ── TOOLBAR & FILTER ─────────────────────────────────── --}}
        <form method="GET" action="{{ route('admin.thong-bao.index') }}" id="filterForm">
            <div class="nb-toolbar">
                <div class="nb-filter-field nb-filter-search">
                    <label for="filter-q" class="nb-filter-label">Từ khóa</label>
                    <div class="input-search">
                        <i class="fas fa-search icon-search"></i>
                        <input type="text" id="filter-q" name="q" placeholder="Tìm tiêu đề, nội dung…"
                            value="{{ request('q') }}">
                    </div>
                </div>

                <div class="nb-filter-field">
                    <label for="filter-loai" class="nb-filter-label">Loại thông báo</label>
                    <select id="filter-loai" name="loaiGui" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        @foreach (App\Models\Interaction\ThongBao::loaiLabels() as $k => $v)
                            <option value="{{ $k }}" {{ request('loaiGui') == (string) $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="nb-filter-field">
                    <label for="filter-doi-tuong" class="nb-filter-label">Đối tượng nhận</label>
                    <select id="filter-doi-tuong" name="doiTuongGui" onchange="this.form.submit()">
                        <option value="">Tất cả đối tượng</option>
                        @foreach (App\Models\Interaction\ThongBao::doiTuongLabels() as $k => $v)
                            <option value="{{ $k }}" {{ request('doiTuongGui') == (string) $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="nb-filter-field">
                    <label for="filter-uu-tien" class="nb-filter-label">Mức ưu tiên</label>
                    <select id="filter-uu-tien" name="uuTien" onchange="this.form.submit()">
                        <option value="">Mọi ưu tiên</option>
                        @foreach (App\Models\Interaction\ThongBao::uuTienLabels() as $k => $v)
                            <option value="{{ $k }}" {{ request('uuTien') == (string) $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="nb-filter-field">
                    <label for="filter-ghim" class="nb-filter-label">Trạng thái ghim</label>
                    <select id="filter-ghim" name="ghim" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('ghim') === '1' ? 'selected' : '' }}>Đã ghim</option>
                        <option value="0" {{ request('ghim') === '0' ? 'selected' : '' }}>Chưa ghim</option>
                    </select>
                </div>

                <div class="nb-filter-field">
                    <label for="filter-send-status" class="nb-filter-label">Trạng thái gửi</label>
                    <select id="filter-send-status" name="sendTrangThai" onchange="this.form.submit()">
                        <option value="">Mọi trạng thái gửi</option>
                        @foreach (App\Models\Interaction\ThongBao::sendTrangThaiLabels() as $k => $v)
                            <option value="{{ $k }}"
                                {{ request('sendTrangThai') !== null && request('sendTrangThai') !== '' && (int) request('sendTrangThai') === $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="nb-btn nb-btn-primary nb-btn-sm">
                    <i class="fas fa-search"></i>
                </button>

                @if (request()->hasAny(['q', 'loaiGui', 'doiTuongGui', 'uuTien', 'ghim', 'sendTrangThai']))
                    <a href="{{ route('admin.thong-bao.index') }}" class="nb-btn nb-btn-secondary nb-btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <div class="nb-spacer"></div>

                <button type="button" id="btnBulkDelete" class="nb-btn nb-btn-danger nb-btn-sm" disabled>
                    <i class="fas fa-trash"></i> Xóa (<span id="selectedCount">0</span>)
                </button>

                <a href="{{ route('admin.thong-bao.create') }}" class="nb-btn nb-btn-primary">
                    <i class="fas fa-plus"></i> Tạo thông báo
                </a>
            </div>
        </form>

        {{-- ── TABLE ─────────────────────────────────────────────── --}}
        <div class="nb-table-card">
            <table class="nb-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="cb-check" id="checkAll"></th>
                        <th>LOẠI</th>
                        <th>THÔNG BÁO</th>
                        <th>NGƯỜI GỬI</th>
                        <th>ĐỐI TƯỢNG</th>
                        <th>TỈ LỆ ĐỌC</th>
                        <th>ƯU TIÊN</th>
                        <th>TRẠNG THÁI</th>
                        <th>NGÀY GỬI</th>
                        <th>THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thongBaos as $tb)
                        @php
                            $tong = $tb->nguoi_nhans_count ?? 0;
                            $daDocs = $tb->da_doc_count ?? 0;
                            $tiLe = $tong > 0 ? round(($daDocs / $tong) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="cb-check cb-row" value="{{ $tb->thongBaoId }}"></td>
                            <td>
                                <span class="nb-badge {{ $tb->getLoaiBadgeClass() }}">
                                    @switch($tb->loaiGui)
                                        @case(0)
                                            <i class="fas fa-cog"></i>
                                        @break

                                        @case(1)
                                            <i class="fas fa-graduation-cap"></i>
                                        @break

                                        @case(2)
                                            <i class="fas fa-wallet"></i>
                                        @break

                                        @case(3)
                                            <i class="fas fa-calendar-alt"></i>
                                        @break

                                        @case(4)
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @break
                                    @endswitch
                                    {{ $tb->getLoaiLabel() }}
                                </span>
                                @if ($tb->ghim)
                                    <span class="nb-badge badge-pin ms-1"><i class="fas fa-thumbtack"></i> Ghim</span>
                                @endif
                            </td>
                            <td class="nb-title-cell">
                                <div class="tb-title">{{ Str::limit($tb->tieuDe, 55) }}</div>
                                <div class="tb-preview">{{ Str::limit(strip_tags($tb->noiDung), 75) }}</div>
                            </td>
                            <td>
                                @if ($tb->nguoiGui)
                                    @php
                                        $g = $tb->nguoiGui;
                                        $ten = $g->hoSoNguoiDung->hoTen ?? ($g->nhanSu->hoTen ?? $g->taiKhoan);
                                    @endphp
                                    <div class="nb-sender">
                                        <div class="nb-avatar">{{ strtoupper(mb_substr($ten, 0, 1)) }}</div>
                                        <div>
                                            <div class="nb-sender-name">{{ $ten }}</div>
                                            <div class="nb-sender-role">{{ $g->getRoleLabel() }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="nb-date">Hệ thống</span>
                                @endif
                            </td>
                            <td>
                                <span class="nb-badge badge-he-thong">{{ $tb->getDoiTuongLabel() }}</span>
                            </td>
                            <td>
                                <div class="nb-read-progress">
                                    <div class="nb-read-percent">{{ $daDocs }}/{{ $tong }}
                                        ({{ $tiLe }}%)</div>
                                    <div class="bar-wrap">
                                        <div class="bar-fill" style="width:{{ $tiLe }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="nb-badge {{ $tb->getUuTienBadgeClass() }}">
                                    {{ $tb->getUuTienLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="nb-badge {{ $tb->getSendTrangThaiBadgeClass() }}">
                                    {{ $tb->getSendTrangThaiLabel() }}
                                </span>
                                @if ((int) $tb->sendTrangThai === App\Models\Interaction\ThongBao::SEND_TRANG_THAI_NHAP)
                                    <div class="nb-date" style="margin-top:.3rem;">Bản nháp: mở Chỉnh sửa để gửi</div>
                                @endif
                            </td>
                            <td class="nb-date">
                                {{ optional($tb->ngayGui ?? $tb->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="nb-actions">
                                    <a href="{{ route('admin.thong-bao.show', $tb->thongBaoId) }}"
                                        class="nb-action-btn view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.thong-bao.edit', $tb->thongBaoId) }}"
                                        class="nb-action-btn edit" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button class="nb-action-btn {{ $tb->ghim ? 'unpin' : 'pin' }}"
                                        title="{{ $tb->ghim ? 'Bỏ ghim' : 'Ghim' }}"
                                        onclick="togglePin({{ $tb->thongBaoId }})">
                                        <i class="fas fa-thumbtack"></i>
                                    </button>
                                    <button class="nb-action-btn view" title="Nhân bản thành nháp"
                                        onclick="duplicateThongBao({{ $tb->thongBaoId }})">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="nb-action-btn edit" title="Gửi thử cho tôi"
                                        onclick="sendTestThongBao({{ $tb->thongBaoId }})">
                                        <i class="fas fa-vial-circle-check"></i>
                                    </button>
                                    <button class="nb-action-btn del" title="Xóa"
                                        onclick="deleteSingle({{ $tb->thongBaoId }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="del-form-{{ $tb->thongBaoId }}" method="POST"
                                    action="{{ route('admin.thong-bao.destroy', $tb->thongBaoId) }}"
                                    style="display:none">
                                    @csrf @method('DELETE')
                                </form>
                                <form id="dup-form-{{ $tb->thongBaoId }}" method="POST"
                                    action="{{ route('admin.thong-bao.duplicate', $tb->thongBaoId) }}"
                                    style="display:none">
                                    @csrf
                                </form>
                                <form id="test-form-{{ $tb->thongBaoId }}" method="POST"
                                    action="{{ route('admin.thong-bao.send-test', $tb->thongBaoId) }}"
                                    style="display:none">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="nb-empty">
                                        <div class="icon-empty"><i class="fas fa-bell-slash"></i></div>
                                        <p>Chưa có thông báo nào.
                                            <a href="{{ route('admin.thong-bao.create') }}">Tạo thông báo đầu tiên</a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($thongBaos->hasPages())
                    <div class="nb-pagination">
                        <div class="page-info">
                            Hiển thị {{ $thongBaos->firstItem() }}–{{ $thongBaos->lastItem() }} / {{ $thongBaos->total() }}
                            thông báo
                        </div>
                        {{ $thongBaos->links() }}
                    </div>
                @endif
            </div>

        </div>
    @endsection

    @section('script')
        {{-- Truyền URL vào JS qua biến toàn cục --}}
        <script>
            const BULK_DESTROY_URL = '{{ route('admin.thong-bao.bulk-destroy') }}';
        </script>
        <script src="{{ asset('assets/admin/js/pages/thong-bao/index.js') }}"></script>
    @endsection
