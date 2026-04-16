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
        $oldPhuPhis = old('phuPhi', []);
    @endphp

    <form action="{{ route('admin.lop-hoc.store') }}" method="POST">
        @csrf
        <input type="hidden" id="conflictPreviewUrl" value="{{ route('admin.lop-hoc.preview-conflicts') }}">
        <input type="hidden" name="_tinhThanhId" id="hiddenTinhThanhId" value="{{ old('_tinhThanhId') }}">
        <input type="hidden" name="_phuongXaId" id="hiddenPhuongXaId" value="{{ old('_phuongXaId') }}">

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
                        <select id="tinhThanhSel" onchange="document.getElementById('hiddenTinhThanhId').value=this.value; loadPhuongXa(this.value)">
                            <option value="">-- Chọn tỉnh --</option>
                            @foreach ($tinhThanhs as $tt)
                                <option value="{{ $tt->tinhThanhId }}"
                                    {{ old('_tinhThanhId') == $tt->tinhThanhId ? 'selected' : '' }}>
                                    {{ $tt->tenTinhThanh }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kf-form-group">
                        <label>Phường / Xã <span class="req">*</span></label>
                        <select id="phuongXaSel" onchange="document.getElementById('hiddenPhuongXaId').value=this.value; loadCoSo()" disabled>
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
                    @error('lichHoc')
                        <div class="invalid-feedback" style="display:block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-clock"></i> Thời gian</div>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Ngày bắt đầu <span class="req">*</span></label>
                        <input type="date" name="ngayBatDau" id="ngayBatDauInput" value="{{ old('ngayBatDau') }}"
                            class="{{ $errors->has('ngayBatDau') ? 'is-invalid' : '' }}">
                        @error('ngayBatDau')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="kf-form-group">
                        <label>Ngày kết thúc <span class="req">*</span></label>
                        <input type="date" name="ngayKetThuc" id="ngayKetThucInput" value="{{ old('ngayKetThuc') }}"
                            class="{{ $errors->has('ngayKetThuc') ? 'is-invalid' : '' }}">
                        @error('ngayKetThuc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-door-open"></i> Phân công giáo viên & phòng học</div>


                <div id="scheduleConflictSummary" class="kf-alert-error" style="display:none;margin-bottom:16px">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div id="scheduleConflictSummaryText">Đang kiểm tra xung đột lịch...</div>
                </div>

                <div id="scheduleConflictHint" class="form-hint" style="margin-bottom:16px">
                    Hoàn tất cơ sở, ca học, lịch học, ngày bắt đầu và ngày kết thúc để bật kiểm tra xung đột phòng học realtime.
                </div>



                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Giáo viên <span class="hint-text text-muted" style="font-weight:normal;font-size:12px;">(Ưu
                                tiên thuộc cơ sở)</span></label>
                        <select name="taiKhoanId" id="giaoVienSel">
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
                        @error('taiKhoanId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kf-form-group">
                        <label>Phòng học</label>
                        <select name="phongHocId" id="phongHocSel" disabled>
                            <option value="">-- Chọn cơ sở trước --</option>
                        </select>
                        <div id="phongHocConflictFeedback" class="form-hint"></div>
                        @error('phongHocId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="kf-tab-panel" id="tab-hoc-phi">
            <div class="pricing-stage">

                <div class="pricing-workbench">
                    <div class="pricing-column pricing-column--main">
                        <div class="kf-card pricing-card pricing-card--tuition">
                            <div class="pricing-card-head">
                                <div>
                                    <div class="pricing-card-kicker">Học phí chính</div>
                                    <div class="pricing-card-heading">Cấu hình khoản thu bắt buộc của lớp</div>
                                </div>
                                <div class="pricing-card-aside">Chỉ phần này ảnh hưởng quyền học</div>
                            </div>

                            <div class="pricing-note">
                                Học phí chính dùng để xác định quyền học. Các khoản như tài liệu hoặc thi thử được quản lý
                                riêng, không cộng vào học phí niêm yết.
                            </div>

                            <div class="pricing-field-grid">
                                <div class="kf-form-group">
                                    <label>Học phí niêm yết (VNĐ)</label>
                                    <div class="kf-input-with-prefix">
                                        <span class="kf-input-prefix">₫</span>
                                        <input type="number" name="hocPhiNiemYet" id="hocPhiNiemYetInput"
                                            value="{{ old('hocPhiNiemYet') }}" min="0" step="1000"
                                            oninput="previewPricing()" class="form-control"
                                            placeholder="Ví dụ: 5.000.000">
                                    </div>
                                    <span class="form-hint">Đây là mức học phí niêm yết dùng để xác định quyền học của học viên.</span>
                                </div>
                                <div class="kf-form-group">
                                    <label>Số buổi cam kết</label>
                                    <input type="number" name="soBuoiCamKet" id="soBuoiCamKetInput"
                                        value="{{ old('soBuoiCamKet') }}" min="1" oninput="previewPricing()"
                                        class="form-control">
                                    <span class="form-hint">Để trống nếu giống số buổi dự kiến của lớp.</span>
                                </div>
                                <div class="kf-form-group">
                                    <label>Cách thu học phí</label>
                                    <div class="kf-segmented" data-input-id="loaiThuInput">
                                        @foreach ($loaiThuOptions as $value => $label)
                                            @php
                                                $isActive = (string) old('loaiThu', 0) === (string) $value;
                                            @endphp
                                            <div class="kf-segment-item {{ $isActive ? 'active' : '' }}" data-value="{{ $value }}">
                                                <i class="fas {{ $value == 0 ? 'fa-wallet' : 'fa-calendar-alt' }}"></i> {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="loaiThu" id="loaiThuInput" value="{{ old('loaiThu', 0) }}">
                                </div>
                                <div class="kf-form-group">
                                    <label>Trạng thái chính sách giá</label>
                                    <div class="kf-segmented" data-input-id="trangThaiChinhSachGiaInput">
                                        @php
                                            $currPolicy = (string) old('trangThaiChinhSachGia', '1');
                                        @endphp
                                        <div class="kf-segment-item {{ $currPolicy === '1' ? 'active' : '' }}" data-value="1">
                                            <i class="fas fa-check-circle"></i> Đang áp dụng
                                        </div>
                                        <div class="kf-segment-item {{ $currPolicy === '0' ? 'active' : '' }}" data-value="0">
                                            <i class="fas fa-pause-circle"></i> Tạm ngưng
                                        </div>
                                    </div>
                                    <input type="hidden" name="trangThaiChinhSachGia" id="trangThaiChinhSachGiaInput" value="{{ $currPolicy }}">
                                    <span class="form-hint">"Tạm ngưng" sẽ giữ nguyên dữ liệu nhưng không sinh công nợ mới.</span>
                                </div>
                            </div>

                            <div class="pricing-one-col" id="mainDueGroup">
                                <div class="kf-form-group">
                                    <label>Hạn thanh toán học phí</label>
                                    <input type="date" name="hanThanhToanHocPhi" id="hanThanhToanHocPhiInput"
                                        value="{{ old('hanThanhToanHocPhi') }}" oninput="previewPricing()"
                                        class="form-control">
                                    <span class="form-hint">Chỉ dùng khi chọn "Thu một lần". Hạn này sẽ áp dụng cho toàn bộ học phí chính.</span>
                                </div>
                            </div>

                            <div class="pricing-subsection dot-thu-section" id="dotThuSection">
                                <div class="pricing-subsection-head">
                                    <div>
                                        <div class="pricing-card-kicker">Kế hoạch thu học phí</div>
                                        <div class="pricing-subsection-title">Chia học phí chính thành nhiều mốc thu</div>
                                    </div>
                                    <button type="button" class="kf-btn kf-btn-secondary" id="addDotThuBtn"
                                        onclick="addDotThuRow()">
                                        <i class="fas fa-plus"></i> Thêm đợt thu
                                    </button>
                                </div>

                                <div class="dot-thu-toolbar">
                                    <p class="dot-thu-mode-hint" id="dotThuModeHint">
                                        Chọn “Chia đợt học phí” để mở cấu hình kế hoạch thu.
                                    </p>
                                </div>

                                <div class="dot-thu-summary" id="dotThuSummary">
                                    <div class="dot-thu-summary-card">
                                        <div class="dot-thu-summary-label">Tổng đợt thu</div>
                                        <div class="dot-thu-summary-value" id="dotThuTotalValue">0 đ</div>
                                    </div>
                                    <div class="dot-thu-summary-card">
                                        <div class="dot-thu-summary-label">Chênh lệch với học phí</div>
                                        <div class="dot-thu-summary-value" id="dotThuDeltaValue">0 đ</div>
                                    </div>
                                    <div class="dot-thu-summary-status" id="dotThuStatusCard">
                                        <div class="dot-thu-summary-label">Trạng thái kiểm tra</div>
                                        <div class="dot-thu-summary-value" id="dotThuStatusValue">Chưa áp dụng</div>
                                        <div class="dot-thu-summary-note" id="dotThuStatusNote">Mỗi đợt phải có hạn thanh
                                            toán tăng dần và tổng tiền phải bằng học phí niêm yết.</div>
                                    </div>
                                </div>

                                <div id="dotThuRows" class="dot-thu-list">
                                    @forelse ($oldDotThus as $index => $dotThu)
                                        <div class="dot-thu-row">
                                            <div class="dot-thu-field dot-thu-field--name">
                                                <label>Tên đợt thu</label>
                                                <input type="text" name="dotThu[{{ $index }}][tenDotThu]"
                                                    value="{{ $dotThu['tenDotThu'] ?? '' }}"
                                                    placeholder="VD: Đợt 1 giữ chỗ" class="form-control">
                                            </div>
                                            <div class="dot-thu-field">
                                                <label>Số tiền</label>
                                                <input type="hidden" name="dotThu[{{ $index }}][soTien]"
                                                    value="{{ $dotThu['soTien'] ?? '' }}" class="dot-thu-amount-hidden">
                                                <input type="text" inputmode="numeric"
                                                    value="{{ isset($dotThu['soTien']) && $dotThu['soTien'] !== '' ? number_format((int)$dotThu['soTien'], 0, '', ',') : '' }}"
                                                    class="form-control dot-thu-amount-display"
                                                    placeholder="VD: 2,000,000"
                                                    oninput="syncDotThuAmount(this)">
                                            </div>
                                            <div class="dot-thu-field">
                                                <label>Hạn thanh toán</label>
                                                <input type="date" name="dotThu[{{ $index }}][hanThanhToan]"
                                                    value="{{ $dotThu['hanThanhToan'] ?? '' }}" class="form-control">
                                            </div>
                                            <div class="dot-thu-meta">
                                                <button type="button" class="kf-btn kf-btn-secondary dot-thu-remove"
                                                    onclick="removeDotThuRow(this)" aria-label="Xóa đợt thu">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="form-hint dot-thu-empty" id="dotThuEmptyHint">Chưa cấu hình đợt thu
                                            học phí nào.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-column pricing-column--side">
                        <div class="kf-card pricing-card pricing-card--summary">
                            <div class="pricing-card-head">
                                <div>
                                    <div class="pricing-card-kicker">Tổng quan</div>
                                    <div class="pricing-card-heading">Xem nhanh công nợ sẽ sinh</div>
                                </div>
                                <div class="pricing-card-aside">Cập nhật theo thời gian thực</div>
                            </div>

                            <div id="pricingPreview" class="pricing-preview-stack" style="display:none">
                                <div class="pricing-preview-grid">
                                    <div class="pricing-preview-card pricing-preview-card--primary">
                                        <div class="pricing-preview-label">Học phí chính</div>
                                        <div class="pricing-preview-value" id="prev-hocphi">—</div>
                                        <div class="pricing-preview-note" id="prev-loaithu">—</div>
                                    </div>
                                    <div class="pricing-preview-card">
                                        <div class="pricing-preview-label">Khoản bổ sung mặc định</div>
                                        <div class="pricing-preview-value" id="prev-phuphi">0 đ</div>
                                        <div class="pricing-preview-note">Chỉ tính các khoản áp dụng cho mọi học viên</div>
                                    </div>
                                    <div class="pricing-preview-card">
                                        <div class="pricing-preview-label">Tổng công nợ dự kiến</div>
                                        <div class="pricing-preview-value" id="prev-total">0 đ</div>
                                        <div class="pricing-preview-note" id="prev-camket">—</div>
                                    </div>
                                </div>
                            </div>
                            <div id="pricingPreviewEmpty" class="pricing-empty-state">
                                Nhập học phí hoặc thêm khoản bổ sung để xem trước tổng công nợ của học viên.
                            </div>
                        </div>

                        <div class="kf-card pricing-card pricing-card--supplemental" id="phuPhiSection">
                            <div class="pricing-card-head">
                                <div>
                                    <div class="pricing-card-kicker">Khoản bổ sung</div>
                                    <div class="pricing-card-heading">Phí tài liệu, thi thử và khoản thu thêm</div>
                                </div>
                                <button type="button" class="kf-btn kf-btn-secondary" onclick="addPhuPhiRow()">
                                    <i class="fas fa-plus"></i> Thêm khoản bổ sung
                                </button>
                            </div>

                            <div class="pricing-note">
                                Khoản bổ sung không tính vào học phí niêm yết. Chúng có thể làm tổng công nợ lớn hơn học phí
                                chính nhưng không khóa học viên khi chưa thanh toán.
                            </div>

                            <div class="phu-phi-summary">
                                <div class="dot-thu-summary-card">
                                    <div class="dot-thu-summary-label">Tổng phụ phí</div>
                                    <div class="dot-thu-summary-value" id="phuPhiTotalValue">0 đ</div>
                                </div>
                                <div class="dot-thu-summary-card">
                                    <div class="dot-thu-summary-label">Phụ phí mặc định</div>
                                    <div class="dot-thu-summary-value" id="phuPhiDefaultValue">0 đ</div>
                                </div>
                                <div class="dot-thu-summary-status" id="phuPhiStatusCard">
                                    <div class="dot-thu-summary-label">Ghi chú</div>
                                    <div class="dot-thu-summary-value" id="phuPhiStatusValue">Độc lập với học phí</div>
                                    <div class="phu-phi-summary-note" id="phuPhiStatusNote">Các khoản bổ sung chỉ là công
                                        nợ riêng, không ảnh hưởng trạng thái học.</div>
                                </div>
                            </div>

                            <div id="phuPhiRows" class="phu-phi-list">
                                @forelse ($oldPhuPhis as $index => $phuPhi)
                                    <div class="phu-phi-row">
                                        <div class="phu-phi-field phu-phi-field--name">
                                            <label>Tên khoản thu</label>
                                            <input type="text" name="phuPhi[{{ $index }}][tenKhoanThu]"
                                                value="{{ $phuPhi['tenKhoanThu'] ?? '' }}" placeholder="VD: Phí tài liệu"
                                                class="form-control">
                                        </div>
                                        <div class="phu-phi-field phu-phi-field--group">
                                            <label>Nhóm phí</label>
                                            <select name="phuPhi[{{ $index }}][nhomPhi]" class="form-select">
                                                @foreach ($nhomPhiOptions as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ ($phuPhi['nhomPhi'] ?? \App\Models\Education\LopHocPhuPhi::NHOM_PHI_KHAC) === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="phu-phi-field phu-phi-field--amount">
                                            <label>Số tiền</label>
                                            <input type="number" name="phuPhi[{{ $index }}][soTien]"
                                                value="{{ $phuPhi['soTien'] ?? '' }}" min="0" step="1000"
                                                oninput="previewPricing()" class="form-control">
                                        </div>
                                        <div class="phu-phi-field phu-phi-field--due">
                                            <label>Hạn thanh toán</label>
                                            <input type="date" name="phuPhi[{{ $index }}][hanThanhToanMau]"
                                                value="{{ $phuPhi['hanThanhToanMau'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="phu-phi-meta">
                                            <label class="phu-phi-check">
                                                <input type="checkbox" name="phuPhi[{{ $index }}][apDungMacDinh]"
                                                    value="1" {{ !empty($phuPhi['apDungMacDinh']) ? 'checked' : '' }}
                                                    class="form-check-input">
                                                <span>Áp dụng cho mọi học viên</span>
                                            </label>
                                            <button type="button" class="kf-btn kf-btn-secondary phu-phi-remove"
                                                onclick="removePhuPhiRow(this)" aria-label="Xóa khoản bổ sung">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-hint phu-phi-empty" id="phuPhiEmptyHint">Chưa có khoản bổ sung nào.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-cog"></i> Cài đặt vận hành & Hiển thị</div>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Sĩ số học viên tối đa</label>
                        <input type="number" name="soHocVienToiDa" value="{{ old('soHocVienToiDa') }}"
                            placeholder="VD: 20" min="1" class="form-control">
                        <span class="form-hint">Số lượng học viên tối đa cho phép. Không vượt quá sức chứa phòng học.</span>
                    </div>
                    <div class="kf-form-group">
                        <label>Trạng thái lớp <span class="req">*</span></label>
                        <select name="trangThai" class="form-select">
                            @php
                                $selectedTrangThai = (string) old(
                                    'trangThai',
                                    \App\Models\Education\LopHoc::TRANG_THAI_SAP_MO,
                                );
                            @endphp
                            @foreach (\App\Models\Education\LopHoc::trangThaiOptions() as $value => $label)
                                <option value="{{ $value }}" {{ $selectedTrangThai === (string) $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <span class="form-hint">Xác định lớp đang mở đăng ký, đang học hay đã kết thúc.</span>
                    </div>
                </div>
                <div class="kf-form-group mt-3">
                    <label>Ghi chú chính sách hiển thị</label>
                    <textarea name="ghiChuChinhSach" rows="3" placeholder="Ví dụ: Học phí chưa bao gồm giáo trình quốc tế..."
                        class="form-control">{{ old('ghiChuChinhSach') }}</textarea>
                    <span class="form-hint">Thông tin này sẽ hiển thị công khai cho học viên và phụ huynh.</span>
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

        // Toggle Segmented Control
        document.querySelectorAll('.kf-segmented .kf-segment-item').forEach(item => {
            item.addEventListener('click', function() {
                const container = this.closest('.kf-segmented');
                const inputId = container.dataset.inputId;
                const input = document.getElementById(inputId);
                const value = this.dataset.value;

                // Update UI
                container.querySelectorAll('.kf-segment-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Update Input & Trigger Preview
                if (input) {
                    input.value = value;
                    // Trigger native events for consistency
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (typeof previewPricing === 'function') {
                    previewPricing();
                }
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
            triggerConflictPreview();
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
                    res.phuongXas.map(p =>
                        `<option value="${p.maPhuongXa}" ${String(p.maPhuongXa) === oldPhuongXa ? 'selected' : ''}>${p.tenPhuongXa}</option>`
                    ).join('');
                pSel.disabled = false;
                if (oldPhuongXa && pSel.value) loadCoSo();
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
                        `<option value="${c.coSoId}" ${String(c.coSoId) === oldCoSoId ? 'selected' : ''}>${c.tenCoSo}${c.tenPhuongXa ? ' — ' + c.tenPhuongXa : ''}</option>`
                    ).join('');
                cSel.disabled = false;
                if (cSel.value) loadPhongVaGV(cSel.value);
            } else {
                cSel.innerHTML = '<option value="">Không tìm thấy cơ sở</option>';
            }
        }

        let preferredPhong = "{{ old('phongHocId') }}";
        let preferredGV = "{{ old('taiKhoanId') }}";
        let conflictPreviewTimer = null;
        let conflictPreviewVersion = 0;
        const oldPhuongXa = "{{ old('_phuongXaId') }}";
        const oldCoSoId = "{{ old('coSoId') }}";

        async function loadPhongVaGV(coSoId) {
            const phongSel = document.getElementById('phongHocSel');
            const gvSel = document.getElementById('giaoVienSel');
            const currentPhong = phongSel.value || preferredPhong;
            const currentGV = gvSel.value || preferredGV;

            phongSel.innerHTML = '<option value="">Đang tải...</option>';
            gvSel.innerHTML = '<option value="">Đang tải...</option>';

            if (!coSoId) {
                phongSel.innerHTML = '<option value="">-- Chọn cơ sở trước --</option>';
                phongSel.disabled = true;
                gvSel.innerHTML = '<option value="">-- Chọn cơ sở trước --</option>';
                preferredPhong = '';
                preferredGV = '';
                clearConflictFeedback();
                return;
            }

            const [phongs, gvs] = await Promise.all([
                fetch(`/api/phong-hoc/${coSoId}`).then(r => r.json()),
                fetch(`/api/giao-vien/${coSoId}`).then(r => r.json()),
            ]);

            phongSel.innerHTML = '<option value="">-- Chọn phòng (tùy chọn) --</option>' +
                phongs.map(p =>
                    `<option value="${p.phongHocId}" data-suc-chua="${p.sucChua}" ${String(p.phongHocId) === String(currentPhong) ? 'selected' : ''}>
                        ${p.tenPhong} (sức chứa: ${p.sucChua} chỗ)
                    </option>`
                ).join('');

            let gvHtml = '<option value="">-- Chọn giáo viên (tùy chọn) --</option>';
            if (gvs.cung_co_so && gvs.cung_co_so.length > 0) {
                gvHtml += '<optgroup label="Giáo viên thuộc cơ sở này">';
                gvHtml += gvs.cung_co_so.map(g =>
                    `<option value="${g.taiKhoanId}" ${String(g.taiKhoanId) === String(currentGV) ? 'selected' : ''}>${g.hoTen}</option>`
                ).join('');
                gvHtml += '</optgroup>';
            }
            if (gvs.khac_co_so && gvs.khac_co_so.length > 0) {
                gvHtml += '<optgroup label="Giáo viên cơ sở khác">';
                gvHtml += gvs.khac_co_so.map(g =>
                    `<option value="${g.taiKhoanId}" ${String(g.taiKhoanId) === String(currentGV) ? 'selected' : ''}>${g.hoTen}</option>`
                ).join('');
                gvHtml += '</optgroup>';
            }
            gvSel.innerHTML = gvHtml;
            preferredPhong = phongSel.value || '';
            preferredGV = gvSel.value || '';

            updatePhongHocAvailability();
            updateSucChuaHint();
            triggerConflictPreview();
        }

        function updatePhongHocAvailability() {
            const phongSel = document.getElementById('phongHocSel');
            const ngayBD = document.getElementById('ngayBatDauInput')?.value || '';
            const ngayKT = document.getElementById('ngayKetThucInput')?.value || '';
            const coSoId = document.getElementById('coSoSel')?.value || '';

            if (!coSoId) {
                phongSel.disabled = true;
                return;
            }

            if (!ngayBD || !ngayKT) {
                phongSel.disabled = true;
                phongSel.title = 'Vui lòng chọn đầy đủ ngày bắt đầu và ngày kết thúc trước';
                return;
            }

            phongSel.disabled = false;
            phongSel.title = '';
        }

        function formatThousands(value) {
            const num = String(value).replace(/[^\d]/g, '');
            return num.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function syncHocPhiFromDisplay(displayInput) {
            const raw = String(displayInput.value).replace(/[^\d]/g, '');
            document.getElementById('hocPhiNiemYetInput').value = raw;
            const pos = displayInput.selectionStart;
            const oldLen = displayInput.value.length;
            displayInput.value = formatThousands(raw);
            const newLen = displayInput.value.length;
            displayInput.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
            previewPricing();
        }

        function syncDotThuAmount(displayInput) {
            const raw = String(displayInput.value).replace(/[^\d]/g, '');
            const hidden = displayInput.parentElement.querySelector('.dot-thu-amount-hidden');
            if (hidden) hidden.value = raw;
            const pos = displayInput.selectionStart;
            const oldLen = displayInput.value.length;
            displayInput.value = formatThousands(raw);
            const newLen = displayInput.value.length;
            displayInput.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
            previewPricing();
        }

        function initHocPhiDisplay() {
            const hidden = document.getElementById('hocPhiNiemYetInput');
            const display = document.getElementById('hocPhiNiemYetDisplay');
            if (hidden && display && hidden.value) {
                display.value = formatThousands(hidden.value);
            }
        }

        function setConflictSummary(status, message) {
            const box = document.getElementById('scheduleConflictSummary');
            const text = document.getElementById('scheduleConflictSummaryText');
            const hint = document.getElementById('scheduleConflictHint');
            if (!box || !text || !hint) return;

            if (!message) {
                box.style.display = 'none';
                hint.style.display = '';
                return;
            }

            text.textContent = message;
            box.style.display = 'flex';
            hint.style.display = 'none';
            box.style.background = status === 'ok' ? '#ecfdf3' : '#fff1f2';
            box.style.color = status === 'ok' ? '#166534' : '#991b1b';
            box.style.border = `1px solid ${status === 'ok' ? '#86efac' : '#fecdd3'}`;
        }

        function setFieldConflictFeedback(fieldId, state) {
            const feedback = document.getElementById(`${fieldId}ConflictFeedback`);
            if (!feedback) return;

            if (!state || !state.message) {
                feedback.textContent = '';
                feedback.style.color = '#64748b';
                return;
            }

            feedback.textContent = state.message;
            feedback.style.color = state.status === 'error' ? '#b91c1c' : '#166534';
        }

        function clearConflictFeedback() {
            setFieldConflictFeedback('phongHoc', null);
            setConflictSummary('', '');
        }

        function triggerConflictPreview() {
            window.clearTimeout(conflictPreviewTimer);
            conflictPreviewTimer = window.setTimeout(previewSchedulingConflicts, 250);
        }

        async function previewSchedulingConflicts() {
            const roomId = document.getElementById('phongHocSel')?.value || '';

            if (!roomId) {
                clearConflictFeedback();
                setConflictSummary('', '');
                document.getElementById('scheduleConflictHint').textContent =
                    'Chọn phòng học để bắt đầu kiểm tra xung đột realtime.';
                return;
            }

            const params = new URLSearchParams({
                coSoId: document.getElementById('coSoSel')?.value || '',
                caHocId: document.querySelector('[name="caHocId"]')?.value || '',
                phongHocId: roomId,
                ngayBatDau: document.querySelector('[name="ngayBatDau"]')?.value || '',
                ngayKetThuc: document.querySelector('[name="ngayKetThuc"]')?.value || '',
                lichHoc: document.getElementById('lichHocInput')?.value || '',
            });

            const requestVersion = ++conflictPreviewVersion;

            try {
                const response = await fetch(
                    `${document.getElementById('conflictPreviewUrl').value}?${params.toString()}`);
                const result = await response.json();

                if (requestVersion !== conflictPreviewVersion) {
                    return;
                }

                if (!result.ready) {
                    clearConflictFeedback();
                    setConflictSummary('', '');
                    document.getElementById('scheduleConflictHint').textContent = result.message || '';
                    return;
                }

                setFieldConflictFeedback('phongHoc', result.fieldStates?.phongHocId || null);
                setConflictSummary(result.ok ? 'ok' : 'error', result.message || '');
            } catch (error) {
                clearConflictFeedback();
                setConflictSummary('error', 'Không thể kiểm tra xung đột lịch lúc này. Vui lòng thử lại.');
            }
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

        document.getElementById('phongHocSel')?.addEventListener('change', function() {
            preferredPhong = this.value || '';
            updateSucChuaHint();
            triggerConflictPreview();
        });
        document.getElementById('giaoVienSel')?.addEventListener('change', function() {
            preferredGV = this.value || '';
        });
        document.querySelector('[name="caHocId"]')?.addEventListener('change', triggerConflictPreview);
        document.querySelector('[name="ngayBatDau"]')?.addEventListener('change', function() {
            validateDateRange();
            updatePhongHocAvailability();
            triggerConflictPreview();
        });
        document.querySelector('[name="ngayKetThuc"]')?.addEventListener('change', function() {
            validateDateRange();
            updatePhongHocAvailability();
            triggerConflictPreview();
        });

        function validateDateRange() {
            const ngayBatDau = document.getElementById('ngayBatDauInput');
            const ngayKetThuc = document.getElementById('ngayKetThucInput');
            if (!ngayBatDau || !ngayKetThuc) return;

            if (ngayBatDau.value) {
                ngayKetThuc.min = ngayBatDau.value;
            }
            if (ngayBatDau.value && ngayKetThuc.value && ngayKetThuc.value < ngayBatDau.value) {
                ngayKetThuc.value = ngayBatDau.value;
            }
        }

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

        let phuPhiIndex = {{ count($oldPhuPhis) }};
        const LOAI_THU_TRON_GOI = {{ \App\Models\Education\LopHocChinhSachGia::LOAI_THU_TRON_GOI }};
        const nhomPhiOptions = @json($nhomPhiOptions);

        function fmtMoney(n) {
            return Number(n || 0).toLocaleString('vi-VN') + ' đ';
        }

        function parseMoneyInputValue(value) {
            const normalized = String(value || '').trim().replace(/,/g, '').replace('.', '.');
            const parsed = Number.parseFloat(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function getDotThuRows() {
            return [...document.querySelectorAll('.dot-thu-row')];
        }

        function getPhuPhiRows() {
            return [...document.querySelectorAll('.phu-phi-row')];
        }

        function isTheoDotSelected() {
            return String(document.getElementById('loaiThuInput')?.value || '') === String(LOAI_THU_THEO_DOT);
        }

        function markRowError(row, hasError) {
            row.classList.toggle('has-error', hasError);
        }

        function getPricingInputs() {
            return {
                hocPhi: parseMoneyInputValue(document.getElementById('hocPhiNiemYetInput')?.value || 0),
                ghiChu: String(document.querySelector('[name="ghiChuChinhSach"]')?.value || '').trim(),
                hanThanhToanHocPhi: String(document.getElementById('hanThanhToanHocPhiInput')?.value || '').trim(),
            };
        }

        function hasPricingConfiguration() {
            const {
                hocPhi,
                ghiChu,
                hanThanhToanHocPhi
            } = getPricingInputs();

            return hocPhi > 0 || Boolean(ghiChu) || Boolean(hanThanhToanHocPhi) || getDotThuRows()
                .length > 0;
        }

        function getPhuPhiTotals() {
            return getPhuPhiRows().reduce((summary, row) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                const defaultCheckbox = row.querySelector('input[name*="[apDungMacDinh]"]');
                const amount = parseMoneyInputValue(amountInput?.value || 0);

                summary.total += amount;
                if (defaultCheckbox?.checked) {
                    summary.defaultTotal += amount;
                }

                return summary;
            }, {
                total: 0,
                defaultTotal: 0,
            });
        }

        function toggleCollectionMode() {
            const isTheoDot = isTheoDotSelected();
            const section = document.getElementById('dotThuSection');
            const addBtn = document.getElementById('addDotThuBtn');
            const modeHint = document.getElementById('dotThuModeHint');
            const mainDueGroup = document.getElementById('mainDueGroup');
            const mainDueInput = document.getElementById('hanThanhToanHocPhiInput');

            section?.classList.toggle('is-disabled', !isTheoDot);
            if (addBtn) {
                addBtn.disabled = !isTheoDot;
            }

            if (mainDueGroup) {
                mainDueGroup.style.display = isTheoDot ? 'none' : '';
            }
            if (mainDueInput) {
                mainDueInput.disabled = isTheoDot;
            }

            if (modeHint) {
                if (isTheoDot) {
                    modeHint.textContent =
                        'Mỗi đợt thu học phí phải có hạn thanh toán tăng dần và tổng tiền phải bằng học phí niêm yết.';
                    modeHint.classList.remove('is-warning');
                } else {
                    modeHint.textContent =
                        'Chế độ một lần dùng một hạn thanh toán chung. Chỉ chuyển sang chia đợt khi thật sự cần nhiều mốc thu.';
                    modeHint.classList.add('is-warning');
                }
            }

            getDotThuRows().forEach((row) => {
                row.querySelectorAll('input, button').forEach((element) => {
                    element.disabled = !isTheoDot;
                });
            });
        }

        function updateDotThuConstraints() {
            const isTheoDot = isTheoDotSelected();
            let previousDueDate = '';

            getDotThuRows().forEach((row, index) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                const dateInput = row.querySelector('input[name*="[hanThanhToan]"]');
                let hasError = false;

                if (amountInput) {
                    amountInput.setCustomValidity('');
                    const amountVal = parseMoneyInputValue(amountInput.value);
                    if (isTheoDot && amountVal <= 0) {
                        amountInput.setCustomValidity('Số tiền đợt thu phải lớn hơn 0.');
                        // Also mark the display input for visual feedback
                        const displayInput = row.querySelector('.dot-thu-amount-display');
                        if (displayInput) displayInput.classList.add('is-invalid');
                        hasError = true;
                    } else {
                        const displayInput = row.querySelector('.dot-thu-amount-display');
                        if (displayInput) displayInput.classList.remove('is-invalid');
                    }
                }

                if (dateInput) {
                    dateInput.min = previousDueDate || '';
                    dateInput.setCustomValidity('');

                    if (isTheoDot && !dateInput.value) {
                        dateInput.setCustomValidity('Vui lòng chọn hạn thanh toán cho đợt thu này.');
                        hasError = true;
                    }

                    if (isTheoDot && dateInput.value && previousDueDate && dateInput.value < previousDueDate) {
                        dateInput.setCustomValidity(index === 0 ?
                            'Hạn thanh toán không hợp lệ.' :
                            'Hạn thanh toán các đợt phải tăng dần theo thứ tự.');
                        hasError = true;
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
            const rows = getDotThuRows();
            const total = rows.reduce((sum, row) => {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                return sum + parseMoneyInputValue(amountInput?.value || 0);
            }, 0);
            const delta = hocPhi - total;
            const hasConstraintError = rows.some((row) =>
                row.querySelector('input[name*="[soTien]"]')?.validationMessage ||
                row.querySelector('input[name*="[hanThanhToan]"]')?.validationMessage
            );

            const totalValue = document.getElementById('dotThuTotalValue');
            const deltaValue = document.getElementById('dotThuDeltaValue');
            const statusCard = document.getElementById('dotThuStatusCard');
            const statusValue = document.getElementById('dotThuStatusValue');
            const statusNote = document.getElementById('dotThuStatusNote');
            const emptyHint = document.getElementById('dotThuEmptyHint');

            if (totalValue) totalValue.textContent = fmtMoney(total);
            if (deltaValue) deltaValue.textContent = fmtMoney(delta);
            if (emptyHint) {
                emptyHint.style.display = rows.length > 0 ? 'none' : 'block';
            }

            statusCard?.classList.remove('is-valid', 'is-invalid');

            if (!isTheoDot) {
                statusValue.textContent = 'Không áp dụng';
                statusNote.textContent =
                    'Đang thu học phí một lần. Hệ thống sẽ dùng hạn thanh toán học phí chung thay vì tách thành nhiều đợt.';
                return;
            }

            if (!rows.length) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = 'Thiếu đợt thu';
                statusNote.textContent = 'Chế độ chia đợt học phí phải có ít nhất một đợt thu.';
                return;
            }

            if (hasConstraintError) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = 'Cần sửa lịch thu';
                statusNote.textContent =
                    'Kiểm tra lại số tiền và hạn thanh toán. Hạn của đợt sau không được sớm hơn đợt trước.';
                return;
            }

            if (Math.abs(delta) > 0.009) {
                statusCard?.classList.add('is-invalid');
                statusValue.textContent = delta > 0 ? 'Chưa đủ tổng tiền' : 'Vượt học phí';
                statusNote.textContent = 'Tổng các đợt thu phải bằng đúng học phí niêm yết của lớp.';
                return;
            }

            statusCard?.classList.add('is-valid');
            statusValue.textContent = 'Hợp lệ';
            statusNote.textContent = 'Kế hoạch thu học phí đã khớp số tiền và thứ tự hạn thanh toán.';
        }

        function updatePhuPhiSummary() {
            const {
                total,
                defaultTotal
            } = getPhuPhiTotals();
            const totalValue = document.getElementById('phuPhiTotalValue');
            const defaultValue = document.getElementById('phuPhiDefaultValue');
            const statusValue = document.getElementById('phuPhiStatusValue');
            const statusNote = document.getElementById('phuPhiStatusNote');
            const emptyHint = document.getElementById('phuPhiEmptyHint');
            const hasRows = getPhuPhiRows().length > 0;

            if (totalValue) totalValue.textContent = fmtMoney(total);
            if (defaultValue) defaultValue.textContent = fmtMoney(defaultTotal);
            if (emptyHint) {
                emptyHint.style.display = hasRows ? 'none' : 'block';
            }

            if (!hasRows) {
                statusValue.textContent = 'Chưa có khoản bổ sung';
                statusNote.textContent =
                    'Các khoản như tài liệu hoặc thi thử có thể thêm sau. Chúng luôn tách khỏi học phí chính.';
                return;
            }

            statusValue.textContent = defaultTotal > 0 ? 'Có khoản áp dụng mặc định' : 'Tất cả đều tùy chọn';
            statusNote.textContent = defaultTotal > 0 ?
                'Những khoản được đánh dấu áp dụng cho mọi học viên sẽ tự sinh công nợ khi đăng ký lớp.' :
                'Các khoản này chỉ phát sinh khi nhân viên gán thêm cho từng học viên.';
        }

        function previewPricing() {
            const {
                hocPhi
            } = getPricingInputs();
            const loaiThuSelect = document.getElementById('loaiThuInput');
            const preview = document.getElementById('pricingPreview');
            const previewEmpty = document.getElementById('pricingPreviewEmpty');
            const {
                defaultTotal
            } = getPhuPhiTotals();

            if (!hasPricingConfiguration() && getPhuPhiRows().length === 0) {
                preview.style.display = 'none';
                if (previewEmpty) {
                    previewEmpty.style.display = 'block';
                }
                toggleCollectionMode();
                updateDotThuConstraints();
                updateDotThuSummary();
                updatePhuPhiSummary();
                return;
            }

            document.getElementById('prev-hocphi').textContent = hocPhi > 0 ? fmtMoney(hocPhi) : 'Chưa nhập';
            document.getElementById('prev-phuphi').textContent = fmtMoney(defaultTotal);
            document.getElementById('prev-total').textContent = fmtMoney(hocPhi + defaultTotal);
            document.getElementById('prev-camket').textContent = '—';
            document.getElementById('prev-loaithu').textContent = loaiThuSelect?.options[loaiThuSelect.selectedIndex]
                ?.text || '—';
            preview.style.display = 'block';
            if (previewEmpty) {
                previewEmpty.style.display = 'none';
            }

            toggleCollectionMode();
            updateDotThuConstraints();
            updateDotThuSummary();
            updatePhuPhiSummary();
        }

        function validateDotThuBeforeSubmit() {
            const {
                hocPhi,
                hanThanhToanHocPhi
            } = getPricingInputs();
            const isTheoDot = isTheoDotSelected();
            const hanThanhToanInput = document.getElementById('hanThanhToanHocPhiInput');

            if (!isTheoDot) {
                if (hocPhi > 0 && !hanThanhToanHocPhi) {
                    alert('Thu học phí một lần phải có hạn thanh toán học phí.');
                    hanThanhToanInput?.focus();
                    return false;
                }
                return true;
            }

            const rows = getDotThuRows();
            if (!rows.length) {
                alert('Chế độ chia đợt học phí phải có ít nhất một đợt thu.');
                return false;
            }

            for (const row of rows) {
                const amountInput = row.querySelector('input[name*="[soTien]"]');
                const dateInput = row.querySelector('input[name*="[hanThanhToan]"]');

                if ((amountInput && !amountInput.reportValidity()) || (dateInput && !dateInput.reportValidity())) {
                    return false;
                }
            }

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
                    <input type="text" name="dotThu[${dotThuIndex}][tenDotThu]" placeholder="VD: Đợt 1 giữ chỗ" class="form-control">
                </div>
                <div class="dot-thu-field">
                    <label>Số tiền</label>
                    <input type="hidden" name="dotThu[${dotThuIndex}][soTien]" class="dot-thu-amount-hidden">
                    <input type="text" inputmode="numeric" class="form-control dot-thu-amount-display" placeholder="VD: 2,000,000" oninput="syncDotThuAmount(this)">
                </div>
                <div class="dot-thu-field">
                    <label>Hạn thanh toán</label>
                    <input type="date" name="dotThu[${dotThuIndex}][hanThanhToan]" class="form-control">
                </div>
                <div class="dot-thu-meta">
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

        function buildNhomPhiOptions(selectedValue) {
            return Object.entries(nhomPhiOptions).map(([value, label]) =>
                `<option value="${value}" ${String(selectedValue) === String(value) ? 'selected' : ''}>${label}</option>`
            ).join('');
        }

        function addPhuPhiRow() {
            const container = document.getElementById('phuPhiRows');
            const emptyHint = document.getElementById('phuPhiEmptyHint');
            if (emptyHint) {
                emptyHint.style.display = 'none';
            }

            const row = document.createElement('div');
            row.className = 'phu-phi-row';
            row.innerHTML = `
                <div class="phu-phi-field phu-phi-field--name">
                    <label>Tên khoản thu</label>
                    <input type="text" name="phuPhi[${phuPhiIndex}][tenKhoanThu]" placeholder="VD: Phí tài liệu" class="form-control">
                </div>
                <div class="phu-phi-field phu-phi-field--group">
                    <label>Nhóm phí</label>
                    <select name="phuPhi[${phuPhiIndex}][nhomPhi]" class="form-select">
                        ${buildNhomPhiOptions('{{ \App\Models\Education\LopHocPhuPhi::NHOM_PHI_KHAC }}')}
                    </select>
                </div>
                <div class="phu-phi-field phu-phi-field--amount">
                    <label>Số tiền</label>
                    <input type="number" name="phuPhi[${phuPhiIndex}][soTien]" min="0" step="1000" oninput="previewPricing()" class="form-control">
                </div>
                <div class="phu-phi-field phu-phi-field--due">
                    <label>Hạn thanh toán</label>
                    <input type="date" name="phuPhi[${phuPhiIndex}][hanThanhToanMau]" class="form-control">
                </div>
                <div class="phu-phi-meta">
                    <label class="phu-phi-check">
                        <input type="checkbox" name="phuPhi[${phuPhiIndex}][apDungMacDinh]" value="1" class="form-check-input">
                        <span>Áp dụng cho mọi học viên</span>
                    </label>
                    <button type="button" class="kf-btn kf-btn-secondary phu-phi-remove" onclick="removePhuPhiRow(this)" aria-label="Xóa khoản bổ sung">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            phuPhiIndex += 1;
            previewPricing();
        }

        function removePhuPhiRow(button) {
            button.closest('.phu-phi-row')?.remove();
            previewPricing();
        }

        document.getElementById('dotThuRows')?.addEventListener('input', function(event) {
            if (event.target.matches('input[name*="[soTien]"], input[name*="[hanThanhToan]"]')) {
                previewPricing();
            }
        });

        document.getElementById('dotThuRows')?.addEventListener('change', function(event) {
            if (event.target.matches('input[name*="[soTien]"], input[name*="[hanThanhToan]"]')) {
                previewPricing();
            }
        });

        document.getElementById('phuPhiRows')?.addEventListener('input', function(event) {
            if (event.target.matches(
                    'input[name*="[soTien]"], input[name*="[hanThanhToanMau]"], input[name*="[tenKhoanThu]"]')) {
                previewPricing();
            }
        });

        document.getElementById('phuPhiRows')?.addEventListener('change', function(event) {
            if (event.target.matches('input[name*="[apDungMacDinh]"], select[name*="[nhomPhi]"]')) {
                previewPricing();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            updateLichHoc();
            initHocPhiDisplay();
            previewPricing();
            updateSucChuaHint();
            validateDateRange();
            updatePhongHocAvailability();

            // Restore location cascade when form has old() values (validation error)
            const tinhSel = document.getElementById('tinhThanhSel');
            if (tinhSel && tinhSel.value) {
                loadPhuongXa(tinhSel.value);
            }

            triggerConflictPreview();
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
