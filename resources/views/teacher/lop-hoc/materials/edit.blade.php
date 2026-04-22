@extends('layouts.internal')

@section('title', 'Chỉnh sửa tài liệu – ' . $lopHoc->tenLopHoc)
@section('page-title', 'Chỉnh sửa tài liệu')
@section('breadcrumb', 'Lớp học / ' . $lopHoc->tenLopHoc . ' / Tài liệu / Sửa')

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
            <h5 class="fw-bold mb-0"><i class="fas fa-pen text-warning me-2"></i>Chỉnh sửa tài liệu</h5>
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

            {{-- File hiện tại --}}
            <div class="alert alert-light border rounded-3 mb-3 d-flex align-items-center gap-3">
                <i class="fas fa-file-alt text-primary fs-4"></i>
                <div>
                    <div class="fw-semibold">{{ $taiLieu->tenGoc }}</div>
                    <div class="text-muted small">{{ $taiLieu->kich_thuoc_readable }} &bull; {{ $taiLieu->mime }}</div>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('teacher.classes.materials.update', [$lopHoc->slug, $taiLieu->lopHocTaiLieuId]) }}"
                  enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" name="tieuDe" class="form-control rounded-3 @error('tieuDe') is-invalid @enderror"
                           value="{{ old('tieuDe', $taiLieu->tieuDe) }}" required>
                    @error('tieuDe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="moTa" rows="2" class="form-control rounded-3">{{ old('moTa', $taiLieu->moTa) }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nhóm tài liệu <span class="text-danger">*</span></label>
                        <select name="nhomTaiLieu" class="form-select rounded-3">
                            @foreach($nhomOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('nhomTaiLieu', $taiLieu->nhomTaiLieu) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Thứ tự</label>
                        <input type="number" name="sortOrder" class="form-control rounded-3"
                               value="{{ old('sortOrder', $taiLieu->sortOrder) }}" min="0">
                        <div class="form-text">Số nhỏ hiện trước.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="trangThai" class="form-select rounded-3">
                            @foreach($trangThaiOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('trangThai', $taiLieu->trangThai) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Ẩn/Hiện với học sinh.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Thay thế file <span class="text-muted fw-normal">(tùy chọn)</span></label>
                    <input type="file" name="tep" class="form-control rounded-3 @error('tep') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.mp3,.mp4,.zip">
                    <div class="form-text text-muted">Để trống nếu không muốn thay thế file. Tối đa 50MB.</div>
                    @error('tep')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark fw-semibold">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('teacher.classes.materials.index', $lopHoc->slug) }}"
                       class="btn btn-light rounded-pill px-4 border">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
