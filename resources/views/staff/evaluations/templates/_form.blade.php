@php
    $rows = old('criteria', $criteriaRows instanceof \Illuminate\Support\Collection ? $criteriaRows->toArray() : $criteriaRows);
    $rows = array_values($rows ?: []);
    $totalCriteria = count($rows);
    $ratingCriteria = collect($rows)->filter(fn ($row) => ($row['loaiDuLieu'] ?? 'text') === 'rating')->count();
    $readonlyCriteria = collect($rows)->filter(fn ($row) => ($row['loaiDuLieu'] ?? 'text') === 'readonly_system')->count();
    $requiredCriteria = collect($rows)->filter(fn ($row) => !empty($row['batBuoc']))->count();
@endphp

<style>
    .tpl-studio {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .6fr);
        gap: 18px;
        align-items: start;
    }

    .tpl-main,
    .tpl-side {
        display: grid;
        gap: 16px;
    }

    .tpl-card {
        background: rgba(255, 255, 255, .96);
        border: 1px solid #dfe7f0;
        border-radius: 20px;
        box-shadow: 0 18px 34px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .tpl-card__head,
    .tpl-card__body,
    .tpl-card__foot {
        padding: 16px 18px;
    }

    .tpl-card__head {
        border-bottom: 1px solid #e8eef5;
        background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%);
    }

    .tpl-card__head h5,
    .tpl-card__head h6 {
        margin: 0;
        font-weight: 800;
        color: #0f172a;
    }

    .tpl-card__head p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: .84rem;
        line-height: 1.55;
    }

    .tpl-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .tpl-grid--wide {
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    }

    .tpl-field label {
        display: block;
        margin-bottom: 6px;
        font-size: .8rem;
        font-weight: 700;
        color: #334155;
    }

    .tpl-field input,
    .tpl-field textarea,
    .tpl-field select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #cfd9e5;
        border-radius: 13px;
        padding: 10px 12px;
        font-size: .9rem;
        background: #fff;
    }

    .tpl-field textarea {
        min-height: 96px;
        resize: vertical;
    }

    .tpl-help {
        margin-top: 6px;
        color: #64748b;
        font-size: .78rem;
        line-height: 1.5;
    }

    .tpl-toggle-list,
    .tpl-summary-list,
    .tpl-quick-list {
        display: grid;
        gap: 10px;
    }

    .tpl-toggle {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px;
        border: 1px solid #dbe5ef;
        border-radius: 16px;
        background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
    }

    .tpl-toggle strong {
        display: block;
        color: #0f172a;
    }

    .tpl-toggle span {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: .82rem;
    }

    .tpl-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .tpl-toolbar__left,
    .tpl-toolbar__right,
    .tpl-inline,
    .tpl-row__meta,
    .tpl-row__actions,
    .tpl-quick-actions,
    .tpl-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .tpl-chip,
    .tpl-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1;
    }

    .tpl-chip {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .tpl-badge {
        background: #f8fafc;
        border: 1px solid #dbe5ef;
        color: #334155;
    }

    .tpl-badge--rating {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .tpl-badge--readonly {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #15803d;
    }

    .tpl-badge--required {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #c2410c;
    }

    .tpl-drag-handle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 10px;
        border-radius: 10px;
        border: 1px dashed #cbd5e1;
        background: #fff;
        color: #475569;
        font-size: .76rem;
        font-weight: 700;
        cursor: grab;
        user-select: none;
    }

    .tpl-drag-handle:active {
        cursor: grabbing;
    }

    .tpl-btn {
        border: 1px solid transparent;
        border-radius: 12px;
        min-height: 38px;
        padding: 0 12px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .tpl-btn:hover {
        transform: translateY(-1px);
    }

    .tpl-btn--primary {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: #fff;
        box-shadow: 0 10px 18px rgba(20, 184, 166, .18);
    }

    .tpl-btn--secondary {
        background: #fff;
        border-color: #d7e1ec;
        color: #334155;
    }

    .tpl-btn--soft {
        background: #eff6ff;
        border-color: #dbeafe;
        color: #1d4ed8;
    }

    .tpl-btn--danger {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }

    .tpl-summary-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tpl-summary {
        padding: 12px;
        border: 1px solid #e3ebf4;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfe 100%);
    }

    .tpl-summary__label {
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .tpl-summary__value {
        margin-top: 7px;
        color: #0f172a;
        font-size: 1.35rem;
        line-height: 1;
        font-weight: 800;
    }

    .tpl-list {
        display: grid;
        gap: 12px;
    }

    .tpl-row {
        border: 1px solid #dfe7f0;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        overflow: hidden;
        transition: box-shadow .16s ease, border-color .16s ease, opacity .16s ease;
    }

    .tpl-row[open] {
        box-shadow: 0 14px 26px rgba(15, 23, 42, .06);
    }

    .tpl-row.is-dragging {
        opacity: .55;
        border-color: #60a5fa;
        box-shadow: 0 18px 28px rgba(59, 130, 246, .14);
    }

    .tpl-row__summary {
        list-style: none;
        cursor: pointer;
        display: grid;
        gap: 10px;
        padding: 14px 16px;
        background: linear-gradient(180deg, #fbfdff 0%, #f4f8fc 100%);
    }

    .tpl-row__summary::-webkit-details-marker {
        display: none;
    }

    .tpl-row__headline {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
    }

    .tpl-row__title {
        display: grid;
        gap: 3px;
    }

    .tpl-row__title strong {
        font-size: .95rem;
        color: #0f172a;
    }

    .tpl-row__title span {
        color: #64748b;
        font-size: .8rem;
    }

    .tpl-row__preview {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .tpl-mini {
        padding: 10px 11px;
        border-radius: 14px;
        border: 1px solid #e3ebf4;
        background: rgba(255, 255, 255, .86);
    }

    .tpl-mini__label {
        color: #64748b;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .tpl-mini__value {
        margin-top: 5px;
        color: #0f172a;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .tpl-row__body {
        padding: 16px;
        display: grid;
        gap: 14px;
        border-top: 1px solid #e8eef5;
    }

    .tpl-checks {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .tpl-checks label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-weight: 700;
        color: #334155;
        font-size: .84rem;
    }

    .tpl-tip {
        padding: 12px;
        border-radius: 16px;
        background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
        border: 1px solid #dbeafe;
        color: #1e3a8a;
        font-size: .82rem;
        line-height: 1.55;
    }

    .tpl-side .tpl-card {
        position: sticky;
        top: 88px;
    }

    .tpl-empty {
        padding: 26px 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        text-align: center;
    }

    .tpl-empty strong {
        display: block;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .tpl-empty p {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
    }

    .tpl-sticky {
        position: sticky;
        bottom: 12px;
        z-index: 8;
    }

    @media (max-width: 1200px) {
        .tpl-studio,
        .tpl-grid,
        .tpl-grid--wide,
        .tpl-row__preview,
        .tpl-summary-list {
            grid-template-columns: 1fr;
        }

        .tpl-side .tpl-card {
            position: static;
        }
    }
</style>

<div class="tpl-studio">
    <div class="tpl-main">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-0">
                <div class="fw-semibold mb-1">Không thể lưu mẫu báo cáo.</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="tpl-card">
            <div class="tpl-card__head">
                <h5>1. Thông tin nền của mẫu</h5>
                <p>Đặt tên rõ ràng, mô tả phạm vi áp dụng và cấu hình trạng thái để staff chọn đúng mẫu khi tạo đợt.</p>
            </div>
            <div class="tpl-card__body">
                <div class="tpl-grid tpl-grid--wide">
                    <div class="tpl-field">
                        <label for="tenMau">Tên mẫu</label>
                        <input id="tenMau" name="tenMau" value="{{ old('tenMau', $template->tenMau) }}" placeholder="Ví dụ: Mẫu giữa khóa IELTS Intensive 6.5+" required>
                    </div>
                    <div class="tpl-field">
                        <label for="phienBan">Phiên bản</label>
                        <input id="phienBan" name="phienBan" value="{{ old('phienBan', $template->phienBan ?: '1.0') }}" placeholder="1.0">
                    </div>
                </div>

                <div class="tpl-field mt-3">
                    <label for="moTa">Mô tả sử dụng</label>
                    <textarea id="moTa" name="moTa" placeholder="Ghi rõ mẫu dùng cho loại lớp nào, giai đoạn nào, điểm khác biệt so với các mẫu còn lại...">{{ old('moTa', $template->moTa) }}</textarea>
                    <div class="tpl-help">Mô tả tốt sẽ giúp staff chọn đúng mẫu mà không cần mở vào chi tiết.</div>
                </div>

                <div class="tpl-toggle-list mt-3">
                    <label class="tpl-toggle">
                        <input type="checkbox" name="kichHoat" value="1" @checked(old('kichHoat', (bool) $template->kichHoat))>
                        <span>
                            <strong>Kích hoạt để staff có thể chọn khi tạo đợt đánh giá</strong>
                            <span>Mẫu đang kích hoạt sẽ xuất hiện ngay trong danh sách chọn mẫu.</span>
                        </span>
                    </label>
                    <label class="tpl-toggle">
                        <input type="checkbox" name="macDinh" value="1" @checked(old('macDinh', (bool) $template->macDinh))>
                        <span>
                            <strong>Đặt làm mẫu mặc định của hệ thống</strong>
                            <span>Dùng khi staff tạo đợt nhưng không chọn một mẫu riêng.</span>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        <section class="tpl-card">
            <div class="tpl-card__head">
                <h5>2. Studio tiêu chí đánh giá</h5>
                <p>Mỗi tiêu chí hiển thị như một khối cấu hình độc lập, có preview nhanh và thao tác điều khiển rõ ràng.</p>
            </div>
            <div class="tpl-card__body">
                <div class="tpl-toolbar">
                    <div class="tpl-toolbar__left">
                        <span class="tpl-chip">Tổng tiêu chí: <span id="criteriaCount">{{ $totalCriteria }}</span></span>
                        <span class="tpl-chip">Rating: <span id="ratingCount">{{ $ratingCriteria }}</span></span>
                        <span class="tpl-chip">Readonly: <span id="readonlyCount">{{ $readonlyCriteria }}</span></span>
                        <span class="tpl-chip">Bắt buộc: <span id="requiredCount">{{ $requiredCriteria }}</span></span>
                    </div>
                    <div class="tpl-toolbar__right">
                        <button type="button" class="tpl-btn tpl-btn--secondary" id="expandAllCriteria">Mở tất cả</button>
                        <button type="button" class="tpl-btn tpl-btn--secondary" id="collapseAllCriteria">Thu gọn tất cả</button>
                        <button type="button" class="tpl-btn tpl-btn--soft" data-add-preset="rating">Thêm tiêu chí rating</button>
                        <button type="button" class="tpl-btn tpl-btn--secondary" data-add-preset="text">Thêm tiêu chí văn bản</button>
                        <button type="button" class="tpl-btn tpl-btn--secondary" data-add-preset="readonly_system">Thêm dữ liệu hệ thống</button>
                        <button type="button" class="tpl-btn tpl-btn--primary" id="addCriterionBtn">Thêm tiêu chí trống</button>
                    </div>
                </div>

                <div class="tpl-list" id="criteriaList">
                    @forelse ($rows as $index => $row)
                        @php
                            $type = $row['loaiDuLieu'] ?? 'text';
                            $typeLabel = $criterionTypeOptions[$type] ?? $type;
                            $group = $row['nhom'] ?? 'Chưa phân nhóm';
                            $title = $row['tenTieuChi'] ?: 'Tiêu chí mới';
                            $code = $row['maTieuChi'] ?: 'ma_tieu_chi';
                        @endphp
                        <details class="tpl-row" data-criterion-row open>
                            <summary class="tpl-row__summary">
                                <div class="tpl-row__headline">
                                    <div class="tpl-row__title">
                                        <strong data-criterion-label>{{ $title }}</strong>
                                        <span data-criterion-code>{{ $code }}</span>
                                    </div>
                                    <div class="tpl-row__meta">
                                        <span class="tpl-drag-handle" draggable="true" data-drag-handle>Kéo thả</span>
                                        <span class="tpl-badge {{ $type === 'rating' ? 'tpl-badge--rating' : ($type === 'readonly_system' ? 'tpl-badge--readonly' : '') }}" data-criterion-type>{{ $typeLabel }}</span>
                                        @if (!empty($row['batBuoc']))
                                            <span class="tpl-badge tpl-badge--required" data-required-badge>Bắt buộc</span>
                                        @else
                                            <span class="tpl-badge tpl-badge--required d-none" data-required-badge>Bắt buộc</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="tpl-row__preview">
                                    <div class="tpl-mini">
                                        <div class="tpl-mini__label">Nhóm</div>
                                        <div class="tpl-mini__value" data-preview-group>{{ $group }}</div>
                                    </div>
                                    <div class="tpl-mini">
                                        <div class="tpl-mini__label">Loại dữ liệu</div>
                                        <div class="tpl-mini__value" data-preview-type>{{ $typeLabel }}</div>
                                    </div>
                                    <div class="tpl-mini">
                                        <div class="tpl-mini__label">Thứ tự</div>
                                        <div class="tpl-mini__value" data-preview-order>{{ $row['thuTu'] ?? (($index + 1) * 10) }}</div>
                                    </div>
                                    <div class="tpl-mini">
                                        <div class="tpl-mini__label">Tùy chọn</div>
                                        <div class="tpl-mini__value" data-preview-extra>
                                            {{ $type === 'rating' ? 'Có thang mức' : (!empty($row['isReadonly']) ? 'Chỉ đọc' : 'Tự nhập') }}
                                        </div>
                                    </div>
                                </div>
                            </summary>

                            <div class="tpl-row__body">
                                <div class="tpl-row__actions">
                                    <button type="button" class="tpl-btn tpl-btn--soft" data-duplicate-row>Nhân bản</button>
                                    <button type="button" class="tpl-btn tpl-btn--danger" data-remove-row>Xóa</button>
                                </div>

                                <div class="tpl-grid">
                                    <div class="tpl-field">
                                        <label>Tên nhóm</label>
                                        <input name="criteria[{{ $index }}][nhom]" value="{{ $row['nhom'] ?? '' }}" placeholder="Ví dụ: Kết luận" data-input-group>
                                    </div>
                                    <div class="tpl-field">
                                        <label>Mã tiêu chí</label>
                                        <input name="criteria[{{ $index }}][maTieuChi]" value="{{ $row['maTieuChi'] ?? '' }}" placeholder="progress_level" data-input-code>
                                    </div>
                                    <div class="tpl-field">
                                        <label>Tên hiển thị</label>
                                        <input name="criteria[{{ $index }}][tenTieuChi]" value="{{ $row['tenTieuChi'] ?? '' }}" placeholder="Mức độ tiến bộ" data-input-title>
                                    </div>
                                    <div class="tpl-field">
                                        <label>Thứ tự</label>
                                        <input type="number" step="1" name="criteria[{{ $index }}][thuTu]" value="{{ $row['thuTu'] ?? (($index + 1) * 10) }}" data-input-order>
                                    </div>
                                </div>

                                <div class="tpl-grid tpl-grid--wide">
                                    <div class="tpl-field">
                                        <label>Loại dữ liệu</label>
                                        <select name="criteria[{{ $index }}][loaiDuLieu]" data-input-type>
                                            @foreach ($criterionTypeOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="tpl-field">
                                        <label>Gợi ý nhập liệu</label>
                                        <input name="criteria[{{ $index }}][goiY]" value="{{ $row['goiY'] ?? '' }}" placeholder="Ví dụ: Tập trung vào sự tiến bộ thực tế, dẫn chứng ngắn gọn.">
                                    </div>
                                </div>

                                <div class="tpl-grid tpl-grid--wide">
                                    <div class="tpl-field">
                                        <label>Danh sách mức / options</label>
                                        <textarea name="criteria[{{ $index }}][danhSachMucText]" data-options-field placeholder="Mỗi dòng là một mức đánh giá&#10;Chưa đạt&#10;Đạt tối thiểu&#10;Khá&#10;Tốt&#10;Rất tốt">{{ $row['danhSachMucText'] ?? '' }}</textarea>
                                        <div class="tpl-help">Chỉ dùng cho loại `rating`. Khi chọn `rating`, hệ thống có thể tự nạp bộ mức mặc định.</div>
                                    </div>
                                    <div class="tpl-field">
                                        <label>Cấu hình nhanh</label>
                                        <div class="tpl-checks">
                                            <label>
                                                <input type="checkbox" name="criteria[{{ $index }}][batBuoc]" value="1" @checked($row['batBuoc'] ?? false) data-input-required>
                                                Bắt buộc
                                            </label>
                                            <label>
                                                <input type="checkbox" name="criteria[{{ $index }}][isReadonly]" value="1" @checked($row['isReadonly'] ?? false) data-input-readonly>
                                                Chỉ đọc
                                            </label>
                                        </div>
                                        <div class="tpl-help">Nếu chọn `readonly_system`, nên bật `Chỉ đọc` để tiêu chí này lấy dữ liệu từ hệ thống thay vì giáo viên nhập tay.</div>
                                    </div>
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="tpl-empty" id="criteriaEmptyState">
                            <strong>Chưa có tiêu chí nào trong mẫu.</strong>
                            <p>Bắt đầu bằng một tiêu chí rating hoặc thêm một tiêu chí trống để dựng rubric theo ý bạn.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <div class="tpl-sticky">
            <section class="tpl-card">
                <div class="tpl-card__body tpl-footer justify-content-between">
                    <div class="text-muted small">Giữ mã tiêu chí ổn định theo thời gian để việc sao chép dữ liệu và so sánh giữa các đợt luôn nhất quán.</div>
                    <div class="tpl-inline">
                        <a href="{{ route('staff.evaluations.templates.index') }}" class="tpl-btn tpl-btn--secondary">Hủy</a>
                        <button type="submit" class="tpl-btn tpl-btn--primary">{{ $submitLabel }}</button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <aside class="tpl-side">
        <section class="tpl-card">
            <div class="tpl-card__head">
                <h6>Tóm tắt mẫu</h6>
                <p>Giúp bạn nhìn nhanh cấu trúc hiện tại trước khi lưu.</p>
            </div>
            <div class="tpl-card__body">
                <div class="tpl-summary-list">
                    <div class="tpl-summary">
                        <div class="tpl-summary__label">Tổng tiêu chí</div>
                        <div class="tpl-summary__value" id="sidebarCriteriaCount">{{ $totalCriteria }}</div>
                    </div>
                    <div class="tpl-summary">
                        <div class="tpl-summary__label">Tiêu chí rating</div>
                        <div class="tpl-summary__value" id="sidebarRatingCount">{{ $ratingCriteria }}</div>
                    </div>
                    <div class="tpl-summary">
                        <div class="tpl-summary__label">Readonly</div>
                        <div class="tpl-summary__value" id="sidebarReadonlyCount">{{ $readonlyCriteria }}</div>
                    </div>
                    <div class="tpl-summary">
                        <div class="tpl-summary__label">Bắt buộc</div>
                        <div class="tpl-summary__value" id="sidebarRequiredCount">{{ $requiredCriteria }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="tpl-card">
            <div class="tpl-card__head">
                <h6>Thêm nhanh</h6>
                <p>Một vài điểm bắt đầu phổ biến để bạn dựng rubric nhanh hơn.</p>
            </div>
            <div class="tpl-card__body">
                <div class="tpl-quick-list">
                    <div class="tpl-tip">
                        <strong class="d-block mb-1">Tiêu chí năng lực</strong>
                        Dùng `rating` cho speaking, writing, attitude, participation hoặc progress level.
                    </div>
                    <div class="tpl-tip">
                        <strong class="d-block mb-1">Tiêu chí nhận xét mở</strong>
                        Dùng `text` cho phần kết luận, định hướng học tiếp theo, đề xuất cho phụ huynh.
                    </div>
                    <div class="tpl-tip">
                        <strong class="d-block mb-1">Dữ liệu hệ thống</strong>
                        Dùng `readonly_system` cho attendance rate, absent count hoặc level hiện tại.
                    </div>
                    <div class="tpl-quick-actions">
                        <button type="button" class="tpl-btn tpl-btn--soft w-100" data-add-preset="rating">Thêm tiêu chí rating</button>
                        <button type="button" class="tpl-btn tpl-btn--secondary w-100" data-add-preset="text">Thêm tiêu chí text</button>
                        <button type="button" class="tpl-btn tpl-btn--secondary w-100" data-add-preset="readonly_system">Thêm dữ liệu hệ thống</button>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</div>

@section('script')
    @parent
    <script>
        (() => {
            const list = document.getElementById('criteriaList');
            const addBtn = document.getElementById('addCriterionBtn');
            const expandAllBtn = document.getElementById('expandAllCriteria');
            const collapseAllBtn = document.getElementById('collapseAllCriteria');
            const countTargets = [
                document.getElementById('criteriaCount'),
                document.getElementById('sidebarCriteriaCount'),
            ];
            const ratingTargets = [
                document.getElementById('ratingCount'),
                document.getElementById('sidebarRatingCount'),
            ];
            const readonlyTargets = [
                document.getElementById('readonlyCount'),
                document.getElementById('sidebarReadonlyCount'),
            ];
            const requiredTargets = [
                document.getElementById('requiredCount'),
                document.getElementById('sidebarRequiredCount'),
            ];
            const defaultOptions = @json(implode("\n", $defaultRatingOptions));
            const typeOptions = @json($criterionTypeOptions);

            if (!list || !addBtn) {
                return;
            }

            let draggedRow = null;

            const slugify = value => value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .slice(0, 50);

            const getTypeLabel = value => typeOptions[value] || value;

            const updateCounters = () => {
                const rows = [...list.querySelectorAll('[data-criterion-row]')];
                const total = rows.length;
                const rating = rows.filter(row => row.querySelector('[data-input-type]')?.value === 'rating').length;
                const readonly = rows.filter(row => row.querySelector('[data-input-type]')?.value === 'readonly_system').length;
                const required = rows.filter(row => row.querySelector('[data-input-required]')?.checked).length;

                countTargets.forEach(el => el && (el.textContent = total));
                ratingTargets.forEach(el => el && (el.textContent = rating));
                readonlyTargets.forEach(el => el && (el.textContent = readonly));
                requiredTargets.forEach(el => el && (el.textContent = required));
            };

            const getRows = () => [...list.querySelectorAll('[data-criterion-row]')];

            const refreshIndexes = (syncOrder = false) => {
                [...list.querySelectorAll('[data-criterion-row]')].forEach((row, index) => {
                    row.querySelectorAll('input, textarea, select').forEach(field => {
                        const current = field.getAttribute('name');
                        if (current) {
                            field.setAttribute('name', current.replace(/criteria\[\d+\]/, `criteria[${index}]`));
                        }
                    });

                    if (syncOrder) {
                        const orderInput = row.querySelector('[data-input-order]');
                        if (orderInput) {
                            orderInput.value = (index + 1) * 10;
                        }
                    }

                    syncRowPreview(row);
                });

                updateCounters();
            };

            const updateEmptyState = () => {
                const rowCount = getRows().length;
                const emptyState = document.getElementById('criteriaEmptyState');
                if (emptyState) {
                    emptyState.style.display = rowCount === 0 ? 'block' : 'none';
                }
            };

            const syncRowPreview = row => {
                const titleInput = row.querySelector('[data-input-title]');
                const codeInput = row.querySelector('[data-input-code]');
                const groupInput = row.querySelector('[data-input-group]');
                const typeInput = row.querySelector('[data-input-type]');
                const orderInput = row.querySelector('[data-input-order]');
                const requiredInput = row.querySelector('[data-input-required]');
                const readonlyInput = row.querySelector('[data-input-readonly]');
                const optionsField = row.querySelector('[data-options-field]');

                row.querySelector('[data-criterion-label]').textContent = titleInput.value.trim() || 'Tiêu chí mới';
                row.querySelector('[data-criterion-code]').textContent = codeInput.value.trim() || 'ma_tieu_chi';
                row.querySelector('[data-preview-group]').textContent = groupInput.value.trim() || 'Chưa phân nhóm';
                row.querySelector('[data-preview-type]').textContent = getTypeLabel(typeInput.value);
                row.querySelector('[data-preview-order]').textContent = orderInput.value || '0';

                const typeBadge = row.querySelector('[data-criterion-type]');
                typeBadge.textContent = getTypeLabel(typeInput.value);
                typeBadge.className = 'tpl-badge';

                if (typeInput.value === 'rating') {
                    typeBadge.classList.add('tpl-badge--rating');
                } else if (typeInput.value === 'readonly_system') {
                    typeBadge.classList.add('tpl-badge--readonly');
                }

                const requiredBadge = row.querySelector('[data-required-badge]');
                if (requiredInput.checked) {
                    requiredBadge.classList.remove('d-none');
                } else {
                    requiredBadge.classList.add('d-none');
                }

                let extra = 'Tự nhập';
                if (typeInput.value === 'rating') {
                    extra = optionsField.value.trim() ? `${optionsField.value.trim().split('\n').filter(Boolean).length} mức đánh giá` : 'Có thang mức';
                } else if (readonlyInput.checked || typeInput.value === 'readonly_system') {
                    extra = 'Chỉ đọc';
                }

                row.querySelector('[data-preview-extra]').textContent = extra;
            };

            const getDragAfterElement = y => {
                const elements = getRows().filter(row => row !== draggedRow);

                return elements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return { offset, element: child };
                    }

                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            };

            const setAllDetailsState = isOpen => {
                getRows().forEach(row => {
                    row.open = isOpen;
                });
            };

            const bindRow = row => {
                const titleInput = row.querySelector('[data-input-title]');
                const codeInput = row.querySelector('[data-input-code]');
                const groupInput = row.querySelector('[data-input-group]');
                const typeInput = row.querySelector('[data-input-type]');
                const orderInput = row.querySelector('[data-input-order]');
                const requiredInput = row.querySelector('[data-input-required]');
                const readonlyInput = row.querySelector('[data-input-readonly]');
                const optionsField = row.querySelector('[data-options-field]');
                const dragHandle = row.querySelector('[data-drag-handle]');

                let codeTouched = Boolean(codeInput.value.trim());

                titleInput.addEventListener('input', () => {
                    if (!codeTouched) {
                        codeInput.value = slugify(titleInput.value) || '';
                    }
                    syncRowPreview(row);
                });

                codeInput.addEventListener('input', () => {
                    codeTouched = true;
                    syncRowPreview(row);
                });

                [groupInput, typeInput, orderInput, requiredInput, readonlyInput, optionsField].forEach(field => {
                    field.addEventListener('input', () => syncRowPreview(row));
                    field.addEventListener('change', () => {
                        if (field === typeInput) {
                            if (typeInput.value === 'rating' && !optionsField.value.trim()) {
                                optionsField.value = defaultOptions;
                            }

                            if (typeInput.value === 'readonly_system') {
                                readonlyInput.checked = true;
                            }
                        }

                        syncRowPreview(row);
                        updateCounters();
                    });
                });

                row.querySelector('[data-remove-row]').addEventListener('click', () => {
                    row.remove();
                    refreshIndexes(true);
                    updateEmptyState();
                });

                row.querySelector('[data-duplicate-row]').addEventListener('click', () => {
                    const clone = row.cloneNode(true);
                    clone.open = true;
                    const cloneCode = clone.querySelector('[data-input-code]');
                    if (cloneCode) {
                        cloneCode.value = (cloneCode.value || 'criterion') + '_copy';
                    }
                    list.insertBefore(clone, row.nextSibling);
                    bindRow(clone);
                    refreshIndexes(true);
                    updateEmptyState();
                });

                if (dragHandle) {
                    dragHandle.addEventListener('click', event => {
                        event.preventDefault();
                        event.stopPropagation();
                    });

                    dragHandle.addEventListener('mousedown', event => {
                        event.stopPropagation();
                    });

                    dragHandle.addEventListener('dragstart', event => {
                        draggedRow = row;
                        row.classList.add('is-dragging');
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', codeInput.value || 'criterion');
                    });

                    dragHandle.addEventListener('dragend', () => {
                        row.classList.remove('is-dragging');
                        draggedRow = null;
                        refreshIndexes(true);
                    });
                }

                syncRowPreview(row);
            };

            const createRow = presetType => {
                const index = list.querySelectorAll('[data-criterion-row]').length;
                const type = presetType || 'text';
                const typeSelect = Object.entries(typeOptions).map(([value, label]) =>
                    `<option value="${value}" ${value === type ? 'selected' : ''}>${label}</option>`
                ).join('');

                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <details class="tpl-row" data-criterion-row open>
                        <summary class="tpl-row__summary">
                            <div class="tpl-row__headline">
                                <div class="tpl-row__title">
                                    <strong data-criterion-label>Tiêu chí mới</strong>
                                    <span data-criterion-code>criterion_${index + 1}</span>
                                </div>
                                <div class="tpl-row__meta">
                                    <span class="tpl-drag-handle" draggable="true" data-drag-handle>Kéo thả</span>
                                    <span class="tpl-badge ${type === 'rating' ? 'tpl-badge--rating' : (type === 'readonly_system' ? 'tpl-badge--readonly' : '')}" data-criterion-type>${getTypeLabel(type)}</span>
                                    <span class="tpl-badge tpl-badge--required d-none" data-required-badge>Bắt buộc</span>
                                </div>
                            </div>
                            <div class="tpl-row__preview">
                                <div class="tpl-mini"><div class="tpl-mini__label">Nhóm</div><div class="tpl-mini__value" data-preview-group>Chưa phân nhóm</div></div>
                                <div class="tpl-mini"><div class="tpl-mini__label">Loại dữ liệu</div><div class="tpl-mini__value" data-preview-type>${getTypeLabel(type)}</div></div>
                                <div class="tpl-mini"><div class="tpl-mini__label">Thứ tự</div><div class="tpl-mini__value" data-preview-order>${(index + 1) * 10}</div></div>
                                <div class="tpl-mini"><div class="tpl-mini__label">Tùy chọn</div><div class="tpl-mini__value" data-preview-extra>${type === 'rating' ? 'Có thang mức' : (type === 'readonly_system' ? 'Chỉ đọc' : 'Tự nhập')}</div></div>
                            </div>
                        </summary>

                        <div class="tpl-row__body">
                            <div class="tpl-row__actions">
                                <button type="button" class="tpl-btn tpl-btn--soft" data-duplicate-row>Nhân bản</button>
                                <button type="button" class="tpl-btn tpl-btn--danger" data-remove-row>Xóa</button>
                            </div>

                            <div class="tpl-grid">
                                <div class="tpl-field">
                                    <label>Tên nhóm</label>
                                    <input name="criteria[${index}][nhom]" placeholder="Ví dụ: Kết luận" data-input-group>
                                </div>
                                <div class="tpl-field">
                                    <label>Mã tiêu chí</label>
                                    <input name="criteria[${index}][maTieuChi]" value="criterion_${index + 1}" placeholder="progress_level" data-input-code>
                                </div>
                                <div class="tpl-field">
                                    <label>Tên hiển thị</label>
                                    <input name="criteria[${index}][tenTieuChi]" placeholder="Mức độ tiến bộ" data-input-title>
                                </div>
                                <div class="tpl-field">
                                    <label>Thứ tự</label>
                                    <input type="number" step="1" name="criteria[${index}][thuTu]" value="${(index + 1) * 10}" data-input-order>
                                </div>
                            </div>

                            <div class="tpl-grid tpl-grid--wide">
                                <div class="tpl-field">
                                    <label>Loại dữ liệu</label>
                                    <select name="criteria[${index}][loaiDuLieu]" data-input-type>${typeSelect}</select>
                                </div>
                                <div class="tpl-field">
                                    <label>Gợi ý nhập liệu</label>
                                    <input name="criteria[${index}][goiY]" placeholder="Mẹo cho giáo viên khi nhập tiêu chí này">
                                </div>
                            </div>

                            <div class="tpl-grid tpl-grid--wide">
                                <div class="tpl-field">
                                    <label>Danh sách mức / options</label>
                                    <textarea name="criteria[${index}][danhSachMucText]" data-options-field placeholder="Mỗi dòng là một mức đánh giá">${type === 'rating' ? defaultOptions : ''}</textarea>
                                    <div class="tpl-help">Chỉ dùng cho loại \`rating\`.</div>
                                </div>
                                <div class="tpl-field">
                                    <label>Cấu hình nhanh</label>
                                    <div class="tpl-checks">
                                        <label><input type="checkbox" name="criteria[${index}][batBuoc]" value="1" data-input-required>Bắt buộc</label>
                                        <label><input type="checkbox" name="criteria[${index}][isReadonly]" value="1" data-input-readonly ${type === 'readonly_system' ? 'checked' : ''}>Chỉ đọc</label>
                                    </div>
                                    <div class="tpl-help">\`readonly_system\` sẽ tự khóa nhập liệu khi phát sinh báo cáo.</div>
                                </div>
                            </div>
                        </div>
                    </details>
                `;

                const row = wrapper.firstElementChild;
                list.appendChild(row);
                bindRow(row);
                refreshIndexes(true);
                updateEmptyState();
            };

            [...list.querySelectorAll('[data-criterion-row]')].forEach(bindRow);
            refreshIndexes();
            updateEmptyState();

            addBtn.addEventListener('click', () => createRow('text'));
            document.querySelectorAll('[data-add-preset]').forEach(button => {
                button.addEventListener('click', () => createRow(button.dataset.addPreset || 'text'));
            });

            list.addEventListener('dragover', event => {
                if (!draggedRow) {
                    return;
                }

                event.preventDefault();
                const afterElement = getDragAfterElement(event.clientY);

                if (!afterElement) {
                    list.appendChild(draggedRow);
                } else {
                    list.insertBefore(draggedRow, afterElement);
                }
            });

            list.addEventListener('drop', event => {
                if (!draggedRow) {
                    return;
                }

                event.preventDefault();
                refreshIndexes(true);
            });

            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', () => setAllDetailsState(true));
            }

            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', () => setAllDetailsState(false));
            }
        })();
    </script>
@endsection
