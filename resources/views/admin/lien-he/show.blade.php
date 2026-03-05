@extends('layouts.admin')

@section('title', 'Chi tiết liên hệ #' . $lienHe->lienHeId)
@section('page-title', 'Chi tiết Liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Liên hệ · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/show.css') }}">
@endsection

@php
    $tsLabels = \App\Models\Interaction\LienHe::TRANG_THAI_LABELS;
    $tsColors = \App\Models\Interaction\LienHe::TRANG_THAI_COLORS;
    $loaiLabels = \App\Models\Interaction\LienHe::LOAI_LABELS;
    $loaiColors = \App\Models\Interaction\LienHe::LOAI_COLORS;
    $lichSuInfo = \App\Models\Interaction\LienHeLichSu::HANH_DONG;
@endphp

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="crm-page-header">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route('admin.lien-he.index') }}" class="crm-btn-back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="crm-page-title">
                    Liên hệ #{{ $lienHe->lienHeId }}
                    @php $tsColor = $tsColors[$lienHe->trangThai] ?? 'gray'; @endphp
                    <span class="badge-ts {{ $tsColor }}">{{ $tsLabels[$lienHe->trangThai] ?? '?' }}</span>
                    @php $loaiColor = $loaiColors[$lienHe->loaiLienHe] ?? 'gray'; @endphp
                    <span class="badge-loai {{ $loaiColor }}">{{ $loaiLabels[$lienHe->loaiLienHe] ?? 'Khác' }}</span>
                </div>
                <div style="font-size:0.8rem;color:#8899a6;margin-top:2px">
                    Nhận lúc {{ $lienHe->created_at->format('H:i, d/m/Y') }}
                    &nbsp;·&nbsp; {{ $lienHe->created_at->diffForHumans() }}
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <form method="POST" action="{{ route('admin.lien-he.destroy', $lienHe->lienHeId) }}" id="delete-form"
                style="display:none">
                @csrf @method('DELETE')
            </form>
            <button type="button" class="crm-btn crm-btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash me-1"></i> Xóa
            </button>
        </div>
    </div>

    {{-- ── Flash ──────────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="crm-alert crm-alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="crm-alert crm-alert-error"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- ── Layout 2 cột ──────────────────────────────────────────────────────── --}}
    <div class="crm-layout">

        {{-- ─── LEFT: Info + Actions ─────────────────────────────────────────── --}}
        <div class="crm-left">

            {{-- Thông tin người gửi --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-user-circle" style="color:#6366f1"></i> Thông tin người gửi
                </div>
                <div class="crm-card-body">
                    <div class="crm-info-row">
                        <div class="crm-avatar-lg">{{ mb_strtoupper(mb_substr($lienHe->hoTen, 0, 2)) }}</div>
                        <div>
                            <div style="font-size:1.05rem;font-weight:700;color:#1a2b3c">{{ $lienHe->hoTen }}</div>
                            <div style="font-size:0.78rem;color:#8899a6">ID: #{{ $lienHe->lienHeId }}</div>
                        </div>
                    </div>
                    <div class="crm-field">
                        <span class="crm-field-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="crm-field-val">
                            @if ($lienHe->email)
                                <a href="mailto:{{ $lienHe->email }}" style="color:#6366f1">{{ $lienHe->email }}</a>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="crm-field">
                        <span class="crm-field-label"><i class="fas fa-phone"></i> SĐT</span>
                        <span class="crm-field-val">{{ $lienHe->soDienThoai ?? '—' }}</span>
                    </div>
                    <div class="crm-field">
                        <span class="crm-field-label"><i class="fas fa-clock"></i> Gửi lúc</span>
                        <span class="crm-field-val">{{ $lienHe->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @if ($lienHe->thoiGianXuLy)
                        <div class="crm-field">
                            <span class="crm-field-label"><i class="fas fa-check-double"></i> Hoàn tất</span>
                            <span class="crm-field-val">{{ $lienHe->thoiGianXuLy->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Nội dung liên hệ --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-message" style="color:#f59e0b"></i> Nội dung liên hệ
                </div>
                <div class="crm-card-body">
                    <div style="font-weight:700;font-size:0.95rem;color:#1a2b3c;margin-bottom:10px">
                        {{ $lienHe->tieuDe }}
                    </div>
                    <div class="crm-message-box">
                        {!! nl2br(e($lienHe->noiDung)) !!}
                    </div>
                </div>
            </div>

            {{-- CRM Actions --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-sliders" style="color:#10b981"></i> Cập nhật xử lý
                </div>
                <div class="crm-card-body">
                    <form method="POST" action="{{ route('admin.lien-he.update', $lienHe->lienHeId) }}" id="update-form">
                        @csrf @method('PUT')

                        {{-- Trạng thái --}}
                        <div class="crm-form-group">
                            <label class="crm-label">Trạng thái</label>
                            <select name="trangThai" class="crm-select">
                                @foreach ($tsLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ $lienHe->trangThai == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Loại liên hệ --}}
                        <div class="crm-form-group">
                            <label class="crm-label">Loại liên hệ</label>
                            <select name="loaiLienHe" class="crm-select">
                                @foreach ($loaiLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ $lienHe->loaiLienHe === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ghi chú nội bộ --}}
                        <div class="crm-form-group">
                            <label class="crm-label">
                                <i class="fas fa-lock" style="font-size:0.7rem;color:#f59e0b"></i>
                                Ghi chú nội bộ
                                <span style="font-size:0.68rem;color:#94a3b8;font-weight:400">(khách không thấy)</span>
                            </label>
                            <textarea name="ghiChuNoiBo" class="crm-textarea" rows="3" placeholder="Ghi chú nội bộ về liên hệ này...">{{ $lienHe->ghiChuNoiBo }}</textarea>
                        </div>

                        <button type="submit" class="crm-btn crm-btn-primary" style="width:100%">
                            <i class="fas fa-save me-1"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            {{-- Gán người phụ trách --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-user-check" style="color:#3b82f6"></i> Người phụ trách
                </div>
                <div class="crm-card-body">
                    @if ($lienHe->nguoiPhuTrach)
                        <div class="crm-assignee-current">
                            <div class="crm-avatar-sm">
                                {{ mb_strtoupper(mb_substr($lienHe->nguoiPhuTrach->hoSoNguoiDung?->hoTen ?? ($lienHe->nguoiPhuTrach->taiKhoan ?? 'A'), 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:0.88rem">
                                    {{ $lienHe->nguoiPhuTrach->hoSoNguoiDung?->hoTen ?? $lienHe->nguoiPhuTrach->taiKhoan }}
                                </div>
                                <div style="font-size:0.72rem;color:#94a3b8">Đang phụ trách</div>
                            </div>
                        </div>
                    @else
                        <div style="font-size:0.82rem;color:#94a3b8;font-style:italic;margin-bottom:12px">Chưa gán người
                            phụ trách</div>
                    @endif

                    <form method="POST" action="{{ route('admin.lien-he.assign', $lienHe->lienHeId) }}"
                        id="assign-form">
                        @csrf @method('PATCH')
                        <div style="display:flex;gap:8px">
                            <select name="nguoiPhuTrachId" class="crm-select" style="flex:1">
                                <option value="">-- Bỏ gán --</option>
                                @foreach ($nhanVienList as $nv)
                                    <option value="{{ $nv->taiKhoanId }}"
                                        {{ $lienHe->nguoiPhuTrachId == $nv->taiKhoanId ? 'selected' : '' }}>
                                        {{ $nv->hoSoNguoiDung?->hoTen ?? $nv->taiKhoan }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="crm-btn crm-btn-secondary">Gán</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── RIGHT: Thread (Lịch sử + Phản hồi) ──────────────────────────── --}}
        <div class="crm-right">

            {{-- Reply box --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-reply" style="color:#6366f1"></i> Phản hồi nội bộ
                </div>
                <div class="crm-card-body">
                    <form id="reply-form">
                        @csrf
                        <div class="crm-reply-tabs" id="reply-tabs">
                            <button type="button" class="crm-tab active" data-loai="noi_bo">
                                <i class="fas fa-comment-dots"></i> Ghi chú nội bộ
                            </button>
                            <button type="button" class="crm-tab" data-loai="email">
                                <i class="fas fa-paper-plane"></i> Ghi nhận gửi email
                            </button>
                        </div>
                        <input type="hidden" name="loai" id="reply-loai" value="noi_bo">
                        <textarea name="noiDung" id="reply-content" class="crm-textarea" rows="3"
                            placeholder="Nhập nội dung phản hồi/ghi chú..."></textarea>
                        <div style="display:flex;justify-content:flex-end;margin-top:8px">
                            <button type="submit" class="crm-btn crm-btn-primary" id="reply-submit">
                                <i class="fas fa-plus me-1"></i> Thêm phản hồi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Thread phản hồi --}}
            @if ($lienHe->phanHoi->isNotEmpty())
                <div class="crm-card">
                    <div class="crm-card-header">
                        <i class="fas fa-comments" style="color:#8b5cf6"></i>
                        Thread phản hồi
                        <span class="crm-badge-count">{{ $lienHe->phanHoi->count() }}</span>
                    </div>
                    <div class="crm-card-body" id="reply-list">
                        @foreach ($lienHe->phanHoi as $ph)
                            <div class="crm-reply-item {{ $ph->loai === 'email' ? 'email' : '' }}">
                                <div class="crm-reply-avatar">
                                    {{ mb_strtoupper(mb_substr($ph->tenNguoiGui ?? 'A', 0, 1)) }}</div>
                                <div class="crm-reply-bubble">
                                    <div class="crm-reply-meta">
                                        <strong>{{ $ph->tenNguoiGui ?? 'Admin' }}</strong>
                                        @if ($ph->loai === 'email')
                                            <span class="badge-loai blue" style="font-size:0.65rem"><i
                                                    class="fas fa-paper-plane"></i> Email</span>
                                        @else
                                            <span class="badge-loai gray" style="font-size:0.65rem"><i
                                                    class="fas fa-lock"></i> Nội bộ</span>
                                        @endif
                                        <span class="crm-reply-time">{{ $ph->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <div class="crm-reply-content">{!! nl2br(e($ph->noiDung)) !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Timeline lịch sử --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    <i class="fas fa-timeline" style="color:#06b6d4"></i>
                    Lịch sử xử lý
                    <span class="crm-badge-count">{{ $lienHe->lichSu->count() }}</span>
                </div>
                <div class="crm-card-body" id="timeline-list">
                    @if ($lienHe->lichSu->isEmpty())
                        <div style="text-align:center;color:#94a3b8;font-size:0.82rem;padding:20px 0">
                            <i class="fas fa-clock-rotate-left"
                                style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.4"></i>
                            Chưa có lịch sử. Mọi thay đổi sẽ được ghi lại tại đây.
                        </div>
                    @else
                        <div class="crm-timeline">
                            @foreach ($lienHe->lichSu as $ls)
                                @php
                                    $info = $lichSuInfo[$ls->hanhDong] ?? [
                                        'label' => $ls->hanhDong,
                                        'icon' => 'fa-circle-info',
                                        'color' => '#94a3b8',
                                    ];
                                @endphp
                                <div class="crm-tl-item">
                                    <div class="crm-tl-icon"
                                        style="background:{{ $info['color'] }}1a;color:{{ $info['color'] }}">
                                        <i class="fas {{ $info['icon'] }}"></i>
                                    </div>
                                    <div class="crm-tl-body">
                                        <div class="crm-tl-action">{{ $info['label'] }}</div>
                                        @if ($ls->noiDung)
                                            <div class="crm-tl-desc">{{ $ls->noiDung }}</div>
                                        @endif
                                        @if ($ls->giaTriCu && $ls->giaTriMoi)
                                            <div class="crm-tl-change">
                                                <span class="tl-old">{{ $ls->giaTriCu }}</span>
                                                <i class="fas fa-arrow-right" style="font-size:0.6rem;color:#94a3b8"></i>
                                                <span class="tl-new">{{ $ls->giaTriMoi }}</span>
                                            </div>
                                        @endif
                                        <div class="crm-tl-meta">
                                            <i class="fas fa-user" style="font-size:0.65rem"></i>
                                            {{ $ls->tenNguoiThucHien ?? 'Hệ thống' }}
                                            &nbsp;·&nbsp;
                                            {{ $ls->created_at->format('d/m/Y H:i') }}
                                            <span style="color:#94a3b8">({{ $ls->created_at->diffForHumans() }})</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection

@section('script')
    <script>
        const REPLY_URL = '{{ route('admin.lien-he.reply.store', $lienHe->lienHeId) }}';
        const CSRF = '{{ csrf_token() }}';

        // ── Tab toggle ──────────────────────────────────────────────────────────────
        document.querySelectorAll('.crm-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.crm-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('reply-loai').value = this.dataset.loai;
                const isEmail = this.dataset.loai === 'email';
                document.getElementById('reply-content').placeholder = isEmail ?
                    'Ghi nhận nội dung email đã gửi cho khách...' :
                    'Nhập ghi chú / trao đổi nội bộ...';
            });
        });

        // ── Submit reply (AJAX) ──────────────────────────────────────────────────────
        document.getElementById('reply-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const content = document.getElementById('reply-content').value.trim();
            if (!content) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nội dung trống!',
                    text: 'Vui lòng nhập nội dung phản hồi.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            const btn = document.getElementById('reply-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang gửi...';

            try {
                const res = await fetch(REPLY_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        noiDung: content,
                        loai: document.getElementById('reply-loai').value,
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    // Append reply to list
                    appendReply(data.phanHoi);
                    document.getElementById('reply-content').value = '';

                    // Reload timeline
                    setTimeout(() => location.reload(), 500);
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không thể gửi phản hồi. Vui lòng thử lại.',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus me-1"></i> Thêm phản hồi';
            }
        });

        function appendReply(ph) {
            let list = document.getElementById('reply-list');
            if (!list) {
                // Create reply-list section dynamically if first reply
                location.reload();
                return;
            }
            const isEmail = ph.loai === 'email';
            const init = (ph.tenNguoiGui || 'A').charAt(0).toUpperCase();
            const html = `
        <div class="crm-reply-item ${isEmail ? 'email' : ''}">
            <div class="crm-reply-avatar">${init}</div>
            <div class="crm-reply-bubble">
                <div class="crm-reply-meta">
                    <strong>${ph.tenNguoiGui || 'Admin'}</strong>
                    <span class="badge-loai ${isEmail ? 'blue' : 'gray'}" style="font-size:.65rem">
                        <i class="fas ${isEmail ? 'fa-paper-plane' : 'fa-lock'}"></i>
                        ${isEmail ? 'Email' : 'Nội bộ'}
                    </span>
                    <span class="crm-reply-time">Vừa xong</span>
                </div>
                <div class="crm-reply-content">${ph.noiDung.replace(/\n/g,'<br>')}</div>
            </div>
        </div>
    `;
            list.insertAdjacentHTML('beforeend', html);
            list.scrollTop = list.scrollHeight;
        }

        // ── Delete confirm ──────────────────────────────────────────────────────────
        function confirmDelete() {
            Swal.fire({
                title: 'Xóa liên hệ?',
                text: 'Liên hệ này sẽ được chuyển vào thùng rác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then(r => {
                if (r.isConfirmed) document.getElementById('delete-form').submit();
            });
        }
    </script>
@endsection
