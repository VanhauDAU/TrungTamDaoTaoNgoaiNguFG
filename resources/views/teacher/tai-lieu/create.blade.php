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

            <form method="POST" action="{{ route('teacher.materials.store') }}" enctype="multipart/form-data" id="materialUploadForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề</label>
                    <input type="text" name="tieuDe"
                           class="form-control rounded-3 @error('tieuDe') is-invalid @enderror"
                           value="{{ old('tieuDe') }}"
                           placeholder="Tùy chọn khi tải 1 file. Nếu chọn nhiều file, hệ thống sẽ dùng tên từng file.">
                    @error('tieuDe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Bạn có thể để trống để hệ thống tự lấy tiêu đề từ tên file.</div>
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

                    <div id="uploadZone" class="border-2 border-dashed rounded-3 p-4 text-center"
                         style="border-color:#d1d5db; cursor:pointer; transition:all .2s"
                         onclick="document.getElementById('tepInput').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <div class="text-muted small" id="uploadLabel">
                            Kéo thả hoặc <strong class="text-primary">click để chọn một hay nhiều file</strong>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">PDF, Word, Excel, PowerPoint, ảnh, âm thanh, video, ZIP — tối đa 50MB mỗi file</div>
                    </div>
                    <input type="file" name="teps[]" id="tepInput"
                           class="d-none @error('tep') is-invalid @enderror @error('teps') is-invalid @enderror @error('teps.*') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.mp3,.mp4,.zip"
                           multiple
                           onchange="previewFile(this)">
                    <div id="selectedFilesPanel" class="mt-3 d-none">
                        <div class="rounded-3 border bg-light-subtle p-3">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <div class="fw-semibold small">File đã chọn</div>
                                <div class="text-muted small" id="selectedFilesMeta"></div>
                            </div>
                            <div id="selectedFilesList" class="small text-muted"></div>
                        </div>
                    </div>
                    <div id="uploadProgressPanel" class="mt-3 d-none">
                        <div class="rounded-3 border bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <div class="fw-semibold small" id="uploadProgressTitle">Đang chuẩn bị tải lên</div>
                                <div class="text-primary small fw-semibold" id="uploadProgressPercent">0%</div>
                            </div>
                            <div class="progress rounded-pill" role="progressbar" aria-label="Tiến độ tải tài liệu" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 10px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                            </div>
                            <div class="text-muted small mt-2" id="uploadProgressText">Chờ bắt đầu tải file...</div>
                        </div>
                    </div>
                    @error('tep')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('teps')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('teps.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="uploadSubmitBtn">
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
function formatFileSize(bytes) {
    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
    return (bytes / 1024).toFixed(0) + ' KB';
}

function fileIconClass(fileName) {
    const ext = (fileName.split('.').pop() || '').toLowerCase();
    const icons = {
        pdf: 'fa-file-pdf text-danger',
        doc: 'fa-file-word text-primary',
        docx: 'fa-file-word text-primary',
        xls: 'fa-file-excel text-success',
        xlsx: 'fa-file-excel text-success',
        ppt: 'fa-file-powerpoint text-warning',
        pptx: 'fa-file-powerpoint text-warning',
        png: 'fa-file-image text-success',
        jpg: 'fa-file-image text-success',
        jpeg: 'fa-file-image text-success',
        mp3: 'fa-file-audio text-info',
        mp4: 'fa-file-video text-primary',
        zip: 'fa-file-archive text-secondary',
    };

    return icons[ext] || 'fa-file text-muted';
}

function updateProgress(percent, title, text) {
    const panel = document.getElementById('uploadProgressPanel');
    const bar = document.getElementById('uploadProgressBar');
    const titleNode = document.getElementById('uploadProgressTitle');
    const textNode = document.getElementById('uploadProgressText');
    const percentNode = document.getElementById('uploadProgressPercent');

    panel.classList.remove('d-none');
    bar.style.width = `${percent}%`;
    bar.parentElement.setAttribute('aria-valuenow', String(percent));
    percentNode.textContent = `${percent}%`;
    titleNode.textContent = title;
    textNode.textContent = text;
}

function restoreSubmitButton() {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fas fa-upload me-2"></i>Tải lên thư viện';
}

function fallbackToNativeSubmit(message) {
    updateProgress(100, 'Chuyển sang tải lên tiêu chuẩn', message);
    form.dataset.nativeSubmitting = '1';
    window.setTimeout(() => form.submit(), 150);
}

function previewFile(input) {
    const zone  = document.getElementById('uploadZone');
    const label = document.getElementById('uploadLabel');
    const list = document.getElementById('selectedFilesList');
    const meta = document.getElementById('selectedFilesMeta');
    const panel = document.getElementById('selectedFilesPanel');
    const files = Array.from(input.files || []);

    if (files.length > 0) {
        const totalBytes = files.reduce((sum, file) => sum + file.size, 0);
        label.innerHTML = `<strong class="text-success">${files.length} file đã được chọn</strong><br><span class="text-muted small">Kiểm tra danh sách bên dưới trước khi tải lên</span>`;
        list.innerHTML = files
            .map((file, index) => `
                <div class="d-flex justify-content-between align-items-center gap-3 py-2 ${index === 0 ? '' : 'border-top'}">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <i class="fas ${fileIconClass(file.name)}"></i>
                        <span class="text-truncate">${file.name}</span>
                    </div>
                    <span class="text-nowrap">${formatFileSize(file.size)}</span>
                </div>
            `)
            .join('');
        meta.textContent = `${files.length} file • ${formatFileSize(totalBytes)}`;
        panel.classList.remove('d-none');
        zone.style.borderColor = '#6366f1';
        zone.style.background  = '#f5f3ff';
        updateProgress(100, 'Đã nạp xong danh sách file', 'File đã được trình duyệt nhận xong. Bạn có thể bấm tải lên.');
    } else {
        label.innerHTML = 'Kéo thả hoặc <strong class="text-primary">click để chọn một hay nhiều file</strong>';
        list.innerHTML = '';
        meta.textContent = '';
        panel.classList.add('d-none');
        zone.style.borderColor = '#d1d5db';
        zone.style.background  = '';
        document.getElementById('uploadProgressPanel').classList.add('d-none');
    }
}

const form = document.getElementById('materialUploadForm');
const submitButton = document.getElementById('uploadSubmitBtn');
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#6366f1'; zone.style.background = '#f5f3ff'; });
zone.addEventListener('dragleave', () => {
    if (!(document.getElementById('tepInput').files || []).length) {
        zone.style.borderColor = '#d1d5db';
        zone.style.background = '';
    }
});
zone.addEventListener('drop', e => {
    e.preventDefault();
    const input = document.getElementById('tepInput');
    input.files = e.dataTransfer.files;
    previewFile(input);
});

form.addEventListener('submit', function (event) {
    if (form.dataset.nativeSubmitting === '1') {
        return;
    }

    const input = document.getElementById('tepInput');
    const files = Array.from(input.files || []);
    const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

    if (files.length === 0 || typeof XMLHttpRequest === 'undefined' || typeof FormData === 'undefined') {
        return;
    }

    event.preventDefault();

    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang tải lên';
    updateProgress(0, 'Bắt đầu tải file lên máy chủ', 'Đang khởi tạo kết nối tải lên...');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.withCredentials = true;
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.setRequestHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');

    xhr.upload.addEventListener('progress', function (e) {
        if (!e.lengthComputable) {
            updateProgress(20, 'Đang tải dữ liệu', 'Đang gửi file lên máy chủ...');
            return;
        }

        const percent = Math.min(100, Math.round((e.loaded / e.total) * 100));
        updateProgress(percent, 'Đang tải file lên máy chủ', `Đã gửi ${formatFileSize(e.loaded)} / ${formatFileSize(e.total)}.`);
    });

    xhr.addEventListener('load', function () {
        const targetUrl = xhr.responseURL || '{{ route('teacher.materials.index') }}';

        if (xhr.status >= 200 && xhr.status < 400) {
            updateProgress(100, 'Tải lên hoàn tất', 'Máy chủ đã xử lý xong. Đang chuyển trang...');

            if (targetUrl && targetUrl !== window.location.href) {
                window.location.href = targetUrl;
                return;
            }

            document.open();
            document.write(xhr.responseText);
            document.close();
            return;
        }

        if (xhr.status === 419 || xhr.status === 403) {
            fallbackToNativeSubmit('Phiên bảo mật của biểu mẫu không khớp với XHR. Đang thử lại bằng submit tiêu chuẩn...');
            return;
        }

        restoreSubmitButton();

        const responseText = (xhr.responseText || '').toLowerCase();
        const serverMessage = responseText.includes('page expired')
            ? 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang rồi thử lại.'
            : 'Không thể tải file lên. Vui lòng thử lại.';

        updateProgress(0, 'Tải lên thất bại', serverMessage);
    });

    xhr.addEventListener('error', function () {
        fallbackToNativeSubmit('Kết nối XHR không ổn định. Đang thử lại bằng submit tiêu chuẩn...');
    });

    xhr.send(new FormData(form));
});
</script>
@endpush
