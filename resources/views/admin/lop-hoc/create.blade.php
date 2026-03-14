@extends('layouts.admin')

@section('title', 'Thêm lớp học mới')
@section('page-title', 'Lớp Học')
@section('breadcrumb', 'Quản lý · Lớp học · Thêm mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/form.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lop-hoc/index.css') }}">
@endsection

@section('content')
    <div class="kf-page-header">
        <div>
            <div class="kf-breadcrumb">
                <a href="{{ route('admin.lop-hoc.index') }}"><i class="fas fa-chalkboard me-1"></i> Lớp học</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Thêm mới
            </div>
            <div class="kf-page-title" style="margin-top:4px;color:#4c1d95">
                <i class="fas fa-plus-circle" style="color:#7c3aed"></i>
                Thêm lớp học mới
            </div>
        </div>
        <a href="{{ route('admin.lop-hoc.index') }}" class="kf-btn kf-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="kf-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Lỗi nhập liệu:</strong>
                <ul style="margin:4px 0 0 16px;padding:0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php
        $oldDotThus = old('dotThu', []);
    @endphp

    <form action="{{ route('admin.lop-hoc.store') }}" method="POST">
        @csrf

        <div class="kf-tabs">
            <button type="button" class="kf-tab-btn active" data-tab="tab-co-ban">
                <i class="fas fa-info-circle"></i> Thông tin cơ bản
            </button>
            <button type="button" class="kf-tab-btn" data-tab="tab-lich-hoc">
                <i class="fas fa-calendar-days"></i> Lịch học & Thời gian
            </button>
            <button type="button" class="kf-tab-btn" data-tab="tab-hoc-phi">
                <i class="fas fa-dollar-sign"></i> Học phí & Cài đặt
            </button>
        </div>

        <div class="kf-tab-panel active" id="tab-co-ban">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-info-circle"></i> Thông tin chung</div>

                <div class="kf-form-row">
                    <div class="kf-form-group" style="grid-column:1/-1">
                        <label>Tên lớp học <span class="req">*</span></label>
                        <input type="text" name="tenLopHoc" value="{{ old('tenLopHoc') }}"
                            placeholder="VD: Lớp Tiếng Anh Giao Tiếp – Sáng T2/T4/T6"
                            class="{{ $errors->has('tenLopHoc') ? 'is-invalid' : '' }}">
                        @error('tenLopHoc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Khóa học <span class="req">*</span></label>
                        <select name="khoaHocId" id="khoaHocSel"
                            class="{{ $errors->has('khoaHocId') ? 'is-invalid' : '' }}">
                            <option value="">-- Chọn khóa học --</option>
                            @foreach ($khoaHocs as $kh)
                                <option value="{{ $kh->khoaHocId }}"
                                    {{ old('khoaHocId', $selectedKhoaHocId) == $kh->khoaHocId ? 'selected' : '' }}>
                                    {{ $kh->tenKhoaHoc }}
                                </option>
                            @endforeach
                        </select>
                        @error('khoaHocId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kf-form-group">
                        <label>Ca học <span class="req">*</span></label>
                        <select name="caHocId" class="{{ $errors->has('caHocId') ? 'is-invalid' : '' }}">
                            <option value="">-- Chọn ca học --</option>
                            @foreach ($caHocs as $ca)
                                <option value="{{ $ca->caHocId }}"
                                    {{ old('caHocId') == $ca->caHocId ? 'selected' : '' }}>
                                    {{ $ca->tenCa }} ({{ $ca->gioBatDau }} – {{ $ca->gioKetThuc }})
                                </option>
                            @endforeach
                        </select>
                        @error('caHocId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-map-marker-alt"></i> Địa điểm đào tạo</div>
                <div class="form-hint" style="margin:-10px 0 16px 0">
                    Chọn địa điểm để hệ thống tự động tải danh sách Phòng học và Giáo viên phù hợp.
                </div>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Tỉnh / Thành phố <span class="req">*</span></label>
                        <select id="tinhThanhSel" onchange="loadPhuongXa(this.value)">
                            <option value="">-- Chọn tỉnh --</option>
                            @foreach ($tinhThanhs as $tt)
                                <option value="{{ $tt->tinhThanhId }}">{{ $tt->tenTinhThanh }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kf-form-group">
                        <label>Phường / Xã <span class="req">*</span></label>
                        <select id="phuongXaSel" onchange="loadCoSo()" disabled>
                            <option value="">-- Chọn tỉnh trước --</option>
                        </select>
                    </div>

                    <div class="kf-form-group">
                        <label>Cơ sở đào tạo <span class="req">*</span></label>
                        <select name="coSoId" id="coSoSel" class="{{ $errors->has('coSoId') ? 'is-invalid' : '' }}"
                            onchange="loadPhongVaGV(this.value)" disabled>
                            <option value="">-- Chọn phường/xã trước --</option>
                        </select>
                        @error('coSoId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Phòng học</label>
                        <select name="phongHocId" id="phongHocSel">
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
                    </div>
                    <div class="kf-form-group">
                        <label>Giáo viên <span class="hint-text text-muted" style="font-weight:normal;font-size:12px;">(Ưu
                                tiên thuộc cơ sở)</span></label>
                        <select name="taiKhoanId" id="giaoVienSel">
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="kf-tab-panel" id="tab-lich-hoc">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-calendar-days"></i> Lịch học trong tuần</div>
                <div class="kf-form-group">
                    <label>Thứ học trong tuần</label>
                    <div class="lich-hoc-grid" id="lichHocGrid">
                        @php
                            $thuNames = [
                                '2' => 'Thứ 2',
                                '3' => 'Thứ 3',
                                '4' => 'Thứ 4',
                                '5' => 'Thứ 5',
                                '6' => 'Thứ 6',
                                '7' => 'Thứ 7',
                                'CN' => 'Chủ nhật',
                            ];
                            $oldLich = old('lichHoc_arr', []);
                        @endphp
                        @foreach ($thuNames as $val => $label)
                            <label
                                style="{{ in_array($val, $oldLich) ? 'background:#7c3aed;border-color:#7c3aed;color:#fff' : '' }}">
                                <input type="checkbox" name="lichHoc_arr[]" value="{{ $val }}"
                                    {{ in_array($val, $oldLich) ? 'checked' : '' }} onchange="updateLichHoc()">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="lichHoc" id="lichHocInput" value="{{ old('lichHoc') }}">
                    <div class="form-hint">Chọn các ngày học trong tuần. Dùng cho tính năng tự động sinh buổi học.</div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-clock"></i> Thời gian & Số buổi</div>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Ngày bắt đầu <span class="req">*</span></label>
                        <input type="date" name="ngayBatDau" value="{{ old('ngayBatDau') }}"
                            class="{{ $errors->has('ngayBatDau') ? 'is-invalid' : '' }}" onchange="calcBuoi()">
                        @error('ngayBatDau')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="kf-form-group">
                        <label>Ngày kết thúc <span class="req">*</span></label>
                        <input type="date" name="ngayKetThuc" value="{{ old('ngayKetThuc') }}"
                            class="{{ $errors->has('ngayKetThuc') ? 'is-invalid' : '' }}" onchange="calcBuoi()">
                        @error('ngayKetThuc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="kf-form-group">
                        <label>Số buổi dự kiến</label>
                        <input type="number" name="soBuoiDuKien" id="soBuoiInput" value="{{ old('soBuoiDuKien') }}"
                            min="1" placeholder="Tự tính hoặc nhập thủ công">
                        <div class="form-hint" id="calcHint"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="kf-tab-panel" id="tab-hoc-phi">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-file-invoice-dollar"></i> Chính sách giá lớp</div>
                <p class="form-hint" style="margin:0 0 14px">
                    Học phí là giá bán của lớp. Lớp có thể lưu khi chưa có giá, nhưng phải có chính sách giá hợp lệ trước
                    khi chuyển sang trạng thái tuyển sinh hoặc đang học.
                </p>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Học phí niêm yết (VNĐ)</label>
                        <input type="number" name="hocPhiNiemYet" id="hocPhiNiemYetInput"
                            value="{{ old('hocPhiNiemYet') }}" min="0" step="1000" oninput="previewPricing()">
                    </div>
                    <div class="kf-form-group">
                        <label>Số buổi cam kết</label>
                        <input type="number" name="soBuoiCamKet" id="soBuoiCamKetInput"
                            value="{{ old('soBuoiCamKet') }}" min="1" oninput="previewPricing()">
                    </div>
                    <div class="kf-form-group">
                        <label>Loại thu</label>
                        <select name="loaiThu" id="loaiThuInput" onchange="previewPricing()">
                            @foreach ($loaiThuOptions as $value => $label)
                                <option value="{{ $value }}" {{ (string) old('loaiThu', 0) === (string) $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="kf-form-group">
                        <label>Trạng thái chính sách giá</label>
                        <select name="trangThaiChinhSachGia">
                            <option value="1" {{ old('trangThaiChinhSachGia', '1') === '1' ? 'selected' : '' }}>Đang áp dụng</option>
                            <option value="0" {{ old('trangThaiChinhSachGia') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                </div>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Hiệu lực từ</label>
                        <input type="datetime-local" name="hieuLucTu"
                            value="{{ old('hieuLucTu') ? \Carbon\Carbon::parse(old('hieuLucTu'))->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="kf-form-group">
                        <label>Hiệu lực đến</label>
                        <input type="datetime-local" name="hieuLucDen"
                            value="{{ old('hieuLucDen') ? \Carbon\Carbon::parse(old('hieuLucDen'))->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="kf-form-group" style="grid-column:1/-1">
                        <label>Ghi chú chính sách</label>
                        <textarea name="ghiChuChinhSach" rows="3"
                            placeholder="Ví dụ: học phí đã bao gồm tài liệu, chưa bao gồm lệ phí thi.">{{ old('ghiChuChinhSach') }}</textarea>
                    </div>
                </div>

                <div id="pricingPreview" style="display:none;margin-top:16px">
                    <div
                        style="background:linear-gradient(135deg,#0f766e,#14b8a6);border-radius:10px;padding:16px 20px;color:#fff;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px">
                        <div>
                            <div style="font-size:.72rem;font-weight:700;opacity:.8;text-transform:uppercase">Học phí niêm yết</div>
                            <div style="font-size:1.35rem;font-weight:700" id="prev-hocphi">—</div>
                        </div>
                        <div>
                            <div style="font-size:.72rem;font-weight:700;opacity:.8;text-transform:uppercase">Số buổi cam kết</div>
                            <div style="font-size:1.35rem;font-weight:700" id="prev-camket">—</div>
                        </div>
                        <div>
                            <div style="font-size:.72rem;font-weight:700;opacity:.8;text-transform:uppercase">Loại thu</div>
                            <div style="font-size:1.1rem;font-weight:700" id="prev-loaithu">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title" style="justify-content:space-between">
                    <span><i class="fas fa-layer-group"></i> Kế hoạch thu theo đợt</span>
                    <button type="button" class="kf-btn kf-btn-secondary" onclick="addDotThuRow()">
                        <i class="fas fa-plus"></i> Thêm đợt thu
                    </button>
                </div>
                <p class="form-hint" style="margin:0 0 14px">
                    Có thể để trống nếu lớp thu một lần. Nếu cấu hình đợt thu, tổng số tiền các đợt phải bằng học phí niêm yết.
                </p>

                <div id="dotThuRows">
                    @forelse ($oldDotThus as $index => $dotThu)
                        <div class="dot-thu-row"
                            style="display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;align-items:end;margin-bottom:10px">
                            <div>
                                <label>Tên đợt thu</label>
                                <input type="text" name="dotThu[{{ $index }}][tenDotThu]"
                                    value="{{ $dotThu['tenDotThu'] ?? '' }}" placeholder="VD: Đợt cọc giữ chỗ">
                            </div>
                            <div>
                                <label>Số tiền</label>
                                <input type="number" name="dotThu[{{ $index }}][soTien]"
                                    value="{{ $dotThu['soTien'] ?? '' }}" min="0" step="1000" oninput="previewPricing()">
                            </div>
                            <div>
                                <label>Hạn thanh toán</label>
                                <input type="date" name="dotThu[{{ $index }}][hanThanhToan]"
                                    value="{{ $dotThu['hanThanhToan'] ?? '' }}">
                            </div>
                            <label style="display:flex;align-items:center;gap:6px;margin-bottom:8px">
                                <input type="checkbox" name="dotThu[{{ $index }}][batBuoc]" value="1"
                                    {{ !empty($dotThu['batBuoc']) ? 'checked' : '' }}>
                                Bắt buộc
                            </label>
                            <button type="button" class="kf-btn kf-btn-secondary" onclick="removeDotThuRow(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="form-hint" id="dotThuEmptyHint">Chưa cấu hình đợt thu nào.</div>
                    @endforelse
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-chalkboard-teacher"></i> Chi phí giáo viên</div>
                <p class="form-hint" style="margin:0 0 14px">
                    Đây là chi phí của trung tâm, tách biệt khỏi học phí học viên.
                </p>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Đơn giá dạy (VNĐ/buổi)</label>
                        <input type="number" name="donGiaDay" id="donGiaDayInput" value="{{ old('donGiaDay') }}"
                            placeholder="VD: 150000" min="0" step="1000">
                    </div>
                    <div class="kf-form-group">
                        <label>Sĩ số học viên tối đa</label>
                        <input type="number" name="soHocVienToiDa" value="{{ old('soHocVienToiDa') }}"
                            placeholder="VD: 20" min="1">
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-sliders-h"></i> Trạng thái lớp</div>
                <div class="kf-form-group">
                    <label>Trạng thái <span class="req">*</span></label>
                    <select name="trangThai">
                        @php
                            $selectedTrangThai = (string) old('trangThai', \App\Models\Education\LopHoc::TRANG_THAI_SAP_MO);
                        @endphp
                        @foreach (\App\Models\Education\LopHoc::trangThaiOptions() as $value => $label)
                            <option value="{{ $value }}" {{ $selectedTrangThai === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="kf-action-bar">
            <a href="{{ route('admin.lop-hoc.index') }}" class="kf-btn kf-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="kf-btn kf-btn-primary"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i class="fas fa-save"></i> Lưu lớp học
            </button>
        </div>
    </form>
@endsection

@section('script')
    <script>
        let dotThuIndex = {{ count($oldDotThus) }};

        document.querySelectorAll('.kf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.kf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.kf-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        function updateLichHoc() {
            const checked = [...document.querySelectorAll('input[name="lichHoc_arr[]"]:checked')]
                .map(cb => cb.value);
            document.getElementById('lichHocInput').value = checked.join(',');
            document.querySelectorAll('#lichHocGrid label').forEach(lbl => {
                const cb = lbl.querySelector('input');
                if (cb.checked) {
                    lbl.style.background = '#7c3aed';
                    lbl.style.borderColor = '#7c3aed';
                    lbl.style.color = '#fff';
                } else {
                    lbl.style.background = '';
                    lbl.style.borderColor = '';
                    lbl.style.color = '';
                }
            });
            calcBuoi();
        }

        function calcBuoi() {
            const start = document.querySelector('[name=ngayBatDau]').value;
            const end = document.querySelector('[name=ngayKetThuc]').value;
            const lichHoc = document.getElementById('lichHocInput').value;
            if (!start || !end || !lichHoc) return;

            const thuMap = {
                '2': 1,
                '3': 2,
                '4': 3,
                '5': 4,
                '6': 5,
                '7': 6,
                'CN': 0
            };
            const thuDays = lichHoc.split(',').map(t => thuMap[t.trim()]).filter(x => x !== undefined);
            if (!thuDays.length) return;

            let d = new Date(start);
            const dEnd = new Date(end);
            let count = 0;
            while (d <= dEnd) {
                if (thuDays.includes(d.getDay())) count++;
                d.setDate(d.getDate() + 1);
            }

            document.getElementById('soBuoiInput').value = count;
            document.getElementById('calcHint').textContent = `Tính tự động: ${count} buổi học`;
        }

        async function loadPhuongXa(tinhThanhId) {
            const pSel = document.getElementById('phuongXaSel');
            const cSel = document.getElementById('coSoSel');
            pSel.innerHTML = '<option value="">Đang tải...</option>';
            pSel.disabled = true;
            cSel.innerHTML = '<option value="">-- Chọn phường/xã trước --</option>';
            cSel.disabled = true;
            if (!tinhThanhId) {
                pSel.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>';
                return;
            }
            const res = await fetch(`/admin/api/phuong-xa-co-so/${tinhThanhId}`).then(r => r.json());
            if (res.success && res.phuongXas.length) {
                pSel.innerHTML = '<option value="">-- Chọn phường/xã --</option>' +
                    res.phuongXas.map(p => `<option value="${p.maPhuongXa}">${p.tenPhuongXa}</option>`).join('');
                pSel.disabled = false;
            } else {
                pSel.innerHTML = '<option value="">Không có phường/xã nào có cơ sở</option>';
            }
        }

        async function loadCoSo() {
            const tinhId = document.getElementById('tinhThanhSel').value;
            const phuongId = document.getElementById('phuongXaSel').value;
            const cSel = document.getElementById('coSoSel');
            cSel.innerHTML = '<option value="">Đang tải...</option>';
            cSel.disabled = true;
            if (!phuongId) return;
            const params = new URLSearchParams({
                tinhThanhId: tinhId,
                maPhuongXa: phuongId
            });
            const res = await fetch(`/admin/api/co-so-by-location?${params}`).then(r => r.json());
            if (res.success && res.coSos.length) {
                cSel.innerHTML = '<option value="">-- Chọn cơ sở --</option>' +
                    res.coSos.map(c =>
                        `<option value="${c.coSoId}">${c.tenCoSo}${c.tenPhuongXa ? ' — ' + c.tenPhuongXa : ''}</option>`
                    ).join('');
                cSel.disabled = false;
            } else {
                cSel.innerHTML = '<option value="">Không tìm thấy cơ sở</option>';
            }
        }

        async function loadPhongVaGV(coSoId) {
            const phongSel = document.getElementById('phongHocSel');
            const gvSel = document.getElementById('giaoVienSel');

            phongSel.innerHTML = '<option value="">Đang tải...</option>';
            gvSel.innerHTML = '<option value="">Đang tải...</option>';

            if (!coSoId) {
                phongSel.innerHTML = '<option value="">-- Chọn cơ sở trước --</option>';
                gvSel.innerHTML = '<option value="">-- Chọn cơ sở trước --</option>';
                return;
            }

            const [phongs, gvs] = await Promise.all([
                fetch(`/api/phong-hoc/${coSoId}`).then(r => r.json()),
                fetch(`/api/giao-vien/${coSoId}`).then(r => r.json()),
            ]);

            phongSel.innerHTML = '<option value="">-- Chọn phòng (tùy chọn) --</option>' +
                phongs.map(p =>
                    `<option value="${p.phongHocId}" data-suc-chua="${p.sucChua}">
                        ${p.tenPhong} (sức chứa: ${p.sucChua} chỗ)
                    </option>`
                ).join('');

            let gvHtml = '<option value="">-- Chọn giáo viên (tùy chọn) --</option>';
            if (gvs.cung_co_so && gvs.cung_co_so.length > 0) {
                gvHtml += '<optgroup label="Giáo viên thuộc cơ sở này">';
                gvHtml += gvs.cung_co_so.map(g => `<option value="${g.taiKhoanId}">${g.hoTen}</option>`).join('');
                gvHtml += '</optgroup>';
            }
            if (gvs.khac_co_so && gvs.khac_co_so.length > 0) {
                gvHtml += '<optgroup label="Giáo viên cơ sở khác">';
                gvHtml += gvs.khac_co_so.map(g => `<option value="${g.taiKhoanId}">${g.hoTen}</option>`).join('');
                gvHtml += '</optgroup>';
            }
            gvSel.innerHTML = gvHtml;
        }

        function updateSucChuaHint() {
            const ps = document.getElementById('phongHocSel');
            const opt = ps.options[ps.selectedIndex];
            const sucChua = opt ? parseInt(opt.dataset.sucChua || 0) : 0;
            const siSoInput = document.querySelector('[name="soHocVienToiDa"]');
            let hint = document.getElementById('sucChuaHint');
            if (!hint) {
                hint = document.createElement('div');
                hint.id = 'sucChuaHint';
                hint.style.cssText = 'font-size:.78rem;margin-top:4px';
                siSoInput?.parentNode?.appendChild(hint);
            }
            if (sucChua > 0) {
                siSoInput?.setAttribute('max', sucChua);
                hint.style.color = '#7c3aed';
                hint.innerHTML =
                    `<i class="fas fa-info-circle me-1"></i> Phòng đã chọn sức chứa <strong>${sucChua}</strong> chỗ. Sĩ số tối đa không được vượt quá.`;
            } else {
                siSoInput?.removeAttribute('max');
                hint.innerHTML = '';
            }
        }

        document.getElementById('phongHocSel')?.addEventListener('change', updateSucChuaHint);

        document.querySelector('form')?.addEventListener('submit', function(e) {
            const ps = document.getElementById('phongHocSel');
            const opt = ps?.options[ps.selectedIndex];
            const sucChua = opt ? parseInt(opt.dataset.sucChua || 0) : 0;
            const siSoInput = document.querySelector('[name="soHocVienToiDa"]');
            const siSo = parseInt(siSoInput?.value || 0);
            if (sucChua > 0 && siSo > sucChua) {
                e.preventDefault();
                alert(`Sĩ số tối đa (${siSo}) không được vượt sức chứa phòng học (${sucChua} chỗ).`);
                siSoInput.focus();
            }
        });

        function fmtMoney(n) {
            return Number(n || 0).toLocaleString('vi-VN') + ' đ';
        }

        function previewPricing() {
            const hocPhi = parseFloat(document.getElementById('hocPhiNiemYetInput')?.value || 0);
            const soBuoiCamKet = document.getElementById('soBuoiCamKetInput')?.value || '';
            const loaiThuSelect = document.getElementById('loaiThuInput');
            const preview = document.getElementById('pricingPreview');
            const dotThuEmptyHint = document.getElementById('dotThuEmptyHint');
            const rowCount = document.querySelectorAll('.dot-thu-row').length;

            if (dotThuEmptyHint) {
                dotThuEmptyHint.style.display = rowCount > 0 ? 'none' : 'block';
            }

            if (hocPhi <= 0 && !soBuoiCamKet) {
                preview.style.display = 'none';
                return;
            }

            document.getElementById('prev-hocphi').textContent = hocPhi > 0 ? fmtMoney(hocPhi) : 'Chưa nhập';
            document.getElementById('prev-camket').textContent = soBuoiCamKet ? `${soBuoiCamKet} buổi` : 'Không ràng buộc';
            document.getElementById('prev-loaithu').textContent = loaiThuSelect?.options[loaiThuSelect.selectedIndex]?.text || '—';
            preview.style.display = 'block';
        }

        function addDotThuRow() {
            const container = document.getElementById('dotThuRows');
            const emptyHint = document.getElementById('dotThuEmptyHint');
            if (emptyHint) {
                emptyHint.style.display = 'none';
            }
            const row = document.createElement('div');
            row.className = 'dot-thu-row';
            row.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;align-items:end;margin-bottom:10px';
            row.innerHTML = `
                <div>
                    <label>Tên đợt thu</label>
                    <input type="text" name="dotThu[${dotThuIndex}][tenDotThu]" placeholder="VD: Đợt 1 giữ chỗ">
                </div>
                <div>
                    <label>Số tiền</label>
                    <input type="number" name="dotThu[${dotThuIndex}][soTien]" min="0" step="1000" oninput="previewPricing()">
                </div>
                <div>
                    <label>Hạn thanh toán</label>
                    <input type="date" name="dotThu[${dotThuIndex}][hanThanhToan]">
                </div>
                <label style="display:flex;align-items:center;gap:6px;margin-bottom:8px">
                    <input type="checkbox" name="dotThu[${dotThuIndex}][batBuoc]" value="1" checked>
                    Bắt buộc
                </label>
                <button type="button" class="kf-btn kf-btn-secondary" onclick="removeDotThuRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(row);
            dotThuIndex += 1;
            previewPricing();
        }

        function removeDotThuRow(button) {
            button.closest('.dot-thu-row')?.remove();
            previewPricing();
        }

        document.addEventListener('DOMContentLoaded', () => {
            previewPricing();
            updateLichHoc();
        });
    </script>
@endsection
