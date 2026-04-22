@extends('layouts.admin')

@section('title', 'Chi tiết liên hệ #' . $lienHe->lienHeId)
@section('page-title', 'Chi tiết liên hệ')
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
    @if (!$canManage)
        <div class="crm-alert" style="margin-bottom:16px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8">
            <i class="fas fa-eye me-2"></i>Admin đang ở chế độ xem. Mọi cập nhật xử lý, gán phụ trách và phản hồi do bộ phận nhân viên thực hiện.
        </div>
    @endif

    <div class="crm-page-header">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($portalRoutePrefix . '.lien-he.index') }}" class="crm-btn-back"><i class="fas fa-arrow-left"></i></a>
            <div>
                <div class="crm-page-title">
                    Liên hệ #{{ $lienHe->lienHeId }}
                    <span class="badge-ts {{ $tsColors[$lienHe->trangThai] ?? 'gray' }}">{{ $tsLabels[$lienHe->trangThai] ?? '?' }}</span>
                    <span class="badge-loai {{ $loaiColors[$lienHe->loaiLienHe] ?? 'gray' }}">{{ $loaiLabels[$lienHe->loaiLienHe] ?? 'Khác' }}</span>
                </div>
                <div style="font-size:0.8rem;color:#8899a6;margin-top:2px">Nhận lúc {{ $lienHe->created_at->format('H:i, d/m/Y') }} · {{ $lienHe->created_at->diffForHumans() }}</div>
            </div>
        </div>
        @if ($canManage)
            <div style="display:flex;gap:8px">
                <form method="POST" action="{{ route($portalRoutePrefix . '.lien-he.destroy', $lienHe->lienHeId) }}" id="delete-form" style="display:none">
                    @csrf @method('DELETE')
                </form>
                <button type="button" class="crm-btn crm-btn-danger" onclick="confirmDelete()"><i class="fas fa-trash me-1"></i> Xóa</button>
            </div>
        @endif
    </div>

    <div class="crm-layout">
        <div class="crm-left">
            <div class="crm-card">
                <div class="crm-card-header"><i class="fas fa-user-circle" style="color:#6366f1"></i> Thông tin người gửi</div>
                <div class="crm-card-body">
                    <div class="crm-info-row">
                        <div class="crm-avatar-lg">{{ mb_strtoupper(mb_substr($lienHe->hoTen, 0, 2)) }}</div>
                        <div>
                            <div style="font-size:1.05rem;font-weight:700;color:#1a2b3c">{{ $lienHe->hoTen }}</div>
                            <div style="font-size:0.78rem;color:#8899a6">ID: #{{ $lienHe->lienHeId }}</div>
                        </div>
                    </div>
                    <div class="crm-field"><span class="crm-field-label"><i class="fas fa-envelope"></i> Email</span><span class="crm-field-val">@if ($lienHe->email)<a href="mailto:{{ $lienHe->email }}" style="color:#6366f1">{{ $lienHe->email }}</a>@else — @endif</span></div>
                    <div class="crm-field"><span class="crm-field-label"><i class="fas fa-phone"></i> SĐT</span><span class="crm-field-val">{{ $lienHe->soDienThoai ?? '—' }}</span></div>
                    <div class="crm-field"><span class="crm-field-label"><i class="fas fa-clock"></i> Gửi lúc</span><span class="crm-field-val">{{ $lienHe->created_at->format('d/m/Y H:i:s') }}</span></div>
                    @if ($lienHe->thoiGianXuLy)
                        <div class="crm-field"><span class="crm-field-label"><i class="fas fa-check-double"></i> Hoàn tất</span><span class="crm-field-val">{{ $lienHe->thoiGianXuLy->format('d/m/Y H:i') }}</span></div>
                    @endif
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-header"><i class="fas fa-message" style="color:#f59e0b"></i> Nội dung liên hệ</div>
                <div class="crm-card-body">
                    <div style="font-weight:700;font-size:0.95rem;color:#1a2b3c;margin-bottom:10px">{{ $lienHe->tieuDe }}</div>
                    <div class="crm-message-box">{!! nl2br(e($lienHe->noiDung)) !!}</div>
                </div>
            </div>

            @if ($canManage)
                <div class="crm-card">
                    <div class="crm-card-header"><i class="fas fa-sliders" style="color:#10b981"></i> Cập nhật xử lý</div>
                    <div class="crm-card-body">
                        <form method="POST" action="{{ route($portalRoutePrefix . '.lien-he.update', $lienHe->lienHeId) }}">
                            @csrf @method('PUT')
                            <div class="crm-form-group">
                                <label class="crm-label">Trạng thái</label>
                                <select name="trangThai" class="crm-select">
                                    @foreach ($tsLabels as $val => $label)
                                        <option value="{{ $val }}" {{ $lienHe->trangThai == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Loại liên hệ</label>
                                <select name="loaiLienHe" class="crm-select">
                                    @foreach ($loaiLabels as $val => $label)
                                        <option value="{{ $val }}" {{ $lienHe->loaiLienHe === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Ghi chú nội bộ</label>
                                <textarea name="ghiChuNoiBo" class="crm-textarea" rows="3" placeholder="Ghi chú nội bộ...">{{ $lienHe->ghiChuNoiBo }}</textarea>
                            </div>
                            <button type="submit" class="crm-btn crm-btn-primary" style="width:100%"><i class="fas fa-save me-1"></i> Lưu thay đổi</button>
                        </form>
                    </div>
                </div>

                <div class="crm-card">
                    <div class="crm-card-header"><i class="fas fa-user-check" style="color:#3b82f6"></i> Người phụ trách</div>
                    <div class="crm-card-body">
                        @if ($lienHe->nguoiPhuTrach)
                            <div class="crm-assignee-current">
                                <div class="crm-avatar-sm">{{ mb_strtoupper(mb_substr($lienHe->nguoiPhuTrach->hoSoNguoiDung?->hoTen ?? ($lienHe->nguoiPhuTrach->taiKhoan ?? 'N'), 0, 1)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:0.88rem">{{ $lienHe->nguoiPhuTrach->hoSoNguoiDung?->hoTen ?? $lienHe->nguoiPhuTrach->taiKhoan }}</div>
                                    <div style="font-size:0.72rem;color:#94a3b8">Đang phụ trách</div>
                                </div>
                            </div>
                        @endif
                        <form method="POST" action="{{ route($portalRoutePrefix . '.lien-he.assign', $lienHe->lienHeId) }}">
                            @csrf @method('PATCH')
                            <div style="display:flex;gap:8px">
                                <select name="nguoiPhuTrachId" class="crm-select" style="flex:1">
                                    <option value="">-- Bỏ gán --</option>
                                    @foreach ($nhanVienList as $nv)
                                        <option value="{{ $nv->taiKhoanId }}" {{ $lienHe->nguoiPhuTrachId == $nv->taiKhoanId ? 'selected' : '' }}>
                                            {{ $nv->hoSoNguoiDung?->hoTen ?? $nv->taiKhoan }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="crm-btn crm-btn-secondary">Gán</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="crm-card">
                    <div class="crm-card-header"><i class="fas fa-circle-info" style="color:#3b82f6"></i> Thông tin xử lý</div>
                    <div class="crm-card-body">
                        <div class="crm-field"><span class="crm-field-label">Trạng thái</span><span class="crm-field-val">{{ $tsLabels[$lienHe->trangThai] ?? '—' }}</span></div>
                        <div class="crm-field"><span class="crm-field-label">Loại liên hệ</span><span class="crm-field-val">{{ $loaiLabels[$lienHe->loaiLienHe] ?? '—' }}</span></div>
                        <div class="crm-field"><span class="crm-field-label">Phụ trách</span><span class="crm-field-val">{{ $lienHe->nguoiPhuTrach?->hoSoNguoiDung?->hoTen ?? 'Chưa gán' }}</span></div>
                        <div class="crm-field" style="align-items:flex-start">
                            <span class="crm-field-label">Ghi chú nội bộ</span>
                            <span class="crm-field-val">{{ $lienHe->ghiChuNoiBo ?: 'Chưa có ghi chú nội bộ.' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="crm-right">
            @if ($canManage)
                <div class="crm-card">
                    <div class="crm-card-header"><i class="fas fa-reply" style="color:#6366f1"></i> Phản hồi nội bộ</div>
                    <div class="crm-card-body">
                        <form id="reply-form">
                            @csrf
                            <div class="crm-reply-tabs" id="reply-tabs">
                                <button type="button" class="crm-tab active" data-loai="noi_bo"><i class="fas fa-comment-dots"></i> Ghi chú nội bộ</button>
                                <button type="button" class="crm-tab" data-loai="email"><i class="fas fa-paper-plane"></i> Ghi nhận gửi email</button>
                            </div>
                            <input type="hidden" name="loai" id="reply-loai" value="noi_bo">
                            <textarea name="noiDung" id="reply-content" class="crm-textarea" rows="3" placeholder="Nhập nội dung phản hồi/ghi chú..."></textarea>
                            <div style="display:flex;justify-content:flex-end;margin-top:8px">
                                <button type="submit" class="crm-btn crm-btn-primary" id="reply-submit"><i class="fas fa-plus me-1"></i> Thêm phản hồi</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if ($lienHe->phanHoi->isNotEmpty())
                <div class="crm-card">
                    <div class="crm-card-header"><i class="fas fa-comments" style="color:#8b5cf6"></i> Thread phản hồi <span class="crm-badge-count">{{ $lienHe->phanHoi->count() }}</span></div>
                    <div class="crm-card-body" id="reply-list">
                        @foreach ($lienHe->phanHoi as $ph)
                            <div class="crm-reply-item {{ $ph->loai === 'email' ? 'email' : '' }}">
                                <div class="crm-reply-avatar">{{ mb_strtoupper(mb_substr($ph->tenNguoiGui ?? 'N', 0, 1)) }}</div>
                                <div class="crm-reply-bubble">
                                    <div class="crm-reply-meta">
                                        <strong>{{ $ph->tenNguoiGui ?? 'Nhân sự' }}</strong>
                                        <span class="badge-loai {{ $ph->loai === 'email' ? 'blue' : 'gray' }}" style="font-size:0.65rem">
                                            <i class="fas {{ $ph->loai === 'email' ? 'fa-paper-plane' : 'fa-lock' }}"></i>
                                            {{ $ph->loai === 'email' ? 'Email' : 'Nội bộ' }}
                                        </span>
                                        <span class="crm-reply-time">{{ $ph->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <div class="crm-reply-content">{!! nl2br(e($ph->noiDung)) !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="crm-card">
                <div class="crm-card-header"><i class="fas fa-timeline" style="color:#06b6d4"></i> Lịch sử xử lý <span class="crm-badge-count">{{ $lienHe->lichSu->count() }}</span></div>
                <div class="crm-card-body" id="timeline-list">
                    @if ($lienHe->lichSu->isEmpty())
                        <div style="text-align:center;color:#94a3b8;font-size:0.82rem;padding:20px 0">Chưa có lịch sử xử lý.</div>
                    @else
                        <div class="crm-timeline">
                            @foreach ($lienHe->lichSu as $ls)
                                @php
                                    $info = $lichSuInfo[$ls->hanhDong] ?? ['label' => $ls->hanhDong, 'icon' => 'fa-circle-info', 'color' => '#94a3b8'];
                                @endphp
                                <div class="crm-tl-item">
                                    <div class="crm-tl-icon" style="background:{{ $info['color'] }}1a;color:{{ $info['color'] }}"><i class="fas {{ $info['icon'] }}"></i></div>
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
                                        <div class="crm-tl-meta"><i class="fas fa-user" style="font-size:0.65rem"></i> {{ $ls->tenNguoiThucHien ?? 'Hệ thống' }} · {{ $ls->created_at->format('d/m/Y H:i') }} <span style="color:#94a3b8">({{ $ls->created_at->diffForHumans() }})</span></div>
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
    @if ($canManage)
        <script>
            const REPLY_URL = @json(route($portalRoutePrefix . '.lien-he.reply.store', $lienHe->lienHeId));
            const CSRF = '{{ csrf_token() }}';

            document.querySelectorAll('.crm-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.crm-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('reply-loai').value = this.dataset.loai;
                });
            });

            document.getElementById('reply-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const content = document.getElementById('reply-content').value.trim();
                if (!content) return;

                const res = await fetch(REPLY_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
                    body: JSON.stringify({noiDung: content, loai: document.getElementById('reply-loai').value}),
                });

                const data = await res.json();
                if (data.success) location.reload();
            });

            function confirmDelete() {
                Swal.fire({
                    title: 'Xóa liên hệ?',
                    text: 'Liên hệ này sẽ được chuyển vào thùng rác.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                }).then(r => {
                    if (r.isConfirmed) document.getElementById('delete-form').submit();
                });
            }
        </script>
    @endif
@endsection
