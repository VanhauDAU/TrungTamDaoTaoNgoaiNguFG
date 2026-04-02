@extends('layouts.client')
@section('title', 'Thông tin cá nhân')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row g-4">

                {{-- SIDEBAR --}}
                @include('components.client.account-sidebar')

                {{-- MAIN CONTENT --}}
                <div class="col-lg-9">
                    <div class="account-content">

                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Thông tin cá nhân'],
                        ]" />

                        <h2 class="content-title">
                            <i class="fas fa-user-circle me-2"></i> Thông tin cá nhân
                        </h2>

                        {{-- Thông báo --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (session('success_avatar'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-image me-2"></i> {{ session('success_avatar') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Vui lòng kiểm tra lại:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (Auth::user()->auth_provider === 'google')
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <i class="fab fa-google me-2"></i>
                                        Tài khoản của bạn đang đăng nhập bằng Google. Nếu muốn đăng nhập thêm bằng email
                                        hoặc tên tài khoản, hãy thiết lập mật khẩu local qua email.
                                    </div>
                                    <form action="{{ route('home.student.setup-password') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-key me-1"></i> Thiết lập mật khẩu
                                        </button>
                                    </form>
                                </div>
                                @error('password_setup')
                                    <div class="text-danger small mt-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- ═══ AVATAR ═══ --}}
                        <div class="profile-section">
                            <h6 class="profile-section-title">
                                <i class="fas fa-camera me-2"></i>Ảnh đại diện
                            </h6>
                            <form action="{{ route('home.student.update-avatar') }}" method="POST"
                                enctype="multipart/form-data" id="avatarUploadForm">
                                @csrf
                                <div class="avatar-upload-area">

                                    {{-- Avatar: hiện tại / xem trước + nút bên dưới --}}
                                    <div class="avatar-current-card">
                                        <div class="avatar-preview" id="avatarPreviewWrap">
                                            <img id="avatarPreview" src="{{ Auth::user()->getAvatarUrl() }}"
                                                data-avatar-image alt="Ảnh đại diện">
                                            <div class="avatar-overlay" id="avatarOverlay">
                                                <label for="avatarInput" class="avatar-overlay-inner">
                                                    <i class="fas fa-camera"></i>
                                                    <span class="overlay-hint">Đổi ảnh</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Nút hiện khi chọn ảnh mới --}}
                                        <div class="avatar-card-actions d-none" id="avatarCardActions">
                                            <button type="button" class="btn btn-update btn-sm w-100" id="avatarConfirmBtn">
                                                <i class="fas fa-upload me-1"></i> Xác nhận
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="avatarCancelBtn">
                                                <i class="fas fa-times me-1"></i> Hủy
                                            </button>
                                        </div>

                                        {{-- Thanh tiến trình upload (hiện phía trước, ngay cạnh ảnh) --}}
                                        <div class="avatar-progress-wrap d-none" id="avatarProgressWrap">
                                            <div class="avatar-progress-track">
                                                <div class="avatar-progress-fill" id="avatarProgressFill" style="width:0%"></div>
                                            </div>
                                            <div class="avatar-progress-footer">
                                                <span class="avatar-progress-text" id="avatarProgressText">Đang chuẩn bị...</span>
                                                <span class="avatar-progress-pct" id="avatarProgressPct">0%</span>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Panel thông tin --}}
                                    <div class="avatar-info">
                                        <div class="avatar-info-header">
                                            <strong>Đổi ảnh đại diện</strong>
                                            <p class="text-muted small mb-0">Nhấn vào ảnh hoặc chọn file bên dưới, ảnh xem trước hiện ngay tại chỗ.</p>
                                        </div>
                                        <div class="avatar-actions">
                                            <label class="btn btn-outline-secondary btn-sm" for="avatarInput">
                                                <i class="fas fa-folder-open me-1"></i> Chọn ảnh
                                            </label>
                                            <input type="file" id="avatarInput" name="anhDaiDien" accept="image/*" class="d-none">
                                        </div>

                                        <p class="avatar-guideline mb-0">Chấp nhận JPG, PNG, GIF, WebP. Tối đa 2MB.</p>

                                        {{-- Tên file đã chọn --}}
                                        <div class="avatar-selected-file d-none" id="avatarSelectedFile"></div>

                                        {{-- Feedback --}}
                                        <div class="avatar-upload-feedback d-none" id="avatarUploadFeedback"></div>

                                        <noscript>
                                            <button type="submit" class="btn btn-update btn-sm mt-2">
                                                <i class="fas fa-upload me-1"></i> Tải lên
                                            </button>
                                        </noscript>
                                        @error('anhDaiDien')
                                            <div class="text-danger small mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                </div>
                            </form>
                        </div>

                        {{-- ═══ FORM THÔNG TIN ═══ --}}
                        <form action="{{ route('home.student.update-profile') }}" method="POST">
                            @csrf

                            {{-- Thông tin cơ bản --}}
                            <div class="profile-section">
                                <h6 class="profile-section-title">
                                    <i class="fas fa-id-card me-2"></i>Thông tin cơ bản
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Họ và tên <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="hoTen"
                                            class="form-control form-control-custom @error('hoTen') is-invalid @enderror"
                                            value="{{ old('hoTen', Auth::user()->hoSoNguoiDung->hoTen ?? '') }}"
                                            placeholder="Nhập họ và tên của bạn">
                                        @error('hoTen')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Email đăng nhập</label>
                                        <input type="email" class="form-control form-control-custom bg-light"
                                            value="{{ Auth::user()->email }}" disabled readonly>
                                        <small class="text-muted">Email không thể thay đổi</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Tên đăng nhập</label>
                                        <input type="text" class="form-control form-control-custom bg-light"
                                            value="{{ Auth::user()->taiKhoan }}" disabled readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Hình thức đăng nhập</label>
                                        <input type="text" class="form-control form-control-custom bg-light"
                                            value="{{ Auth::user()->getAuthProviderLabel() }}" disabled readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Số điện thoại</label>
                                        <input type="text" name="soDienThoai"
                                            class="form-control form-control-custom @error('soDienThoai') is-invalid @enderror"
                                            value="{{ old('soDienThoai', Auth::user()->hoSoNguoiDung->soDienThoai ?? '') }}"
                                            placeholder="Nhập số điện thoại">
                                        @error('soDienThoai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Zalo</label>
                                        <input type="text" name="zalo"
                                            class="form-control form-control-custom @error('zalo') is-invalid @enderror"
                                            value="{{ old('zalo', Auth::user()->hoSoNguoiDung->zalo ?? '') }}"
                                            placeholder="Số Zalo (nếu khác SĐT)">
                                        @error('zalo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Ngày sinh</label>
                                        <input type="date" name="ngaySinh"
                                            class="form-control form-control-custom @error('ngaySinh') is-invalid @enderror"
                                            value="{{ old('ngaySinh', Auth::user()->hoSoNguoiDung?->getRawOriginal('ngaySinh') ?? '') }}">
                                        @error('ngaySinh')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Giới tính</label>
                                        <select name="gioiTinh"
                                            class="form-control form-control-custom @error('gioiTinh') is-invalid @enderror">
                                            <option value="">-- Chọn giới tính --</option>
                                            <option value="1" @selected(old('gioiTinh', Auth::user()->hoSoNguoiDung->gioiTinh ?? '') == '1')>Nam</option>
                                            <option value="0" @selected(old('gioiTinh', Auth::user()->hoSoNguoiDung->gioiTinh ?? '') == '0')>Nữ</option>
                                            <option value="2" @selected(old('gioiTinh', Auth::user()->hoSoNguoiDung->gioiTinh ?? '') == '2')>Khác</option>
                                        </select>
                                        @error('gioiTinh')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">CCCD / CMND</label>
                                        <input type="text" name="cccd"
                                            class="form-control form-control-custom @error('cccd') is-invalid @enderror"
                                            value="{{ old('cccd', Auth::user()->hoSoNguoiDung->cccd ?? '') }}"
                                            placeholder="Số CCCD hoặc CMND">
                                        @error('cccd')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Địa chỉ</label>
                                        <input type="text" name="diaChi"
                                            class="form-control form-control-custom @error('diaChi') is-invalid @enderror"
                                            value="{{ old('diaChi', Auth::user()->hoSoNguoiDung->diaChi ?? '') }}"
                                            placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành">
                                        @error('diaChi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Thông tin học tập --}}
                            <div class="profile-section">
                                <h6 class="profile-section-title">
                                    <i class="fas fa-graduation-cap me-2"></i>Thông tin học tập
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Trình độ hiện tại</label>
                                        <select name="trinhDoHienTai" class="form-control form-control-custom">
                                            <option value="">-- Chọn trình độ --</option>
                                            @foreach (['Mất gốc', 'Elementary (Sơ cấp)', 'Pre-Intermediate', 'Intermediate (Trung cấp)', 'Upper-Intermediate', 'Advanced (Cao cấp)'] as $td)
                                                <option value="{{ $td }}" @selected(old('trinhDoHienTai', Auth::user()->hoSoNguoiDung->trinhDoHienTai ?? '') == $td)>
                                                    {{ $td }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Ngôn ngữ mục tiêu</label>
                                        <select name="ngonNguMucTieu" class="form-control form-control-custom">
                                            <option value="">-- Chọn ngôn ngữ --</option>
                                            @foreach (['Tiếng Anh', 'Tiếng Nhật', 'Tiếng Hàn', 'Tiếng Trung', 'Tiếng Pháp', 'Tiếng Đức', 'Khác'] as $nn)
                                                <option value="{{ $nn }}" @selected(old('ngonNguMucTieu', Auth::user()->hoSoNguoiDung->ngonNguMucTieu ?? '') == $nn)>
                                                    {{ $nn }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Biết đến trung tâm qua</label>
                                        <select name="nguonBietDen" class="form-control form-control-custom">
                                            <option value="">-- Chọn nguồn --</option>
                                            @foreach (['Bạn bè giới thiệu', 'Facebook', 'Google', 'Zalo', 'Banner/Tờ rơi', 'Youtube', 'Khác'] as $nb)
                                                <option value="{{ $nb }}" @selected(old('nguonBietDen', Auth::user()->hoSoNguoiDung->nguonBietDen ?? '') == $nb)>
                                                    {{ $nb }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Người giám hộ --}}
                            <div class="profile-section">
                                <h6 class="profile-section-title">
                                    <i class="fas fa-users me-2"></i>Người giám hộ / Phụ huynh
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Họ tên người giám hộ</label>
                                        <input type="text" name="nguoiGiamHo" class="form-control form-control-custom"
                                            value="{{ old('nguoiGiamHo', Auth::user()->hoSoNguoiDung->nguoiGiamHo ?? '') }}"
                                            placeholder="Họ và tên">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Số điện thoại giám hộ</label>
                                        <input type="text" name="sdtGuardian" class="form-control form-control-custom"
                                            value="{{ old('sdtGuardian', Auth::user()->hoSoNguoiDung->sdtGuardian ?? '') }}"
                                            placeholder="Số điện thoại">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Mối quan hệ</label>
                                        <select name="moiQuanHe" class="form-control form-control-custom">
                                            <option value="">-- Chọn --</option>
                                            @foreach (['Bố/Mẹ', 'Anh/Chị', 'Vợ/Chồng', 'Người thân khác'] as $mq)
                                                <option value="{{ $mq }}" @selected(old('moiQuanHe', Auth::user()->hoSoNguoiDung->moiQuanHe ?? '') == $mq)>
                                                    {{ $mq }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Ghi chú --}}
                            <div class="profile-section">
                                <h6 class="profile-section-title">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi chú thêm
                                </h6>
                                <textarea name="ghiChu" class="form-control form-control-custom" rows="3"
                                    placeholder="Ghi chú về mục tiêu học tập, yêu cầu đặc biệt...">{{ old('ghiChu', Auth::user()->hoSoNguoiDung->ghiChu ?? '') }}</textarea>
                            </div>

                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-update">
                                    <i class="fas fa-save me-2"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        (() => {
            const form           = document.getElementById('avatarUploadForm');
            const input          = document.getElementById('avatarInput');
            const avatarImg      = document.getElementById('avatarPreview');      // circle img (hiện tại và preview)
            const previewWrap    = document.getElementById('avatarPreviewWrap');
            const cardActions    = document.getElementById('avatarCardActions');  // div chứa 2 nút
            const confirmBtn     = document.getElementById('avatarConfirmBtn');
            const cancelBtn      = document.getElementById('avatarCancelBtn');
            const selectedFileEl = document.getElementById('avatarSelectedFile');
            const feedback       = document.getElementById('avatarUploadFeedback');
            const progressWrap   = document.getElementById('avatarProgressWrap');
            const progressFill   = document.getElementById('avatarProgressFill');
            const progressText   = document.getElementById('avatarProgressText');
            const progressPct    = document.getElementById('avatarProgressPct');

            if (!form || !input || !avatarImg || !confirmBtn || !cancelBtn) return;

            const ALLOWED  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const MAX_SIZE = 2 * 1024 * 1024;
            let originalAvatarUrl = avatarImg.getAttribute('src');
            let previewUrl  = null;
            let isUploading = false;

            /* --- Helpers --- */
            const fmtSize = (n) => n < 1024 ? `${n} B`
                : n < 1048576 ? `${(n / 1024).toFixed(1)} KB`
                : `${(n / 1048576).toFixed(2)} MB`;

            const ICONS = {
                error  : 'fas fa-times-circle',
                success: 'fas fa-check-circle',
                info   : 'fas fa-info-circle',
            };

            const setFeedback = (msg, type = 'info') => {
                feedback.innerHTML = `<i class="${ICONS[type] ?? ICONS.info}"></i><span>${msg}</span>`;
                feedback.className = 'avatar-upload-feedback';
                feedback.classList.add(type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-muted');
            };
            const clearFeedback = () => {
                feedback.innerHTML = '';
                feedback.className = 'avatar-upload-feedback d-none';
            };

            const setProgress = (pct, label) => {
                const p = Math.max(0, Math.min(100, pct));
                if (progressFill) progressFill.style.width = `${p}%`;
                if (progressText) progressText.textContent = label ?? `Đang tải lên: ${p}%`;
                if (progressPct)  progressPct.textContent  = `${p}%`;
            };
            const resetProgress = () => {
                setProgress(0, 'Đang chuẩn bị...');
                progressWrap.classList.add('d-none');
            };

            const revokePreviewUrl = () => {
                if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
            };

            /* Ẩn nút, khôi phục ảnh gốc */
            const resetSelection = ({ restoreOriginal = true, keepFeedback = false } = {}) => {
                revokePreviewUrl();
                input.value = '';
                cardActions.classList.add('d-none');
                confirmBtn.disabled = false;
                cancelBtn.disabled  = false;
                selectedFileEl.textContent = '';
                selectedFileEl.classList.add('d-none');
                previewWrap.classList.remove('is-uploading');
                resetProgress();
                if (restoreOriginal) {
                    avatarImg.setAttribute('src', originalAvatarUrl); // khôi phục ảnh gốc
                    previewWrap.classList.remove('is-preview');        // gỡ class preview
                }
                if (!keepFeedback) clearFeedback();
            };

            const updateAllAvatarImages = (url) =>
                document.querySelectorAll('[data-avatar-image]').forEach(img => img.setAttribute('src', url));

            /* --- Chọn file --- */
            input.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                resetProgress();
                clearFeedback();

                if (!file) { resetSelection(); return; }

                if (!ALLOWED.includes(file.type)) {
                    resetSelection({ keepFeedback: true });
                    setFeedback('Ảnh không đúng định dạng. Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.', 'error');
                    return;
                }
                if (file.size > MAX_SIZE) {
                    resetSelection({ keepFeedback: true });
                    setFeedback('Ảnh vượt quá giới hạn 2 MB. Vui lòng chọn ảnh nhỏ hơn.', 'error');
                    return;
                }

                revokePreviewUrl();
                previewUrl = URL.createObjectURL(file);

                // Swap ảnh ngay vào circle hiện tại
                avatarImg.setAttribute('src', previewUrl);
                previewWrap.classList.add('is-preview');  // hiện tiêu đề "Xem trước"

                // Hiện các nút bên dưới avatar
                cardActions.classList.remove('d-none');

                selectedFileEl.textContent = `${file.name} (${fmtSize(file.size)})`;
                selectedFileEl.classList.remove('d-none');
            });

            /* --- Hủy --- */
            cancelBtn.addEventListener('click', () => { if (!isUploading) resetSelection(); });

            /* --- Xác nhận upload --- */
            confirmBtn.addEventListener('click', () => {
                if (isUploading || !input.files?.length) return;

                isUploading = true;
                confirmBtn.disabled = true;
                cancelBtn.disabled  = true;
                previewWrap.classList.add('is-uploading');
                progressWrap.classList.remove('d-none');
                setProgress(0, 'Đang chuẩn bị tải lên...');
                setFeedback('Đang tải ảnh đại diện lên hệ thống...', 'info');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', (ev) => {
                    if (!ev.lengthComputable) return;
                    setProgress(Math.round((ev.loaded / ev.total) * 100), 'Đang tải lên...');
                });

                xhr.onload = () => {
                    isUploading = false;
                    previewWrap.classList.remove('is-uploading');

                    let res = null;
                    try { res = xhr.responseText ? JSON.parse(xhr.responseText) : null; } catch (_) {}

                    if (xhr.status >= 200 && xhr.status < 300) {
                        setProgress(100, 'Hoàn tất!');
                        originalAvatarUrl = res?.avatarUrl || avatarImg.getAttribute('src');
                        updateAllAvatarImages(originalAvatarUrl);
                        avatarImg.setAttribute('src', originalAvatarUrl);
                        setTimeout(() => {
                            resetSelection({ restoreOriginal: false, keepFeedback: true });
                            setFeedback(res?.message || 'Cập nhật ảnh đại diện thành công!', 'success');
                        }, 600);
                        return;
                    }

                    confirmBtn.disabled = false;
                    cancelBtn.disabled  = false;
                    progressWrap.classList.add('d-none');

                    if (xhr.status === 422 && res?.errors) {
                        setFeedback(Object.values(res.errors).flat()[0] || 'Dữ liệu ảnh chưa hợp lệ.', 'error');
                        return;
                    }
                    setFeedback(res?.message || 'Không thể tải ảnh lên lúc này. Vui lòng thử lại.', 'error');
                };

                xhr.onerror = () => {
                    isUploading = false;
                    previewWrap.classList.remove('is-uploading');
                    confirmBtn.disabled = false;
                    cancelBtn.disabled  = false;
                    progressWrap.classList.add('d-none');
                    setFeedback('Kết nối bị gián đoạn. Vui lòng kiểm tra mạng và thử lại.', 'error');
                };

                xhr.send(new FormData(form));
            });

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (!isUploading && input.files?.length) confirmBtn.click();
            });
        })();
    </script>
@endsection
