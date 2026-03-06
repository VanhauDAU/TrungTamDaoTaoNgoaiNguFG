@extends('layouts.admin')

@section('title', 'Chi tiết Thông Báo')
@section('page-title', 'Chi tiết Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2">

        {{-- ── Action buttons ───────────────────────────────────── --}}
        <div style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem;">
            <a href="{{ route('admin.thong-bao.index') }}" class="nb-btn nb-btn-secondary">
                <i class="fas fa-arrow-left"></i> Danh sách
            </a>
            <a href="{{ route('admin.thong-bao.edit', $thongBao->thongBaoId) }}" class="nb-btn nb-btn-primary">
                <i class="fas fa-pen"></i> Chỉnh sửa
            </a>
            <button type="button" class="nb-btn nb-btn-secondary" onclick="togglePin({{ $thongBao->thongBaoId }})">
                <i class="fas fa-thumbtack"></i> {{ $thongBao->ghim ? 'Bỏ ghim' : 'Ghim' }}
            </button>
            <button type="button" class="nb-btn nb-btn-danger" onclick="deleteThis()">
                <i class="fas fa-trash"></i> Xóa
            </button>
        </div>

        <div class="show-layout">

            {{-- ── CỘT TRÁI ──────────────────────────────────────── --}}
            <div>

                {{-- Nội dung thông báo --}}
                <div class="nb-card">
                    <div style="margin-bottom:1rem;">
                        <div style="display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:.75rem;">
                            <span class="nb-badge {{ $thongBao->getLoaiBadgeClass() }}">
                                {{ $thongBao->getLoaiLabel() }}
                            </span>
                            <span class="nb-badge {{ $thongBao->getUuTienBadgeClass() }}">
                                <i class="fas fa-flag me-1"></i>{{ $thongBao->getUuTienLabel() }}
                            </span>
                            @if ($thongBao->ghim)
                                <span class="nb-badge badge-pin"><i class="fas fa-thumbtack me-1"></i>Đã ghim</span>
                            @endif
                        </div>
                        <h2 style="font-size:1.4rem; font-weight:800; color:#111827; margin-bottom:.5rem;">
                            {{ $thongBao->tieuDe }}
                        </h2>
                        <div style="font-size:.8rem; color:#9ca3af; display:flex; gap:.75rem; flex-wrap:wrap;">
                            <span><i class="fas fa-clock me-1"></i>
                                {{ optional($thongBao->ngayGui ?? $thongBao->created_at)->format('d/m/Y H:i') }}
                            </span>
                            <span>•</span>
                            <span>{{ $thongBao->getDoiTuongLabel() }}</span>
                        </div>
                    </div>
                    <div style="font-size:.93rem; color:#374151; line-height:1.75;">
                        {!! $thongBao->noiDung !!}
                    </div>
                </div>

                {{-- File đính kèm --}}
                @if ($thongBao->tepDinhs->isNotEmpty())
                    <div class="nb-card">
                        <div class="nb-card-title">
                            <div class="nb-icon-tag" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            File đính kèm
                            <span class="nb-badge badge-hoc-tap" style="margin-left:auto;">
                                {{ $thongBao->tepDinhs->count() }} file
                            </span>
                        </div>
                        <div class="attach-list">
                            @foreach ($thongBao->tepDinhs as $tep)
                                <div class="attach-item">
                                    <span class="attach-icon"><i class="fas {{ $tep->iconClass }}"></i></span>
                                    <span class="attach-name" title="{{ $tep->tenFile }}">{{ $tep->tenFile }}</span>
                                    <span class="attach-size">{{ $tep->kichThuocHienThi }}</span>
                                    <a href="{{ $tep->url }}" download="{{ $tep->tenFile }}" class="attach-dl">
                                        <i class="fas fa-download me-1"></i>Tải
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Analytics đọc --}}
                <div class="nb-card">
                    <div class="nb-card-title">
                        <i class="fas fa-chart-pie" style="color:#6366f1;"></i> Analytics đọc thông báo
                    </div>
                    <div class="analytics-ring">
                        <div class="ring-wrap">
                            <canvas id="readChart" width="110" height="110"></canvas>
                            <div class="ring-center">
                                <div class="ring-percent">{{ $tiLe }}%</div>
                                <div class="ring-sub">Đã đọc</div>
                            </div>
                        </div>
                        <div class="ring-stats">
                            <div class="ring-stat">
                                <div class="ring-stat-num rs-green">{{ $daDocs }}</div>
                                <div class="ring-stat-label">Đã đọc</div>
                            </div>
                            <div class="ring-stat">
                                <div class="ring-stat-num rs-amber">{{ $chuaDocs }}</div>
                                <div class="ring-stat-label">Chưa đọc</div>
                            </div>
                            <div class="ring-stat" style="grid-column:span 2;">
                                <div class="ring-stat-num" style="color:#6366f1;">{{ $tongNguoiNhan }}</div>
                                <div class="ring-stat-label">Tổng người nhận</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Danh sách người nhận --}}
                <div class="nb-card">
                    <div class="nb-card-title">
                        <i class="fas fa-users" style="color:#6366f1;"></i> Danh sách người nhận
                    </div>
                    <div class="recipient-filter">
                        <button class="rf-btn active" onclick="filterRecipients('all', this)">
                            Tất cả ({{ $tongNguoiNhan }})
                        </button>
                        <button class="rf-btn" onclick="filterRecipients('read', this)">
                            Đã đọc ({{ $daDocs }})
                        </button>
                        <button class="rf-btn" onclick="filterRecipients('unread', this)">
                            Chưa đọc ({{ $chuaDocs }})
                        </button>
                    </div>
                    <div class="recipient-list" id="recipientList">
                        @foreach ($thongBao->nguoiNhans as $r)
                            @php
                                $u = $r->nguoiDung;
                                $ten = $u ? $u->hoSoNguoiDung->hoTen ?? ($u->nhanSu->hoTen ?? $u->taiKhoan) : '—';
                            @endphp
                            <div class="recipient-item" data-read="{{ $r->daDoc ? '1' : '0' }}">
                                <div class="rec-avatar {{ $r->daDoc ? 'read' : '' }}">
                                    {{ strtoupper(mb_substr($ten, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="rec-name">{{ $ten }}</div>
                                    <div class="rec-email">{{ $u?->email }}</div>
                                </div>
                                <div class="rec-status">
                                    @if ($r->daDoc)
                                        <span class="rec-read">
                                            <i class="fas fa-check-double"></i>
                                            {{ optional($r->ngayDoc)->format('d/m H:i') }}
                                        </span>
                                    @else
                                        <span class="rec-unread"><i class="fas fa-clock"></i> Chưa đọc</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── CỘT PHẢI ─────────────────────────────────────── --}}
            <div>
                <div class="nb-card">
                    <div class="nb-card-title">
                        <i class="fas fa-info-circle" style="color:#6366f1;"></i> Thông tin chi tiết
                    </div>
                    @php
                        $g = $thongBao->nguoiGui;
                        $tenGui = $g ? $g->hoSoNguoiDung->hoTen ?? ($g->nhanSu->hoTen ?? $g->taiKhoan) : 'Hệ thống';
                    @endphp
                    <div class="meta-list">
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-user-edit"></i></div>
                            <div>
                                <div class="meta-label">Người gửi</div>
                                <div class="meta-value">{{ $tenGui }}
                                    @if ($g)
                                        <span style="font-weight:400; color:#9ca3af;"> ({{ $g->getRoleLabel() }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="meta-label">Ngày gửi</div>
                                <div class="meta-value">
                                    {{ optional($thongBao->ngayGui ?? $thongBao->created_at)->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                        </div>
                        @if ((int) $thongBao->sendTrangThai === App\Models\Interaction\ThongBao::SEND_TRANG_THAI_DA_LEN_LICH && $thongBao->scheduled_at)
                            <div class="meta-item">
                                <div class="meta-icon"><i class="fas fa-calendar"></i></div>
                                <div>
                                    <div class="meta-label">Lịch gửi</div>
                                    <div class="meta-value">{{ $thongBao->scheduled_at->format('d/m/Y H:i:s') }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-bullseye"></i></div>
                            <div>
                                <div class="meta-label">Đối tượng</div>
                                <div class="meta-value">{{ $thongBao->getDoiTuongLabel() }}</div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-tag"></i></div>
                            <div>
                                <div class="meta-label">Loại thông báo</div>
                                <div class="meta-value">{{ $thongBao->getLoaiLabel() }}</div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-flag"></i></div>
                            <div>
                                <div class="meta-label">Mức ưu tiên</div>
                                <div class="meta-value">{{ $thongBao->getUuTienLabel() }}</div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon"><i class="fas fa-thumbtack"></i></div>
                            <div>
                                <div class="meta-label">Trạng thái ghim</div>
                                <div class="meta-value">{{ $thongBao->ghim ? '📌 Đang ghim' : 'Chưa ghim' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Delete form --}}
    <form id="del-form" method="POST" action="{{ route('admin.thong-bao.destroy', $thongBao->thongBaoId) }}"
        style="display:none;">
        @csrf @method('DELETE')
    </form>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Inject data PHP → JS --}}
    <script>
        window.DA_DOC = {{ $daDocs }};
        window.CHUA_DOC = {{ $chuaDocs }};
        window.THONG_BAO_ID = {{ $thongBao->thongBaoId }};
    </script>

    <script src="{{ asset('assets/admin/js/pages/thong-bao/show.js') }}"></script>
@endsection
