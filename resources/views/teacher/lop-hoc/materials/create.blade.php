@extends('layouts.internal')

@section('title', 'Tải lên tài liệu – ' . $lopHoc->tenLopHoc)
@section('page-title', 'Tải lên tài liệu')
@section('breadcrumb', 'Lớp học / ' . $lopHoc->tenLopHoc . ' / Tài liệu / Tạo mới')

@section('content')
<div class="container-fluid px-0" style="max-width:720px">
    <div class="mb-4">
        <a href="{{ route('teacher.classes.materials.index', $lopHoc->slug) }}"
           class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách tài liệu
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0"><i class="fas fa-upload text-primary me-2"></i>Tải lên tài liệu mới</h5>
            <p class="text-muted small mb-3 mt-1">Lớp: <strong>{{ $lopHoc->tenLopHoc }}</strong></p>
        </div>
        <div class="card-body px-4 pb-4">
            @if($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('teacher.classes.materials.store', $lopHoc->slug) }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" name="tieuDe" class="form-control rounded-3 @error('tieuDe') is-invalid @enderror"
                           value="{{ old('tieuDe') }}" placeholder="VD: Bài giảng buổi 1" required>
                    @error('tieuDe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="moTa" rows="2" class="form-control rounded-3 @error('moTa') is-invalid @enderror"
                              placeholder="Ghi chú ngắn về tài liệu (tùy chọn)">{{ old('moTa') }}</textarea>
                    @error('moTa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nhóm tài liệu <span class="text-danger">*</span></label>
                        <select name="nhomTaiLieu" class="form-select rounded-3 @error('nhomTaiLieu') is-invalid @enderror">
                            @foreach($nhomOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('nhomTaiLieu') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('nhomTaiLieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Thứ tự</label>
                        <input type="number" name="sortOrder" class="form-control rounded-3"
                               value="{{ old('sortOrder', 0) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="trangThai" class="form-select rounded-3">
                            @foreach($trangThaiOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('trangThai', 1) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">File tài liệu <span class="text-danger">*</span></label>
                    <input type="file" name="tep" id="tep"
                           class="form-control rounded-3 @error('tep') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.mp3,.mp4,.zip">
                    <div class="form-text text-muted">Hỗ trợ: PDF, Word, Excel, PowerPoint, ảnh, âm thanh, video, ZIP. Tối đa 50MB.</div>
                    @error('tep')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-upload me-2"></i>Tải lên
                    </button>
                    <a href="{{ route('teacher.classes.materials.index', $lopHoc->slug) }}"
                       class="btn btn-light rounded-pill px-4 border">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
