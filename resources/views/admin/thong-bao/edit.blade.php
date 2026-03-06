@extends('layouts.admin')

@section('title', 'Chỉnh sửa Thông Báo')
@section('page-title', 'Chỉnh sửa Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('content')
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
                            <div class="nb-icon-tag"><i class="fas fa-paperclip"></i></div>
                            Quản lý tệp đính kèm
                        </div>

                        @if ($thongBao->tepDinhs->isNotEmpty())
                            <div class="nb-form-group">
                                <label class="nb-form-label">Tệp hiện có (đánh dấu để xóa khi lưu)</label>
                                <div class="attach-list">
                                    @foreach ($thongBao->tepDinhs as $tep)
                                        <div class="attach-item">
                                            <i class="fas {{ $tep->icon_class }} attach-icon"></i>
                                            <div class="attach-name" title="{{ $tep->tenFile }}">{{ $tep->tenFile }}</div>
                                            <div class="attach-size">{{ $tep->kich_thuoc_hien_thi }}</div>
                                            <a class="attach-dl" href="{{ $tep->url }}" target="_blank">Xem</a>
                                            <label class="attach-del-label">
                                                <input type="checkbox" name="xoa_tep[]" value="{{ $tep->tepDinhId }}"
                                                    class="edit-delete-attachment">
                                                Xóa
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="nb-form-group mb-0">
                            <label class="nb-form-label">
                                Tải thêm tệp mới
                                <span style="font-weight:400;color:#6b7280;font-size:.82rem;">(Tối đa 5 file mỗi lần cập nhật, mỗi file ≤ 10MB)</span>
                            </label>
                            <div class="nb-dropzone" id="edit-dropzone" onclick="document.getElementById('edit-tepDinhInput').click()"
                                ondragover="event.preventDefault();this.classList.add('drag-over')"
                                ondragleave="this.classList.remove('drag-over')" ondrop="handleEditDrop(event)">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:#a5b4fc;"></i>
                                <div style="font-size:.9rem;color:#6b7280;margin-top:.5rem;">Kéo thả file vào đây hoặc <span
                                        style="color:#6366f1;text-decoration:underline;cursor:pointer;">chọn file</span></div>
                                <div style="font-size:.78rem;color:#9ca3af;margin-top:.25rem;">PDF, Word, Excel, ảnh, ZIP…</div>
                            </div>
                            <input type="file" id="edit-tepDinhInput" name="tepDinhs[]" multiple style="display:none;"
                                onchange="previewEditFiles(this.files)">
                            <div id="edit-file-list" style="margin-top:.75rem;display:flex;flex-wrap:wrap;gap:.65rem;"></div>
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem; align-items:center;">
                        <a href="{{ route('admin.thong-bao.show', $thongBao->thongBaoId) }}" class="nb-btn nb-btn-secondary">
                            <i class="fas fa-times"></i> Huỷ
                        </a>
                        <div class="nb-spacer"></div>
                        <button type="submit" class="nb-btn nb-btn-primary" name="hanhDong" value="save">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        @if ((int) $thongBao->sendTrangThai !== \App\Models\Interaction\ThongBao::SEND_TRANG_THAI_DA_GUI)
                            <button type="submit" class="nb-btn nb-btn-success" name="hanhDong" value="send">
                                <i class="fas fa-paper-plane"></i> Gửi thông báo
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <aside class="nb-compose-aside">
                <div class="nb-side-card">
                    <div class="nb-side-title"><i class="fas fa-chart-simple"></i> Trạng thái hiện tại</div>
                    <div class="nb-side-kv">
                        <span>Thông báo ID</span>
                        <strong>#{{ $thongBao->thongBaoId }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Tiêu đề</span>
                        <strong id="edit-summary-title">{{ old('tieuDe', $thongBao->tieuDe) }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Loại / Ưu tiên</span>
                        <strong><span id="edit-summary-loai">{{ $thongBao->getLoaiLabel() }}</span> · <span id="edit-summary-uu-tien">{{ $thongBao->getUuTienLabel() }}</span></strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Đối tượng nhận</span>
                        <strong>{{ $thongBao->getDoiTuongLabel() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Tệp hiện có</span>
                        <strong id="edit-existing-files">{{ $thongBao->tepDinhs->count() }}</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Tệp mới thêm</span>
                        <strong id="edit-new-files">0</strong>
                    </div>
                    <div class="nb-side-kv">
                        <span>Ghim lên đầu</span>
                        <strong id="edit-summary-pin">{{ old('ghim', $thongBao->ghim) ? 'Có' : 'Không' }}</strong>
                    </div>
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
