@extends('layouts.internal')

@section('title', 'Thư viện tài liệu của tôi')
@section('page-title', 'Thư viện tài liệu')
@section('breadcrumb', 'Thư viện tài liệu')

@section('stylesheet')
<style>
    .library-card { transition: all .2s; border: 2px solid transparent; }
    .library-card:hover { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.12) !important; }
    .library-card.selected { border-color: #2563eb; background: #eff6ff; box-shadow: 0 10px 28px rgba(37,99,235,.15) !important; }
    .file-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .nhom-badge { font-size: .72rem; letter-spacing: .03em; }
    .filter-chip { border-radius: 20px; font-size: .8rem; padding: .35rem .85rem; border: 1.5px solid #dee2e6; background: #fff; cursor: pointer; transition: all .15s; }
    .filter-chip:hover, .filter-chip.active { border-color: #6366f1; background: #6366f1; color: #fff; }
    .search-box { border-radius: 30px !important; padding-left: 2.5rem; }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
    .empty-state { background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%); border-radius: 20px; padding: 3rem; }
    .selection-bar { position: sticky; top: 1rem; z-index: 10; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">📁 Thư viện tài liệu của tôi</h4>
            <p class="text-muted mb-0 small">
                Upload file một lần, chia sẻ vào nhiều lớp học khác nhau.
                <span class="badge bg-primary-subtle text-primary rounded-pill ms-2">{{ $taiLieus->count() }} tài liệu</span>
            </p>
        </div>
        <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-upload me-2"></i>Tải lên tài liệu mới
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 px-3 py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Search --}}
            <div class="position-relative flex-grow-1" style="max-width:300px; min-width:180px">
                <i class="fas fa-search search-icon"></i>
                <form method="GET" action="{{ route('teacher.materials.index') }}" id="searchForm">
                    <input type="hidden" name="nhom" value="{{ $nhom }}">
                    <input type="text" name="q" value="{{ $search }}"
                           class="form-control search-box border-0 bg-light"
                           placeholder="Tìm kiếm tài liệu..."
                           oninput="document.getElementById('searchForm').submit()">
                </form>
            </div>

            <div class="vr d-none d-md-block mx-1"></div>

            {{-- Nhóm filter --}}
            <a href="{{ route('teacher.materials.index', ['q' => $search]) }}"
               class="filter-chip text-decoration-none {{ !$nhom ? 'active' : '' }}">
                Tất cả
            </a>
            @foreach($nhomOptions as $val => $label)
                <a href="{{ route('teacher.materials.index', ['nhom' => $val, 'q' => $search]) }}"
                   class="filter-chip text-decoration-none {{ $nhom === $val ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if($taiLieus->isNotEmpty())
        <div id="selectionBar" class="card border-0 shadow-sm rounded-4 mb-4 px-3 py-3 selection-bar d-none">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" value="" id="selectAllFiles">
                        <label class="form-check-label small fw-semibold" for="selectAllFiles">
                            Chọn tất cả trên trang
                        </label>
                    </div>
                    <div class="small text-muted">
                        Đã chọn <strong id="selectedCount">0</strong> tài liệu
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-3" id="clearSelectionBtn">
                        Bỏ chọn
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="shareSelectedBtn" data-bs-toggle="modal" data-bs-target="#shareModal" disabled>
                        <i class="fas fa-share-alt me-2"></i>Gửi cho lớp
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Danh sách --}}
    @if($taiLieus->isEmpty())
        <div class="empty-state text-center">
            <i class="fas fa-cloud-upload-alt fa-4x text-primary opacity-30 mb-3"></i>
            <h5 class="fw-bold">Thư viện trống</h5>
            <p class="text-muted mb-4">Bắt đầu bằng cách tải lên tài liệu đầu tiên của bạn.</p>
            <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-upload me-2"></i>Tải lên ngay
            </a>
        </div>
    @else
        <div class="row g-3">
            @foreach($taiLieus as $tl)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 library-card"
                         data-file-card
                         data-id="{{ $tl->giaoVienTaiLieuId }}"
                         data-title="{{ $tl->tieuDe }}"
                         data-nhom="{{ $tl->nhomTaiLieu }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="form-check mt-1">
                                    <input class="form-check-input library-checkbox" type="checkbox"
                                           value="{{ $tl->giaoVienTaiLieuId }}"
                                           id="fileCheckbox{{ $tl->giaoVienTaiLieuId }}"
                                           data-file-checkbox
                                           data-title="{{ $tl->tieuDe }}"
                                           data-nhom="{{ $tl->nhomTaiLieu }}">
                                </div>
                                {{-- Icon file --}}
                                <div class="file-icon bg-primary-subtle flex-shrink-0">
                                    <i class="fas {{ $tl->mime_icon }}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-dark text-truncate" title="{{ $tl->tieuDe }}">
                                        {{ $tl->tieuDe }}
                                    </div>
                                    <div class="text-muted small font-monospace text-truncate" title="{{ $tl->tenGoc }}">
                                        {{ $tl->tenGoc }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary nhom-badge rounded-pill px-2 py-1">
                                            {{ $tl->nhom_label }}
                                        </span>
                                        <span class="text-muted small">{{ $tl->kich_thuoc_readable }}</span>
                                        <span class="text-muted small">{{ $tl->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($tl->moTa)
                                        <div class="text-muted small mt-1 text-truncate" title="{{ $tl->moTa }}">
                                            {{ $tl->moTa }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                            <div class="d-flex gap-2 justify-content-end">
                                {{-- Download --}}
                                <a href="{{ route('teacher.materials.download', $tl->giaoVienTaiLieuId) }}"
                                   class="btn btn-sm btn-light border rounded-pill px-2" title="Tải xuống">
                                    <i class="fas fa-download text-primary me-1"></i>
                                    <span class="d-none d-md-inline small">Tải</span>
                                </a>

                                {{-- Chỉnh sửa --}}
                                <a href="{{ route('teacher.materials.edit', $tl->giaoVienTaiLieuId) }}"
                                   class="btn btn-sm btn-light border rounded-pill px-2" title="Chỉnh sửa">
                                    <i class="fas fa-edit text-muted"></i>
                                </a>

                                {{-- Chia sẻ vào lớp --}}
                                <button type="button"
                                        class="btn btn-sm btn-primary rounded-pill px-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#shareModal"
                                        data-id="{{ $tl->giaoVienTaiLieuId }}"
                                        data-title="{{ $tl->tieuDe }}"
                                        data-nhom="{{ $tl->nhomTaiLieu }}"
                                        data-mode="single">
                                    <i class="fas fa-share-alt me-1"></i>Chia sẻ vào lớp
                                </button>

                                {{-- Xóa --}}
                                <form method="POST"
                                      action="{{ route('teacher.materials.destroy', $tl->giaoVienTaiLieuId) }}"
                                      onsubmit="return confirm('Xóa tài liệu này khỏi thư viện?\nCác bản chia sẻ trong lớp học sẽ mất kết nối với file.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2" title="Xóa">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@php
    $shareCourses = $courses->map(fn ($course) => [
        'id' => (string) $course['id'],
        'name' => $course['name'],
    ])->values();

    $shareClasses = $classes->map(fn ($lop) => [
        'slug' => $lop->slug,
        'name' => $lop->tenLopHoc,
        'courseId' => (string) ($lop->khoaHocId ?? 0),
    ])->values();
@endphp

@section('modal')
{{-- Modal: Chia sẻ vào lớp --}}
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="shareModalLabel">
                    <i class="fas fa-share-alt text-primary me-2"></i>Chia sẻ tài liệu vào lớp học
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                {{-- Hiển thị lỗi nếu có --}}
                @if($errors->any())
                    <div class="alert alert-danger rounded-4 border-0 mb-3 small shadow-sm">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Chọn khóa học <span class="text-danger">*</span></label>
                    <select id="shareCourseSelect" name="shareCourseId" form="shareForm" class="form-select rounded-3">
                        <option value="">-- Chọn khóa học --</option>
                        @foreach($shareCourses as $course)
                            <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                        @endforeach
                    </select>
                    <div id="shareCourseError" class="text-danger small mt-1 d-none">Vui lòng chọn khóa học.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Chọn lớp học <span class="text-danger">*</span></label>
                    <select id="shareClassSelect" name="shareClassSlug" form="shareForm" class="form-select rounded-3" disabled>
                        <option value="">-- Chọn khóa học trước --</option>
                    </select>
                    <div id="shareClassError" class="text-danger small mt-1 d-none">Vui lòng chọn lớp học.</div>
                    <div class="form-text">Chỉ hiển thị các lớp đang hiệu lực của khóa học đã chọn.</div>
                </div>

                <div class="alert alert-info rounded-3 border-0 mb-3 py-2 px-3 small">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="shareSingleInfo">Tài liệu: <strong id="shareTitleDisplay">...</strong></span>
                    <span id="shareMultipleInfo" class="d-none">Đang chuẩn bị gửi <strong id="shareCountDisplay">0</strong> tài liệu vào lớp.</span>
                </div>

                <form id="shareForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="giaoVienTaiLieuId" id="shareGvTlId">
                    <div id="shareIdsContainer"></div>

                    <div class="mb-3" id="shareTitleField">
                        <label class="form-label fw-semibold">Tiêu đề trong lớp <span class="text-danger">*</span></label>
                        <input type="text" name="tieuDe" id="shareTitleInput" class="form-control rounded-3" required>
                        <div class="form-text">Mặc định lấy tiêu đề từ thư viện.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề đợt gửi</label>
                        <input type="text" name="dotChiaSeTieuDe" id="shareBatchTitleInput" class="form-control rounded-3">
                        <div class="form-text">Ví dụ: Tài liệu buổi 1, Bài tập tuần 2, Tài liệu ôn tập giữa kỳ.</div>
                    </div>

                    <div class="mb-3 d-none" id="shareSelectedListWrapper">
                        <label class="form-label fw-semibold">Các tài liệu sẽ gửi</label>
                        <div class="border rounded-3 bg-light-subtle p-3 small" id="shareSelectedList"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú thêm</label>
                        <textarea name="moTa" class="form-control rounded-3" rows="2" placeholder="(Tùy chọn)"></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thứ tự</label>
                            <input type="number" name="sortOrder" class="form-control rounded-3" value="0" min="0">
                            <div class="form-text small">Số nhỏ hiện trước.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trangThai" class="form-select rounded-3">
                                @foreach(\App\Models\Education\LopHocTaiLieu::trangThaiOptions() as $val => $label)
                                    <option value="{{ $val }}" {{ $val == 1 ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Hiện/Ẩn với HS.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-share-alt me-2"></i>Xác nhận chia sẻ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const shareCourses = @json($shareCourses);
const shareClasses = @json($shareClasses);
const checkboxes = Array.from(document.querySelectorAll('[data-file-checkbox]'));
const selectionBar = document.getElementById('selectionBar');
const selectedCount = document.getElementById('selectedCount');
const shareSelectedBtn = document.getElementById('shareSelectedBtn');
const clearSelectionBtn = document.getElementById('clearSelectionBtn');
const selectAllFiles = document.getElementById('selectAllFiles');
const shareModalEl = document.getElementById('shareModal');
const shareIdsContainer = document.getElementById('shareIdsContainer');
const shareSingleInfo = document.getElementById('shareSingleInfo');
const shareMultipleInfo = document.getElementById('shareMultipleInfo');
const shareSelectedListWrapper = document.getElementById('shareSelectedListWrapper');
const shareSelectedList = document.getElementById('shareSelectedList');
const shareTitleField = document.getElementById('shareTitleField');
const shareTitleInput = document.getElementById('shareTitleInput');
const shareBatchTitleInput = document.getElementById('shareBatchTitleInput');
const shareForm = document.getElementById('shareForm');
const shareCourseSelect = document.getElementById('shareCourseSelect');
const shareClassSelect = document.getElementById('shareClassSelect');
const shareCourseError = document.getElementById('shareCourseError');
const shareClassError = document.getElementById('shareClassError');

function makeDefaultBatchTitle() {
    const now = new Date();
    const day = String(now.getDate()).padStart(2, '0');
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');

    return `Đợt gửi ${day}/${month}/${year} ${hour}:${minute}`;
}

function getSelectedFiles() {
    return checkboxes
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => ({
            id: checkbox.value,
            title: checkbox.dataset.title,
        }));
}

function syncCardState(checkbox) {
    const card = checkbox.closest('[data-file-card]');
    if (!card) {
        return;
    }

    card.classList.toggle('selected', checkbox.checked);
}

function updateSelectionUI() {
    const selected = getSelectedFiles();
    const count = selected.length;

    if (selectionBar) {
        selectionBar.classList.toggle('d-none', count === 0);
    }

    if (selectedCount) {
        selectedCount.textContent = count;
    }

    if (shareSelectedBtn) {
        shareSelectedBtn.disabled = count === 0;
    }

    if (selectAllFiles) {
        const total = checkboxes.length;
        selectAllFiles.checked = total > 0 && count === total;
        selectAllFiles.indeterminate = count > 0 && count < total;
    }
}

checkboxes.forEach((checkbox) => {
    syncCardState(checkbox);
    checkbox.addEventListener('change', function () {
        syncCardState(this);
        updateSelectionUI();
    });
});

if (selectAllFiles) {
    selectAllFiles.addEventListener('change', function () {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
            syncCardState(checkbox);
        });
        updateSelectionUI();
    });
}

if (clearSelectionBtn) {
    clearSelectionBtn.addEventListener('click', function () {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
            syncCardState(checkbox);
        });
        updateSelectionUI();
    });
}

function resetShareFormState() {
    const noteField = document.querySelector('#shareForm textarea[name="moTa"]');
    const orderField = document.querySelector('#shareForm input[name="sortOrder"]');
    const statusField = document.querySelector('#shareForm select[name="trangThai"]');

    shareIdsContainer.innerHTML = '';
    document.getElementById('shareGvTlId').value = '';
    shareCourseSelect.value = '';
    shareCourseError.classList.add('d-none');
    shareClassError.classList.add('d-none');
    refreshClassOptions('', '');
    shareForm.action = '';
    document.getElementById('shareCountDisplay').textContent = '0';
    shareSelectedList.innerHTML = '';
    shareSelectedListWrapper.classList.add('d-none');
    shareSingleInfo.classList.remove('d-none');
    shareMultipleInfo.classList.add('d-none');
    shareTitleField.classList.remove('d-none');
    shareTitleInput.required = true;
    shareTitleInput.value = '';
    shareBatchTitleInput.value = makeDefaultBatchTitle();
    noteField.value = '';
    orderField.value = '0';
    statusField.value = '1';
}

function buildShareAction(slug) {
    return `{{ url('teacher/lop-hoc-cua-toi') }}/${slug}/tai-lieu/chia-se`;
}

function refreshClassOptions(courseId, selectedSlug = '') {
    const normalizedCourseId = String(courseId || '');
    const availableClasses = shareClasses.filter((item) => item.courseId === normalizedCourseId);

    if (!normalizedCourseId) {
        shareClassSelect.innerHTML = '<option value="">-- Chọn khóa học trước --</option>';
        shareClassSelect.disabled = true;
        shareClassSelect.value = '';
        return;
    }

    shareClassSelect.innerHTML = ['<option value="">-- Chọn lớp đang hiệu lực --</option>']
        .concat(
            availableClasses.map((item) => {
                const selected = item.slug === selectedSlug ? ' selected' : '';
                return `<option value="${item.slug}"${selected}>${item.name}</option>`;
            })
        )
        .join('');

    shareClassSelect.disabled = availableClasses.length === 0;
    shareClassSelect.value = selectedSlug && availableClasses.some((item) => item.slug === selectedSlug)
        ? selectedSlug
        : '';
}

function populateShareForm(files) {
    resetShareFormState();

    if (files.length === 1) {
        const file = files[0];
        document.getElementById('shareGvTlId').value = file.id;
        document.getElementById('shareTitleDisplay').textContent = file.title;
        shareTitleInput.value = file.title;
        return;
    }

    shareSingleInfo.classList.add('d-none');
    shareMultipleInfo.classList.remove('d-none');
    shareSelectedListWrapper.classList.remove('d-none');
    shareTitleField.classList.add('d-none');
    shareTitleInput.required = false;
    shareTitleInput.value = '';
    document.getElementById('shareCountDisplay').textContent = String(files.length);

    shareSelectedList.innerHTML = files
        .map((file, index) => `<div class="py-2 ${index === 0 ? '' : 'border-top'}"><span class="fw-medium">${index + 1}.</span> <span class="text-truncate">${file.title}</span></div>`)
        .join('');

    shareIdsContainer.innerHTML = files
        .map((file) => `<input type="hidden" name="giaoVienTaiLieuIds[]" value="${file.id}">`)
        .join('');
}

shareModalEl.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    if (!btn) {
        return;
    }

    if (btn.id === 'shareSelectedBtn') {
        const files = getSelectedFiles();
        populateShareForm(files);
        return;
    }

    const id = btn.getAttribute('data-id');
    const title = btn.getAttribute('data-title');
    populateShareForm([{ id, title }]);
});

shareCourseSelect.addEventListener('change', function () {
    shareCourseError.classList.add('d-none');
    shareClassError.classList.add('d-none');
    refreshClassOptions(this.value);
    shareForm.action = '';
});

shareClassSelect.addEventListener('change', function () {
    shareClassError.classList.add('d-none');
    shareForm.action = this.value ? buildShareAction(this.value) : '';
});

shareForm.addEventListener('submit', function (e) {
    const courseId = shareCourseSelect.value;
    const classSlug = shareClassSelect.value;

    if (!courseId) {
        e.preventDefault();
        shareCourseError.classList.remove('d-none');
        shareCourseSelect.focus();
        return false;
    }

    if (!classSlug) {
        e.preventDefault();
        shareClassError.classList.remove('d-none');
        shareClassSelect.focus();
        return false;
    }

    this.action = buildShareAction(classSlug);
});

// Tự động mở lại modal nếu có lỗi validation
@if($errors->has('giaoVienTaiLieuId') || $errors->has('giaoVienTaiLieuIds') || $errors->has('giaoVienTaiLieuIds.*') || $errors->has('tieuDe') || $errors->has('dotChiaSeTieuDe') || $errors->has('sortOrder') || $errors->has('trangThai'))
    document.addEventListener('DOMContentLoaded', function() {
        const oldIds = @json(array_values(array_filter((array) old('giaoVienTaiLieuIds', []))));
        if (oldIds.length > 0) {
            oldIds.forEach((id) => {
                const checkbox = document.querySelector(`[data-file-checkbox][value="${id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    syncCardState(checkbox);
                }
            });
            updateSelectionUI();
            populateShareForm(getSelectedFiles());
        } else if (@json(old('giaoVienTaiLieuId'))) {
            const checkbox = document.querySelector(`[data-file-checkbox][value="{{ old('giaoVienTaiLieuId') }}"]`);
            if (checkbox) {
                checkbox.checked = true;
                syncCardState(checkbox);
                updateSelectionUI();
                populateShareForm([{
                    id: checkbox.value,
                    title: checkbox.dataset.title,
                }]);
            }
        }

        if (@json(old('moTa'))) {
            document.querySelector('#shareForm textarea[name="moTa"]').value = @json(old('moTa'));
        }
        if (@json(old('sortOrder', 0)) !== null) {
            document.querySelector('#shareForm input[name="sortOrder"]').value = @json(old('sortOrder', 0));
        }
        if (@json(old('trangThai', 1)) !== null) {
            document.querySelector('#shareForm select[name="trangThai"]').value = @json((string) old('trangThai', 1));
        }
        if (@json(old('tieuDe'))) {
            shareTitleInput.value = @json(old('tieuDe'));
        }
        if (@json(old('dotChiaSeTieuDe'))) {
            shareBatchTitleInput.value = @json(old('dotChiaSeTieuDe'));
        }
        if (@json(old('shareCourseId'))) {
            shareCourseSelect.value = @json((string) old('shareCourseId'));
            refreshClassOptions(@json((string) old('shareCourseId')), @json(old('shareClassSlug')));
            if (shareClassSelect.value) {
                shareForm.action = buildShareAction(shareClassSelect.value);
            }
        } else if (@json(old('shareClassSlug'))) {
            const matchedClass = shareClasses.find((item) => item.slug === @json(old('shareClassSlug')));
            if (matchedClass) {
                shareCourseSelect.value = matchedClass.courseId;
                refreshClassOptions(matchedClass.courseId, matchedClass.slug);
                shareForm.action = buildShareAction(matchedClass.slug);
            }
        }

        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        modal.show();
    });
@endif

updateSelectionUI();
</script>
@endpush
