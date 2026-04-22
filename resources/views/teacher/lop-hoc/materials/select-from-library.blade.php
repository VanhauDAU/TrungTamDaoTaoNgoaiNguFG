@extends('layouts.internal')

@section('title', 'Chọn tài liệu chia sẻ vào lớp')
@section('page-title', 'Chia sẻ tài liệu vào lớp')
@section('breadcrumb', 'Lớp học / ' . $lopHoc->tenLopHoc . ' / Tài liệu / Chọn từ thư viện')

@section('stylesheet')
<style>
    .lib-card { border: 2px solid transparent; cursor: pointer; transition: all .18s; }
    .lib-card:hover { border-color: #6366f1; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.15) !important; }
    .lib-card.selected { border-color: #6366f1 !important; background: #f5f3ff; }
    .lib-card.already-shared { border-color: #16a34a !important; }
    .nhom-badge { font-size: .72rem; }
    .filter-chip { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; border: 1.5px solid #dee2e6; background: #fff; cursor: pointer; transition: all .15s; }
    .filter-chip:hover, .filter-chip.active { border-color: #6366f1; background: #6366f1; color: #fff; }
    .search-box { border-radius: 30px !important; padding-left: 2.5rem; }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
    .share-panel { position: sticky; top: 1rem; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('teacher.classes.materials.index', $lopHoc->slug) }}"
           class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i>Quay lại tài liệu lớp
        </a>
        <div>
            <h4 class="fw-bold mb-0">🔗 Chọn tài liệu từ thư viện</h4>
            <p class="text-muted mb-0 small">Lớp: <strong>{{ $lopHoc->tenLopHoc }}</strong></p>
        </div>
    </div>

    @if($thuVien->isEmpty() && !$nhom && !$search)
        {{-- Thư viện trống --}}
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <i class="fas fa-cloud-upload-alt fa-4x text-muted opacity-30 mb-3"></i>
            <h5 class="fw-bold">Thư viện của bạn đang trống</h5>
            <p class="text-muted mb-4">Bạn cần tải file lên thư viện cá nhân trước khi chia sẻ vào lớp.</p>
            <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-upload me-2"></i>Tải lên tài liệu mới
            </a>
        </div>
    @else
        <div class="row g-4">
            {{-- Cột trái: Danh sách thư viện --}}
            <div class="col-lg-8">
                {{-- Bộ lọc --}}
                <div class="card border-0 shadow-sm rounded-4 mb-3 px-3 py-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="position-relative flex-grow-1" style="max-width:260px; min-width:160px">
                            <i class="fas fa-search search-icon"></i>
                            <form method="GET" action="{{ route('teacher.classes.materials.select-library', $lopHoc->slug) }}" id="searchForm">
                                <input type="hidden" name="nhom" value="{{ $nhom }}">
                                <input type="text" name="q" value="{{ $search }}"
                                       class="form-control search-box border-0 bg-light"
                                       placeholder="Tìm tài liệu..."
                                       oninput="document.getElementById('searchForm').submit()">
                            </form>
                        </div>
                        <div class="vr d-none d-sm-block"></div>
                        <a href="{{ route('teacher.classes.materials.select-library', $lopHoc->slug, ['q' => $search]) }}"
                           class="filter-chip text-decoration-none {{ !$nhom ? 'active' : '' }}">Tất cả</a>
                        @foreach($nhomOptions as $val => $label)
                            <a href="{{ route('teacher.classes.materials.select-library', $lopHoc->slug) }}?nhom={{ $val }}&q={{ $search }}"
                               class="filter-chip text-decoration-none {{ $nhom === $val ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Grid tài liệu --}}
                @if($thuVien->isEmpty())
                    <div class="card border-0 rounded-4 p-4 text-center text-muted">
                        <i class="fas fa-search fa-2x opacity-30 mb-2"></i>
                        <div>Không tìm thấy tài liệu phù hợp.</div>
                    </div>
                @else
                    <div class="row g-3" id="libraryGrid">
                        @foreach($thuVien as $tl)
                            @php $alreadyShared = in_array($tl->giaoVienTaiLieuId, $sharedIds); @endphp
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 lib-card h-100 {{ $alreadyShared ? 'already-shared' : '' }}"
                                     onclick="selectLibItem(this, {{ $tl->giaoVienTaiLieuId }}, '{{ addslashes($tl->tieuDe) }}', '{{ $tl->nhomTaiLieu }}')"
                                     data-id="{{ $tl->giaoVienTaiLieuId }}"
                                     data-title="{{ $tl->tieuDe }}"
                                     data-nhom="{{ $tl->nhomTaiLieu }}"
                                     data-shared="{{ $alreadyShared ? '1' : '0' }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width:40px;height:40px;font-size:1rem">
                                                <i class="fas {{ $tl->mime_icon }}"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-semibold text-truncate" title="{{ $tl->tieuDe }}">
                                                    {{ $tl->tieuDe }}
                                                </div>
                                                <div class="text-muted small font-monospace text-truncate">{{ $tl->tenGoc }}</div>
                                                <div class="d-flex gap-2 align-items-center mt-1 flex-wrap">
                                                    <span class="badge bg-primary-subtle text-primary nhom-badge rounded-pill px-2">
                                                        {{ $tl->nhom_label }}
                                                    </span>
                                                    <span class="text-muted" style="font-size:.72rem">{{ $tl->kich_thuoc_readable }}</span>
                                                    @if($alreadyShared)
                                                        <span class="badge bg-success-subtle text-success nhom-badge rounded-pill px-2">
                                                            <i class="fas fa-check me-1"></i>Đã chia sẻ
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Cột phải: Form điền thông tin chia sẻ --}}
            <div class="col-lg-4">
                <div class="share-panel">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                            <h6 class="fw-bold mb-0">
                                <i class="fas fa-share-alt text-primary me-2"></i>Thông tin chia sẻ
                            </h6>
                        </div>
                        <div class="card-body px-4 pb-4">

                            {{-- Tài liệu đã chọn --}}
                            <div id="selectedInfo" class="alert alert-light border rounded-3 mb-3 py-2 px-3" style="display:none!important">
                                <div class="small text-muted mb-1">Tài liệu đã chọn:</div>
                                <div class="fw-semibold small" id="selectedTitle">—</div>
                            </div>
                            <div id="noSelection" class="alert alert-warning border-0 rounded-3 mb-3 py-2 px-3 small">
                                <i class="fas fa-arrow-left me-1"></i>Chọn tài liệu từ danh sách bên để tiếp tục.
                            </div>

                            <form method="POST"
                                  action="{{ route('teacher.classes.materials.share', $lopHoc->slug) }}"
                                  id="shareForm">
                                @csrf
                                <input type="hidden" name="giaoVienTaiLieuId" id="hiddenGvTlId" required>

                                @if($errors->any())
                                    <div class="alert alert-danger rounded-3 border-0 mb-3 small">
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Tiêu đề trong lớp <span class="text-danger">*</span></label>
                                    <input type="text" name="tieuDe" id="formTieuDe"
                                           class="form-control rounded-3 @error('tieuDe') is-invalid @enderror"
                                           value="{{ old('tieuDe') }}"
                                           placeholder="Hiển thị với học viên" required>
                                    @error('tieuDe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Nhóm tài liệu <span class="text-danger">*</span></label>
                                    <select name="nhomTaiLieu" id="formNhom" class="form-select rounded-3 @error('nhomTaiLieu') is-invalid @enderror">
                                        @foreach(\App\Models\Education\LopHocTaiLieu::nhomOptions() as $val => $label)
                                            <option value="{{ $val }}" {{ old('nhomTaiLieu') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('nhomTaiLieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">Trạng thái</label>
                                        <select name="trangThai" class="form-select rounded-3">
                                            @foreach(\App\Models\Education\LopHocTaiLieu::trangThaiOptions() as $val => $label)
                                                <option value="{{ $val }}" {{ (old('trangThai', 1) == $val) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">Thứ tự</label>
                                        <input type="number" name="sortOrder" class="form-control rounded-3"
                                               value="{{ old('sortOrder', 0) }}" min="0">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold small">Ghi chú thêm</label>
                                    <textarea name="moTa" class="form-control rounded-3" rows="2"
                                              placeholder="(Tùy chọn)">{{ old('moTa') }}</textarea>
                                </div>

                                <button type="submit" id="submitBtn" class="btn btn-primary w-100 rounded-pill" disabled>
                                    <i class="fas fa-share-alt me-2"></i>Chia sẻ vào lớp
                                </button>
                                <a href="{{ route('teacher.classes.materials.index', $lopHoc->slug) }}"
                                   class="btn btn-light w-100 rounded-pill mt-2 border">Hủy</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
let selectedCard = null;

function selectLibItem(card, id, title, nhom) {
    // Bỏ chọn cũ
    if (selectedCard && selectedCard !== card) {
        selectedCard.classList.remove('selected');
    }

    // Toggle chọn mới
    if (selectedCard === card) {
        card.classList.remove('selected');
        selectedCard = null;
        updatePanel(null, '', '');
    } else {
        card.classList.add('selected');
        selectedCard = card;
        updatePanel(id, title, nhom);
    }
}

function updatePanel(id, title, nhom) {
    const submit     = document.getElementById('submitBtn');
    const hiddenId   = document.getElementById('hiddenGvTlId');
    const titleInput = document.getElementById('formTieuDe');
    const nhomSelect = document.getElementById('formNhom');
    const selInfo    = document.getElementById('selectedInfo');
    const noSel      = document.getElementById('noSelection');
    const selTitle   = document.getElementById('selectedTitle');

    if (id) {
        hiddenId.value   = id;
        titleInput.value = title;
        selTitle.textContent = title;
        selInfo.style.removeProperty('display');
        noSel.style.display = 'none';
        submit.disabled = false;

        // Đặt nhóm
        for (let opt of nhomSelect.options) {
            if (opt.value === nhom) { opt.selected = true; break; }
        }
    } else {
        hiddenId.value   = '';
        submit.disabled  = true;
        selInfo.style.display = 'none!important';
        noSel.style.removeProperty('display');
    }
}

// Nếu có lỗi validation, restore giá trị cũ
@if(old('giaoVienTaiLieuId'))
document.addEventListener('DOMContentLoaded', () => {
    const id = {{ old('giaoVienTaiLieuId') }};
    const card = document.querySelector(`[data-id="${id}"]`);
    if (card) {
        card.classList.add('selected');
        selectedCard = card;
        updatePanel(id, card.dataset.title, card.dataset.nhom);
    }
});
@endif
</script>
@endpush
