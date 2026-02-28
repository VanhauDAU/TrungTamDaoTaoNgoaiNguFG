@extends('layouts.admin')

@section('title', 'Chỉnh sửa bài viết')
@section('page-title', 'Bài Viết / Blog')
@section('breadcrumb', 'Nội dung · Bài viết · Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/bai-viet/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/bai-viet/form.css') }}">
@endsection

@section('content')

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="bf-page-header">
        <div>
            <div class="bf-breadcrumb">
                <a href="{{ route('admin.bai-viet.index') }}"><i class="fas fa-newspaper me-1"></i> Bài viết</a>
                <span style="margin:0 6px;color:#cbd5e1">/</span> Chỉnh sửa
            </div>
            <div class="bf-page-title" style="margin-top:4px">
                <i class="fas fa-edit" style="color:#1d4ed8"></i>
                Chỉnh sửa: {{ Str::limit($baiViet->tieuDe, 50) }}
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('admin.bai-viet.show', $baiViet->baiVietId) }}" class="bf-btn bf-btn-secondary">
                <i class="fas fa-eye"></i> Xem
            </a>
            <a href="{{ route('admin.bai-viet.index') }}" class="bf-btn bf-btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    {{-- ── Validation errors ────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="bf-alert-error">
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

    <form action="{{ route('admin.bai-viet.update', $baiViet->baiVietId) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ── Tabs ──────────────────────────────────────────────── --}}
        <div class="bf-tabs">
            <button type="button" class="bf-tab-btn active" data-tab="tab-thongtin">
                <i class="fas fa-info-circle"></i> Thông tin chung
            </button>
            <button type="button" class="bf-tab-btn" data-tab="tab-noidung">
                <i class="fas fa-edit"></i> Nội dung bài viết
            </button>
            <button type="button" class="bf-tab-btn" data-tab="tab-phanloai">
                <i class="fas fa-tags"></i> Phân loại
            </button>
            <button type="button" class="bf-tab-btn" data-tab="tab-caidat">
                <i class="fas fa-sliders-h"></i> Cài đặt
            </button>
        </div>

        {{-- ── Tab 1: Thông tin chung ───────────────────────────── --}}
        <div class="bf-tab-panel active" id="tab-thongtin">
            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-info-circle"></i> Thông tin bài viết</div>
                <div class="bf-form-row">
                    <div class="bf-form-group" style="grid-column: 1/-1">
                        <label>Tiêu đề <span class="req">*</span></label>
                        <input type="text" name="tieuDe" value="{{ old('tieuDe', $baiViet->tieuDe) }}"
                            placeholder="Nhập tiêu đề bài viết..." class="{{ $errors->has('tieuDe') ? 'is-invalid' : '' }}">
                        @error('tieuDe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="bf-form-row">
                    <div class="bf-form-group" style="grid-column: 1/-1">
                        <label>Tóm tắt</label>
                        <textarea name="tomTat" rows="3"
                            placeholder="Mô tả ngắn gọn nội dung bài viết...">{{ old('tomTat', $baiViet->tomTat) }}</textarea>
                        <span class="form-hint">Tối đa 500 ký tự.</span>
                    </div>
                </div>
            </div>

            {{-- Upload ảnh --}}
            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-image"></i> Ảnh đại diện</div>
                <div class="bf-img-upload" id="img-drop-zone">
                    <input type="file" name="anhDaiDien" accept="image/*" id="img-input">
                    <div class="bf-img-placeholder" id="img-placeholder"
                        style="{{ $baiViet->anhDaiDien ? 'display:none' : '' }}">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div>Kéo ảnh vào đây hoặc <strong style="color:#1d4ed8">chọn file</strong></div>
                        <small>JPG, PNG, WEBP · Tối đa 2MB</small>
                    </div>
                    <img src="{{ $baiViet->anhDaiDien ? asset('storage/' . $baiViet->anhDaiDien) : '' }}" alt="Preview"
                        class="bf-img-preview" id="img-preview"
                        style="{{ $baiViet->anhDaiDien ? 'display:block' : 'display:none' }}">
                </div>
            </div>
        </div>

        {{-- ── Tab 2: Nội dung bài viết ──────────────────────────── --}}
        <div class="bf-tab-panel" id="tab-noidung">
            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-edit"></i> Nội dung bài viết <span class="req">*</span></div>
                <div class="bf-editor-wrap">
                    <textarea name="noiDung" id="noiDung-editor">{!! old('noiDung', $baiViet->noiDung) !!}</textarea>
                </div>
                @error('noiDung')
                    <div class="invalid-feedback" style="display:block;margin-top:6px">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- ── Tab 3: Phân loại ──────────────────────────────────── --}}
        <div class="bf-tab-panel" id="tab-phanloai">
            @php
                $selectedDanhMucs = old('danhMucIds', $baiViet->danhMucs->pluck('danhMucId')->toArray());
                $selectedTags = old('tagNames', $baiViet->tags->pluck('tenTag')->implode(','));
            @endphp

            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-folder-open"></i> Danh mục bài viết</div>
                <div class="bf-category-grid">
                    @foreach ($danhMucs as $dm)
                        <label>
                            <input type="checkbox" name="danhMucIds[]" value="{{ $dm->danhMucId }}" {{ in_array($dm->danhMucId, $selectedDanhMucs) ? 'checked' : '' }}>
                            <span>{{ $dm->tenDanhMuc }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-tags"></i> Tags</div>
                <input type="hidden" name="tagNames" id="tagNamesHidden" value="{{ $selectedTags }}">
                <div class="bf-tag-input-wrap" id="tag-input-wrap">
                    <input type="text" class="bf-tag-input" id="tag-input" placeholder="Nhập tag rồi nhấn Enter..."
                        autocomplete="off">
                </div>
                <span class="form-hint" style="margin-top:6px;display:block">
                    Nhấn Enter hoặc dấu phẩy để thêm tag. Click × để xóa tag.
                </span>
            </div>
        </div>

        {{-- ── Tab 4: Cài đặt ───────────────────────────────────── --}}
        <div class="bf-tab-panel" id="tab-caidat">
            <div class="bf-card">
                <div class="bf-card-title"><i class="fas fa-sliders-h"></i> Trạng thái xuất bản</div>
                <div class="bf-toggle-group">
                    <label class="bf-toggle">
                        <input type="hidden" name="trangThai" value="0">
                        <input type="checkbox" id="trangThaiToggle" value="1" {{ old('trangThai', $baiViet->trangThai) ? 'checked' : '' }}
                            onchange="document.querySelector('input[name=trangThai]').value = this.checked ? 1 : 0">
                        <span class="bf-toggle-slider"></span>
                    </label>
                    <span class="bf-toggle-label" id="toggle-label">
                        {{ old('trangThai', $baiViet->trangThai) ? 'Xuất bản ngay' : 'Lưu bản nháp' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Action bar ───────────────────────────────────────── --}}
        <div class="bf-action-bar">
            <a href="{{ route('admin.bai-viet.index') }}" class="bf-btn bf-btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="bf-btn bf-btn-primary">
                <i class="fas fa-save"></i> Cập nhật bài viết
            </button>
        </div>
    </form>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // ── TinyMCE Init ──────────────────────────────
        tinymce.init({
            selector: '#noiDung-editor',
            height: 500,
            menubar: 'file edit view insert format tools table',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons codesample',
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'link image media codesample emoticons | removeformat | fullscreen code help',
            content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; line-height: 1.7; }',
            images_upload_url: '{{ route("admin.bai-viet.upload-image") }}',
            images_upload_credentials: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            relative_urls: false,
            remove_script_host: false,
            images_upload_handler: function (blobInfo) {
                return new Promise(function (resolve, reject) {
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    fetch('{{ route("admin.bai-viet.upload-image") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: formData,
                    })
                        .then(response => response.json())
                        .then(data => resolve(data.location))
                        .catch(() => reject('Upload ảnh thất bại.'));
                });
            },
            branding: false,
            promotion: false,
        });

        // ── Tab switching ──────────────────────────────
        document.querySelectorAll('.bf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.bf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.bf-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        // ── Image preview ──────────────────────────────
        document.getElementById('img-input').addEventListener('change', function () {
            if (!this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('img-preview').style.display = 'block';
                document.getElementById('img-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(this.files[0]);
        });

        // ── Toggle label ───────────────────────────────
        document.getElementById('trangThaiToggle').addEventListener('change', function () {
            document.getElementById('toggle-label').textContent =
                this.checked ? 'Xuất bản ngay' : 'Lưu bản nháp';
        });

        // ── Tag input system ───────────────────────────
        const tagWrap = document.getElementById('tag-input-wrap');
        const tagInput = document.getElementById('tag-input');
        const tagHidden = document.getElementById('tagNamesHidden');
        let currentTags = tagHidden.value ? tagHidden.value.split(',').map(t => t.trim()).filter(Boolean) : [];

        function renderTags() {
            tagWrap.querySelectorAll('.bf-tag-chip').forEach(c => c.remove());
            currentTags.forEach((tag, i) => {
                const chip = document.createElement('span');
                chip.className = 'bf-tag-chip';
                chip.innerHTML = `${tag} <span class="remove-tag" data-index="${i}"><i class="fas fa-times"></i></span>`;
                tagWrap.insertBefore(chip, tagInput);
            });
            tagHidden.value = currentTags.join(',');
        }

        tagInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = this.value.trim().replace(/,/g, '');
                if (val && !currentTags.includes(val)) {
                    currentTags.push(val);
                    renderTags();
                }
                this.value = '';
            }
            if (e.key === 'Backspace' && !this.value && currentTags.length) {
                currentTags.pop();
                renderTags();
            }
        });

        tagWrap.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-tag');
            if (removeBtn) {
                currentTags.splice(parseInt(removeBtn.dataset.index), 1);
                renderTags();
            }
            tagInput.focus();
        });

        renderTags();
    </script>
@endsection