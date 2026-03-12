@extends('layouts.admin')

@section('title', 'Thêm nhân viên')
@section('page-title', 'Thêm nhân viên mới')
@section('breadcrumb', 'Quản lý nhân viên · Thêm nhân viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/nhan-vien/create.css') }}">
@endsection

@section('content')

    <form action="{{ route('admin.nhan-vien.store') }}" method="POST" id="nv-create-form" autocomplete="off">
        @csrf

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="nv-create-header">
            <div class="nv-create-title">
                <i class="fas fa-user-plus"></i> Thêm nhân viên mới
            </div>
            <div class="nv-breadcrumb-actions">
                <a href="{{ route('admin.nhan-vien.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu nhân viên
                </button>
            </div>
        </div>

        {{-- ── Step indicator ───────────────────────────────────── --}}
        <div class="nv-steps-sticky">
            <div class="nv-steps">
                <a href="#sec-account" class="nv-step active"><span>1</span> Tài khoản</a>
                <a href="#sec-personal" class="nv-step"><span>2</span> Cá nhân</a>
                <a href="#sec-staff" class="nv-step"><span>3</span> Nhân sự</a>
                <a href="#sec-note" class="nv-step"><span>4</span> Ghi chú</a>
            </div>
        </div>

        {{-- ── Validation errors ───────────────────────────────── --}}
        @if ($errors->any())
            <div class="nv-form-section" style="border-color:#fca5a5;background:#fef9f9;margin-bottom:20px">
                <div class="nv-section-body">
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
        <div class="nv-form-section" id="sec-account">
            <div class="nv-section-header">
                <div class="nv-section-icon indigo"><i class="fas fa-key"></i></div>
                <div>
                    <div class="nv-section-title">Thông tin tài khoản</div>
                    <div class="nv-section-desc">Thông tin dùng để đăng nhập hệ thống</div>
                </div>
            </div>
            <div class="nv-section-body">
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
                                    Tên đăng nhập: <code id="username-preview">NV######</code><br>
                                    Mật khẩu:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code
                                        id="password-preview">theo CCCD (hoặc <strong>12345678</strong> nếu chưa
                                        có)</code><br>
                                    Tên đăng nhập sẽ được hệ thống cấp tự động theo mã nhân sự. Nhân viên có thể đổi mật khẩu sau lần đăng nhập đầu tiên.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Thông tin cá nhân ───────────────────── --}}
        <div class="nv-form-section" id="sec-personal">
            <div class="nv-section-header">
                <div class="nv-section-icon blue"><i class="fas fa-id-card"></i></div>
                <div>
                    <div class="nv-section-title">Thông tin cá nhân</div>
                    <div class="nv-section-desc">Họ tên, ngày sinh, địa chỉ và liên hệ</div>
                </div>
            </div>
            <div class="nv-section-body">
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

        {{-- ── SECTION 3: Thông tin nhân sự ────────────────────── --}}
        <div class="nv-form-section" id="sec-staff">
            <div class="nv-section-header">
                <div class="nv-section-icon orange"><i class="fas fa-briefcase"></i></div>
                <div>
                    <div class="nv-section-title">Thông tin nhân sự</div>
                    <div class="nv-section-desc">Chức vụ, chuyên môn, bằng cấp và cơ sở làm việc</div>
                </div>
            </div>
            <div class="nv-section-body">
                <div class="form-grid">
                    {{-- Chức vụ --}}
                    <div class="form-group">
                        <label class="form-label" for="chucVu">Chức vụ</label>
                        <select id="chucVu" name="chucVu" class="form-control">
                            <option value="">— Chọn chức vụ —</option>
                            @foreach (['Nhân viên', 'Quản lý', 'Trưởng phòng', 'Phó phòng'] as $cv)
                                <option value="{{ $cv }}" {{ old('chucVu') === $cv ? 'selected' : '' }}>
                                    {{ $cv }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Chuyên môn --}}
                    <div class="form-group">
                        <label class="form-label" for="chuyenMon">Chuyên môn</label>
                        <select id="chuyenMon" name="chuyenMon" class="form-control">
                            <option value="">— Chọn chuyên môn —</option>
                            @foreach (['Kế toán', 'Hành chính nhân sự', 'IT', 'Marketing', 'Tư vấn tuyển sinh', 'Khác'] as $cm)
                                <option value="{{ $cm }}" {{ old('chuyenMon') === $cm ? 'selected' : '' }}>
                                    {{ $cm }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bằng cấp --}}
                    <div class="form-group">
                        <label class="form-label" for="bangCap">Bằng cấp</label>
                        <select id="bangCap" name="bangCap" class="form-control">
                            <option value="">— Chọn bằng cấp —</option>
                            @foreach (['Trung cấp', 'Cao đẳng', 'Cử nhân', 'Thạc sĩ', 'Khác'] as $bc)
                                <option value="{{ $bc }}" {{ old('bangCap') === $bc ? 'selected' : '' }}>
                                    {{ $bc }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Học vị --}}
                    <div class="form-group">
                        <label class="form-label" for="hocVi">Khóa học / Chứng chỉ</label>
                        <input type="text" id="hocVi" name="hocVi"
                            class="form-control @error('hocVi') is-invalid @enderror" value="{{ old('hocVi') }}"
                            placeholder="vd: TOEIC 600, MOS...">
                    </div>

                    {{-- Loại hợp đồng --}}
                    <div class="form-group">
                        <label class="form-label" for="loaiHopDong">Loại hợp đồng</label>
                        <select id="loaiHopDong" name="loaiHopDong" class="form-control">
                            <option value="">— Chọn loại hợp đồng —</option>
                            @foreach (['Toàn thời gian', 'Bán thời gian', 'Thử việc'] as $lhd)
                                <option value="{{ $lhd }}" {{ old('loaiHopDong') === $lhd ? 'selected' : '' }}>
                                    {{ $lhd }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ngày vào làm --}}
                    <div class="form-group">
                        <label class="form-label" for="ngayVaoLam">Ngày vào làm</label>
                        <input type="date" id="ngayVaoLam" name="ngayVaoLam"
                            class="form-control @error('ngayVaoLam') is-invalid @enderror"
                            value="{{ old('ngayVaoLam', date('Y-m-d')) }}">
                    </div>

                    {{-- Cơ sở làm việc --}}
                    <div class="form-group full">
                        <label class="form-label" for="coSoId">
                            Cơ sở làm việc <span class="required">*</span>
                        </label>
                        <select id="coSoId" name="coSoId"
                            class="form-control @error('coSoId') is-invalid @enderror">
                            <option value="">— Chọn cơ sở —</option>
                            @foreach ($coSos as $cs)
                                <option value="{{ $cs->coSoId }}" {{ old('coSoId') == $cs->coSoId ? 'selected' : '' }}>
                                    {{ $cs->tenCoSo }} — {{ $cs->diaChiDayDu }}
                                </option>
                            @endforeach
                        </select>
                        @error('coSoId')
                            <div class="invalid-feedback"><i class="fas fa-circle-xmark"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 4: Ghi chú ─────────────────────────────── --}}
        <div class="nv-form-section" id="sec-note">
            <div class="nv-section-header">
                <div class="nv-section-icon purple"><i class="fas fa-sticky-note"></i></div>
                <div>
                    <div class="nv-section-title">Ghi chú</div>
                    <div class="nv-section-desc">Thông tin thêm về nhân viên</div>
                </div>
            </div>
            <div class="nv-section-body">
                <div class="form-group">
                    <label class="form-label" for="ghiChu">Ghi chú nội bộ</label>
                    <textarea id="ghiChu" name="ghiChu" class="form-control" rows="4"
                        placeholder="Nhập ghi chú thêm về nhân viên (chỉ admin và nhân sự xem được)...">{{ old('ghiChu') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────── --}}
        <div class="nv-form-footer">
            <a href="{{ route('admin.nhan-vien.index') }}" class="btn-back">
                <i class="fas fa-times"></i> Hủy bỏ
            </a>
            <button type="reset" class="btn-back" style="cursor:pointer">
                <i class="fas fa-undo"></i> Nhập lại
            </button>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Lưu nhân viên
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
            if (uPrev) uPrev.textContent = 'NV######';

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
        document.getElementById('nv-create-form')
            ?.addEventListener('submit', syncFromCCCD);

        // ── Smooth scroll to sections via step links ────────────────────
        document.querySelectorAll('.nv-step').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nv-step').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
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
        const sections = ['sec-account', 'sec-personal', 'sec-staff', 'sec-note'];
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = '#' + entry.target.id;
                    document.querySelectorAll('.nv-step').forEach(s => {
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
