@extends('layouts.admin')

@section('title', 'Cập nhật giáo viên')
@section('page-title', 'Cập nhật giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Cập nhật giáo viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/giao-vien/create.css') }}">
@endsection

@section('content')

    <form action="{{ route('admin.giao-vien.update', $giaoVien->taiKhoan) }}" method="POST" id="gv-create-form" autocomplete="off">
        @csrf
        @method('PUT')

        {{-- ── Top Header ─────────────────────────────────────────────── --}}
        <div class="gv-header">
            <div class="gv-header-left">
                <div class="gv-avatar-preview">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h1 class="gv-header-title">Cập nhật giáo viên</h1>
                    <p class="gv-header-sub">Chỉnh sửa thông tin hồ sơ giáo viên</p>
                </div>
            </div>
            <div class="gv-header-actions">
                <a href="{{ route('admin.giao-vien.index') }}" class="gv-btn gv-btn-ghost">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="gv-btn gv-btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </div>
        </div>

        {{-- ── Progress bar ─────────────────────────────────────────────── --}}
        <div class="gv-progress-bar">
            <div class="gv-progress-fill" id="progressFill" style="width:25%"></div>
        </div>

        {{-- ── Tabs navigation ──────────────────────────────────────────── --}}
        <div class="gv-tabs-wrap">
            <div class="gv-tabs">
                <button type="button" class="gv-tab active" data-tab="tab-account">
                    <span class="gv-tab-icon"><i class="fas fa-key"></i></span>
                    <span class="gv-tab-label">
                        <span class="gv-tab-num">01</span>
                        Tài khoản
                    </span>
                    <span class="gv-tab-check" id="check-tab-account"><i class="fas fa-check-circle"></i></span>
                </button>
                <button type="button" class="gv-tab" data-tab="tab-personal">
                    <span class="gv-tab-icon"><i class="fas fa-id-card"></i></span>
                    <span class="gv-tab-label">
                        <span class="gv-tab-num">02</span>
                        Cá nhân
                    </span>
                    <span class="gv-tab-check" id="check-tab-personal"><i class="fas fa-check-circle"></i></span>
                </button>
                <button type="button" class="gv-tab" data-tab="tab-staff">
                    <span class="gv-tab-icon"><i class="fas fa-briefcase"></i></span>
                    <span class="gv-tab-label">
                        <span class="gv-tab-num">03</span>
                        Nhân sự
                    </span>
                    <span class="gv-tab-check" id="check-tab-staff"><i class="fas fa-check-circle"></i></span>
                </button>
                <button type="button" class="gv-tab" data-tab="tab-note">
                    <span class="gv-tab-icon"><i class="fas fa-note-sticky"></i></span>
                    <span class="gv-tab-label">
                        <span class="gv-tab-num">04</span>
                        Ghi chú
                    </span>
                    <span class="gv-tab-check" id="check-tab-note"><i class="fas fa-check-circle"></i></span>
                </button>
            </div>
        </div>

        {{-- ── Validation errors ────────────────────────────────────────── --}}
        @if ($errors->any())
            <div class="gv-alert-error">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <strong>Vui lòng kiểm tra các lỗi sau:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @php
            $profile = $giaoVien->hoSoNguoiDung;
            $nhanSu = $giaoVien->nhanSu;
        @endphp

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1 — Tài khoản                                             --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="gv-tab-panel active" id="tab-account">
            <div class="gv-card">
                <div class="gv-card-header teal">
                    <div class="gv-card-icon"><i class="fas fa-key"></i></div>
                    <div>
                        <div class="gv-card-title">Thông tin tài khoản</div>
                        <div class="gv-card-desc">Thông tin dùng để đăng nhập hệ thống</div>
                    </div>
                </div>
                <div class="gv-card-body">
                    <div class="form-grid">
                        {{-- Tỉnh đăng nhập (readonly) --}}
                        <div class="form-group full">
                            <label class="form-label">Tên đăng nhập (không thể thay đổi)</label>
                            <input type="text" class="form-control" value="{{ $giaoVien->taiKhoan }}" disabled>
                        </div>
                        
                        {{-- Email --}}
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $giaoVien->email) }}"
                                placeholder="nguyenvana@gmail.com">
                            @error('email')
                                <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Trạng thái --}}
                        <div class="form-group">
                            <label class="form-label" for="trangThai">
                                <i class="fas fa-toggle-on"></i> Trạng thái hoạt động
                            </label>
                            <select id="trangThai" name="trangThai" class="form-control">
                                <option value="1" {{ old('trangThai', $giaoVien->trangThai) == 1 ? 'selected' : '' }}>Đang hoạt động</option>
                                <option value="0" {{ old('trangThai', $giaoVien->trangThai) == 0 ? 'selected' : '' }}>Bị khóa</option>
                            </select>
                        </div>

                        {{-- Password (optional) --}}
                        <div class="form-group full">
                            <div class="gv-pass-notice" style="background:#fff3cd; border-color:#ffeeba;">
                                <div class="gv-pass-notice-icon" style="color:#856404;"><i class="fas fa-lock-open"></i></div>
                                <div class="gv-pass-notice-body" style="color:#856404;">
                                    <strong>Đổi mật khẩu mới (Tùy chọn)</strong>
                                    <p>Nếu không muốn đổi mật khẩu, vui lòng để trống 2 ô dưới đây.</p>
                                    
                                    <div class="form-grid mt-3">
                                        <div class="form-group">
                                            <label class="form-label" for="matKhau">Mật khẩu mới</label>
                                            <input type="password" id="matKhau" name="matKhau" class="form-control @error('matKhau') is-invalid @enderror" placeholder="Nhập để đổi mật khẩu...">
                                            @error('matKhau')
                                                <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="matKhau_confirmation">Xác nhận mật khẩu</label>
                                            <input type="password" id="matKhau_confirmation" name="matKhau_confirmation" class="form-control" placeholder="Nhập lại mật khẩu mới...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gv-tab-footer">
                <span></span>
                <button type="button" class="gv-btn gv-btn-next" onclick="switchTab('tab-personal')">
                    Tiếp theo <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2 — Thông tin cá nhân                                     --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="gv-tab-panel" id="tab-personal">
            <div class="gv-card">
                <div class="gv-card-header blue">
                    <div class="gv-card-icon"><i class="fas fa-id-card"></i></div>
                    <div>
                        <div class="gv-card-title">Thông tin cá nhân</div>
                        <div class="gv-card-desc">Họ tên, ngày sinh, địa chỉ và liên hệ</div>
                    </div>
                </div>
                <div class="gv-card-body">
                    <div class="form-grid">
                        {{-- Họ tên --}}
                        <div class="form-group full">
                            <label class="form-label" for="hoTen">
                                <i class="fas fa-user"></i> Họ và tên <span class="required">*</span>
                            </label>
                            <input type="text" id="hoTen" name="hoTen"
                                class="form-control @error('hoTen') is-invalid @enderror" value="{{ old('hoTen', $profile?->hoTen) }}"
                                placeholder="vd: Nguyễn Văn A">
                            @error('hoTen')
                                <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        {{-- CCCD --}}
                        <div class="form-group full">
                            <label class="form-label" for="cccd">
                                <i class="fas fa-id-badge"></i> CCCD / CMND
                            </label>
                            <input type="text" id="cccd" name="cccd"
                                class="form-control @error('cccd') is-invalid @enderror" value="{{ old('cccd', $profile?->cccd) }}"
                                placeholder="12 hoặc 9 số" maxlength="12">
                            @error('cccd')
                                <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ngày sinh --}}
                        <div class="form-group">
                            <label class="form-label" for="ngaySinh">
                                <i class="fas fa-cake-candles"></i> Ngày sinh
                            </label>
                            <input type="date" id="ngaySinh" name="ngaySinh"
                                class="form-control @error('ngaySinh') is-invalid @enderror"
                                value="{{ old('ngaySinh', $profile?->ngaySinh ? date('Y-m-d', strtotime($profile->ngaySinh)) : '') }}" max="{{ date('Y-m-d') }}">
                        </div>

                        {{-- Giới tính --}}
                        <div class="form-group">
                            <label class="form-label" for="gioiTinh">
                                <i class="fas fa-venus-mars"></i> Giới tính
                            </label>
                            <select id="gioiTinh" name="gioiTinh" class="form-control">
                                <option value="">— Chọn giới tính —</option>
                                <option value="1" {{ old('gioiTinh', $profile?->gioiTinh) == '1' ? 'selected' : '' }}>Nam</option>
                                <option value="0" {{ old('gioiTinh', $profile?->gioiTinh) == '0' ? 'selected' : '' }}>Nữ</option>
                                <option value="2" {{ old('gioiTinh', $profile?->gioiTinh) == '2' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="form-group">
                            <label class="form-label" for="soDienThoai">
                                <i class="fas fa-phone"></i> Số điện thoại
                            </label>
                            <input type="tel" id="soDienThoai" name="soDienThoai"
                                class="form-control @error('soDienThoai') is-invalid @enderror"
                                value="{{ old('soDienThoai', $profile?->soDienThoai) }}" placeholder="vd: 0901234567">
                        </div>

                        {{-- Zalo --}}
                        <div class="form-group">
                            <label class="form-label" for="zalo">
                                <i class="fas fa-comment-dots"></i> Zalo
                            </label>
                            <input type="tel" id="zalo" name="zalo" class="form-control"
                                value="{{ old('zalo', $profile?->zalo) }}" placeholder="SĐT Zalo (nếu khác)">
                            <div class="form-hint"><i class="fas fa-info-circle"></i> Để trống nếu giống SĐT chính</div>
                        </div>

                        {{-- Địa chỉ --}}
                        <div class="form-group full">
                            <label class="form-label" for="diaChi">
                                <i class="fas fa-map-pin"></i> Địa chỉ
                            </label>
                            <input type="text" id="diaChi" name="diaChi" class="form-control"
                                value="{{ old('diaChi', $profile?->diaChi) }}"
                                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố">
                        </div>
                    </div>
                </div>
            </div>

            <div class="gv-tab-footer">
                <button type="button" class="gv-btn gv-btn-ghost" onclick="switchTab('tab-account')">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </button>
                <button type="button" class="gv-btn gv-btn-next" onclick="switchTab('tab-staff')">
                    Tiếp theo <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3 — Nhân sự & Cơ sở                                      --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="gv-tab-panel" id="tab-staff">
            <div class="gv-card">
                <div class="gv-card-header orange">
                    <div class="gv-card-icon"><i class="fas fa-briefcase"></i></div>
                    <div>
                        <div class="gv-card-title">Thông tin nhân sự</div>
                        <div class="gv-card-desc">Chức vụ, chuyên môn, bằng cấp</div>
                    </div>
                </div>
                <div class="gv-card-body">
                    <div class="form-grid">
                        {{-- Chức vụ --}}
                        <div class="form-group">
                            <label class="form-label" for="chucVu">
                                <i class="fas fa-star"></i> Chức vụ
                            </label>
                            <select id="chucVu" name="chucVu" class="form-control">
                                <option value="">— Chọn chức vụ —</option>
                                @foreach (['Giáo viên', 'Giáo viên chính', 'Trưởng bộ môn', 'Phó trưởng bộ môn'] as $cv)
                                    <option value="{{ $cv }}" {{ old('chucVu', $nhanSu?->chucVu) === $cv ? 'selected' : '' }}>
                                        {{ $cv }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Chuyên môn --}}
                        <div class="form-group">
                            <label class="form-label" for="chuyenMon">
                                <i class="fas fa-language"></i> Chuyên môn
                            </label>
                            <select id="chuyenMon" name="chuyenMon" class="form-control">
                                <option value="">— Chọn chuyên môn —</option>
                                @foreach (['Tiếng Anh', 'Tiếng Nhật', 'Tiếng Hàn', 'Tiếng Trung', 'Tiếng Pháp', 'Tiếng Đức', 'Khác'] as $cm)
                                    <option value="{{ $cm }}" {{ old('chuyenMon', $nhanSu?->chuyenMon) === $cm ? 'selected' : '' }}>
                                        {{ $cm }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bằng cấp --}}
                        <div class="form-group">
                            <label class="form-label" for="bangCap">
                                <i class="fas fa-graduation-cap"></i> Bằng cấp
                            </label>
                            <select id="bangCap" name="bangCap" class="form-control">
                                <option value="">— Chọn bằng cấp —</option>
                                @foreach (['Cử nhân', 'Thạc sĩ', 'Tiến sĩ', 'Phó Giáo sư', 'Giáo sư', 'Khác'] as $bc)
                                    <option value="{{ $bc }}" {{ old('bangCap', $nhanSu?->bangCap) === $bc ? 'selected' : '' }}>
                                        {{ $bc }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Học vị --}}
                        <div class="form-group">
                            <label class="form-label" for="hocVi">
                                <i class="fas fa-certificate"></i> Học vị / Chứng chỉ
                            </label>
                            <input type="text" id="hocVi" name="hocVi" class="form-control"
                                value="{{ old('hocVi', $nhanSu?->hocVi) }}" placeholder="vd: IELTS 8.0, JLPT N1...">
                        </div>

                        {{-- Loại hợp đồng --}}
                        <div class="form-group">
                            <label class="form-label" for="loaiHopDong">
                                <i class="fas fa-file-contract"></i> Loại hợp đồng
                            </label>
                            <select id="loaiHopDong" name="loaiHopDong" class="form-control">
                                <option value="">— Chọn loại hợp đồng —</option>
                                @foreach (['Toàn thời gian', 'Bán thời gian', 'Thỉnh giảng', 'Thử việc'] as $lhd)
                                    <option value="{{ $lhd }}"
                                        {{ old('loaiHopDong', $nhanSu?->loaiHopDong) === $lhd ? 'selected' : '' }}>{{ $lhd }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ngày vào làm --}}
                        <div class="form-group">
                            <label class="form-label" for="ngayVaoLam">
                                <i class="fas fa-calendar-check"></i> Ngày vào làm
                            </label>
                            <input type="date" id="ngayVaoLam" name="ngayVaoLam" class="form-control"
                                value="{{ old('ngayVaoLam', $nhanSu?->ngayVaoLam ? date('Y-m-d', strtotime($nhanSu->ngayVaoLam)) : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Cơ sở làm việc (Cascade) ─────────────────────────────── --}}
            <div class="gv-card gv-card-facility">
                <div class="gv-card-header indigo">
                    <div class="gv-card-icon"><i class="fas fa-building"></i></div>
                    <div>
                        <div class="gv-card-title">Cơ sở làm việc <span class="required">*</span></div>
                        <div class="gv-card-desc">Chọn tỉnh/thành → phường/xã → cơ sở</div>
                    </div>
                </div>
                <div class="gv-card-body">
                    {{-- Hidden real coSoId for form submission --}}
                    <input type="hidden" name="coSoId" id="coSoId" value="{{ old('coSoId', $nhanSu?->coSoId) }}">
                    @error('coSoId')
                        <div class="invalid-feedback mb-2" style="display:flex"><i class="fas fa-circle-xmark"></i>
                            {{ $message }}</div>
                    @enderror

                    <div class="gv-cascade">
                        {{-- Step 1: Tỉnh/Thành --}}
                        <div class="gv-cascade-step">
                            <div class="gv-cascade-step-num">1</div>
                            <div class="gv-cascade-step-body">
                                <label class="form-label" for="sel-tinh">
                                    <i class="fas fa-map"></i> Tỉnh / Thành phố
                                </label>
                                <select id="sel-tinh" class="form-control">
                                    <option value="">— Chọn tỉnh/thành —</option>
                                    @foreach ($tinhThanhs as $tt)
                                        <option value="{{ $tt->tinhThanhId }}"
                                            data-ma="{{ $tt->maAPI ?? $tt->tinhThanhId }}">
                                            {{ $tt->tenTinhThanh }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="gv-cascade-arrow"><i class="fas fa-chevron-right"></i></div>

                        {{-- Step 2: Phường/Xã --}}
                        <div class="gv-cascade-step" id="step-phuongxa">
                            <div class="gv-cascade-step-num locked" id="num-phuongxa">2</div>
                            <div class="gv-cascade-step-body">
                                <label class="form-label" for="sel-phuongxa">
                                    <i class="fas fa-map-location-dot"></i> Phường / Xã
                                </label>
                                <div class="gv-select-wrap">
                                    <select id="sel-phuongxa" class="form-control" disabled>
                                        <option value="">— Chọn tỉnh trước —</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="gv-cascade-arrow"><i class="fas fa-chevron-right"></i></div>

                        {{-- Step 3: Cơ sở --}}
                        <div class="gv-cascade-step" id="step-coso">
                            <div class="gv-cascade-step-num locked" id="num-coso">3</div>
                            <div class="gv-cascade-step-body">
                                <label class="form-label" for="sel-coso">
                                    <i class="fas fa-school"></i> Cơ sở đào tạo
                                </label>
                                <div class="gv-select-wrap">
                                    <select id="sel-coso" class="form-control" disabled>
                                        <option value="">— Chọn phường/xã trước —</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Selected facility preview --}}
                    <div class="gv-facility-preview" id="facility-preview" style="display:none">
                        <div class="gv-facility-preview-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="gv-facility-preview-info">
                            <div class="gv-facility-preview-name" id="preview-name">—</div>
                            <div class="gv-facility-preview-addr" id="preview-addr">—</div>
                        </div>
                        <button type="button" class="gv-facility-preview-clear" id="clear-facility" title="Xóa chọn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- All facilities fallback (shown when no province with maAPI) --}}
                    <div class="gv-fallback-select" id="fallback-select" style="display:none">
                        <div class="form-hint mb-2"><i class="fas fa-info-circle"></i> Tỉnh này chưa có dữ liệu phường/xã
                            — chọn thẳng cơ sở:</div>
                        <select id="sel-coso-fallback" class="form-control">
                            <option value="">— Chọn cơ sở —</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="gv-tab-footer">
                <button type="button" class="gv-btn gv-btn-ghost" onclick="switchTab('tab-personal')">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </button>
                <button type="button" class="gv-btn gv-btn-next" onclick="switchTab('tab-note')">
                    Tiếp theo <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 4 — Ghi chú                                               --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="gv-tab-panel" id="tab-note">
            <div class="gv-card">
                <div class="gv-card-header purple">
                    <div class="gv-card-icon"><i class="fas fa-note-sticky"></i></div>
                    <div>
                        <div class="gv-card-title">Ghi chú nội bộ</div>
                        <div class="gv-card-desc">Thông tin thêm, chỉ admin và nhân viên thấy</div>
                    </div>
                </div>
                <div class="gv-card-body">
                    <div class="form-group">
                        <textarea id="ghiChu" name="ghiChu" class="form-control" rows="6"
                            placeholder="Nhập ghi chú thêm về giáo viên (chỉ admin và nhân viên xem được)...">{{ old('ghiChu', $profile?->ghiChu) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="gv-tab-footer">
                <button type="button" class="gv-btn gv-btn-ghost" onclick="switchTab('tab-staff')">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </button>
                <div class="gv-footer-right">
                    <button type="submit" class="gv-btn gv-btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </div>
        </div>

    </form>

@endsection

@section('script')
    <script>
        // ── Dữ liệu cơ sở từ server ──────────────────────────────────────────────────
        const ALL_COSO = @json($coSosData);
        
        const currentCoSoId = document.getElementById('coSoId').value;
        if(currentCoSoId) {
            const foundCs = ALL_COSO.find(c => c.coSoId == currentCoSoId);
            if(foundCs) {
                document.getElementById('sel-tinh').value = foundCs.tinhThanhId;
                // Kích hoạt event change
                document.getElementById('sel-tinh').dispatchEvent(new Event('change'));
                
                if(foundCs.maPhuongXa) {
                    setTimeout(() => {
                        document.getElementById('sel-phuongxa').value = foundCs.maPhuongXa;
                        document.getElementById('sel-phuongxa').dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            document.getElementById('sel-coso').value = foundCs.coSoId;
                            document.getElementById('sel-coso').dispatchEvent(new Event('change'));
                        }, 50);
                    }, 50);
                } else {
                    setTimeout(() => {
                        document.getElementById('sel-coso-fallback').value = foundCs.coSoId;
                        document.getElementById('sel-coso-fallback').dispatchEvent(new Event('change'));
                    }, 50);
                }
            }
        }


        const TABS = ['tab-account', 'tab-personal', 'tab-staff', 'tab-note'];
        const TAB_PROGRESS = {
            'tab-account': 25,
            'tab-personal': 50,
            'tab-staff': 75,
            'tab-note': 100
        };
        let currentTab = 'tab-account';

        // ── Tab switching ─────────────────────────────────────────────────────────────
        function switchTab(tabId) {
            document.querySelectorAll('.gv-tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.gv-tab').forEach(t => t.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.gv-tab[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById('progressFill').style.width = TAB_PROGRESS[tabId] + '%';
            currentTab = tabId;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        document.querySelectorAll('.gv-tab').forEach(btn => {
            btn.addEventListener('click', () => switchTab(btn.dataset.tab));
        });

        // ── Cascade: Tỉnh → Phường/Xã → Cơ sở ───────────────────────────────────────
        const selTinh = document.getElementById('sel-tinh');
        const selPhuongXa = document.getElementById('sel-phuongxa');
        const selCoSo = document.getElementById('sel-coso');
        const selCoSoFb = document.getElementById('sel-coso-fallback');
        const coSoHidden = document.getElementById('coSoId');
        const facilityPreview = document.getElementById('facility-preview');
        const fallbackWrap = document.getElementById('fallback-select');
        const numPhuongXa = document.getElementById('num-phuongxa');
        const numCoSo = document.getElementById('num-coso');

        function resetSelects(level) {
            if (level <= 2) {
                selPhuongXa.innerHTML = '<option value="">— Chọn tỉnh trước —</option>';
                selPhuongXa.disabled = true;
                numPhuongXa.classList.add('locked');
            }
            if (level <= 3) {
                selCoSo.innerHTML = '<option value="">— Chọn phường/xã trước —</option>';
                selCoSo.disabled = true;
                numCoSo.classList.add('locked');
                fallbackWrap.style.display = 'none';
            }
            setFacilityPreview(null);
            coSoHidden.value = '';
        }

        function setFacilityPreview(cs) {
            if (!cs) {
                facilityPreview.style.display = 'none';
                document.getElementById('preview-name').textContent = '—';
                document.getElementById('preview-addr').textContent = '—';
                coSoHidden.value = '';
                return;
            }
            document.getElementById('preview-name').textContent = cs.tenCoSo;
            document.getElementById('preview-addr').textContent = [cs.diaChi, cs.tenPhuongXa].filter(Boolean).join(', ');
            coSoHidden.value = cs.coSoId;
            facilityPreview.style.display = 'flex';
        }

        function populateCoSoSelect(sel, coSos) {
            sel.innerHTML = '';
            if (!coSos.length) {
                sel.innerHTML = '<option value="">— Không có cơ sở —</option>';
                return;
            }
            sel.innerHTML = '<option value="">— Chọn cơ sở —</option>';
            coSos.forEach(cs => {
                const opt = document.createElement('option');
                opt.value = cs.coSoId;
                opt.textContent = cs.tenCoSo;
                opt.dataset.diaChi = cs.diaChi || '';
                opt.dataset.phuongXa = cs.tenPhuongXa || '';
                sel.appendChild(opt);
            });
        }

        // Tỉnh change
        selTinh.addEventListener('change', function() {
            resetSelects(2);
            const tinhThanhId = this.value;
            if (!tinhThanhId) return;

            // Load phường/xã từ ALL_COSO thay vì gọi API
            const coSosByTinh = ALL_COSO.filter(c => c.tinhThanhId == tinhThanhId);

            if (coSosByTinh.length > 0) {
                // Lấy danh sách phường/xã duy nhất có cơ sở
                const wardsMap = new Map();
                coSosByTinh.forEach(c => {
                    if (c.maPhuongXa && c.tenPhuongXa) {
                        wardsMap.set(c.maPhuongXa, c.tenPhuongXa);
                    }
                });

                if (wardsMap.size > 0) {
                    selPhuongXa.innerHTML = '<option value="">— Chọn phường/xã —</option>';
                    wardsMap.forEach((name, code) => {
                        const opt = document.createElement('option');
                        opt.value = code;
                        opt.textContent = name;
                        selPhuongXa.appendChild(opt);
                    });
                    selPhuongXa.disabled = false;
                    numPhuongXa.classList.remove('locked');
                } else {
                    // Nếu không có phường/xã mà chỉ có cơ sở (trường hợp cũ/fallback)
                    fallbackWrap.style.display = 'block';
                    populateCoSoSelect(selCoSoFb, coSosByTinh);
                    selPhuongXa.innerHTML = '<option value="">— Không có dữ liệu phường/xã —</option>';
                }
            } else {
                // Không có cơ sở tại tỉnh này
                selPhuongXa.innerHTML = '<option value="">— Không có cơ sở —</option>';
                fallbackWrap.style.display = 'none';
            }
        });

        // Xã change
        selPhuongXa.addEventListener('change', function() {
            resetSelects(3);
            const maPhuongXa = this.value;
            const tinhThanhId = selTinh.value;
            if (!maPhuongXa || !tinhThanhId) return;

            // Lấy cơ sở theo phường/xã từ ALL_COSO
            const coSosByPhuongXa = ALL_COSO.filter(c => c.tinhThanhId == tinhThanhId && c.maPhuongXa ==
                maPhuongXa);

            if (coSosByPhuongXa.length > 0) {
                populateCoSoSelect(selCoSo, coSosByPhuongXa);
                selCoSo.disabled = false;
                numCoSo.classList.remove('locked');
            } else {
                selCoSo.innerHTML = '<option value="">— Không có cơ sở —</option>';
            }
        });

        // Cơ sở change (main cascade)
        selCoSo.addEventListener('change', function() {
            const coSoId = this.value;
            if (!coSoId) {
                setFacilityPreview(null);
                return;
            }
            // Find from ALL_COSO or construct from select option
            const found = ALL_COSO.find(c => c.coSoId == coSoId);
            if (found) {
                setFacilityPreview(found);
                return;
            }
            // Fallback: construct from option dataset
            const opt = this.selectedOptions[0];
            setFacilityPreview({
                coSoId,
                tenCoSo: opt.textContent,
                diaChi: opt.dataset.diaChi || '',
                tenPhuongXa: opt.dataset.phuongXa || ''
            });
        });

        // Cơ sở fallback (direct select when no ward API data)
        selCoSoFb.addEventListener('change', function() {
            const coSoId = this.value;
            if (!coSoId) {
                setFacilityPreview(null);
                return;
            }
            const found = ALL_COSO.find(c => c.coSoId == coSoId);
            if (found) setFacilityPreview(found);
        });

        // Clear selection
        document.getElementById('clear-facility').addEventListener('click', () => {
            selTinh.value = '';
            resetSelects(2);
            selCoSoFb.value = '';
        });
    </script>
@endsection
