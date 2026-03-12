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

                        {{-- ═══ AVATAR ═══ --}}
                        <div class="profile-section">
                            <h6 class="profile-section-title">
                                <i class="fas fa-camera me-2"></i>Ảnh đại diện
                            </h6>
                            <form action="{{ route('home.student.update-avatar') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="avatar-upload-area">
                                    <div class="avatar-preview" id="avatarPreviewWrap">
                                        <img id="avatarPreview" src="{{ Auth::user()->getAvatarUrl() }}"
                                            alt="Ảnh đại diện">
                                        <div class="avatar-overlay">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    </div>
                                    <div class="avatar-info">
                                        <p class="text-muted small mb-2">Chấp nhận JPG, PNG, GIF, WebP. Tối đa 2MB.</p>
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            <label class="btn btn-outline-secondary btn-sm" for="avatarInput">
                                                <i class="fas fa-folder-open me-1"></i> Chọn ảnh
                                            </label>
                                            <input type="file" id="avatarInput" name="anhDaiDien" accept="image/*"
                                                class="d-none">
                                            <button type="submit" class="btn btn-update btn-sm" id="avatarSubmitBtn"
                                                style="display:none!important">
                                                <i class="fas fa-upload me-1"></i> Tải lên
                                            </button>
                                        </div>
                                        @error('anhDaiDien')
                                            <div class="text-danger small mt-1"><i
                                                    class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</div>
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
        // Preview ảnh khi chọn file
        document.getElementById('avatarInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('avatarPreview').src = evt.target.result;
                // Hiện nút Tải lên
                const btn = document.getElementById('avatarSubmitBtn');
                if (btn) btn.style.removeProperty('display');
            };
            reader.readAsDataURL(file);
            // Auto submit form avatar
            e.target.closest('form').submit();
        });
    </script>
@endsection
