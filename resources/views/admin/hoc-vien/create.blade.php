@extends('layouts.admin')

@section('title', 'Thêm học viên')
@section('page-title', 'Thêm học viên mới')
@section('breadcrumb', 'Quản lý học viên · Thêm học viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoc-vien/create.css') }}">
@endsection

@section('content')

    <form action="{{ route('admin.hoc-vien.store') }}" method="POST" id="hv-create-form" class="needs-validation" novalidate data-joi-schema="hocVien" autocomplete="off">
        @csrf

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="hv-create-header">
            <div class="hv-create-title">
                <i class="fas fa-user-plus"></i> Thêm học viên mới
            </div>
            <div class="hv-breadcrumb-actions">
                <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu học viên
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
                    <div class="hv-section-desc">Thông tin dùng để đăng nhập hệ thống</div>
                </div>
            </div>
            <div class="hv-section-body">
                <div class="form-grid">
                    {{-- Hidden inputs --}}
                    <input type="hidden" name="taiKhoan" id="taiKhoan">
                    <input type="hidden" name="matKhau" id="matKhau">
                    <input type="hidden" name="matKhau_confirmation" id="matKhau_confirmation">

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email <span class="required">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            placeholder="vd: nguyenvana@gmail.com">
                        @error('email')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CCCD (dùng để tự sinh mật khẩu tạm) --}}
                    <div class="form-group">
                        <label class="form-label" for="cccd">
                            CCCD / CMND <span class="form-hint-inline">(dùng làm mật khẩu tạm)</span>
                        </label>
                        <input type="text" id="cccd" name="cccd"
                            class="form-control @error('cccd') is-invalid @enderror" value="{{ old('cccd') }}"
                            placeholder="12 hoặc 9 số" maxlength="12">
                        @error('cccd')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Preview tài khoản & mật khẩu tự sinh --}}
                    <div class="form-group full">
                        <div class="default-pass-notice">
                            <i class="fas fa-shield-halved"></i>
                            <div>
                                <strong>Tài khoản &amp; mật khẩu được tạo tự động</strong>
                                <p>
                                    Tên đăng nhập: <code id="username-preview">HV######</code><br>
                                    Mật khẩu:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code
                                        id="password-preview">theo CCCD (hoặc <strong>12345678</strong> nếu chưa
                                        có)</code><br>
                                    Tên đăng nhập sẽ được hệ thống cấp tự động theo mã học viên. Học viên có thể đổi mật khẩu sau lần đăng nhập đầu tiên.
                                </p>
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
                    {{-- Họ tên --}}
                    <div class="form-group full">
                        <label class="form-label" for="hoTen">
                            Họ và tên <span class="required">*</span>
                        </label>
                        <input type="text" id="hoTen" name="hoTen"
                            class="form-control @error('hoTen') is-invalid @enderror" value="{{ old('hoTen') }}"
                            placeholder="vd: Nguyễn Văn A">
                        @error('hoTen')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ngày sinh --}}
                    <div class="form-group">
                        <label class="form-label" for="ngaySinh">Ngày sinh</label>
                        <input type="date" id="ngaySinh" name="ngaySinh"
                            class="form-control @error('ngaySinh') is-invalid @enderror" value="{{ old('ngaySinh') }}"
                            max="{{ date('Y-m-d') }}">
                        @error('ngaySinh')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Giới tính --}}
                    <div class="form-group">
                        <label class="form-label" for="gioiTinh">Giới tính</label>
                        <select id="gioiTinh" name="gioiTinh"
                            class="form-control @error('gioiTinh') is-invalid @enderror">
                            <option value="">— Chọn giới tính —</option>
                            <option value="1" {{ old('gioiTinh') === '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ old('gioiTinh') === '0' ? 'selected' : '' }}>Nữ</option>
                            <option value="2" {{ old('gioiTinh') === '2' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>

                    {{-- Số điện thoại --}}
                    <div class="form-group">
                        <label class="form-label" for="soDienThoai">Số điện thoại</label>
                        <input type="tel" id="soDienThoai" name="soDienThoai"
                            class="form-control @error('soDienThoai') is-invalid @enderror"
                            value="{{ old('soDienThoai') }}" placeholder="vd: 0901234567">
                    </div>

                    {{-- Zalo --}}
                    <div class="form-group">
                        <label class="form-label" for="zalo">Zalo</label>
                        <input type="tel" id="zalo" name="zalo"
                            class="form-control @error('zalo') is-invalid @enderror" value="{{ old('zalo') }}"
                            placeholder="SĐT Zalo (nếu khác)">
                        <div class="form-hint"><i class="fas fa-info-circle"></i> Để trống nếu giống SĐT chính</div>
                    </div>

                    {{-- Địa chỉ --}}
                    <div class="form-group full">
                        <label class="form-label" for="diaChi">Địa chỉ</label>
                        <input type="text" id="diaChi" name="diaChi"
                            class="form-control @error('diaChi') is-invalid @enderror" value="{{ old('diaChi') }}"
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
                            value="{{ old('nguoiGiamHo') }}" placeholder="vd: Nguyễn Thị B">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="moiQuanHe">Mối quan hệ</label>
                        <select id="moiQuanHe" name="moiQuanHe" class="form-control">
                            <option value="">— Chọn quan hệ —</option>
                            @foreach (['Bố', 'Mẹ', 'Anh', 'Chị', 'Ông', 'Bà', 'Chú', 'Bác', 'Khác'] as $rel)
                                <option value="{{ $rel }}" {{ old('moiQuanHe') === $rel ? 'selected' : '' }}>
                                    {{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="sdtGuardian">SĐT người giám hộ</label>
                        <input type="tel" id="sdtGuardian" name="sdtGuardian" class="form-control"
                            value="{{ old('sdtGuardian') }}" placeholder="vd: 0912345678">
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
                                    {{ old('ngonNguMucTieu') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trinhDoHienTai">Trình độ hiện tại</label>
                        <select id="trinhDoHienTai" name="trinhDoHienTai" class="form-control">
                            <option value="">— Chọn trình độ —</option>
                            @foreach (['Beginner (Mới bắt đầu)', 'Elementary (Sơ cấp)', 'Pre-Intermediate', 'Intermediate (Trung cấp)', 'Upper-Intermediate', 'Advanced (Nâng cao)'] as $lvl)
                                <option value="{{ $lvl }}"
                                    {{ old('trinhDoHienTai') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="nguonBietDen">Biết đến trung tâm qua</label>
                        <select id="nguonBietDen" name="nguonBietDen" class="form-control">
                            <option value="">— Chọn nguồn —</option>
                            @foreach (['Facebook', 'Zalo', 'Google / Website', 'Bạn bè giới thiệu', 'Panô / Tờ rơi', 'Khác'] as $src)
                                <option value="{{ $src }}" {{ old('nguonBietDen') === $src ? 'selected' : '' }}>
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
                        placeholder="Nhập ghi chú thêm về học viên (chỉ admin và nhân viên xem được)...">{{ old('ghiChu') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────── --}}
        <div class="hv-form-footer">
            <a href="{{ route('admin.hoc-vien.index') }}" class="btn-back">
                <i class="fas fa-times"></i> Hủy bỏ
            </a>
            <button type="reset" class="btn-back" style="cursor:pointer">
                <i class="fas fa-undo"></i> Nhập lại
            </button>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Lưu học viên
            </button>
        </div>

    </form>

@endsection

@section('script')
    <script>
        // ── Auto-set temporary password from CCCD ──────────────────────
        const cccdInput = document.getElementById('cccd');
        const passHidden = document.getElementById('matKhau');
        const passConfirm = document.getElementById('matKhau_confirmation');
        const DEFAULT_PASS = '12345678';

        function syncFromCCCD() {
            const cccd = cccdInput?.value?.trim();

            // ── Update preview text ────────────────────────────────────
            const uPrev = document.getElementById('username-preview');
            const pPrev = document.getElementById('password-preview');
            if (uPrev) uPrev.textContent = 'HV######';

            // ── Password: CCCD if ≥ 8 chars, else default ─────────────
            const pwd = cccd && cccd.length >= 8 ? cccd : DEFAULT_PASS;
            if (passHidden) passHidden.value = pwd;
            if (passConfirm) passConfirm.value = pwd;
            if (pPrev) pPrev.textContent = pwd;
        }

        // Sync immediately and on every CCCD change
        syncFromCCCD();
        cccdInput?.addEventListener('input', syncFromCCCD);

        // Safety net before submit
        document.getElementById('hv-create-form')
            ?.addEventListener('submit', syncFromCCCD);

        // ── Smooth scroll to sections via step links ────────────────────
        document.querySelectorAll('.hv-step').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.hv-step').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    // Offset for sticky bar height
                    const offset = 70;
                    const top = target.getBoundingClientRect().top + window.scrollY - offset;
                    window.scrollTo({
                        top,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // ── Highlight active step on scroll ────────────────────────────
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
