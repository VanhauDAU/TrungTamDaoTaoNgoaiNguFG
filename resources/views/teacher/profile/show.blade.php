@extends('layouts.internal')

@section('title', 'Hồ sơ giáo viên')
@section('page-title', 'Hồ sơ của tôi')
@section('breadcrumb', 'Cổng giáo viên · Hồ sơ cá nhân')

@section('stylesheet')
<style>
    /* ── Hero card ─────────────────────────────────────── */
    .profile-hero {
        background: linear-gradient(135deg, #0f2944 0%, #0d3b5e 50%, #0a4d73 100%);
        border-radius: 20px;
        padding: 2rem 2rem 1.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .profile-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 80% 20%, rgba(39,196,181,.18), transparent 45%),
            radial-gradient(circle at 10% 80%, rgba(56,130,246,.12), transparent 40%);
        pointer-events: none;
    }
    .profile-hero__avatar-wrap { position: relative; display: inline-block; }
    .profile-hero__avatar {
        width: 96px; height: 96px; border-radius: 50%; object-fit: cover;
        border: 3px solid rgba(255,255,255,.22);
        box-shadow: 0 6px 24px rgba(0,0,0,.32);
    }
    .profile-hero__avatar-initial {
        width: 96px; height: 96px; border-radius: 50%;
        background: linear-gradient(135deg, #27c4b5, #3b82f6);
        border: 3px solid rgba(255,255,255,.22);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 700; color: #fff;
        box-shadow: 0 6px 24px rgba(0,0,0,.32);
    }
    .profile-hero__online-dot {
        position: absolute; bottom: 4px; right: 4px;
        width: 13px; height: 13px; border-radius: 50%;
        background: #22c55e; border: 2px solid #0d3b5e;
    }
    .profile-hero__name  { font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: .2rem; }
    .profile-hero__role  { font-size: .83rem; color: rgba(255,255,255,.6); margin-bottom: .65rem; }
    .profile-hero__badges { display: flex; flex-wrap: wrap; gap: .4rem; }

    .profile-badge {
        display: inline-flex; align-items: center; gap: .28rem;
        padding: .22rem .65rem; border-radius: 999px;
        font-size: .72rem; font-weight: 600;
    }
    .profile-badge--teal   { background: rgba(39,196,181,.18);  color: #27c4b5; border: 1px solid rgba(39,196,181,.3); }
    .profile-badge--blue   { background: rgba(59,130,246,.18);   color: #60a5fa; border: 1px solid rgba(59,130,246,.3); }
    .profile-badge--green  { background: rgba(34,197,94,.18);    color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
    .profile-badge--red    { background: rgba(239,68,68,.18);    color: #f87171; border: 1px solid rgba(239,68,68,.3); }
    .profile-badge--purple { background: rgba(168,85,247,.18);   color: #c084fc; border: 1px solid rgba(168,85,247,.3); }
    .profile-badge--orange { background: rgba(251,146,60,.18);   color: #fb923c; border: 1px solid rgba(251,146,60,.3); }

    /* ── Mini stats trong hero ──────────────────────────── */
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: .6rem;
        margin-top: 1.1rem;
    }
    .profile-stat {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 10px;
        padding: .65rem .9rem;
    }
    .profile-stat__value { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: .05rem; }
    .profile-stat__label { font-size: .68rem; color: rgba(255,255,255,.5); font-weight: 500; }

    /* ── Info card ──────────────────────────────────────── */
    .info-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5eaf2;
        box-shadow: 0 2px 10px rgba(15,41,68,.05);
        overflow: hidden;
        height: 100%;
    }
    .info-card__header {
        display: flex; align-items: center; gap: .6rem;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid #f0f4f9;
        background: linear-gradient(90deg, #f8fafc, #fff);
    }
    .info-card__header-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .82rem; flex-shrink: 0;
    }
    .icon--teal   { background: #e6faf8; color: #0d9488; }
    .icon--blue   { background: #eff6ff; color: #2563eb; }
    .icon--purple { background: #f5f3ff; color: #7c3aed; }
    .icon--orange { background: #fff7ed; color: #c2410c; }

    .info-card__title { font-size: .88rem; font-weight: 700; color: #1e293b; margin: 0; }
    .info-card__body  { padding: 1rem 1.25rem; }

    /* ── Field grid 3 cột ───────────────────────────────── */
    .field-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .6rem .9rem;
    }
    .field-grid--2 { grid-template-columns: repeat(2, 1fr); }

    .field-item { min-width: 0; }
    .field-item__label {
        font-size: .69rem;
        font-weight: 600;
        color: #94a3b8;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: .18rem;
    }
    .field-item__value {
        font-size: .84rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .field-item__value.empty {
        color: #cbd5e1;
        font-weight: 400;
        font-style: italic;
    }
    /* field chiếm 2 cột khi cần (địa chỉ, ghi chú...) */
    .field-item--span2 { grid-column: span 2; }
    .field-item--span3 { grid-column: span 3; }

    /* ── Divider nhẹ giữa các nhóm field ───────────────── */
    .field-section-divider {
        grid-column: 1 / -1;
        height: 1px;
        background: #f1f5f9;
        margin: .25rem 0;
    }

    /* ── Badge inline (trạng thái) ──────────────────────── */
    .status-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .75rem; font-weight: 600;
        padding: .22rem .6rem; border-radius: 8px;
    }
    .status-badge--green  { background: #dcfce7; color: #166534; }
    .status-badge--red    { background: #fee2e2; color: #991b1b; }
    .status-badge--yellow { background: #fef3c7; color: #92400e; }
    .dot {
        width: 7px; height: 7px; border-radius: 50%;
        display: inline-block; flex-shrink: 0;
    }
    .dot--green  { background: #22c55e; }
    .dot--red    { background: #dc2626; }
    .dot--yellow { background: #f59e0b; }

    /* ── Hồ sơ quy định block ───────────────────────────── */
    .hoso-block {
        border: 1.5px dashed #cbd5e1;
        border-radius: 10px;
        padding: .85rem 1rem;
        background: #f8fafc;
        display: flex; align-items: flex-start; gap: .85rem;
        margin-bottom: .75rem;
    }
    .hoso-block.complete { border-color: #86efac; background: #f0fdf4; }
    .hoso-block.draft    { border-color: #fcd34d; background: #fffbeb; }
    .hoso-block__icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .hoso-block.complete .hoso-block__icon { background: #bbf7d0; color: #16a34a; }
    .hoso-block.draft    .hoso-block__icon { background: #fef3c7; color: #d97706; }
    .hoso-block.none     .hoso-block__icon { background: #f1f5f9; color: #94a3b8; }
    .hoso-block__title { font-size: .84rem; font-weight: 700; color: #1e293b; margin-bottom: .1rem; }
    .hoso-block__sub   { font-size: .76rem; color: #64748b; }

    .hoso-content {
        font-size: .78rem; color: #475569; line-height: 1.7;
        background: #f8fafc; border-radius: 8px;
        padding: .75rem .9rem;
        border: 1px solid #e2e8f0;
        max-height: 160px; overflow-y: auto;
    }

    @media (max-width: 767px) {
        .profile-hero { padding: 1.25rem; }
        .profile-stats { grid-template-columns: 1fr 1fr; }
        .field-grid { grid-template-columns: repeat(2, 1fr); }
        .field-item--span3 { grid-column: span 2; }
    }
    @media (max-width: 480px) {
        .field-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $hoSo       = $user->hoSoNguoiDung;
    $nhanSu     = $user->nhanSu;
    $nhanSuHoSo = $user->nhanSuHoSo;
    $hoTen      = $hoSo?->hoTen ?? $user->taiKhoan;
    $initial    = mb_strtoupper(mb_substr($hoTen, 0, 1, 'UTF-8'));
    $isActive   = (int) $user->trangThai === 1;

    $gioiTinhMap     = [0 => 'Nam', 1 => 'Nữ', 2 => 'Khác'];
    $gioiTinhLabel   = $gioiTinhMap[$hoSo?->gioiTinh ?? -1] ?? null;

    $hdMap = [
        'chinh_thuc'         => 'Chính thức',
        'thu_viec'           => 'Thử việc',
        'cong_tac_vien'      => 'Cộng tác viên',
        'hop_dong_ngan_han'  => 'HĐ ngắn hạn',
    ];
    $loaiHopDong = $hdMap[$nhanSu?->loaiHopDong ?? ''] ?? $nhanSu?->loaiHopDong;

    $avatarUrl = $user->getAvatarUrl();
    $hasAvatar = $avatarUrl && !str_ends_with($avatarUrl, 'user-default.png');
@endphp

<div class="container-fluid px-0">

    {{-- ── HERO ── --}}
    <div class="profile-hero">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            <div class="profile-hero__avatar-wrap">
                @if($hasAvatar)
                    <img src="{{ $avatarUrl }}" alt="{{ $hoTen }}" class="profile-hero__avatar">
                @else
                    <div class="profile-hero__avatar-initial">{{ $initial }}</div>
                @endif
                @if($isActive)<span class="profile-hero__online-dot"></span>@endif
            </div>
            <div class="flex-grow-1">
                <h1 class="profile-hero__name">{{ $hoTen }}</h1>
                <div class="profile-hero__role">
                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    {{ $nhanSu?->chucVu ?? 'Giáo viên' }}
                    @if($nhanSu?->coSoDaoTao) &nbsp;·&nbsp; {{ $nhanSu->coSoDaoTao->tenCoSo }} @endif
                </div>
                <div class="profile-hero__badges">
                    <span class="profile-badge profile-badge--teal"><i class="fas fa-id-badge"></i> {{ $user->taiKhoan }}</span>
                    @if($nhanSu?->maNhanVien)
                        <span class="profile-badge profile-badge--blue"><i class="fas fa-fingerprint"></i> {{ $nhanSu->maNhanVien }}</span>
                    @endif
                    @if($isActive)
                        <span class="profile-badge profile-badge--green"><i class="fas fa-circle-check"></i> Đang hoạt động</span>
                    @else
                        <span class="profile-badge profile-badge--red"><i class="fas fa-circle-xmark"></i> Bị khóa</span>
                    @endif
                    @if($nhanSu?->bangCap)
                        <span class="profile-badge profile-badge--purple"><i class="fas fa-graduation-cap"></i> {{ $nhanSu->bangCap }}</span>
                    @endif
                    @if($loaiHopDong)
                        <span class="profile-badge profile-badge--orange"><i class="fas fa-file-contract"></i> {{ $loaiHopDong }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <div class="profile-stat">
                <div class="profile-stat__value">{{ $nhanSu?->chuyenMon ?? '—' }}</div>
                <div class="profile-stat__label">Chuyên môn</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat__value">{{ $nhanSu?->hocVi ?? '—' }}</div>
                <div class="profile-stat__label">Học vị</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat__value">{{ $nhanSu?->ngayVaoLam?->format('d/m/Y') ?? '—' }}</div>
                <div class="profile-stat__label">Ngày vào làm</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat__value">{{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}</div>
                <div class="profile-stat__label">Email</div>
            </div>
        </div>
    </div>

    {{-- ── CARDS ── --}}
    <div class="row g-3">

        {{-- ── Thông tin cá nhân ─────────────────────────── --}}
        <div class="col-12">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--teal"><i class="fas fa-user"></i></div>
                    <h2 class="info-card__title">Thông tin cá nhân</h2>
                </div>
                <div class="info-card__body">
                    <div class="field-grid">

                        <div class="field-item">
                            <div class="field-item__label">Họ và tên</div>
                            <div class="field-item__value {{ $hoSo?->hoTen ? '' : 'empty' }}">{{ $hoSo?->hoTen ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Giới tính</div>
                            <div class="field-item__value {{ $gioiTinhLabel ? '' : 'empty' }}">{{ $gioiTinhLabel ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Ngày sinh</div>
                            <div class="field-item__value {{ $hoSo?->ngaySinh ? '' : 'empty' }}">
                                {{ $hoSo?->ngaySinh ? \Carbon\Carbon::parse($hoSo->ngaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">CCCD / CMND</div>
                            <div class="field-item__value {{ $hoSo?->cccd ? '' : 'empty' }}">{{ $hoSo?->cccd ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Số điện thoại</div>
                            <div class="field-item__value {{ $hoSo?->soDienThoai ? '' : 'empty' }}">{{ $hoSo?->soDienThoai ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Zalo</div>
                            <div class="field-item__value {{ $hoSo?->zalo ? '' : 'empty' }}">{{ $hoSo?->zalo ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item field-item--span3">
                            <div class="field-item__label">Địa chỉ</div>
                            <div class="field-item__value {{ $hoSo?->diaChi ? '' : 'empty' }}" style="white-space:normal;">{{ $hoSo?->diaChi ?? 'Chưa cập nhật' }}</div>
                        </div>

                        @if($hoSo?->ghiChu)
                        <div class="field-item field-item--span3">
                            <div class="field-item__label">Ghi chú</div>
                            <div class="field-item__value" style="white-space:normal;">{{ $hoSo->ghiChu }}</div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Thông tin nhân sự ────────────────────────── --}}
        <div class="col-12">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--blue"><i class="fas fa-briefcase"></i></div>
                    <h2 class="info-card__title">Thông tin nhân sự</h2>
                </div>
                <div class="info-card__body">
                    <div class="field-grid">

                        <div class="field-item">
                            <div class="field-item__label">Mã nhân viên</div>
                            <div class="field-item__value {{ $nhanSu?->maNhanVien ? '' : 'empty' }}">{{ $nhanSu?->maNhanVien ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Chức vụ</div>
                            <div class="field-item__value {{ $nhanSu?->chucVu ? '' : 'empty' }}">{{ $nhanSu?->chucVu ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Cơ sở đào tạo</div>
                            <div class="field-item__value {{ $nhanSu?->coSoDaoTao ? '' : 'empty' }}">{{ $nhanSu?->coSoDaoTao?->tenCoSo ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Học vị</div>
                            <div class="field-item__value {{ $nhanSu?->hocVi ? '' : 'empty' }}">{{ $nhanSu?->hocVi ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Bằng cấp</div>
                            <div class="field-item__value {{ $nhanSu?->bangCap ? '' : 'empty' }}">{{ $nhanSu?->bangCap ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Chuyên môn</div>
                            <div class="field-item__value {{ $nhanSu?->chuyenMon ? '' : 'empty' }}">{{ $nhanSu?->chuyenMon ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Loại hợp đồng</div>
                            <div class="field-item__value {{ $loaiHopDong ? '' : 'empty' }}">{{ $loaiHopDong ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Ngày vào làm</div>
                            <div class="field-item__value {{ $nhanSu?->ngayVaoLam ? '' : 'empty' }}">{{ $nhanSu?->ngayVaoLam?->format('d/m/Y') ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Trạng thái nhân sự</div>
                            <div class="field-item__value" style="overflow:visible;">
                                @if($nhanSu)
                                    @if((int)$nhanSu->trangThai === 1)
                                        <span class="status-badge status-badge--green"><span class="dot dot--green"></span>Đang làm việc</span>
                                    @else
                                        <span class="status-badge status-badge--red"><span class="dot dot--red"></span>Nghỉ việc</span>
                                    @endif
                                @else
                                    <span class="empty">Chưa có dữ liệu</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Thông tin tài khoản & Hồ sơ (2 cột) ─────── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--purple"><i class="fas fa-shield-halved"></i></div>
                    <h2 class="info-card__title">Thông tin tài khoản</h2>
                </div>
                <div class="info-card__body">
                    <div class="field-grid">

                        <div class="field-item">
                            <div class="field-item__label">Tên đăng nhập</div>
                            <div class="field-item__value">{{ $user->taiKhoan }}</div>
                        </div>

                        <div class="field-item field-item--span2">
                            <div class="field-item__label">Email</div>
                            <div class="field-item__value" style="overflow:visible;white-space:normal;">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span style="font-size:.68rem;color:#16a34a;font-weight:600;margin-left:.3rem;"><i class="fas fa-circle-check"></i> Đã xác thực</span>
                                @else
                                    <span style="font-size:.68rem;color:#d97706;font-weight:600;margin-left:.3rem;"><i class="fas fa-circle-exclamation"></i> Chưa xác thực</span>
                                @endif
                            </div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Vai trò</div>
                            <div class="field-item__value">{{ $user->getRoleLabel() }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Phương thức đăng nhập</div>
                            <div class="field-item__value">{{ $user->getAuthProviderLabel() }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Trạng thái tài khoản</div>
                            <div class="field-item__value" style="overflow:visible;">
                                @if($isActive)
                                    <span class="status-badge status-badge--green"><span class="dot dot--green"></span>Đang hoạt động</span>
                                @else
                                    <span class="status-badge status-badge--red"><span class="dot dot--red"></span>Bị khóa</span>
                                @endif
                            </div>
                        </div>

                        <div class="field-item field-item--span2">
                            <div class="field-item__label">Đăng nhập lần cuối</div>
                            <div class="field-item__value {{ $user->lastLogin ? '' : 'empty' }}">{{ $user->lastLogin?->format('H:i · d/m/Y') ?? 'Chưa có dữ liệu' }}</div>
                        </div>

                        <div class="field-item">
                            <div class="field-item__label">Ngày tạo tài khoản</div>
                            <div class="field-item__value">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Hồ sơ nhân sự & quy định ────────────────── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--orange"><i class="fas fa-folder-open"></i></div>
                    <h2 class="info-card__title">Hồ sơ nhân sự & Quy định</h2>
                </div>
                <div class="info-card__body">
                    @if($nhanSuHoSo)
                        @php $isComplete = $nhanSuHoSo->isCompleted(); @endphp
                        <div class="hoso-block {{ $isComplete ? 'complete' : 'draft' }}">
                            <div class="hoso-block__icon">
                                <i class="fas fa-{{ $isComplete ? 'circle-check' : 'hourglass-half' }}"></i>
                            </div>
                            <div>
                                <div class="hoso-block__title">{{ $nhanSuHoSo->tieuDeMauSnapshot ?? 'Hồ sơ nhân sự' }}</div>
                                <div class="hoso-block__sub">
                                    Mã: <strong>{{ $nhanSuHoSo->maHoSo }}</strong>
                                    &nbsp;·&nbsp;
                                    @if($isComplete)
                                        <span style="color:#16a34a;font-weight:600;">Hoàn tất</span>
                                    @else
                                        <span style="color:#d97706;font-weight:600;">Bản nháp</span>
                                    @endif
                                </div>
                                @if($nhanSuHoSo->ghiChuHoSo)
                                    <div class="hoso-block__sub mt-1"><i class="fas fa-comment-dots me-1"></i>{{ $nhanSuHoSo->ghiChuHoSo }}</div>
                                @endif
                            </div>
                        </div>

                        @if($nhanSuHoSo->noiDungQuyDinhSnapshot)
                            <div class="hoso-content">{!! nl2br(e($nhanSuHoSo->noiDungQuyDinhSnapshot)) !!}</div>
                        @endif
                    @else
                        <div class="hoso-block none">
                            <div class="hoso-block__icon"><i class="fas fa-folder-minus"></i></div>
                            <div>
                                <div class="hoso-block__title">Chưa có hồ sơ quy định</div>
                                <div class="hoso-block__sub">Bộ phận nhân sự sẽ gắn hồ sơ sau khi hoàn thiện thủ tục.</div>
                            </div>
                        </div>
                    @endif

                    {{-- Thông tin bổ sung từ hồ sơ --}}
                    @if($hoSo?->nguonBietDen || $hoSo?->trinhDoHienTai || $hoSo?->ngonNguMucTieu)
                    <hr style="border-color:#f0f4f9;margin:.75rem 0;">
                    <div class="field-grid">
                        @if($hoSo?->trinhDoHienTai)
                        <div class="field-item">
                            <div class="field-item__label">Trình độ hiện tại</div>
                            <div class="field-item__value">{{ $hoSo->trinhDoHienTai }}</div>
                        </div>
                        @endif
                        @if($hoSo?->ngonNguMucTieu)
                        <div class="field-item">
                            <div class="field-item__label">Ngôn ngữ mục tiêu</div>
                            <div class="field-item__value">{{ $hoSo->ngonNguMucTieu }}</div>
                        </div>
                        @endif
                        @if($hoSo?->nguonBietDen)
                        <div class="field-item">
                            <div class="field-item__label">Nguồn biết đến</div>
                            <div class="field-item__value">{{ $hoSo->nguonBietDen }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end row --}}
</div>
@endsection
