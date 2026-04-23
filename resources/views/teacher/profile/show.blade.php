@extends('layouts.internal')

@section('title', 'Hồ sơ giáo viên')
@section('page-title', 'Hồ sơ của tôi')
@section('breadcrumb', 'Cổng giáo viên · Hồ sơ cá nhân')

@section('stylesheet')
<style>
    /* ══════════════════════════════════════════════
       HERO
    ══════════════════════════════════════════════ */
    .pf-hero {
        background: linear-gradient(135deg, #0f2944 0%, #0d3b5e 50%, #0a4d73 100%);
        border-radius: 20px;
        padding: 1.75rem 2rem 1.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .pf-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 82% 18%, rgba(39,196,181,.18), transparent 42%),
            radial-gradient(circle at 12% 80%, rgba(56,130,246,.12), transparent 40%);
        pointer-events: none;
    }

    /* Avatar */
    .pf-avatar-wrap { position: relative; display: inline-block; flex-shrink: 0; }
    .pf-avatar {
        width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
        border: 3px solid rgba(255,255,255,.22);
        box-shadow: 0 6px 22px rgba(0,0,0,.32);
    }
    .pf-avatar-initial {
        width: 90px; height: 90px; border-radius: 50%;
        background: linear-gradient(135deg, #27c4b5, #3b82f6);
        border: 3px solid rgba(255,255,255,.22);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 700; color: #fff;
        box-shadow: 0 6px 22px rgba(0,0,0,.32);
    }
    .pf-online {
        position: absolute; bottom: 3px; right: 3px;
        width: 12px; height: 12px; border-radius: 50%;
        background: #22c55e; border: 2px solid #0d3b5e;
    }

    /* Name / role */
    .pf-name { font-size: 1.35rem; font-weight: 700; color: #fff; margin-bottom: .15rem; }
    .pf-role { font-size: .82rem; color: rgba(255,255,255,.58); margin-bottom: .6rem; }

    /* Badges */
    .pf-badges { display: flex; flex-wrap: wrap; gap: .35rem; }
    .pf-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem; border-radius: 999px;
        font-size: .7rem; font-weight: 600;
    }
    .pf-badge--teal   { background: rgba(39,196,181,.18);  color: #27c4b5; border: 1px solid rgba(39,196,181,.28); }
    .pf-badge--blue   { background: rgba(59,130,246,.18);   color: #60a5fa; border: 1px solid rgba(59,130,246,.28); }
    .pf-badge--green  { background: rgba(34,197,94,.18);    color: #4ade80; border: 1px solid rgba(34,197,94,.28); }
    .pf-badge--red    { background: rgba(239,68,68,.18);    color: #f87171; border: 1px solid rgba(239,68,68,.28); }
    .pf-badge--purple { background: rgba(168,85,247,.18);   color: #c084fc; border: 1px solid rgba(168,85,247,.28); }
    .pf-badge--orange { background: rgba(251,146,60,.18);   color: #fb923c; border: 1px solid rgba(251,146,60,.28); }

    /* Mini-stats in hero */
    .pf-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(125px, 1fr));
        gap: .55rem;
        margin-top: 1rem;
    }
    .pf-stat {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 10px;
        padding: .6rem .85rem;
    }
    .pf-stat__val   { font-size: .95rem; font-weight: 700; color: #fff; margin-bottom: .05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pf-stat__label { font-size: .65rem; color: rgba(255,255,255,.48); font-weight: 500; }

    /* ══════════════════════════════════════════════
       INFO CARD
    ══════════════════════════════════════════════ */
    .pf-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5eaf2;
        box-shadow: 0 2px 10px rgba(15,41,68,.05);
        overflow: hidden;
        height: 100%;
    }
    .pf-card__head {
        display: flex; align-items: center; gap: .55rem;
        padding: .85rem 1.2rem;
        border-bottom: 1px solid #f0f4f9;
        background: linear-gradient(90deg, #f8fafc 0%, #fff 100%);
    }
    .pf-card__ico {
        width: 29px; height: 29px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem; flex-shrink: 0;
    }
    .ico--teal   { background: #e6faf8; color: #0d9488; }
    .ico--blue   { background: #eff6ff; color: #2563eb; }
    .ico--purple { background: #f5f3ff; color: #7c3aed; }
    .ico--orange { background: #fff7ed; color: #c2410c; }
    .pf-card__title { font-size: .86rem; font-weight: 700; color: #1e293b; margin: 0; }
    .pf-card__body  { padding: 1rem 1.2rem; }

    /* ══════════════════════════════════════════════
       FIELD GRID  (3 cột)
    ══════════════════════════════════════════════ */
    .fg {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .5rem .85rem;
    }
    .fg--2 { grid-template-columns: repeat(2, 1fr); }

    .fg__item      { min-width: 0; }
    .fg__label {
        font-size: .665rem;
        font-weight: 600;
        color: #94a3b8;
        letter-spacing: .045em;
        text-transform: uppercase;
        margin-bottom: .15rem;
    }
    .fg__val {
        font-size: .825rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fg__val.na   { color: #cbd5e1; font-weight: 400; font-style: italic; }
    .fg__val.wrap { white-space: normal; }

    .fg__span2 { grid-column: span 2; }
    .fg__span3 { grid-column: span 3; }

    /* ══════════════════════════════════════════════
       STATUS PILL
    ══════════════════════════════════════════════ */
    .sp {
        display: inline-flex; align-items: center; gap: .28rem;
        font-size: .73rem; font-weight: 600;
        padding: .2rem .55rem; border-radius: 8px;
    }
    .sp--green  { background: #dcfce7; color: #166534; }
    .sp--red    { background: #fee2e2; color: #991b1b; }
    .sp--yellow { background: #fef3c7; color: #92400e; }
    .sdot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .sdot--green  { background: #22c55e; }
    .sdot--red    { background: #dc2626; }
    .sdot--yellow { background: #f59e0b; }

    /* ══════════════════════════════════════════════
       HỒ SƠ QUY ĐỊNH
    ══════════════════════════════════════════════ */
    .hs-block {
        border: 1.5px dashed #cbd5e1;
        border-radius: 10px;
        padding: .8rem .95rem;
        background: #f8fafc;
        display: flex; align-items: flex-start; gap: .8rem;
        margin-bottom: .7rem;
    }
    .hs-block.complete { border-color: #86efac; background: #f0fdf4; }
    .hs-block.draft    { border-color: #fcd34d; background: #fffbeb; }
    .hs-block__ico {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; flex-shrink: 0;
    }
    .hs-block.complete .hs-block__ico { background: #bbf7d0; color: #16a34a; }
    .hs-block.draft    .hs-block__ico { background: #fef3c7; color: #d97706; }
    .hs-block.none     .hs-block__ico { background: #f1f5f9; color: #94a3b8; }
    .hs-block__title { font-size: .82rem; font-weight: 700; color: #1e293b; margin-bottom: .08rem; }
    .hs-block__sub   { font-size: .74rem; color: #64748b; }
    .hs-content {
        font-size: .76rem; color: #475569; line-height: 1.65;
        background: #f8fafc; border-radius: 8px;
        padding: .7rem .85rem;
        border: 1px solid #e2e8f0;
        max-height: 150px; overflow-y: auto;
    }

    /* ══════════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════════ */
    @media (max-width: 767px) {
        .pf-hero   { padding: 1.2rem; }
        .pf-stats  { grid-template-columns: 1fr 1fr; }
        .fg        { grid-template-columns: repeat(2, 1fr); }
        .fg__span3 { grid-column: span 2; }
    }
</style>
@endsection

@section('content')
@php
    $hoSo       = $user->hoSoNguoiDung;
    $nhanSu     = $user->nhanSu;
    $nhanSuHoSo = $user->nhanSuHoSo;

    $hoTen    = $hoSo?->hoTen ?? $user->taiKhoan;
    $initial  = mb_strtoupper(mb_substr($hoTen, 0, 1, 'UTF-8'));
    $isActive = (int) $user->trangThai === 1;

    $gioiTinhLbl = match((int)($hoSo?->gioiTinh ?? -1)) {
        0 => 'Nam', 1 => 'Nữ', 2 => 'Khác', default => null,
    };

    $hdLbl = match($nhanSu?->loaiHopDong ?? '') {
        'chinh_thuc'        => 'Chính thức',
        'thu_viec'          => 'Thử việc',
        'cong_tac_vien'     => 'Cộng tác viên',
        'hop_dong_ngan_han' => 'HĐ ngắn hạn',
        default             => $nhanSu?->loaiHopDong,
    };

    $avatarUrl = $user->getAvatarUrl();
    $hasAvatar = $avatarUrl && !str_ends_with($avatarUrl, 'user-default.png');
@endphp

<div class="container-fluid px-0">

    {{-- ══ HERO ══ --}}
    <div class="pf-hero">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3" style="position:relative;z-index:1;">
            {{-- Avatar --}}
            <div class="pf-avatar-wrap">
                @if($hasAvatar)
                    <img src="{{ $avatarUrl }}" alt="{{ $hoTen }}" class="pf-avatar">
                @else
                    <div class="pf-avatar-initial">{{ $initial }}</div>
                @endif
                @if($isActive) <span class="pf-online"></span> @endif
            </div>

            {{-- Name + badges --}}
            <div class="flex-grow-1">
                <h1 class="pf-name">{{ $hoTen }}</h1>
                <div class="pf-role">
                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    {{ $nhanSu?->chucVu ?? 'Giáo viên' }}
                    @if($nhanSu?->coSoDaoTao) &nbsp;·&nbsp; {{ $nhanSu->coSoDaoTao->tenCoSo }} @endif
                </div>
                <div class="pf-badges">
                    <span class="pf-badge pf-badge--teal"><i class="fas fa-id-badge"></i> {{ $user->taiKhoan }}</span>
                    @if($nhanSu?->maNhanVien)
                        <span class="pf-badge pf-badge--blue"><i class="fas fa-fingerprint"></i> {{ $nhanSu->maNhanVien }}</span>
                    @endif
                    @if($isActive)
                        <span class="pf-badge pf-badge--green"><i class="fas fa-circle-check"></i> Đang hoạt động</span>
                    @else
                        <span class="pf-badge pf-badge--red"><i class="fas fa-circle-xmark"></i> Bị khóa</span>
                    @endif
                    @if($nhanSu?->bangCap)
                        <span class="pf-badge pf-badge--purple"><i class="fas fa-graduation-cap"></i> {{ $nhanSu->bangCap }}</span>
                    @endif
                    @if($hdLbl)
                        <span class="pf-badge pf-badge--orange"><i class="fas fa-file-contract"></i> {{ $hdLbl }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mini stats --}}
        <div class="pf-stats" style="position:relative;z-index:1;">
            <div class="pf-stat">
                <div class="pf-stat__val">{{ $nhanSu?->chuyenMon ?? '—' }}</div>
                <div class="pf-stat__label">Chuyên môn</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat__val">{{ $nhanSu?->hocVi ?? '—' }}</div>
                <div class="pf-stat__label">Học vị</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat__val">{{ $nhanSu?->ngayVaoLam?->format('d/m/Y') ?? '—' }}</div>
                <div class="pf-stat__label">Ngày vào làm</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat__val">{{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}</div>
                <div class="pf-stat__label">Email</div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ══ THÔNG TIN CÁ NHÂN ══ --}}
        <div class="col-12">
            <div class="pf-card">
                <div class="pf-card__head">
                    <div class="pf-card__ico ico--teal"><i class="fas fa-user"></i></div>
                    <h2 class="pf-card__title">Thông tin cá nhân</h2>
                </div>
                <div class="pf-card__body">
                    <div class="fg">

                        <div class="fg__item">
                            <div class="fg__label">Họ và tên</div>
                            <div class="fg__val {{ $hoSo?->hoTen ? '' : 'na' }}">{{ $hoSo?->hoTen ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Giới tính</div>
                            <div class="fg__val {{ $gioiTinhLbl ? '' : 'na' }}">{{ $gioiTinhLbl ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Ngày sinh</div>
                            <div class="fg__val {{ $hoSo?->ngaySinh ? '' : 'na' }}">
                                {{ $hoSo?->ngaySinh ? \Carbon\Carbon::parse($hoSo->ngaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">CCCD / CMND</div>
                            <div class="fg__val {{ $hoSo?->cccd ? '' : 'na' }}">{{ $hoSo?->cccd ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Số điện thoại</div>
                            <div class="fg__val {{ $hoSo?->soDienThoai ? '' : 'na' }}">{{ $hoSo?->soDienThoai ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Zalo</div>
                            <div class="fg__val {{ $hoSo?->zalo ? '' : 'na' }}">{{ $hoSo?->zalo ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item fg__span3">
                            <div class="fg__label">Địa chỉ</div>
                            <div class="fg__val wrap {{ $hoSo?->diaChi ? '' : 'na' }}">{{ $hoSo?->diaChi ?? 'Chưa cập nhật' }}</div>
                        </div>

                        @if($hoSo?->ghiChu)
                        <div class="fg__item fg__span3">
                            <div class="fg__label">Ghi chú</div>
                            <div class="fg__val wrap">{{ $hoSo->ghiChu }}</div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- ══ THÔNG TIN NHÂN SỰ ══ --}}
        <div class="col-12">
            <div class="pf-card">
                <div class="pf-card__head">
                    <div class="pf-card__ico ico--blue"><i class="fas fa-briefcase"></i></div>
                    <h2 class="pf-card__title">Thông tin nhân sự</h2>
                </div>
                <div class="pf-card__body">
                    <div class="fg">

                        <div class="fg__item">
                            <div class="fg__label">Mã nhân viên</div>
                            <div class="fg__val {{ $nhanSu?->maNhanVien ? '' : 'na' }}">{{ $nhanSu?->maNhanVien ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Chức vụ</div>
                            <div class="fg__val {{ $nhanSu?->chucVu ? '' : 'na' }}">{{ $nhanSu?->chucVu ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Cơ sở đào tạo</div>
                            <div class="fg__val {{ $nhanSu?->coSoDaoTao ? '' : 'na' }}">{{ $nhanSu?->coSoDaoTao?->tenCoSo ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Học vị</div>
                            <div class="fg__val {{ $nhanSu?->hocVi ? '' : 'na' }}">{{ $nhanSu?->hocVi ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Bằng cấp</div>
                            <div class="fg__val {{ $nhanSu?->bangCap ? '' : 'na' }}">{{ $nhanSu?->bangCap ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Chuyên môn</div>
                            <div class="fg__val {{ $nhanSu?->chuyenMon ? '' : 'na' }}">{{ $nhanSu?->chuyenMon ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Loại hợp đồng</div>
                            <div class="fg__val {{ $hdLbl ? '' : 'na' }}">{{ $hdLbl ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Ngày vào làm</div>
                            <div class="fg__val {{ $nhanSu?->ngayVaoLam ? '' : 'na' }}">{{ $nhanSu?->ngayVaoLam?->format('d/m/Y') ?? 'Chưa cập nhật' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Trạng thái nhân sự</div>
                            <div class="fg__val" style="overflow:visible;white-space:normal;">
                                @if($nhanSu)
                                    @if((int)$nhanSu->trangThai === 1)
                                        <span class="sp sp--green"><span class="sdot sdot--green"></span>Đang làm việc</span>
                                    @else
                                        <span class="sp sp--red"><span class="sdot sdot--red"></span>Nghỉ việc</span>
                                    @endif
                                @else
                                    <span class="na">Chưa có dữ liệu</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ══ THÔNG TIN TÀI KHOẢN ══ --}}
        <div class="col-lg-6">
            <div class="pf-card">
                <div class="pf-card__head">
                    <div class="pf-card__ico ico--purple"><i class="fas fa-shield-halved"></i></div>
                    <h2 class="pf-card__title">Thông tin tài khoản</h2>
                </div>
                <div class="pf-card__body">
                    <div class="fg">

                        <div class="fg__item">
                            <div class="fg__label">Tên đăng nhập</div>
                            <div class="fg__val">{{ $user->taiKhoan }}</div>
                        </div>

                        <div class="fg__item fg__span2">
                            <div class="fg__label">Email</div>
                            <div class="fg__val" style="overflow:visible;white-space:normal;">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span style="font-size:.67rem;color:#16a34a;font-weight:600;margin-left:.3rem;">
                                        <i class="fas fa-circle-check"></i> Đã xác thực
                                    </span>
                                @else
                                    <span style="font-size:.67rem;color:#d97706;font-weight:600;margin-left:.3rem;">
                                        <i class="fas fa-circle-exclamation"></i> Chưa xác thực
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Vai trò</div>
                            <div class="fg__val">{{ $user->getRoleLabel() }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Phương thức đăng nhập</div>
                            <div class="fg__val">{{ $user->getAuthProviderLabel() }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Trạng thái tài khoản</div>
                            <div class="fg__val" style="overflow:visible;white-space:normal;">
                                @if($isActive)
                                    <span class="sp sp--green"><span class="sdot sdot--green"></span>Đang hoạt động</span>
                                @else
                                    <span class="sp sp--red"><span class="sdot sdot--red"></span>Bị khóa</span>
                                @endif
                            </div>
                        </div>

                        <div class="fg__item fg__span2">
                            <div class="fg__label">Đăng nhập lần cuối</div>
                            <div class="fg__val {{ $user->lastLogin ? '' : 'na' }}">{{ $user->lastLogin?->format('H:i · d/m/Y') ?? 'Chưa có dữ liệu' }}</div>
                        </div>

                        <div class="fg__item">
                            <div class="fg__label">Ngày tạo tài khoản</div>
                            <div class="fg__val">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ══ HỒ SƠ NHÂN SỰ & QUY ĐỊNH ══ --}}
        <div class="col-lg-6">
            <div class="pf-card">
                <div class="pf-card__head">
                    <div class="pf-card__ico ico--orange"><i class="fas fa-folder-open"></i></div>
                    <h2 class="pf-card__title">Hồ sơ nhân sự & Quy định</h2>
                </div>
                <div class="pf-card__body">
                    @if($nhanSuHoSo)
                        @php $isComplete = $nhanSuHoSo->isCompleted(); @endphp
                        <div class="hs-block {{ $isComplete ? 'complete' : 'draft' }}">
                            <div class="hs-block__ico">
                                <i class="fas fa-{{ $isComplete ? 'circle-check' : 'hourglass-half' }}"></i>
                            </div>
                            <div>
                                <div class="hs-block__title">{{ $nhanSuHoSo->tieuDeMauSnapshot ?? 'Hồ sơ nhân sự' }}</div>
                                <div class="hs-block__sub">
                                    Mã: <strong>{{ $nhanSuHoSo->maHoSo }}</strong>
                                    &nbsp;·&nbsp;
                                    @if($isComplete)
                                        <span style="color:#16a34a;font-weight:600;">Hoàn tất</span>
                                    @else
                                        <span style="color:#d97706;font-weight:600;">Bản nháp</span>
                                    @endif
                                </div>
                                @if($nhanSuHoSo->ghiChuHoSo)
                                    <div class="hs-block__sub mt-1">
                                        <i class="fas fa-comment-dots me-1"></i>{{ $nhanSuHoSo->ghiChuHoSo }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($nhanSuHoSo->noiDungQuyDinhSnapshot)
                            <div class="hs-content">{!! nl2br(e($nhanSuHoSo->noiDungQuyDinhSnapshot)) !!}</div>
                        @endif
                    @else
                        <div class="hs-block none">
                            <div class="hs-block__ico"><i class="fas fa-folder-minus"></i></div>
                            <div>
                                <div class="hs-block__title">Chưa có hồ sơ quy định</div>
                                <div class="hs-block__sub">Bộ phận nhân sự sẽ gắn hồ sơ sau khi hoàn thiện thủ tục.</div>
                            </div>
                        </div>
                    @endif

                    @if($hoSo?->trinhDoHienTai || $hoSo?->ngonNguMucTieu || $hoSo?->nguonBietDen)
                    <hr style="border-color:#f1f5f9;margin:.75rem 0;">
                    <div class="fg">
                        @if($hoSo?->trinhDoHienTai)
                        <div class="fg__item">
                            <div class="fg__label">Trình độ hiện tại</div>
                            <div class="fg__val">{{ $hoSo->trinhDoHienTai }}</div>
                        </div>
                        @endif
                        @if($hoSo?->ngonNguMucTieu)
                        <div class="fg__item">
                            <div class="fg__label">Ngôn ngữ mục tiêu</div>
                            <div class="fg__val">{{ $hoSo->ngonNguMucTieu }}</div>
                        </div>
                        @endif
                        @if($hoSo?->nguonBietDen)
                        <div class="fg__item">
                            <div class="fg__label">Nguồn biết đến</div>
                            <div class="fg__val">{{ $hoSo->nguonBietDen }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection
