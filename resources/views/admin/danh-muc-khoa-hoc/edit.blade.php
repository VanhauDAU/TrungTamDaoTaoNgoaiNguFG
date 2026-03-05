@extends('layouts.admin')
@section('title', 'Chỉnh sửa Danh Mục Khóa Học')
@section('page-title', 'Danh Mục Khóa Học')
@section('breadcrumb', 'Quản lý · Danh mục · Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/danh-muc-khoa-hoc/index.css') }}">
@endsection

@section('content')

    <div class="dm-form-header">
        <div>
            <div class="dm-form-breadcrumb">
                <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}">
                    <i class="fas fa-sitemap me-1"></i> Danh mục khóa học
                </a>
                <span style="margin:0 6px;color:#cbd5e1">/</span>
                {{ Str::limit($danhMuc->tenDanhMuc, 30) }}
                <span style="margin:0 6px;color:#cbd5e1">/</span> Chỉnh sửa
            </div>
            <div class="dm-form-title">
                <i class="fas fa-pen" style="color:#0f766e"></i>
                Chỉnh sửa: {{ Str::limit($danhMuc->tenDanhMuc, 40) }}
            </div>
        </div>
        <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="dm-btn dm-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="dm-alert-error">
            <i class="fas fa-exclamation-circle" style="font-size:1.1rem;flex-shrink:0"></i>
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

    <form action="{{ route('admin.danh-muc-khoa-hoc.update', $danhMuc->danhMucId) }}" method="POST">
        @csrf @method('PUT')

        <div class="dm-card">
            <div class="dm-card-title"><i class="fas fa-info-circle"></i> Thông tin danh mục</div>

            {{-- Tên --}}
            <div class="dm-form-group">
                <label>Tên danh mục <span class="req">*</span></label>
                <input type="text" name="tenDanhMuc" value="{{ old('tenDanhMuc', $danhMuc->tenDanhMuc) }}"
                    placeholder="Ví dụ: Tiếng Anh..." class="{{ $errors->has('tenDanhMuc') ? 'is-invalid' : '' }}">
                <div class="form-hint" style="margin-top:4px;font-size:.8rem">
                    Slug hiện tại: <strong>{{ $danhMuc->slug }}</strong>
                    (Thay đổi tên sẽ tạo slug mới.)
                </div>
                @error('tenDanhMuc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Danh mục cha --}}
            <div class="dm-form-group">
                <label>Danh mục cha</label>
                @if ($danhMuc->children->isNotEmpty())
                    <div class="dm-info-note">
                        <i class="fas fa-info-circle"></i>
                        Danh mục này đang có <strong>{{ $danhMuc->children->count() }} danh mục con</strong>
                        → không thể trở thành danh mục con của danh mục khác.
                    </div>
                    <input type="hidden" name="parent_id" value="">
                @else
                    <select name="parent_id" class="dm-select {{ $errors->has('parent_id') ? 'is-invalid' : '' }}">
                        <option value="">— Đây là danh mục gốc —</option>
                        @foreach ($flatTree as $item)
                            @php
                                $node = $item['node'];
                                $depth = $item['depth'];
                            @endphp
                            <option value="{{ $node->danhMucId }}"
                                {{ old('parent_id', $danhMuc->parent_id) == $node->danhMucId ? 'selected' : '' }}>
                                {{ str_repeat('　', $depth) }}{{ $depth > 0 ? '└─ ' : '' }}{{ $node->tenDanhMuc }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            {{-- Mô tả --}}
            <div class="dm-form-group">
                <label>Mô tả</label>
                <textarea name="moTa" rows="3">{{ old('moTa', $danhMuc->moTa) }}</textarea>
            </div>
        </div>

        {{-- Trạng thái --}}
        <div class="dm-card">
            <div class="dm-card-title"><i class="fas fa-sliders-h"></i> Trạng thái</div>
            @php $currentStatus = old('trangThai', $danhMuc->trangThai); @endphp
            <input type="hidden" name="trangThai" value="{{ $currentStatus ? 1 : 0 }}">
            <div class="dm-toggle-group">
                <label class="dm-toggle">
                    <input type="checkbox" id="trangThaiToggle" value="1" {{ $currentStatus ? 'checked' : '' }}
                        onchange="document.querySelector('input[name=trangThai]').value = this.checked ? 1 : 0;
                                  document.getElementById('toggle-label').textContent = this.checked ? 'Đang hoạt động' : 'Ngừng hoạt động';">
                    <span class="dm-toggle-slider"></span>
                </label>
                <span class="dm-toggle-label" id="toggle-label">
                    {{ $currentStatus ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                </span>
            </div>
        </div>

        <div class="dm-action-bar">
            <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="dm-btn dm-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="dm-btn dm-btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    </form>
@endsection
