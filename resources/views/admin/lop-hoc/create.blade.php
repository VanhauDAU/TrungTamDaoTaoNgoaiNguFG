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

    <form action="{{ route('admin.lop-hoc.store') }}" method="POST">
        @csrf

        {{-- ── Tabs ──────────────────────────────────────────────── --}}
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

        {{-- ── Tab 1: Thông tin cơ bản ──────────────────────────── --}}
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

                    <div class="kf-form-group">
                        <label>Giáo viên</label>
                        <select name="taiKhoanId" id="giaoVienSel">
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
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
                    <div class="kf-form-group" style="max-width:33%">
                        <label>Phòng học</label>
                        <select name="phongHocId" id="phongHocSel">
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tab 2: Lịch học & Thời gian ──────────────────────── --}}
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

        {{-- ── Tab 3: Học phí & Cài đặt ────────────────────────────────── --}}
        <div class="kf-tab-panel" id="tab-hoc-phi">
            {{-- Gói học phí --}}
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-file-invoice-dollar"></i> Gói học phí học viên</div>
                <p class="form-hint" style="margin:0 0 14px">
                    Chọn gói học phí áp dụng cho lớp học này. Mỗi gói định nghĩa số buổi và đơn giá/buổi mà học viên phải
                    nộp.
                </p>

                <div class="kf-form-row">
                    <div class="kf-form-group" style="grid-column:1/-1">
                        <label>Chọn gói học phí</label>
                        <select name="hocPhiId" id="hocPhiSel" onchange="previewHocPhi()">
                            <option value="">-- Chọn khóa học trước (Tab 1) --</option>
                            @foreach ($hocPhis as $hp)
                                <option value="{{ $hp->hocPhiId }}" data-sobuoi="{{ $hp->soBuoi }}"
                                    data-dongia="{{ $hp->donGia }}" data-tong="{{ $hp->tongHocPhi }}"
                                    {{ old('hocPhiId') == $hp->hocPhiId ? 'selected' : '' }}>
                                    Gói {{ $hp->soBuoi }} buổi &ndash; {{ number_format($hp->donGia, 0, ',', '.') }}
                                    đ/buổi
                                    &rarr; Tổng: {{ number_format($hp->tongHocPhi, 0, ',', '.') }} đ
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Danh sách gói sẽ cập nhật khi bạn chọn khóa học (AJAX).</div>
                    </div>
                </div>

                {{-- Preview card --}}
                <div id="hocPhiPreview" style="display:none;margin-top:16px">
                    <div
                        style="background:linear-gradient(135deg,#7c3aed,#a78bfa);border-radius:10px;padding:16px 20px;color:#fff;display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px">
                        <div>
                            <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Số buổi gói
                            </div>
                            <div style="font-size:1.4rem;font-weight:700" id="prev-sobuoi">—</div>
                        </div>
                        <div>
                            <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Đơn giá/buổi
                            </div>
                            <div style="font-size:1.4rem;font-weight:700" id="prev-dongia">—</div>
                        </div>
                        <div>
                            <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Tổng học phí
                                HV</div>
                            <div style="font-size:1.4rem;font-weight:700;color:#fde68a" id="prev-tong">—</div>
                        </div>
                    </div>
                    {{-- Cảnh báo lệch số buổi --}}
                    <div id="hp-mismatch-warn"
                        style="display:none;margin-top:10px;padding:10px 14px;border-radius:8px;font-size:.82rem"></div>
                </div>
            </div>

            {{-- Chi phí giáo viên --}}
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-chalkboard-teacher"></i> Chi phí giáo viên</div>
                <p class="form-hint" style="margin:0 0 14px">
                    ĐÂY LÀ CHI PHÍ CỦA TRUNG TÂM (khác với học phí học viên ở trên).
                </p>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Đơn giá dạy (VNĐ/buổi)</label>
                        <input type="number" name="donGiaDay" id="donGiaDayInput" value="{{ old('donGiaDay') }}"
                            placeholder="VD: 150000" min="0" step="1000" onchange="previewHocPhi()">
                        <div class="form-hint">Số tiền trả giáo viên mỗi buổi dạy.</div>
                    </div>
                    <div class="kf-form-group">
                        <label>Sĩ số học viên tối đa</label>
                        <input type="number" name="soHocVienToiDa" value="{{ old('soHocVienToiDa') }}"
                            placeholder="VD: 20" min="1">
                    </div>
                </div>
            </div>

            {{-- Trạng thái --}}
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-sliders-h"></i> Trạng thái lớp</div>
                <div class="kf-form-group">
                    <label>Trạng thái <span class="req">*</span></label>
                    <select name="trangThai">
                        <option value="0" {{ old('trangThai', '0') == '0' ? 'selected' : '' }}>Sắp mở</option>
                        <option value="1" {{ old('trangThai') == '1' ? 'selected' : '' }}>Đang mở đăng ký</option>
                        <option value="4" {{ old('trangThai') == '4' ? 'selected' : '' }}>Đang học</option>
                        <option value="2" {{ old('trangThai') == '2' ? 'selected' : '' }}>Đã đóng</option>
                        <option value="3" {{ old('trangThai') == '3' ? 'selected' : '' }}>Đã hủy</option>
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
        // ── Tab switching ──────────────────────────────────
        document.querySelectorAll('.kf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.kf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.kf-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        // ── Lịch học checkboxes → hidden input ────────────
        function updateLichHoc() {
            const checked = [...document.querySelectorAll('input[name="lichHoc_arr[]"]:checked')]
                .map(cb => cb.value);
            document.getElementById('lichHocInput').value = checked.join(',');
            // Style active
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

        // ── Tính số buổi dự kiến ──────────────────────────
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

        // ── AJAX: Cascade Tỉnh → Phường/Xã → Cơ sở ─────
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

        // ── AJAX: Phòng học & Giáo viên theo cơ sở ───────
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

            gvSel.innerHTML = '<option value="">-- Chọn giáo viên (tùy chọn) --</option>' +
                gvs.map(g => `<option value="${g.taiKhoanId}">${g.hoTen}</option>`).join('');
        }

        // ── Validation sĩ số ≤ sức chứa phòng ──────────────────
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

        // Client-side guard trước khi submit
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

        // ── AJAX: HocPhi theo khóa học ──────────────────
        async function loadHocPhi(khoaHocId) {
            const sel = document.getElementById('hocPhiSel');
            sel.innerHTML = '<option value="">Đang tải...</option>';
            if (!khoaHocId) {
                sel.innerHTML = '<option value="">-- Chọn khóa học trước --</option>';
                return;
            }
            const data = await fetch(`/admin/api/hoc-phi/${khoaHocId}`).then(r => r.json());
            sel.innerHTML = '<option value="">-- Chọn gói học phí --</option>' +
                data.map(hp => `<option value="${hp.hocPhiId}"
                    data-sobuoi="${hp.soBuoi}" data-dongia="${hp.donGia}" data-tong="${hp.tongHocPhi}">
                    Gói ${hp.soBuoi} buổi – ${fmtMoney(hp.donGia)}/buổi → Tổng: ${fmtMoney(hp.tongHocPhi)}
                </option>`).join('');
            document.getElementById('hocPhiPreview').style.display = 'none';
        }

        // Wire khoaHocSel to also load HocPhi
        document.getElementById('khoaHocSel').addEventListener('change', function() {
            loadHocPhi(this.value);
        });
        const oldKhoa = '{{ old('khoaHocId', $selectedKhoaHocId ?? '') }}';
        if (oldKhoa) loadHocPhi(oldKhoa);

        // Re-check mismatch whenever soBuoiDuKien changes
        document.getElementById('soBuoiInput')?.addEventListener('input', previewHocPhi);

        // ── Live preview học phí ─────────────────────────
        function fmtMoney(n) {
            return Number(n).toLocaleString('vi-VN') + ' đ';
        }

        function previewHocPhi() {
            const sel = document.getElementById('hocPhiSel');
            const opt = sel.options[sel.selectedIndex];
            const preview = document.getElementById('hocPhiPreview');
            if (!opt || !opt.value) {
                preview.style.display = 'none';
                return;
            }
            const soBuoiGoi = parseInt(opt.dataset.sobuoi) || 0;
            const donGia = parseFloat(opt.dataset.dongia) || 0;
            const tong = parseFloat(opt.dataset.tong) || soBuoiGoi * donGia;
            const donGiaDay = parseFloat(document.getElementById('donGiaDayInput')?.value || 0);
            const chiPhiGV = soBuoiGoi * donGiaDay;
            const diff = tong - chiPhiGV;
            document.getElementById('prev-sobuoi').textContent = soBuoiGoi + ' buổi';
            document.getElementById('prev-dongia').textContent = fmtMoney(donGia);
            document.getElementById('prev-tong').textContent = fmtMoney(tong);
            document.getElementById('prev-diff').textContent =
                (diff >= 0 ? '+' : '') + fmtMoney(diff);
            document.getElementById('prev-diff').style.color = diff >= 0 ? '#a7f3d0' : '#fca5a5';
            preview.style.display = 'block';

            // ── Cảnh báo lệch số buổi ─────────────────────
            const soBuoiLop = parseInt(document.getElementById('soBuoiInput')?.value || 0);
            const warn = document.getElementById('hp-mismatch-warn');
            if (soBuoiLop > 0 && soBuoiGoi !== soBuoiLop) {
                const isMore = soBuoiGoi > soBuoiLop;
                warn.style.display = 'block';
                warn.style.background = isMore ? '#fef3c7' : '#fff7ed';
                warn.style.border = isMore ? '1px solid #fcd34d' : '1px solid #fdba74';
                warn.style.color = isMore ? '#92400e' : '#c2410c';
                warn.innerHTML =
                    `<i class="fas fa-info-circle me-1"></i>
                    <strong>Lưu ý:</strong> Gói học phí có <strong>${soBuoiGoi} buổi</strong>,
                    nhưng lớp học dự kiến <strong>${soBuoiLop} buổi</strong>.
                    ${isMore
                        ? 'Học viên mua nhiều hơn số buổi thực tế của lớp — kiểm tra lại.'
                        : 'Lớp có nhiều buổi hơn gói mua — học viên sẽ thiếu buổi nếu mua đúng gói này.'
                    }
                    <br><small style="opacity:.8">Hai con số này độc lập: số buổi lớp tính từ lịch học, số buổi gói là số HV mua.</small>`;
            } else {
                warn.style.display = 'none';
            }
        }
    </script>
@endsection
