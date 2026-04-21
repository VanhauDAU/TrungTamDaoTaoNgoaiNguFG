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
        padding: 2rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .profile-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 80% 20%, rgba(39, 196, 181, 0.18), transparent 45%),
            radial-gradient(circle at 10% 80%, rgba(56, 130, 246, 0.12), transparent 40%);
        pointer-events: none;
    }
    .profile-hero__avatar-wrap {
        position: relative;
        display: inline-block;
    }
    .profile-hero__avatar {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,.22);
        box-shadow: 0 8px 30px rgba(0,0,0,.35);
    }
    .profile-hero__avatar-initial {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        background: linear-gradient(135deg, #27c4b5, #3b82f6);
        border: 4px solid rgba(255,255,255,.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.6rem;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,.35);
    }
    .profile-hero__online-dot {
        position: absolute;
        bottom: 6px;
        right: 6px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #22c55e;
        border: 2.5px solid #0d3b5e;
    }
    .profile-hero__name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: .25rem;
    }
    .profile-hero__role {
        font-size: .85rem;
        color: rgba(255,255,255,.65);
        margin-bottom: .75rem;
    }
    .profile-hero__badges {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .75rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
    }
    .profile-badge--teal    { background: rgba(39,196,181,.18); color: #27c4b5; border: 1px solid rgba(39,196,181,.3); }
    .profile-badge--blue    { background: rgba(59,130,246,.18);  color: #60a5fa; border: 1px solid rgba(59,130,246,.3); }
    .profile-badge--green   { background: rgba(34,197,94,.18);   color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
    .profile-badge--orange  { background: rgba(251,146,60,.18);  color: #fb923c; border: 1px solid rgba(251,146,60,.3); }
    .profile-badge--red     { background: rgba(239,68,68,.18);   color: #f87171; border: 1px solid rgba(239,68,68,.3); }
    .profile-badge--purple  { background: rgba(168,85,247,.18);  color: #c084fc; border: 1px solid rgba(168,85,247,.3); }
    .profile-badge--gray    { background: rgba(156,163,175,.15); color: #cbd5e1; border: 1px solid rgba(156,163,175,.2);}

    /* ── Info cards ─────────────────────────────────────── */
    .info-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5eaf2;
        box-shadow: 0 2px 12px rgba(15,41,68,.06);
        overflow: hidden;
        height: 100%;
    }
    .info-card__header {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid #f0f4f9;
        background: linear-gradient(90deg, #f8fafc, #fff);
    }
    .info-card__header-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }
    .icon--teal   { background: #e6faf8; color: #0d9488; }
    .icon--blue   { background: #eff6ff; color: #2563eb; }
    .icon--purple { background: #f5f3ff; color: #7c3aed; }
    .icon--orange { background: #fff7ed; color: #c2410c; }
    .info-card__title {
        font-size: .92rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .info-card__body {
        padding: 1.25rem 1.4rem;
    }

    /* ── Info rows ──────────────────────────────────────── */
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .7rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row__icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        flex-shrink: 0;
        margin-top: .1rem;
    }
    .info-row__label {
        font-size: .75rem;
        color: #94a3b8;
        font-weight: 500;
        margin-bottom: .15rem;
        letter-spacing: .01em;
    }
    .info-row__value {
        font-size: .875rem;
        color: #1e293b;
        font-weight: 600;
        word-break: break-word;
    }
    .info-row__value.empty {
        color: #cbd5e1;
        font-weight: 400;
        font-style: italic;
    }

    /* ── Stat mini cards (in hero) ───────────────────────── */
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .75rem;
        margin-top: 1.25rem;
    }
    .profile-stat {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 12px;
        padding: .8rem 1rem;
        backdrop-filter: blur(6px);
    }
    .profile-stat__value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: .1rem;
    }
    .profile-stat__label {
        font-size: .72rem;
        color: rgba(255,255,255,.55);
        font-weight: 500;
    }

    /* ── Hồ sơ quy định block ────────────────────────────── */
    .hoso-block {
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .hoso-block.complete {
        border-color: #86efac;
        background: #f0fdf4;
    }
    .hoso-block.draft {
        border-color: #fcd34d;
        background: #fffbeb;
    }
    .hoso-block__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .hoso-block.complete .hoso-block__icon { background: #bbf7d0; color: #16a34a; }
    .hoso-block.draft    .hoso-block__icon { background: #fef3c7; color: #d97706; }
    .hoso-block.none     .hoso-block__icon { background: #f1f5f9; color: #94a3b8; }
    .hoso-block__title { font-size: .875rem; font-weight: 700; color: #1e293b; margin-bottom: .1rem; }
    .hoso-block__sub   { font-size: .78rem; color: #64748b; }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 767px) {
        .profile-hero { padding: 1.5rem 1.25rem; }
        .profile-hero__name { font-size: 1.25rem; }
        .profile-stats { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $hoSo      = $user->hoSoNguoiDung;
    $nhanSu    = $user->nhanSu;
    $nhanSuHoSo = $user->nhanSuHoSo;
    $hoTen     = $hoSo?->hoTen ?? $user->taiKhoan;
    $initial   = mb_strtoupper(mb_substr($hoTen, 0, 1, 'UTF-8'));

    $trangThai = (int) $user->trangThai;
    $isActive  = $trangThai === 1;

    $gioiTinhMap = [0 => 'Nam', 1 => 'Nữ', 2 => 'Khác'];
    $gioiTinhLabel = $gioiTinhMap[$hoSo?->gioiTinh ?? -1] ?? null;

    $loaiHopDongMap = [
        'chinh_thuc'  => 'Chính thức',
        'thu_viec'    => 'Thử việc',
        'cong_tac_vien' => 'Cộng tác viên',
        'hop_dong_ngan_han' => 'Hợp đồng ngắn hạn',
    ];
    $loaiHopDongLabel = $loaiHopDongMap[$nhanSu?->loaiHopDong ?? ''] ?? ($nhanSu?->loaiHopDong ?? null);
@endphp

<div class="container-fluid px-0">

    {{-- ── HERO ── --}}
    <div class="profile-hero">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            {{-- Avatar --}}
            <div class="profile-hero__avatar-wrap">
                @php $avatarUrl = $user->getAvatarUrl(); @endphp
                @if($avatarUrl && !str_ends_with($avatarUrl, 'user-default.png'))
                    <img src="{{ $avatarUrl }}" alt="{{ $hoTen }}" class="profile-hero__avatar">
                @else
                    <div class="profile-hero__avatar-initial">{{ $initial }}</div>
                @endif
                @if($isActive)
                    <span class="profile-hero__online-dot" title="Tài khoản đang hoạt động"></span>
                @endif
            </div>

            {{-- Name + badges --}}
            <div class="flex-grow-1">
                <h1 class="profile-hero__name">{{ $hoTen }}</h1>
                <div class="profile-hero__role">
                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    {{ $nhanSu?->chucVu ?? 'Giáo viên' }}
                    @if($nhanSu?->coSoDaoTao)
                        &nbsp;·&nbsp; {{ $nhanSu->coSoDaoTao->tenCoSo }}
                    @endif
                </div>
                <div class="profile-hero__badges">
                    <span class="profile-badge profile-badge--teal">
                        <i class="fas fa-id-badge"></i>
                        {{ $user->taiKhoan }}
                    </span>
                    @if($nhanSu?->maNhanVien)
                        <span class="profile-badge profile-badge--blue">
                            <i class="fas fa-fingerprint"></i>
                            {{ $nhanSu->maNhanVien }}
                        </span>
                    @endif
                    @if($isActive)
                        <span class="profile-badge profile-badge--green">
                            <i class="fas fa-circle-check"></i> Đang hoạt động
                        </span>
                    @else
                        <span class="profile-badge profile-badge--red">
                            <i class="fas fa-circle-xmark"></i> Bị khóa
                        </span>
                    @endif
                    @if($nhanSu?->bangCap)
                        <span class="profile-badge profile-badge--purple">
                            <i class="fas fa-graduation-cap"></i>
                            {{ $nhanSu->bangCap }}
                        </span>
                    @endif
                    @if($loaiHopDongLabel)
                        <span class="profile-badge profile-badge--orange">
                            <i class="fas fa-file-contract"></i>
                            {{ $loaiHopDongLabel }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mini stats --}}
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
                <div class="profile-stat__value">
                    {{ $nhanSu?->ngayVaoLam ? $nhanSu->ngayVaoLam->format('d/m/Y') : '—' }}
                </div>
                <div class="profile-stat__label">Ngày vào làm</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat__value">
                    {{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}
                </div>
                <div class="profile-stat__label">Email</div>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT ── --}}
    <div class="row g-4">

        {{-- ── CỘT TRÁI: Thông tin cá nhân ── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--teal">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="info-card__title">Thông tin cá nhân</h2>
                </div>
                <div class="info-card__body">

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-user-circle"></i></div>
                        <div>
                            <div class="info-row__label">Họ và tên</div>
                            <div class="info-row__value {{ $hoSo?->hoTen ? '' : 'empty' }}">
                                {{ $hoSo?->hoTen ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-venus-mars"></i></div>
                        <div>
                            <div class="info-row__label">Giới tính</div>
                            <div class="info-row__value {{ $gioiTinhLabel ? '' : 'empty' }}">
                                {{ $gioiTinhLabel ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-cake-candles"></i></div>
                        <div>
                            <div class="info-row__label">Ngày sinh</div>
                            <div class="info-row__value {{ $hoSo?->ngaySinh ? '' : 'empty' }}">
                                {{ $hoSo?->ngaySinh ? \Carbon\Carbon::parse($hoSo->ngaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-id-card"></i></div>
                        <div>
                            <div class="info-row__label">CCCD / CMND</div>
                            <div class="info-row__value {{ $hoSo?->cccd ? '' : 'empty' }}">
                                {{ $hoSo?->cccd ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="info-row__label">Số điện thoại</div>
                            <div class="info-row__value {{ $hoSo?->soDienThoai ? '' : 'empty' }}">
                                {{ $hoSo?->soDienThoai ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fab fa-zalo"></i></div>
                        <div>
                            <div class="info-row__label">Zalo</div>
                            <div class="info-row__value {{ $hoSo?->zalo ? '' : 'empty' }}">
                                {{ $hoSo?->zalo ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <div class="info-row__label">Địa chỉ</div>
                            <div class="info-row__value {{ $hoSo?->diaChi ? '' : 'empty' }}">
                                {{ $hoSo?->diaChi ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    @if($hoSo?->ghiChu)
                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-note-sticky"></i></div>
                        <div>
                            <div class="info-row__label">Ghi chú</div>
                            <div class="info-row__value">{{ $hoSo->ghiChu }}</div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── CỘT PHẢI: Thông tin nhân sự ── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--blue">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h2 class="info-card__title">Thông tin nhân sự</h2>
                </div>
                <div class="info-card__body">

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-fingerprint"></i></div>
                        <div>
                            <div class="info-row__label">Mã nhân viên</div>
                            <div class="info-row__value {{ $nhanSu?->maNhanVien ? '' : 'empty' }}">
                                {{ $nhanSu?->maNhanVien ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-sitemap"></i></div>
                        <div>
                            <div class="info-row__label">Chức vụ</div>
                            <div class="info-row__value {{ $nhanSu?->chucVu ? '' : 'empty' }}">
                                {{ $nhanSu?->chucVu ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <div class="info-row__label">Học vị</div>
                            <div class="info-row__value {{ $nhanSu?->hocVi ? '' : 'empty' }}">
                                {{ $nhanSu?->hocVi ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-certificate"></i></div>
                        <div>
                            <div class="info-row__label">Bằng cấp</div>
                            <div class="info-row__value {{ $nhanSu?->bangCap ? '' : 'empty' }}">
                                {{ $nhanSu?->bangCap ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-microscope"></i></div>
                        <div>
                            <div class="info-row__label">Chuyên môn</div>
                            <div class="info-row__value {{ $nhanSu?->chuyenMon ? '' : 'empty' }}">
                                {{ $nhanSu?->chuyenMon ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-building"></i></div>
                        <div>
                            <div class="info-row__label">Cơ sở đào tạo</div>
                            <div class="info-row__value {{ $nhanSu?->coSoDaoTao ? '' : 'empty' }}">
                                {{ $nhanSu?->coSoDaoTao?->tenCoSo ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-file-contract"></i></div>
                        <div>
                            <div class="info-row__label">Loại hợp đồng</div>
                            <div class="info-row__value {{ $loaiHopDongLabel ? '' : 'empty' }}">
                                {{ $loaiHopDongLabel ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-calendar-plus"></i></div>
                        <div>
                            <div class="info-row__label">Ngày vào làm</div>
                            <div class="info-row__value {{ $nhanSu?->ngayVaoLam ? '' : 'empty' }}">
                                {{ $nhanSu?->ngayVaoLam ? $nhanSu->ngayVaoLam->format('d/m/Y') : 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>

                    @if($nhanSu)
                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <div class="info-row__label">Trạng thái nhân sự</div>
                            <div class="info-row__value">
                                @if((int)$nhanSu->trangThai === 1)
                                    <span class="badge" style="background:#dcfce7;color:#166534;font-size:.78rem;padding:.3rem .65rem;border-radius:8px;">
                                        <i class="fas fa-circle-check me-1"></i>Đang làm việc
                                    </span>
                                @else
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.78rem;padding:.3rem .65rem;border-radius:8px;">
                                        <i class="fas fa-circle-xmark me-1"></i>Nghỉ việc
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── Thông tin tài khoản ── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--purple">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h2 class="info-card__title">Thông tin tài khoản</h2>
                </div>
                <div class="info-card__body">

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-at"></i></div>
                        <div>
                            <div class="info-row__label">Tên đăng nhập</div>
                            <div class="info-row__value">{{ $user->taiKhoan }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="info-row__label">Email</div>
                            <div class="info-row__value">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span style="font-size:.72rem;color:#16a34a;font-weight:500;margin-left:.4rem;">
                                        <i class="fas fa-circle-check"></i> Đã xác thực
                                    </span>
                                @else
                                    <span style="font-size:.72rem;color:#d97706;font-weight:500;margin-left:.4rem;">
                                        <i class="fas fa-circle-exclamation"></i> Chưa xác thực
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-user-tag"></i></div>
                        <div>
                            <div class="info-row__label">Vai trò</div>
                            <div class="info-row__value">{{ $user->getRoleLabel() }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-plug"></i></div>
                        <div>
                            <div class="info-row__label">Phương thức đăng nhập</div>
                            <div class="info-row__value">{{ $user->getAuthProviderLabel() }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <div>
                            <div class="info-row__label">Đăng nhập lần cuối</div>
                            <div class="info-row__value {{ $user->lastLogin ? '' : 'empty' }}">
                                {{ $user->lastLogin ? $user->lastLogin->format('H:i d/m/Y') : 'Chưa có dữ liệu' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-calendar-days"></i></div>
                        <div>
                            <div class="info-row__label">Ngày tạo tài khoản</div>
                            <div class="info-row__value">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row__icon"><i class="fas fa-circle-half-stroke"></i></div>
                        <div>
                            <div class="info-row__label">Trạng thái tài khoản</div>
                            <div class="info-row__value">
                                @if($isActive)
                                    <span style="display:inline-flex;align-items:center;gap:.3rem;color:#16a34a;font-weight:600;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:.3rem;color:#dc2626;font-weight:600;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                                        Bị khóa
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Hồ sơ quy định ── --}}
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card__header">
                    <div class="info-card__header-icon icon--orange">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h2 class="info-card__title">Hồ sơ nhân sự & Quy định</h2>
                </div>
                <div class="info-card__body">
                    @if($nhanSuHoSo)
                        @php
                            $isComplete = $nhanSuHoSo->isCompleted();
                            $blockClass = $isComplete ? 'complete' : 'draft';
                        @endphp
                        <div class="hoso-block {{ $blockClass }} mb-3">
                            <div class="hoso-block__icon">
                                <i class="fas fa-{{ $isComplete ? 'circle-check' : 'hourglass-half' }}"></i>
                            </div>
                            <div>
                                <div class="hoso-block__title">
                                    {{ $nhanSuHoSo->tieuDeMauSnapshot ?? 'Hồ sơ nhân sự' }}
                                </div>
                                <div class="hoso-block__sub">
                                    Mã hồ sơ: <strong>{{ $nhanSuHoSo->maHoSo }}</strong>
                                    &nbsp;·&nbsp;
                                    @if($isComplete)
                                        <span style="color:#16a34a;font-weight:600;">Hoàn tất</span>
                                    @else
                                        <span style="color:#d97706;font-weight:600;">Bản nháp</span>
                                    @endif
                                </div>
                                @if($nhanSuHoSo->ghiChuHoSo)
                                    <div class="hoso-block__sub mt-1">
                                        <i class="fas fa-comment-dots me-1"></i>
                                        {{ $nhanSuHoSo->ghiChuHoSo }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($nhanSuHoSo->noiDungQuyDinhSnapshot)
                            <div style="font-size:.8rem;color:#475569;background:#f8fafc;border-radius:10px;padding:.9rem 1rem;border:1px solid #e2e8f0;max-height:180px;overflow-y:auto;line-height:1.7;">
                                {!! nl2br(e($nhanSuHoSo->noiDungQuyDinhSnapshot)) !!}
                            </div>
                        @endif

                    @else
                        <div class="hoso-block none">
                            <div class="hoso-block__icon">
                                <i class="fas fa-folder-minus"></i>
                            </div>
                            <div>
                                <div class="hoso-block__title">Chưa có hồ sơ quy định</div>
                                <div class="hoso-block__sub">
                                    Bộ phận quản lý nhân sự sẽ gắn hồ sơ quy định sau khi hoàn thiện thủ tục.
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Phần thông tin thêm từ hoso nếu có --}}
                    @if($hoSo?->ghiChu || $hoSo?->nguonBietDen)
                    <hr style="border-color:#f0f4f9;margin:1rem 0;">
                    @endif

                    @if($hoSo?->nguonBietDen)
                        <div class="info-row">
                            <div class="info-row__icon"><i class="fas fa-route"></i></div>
                            <div>
                                <div class="info-row__label">Nguồn biết đến</div>
                                <div class="info-row__value">{{ $hoSo->nguonBietDen }}</div>
                            </div>
                        </div>
                    @endif

                    @if($hoSo?->trinhDoHienTai)
                        <div class="info-row">
                            <div class="info-row__icon"><i class="fas fa-layer-group"></i></div>
                            <div>
                                <div class="info-row__label">Trình độ hiện tại</div>
                                <div class="info-row__value">{{ $hoSo->trinhDoHienTai }}</div>
                            </div>
                        </div>
                    @endif

                    @if($hoSo?->ngonNguMucTieu)
                        <div class="info-row">
                            <div class="info-row__icon"><i class="fas fa-language"></i></div>
                            <div>
                                <div class="info-row__label">Ngôn ngữ mục tiêu</div>
                                <div class="info-row__value">{{ $hoSo->ngonNguMucTieu }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end row --}}
</div>
@endsection
