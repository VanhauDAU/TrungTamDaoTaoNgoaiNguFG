@extends('layouts.admin')

@section('title', 'Thêm cơ sở mới')
@section('page-title', 'Cơ sở Đào tạo')
@section('breadcrumb', 'Cấu hình hệ thống · Cơ sở & Phòng học · Thêm mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/co-so/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/co-so/create.css') }}">
@endsection

@section('content')

    <div class="cs-form-wrap">
        <div class="cs-form-header">
            <a href="{{ route('admin.co-so.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <div class="cs-form-title"><i class="fas fa-plus-circle" style="color:#27c4b5;margin-right:8px;"></i> Thêm cơ sở
                đào tạo mới</div>
        </div>

        <form action="{{ route('admin.co-so.store') }}" method="POST" autocomplete="off">
            @csrf

            <div class="form-grid">
                {{-- Mã cơ sở --}}
                <div class="form-group">
                    <label class="form-label" for="maCoSo">Mã cơ sở <span class="req">*</span></label>
                    <input type="text" id="maCoSo" name="maCoSo"
                        class="form-control @error('maCoSo') is-invalid @enderror" value="{{ old('maCoSo') }}"
                        placeholder="VD: CS01">
                    @error('maCoSo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Tên cơ sở --}}
                <div class="form-group">
                    <label class="form-label" for="tenCoSo">Tên cơ sở <span class="req">*</span></label>
                    <input type="text" id="tenCoSo" name="tenCoSo"
                        class="form-control @error('tenCoSo') is-invalid @enderror" value="{{ old('tenCoSo') }}"
                        placeholder="Tên cơ sở chi nhánh...">
                    @error('tenCoSo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Số điện thoại --}}
                <div class="form-group">
                    <label class="form-label" for="soDienThoai">Số điện thoại</label>
                    <input type="text" id="soDienThoai" name="soDienThoai"
                        class="form-control @error('soDienThoai') is-invalid @enderror" value="{{ old('soDienThoai') }}"
                        placeholder="Số hotline/điện thoại bàn">
                    @error('soDienThoai')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email liên hệ</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                        placeholder="example@fivegenius.com">
                    @error('email')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- ════ ĐỊA CHỈ ════ --}}
                {{-- Tỉnh / Thành phố --}}
                <div class="form-group">
                    <label class="form-label" for="tinhThanhId">Tỉnh / Thành phố</label>
                    <select id="tinhThanhId" name="tinhThanhId"
                        class="form-control @error('tinhThanhId') is-invalid @enderror">
                        <option value="">— Chọn tỉnh/thành —</option>
                        @foreach ($tinhThanhs as $tinh)
                            <option value="{{ $tinh->tinhThanhId }}" data-ma-api="{{ $tinh->maAPI }}"
                                {{ old('tinhThanhId') == $tinh->tinhThanhId ? 'selected' : '' }}>
                                {{ $tinh->tenTinhThanh }}
                            </option>
                        @endforeach
                    </select>
                    @error('tinhThanhId')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Phường / Xã (load theo tỉnh) --}}
                <div class="form-group">
                    <label class="form-label" for="maPhuongXa">Phường / Xã</label>
                    <div class="select-loading-wrap">
                        <select id="maPhuongXa" name="maPhuongXa"
                            class="form-control @error('maPhuongXa') is-invalid @enderror" disabled>
                            <option value="">— Chọn tỉnh trước —</option>
                        </select>
                        <span class="select-spinner" id="phuongXaSpinner" style="display:none;">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </span>
                    </div>
                    <input type="hidden" id="tenPhuongXa" name="tenPhuongXa" value="{{ old('tenPhuongXa') }}">
                    @error('maPhuongXa')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Địa chỉ chi tiết --}}
                <div class="form-group full">
                    <label class="form-label" for="diaChi">Địa chỉ chi tiết <span class="req">*</span></label>
                    <input type="text" id="diaChi" name="diaChi"
                        class="form-control @error('diaChi') is-invalid @enderror" value="{{ old('diaChi') }}"
                        placeholder="Số nhà, tên đường...">
                    @error('diaChi')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- ════ TỌA ĐỘ ════ --}}
                <div class="form-group">
                    <label class="form-label" for="viDo">
                        Vĩ độ (Latitude)
                        <a href="https://maps.google.com" target="_blank" class="hint-link"><i
                                class="fas fa-map-pin"></i> Lấy từ Google Maps</a>
                    </label>
                    <input type="number" step="any" id="viDo" name="viDo"
                        class="form-control @error('viDo') is-invalid @enderror" value="{{ old('viDo') }}"
                        placeholder="Tự động cập nhật khi dán link Google Maps" readonly>
                    @error('viDo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="kinhDo">Kinh độ (Longitude)</label>
                    <input type="number" step="any" id="kinhDo" name="kinhDo"
                        class="form-control @error('kinhDo') is-invalid @enderror" value="{{ old('kinhDo') }}"
                        placeholder="Tự động cập nhật khi dán link Google Maps" readonly>
                    @error('kinhDo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
                {{-- Ngày khai trương --}}
                <div class="form-group">
                    <label class="form-label" for="ngayKhaiTruong">Ngày khai trương</label>
                    <input type="date" id="ngayKhaiTruong" name="ngayKhaiTruong"
                        class="form-control @error('ngayKhaiTruong') is-invalid @enderror"
                        value="{{ old('ngayKhaiTruong') }}">
                    @error('ngayKhaiTruong')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Trạng thái --}}
                <div class="form-group">
                    <label class="form-label" for="trangThai">Trạng thái hoạt động <span class="req">*</span></label>
                    <select id="trangThai" name="trangThai"
                        class="form-control @error('trangThai') is-invalid @enderror">
                        <option value="1" {{ old('trangThai', '1') == '1' ? 'selected' : '' }}>Đang hoạt động
                        </option>
                        <option value="0" {{ old('trangThai') == '0' ? 'selected' : '' }}>Tạm ngưng</option>
                    </select>
                    @error('trangThai')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Google Map iframe --}}
                <div class="form-group full">
                    <label class="form-label" for="banDoGoogle">Link / Mã nhúng Google Map</label>
                    <textarea id="banDoGoogle" name="banDoGoogle" class="form-control @error('banDoGoogle') is-invalid @enderror"
                        rows="3" placeholder="Nhập link Google Map hoặc phần src của iframe...">{{ old('banDoGoogle') }}</textarea>
                    <div class="map-hint">Sẽ dùng thẻ iframe để nhúng bản đồ trên website. Tọa độ vĩ/kinh độ dùng để hiển
                        thị marker trên bản đồ trang liên hệ.</div>
                    @error('banDoGoogle')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full" style="text-align: right; margin-top:10px;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Thêm mới</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('script')
    <script>
        (function() {
            const tinhSelect = document.getElementById('tinhThanhId');
            const phuongSelect = document.getElementById('maPhuongXa');
            const tenPhuongHidden = document.getElementById('tenPhuongXa');
            const spinner = document.getElementById('phuongXaSpinner');

            async function loadPhuongXa(maTinh, selectedMa) {
                if (!maTinh) {
                    phuongSelect.innerHTML = '<option value="">— Chọn tỉnh trước —</option>';
                    phuongSelect.disabled = true;
                    tenPhuongHidden.value = '';
                    return;
                }

                spinner.style.display = 'inline-flex';
                phuongSelect.disabled = true;
                phuongSelect.innerHTML = '<option value="">Đang tải...</option>';

                try {
                    const res = await fetch(`/api/phuong-xa/${maTinh}`);
                    const data = await res.json();

                    phuongSelect.innerHTML = '<option value="">— Chọn phường/xã —</option>';
                    (data.wards || []).forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w.code;
                        opt.textContent = w.name;
                        opt.dataset.ten = w.name;
                        if (selectedMa && String(selectedMa) === String(w.code)) {
                            opt.selected = true;
                            tenPhuongHidden.value = w.name;
                        }
                        phuongSelect.appendChild(opt);
                    });
                    phuongSelect.disabled = false;
                } catch (e) {
                    phuongSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                } finally {
                    spinner.style.display = 'none';
                }
            }

            // Cập nhật hidden tenPhuongXa khi chọn phường/xã
            phuongSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                tenPhuongHidden.value = opt ? (opt.dataset.ten || opt.textContent) : '';
            });

            // Khi chọn tỉnh → load phường/xã
            tinhSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const maAPI = opt ? opt.dataset.maApi : null;
                loadPhuongXa(maAPI, null);
            });

            // Load ngay nếu có giá trị cũ (old())
            const selectedOpt = tinhSelect.options[tinhSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.maApi) {
                loadPhuongXa(selectedOpt.dataset.maApi, '{{ old('maPhuongXa') }}');
            }

            // Tự động xử lý link Google Map / Iframe
            const banDoInput = document.getElementById('banDoGoogle');
            const viDoInput = document.getElementById('viDo');
            const kinhDoInput = document.getElementById('kinhDo');

            if (banDoInput) {
                banDoInput.addEventListener('input', function() {
                    let val = this.value.trim();

                    // Nếu nhập vào là thẻ iframe, bóc tách lấy src=""
                    const iframeMatch = val.match(/<iframe\s+[^>]*src=["']([^"']+)["']/i);
                    if (iframeMatch && iframeMatch[1]) {
                        val = iframeMatch[1];
                        this.value = val;
                    }

                    // Tìm vĩ độ (!3d) và kinh độ (!2d)
                    const lngMatch = val.match(/!2d(-?\d+\.\d+)/);
                    const latMatch = val.match(/!3d(-?\d+\.\d+)/);

                    if (latMatch && latMatch[1]) {
                        viDoInput.value = latMatch[1];
                    }
                    if (lngMatch && lngMatch[1]) {
                        kinhDoInput.value = lngMatch[1];
                    }
                });
            }
        })();
    </script>
@endsection
