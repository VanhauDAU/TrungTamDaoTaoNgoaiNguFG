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
                            class="{{ $errors->has('ngayBatDau') ? 'is-invalid' : '' }}">
                        @error('ngayBatDau')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="kf-form-group">
                        <label>Ngày kết thúc</label>
                        <input type="text" value="Tự cập nhật theo buổi học cuối cùng" readonly>
                        <div class="form-hint">Không cần nhập tay. Hệ thống sẽ cập nhật sau khi thêm buổi học.</div>
                    </div>
                    <div class="kf-form-group">
                        <label>Số buổi dự kiến</label>
                        <input type="number" name="soBuoiDuKien" id="soBuoiInput" value="{{ old('soBuoiDuKien') }}"
                            min="1" placeholder="VD: 24">
                        <div class="form-hint">Dùng cho kế hoạch đào tạo và tính năng tự động sinh buổi học.</div>
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

            <div class="kf-card dot-thu-section" id="dotThuSection">
                <div class="kf-card-title" style="justify-content:space-between">
                    <span><i class="fas fa-layer-group"></i> Kế hoạch thu theo đợt</span>
                    <button type="button" class="kf-btn kf-btn-secondary" id="addDotThuBtn" onclick="addDotThuRow()">
                        <i class="fas fa-plus"></i> Thêm đợt thu
                    </button>
                </div>
                <p class="form-hint" style="margin:0 0 10px">
                    Có thể để trống nếu lớp thu một lần. Nếu cấu hình đợt thu, tổng số tiền các đợt phải bằng học phí niêm yết.
                </p>
                <div class="dot-thu-toolbar">
                    <p class="dot-thu-mode-hint" id="dotThuModeHint">
                        Chọn loại thu "Theo đợt" để mở cấu hình kế hoạch thu.
                    </p>
                </div>
                <div class="dot-thu-summary" id="dotThuSummary">
                    <div class="dot-thu-summary-card">
                        <div class="dot-thu-summary-label">Tong dot thu</div>
                        <div class="dot-thu-summary-value" id="dotThuTotalValue">0 đ</div>
                    </div>
                    <div class="dot-thu-summary-card">
                        <div class="dot-thu-summary-label">Chenhlech voi hoc phi</div>
                        <div class="dot-thu-summary-value" id="dotThuDeltaValue">0 đ</div>
                    </div>
                    <div class="dot-thu-summary-status" id="dotThuStatusCard">
                        <div class="dot-thu-summary-label">Trang thai kiem tra</div>
                        <div class="dot-thu-summary-value" id="dotThuStatusValue">Chua ap dung</div>
                        <div class="dot-thu-summary-note" id="dotThuStatusNote">Khi chon Theo đợt, tổng các đợt thu phải khớp học phí niêm yết và hạn thanh toán phải tăng dần.</div>
                    </div>
                </div>

                <div id="dotThuRows" class="dot-thu-list">
                    @forelse ($oldDotThus as $index => $dotThu)
                        <div class="dot-thu-row">
                            <div class="dot-thu-field dot-thu-field--name">
                                <label>Tên đợt thu</label>
                                <input type="text" name="dotThu[{{ $index }}][tenDotThu]"
                                    value="{{ $dotThu['tenDotThu'] ?? '' }}" placeholder="VD: Đợt cọc giữ chỗ">
                            </div>
                            <div class="dot-thu-field">
                                <label>Số tiền</label>
                                <input type="number" name="dotThu[{{ $index }}][soTien]"
                                    value="{{ $dotThu['soTien'] ?? '' }}" min="0" step="1000" oninput="previewPricing()">
                            </div>
                            <div class="dot-thu-field">
                                <label>Hạn thanh toán</label>
                                <input type="date" name="dotThu[{{ $index }}][hanThanhToan]"
                                    value="{{ $dotThu['hanThanhToan'] ?? '' }}">
                            </div>
                            <div class="dot-thu-meta">
                                <label class="dot-thu-check">
                                    <input type="checkbox" name="dotThu[{{ $index }}][batBuoc]" value="1"
                                        {{ !empty($dotThu['batBuoc']) ? 'checked' : '' }}>
                                    <span>Bắt buộc</span>
                                </label>
                                <button type="button" class="kf-btn kf-btn-secondary dot-thu-remove"
                                    onclick="removeDotThuRow(this)" aria-label="Xóa đợt thu">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="form-hint dot-thu-empty" id="dotThuEmptyHint">Chưa cấu hình đợt thu nào.</div>
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
        const LOAI_THU_THEO_DOT = {{ \App\Models\Education\LopHocChinhSachGia::LOAI_THU_THEO_DOT }};

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

        function parseMoneyInputValue(value) {
            const normalized = String(value || '').trim().replace(',', '.');
            const parsed = Number.parseFloat(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function getDotThuRows() {
            return [...document.querySelectorAll('.dot-thu-row')];
        }

        function isTheoDotSelected() {
            return String(document.getElementById('loaiThuInput')?.value || '') === String(LOAI_THU_THEO_DOT);
        }

        function getDateOnlyValue(value) {
            return value ? String(value).slice(0, 10) : '';
        }

        function getMaxDateValue(dateA, dateB) {
            if (!dateA) return dateB;
            if (!dateB) return dateA;
            return dateA > dateB ? dateA : dateB;
        }

        function markRowError(row, hasError) {
            row.classList.toggle('has-error', hasError);
        }

        function toggleDotThuMode() {
            const isTheoDot = isTheoDotSelected();
            const section = document.getElementById('dotThuSection');
            const addBtn = document.getElementById('addDotThuBtn');
            const modeHint = document.getElementById('dotThuModeHint');

            section?.classList.toggle('is-disabled', !isTheoDot);

            if (addBtn) {
                addBtn.disabled = !isTheoDot;
            }

            if (modeHint) {
                if (isTheoDot) {
                    modeHint.textContent = 'Loai thu dang la Theo đợt. Moi đợt thu phai co han thanh toan tang dan va tong tien phai bang hoc phi niem yet.';
                    modeHint.classList.remove('is-warning');
                } else {
                    modeHint.textContent = 'Loai thu hien tai khong dung ke hoach thu theo dot. Chuyen sang "Theo đợt" neu muon cau hinh cac dot thu.';
                    modeHint.classList.add('is-warning');
                }
            }

            getDotThuRows().forEach((row) => {
                row.querySelectorAll('input, button').forEach((element) => {
                    if (element.classList.contains('dot-thu-remove')) {
                        element.disabled = !isTheoDot;
                        return;
                    }

                    element.disabled = !isTheoDot;
                });
            });
        }

        function updateDotThuConstraints() {
            const isTheoDot = isTheoDotSelected();
            const effectiveFrom = getDateOnlyValue(document.querySelector('[name="hieuLucTu"]')?.value || '');
            const effectiveTo = getDateOnlyValue(document.querySelector('[name="hieuLucDen"]')?.value || '');
            let previousDueDate = '';

            getDotThuRows().forEach((row, index) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                const dateInput = row.querySelector('input[name*="[hanThanhToan]"]');
                let hasError = false;

                if (amountInput) {
                    amountInput.setCustomValidity('');
                    if (isTheoDot && parseMoneyInputValue(amountInput.value) <= 0) {
                        amountInput.setCustomValidity('Số tiền đợt thu phải lớn hơn 0.');
                        hasError = true;
                    }
                }

                if (dateInput) {
                    const minDate = getMaxDateValue(effectiveFrom, previousDueDate);
                    dateInput.min = minDate || '';
                    dateInput.max = effectiveTo || '';
                    dateInput.setCustomValidity('');

                    if (isTheoDot && !dateInput.value) {
                        dateInput.setCustomValidity('Vui lòng chọn hạn thanh toán cho đợt thu này.');
                        hasError = true;
                    }

                    if (isTheoDot && dateInput.value) {
                        if (minDate && dateInput.value < minDate) {
                            dateInput.setCustomValidity(index === 0
                                ? 'Hạn thanh toán không được sớm hơn hiệu lực từ của chính sách giá.'
                                : 'Hạn thanh toán các đợt phải tăng dần theo thứ tự.');
                            hasError = true;
                        } else if (effectiveTo && dateInput.value > effectiveTo) {
                            dateInput.setCustomValidity('Hạn thanh toán không được muộn hơn hiệu lực đến của chính sách giá.');
                            hasError = true;
                        }
                    }

                    if (dateInput.value) {
                        previousDueDate = dateInput.value;
                    }
                }

                markRowError(row, isTheoDot && hasError);
            });
        }

        function updateDotThuSummary() {
            const hocPhi = parseMoneyInputValue(document.getElementById('hocPhiNiemYetInput')?.value || 0);
            const isTheoDot = isTheoDotSelected();
            const total = getDotThuRows().reduce((sum, row) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                return sum + parseMoneyInputValue(amountInput?.value || 0);
            }, 0);
            const delta = hocPhi - total;
            const hasRows = getDotThuRows().length > 0;
            const hasConstraintError = getDotThuRows().some((row) =>
                row.querySelector('input[name*="[soTien]"]')?.validationMessage
                || row.querySelector('input[name*="[hanThanhToan]"]')?.validationMessage
            );

            const totalValue = document.getElementById('dotThuTotalValue');
            const deltaValue = document.getElementById('dotThuDeltaValue');
            const statusCard = document.getElementById('dotThuStatusCard');
            const statusValue = document.getElementById('dotThuStatusValue');
            const statusNote = document.getElementById('dotThuStatusNote');

            if (totalValue) totalValue.textContent = fmtMoney(total);
            if (deltaValue) deltaValue.textContent = fmtMoney(delta);

            statusCard?.classList.remove('is-valid', 'is-invalid');

            if (!isTheoDot) {
                statusValue.textContent = 'Khong ap dung';
                statusNote.textContent = 'Loai thu hien tai khong su dung cac dot thu. Neu luu o che do nay, du lieu dot thu se khong duoc ap dung.';
                return;
            }

            if (!hasRows) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = 'Thieu dot thu';
                statusNote.textContent = 'Loai thu Theo đợt bat buoc phai co it nhat mot dot thu.';
                return;
            }

            if (hasConstraintError) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = 'Can sua lich thu';
                statusNote.textContent = 'Kiem tra lai han thanh toan va so tien cua tung dot. Han cua dot sau khong duoc som hon dot truoc.';
                return;
            }

            if (Math.abs(delta) > 0.009) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = delta > 0 ? 'Chua du tong tien' : 'Vuot hoc phi';
                statusNote.textContent = 'Tong cac dot thu phai bang dung hoc phi niem yet cua lop.';
                return;
            }

            statusCard?.classList.add('is-valid');
            statusValue.textContent = 'Hop le';
            statusNote.textContent = 'Tong tien va thu tu han thanh toan cua cac dot thu da hop le.';
        }

        function validateDotThuBeforeSubmit() {
            const isTheoDot = isTheoDotSelected();

            if (!isTheoDot) {
                return true;
            }

            const rows = getDotThuRows();
            if (!rows.length) {
                alert('Loại thu Theo đợt phải có ít nhất một đợt thu.');
                return false;
            }

            for (const row of rows) {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                const dateInput = row.querySelector('input[name*="[hanThanhToan]"]');

                if ((amountInput && !amountInput.reportValidity()) || (dateInput && !dateInput.reportValidity())) {
                    return false;
                }
            }

            const hocPhi = parseMoneyInputValue(document.getElementById('hocPhiNiemYetInput')?.value || 0);
            const total = rows.reduce((sum, row) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                return sum + parseMoneyInputValue(amountInput?.value || 0);
            }, 0);

            if (Math.abs(hocPhi - total) > 0.009) {
                alert('Tổng các đợt thu phải bằng đúng học phí niêm yết của lớp.');
                return false;
            }

            return true;
        }

        function previewPricing() {
            const hocPhi = parseMoneyInputValue(document.getElementById('hocPhiNiemYetInput')?.value || 0);
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
                toggleDotThuMode();
                updateDotThuConstraints();
                updateDotThuSummary();
                return;
            }

            document.getElementById('prev-hocphi').textContent = hocPhi > 0 ? fmtMoney(hocPhi) : 'Chưa nhập';
            document.getElementById('prev-camket').textContent = soBuoiCamKet ? `${soBuoiCamKet} buổi` : 'Không ràng buộc';
            document.getElementById('prev-loaithu').textContent = loaiThuSelect?.options[loaiThuSelect.selectedIndex]?.text || '—';
            preview.style.display = 'block';
            toggleDotThuMode();
            updateDotThuConstraints();
            updateDotThuSummary();
        }

        function addDotThuRow() {
            const container = document.getElementById('dotThuRows');
            const emptyHint = document.getElementById('dotThuEmptyHint');
            if (emptyHint) {
                emptyHint.style.display = 'none';
            }
            const row = document.createElement('div');
            row.className = 'dot-thu-row';
            row.innerHTML = `
                <div class="dot-thu-field dot-thu-field--name">
                    <label>Tên đợt thu</label>
                    <input type="text" name="dotThu[${dotThuIndex}][tenDotThu]" placeholder="VD: Đợt 1 giữ chỗ">
                </div>
                <div class="dot-thu-field">
                    <label>Số tiền</label>
                    <input type="number" name="dotThu[${dotThuIndex}][soTien]" min="0" step="1000" oninput="previewPricing()">
                </div>
                <div class="dot-thu-field">
                    <label>Hạn thanh toán</label>
                    <input type="date" name="dotThu[${dotThuIndex}][hanThanhToan]">
                </div>
                <div class="dot-thu-meta">
                    <label class="dot-thu-check">
                        <input type="checkbox" name="dotThu[${dotThuIndex}][batBuoc]" value="1" checked>
                        <span>Bắt buộc</span>
                    </label>
                    <button type="button" class="kf-btn kf-btn-secondary dot-thu-remove" onclick="removeDotThuRow(this)" aria-label="Xóa đợt thu">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            dotThuIndex += 1;
            previewPricing();
        }

        function removeDotThuRow(button) {
            button.closest('.dot-thu-row')?.remove();
            previewPricing();
        }

        document.getElementById('dotThuRows')?.addEventListener('input', function(event) {
            if (event.target.matches('input[name*="[soTien]"], input[name*="[hanThanhToan]"], input[name*="[batBuoc]"]')) {
                previewPricing();
            }
        });

        document.getElementById('dotThuRows')?.addEventListener('change', function(event) {
            if (event.target.matches('input[name*="[soTien]"], input[name*="[hanThanhToan]"], input[name*="[batBuoc]"]')) {
                previewPricing();
            }
        });

        document.querySelector('[name="hieuLucTu"]')?.addEventListener('input', previewPricing);
        document.querySelector('[name="hieuLucDen"]')?.addEventListener('input', previewPricing);

        document.addEventListener('DOMContentLoaded', () => {
            previewPricing();
            updateLichHoc();
        });

        document.querySelector('form')?.addEventListener('submit', function(e) {
            updateDotThuConstraints();
            updateDotThuSummary();

            if (!validateDotThuBeforeSubmit()) {
                e.preventDefault();
            }
        });
    </script>
@endsection
