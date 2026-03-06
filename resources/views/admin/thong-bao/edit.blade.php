@extends('layouts.admin')

@section('title', 'Chỉnh sửa Thông Báo')
@section('page-title', 'Chỉnh sửa Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('content')
    @php
        $isDraft = (int) $thongBao->sendTrangThai === App\Models\Interaction\ThongBao::SEND_TRANG_THAI_NHAP;
        $canSendNow = (int) $thongBao->sendTrangThai !== App\Models\Interaction\ThongBao::SEND_TRANG_THAI_DA_GUI;
        $xoaTepOld = collect(old('xoa_tep', []))
            ->map(fn($id) => (int) $id)
            ->all();
    @endphp

    <div class="container-fluid px-4 py-2" style="max-width:1220px; margin:auto;">
        <div class="nb-editor-hero">
            <div>
                <div class="nb-editor-hero-title">Cập Nhật Thông Báo</div>
                <div class="nb-editor-hero-subtitle">Chỉnh sửa nội dung, mức độ ưu tiên, ghim và quản lý tệp đính kèm trong một màn hình.</div>
            </div>
            <div class="nb-hero-chips">
                <span class="nb-hero-chip"><i class="fas fa-lock"></i> Giữ nguyên người nhận</span>
                <span class="nb-hero-chip"><i class="fas fa-file-pen"></i> Sửa trực tiếp nội dung</span>
                <span class="nb-hero-chip"><i class="fas fa-paperclip"></i> Thêm/xóa tệp đính kèm</span>
            </div>
        </div>

        {{-- Thông báo về giới hạn chỉnh sửa --}}
        <div class="locked-notice">
            <i class="fas fa-lock"></i>
            Đối tượng nhận đã được ghi nhận khi gửi và không thể thay đổi.
            Chỉ có thể chỉnh sửa nội dung và cài đặt hiển thị.
        </div>

        @if ($isDraft)
            <div class="locked-notice" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;">
                <i class="fas fa-paper-plane" style="color:#2563eb;"></i>
                Thông báo này đang ở trạng thái <strong>nháp</strong>. Sau khi hoàn tất, bấm nút <strong>"Gửi thông báo
                    ngay"</strong> ở cuối form để phát hành.
            </div>
        @endif

        @if ($errors->any())
            <div class="nb-alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    <strong>Dữ liệu chưa hợp lệ.</strong> Vui lòng kiểm tra lại tiêu đề, nội dung hoặc tệp đính kèm.
                </div>
            </div>
        @endif

        <div class="nb-compose-layout">
            <div class="nb-compose-main">
                <form method="POST" action="{{ route('admin.thong-bao.update', $thongBao->thongBaoId) }}" enctype="multipart/form-data" id="editThongBaoForm">
                    @csrf @method('PUT')

                    <div class="nb-card">
                        <div class="nb-card-title">
                            <div class="nb-icon-tag"><i class="fas fa-pen"></i></div>
                            Nội dung thông báo
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-form-label">Tiêu đề <span class="req">*</span></label>
                            <input type="text" id="edit-tieu-de" name="tieuDe" class="nb-input"
                                value="{{ old('tieuDe', $thongBao->tieuDe) }}" required>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-form-label">Nội dung <span class="req">*</span></label>
                            <div id="quillEditor" style="min-height:180px; border-radius:10px; background:#fff;"></div>
                            <textarea name="noiDung" id="noiDungHidden" style="display:none;">{{ old('noiDung', $thongBao->noiDung) }}</textarea>
                        </div>

                        <div class="nb-grid-2">
                            <div class="nb-form-group mb-0">
                                <label class="nb-form-label">Loại thông báo</label>
                                <select id="edit-loai" name="loaiGui" class="nb-select">
                                    @foreach (App\Models\Interaction\ThongBao::loaiLabels() as $k => $v)
                                        <option value="{{ $k }}"
                                            {{ old('loaiGui', $thongBao->loaiGui) == $k ? 'selected' : '' }}>
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="nb-form-group mb-0">
                                <label class="nb-form-label">Mức ưu tiên</label>
                                <select id="edit-uu-tien" name="uuTien" class="nb-select">
                                    @foreach (App\Models\Interaction\ThongBao::uuTienLabels() as $k => $v)
                                        <option value="{{ $k }}"
                                            {{ old('uuTien', $thongBao->uuTien) == $k ? 'selected' : '' }}>
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="nb-form-group" style="margin-top:1.25rem;">
                            <label class="nb-toggle-pin">
                                <input type="checkbox" id="edit-ghim" name="ghim" value="1"
                                    {{ old('ghim', $thongBao->ghim) ? 'checked' : '' }}>
                                <i class="fas fa-thumbtack"></i> Ghim thông báo này lên đầu danh sách
                            </label>
                        </div>
                    </div>

                    <div class="nb-card">
                        <div class="nb-card-title">
                            <div class="nb-icon-tag" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            Tệp đính kèm
                            <span class="nb-badge badge-hoc-tap" style="margin-left:auto;">
                                {{ $thongBao->tepDinhs->count() }} file hiện có
                            </span>
                        </div>

                        @if ($thongBao->tepDinhs->isNotEmpty())
                            <div class="nb-form-group">
                                <label class="nb-form-label">Tệp hiện tại (chọn để xóa)</label>
                                <div class="attach-list">
                                    @foreach ($thongBao->tepDinhs as $tep)
                                        <div class="attach-item">
                                            <span class="attach-icon"><i class="fas {{ $tep->iconClass }}"></i></span>
                                            <span class="attach-name" title="{{ $tep->tenFile }}">{{ $tep->tenFile }}</span>
                                            <span class="attach-size">{{ $tep->kichThuocHienThi }}</span>
                                            <a href="{{ $tep->url }}" target="_blank" rel="noopener"
                                                class="attach-dl">
                                                <i class="fas fa-eye me-1"></i>Xem
                                            </a>
                                            <label class="attach-del-label">
                                                <input type="checkbox" class="edit-delete-attachment" name="xoa_tep[]"
                                                    value="{{ $tep->tepDinhId }}"
                                                    {{ in_array((int) $tep->tepDinhId, $xoaTepOld, true) ? 'checked' : '' }}>
                                                Xóa
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="nb-form-group mb-0">
                            <label class="nb-form-label">
                                Thêm tệp mới
                                <span style="font-weight:400;color:#6b7280;font-size:.82rem;">(Tối đa 5 file tải mới, mỗi
                                    file ≤ 10MB)</span>
                            </label>
                            <div class="nb-dropzone" id="edit-dropzone"
                                onclick="document.getElementById('edit-tepDinhInput').click()"
                                ondragover="event.preventDefault();this.classList.add('drag-over')"
                                ondragleave="this.classList.remove('drag-over')" ondrop="handleEditDrop(event)">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:#a5b4fc;"></i>
                                <div style="font-size:.9rem;color:#6b7280;margin-top:.5rem;">
                                    Kéo thả file vào đây hoặc <span
                                        style="color:#6366f1;text-decoration:underline;cursor:pointer;">chọn file</span>
                                </div>
                                <div style="font-size:.78rem;color:#9ca3af;margin-top:.25rem;">PDF, Word, Excel, ảnh,
                                    ZIP…</div>
                            </div>
                            <input type="file" id="edit-tepDinhInput" name="tepDinhs[]" multiple
                                style="display:none;" onchange="previewEditFiles(this.files)">
                            <div id="edit-file-list" style="margin-top:.75rem;display:flex;flex-wrap:wrap;gap:.5rem;">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem; align-items:center;">
                        <a href="{{ route('admin.thong-bao.show', $thongBao->thongBaoId) }}" class="nb-btn nb-btn-secondary">
                            <i class="fas fa-times"></i> Huỷ
                        </a>
                        <div class="nb-spacer"></div>
                        <button type="submit" class="nb-btn nb-btn-secondary" name="hanhDong" value="save">
                            <i class="fas fa-save"></i> {{ $isDraft ? 'Lưu nháp' : 'Lưu thay đổi' }}
                        </button>
                        @if ($canSendNow)
                            <button type="submit" class="nb-btn nb-btn-success" name="hanhDong" value="send">
                                <i class="fas fa-paper-plane"></i> Gửi thông báo ngay
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <aside class="nb-compose-aside">
                <div class="nb-side-card">
                    <div class="nb-side-title"><i class="fas fa-list-check"></i> Tóm tắt chỉnh sửa</div>
                    <div class="nb-side-kv">
                        <span>Tiêu đề</span>
                        <strong id="edit-summary-title">{{ old('tieuDe', $thongBao->tieuDe) }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Loại</span>
                        <strong id="edit-summary-loai">{{ $thongBao->getLoaiLabel() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Ưu tiên</span>
                        <strong id="edit-summary-uu-tien">{{ $thongBao->getUuTienLabel() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Ghim</span>
                        <strong id="edit-summary-pin">{{ old('ghim', $thongBao->ghim) ? 'Có' : 'Không' }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Trạng thái gửi</span>
                        <strong>{{ $thongBao->getSendTrangThaiLabel() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Tệp hiện có</span>
                        <strong id="edit-existing-files">{{ $thongBao->tepDinhs->count() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Tệp mới</span>
                        <strong id="edit-new-files">0</strong>
                    </div>
                </div>

                <div class="nb-side-card">
                    <div class="nb-side-title"><i class="fas fa-circle-info"></i> Hướng dẫn nhanh</div>
                    <ul class="nb-side-checklist">
                        <li>Đánh dấu <strong>Xóa</strong> tại tệp hiện có nếu muốn gỡ khỏi thông báo.</li>
                        <li>Dùng vùng kéo-thả để thêm tệp đính kèm mới.</li>
                        <li>Nút <strong>{{ $isDraft ? 'Lưu nháp' : 'Lưu thay đổi' }}</strong> chỉ cập nhật nội dung, chưa gửi.</li>
                        @if ($canSendNow)
                            <li>Bấm <strong>Gửi thông báo ngay</strong> để phát hành cho người nhận đã cấu hình trước đó.</li>
                        @endif
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        window.EDIT_LOAI_LABELS = @json(App\Models\Interaction\ThongBao::loaiLabels());
        window.EDIT_UU_TIEN_LABELS = @json(App\Models\Interaction\ThongBao::uuTienLabels());
    </script>
    <script src="{{ asset('assets/admin/js/pages/thong-bao/edit.js') }}"></script>
@endsection
