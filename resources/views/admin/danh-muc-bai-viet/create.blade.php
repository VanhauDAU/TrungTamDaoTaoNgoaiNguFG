@extends('layouts.admin')

@section('title', 'Thêm danh mục bài viết')
@section('page-title', 'Danh Mục Bài Viết')
@section('breadcrumb', 'Nội dung · Danh mục · Thêm mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/danh-muc-bai-viet/index.css') }}">
@endsection

@section('content')

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="dm-page-header">
        <div>
            <div class="dm-breadcrumb">
                <a href="{{ route('admin.danh-muc-bai-viet.index') }}"><i class="fas fa-folder-open me-1"></i> Danh mục bài
                    viết</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Thêm mới
            </div>
            <div class="dm-page-title" style="margin-top:4px">
                <i class="fas fa-plus-circle" style="color:#1d4ed8"></i>
                Thêm danh mục mới
            </div>
        </div>
        <a href="{{ route('admin.danh-muc-bai-viet.index') }}" class="dm-btn dm-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    {{-- ── Validation errors ────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="dm-alert-error">
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

    <form action="{{ route('admin.danh-muc-bai-viet.store') }}" method="POST">
        @csrf

        <div class="dm-card">
            <div class="dm-card-title"><i class="fas fa-info-circle"></i> Thông tin danh mục</div>

            <div class="dm-form-row">
                <div class="dm-form-group" style="grid-column: 1/-1">
                    <label>Tên danh mục <span class="req">*</span></label>
                    <input type="text" name="tenDanhMuc" value="{{ old('tenDanhMuc') }}" placeholder="Nhập tên danh mục..."
                        class="{{ $errors->has('tenDanhMuc') ? 'is-invalid' : '' }}">
                    @error('tenDanhMuc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="dm-form-row">
                <div class="dm-form-group" style="grid-column: 1/-1">
                    <label>Mô tả</label>
                    <textarea name="moTa" rows="3" placeholder="Mô tả ngắn gọn cho danh mục...">{{ old('moTa') }}</textarea>
                </div>
            </div>
        </div>

        <div class="dm-card">
            <div class="dm-card-title"><i class="fas fa-sliders-h"></i> Trạng thái</div>
            <div class="dm-toggle-group">
                <label class="dm-toggle">
                    <input type="hidden" name="trangThai" value="0">
                    <input type="checkbox" id="trangThaiToggle" value="1" {{ old('trangThai', 1) ? 'checked' : '' }}
                        onchange="document.querySelector('input[name=trangThai]').value = this.checked ? 1 : 0">
                    <span class="dm-toggle-slider"></span>
                </label>
                <span class="dm-toggle-label" id="toggle-label">
                    {{ old('trangThai', 1) ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                </span>
            </div>
        </div>

        <div class="dm-action-bar">
            <a href="{{ route('admin.danh-muc-bai-viet.index') }}" class="dm-btn dm-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="dm-btn dm-btn-primary">
                <i class="fas fa-save"></i> Lưu danh mục
            </button>
        </div>
    </form>

@endsection

@section('script')
    <script>
        document.getElementById('trangThaiToggle').addEventListener('change', function () {
            document.getElementById('toggle-label').textContent =
                this.checked ? 'Đang hoạt động' : 'Ngừng hoạt động';
        });
    </script>
@endsection