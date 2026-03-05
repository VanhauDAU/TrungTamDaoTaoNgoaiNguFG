@extends('layouts.admin')

@section('title', 'Thêm khóa học mới')
@section('page-title', 'Khóa Học')
@section('breadcrumb', 'Quản lý · Khóa học · Thêm mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/form.css') }}">
@endsection

@section('content')

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="kf-page-header">
        <div>
            <div class="kf-breadcrumb">
                <a href="{{ route('admin.khoa-hoc.index') }}"><i class="fas fa-graduation-cap me-1"></i> Khóa học</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Thêm mới
            </div>
            <div class="kf-page-title" style="margin-top:4px">
                <i class="fas fa-plus-circle" style="color:#0f766e"></i>
                Thêm khóa học mới
            </div>
        </div>
        <a href="{{ route('admin.khoa-hoc.index') }}" class="kf-btn kf-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    {{-- ── Validation errors ────────────────────────────────────── --}}
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

    <form action="{{ route('admin.khoa-hoc.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ── Tabs ──────────────────────────────────────────────── --}}
        <div class="kf-tabs">
            <button type="button" class="kf-tab-btn active" data-tab="tab-thongtin">
                <i class="fas fa-info-circle"></i> Thông tin chung
            </button>
            <button type="button" class="kf-tab-btn" data-tab="tab-mota">
                <i class="fas fa-align-left"></i> Mô tả & Yêu cầu
            </button>
            <button type="button" class="kf-tab-btn" data-tab="tab-caidat">
                <i class="fas fa-sliders-h"></i> Cài đặt
            </button>
        </div>

        {{-- ── Tab 1: Thông tin chung ───────────────────────────── --}}
        <div class="kf-tab-panel active" id="tab-thongtin">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-info-circle"></i> Thông tin khóa học</div>

                <div class="kf-form-row">
                    <div class="kf-form-group" style="grid-column: 1/-1">
                        <label>Tên khóa học <span class="req">*</span></label>
                        <input type="text" name="tenKhoaHoc" value="{{ old('tenKhoaHoc') }}"
                            placeholder="Nhập tên khóa học..." class="{{ $errors->has('tenKhoaHoc') ? 'is-invalid' : '' }}">
                        @error('tenKhoaHoc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Danh mục khóa học <span class="req">*</span></label>
                        <select name="danhMucId" class="{{ $errors->has('danhMucId') ? 'is-invalid' : '' }}">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach ($flatTree as $item)
                                @php
                                    $node = $item['node'];
                                    $depth = $item['depth'];
                                @endphp
                                <option value="{{ $node->danhMucId }}"
                                    {{ old('danhMucId') == $node->danhMucId ? 'selected' : '' }}
                                    style="{{ $depth === 0 ? 'font-weight:600' : '' }}">
                                    {{ str_repeat('　', $depth) }}{{ $depth > 0 ? '└─ ' : '📂 ' }}{{ $node->tenDanhMuc }}
                                </option>
                            @endforeach
                        </select>
                        @error('danhMucId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kf-form-group">
                        <label>Đối tượng học viên</label>
                        <input type="text" name="doiTuong" value="{{ old('doiTuong') }}"
                            placeholder="VD: Người mới bắt đầu, 5–12 tuổi...">
                    </div>
                </div>
            </div>

            {{-- Upload ảnh --}}
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-image"></i> Ảnh khóa học</div>
                <div class="kf-img-upload" id="img-drop-zone">
                    <input type="file" name="anhKhoaHoc" accept="image/*" id="img-input">
                    <div class="kf-img-placeholder" id="img-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div>Kéo ảnh vào đây hoặc <strong style="color:#0f766e">chọn file</strong></div>
                        <small>JPG, PNG, WEBP · Tối đa 2MB</small>
                    </div>
                    <img src="" alt="Preview" class="kf-img-preview" id="img-preview">
                </div>
                @error('anhKhoaHoc')
                    <div class="invalid-feedback" style="display:block;margin-top:6px">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- ── Tab 2: Mô tả & Yêu cầu ──────────────────────────── --}}
        <div class="kf-tab-panel" id="tab-mota">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-align-left"></i> Mô tả khóa học</div>
                <div class="kf-form-group">
                    <label>Mô tả tổng quan</label>
                    <textarea name="moTa" rows="5" placeholder="Giới thiệu về khóa học, nội dung giảng dạy...">{{ old('moTa') }}</textarea>
                </div>
            </div>
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-clipboard-list"></i> Yêu cầu & Kết quả</div>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Yêu cầu đầu vào</label>
                        <textarea name="yeuCauDauVao" rows="4" placeholder="Học viên cần có những kỹ năng / điều kiện gì...">{{ old('yeuCauDauVao') }}</textarea>
                    </div>
                    <div class="kf-form-group">
                        <label>Kết quả đạt được</label>
                        <textarea name="ketQuaDatDuoc" rows="4" placeholder="Sau khóa học, học viên sẽ...">{{ old('ketQuaDatDuoc') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tab 3: Cài đặt ───────────────────────────────────── --}}
        <div class="kf-tab-panel" id="tab-caidat">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-sliders-h"></i> Trạng thái khóa học</div>
                <div class="kf-toggle-group">
                    <label class="kf-toggle">
                        <input type="hidden" name="trangThai" value="0">
                        <input type="checkbox" id="trangThaiToggle" value="1"
                            {{ old('trangThai', 1) ? 'checked' : '' }}
                            onchange="document.querySelector('input[name=trangThai]').value = this.checked ? 1 : 0">
                        <span class="kf-toggle-slider"></span>
                    </label>
                    <span class="kf-toggle-label" id="toggle-label">
                        {{ old('trangThai', 1) ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                    </span>
                </div>
                <p class="form-hint" style="margin-top:10px">
                    Khóa học đang hoạt động sẽ hiển thị trên trang web và có thể mở lớp học.
                </p>
            </div>
        </div>

        {{-- ── Action bar ───────────────────────────────────────── --}}
        <div class="kf-action-bar">
            <a href="{{ route('admin.khoa-hoc.index') }}" class="kf-btn kf-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="kf-btn kf-btn-primary">
                <i class="fas fa-save"></i> Lưu khóa học
            </button>
        </div>
    </form>

@endsection

@section('script')
    <script>
        // ── Tab switching ──────────────────────────────
        document.querySelectorAll('.kf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.kf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.kf-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        // ── Image preview ──────────────────────────────
        document.getElementById('img-input').addEventListener('change', function() {
            previewImage(this.files[0]);
        });

        function previewImage(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('img-preview').style.display = 'block';
                document.getElementById('img-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        // ── Toggle label ───────────────────────────────
        document.getElementById('trangThaiToggle').addEventListener('change', function() {
            document.getElementById('toggle-label').textContent =
                this.checked ? 'Đang hoạt động' : 'Ngừng hoạt động';
        });

        // ── Jump to tab with errors ────────────────────
        @if ($errors->has('tenKhoaHoc') || $errors->has('danhMucId') || $errors->has('anhKhoaHoc'))
            document.querySelector('[data-tab="tab-thongtin"]').click();
        @elseif ($errors->has('moTa') || $errors->has('yeuCauDauVao') || $errors->has('ketQuaDatDuoc'))
            document.querySelector('[data-tab="tab-mota"]').click();
        @endif
    </script>
@endsection
