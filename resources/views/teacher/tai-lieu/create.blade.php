@extends('layouts.internal')

@section('title', 'Tải lên tài liệu mới')
@section('page-title', 'Thư viện tài liệu')
@section('breadcrumb', 'Thư viện / Tải lên mới')

@section('content')
<div class="container-fluid px-0" style="max-width:720px">
    <div class="mb-4">
        <a href="{{ route('teacher.materials.index') }}" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i>Quay lại thư viện
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-upload text-primary me-2"></i>Tải lên tài liệu vào thư viện
            </h5>
            <p class="text-muted small mb-3 mt-1">
                File sẽ được lưu vào thư viện cá nhân. Sau đó bạn có thể chia sẻ vào bất kỳ lớp học nào.
            </p>
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

            <form method="POST" action="{{ route('teacher.materials.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" name="tieuDe"
                           class="form-control rounded-3 @error('tieuDe') is-invalid @enderror"
                           value="{{ old('tieuDe') }}"
                           placeholder="VD: Bài giảng ngữ pháp căn bản" required>
                    @error('tieuDe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nhóm tài liệu <span class="text-danger">*</span></label>
                    <select name="nhomTaiLieu" class="form-select rounded-3 @error('nhomTaiLieu') is-invalid @enderror">
                        @foreach($nhomOptions as $val => $label)
                            <option value="{{ $val }}" {{ old('nhomTaiLieu') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('nhomTaiLieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả ngắn</label>
                    <textarea name="moTa" rows="2"
                              class="form-control rounded-3 @error('moTa') is-invalid @enderror"
                              placeholder="(Tùy chọn) Ghi chú về tài liệu này">{{ old('moTa') }}</textarea>
                    @error('moTa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">File đính kèm <span class="text-danger">*</span></label>

                    {{-- Custom upload zone --}}
                    <div id="uploadZone" class="border-2 border-dashed rounded-3 p-4 text-center"
                         style="border-color:#d1d5db; cursor:pointer; transition:all .2s"
                         onclick="document.getElementById('tepInput').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <div class="text-muted small" id="uploadLabel">
                            Kéo thả hoặc <strong class="text-primary">click để chọn file</strong>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">PDF, Word, Excel, PowerPoint, ảnh, âm thanh, video, ZIP — tối đa 50MB</div>
                    </div>
                    <input type="file" name="tep" id="tepInput"
                           class="d-none @error('tep') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.mp3,.mp4,.zip"
                           onchange="previewFile(this)">
                    @error('tep')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-upload me-2"></i>Tải lên thư viện
                    </button>
                    <a href="{{ route('teacher.materials.index') }}" class="btn btn-light rounded-pill px-4 border">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFile(input) {
    const zone  = document.getElementById('uploadZone');
    const label = document.getElementById('uploadLabel');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const size = file.size > 1048576
            ? (file.size / 1048576).toFixed(1) + ' MB'
            : (file.size / 1024).toFixed(0) + ' KB';
        label.innerHTML = `<strong class="text-success">${file.name}</strong><br><span class="text-muted small">${size}</span>`;
        zone.style.borderColor = '#6366f1';
        zone.style.background  = '#f5f3ff';
    }
}

// Drag and drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#6366f1'; zone.style.background = '#f5f3ff'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor = '#d1d5db'; zone.style.background = ''; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    const input = document.getElementById('tepInput');
    input.files = e.dataTransfer.files;
    previewFile(input);
});
</script>
@endpush
