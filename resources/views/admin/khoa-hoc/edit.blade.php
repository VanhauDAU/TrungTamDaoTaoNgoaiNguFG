@extends('layouts.admin')

@section('title', 'Chỉnh sửa khóa học')
@section('page-title', 'Khóa Học')
@section('breadcrumb', 'Quản lý · Khóa học · Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/form.css') }}">
@endsection

@section('content')

    <div class="kf-page-header">
        <div>
            <div class="kf-breadcrumb">
                <a href="{{ route('admin.khoa-hoc.index') }}"><i class="fas fa-graduation-cap me-1"></i> Khóa học</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span>
                <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->slug) }}">{{ Str::limit($khoaHoc->tenKhoaHoc, 30) }}</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Chỉnh sửa
            </div>
            <div class="kf-page-title" style="margin-top:4px">
                <i class="fas fa-pen" style="color:#0f766e"></i>
                Chỉnh sửa: {{ Str::limit($khoaHoc->tenKhoaHoc, 40) }}
            </div>
        </div>
        <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->slug) }}" class="kf-btn kf-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if (session('success'))
        <div class="kf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
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

    <form action="{{ route('admin.khoa-hoc.update', $khoaHoc->slug) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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

        {{-- Tab 1 --}}
        <div class="kf-tab-panel active" id="tab-thongtin">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-info-circle"></i> Thông tin khóa học</div>

                <div class="kf-form-row">
                    <div class="kf-form-group" style="grid-column:1/-1">
                        <label>Tên khóa học <span class="req">*</span></label>
                        <input type="text" name="tenKhoaHoc" value="{{ old('tenKhoaHoc', $khoaHoc->tenKhoaHoc) }}"
                            class="{{ $errors->has('tenKhoaHoc') ? 'is-invalid' : '' }}">
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
                                    {{ old('danhMucId', $khoaHoc->danhMucId) == $node->danhMucId ? 'selected' : '' }}
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
                        <input type="text" name="doiTuong" value="{{ old('doiTuong', $khoaHoc->doiTuong) }}">
                    </div>
                </div>
            </div>

            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-image"></i> Ảnh khóa học</div>
                <x-upload.image
                    :standalone="false"
                    mode="deferred"
                    name="anhKhoaHoc"
                    title="Ảnh khóa học"
                    description="Chọn ảnh mới nếu cần thay ảnh cũ. Nếu bỏ trống, hệ thống sẽ giữ nguyên ảnh hiện tại."
                    choose-label="Đổi ảnh"
                    preview-url="{{ $khoaHoc->anhKhoaHoc ? asset('storage/' . $khoaHoc->anhKhoaHoc) : '' }}"
                    preview-alt="Ảnh khóa học"
                    hint="Chấp nhận JPG, PNG, GIF, WebP. Tối đa 2MB."
                    max-size="2097152"
                    max-size-label="2MB"
                    allowed-extensions-label="JPG, PNG, GIF, WebP"
                    pending-label="Ảnh mới sẽ được lưu khi bạn nhấn Lưu thay đổi."
                />
            </div>
        </div>

        {{-- Tab 2 --}}
        <div class="kf-tab-panel" id="tab-mota">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-align-left"></i> Mô tả khóa học</div>
                <div class="kf-form-group">
                    <label>Mô tả tổng quan</label>
                    <textarea name="moTa" rows="5">{{ old('moTa', $khoaHoc->moTa) }}</textarea>
                </div>
            </div>
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-clipboard-list"></i> Yêu cầu & Kết quả</div>
                <div class="kf-form-row">
                    <div class="kf-form-group">
                        <label>Yêu cầu đầu vào</label>
                        <textarea name="yeuCauDauVao" rows="4">{{ old('yeuCauDauVao', $khoaHoc->yeuCauDauVao) }}</textarea>
                    </div>
                    <div class="kf-form-group">
                        <label>Kết quả đạt được</label>
                        <textarea name="ketQuaDatDuoc" rows="4">{{ old('ketQuaDatDuoc', $khoaHoc->ketQuaDatDuoc) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 3 --}}
        <div class="kf-tab-panel" id="tab-caidat">
            <div class="kf-card">
                <div class="kf-card-title"><i class="fas fa-sliders-h"></i> Trạng thái khóa học</div>
                @php $currentStatus = old('trangThai', $khoaHoc->trangThai); @endphp
                <div class="kf-toggle-group">
                    <label class="kf-toggle">
                        <input type="hidden" name="trangThai" value="{{ $currentStatus ? 1 : 0 }}">
                        <input type="checkbox" id="trangThaiToggle" value="1" {{ $currentStatus ? 'checked' : '' }}
                            onchange="document.querySelector('input[name=trangThai]').value = this.checked ? 1 : 0;
                                      document.getElementById('toggle-label').textContent = this.checked ? 'Đang hoạt động' : 'Ngừng hoạt động';">
                        <span class="kf-toggle-slider"></span>
                    </label>
                    <span class="kf-toggle-label" id="toggle-label">
                        {{ $currentStatus ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="kf-action-bar">
            <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->slug) }}" class="kf-btn kf-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="kf-btn kf-btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    </form>

@endsection

@section('script')
    <script>
        document.querySelectorAll('.kf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.kf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.kf-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });
    </script>
@endsection
