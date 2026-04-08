@extends('layouts.admin')

@section('title', 'Chỉnh sửa học viên')
@section('page-title', 'Chỉnh sửa học viên')
@section('breadcrumb', 'Quản lý học viên · Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoc-vien/create.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoc-vien/edit.css') }}">
@endsection

@section('content')

    @php
        $profile = $hocVien->hoSoNguoiDung;
        $hoTen = $profile->hoTen ?? $hocVien->taiKhoan;
    @endphp

    <form action="{{ route('admin.hoc-vien.update', $hocVien->taiKhoan) }}" method="POST" id="hv-edit-form" class="needs-validation" novalidate data-joi-schema="hocVien"
        autocomplete="off" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="hv-create-header">
            <div class="hv-create-title">
                <i class="fas fa-user-pen"></i> Chỉnh sửa: {{ $hoTen }}
                <span class="badge-meta ms-2">
                    <i class="fas fa-hashtag"></i> ID {{ $hocVien->taiKhoanId }}
                </span>
            </div>
            <div class="hv-breadcrumb-actions">
                <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>

        {{-- ── Step indicator ───────────────────────────────────── --}}
        <div class="hv-steps-sticky">
            <div class="hv-steps">
                <a href="#sec-account" class="hv-step active"><span>1</span> Tài khoản</a>
                <a href="#sec-personal" class="hv-step"><span>2</span> Cá nhân</a>
                <a href="#sec-guardian" class="hv-step"><span>3</span> Người giám hộ</a>
                <a href="#sec-learning" class="hv-step"><span>4</span> Học tập</a>
                <a href="#sec-note" class="hv-step"><span>5</span> Ghi chú</a>
            </div>
        </div>

        {{-- ── Validation errors ───────────────────────────────── --}}
        @if ($errors->any())
            <div class="hv-form-section" style="border-color:#fca5a5;background:#fef9f9;margin-bottom:20px">
                <div class="hv-section-body">
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <i class="fas fa-exclamation-triangle" style="color:#dc2626;margin-top:2px"></i>
                        <div>
                            <p style="font-weight:600;color:#dc2626;font-size:0.875rem;margin-bottom:6px">
                                Vui lòng kiểm tra các lỗi sau:
                            </p>
                            <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:4px">
                                @foreach ($errors->all() as $error)
                                    <li style="font-size:0.8rem;color:#dc2626">
                                        <i class="fas fa-circle"
                                            style="font-size:.4em;margin-right:6px"></i>{{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── SECTION 1: Tài khoản ────────────────────────────── --}}
        <div class="hv-form-section" id="sec-account">
            <div class="hv-section-header">
                <div class="hv-section-icon teal"><i class="fas fa-key"></i></div>
                <div>
                    <div class="hv-section-title">Thông tin tài khoản</div>
                    <div class="hv-section-desc">Tên đăng nhập, email và trạng thái tài khoản</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-grid">

                    {{-- Tên đăng nhập (readonly) --}}
                    <div class="form-group">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" value="{{ $hocVien->taiKhoan }}" readonly
                            style="background:#f5f7fa;color:#8899a6;cursor:not-allowed">
                        <div class="form-hint"><i class="fas fa-lock"></i> Không thể thay đổi tên đăng nhập</div>
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email <span class="required">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $hocVien->email) }}" placeholder="vd: nguyenvana@gmail.com">
                        @error('email')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div class="form-group full">
                        <label class="form-label">Trạng thái tài khoản</label>
                        <div class="status-toggle">
                            <label class="opt opt-active">
                                <input type="radio" name="trangThai" value="1"
                                    {{ old('trangThai', $hocVien->trangThai) == 1 ? 'checked' : '' }}>
                                <i class="fas fa-circle-check"></i> Đang hoạt động
                            </label>
                            <label class="opt opt-lock">
                                <input type="radio" name="trangThai" value="0"
                                    {{ old('trangThai', $hocVien->trangThai) == 0 ? 'checked' : '' }}>
                                <i class="fas fa-ban"></i> Khoá tài khoản
                            </label>
                        </div>
                    </div>

                    {{-- Đổi mật khẩu (tuỳ chọn) --}}
                    <div class="form-group full">
                        <div class="pwd-toggle-wrap">
                            <label>
                                <input type="checkbox" id="changePwdToggle">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </label>
                            <span style="font-size:0.78rem;color:#aab8c2">Để trống nếu không muốn thay đổi</span>
                        </div>

                        <div id="pwd-fields" style="display:none">
                            <div class="form-grid" style="margin-top:10px">
                                <div class="form-group">
                                    <label class="form-label" for="matKhau">Mật khẩu mới</label>
                                    <input type="password" id="matKhau" name="matKhau"
                                        class="form-control @error('matKhau') is-invalid @enderror" minlength="8"
                                        placeholder="Tối thiểu 8 ký tự">
                                    @error('matKhau')
                                        <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="matKhau_confirmation">Xác nhận mật khẩu</label>
                                    <input type="password" id="matKhau_confirmation" name="matKhau_confirmation"
                                        class="form-control" minlength="8" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Thông tin cá nhân ───────────────────── --}}
        <div class="hv-form-section" id="sec-personal">
            <div class="hv-section-header">
                <div class="hv-section-icon blue"><i class="fas fa-id-card"></i></div>
                <div>
                    <div class="hv-section-title">Thông tin cá nhân</div>
                    <div class="hv-section-desc">Họ tên, ngày sinh, địa chỉ và liên hệ</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-grid">
                    {{-- Ảnh đại diện --}}
                    <div class="form-group full">
                        <label class="form-label" for="anhDaiDien">
                            Ảnh đại diện
                            <span class="form-hint-inline">Định dạng JPG, PNG, WEBP, tối đa 2MB.</span>
                        </label>
                        <x-upload.image
                            id="avatar-upload"
                            name="anhDaiDien"
                            title="Tải ảnh đại diện"
                            description="Kéo thả ảnh hoặc click để chọn"
                            chooseLabel="Chọn ảnh"
                            mode="deferred"
                            :standalone="false"
                            :previewUrl="$hocVien->getAvatarUrl()"
                            previewShape="circle"
                            accept="image/jpeg,image/png,image/webp"
                            :allowedTypes="['image/jpeg', 'image/png', 'image/webp']"
                            allowedExtensionsLabel="JPG, PNG, WebP"
                            maxSize="2097152"
                        />
                    </div>

                    {{-- Họ tên --}}
                    <div class="form-group full">
                        <label class="form-label" for="hoTen">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="hoTen" name="hoTen"
                            class="form-control @error('hoTen') is-invalid @enderror"
                            value="{{ old('hoTen', $profile->hoTen ?? '') }}" placeholder="vd: Nguyễn Văn A">
                        @error('hoTen')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ngày sinh --}}
                    <div class="form-group">
                        <label class="form-label" for="ngaySinh">Ngày sinh</label>
                        <input type="date" id="ngaySinh" name="ngaySinh"
                            class="form-control @error('ngaySinh') is-invalid @enderror"
                            value="{{ old('ngaySinh', $profile->ngaySinh ?? '') }}" max="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Giới tính --}}
                    <div class="form-group">
                        <label class="form-label" for="gioiTinh">Giới tính</label>
                        <select id="gioiTinh" name="gioiTinh" class="form-control">
                            <option value="">— Chọn giới tính —</option>
                            <option value="1"
                                {{ old('gioiTinh', $profile->gioiTinh ?? '') == '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0"
                                {{ old('gioiTinh', $profile->gioiTinh ?? '') === '0' ? 'selected' : '' }}>Nữ</option>
                            <option value="2"
                                {{ old('gioiTinh', $profile->gioiTinh ?? '') == '2' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>

                    {{-- Số điện thoại --}}
                    <div class="form-group">
                        <label class="form-label" for="soDienThoai">Số điện thoại</label>
                        <input type="tel" id="soDienThoai" name="soDienThoai" class="form-control"
                            value="{{ old('soDienThoai', $profile->soDienThoai ?? '') }}" placeholder="vd: 0901234567">
                    </div>

                    {{-- Zalo --}}
                    <div class="form-group">
                        <label class="form-label" for="zalo">Zalo</label>
                        <input type="tel" id="zalo" name="zalo" class="form-control"
                            value="{{ old('zalo', $profile->zalo ?? '') }}" placeholder="SĐT Zalo (nếu khác)">
                        <div class="form-hint"><i class="fas fa-info-circle"></i> Để trống nếu giống SĐT chính</div>
                    </div>

                    {{-- CCCD --}}
                    <div class="form-group">
                        <label class="form-label" for="cccd">CCCD / CMND</label>
                        <input type="text" id="cccd" name="cccd"
                            class="form-control @error('cccd') is-invalid @enderror"
                            value="{{ old('cccd', $profile->cccd ?? '') }}" placeholder="12 hoặc 9 số" maxlength="12">
                        @error('cccd')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Địa chỉ --}}
                    <div class="form-group full">
                        <label class="form-label" for="diaChi">Địa chỉ</label>
                        <input type="text" id="diaChi" name="diaChi" class="form-control"
                            value="{{ old('diaChi', $profile->diaChi ?? '') }}"
                            placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 3: Người giám hộ ───────────────────────── --}}
        <div class="hv-form-section" id="sec-guardian">
            <div class="hv-section-header">
                <div class="hv-section-icon orange"><i class="fas fa-people-roof"></i></div>
                <div>
                    <div class="hv-section-title">Người giám hộ / Phụ huynh</div>
                    <div class="hv-section-desc">Thông tin liên lạc khẩn cấp</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="nguoiGiamHo">Họ tên người giám hộ</label>
                        <input type="text" id="nguoiGiamHo" name="nguoiGiamHo" class="form-control"
                            value="{{ old('nguoiGiamHo', $profile->nguoiGiamHo ?? '') }}" placeholder="vd: Nguyễn Thị B">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="moiQuanHe">Mối quan hệ</label>
                        <select id="moiQuanHe" name="moiQuanHe" class="form-control">
                            <option value="">— Chọn quan hệ —</option>
                            @foreach (['Bố', 'Mẹ', 'Anh', 'Chị', 'Ông', 'Bà', 'Chú', 'Bác', 'Khác'] as $rel)
                                <option value="{{ $rel }}"
                                    {{ old('moiQuanHe', $profile->moiQuanHe ?? '') === $rel ? 'selected' : '' }}>
                                    {{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="sdtGuardian">SĐT người giám hộ</label>
                        <input type="tel" id="sdtGuardian" name="sdtGuardian" class="form-control"
                            value="{{ old('sdtGuardian', $profile->sdtGuardian ?? '') }}" placeholder="vd: 0912345678">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 4: Thông tin học tập ───────────────────── --}}
        <div class="hv-form-section" id="sec-learning">
            <div class="hv-section-header">
                <div class="hv-section-icon green"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <div class="hv-section-title">Thông tin học tập</div>
                    <div class="hv-section-desc">Ngôn ngữ mục tiêu và trình độ hiện tại</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ngonNguMucTieu">Ngôn ngữ muốn học</label>
                        <select id="ngonNguMucTieu" name="ngonNguMucTieu" class="form-control">
                            <option value="">— Chọn ngôn ngữ —</option>
                            @foreach (['Tiếng Anh', 'Tiếng Nhật', 'Tiếng Hàn', 'Tiếng Trung', 'Tiếng Pháp', 'Tiếng Đức', 'Khác'] as $lang)
                                <option value="{{ $lang }}"
                                    {{ old('ngonNguMucTieu', $profile->ngonNguMucTieu ?? '') === $lang ? 'selected' : '' }}>
                                    {{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trinhDoHienTai">Trình độ hiện tại</label>
                        <select id="trinhDoHienTai" name="trinhDoHienTai" class="form-control">
                            <option value="">— Chọn trình độ —</option>
                            @foreach (['Beginner (Mới bắt đầu)', 'Elementary (Sơ cấp)', 'Pre-Intermediate', 'Intermediate (Trung cấp)', 'Upper-Intermediate', 'Advanced (Nâng cao)'] as $lvl)
                                <option value="{{ $lvl }}"
                                    {{ old('trinhDoHienTai', $profile->trinhDoHienTai ?? '') === $lvl ? 'selected' : '' }}>
                                    {{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="nguonBietDen">Biết đến trung tâm qua</label>
                        <select id="nguonBietDen" name="nguonBietDen" class="form-control">
                            <option value="">— Chọn nguồn —</option>
                            @foreach (['Facebook', 'Zalo', 'Google / Website', 'Bạn bè giới thiệu', 'Panô / Tờ rơi', 'Khác'] as $src)
                                <option value="{{ $src }}"
                                    {{ old('nguonBietDen', $profile->nguonBietDen ?? '') === $src ? 'selected' : '' }}>
                                    {{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 5: Ghi chú ─────────────────────────────── --}}
        <div class="hv-form-section" id="sec-note">
            <div class="hv-section-header">
                <div class="hv-section-icon purple"><i class="fas fa-sticky-note"></i></div>
                <div>
                    <div class="hv-section-title">Ghi chú</div>
                    <div class="hv-section-desc">Thông tin thêm về học viên</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-group">
                    <label class="form-label" for="ghiChu">Ghi chú nội bộ</label>
                    <textarea id="ghiChu" name="ghiChu" class="form-control" rows="4"
                        placeholder="Nhập ghi chú thêm về học viên (chỉ admin và nhân viên xem được)...">{{ old('ghiChu', $profile->ghiChu ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────── --}}
        <div class="hv-form-footer">
            <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back">
                <i class="fas fa-times"></i> Hủy bỏ
            </a>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>

    </form>

@endsection

@section('script')
    <script>
        // ── Toggle password fields ─────────────────────────────────────
        const toggle = document.getElementById('changePwdToggle');
        const pwdFields = document.getElementById('pwd-fields');
        const matKhauInput = document.getElementById('matKhau');
        const matKhauConfirm = document.getElementById('matKhau_confirmation');

        @if ($errors->has('matKhau'))
            // Nếu có lỗi mật khẩu, mở sẵn
            toggle.checked = true;
            pwdFields.style.display = 'block';
        @endif

        toggle?.addEventListener('change', function() {
            pwdFields.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                matKhauInput.value = '';
                matKhauConfirm.value = '';
            }
        });

        // ── Smooth scroll + active step ───────────────────────────────
        document.querySelectorAll('.hv-step').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.hv-step').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const top = target.getBoundingClientRect().top + window.scrollY - 70;
                    window.scrollTo({
                        top,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // ── Highlight step on scroll ──────────────────────────────────
        const sections = ['sec-account', 'sec-personal', 'sec-guardian', 'sec-learning', 'sec-note'];
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = '#' + entry.target.id;
                    document.querySelectorAll('.hv-step').forEach(s => {
                        s.classList.toggle('active', s.getAttribute('href') === id);
                    });
                }
            });
        }, {
            rootMargin: '-30% 0px -60% 0px'
        });

        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        });
    </script>
@endsection
