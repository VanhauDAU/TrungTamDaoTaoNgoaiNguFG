@extends('layouts.admin')

@section('title', 'Tạo Thông Báo Mới')
@section('page-title', 'Tạo Thông Báo Mới')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Tạo mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2" style="max-width:860px; margin:auto;">

        {{-- ── WIZARD STEPS HEADER ──────────────────────────────── --}}
        <div class="wizard-steps" id="wizardSteps">
            <div class="wz-step active" id="step-dot-1">
                <div class="wz-step-inner">
                    <div class="wz-circle">1</div>
                    <div class="wz-label">Soạn nội dung</div>
                </div>
            </div>
            <div class="wz-connector" id="conn-1"></div>
            <div class="wz-step" id="step-dot-2">
                <div class="wz-step-inner">
                    <div class="wz-circle">2</div>
                    <div class="wz-label">Chọn đối tượng</div>
                </div>
            </div>
            <div class="wz-connector" id="conn-2"></div>
            <div class="wz-step" id="step-dot-3">
                <div class="wz-step-inner">
                    <div class="wz-circle">3</div>
                    <div class="wz-label">Xác nhận & Gửi</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.thong-bao.store') }}" id="wizardForm" enctype="multipart/form-data">
            @csrf

            {{-- ════════════ STEP 1: Soạn nội dung ════════════ --}}
            <div class="wizard-panel active" id="panel-1">
                <div class="nb-card">
                    <div class="nb-card-title">
                        <div class="nb-icon-tag"><i class="fas fa-pen"></i></div>
                        Soạn nội dung thông báo
                    </div>

                    <div class="nb-form-group">
                        <label class="nb-form-label">Tiêu đề <span class="req">*</span></label>
                        <input type="text" name="tieuDe" id="tieuDe" class="nb-input"
                            placeholder="Nhập tiêu đề thông báo…" value="{{ old('tieuDe') }}" required>
                    </div>

                    <div class="nb-form-group">
                        <label class="nb-form-label">Nội dung <span class="req">*</span></label>
                        <div id="quillEditor" style="background:#fff; border-radius:10px; min-height:180px;"></div>
                        <textarea name="noiDung" id="noiDungHidden" style="display:none">{{ old('noiDung') }}</textarea>
                    </div>

                    <div class="nb-grid-2">
                        <div class="nb-form-group mb-0">
                            <label class="nb-form-label">Loại thông báo <span class="req">*</span></label>
                            <select name="loaiGui" class="nb-select" required>
                                @foreach (App\Models\Interaction\ThongBao::loaiLabels() as $k => $v)
                                    <option value="{{ $k }}" {{ old('loaiGui') == $k ? 'selected' : '' }}>
                                        {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="nb-form-group mb-0">
                            <label class="nb-form-label">Mức ưu tiên <span class="req">*</span></label>
                            <select name="uuTien" class="nb-select" required>
                                @foreach (App\Models\Interaction\ThongBao::uuTienLabels() as $k => $v)
                                    <option value="{{ $k }}" {{ old('uuTien') == $k ? 'selected' : '' }}>
                                        {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="nb-form-group" style="margin-top:1.25rem;">
                        <label class="nb-toggle-pin">
                            <input type="checkbox" name="ghim" value="1" {{ old('ghim') ? 'checked' : '' }}>
                            <i class="fas fa-thumbtack"></i> Ghim thông báo này lên đầu danh sách
                        </label>
                    </div>

                    {{-- ── FILE ĐÍNH KÈM ──────────────────── --}}
                    <div class="nb-form-group" style="margin-top:1.25rem;">
                        <label class="nb-form-label">
                            <i class="fas fa-paperclip me-1"></i> File đính kèm
                            <span style="font-weight:400;color:#6b7280;font-size:.82rem;">(Tối đa 5 file, mỗi file ≤
                                10MB)</span>
                        </label>
                        <div class="nb-dropzone" id="nb-dropzone" onclick="document.getElementById('tepDinhInput').click()"
                            ondragover="event.preventDefault();this.classList.add('drag-over')"
                            ondragleave="this.classList.remove('drag-over')" ondrop="handleDrop(event)">
                            <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:#a5b4fc;"></i>
                            <div style="font-size:.9rem;color:#6b7280;margin-top:.5rem;">Kéo thả file vào đây hoặc <span
                                    style="color:#6366f1;text-decoration:underline;cursor:pointer;">chọn file</span></div>
                            <div style="font-size:.78rem;color:#9ca3af;margin-top:.25rem;">PDF, Word, Excel, ảnh, ZIP…</div>
                        </div>
                        <input type="file" id="tepDinhInput" name="tepDinhs[]" multiple style="display:none;"
                            onchange="previewFiles(this.files)">
                        <div id="nb-file-list" style="margin-top:.75rem;display:flex;flex-wrap:wrap;gap:.5rem;"></div>
                    </div>

                </div>

                <div class="wizard-nav">
                    <a href="{{ route('admin.thong-bao.index') }}" class="nb-btn nb-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Huỷ
                    </a>
                    <div class="nb-spacer"></div>
                    <button type="button" class="nb-btn nb-btn-primary" onclick="goStep(2)">
                        Tiếp theo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ════════════ STEP 2: Chọn đối tượng ════════════ --}}
            <div class="wizard-panel" id="panel-2">
                <div class="nb-card">
                    <div class="nb-card-title">
                        <div class="nb-icon-tag"><i class="fas fa-users"></i></div>
                        Chọn đối tượng nhận thông báo
                    </div>

                    <div class="nb-form-group">
                        <label class="nb-form-label">Loại đối tượng <span class="req">*</span></label>
                        <div class="doi-tuong-cards" id="doiTuongCards">
                            @php
                                $dtOptions = [
                                    [0, '🌐', 'Tất cả', 'Toàn bộ người dùng'],
                                    [1, '🏫', 'Theo lớp', 'Học viên 1 lớp cụ thể'],
                                    [2, '📚', 'Theo khóa học', 'HV thuộc khóa học'],
                                    [3, '👤', 'Cá nhân', 'Gửi cho 1 người'],
                                    [4, '🎭', 'Theo vai trò', 'Admin/GV/NV/HV'],
                                ];
                            @endphp
                            @foreach ($dtOptions as [$val, $icon, $label, $desc])
                                <label class="doi-tuong-card {{ old('doiTuongGui', 0) == $val ? 'selected' : '' }}"
                                    onclick="selectDoiTuong({{ $val }}, this)">
                                    <input type="radio" name="doiTuongGui" value="{{ $val }}"
                                        {{ old('doiTuongGui', 0) == $val ? 'checked' : '' }}>
                                    <div class="dt-icon">{{ $icon }}</div>
                                    <div class="dt-label">{{ $label }}</div>
                                    <div class="dt-desc">{{ $desc }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sub-selectors (hidden theo default) --}}
                    <div id="subSelector" class="nb-form-group" style="display:none;">
                        <div id="ss-lop" class="ss-panel" style="display:none;">
                            <label class="nb-form-label">Chọn lớp học <span class="req">*</span></label>
                            <select id="sel-lop" class="nb-select">
                                <option value="">-- Chọn lớp học --</option>
                                @foreach ($lopHocs as $lop)
                                    <option value="{{ $lop->lopHocId }}">{{ $lop->tenLopHoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="ss-khoa" class="ss-panel" style="display:none;">
                            <label class="nb-form-label">Chọn khóa học <span class="req">*</span></label>
                            <select id="sel-khoa" class="nb-select">
                                <option value="">-- Chọn khóa học --</option>
                                @foreach ($khoaHocs as $khoa)
                                    <option value="{{ $khoa->khoaHocId }}">{{ $khoa->tenKhoaHoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="ss-canhan" class="ss-panel" style="display:none;">
                            <label class="nb-form-label">Chọn người nhận <span class="req">*</span></label>
                            <select id="sel-canhan" class="nb-select">
                                <option value="">-- Chọn người dùng --</option>
                                @foreach ($taiKhoans as $tk)
                                    @php $ten = $tk->hoSoNguoiDung->hoTen ?? $tk->nhanSu->hoTen ?? $tk->taiKhoan; @endphp
                                    <option value="{{ $tk->taiKhoanId }}">
                                        {{ $ten }} ({{ $tk->email }}) — {{ $tk->getRoleLabel() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="ss-role" class="ss-panel" style="display:none;">
                            <label class="nb-form-label">Chọn vai trò</label>
                            <select id="sel-role" class="nb-select">
                                <option value="0">Học viên</option>
                                <option value="1">Giáo viên</option>
                                <option value="2">Nhân viên</option>
                                <option value="3">Admin</option>
                            </select>
                        </div>
                    </div>

                    {{-- Preview người nhận --}}
                    <div id="recipientPreview" style="display:none;">
                        <div class="preview-header">
                            <span><i class="fas fa-users me-1"></i> Preview người nhận (tối đa 20)</span>
                            <span class="preview-count-badge" id="previewCount">0</span>
                        </div>
                        <div class="preview-body" id="previewBody">
                            <div class="preview-loading"><i class="fas fa-spinner fa-spin me-1"></i> Đang tải…</div>
                        </div>
                        <div class="preview-more" id="previewMore" style="display:none;"></div>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="nb-btn nb-btn-secondary" onclick="goStep(1)">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <div class="nb-spacer"></div>
                    <button type="button" class="nb-btn nb-btn-primary" onclick="goStep(3)">
                        Xem trước & Gửi <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ════════════ STEP 3: Xác nhận & Gửi ════════════ --}}
            <div class="wizard-panel" id="panel-3">
                <div class="nb-card">
                    <div class="nb-card-title">
                        <div class="nb-icon-tag" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        Xác nhận & Gửi thông báo
                    </div>

                    <div class="confirm-preview">
                        <div class="cp-title" id="cf-tieu-de">—</div>
                        <div class="cp-body" id="cf-noi-dung">—</div>
                        <div class="cp-meta">
                            <div class="cp-badge"><i class="fas fa-tag"></i> <span id="cf-loai">—</span></div>
                            <div class="cp-badge"><i class="fas fa-flag"></i> <span id="cf-uu-tien">—</span></div>
                            <div class="cp-badge"><i class="fas fa-users"></i> <span id="cf-doi-tuong">—</span></div>
                        </div>
                        <div class="cp-recipient-count">
                            <i class="fas fa-paper-plane me-2"></i>
                            Sẽ gửi đến <strong id="cf-count">?</strong> người nhận
                        </div>
                        <div id="cf-file-info" style="margin-top:.75rem;font-size:.85rem;color:#6b7280;display:none;">
                            <i class="fas fa-paperclip me-1"></i> <span id="cf-file-count">0</span> file đính kèm
                        </div>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="nb-btn nb-btn-secondary" onclick="goStep(2)">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <div class="nb-spacer"></div>
                    <button type="submit" class="nb-btn nb-btn-success">
                        <i class="fas fa-paper-plane"></i> Gửi thông báo ngay
                    </button>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    {{-- Inject biến PHP → JS (tối thiểu, không inline logic) --}}
    <script>
        window.RECIPIENTS_URL = '{{ route('admin.api.thong-bao.recipients') }}';
        window.LOAI_LABELS = @json(App\Models\Interaction\ThongBao::loaiLabels());
        window.UU_TIEN_LABELS = @json(App\Models\Interaction\ThongBao::uuTienLabels());
        window.DOI_TUONG_LABELS = @json(App\Models\Interaction\ThongBao::doiTuongLabels());
    </script>

    <script>
        // ── File dropzone ──────────────────────────────────────────
        let selectedFiles = []; // DataTransfer-backed list

        // ─── Helpers ────────────────────────────────────────────────
        function formatSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
            return (bytes / 1024).toFixed(0) + ' KB';
        }

        function getFileIcon(mime) {
            if (!mime) return {
                icon: 'fa-file',
                color: '#6b7280'
            };
            if (mime.startsWith('image/')) return {
                icon: 'fa-file-image',
                color: '#3b82f6'
            };
            if (mime === 'application/pdf') return {
                icon: 'fa-file-pdf',
                color: '#ef4444'
            };
            if (mime.includes('word') || mime.includes('doc'))
                return {
                    icon: 'fa-file-word',
                    color: '#2563eb'
                };
            if (mime.includes('sheet') || mime.includes('excel') || mime.includes('xls'))
                return {
                    icon: 'fa-file-excel',
                    color: '#16a34a'
                };
            if (mime.includes('presentation') || mime.includes('powerpoint'))
                return {
                    icon: 'fa-file-powerpoint',
                    color: '#ea580c'
                };
            if (mime.includes('zip') || mime.includes('rar') || mime.includes('archive'))
                return {
                    icon: 'fa-file-archive',
                    color: '#7c3aed'
                };
            if (mime.startsWith('text/')) return {
                icon: 'fa-file-alt',
                color: '#6b7280'
            };
            return {
                icon: 'fa-file',
                color: '#9ca3af'
            };
        }

        function previewFiles(fileList) {
            for (const f of fileList) {
                if (selectedFiles.length >= 5) {
                    alert('Tối đa 5 file đính kèm.');
                    break;
                }
                if (f.size > 10 * 1024 * 1024) {
                    alert(`File "${f.name}" vượt quá 10MB.`);
                    continue;
                }
                // Tránh trùng tên
                if (selectedFiles.some(s => s.name === f.name && s.size === f.size)) continue;
                selectedFiles.push(f);
            }
            rebuildInput();
            renderCards();
            updateConfirmFileCount();
        }

        function renderCards() {
            const list = document.getElementById('nb-file-list');
            list.innerHTML = '';
            list.style.cssText = 'margin-top:.75rem; display:flex; flex-wrap:wrap; gap:.65rem;';

            selectedFiles.forEach((f, i) => {
                const card = document.createElement('div');
                card.className = 'nb-preview-card';
                card.dataset.idx = i;

                if (f.type.startsWith('image/')) {
                    // ── Ảnh: thumbnail thực ─────────────────────────
                    const reader = new FileReader();
                    reader.onload = e => {
                        card.innerHTML = `
                        <div class="npc-thumb">
                            <img src="${e.target.result}" alt="${f.name}" onclick="openPreviewWindow('${e.target.result}','${f.name}')">
                            <div class="npc-overlay">
                                <span onclick="openPreviewWindow('${e.target.result}','${f.name}')" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="npc-info">
                            <div class="npc-name" title="${f.name}">${f.name}</div>
                            <div class="npc-size">${formatSize(f.size)}</div>
                        </div>
                        <button type="button" class="npc-remove" onclick="removeFile(${i})" title="Xóa">✕</button>`;
                    };
                    reader.readAsDataURL(f);
                } else {
                    // ── File khác: icon lớn ──────────────────────────
                    const {
                        icon,
                        color
                    } = getFileIcon(f.type);
                    const objUrl = URL.createObjectURL(f);
                    card.innerHTML = `
                    <div class="npc-thumb npc-thumb-icon">
                        <i class="fas ${icon}" style="color:${color};font-size:2rem;"></i>
                        <div class="npc-overlay">
                            <span onclick="window.open('${objUrl}','_blank')" title="Mở xem">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="npc-info">
                        <div class="npc-name" title="${f.name}">${f.name}</div>
                        <div class="npc-size">${formatSize(f.size)}</div>
                    </div>
                    <button type="button" class="npc-remove" onclick="removeFile(${i})" title="Xóa">✕</button>`;
                }

                list.appendChild(card);
            });
        }

        function openPreviewWindow(src, name) {
            const w = window.open('', '_blank');
            w.document.write(`<html><head><title>${name}</title>
            <style>body{margin:0;display:flex;align-items:center;justify-content:center;
            min-height:100vh;background:#111;}img{max-width:100%;max-height:100vh;}</style>
            </head><body><img src="${src}" alt="${name}"></body></html>`);
        }

        function removeFile(idx) {
            selectedFiles.splice(idx, 1);
            rebuildInput();
            renderCards();
            updateConfirmFileCount();
        }

        function rebuildInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            document.getElementById('tepDinhInput').files = dt.files;
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('nb-dropzone').classList.remove('drag-over');
            previewFiles(e.dataTransfer.files);
        }

        function updateConfirmFileCount() {
            const el = document.getElementById('cf-file-info');
            const cnt = document.getElementById('cf-file-count');
            if (el && cnt) {
                cnt.textContent = selectedFiles.length;
                el.style.display = selectedFiles.length > 0 ? 'block' : 'none';
            }
        }
    </script>

    <script src="{{ asset('assets/admin/js/pages/thong-bao/create.js') }}"></script>
@endsection
