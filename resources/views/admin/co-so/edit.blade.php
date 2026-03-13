@extends('layouts.admin')

@section('title', 'Sửa ' . $coSo->tenCoSo)
@section('page-title', 'Cơ sở Đào tạo')
@section('breadcrumb', 'Cấu hình hệ thống · Cơ sở & Phòng học · Cập nhật')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/co-so/edit.css') }}">
@endsection

@section('content')

    <div class="cs-form-wrap">
        <div class="cs-form-header">
            <div class="cs-form-title">
                <i class="fas fa-pen-to-square" style="color:#f59e0b;"></i> Cập nhật thông tin: {{ $coSo->tenCoSo }}
            </div>
            <a href="{{ route('admin.co-so.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Danh sách</a>
        </div>

        <form action="{{ route('admin.co-so.update', $coSo->coSoId) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="form-grid">
                {{-- Mã cơ sở --}}
                <div class="form-group">
                    <label class="form-label" for="maCoSo">Mã cơ sở <span class="req">*</span></label>
                    <input type="text" id="maCoSo" name="maCoSo"
                        class="form-control @error('maCoSo') is-invalid @enderror"
                        value="{{ old('maCoSo', $coSo->maCoSo) }}" placeholder="VD: CS01">
                    @error('maCoSo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Tên cơ sở --}}
                <div class="form-group">
                    <label class="form-label" for="tenCoSo">Tên cơ sở <span class="req">*</span></label>
                    <input type="text" id="tenCoSo" name="tenCoSo"
                        class="form-control @error('tenCoSo') is-invalid @enderror"
                        value="{{ old('tenCoSo', $coSo->tenCoSo) }}" placeholder="Tên cơ sở chi nhánh...">
                    @error('tenCoSo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Số điện thoại --}}
                <div class="form-group">
                    <label class="form-label" for="soDienThoai">Số điện thoại</label>
                    <input type="text" id="soDienThoai" name="soDienThoai"
                        class="form-control @error('soDienThoai') is-invalid @enderror"
                        value="{{ old('soDienThoai', $coSo->soDienThoai) }}" placeholder="Số hotline/điện thoại bàn">
                    @error('soDienThoai')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email liên hệ</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $coSo->email) }}"
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
                                {{ old('tinhThanhId', $coSo->tinhThanhId) == $tinh->tinhThanhId ? 'selected' : '' }}>
                                {{ $tinh->tenTinhThanh }}
                            </option>
                        @endforeach
                    </select>
                    @error('tinhThanhId')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Phường / Xã --}}
                <div class="form-group">
                    <label class="form-label" for="maPhuongXa">Phường / Xã</label>
                    <div class="select-loading-wrap">
                        <select id="maPhuongXa" name="maPhuongXa"
                            class="form-control @error('maPhuongXa') is-invalid @enderror" disabled>
                            <option value="">— Đang tải... —</option>
                        </select>
                        <span class="select-spinner" id="phuongXaSpinner" style="display:none;">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </span>
                    </div>
                    <input type="hidden" id="tenPhuongXa" name="tenPhuongXa"
                        value="{{ old('tenPhuongXa', $coSo->tenPhuongXa) }}">
                    @error('maPhuongXa')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Địa chỉ chi tiết --}}
                <div class="form-group full">
                    <label class="form-label" for="diaChi">Địa chỉ chi tiết <span class="req">*</span></label>
                    <input type="text" id="diaChi" name="diaChi"
                        class="form-control @error('diaChi') is-invalid @enderror"
                        value="{{ old('diaChi', $coSo->diaChi) }}" placeholder="Số nhà, tên đường...">
                    @error('diaChi')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- ════ TỌA ĐỘ ════ --}}
                <div class="form-group">
                    <label class="form-label" for="viDo">
                        Vĩ độ (Latitude)
                        <a href="https://maps.google.com" target="_blank" class="hint-link"><i
                                class="fas fa-map-pin"></i> Google Maps</a>
                    </label>
                    <input type="number" step="any" id="viDo" name="viDo"
                        class="form-control @error('viDo') is-invalid @enderror" value="{{ old('viDo', $coSo->viDo) }}"
                        placeholder="VD: 10.776889" autocomplete="off">
                    @error('viDo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="kinhDo">Kinh độ (Longitude)</label>
                    <input type="number" step="any" id="kinhDo" name="kinhDo"
                        class="form-control @error('kinhDo') is-invalid @enderror"
                        value="{{ old('kinhDo', $coSo->kinhDo) }}" placeholder="VD: 106.700980" autocomplete="off">
                    @error('kinhDo')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Ngày khai trương --}}
                <div class="form-group">
                    <label class="form-label" for="ngayKhaiTruong">Ngày khai trương</label>
                    <input type="date" id="ngayKhaiTruong" name="ngayKhaiTruong"
                        class="form-control @error('ngayKhaiTruong') is-invalid @enderror"
                        value="{{ old('ngayKhaiTruong', optional($coSo->ngayKhaiTruong)->format('Y-m-d')) }}">
                    @error('ngayKhaiTruong')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Trạng thái --}}
                <div class="form-group">
                    <label class="form-label" for="trangThai">Trạng thái hoạt động <span class="req">*</span></label>
                    <select id="trangThai" name="trangThai"
                        class="form-control @error('trangThai') is-invalid @enderror">
                        <option value="1" {{ old('trangThai', $coSo->trangThai) == '1' ? 'selected' : '' }}>Đang hoạt
                            động</option>
                        <option value="0" {{ old('trangThai', $coSo->trangThai) == '0' ? 'selected' : '' }}>Tạm ngưng
                        </option>
                    </select>
                    @error('trangThai')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Google Map iframe --}}
                <div class="form-group full">
                    <label class="form-label" for="banDoGoogle">Link / Mã nhúng Google Map</label>
                    <textarea id="banDoGoogle" name="banDoGoogle" class="form-control @error('banDoGoogle') is-invalid @enderror"
                        rows="3" placeholder="Nhập link Google Map hoặc phần src của iframe...">{{ old('banDoGoogle', $coSo->banDoGoogle) }}</textarea>
                    <div class="map-hint">Sẽ dùng thẻ iframe để nhúng bản đồ trên website. Tọa độ vĩ/kinh độ dùng để hiển
                        thị marker trên bản đồ trang liên hệ.</div>
                    @error('banDoGoogle')
                        <div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full" style="text-align: right; margin-top:10px;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Lưu cập nhật</button>
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

            // Giá trị đang được chọn (preload)
            const currentMaPhuongXa = "{{ old('maPhuongXa', $coSo->maPhuongXa) }}";

            async function loadPhuongXa(maTinh, selectedMa) {
                if (!maTinh) {
                    phuongSelect.innerHTML = '<option value="">— Chọn tỉnh trước —</option>';
                    phuongSelect.disabled = true;
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

            phuongSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                tenPhuongHidden.value = opt ? (opt.dataset.ten || opt.textContent) : '';
            });

            tinhSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const maAPI = opt ? opt.dataset.maApi : null;
                loadPhuongXa(maAPI, null);
            });

            // Tự động load khi trang mở với tỉnh đang được chọn
            const selectedOpt = tinhSelect.options[tinhSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.maApi) {
                loadPhuongXa(selectedOpt.dataset.maApi, currentMaPhuongXa);
            }

            // ── Tự động xử lý link Google Map / Iframe ──────────────────────────
            const banDoInput = document.getElementById('banDoGoogle');
            const viDoInput = document.getElementById('viDo');
            const kinhDoInput = document.getElementById('kinhDo');

            function parseLatLng(rawVal) {
                let val = rawVal.trim();
                const iframeMatch = val.match(/<iframe\s+[^>]*src=["']([^"']+)["']/i);
                if (iframeMatch && iframeMatch[1]) {
                    val = iframeMatch[1];
                    banDoInput.value = val;
                }
                // Reset
                viDoInput.value = '';
                kinhDoInput.value = '';
                // Dạng 1: !2d<lng>!3d<lat>
                const lngM = val.match(/!2d(-?\d+\.?\d*)/);
                const latM = val.match(/!3d(-?\d+\.?\d*)/);
                if (latM && lngM) {
                    viDoInput.value = latM[1];
                    kinhDoInput.value = lngM[1];
                    return;
                }
                // Dạng 2: @lat,lng
                const atM = val.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
                if (atM) {
                    viDoInput.value = atM[1];
                    kinhDoInput.value = atM[2];
                    return;
                }
                // Dạng 3: ?q=lat,lng
                const qM = val.match(/[?&](?:q|ll)=(-?\d+\.?\d*),(-?\d+\.?\d*)/);
                if (qM) {
                    viDoInput.value = qM[1];
                    kinhDoInput.value = qM[2];
                }
            }

            if (banDoInput) {
                banDoInput.addEventListener('input', () => parseLatLng(banDoInput.value));
                banDoInput.addEventListener('paste', () => setTimeout(() => parseLatLng(banDoInput.value), 50));
            }
        })();
    </script>
@endsection
