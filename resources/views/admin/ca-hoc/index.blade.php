@extends('layouts.admin')

@section('title', 'Quản lý Ca Học')
@section('page-title', 'Ca Học')
@section('breadcrumb', 'Quản lý · Ca học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/ca-hoc/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ──────────────────────────────────────────── --}}
    <div class="ch-page-header">
        <div class="ch-page-title">
            <i class="fas fa-clock" style="color:#7c3aed"></i>
            Danh sách ca học
            <span>{{ $tongCa }} ca</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.lop-hoc.index') }}" class="btn-add-ch"
                style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                <i class="fas fa-chalkboard"></i> Lớp học
            </a>
            <button type="button" class="btn-add-ch" id="btnOpenAdd">
                <i class="fas fa-plus"></i> Thêm ca học
            </button>
        </div>
    </div>

    {{-- ── Stats Strip ──────────────────────────────────────────── --}}
    <div class="ch-stats">
        <div class="ch-stat-card">
            <div class="ch-stat-icon total"><i class="fas fa-clock"></i></div>
            <div>
                <div class="ch-stat-value" id="stat-tong">{{ number_format($tongCa) }}</div>
                <div class="ch-stat-label">Tổng ca học</div>
            </div>
        </div>
        <div class="ch-stat-card">
            <div class="ch-stat-icon active"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="ch-stat-value" id="stat-active">{{ number_format($dangHoatDong) }}</div>
                <div class="ch-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="ch-stat-card">
            <div class="ch-stat-icon stopped"><i class="fas fa-pause-circle"></i></div>
            <div>
                <div class="ch-stat-value" id="stat-stopped">{{ number_format($ngungHoatDong) }}</div>
                <div class="ch-stat-label">Ngừng hoạt động</div>
            </div>
        </div>
        <div class="ch-stat-card">
            <div class="ch-stat-icon usage"><i class="fas fa-chalkboard"></i></div>
            <div>
                <div class="ch-stat-value">{{ number_format($tongLopSuDung) }}</div>
                <div class="ch-stat-label">Lớp đang sử dụng</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ────────────────────────────────────────────── --}}
    <form action="{{ route('admin.ca-hoc.index') }}" method="GET" class="ch-filter-bar" id="ch-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm tên ca học..."
                value="{{ request('q') }}" autocomplete="off">
        </div>
        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Ngừng</option>
        </select>
        <select name="orderBy" onchange="this.form.submit()">
            <option value="gioBatDau" {{ request('orderBy', 'gioBatDau') === 'gioBatDau' ? 'selected' : '' }}>Theo giờ
            </option>
            <option value="tenCa" {{ request('orderBy') === 'tenCa' ? 'selected' : '' }}>Tên A–Z</option>
            <option value="caHocId" {{ request('orderBy') === 'caHocId' ? 'selected' : '' }}>Mới nhất</option>
        </select>
        <button type="submit" class="ch-btn-filter ch-btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.ca-hoc.index') }}" class="ch-btn-filter ch-btn-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table Card ────────────────────────────────────────────── --}}
    <div class="ch-card">
        <div class="ch-table-header">
            <div class="ch-table-title"><i class="fas fa-list me-2"></i>Danh sách ca học</div>
            <div style="font-size:.82rem;color:#94a3b8">
                Hiển thị {{ $caHocs->firstItem() ?? 0 }}–{{ $caHocs->lastItem() ?? 0 }} / {{ $caHocs->total() }}
            </div>
        </div>

        @if ($caHocs->isEmpty())
            <div class="ch-empty">
                <i class="fas fa-clock"></i>
                <p>Chưa có ca học nào. Hãy thêm ca học đầu tiên!</p>
                @if (request()->anyFilled(['q', 'trangThai']))
                    <a href="{{ route('admin.ca-hoc.index') }}" class="ch-btn-filter ch-btn-reset"
                        style="margin-top:10px;display:inline-flex">Xóa bộ lọc</a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="ch-table" id="caHocTable">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Tên ca</th>
                            <th>Giờ học</th>
                            <th>Thời lượng</th>
                            <th>Mô tả</th>
                            <th style="text-align:center">Số lớp</th>
                            <th style="text-align:center">Trạng thái</th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="caHocTbody">
                        @foreach ($caHocs as $ca)
                            @php
                                $start = \Carbon\Carbon::createFromFormat('H:i:s', $ca->gioBatDau);
                                $end = \Carbon\Carbon::createFromFormat('H:i:s', $ca->gioKetThuc);
                                $mins = $start->diffInMinutes($end);
                                $thoiLuong =
                                    $mins >= 60
                                        ? intdiv($mins, 60) . 'g' . ($mins % 60 > 0 ? $mins % 60 . 'p' : '')
                                        : $mins . 'p';
                                $gio = (int) $start->format('H');
                                $caType = $gio < 12 ? 'sang' : ($gio < 18 ? 'chieu' : 'toi');
                                $caIcon = $gio < 12 ? 'fa-sun' : ($gio < 18 ? 'fa-cloud-sun' : 'fa-moon');
                            @endphp
                            <tr id="row-{{ $ca->caHocId }}">
                                <td style="color:#94a3b8;font-size:.78rem">{{ $caHocs->firstItem() + $loop->index }}</td>
                                <td>
                                    <div style="font-weight:700;color:#1e1b4b;font-size:.92rem">{{ $ca->tenCa }}</div>
                                </td>
                                <td>
                                    <span class="time-badge {{ $caType }}">
                                        <i class="fas {{ $caIcon }}"></i>
                                        {{ substr($ca->gioBatDau, 0, 5) }} – {{ substr($ca->gioKetThuc, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="duration-pill">
                                        <i class="fas fa-hourglass-half" style="font-size:.7rem"></i>
                                        {{ $thoiLuong }}
                                    </span>
                                </td>
                                <td style="font-size:.82rem;color:#64748b;max-width:220px">
                                    {{ $ca->moTa ? \Illuminate\Support\Str::limit($ca->moTa, 60) : '—' }}
                                </td>
                                <td style="text-align:center">
                                    <span class="lop-count" id="lopCount-{{ $ca->caHocId }}">
                                        <i class="fas fa-chalkboard" style="font-size:.65rem"></i>
                                        {{ $ca->lop_hocs_count ?? 0 }}
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <label class="toggle-wrap">
                                        <label class="toggle-switch">
                                            <input type="checkbox" {{ $ca->trangThai ? 'checked' : '' }}
                                                onchange="toggleStatus({{ $ca->caHocId }}, this)">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span id="statusLabel-{{ $ca->caHocId }}"
                                            class="ch-badge {{ $ca->trangThai ? 'ch-badge-active' : 'ch-badge-inactive' }}">
                                            {{ $ca->trangThai ? 'Hoạt động' : 'Ngừng' }}
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="ch-actions">
                                        <button type="button" class="ch-btn-action ch-btn-edit" title="Sửa"
                                            onclick="openEdit({{ json_encode([
                                                'caHocId' => $ca->caHocId,
                                                'tenCa' => $ca->tenCa,
                                                'gioBatDau' => substr($ca->gioBatDau, 0, 5),
                                                'gioKetThuc' => substr($ca->gioKetThuc, 0, 5),
                                                'moTa' => $ca->moTa,
                                                'trangThai' => $ca->trangThai,
                                            ]) }})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="ch-btn-action ch-btn-del" title="Xóa"
                                            onclick="confirmDelete({{ $ca->caHocId }}, '{{ addslashes($ca->tenCa) }}', {{ $ca->lop_hocs_count ?? 0 }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($caHocs->hasPages())
                <div class="ch-pagination">
                    <div class="ch-pagination-info">
                        Trang {{ $caHocs->currentPage() }} / {{ $caHocs->lastPage() }}
                    </div>
                    {{ $caHocs->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

{{-- ── Modal Thêm / Sửa Ca Học ──────────────────────────────────── --}}
@section('modal')

    {{-- Modal backdrop --}}
    <div class="ch-modal-overlay" id="caHocModal">
        <div class="ch-modal" role="dialog" aria-modal="true">
            <div class="ch-modal-header">
                <div class="ch-modal-title" id="modalTitle">
                    <i class="fas fa-clock"></i>
                    <span id="modalTitleText">Thêm Ca Học</span>
                </div>
                <button class="ch-modal-close" onclick="closeModal()" title="Đóng"><i
                        class="fas fa-times"></i></button>
            </div>

            <form id="caHocForm" novalidate>
                @csrf
                <input type="hidden" id="editId" value="">

                {{-- Tên ca --}}
                <div class="ch-form-group">
                    <label class="ch-form-label" for="tenCa">Tên ca học <span class="req">*</span></label>
                    <input type="text" id="tenCa" name="tenCa" class="ch-form-control"
                        placeholder="VD: Ca sáng sớm, Ca chiều, Ca tối..." maxlength="100">
                    <div class="ch-error-msg" id="err-tenCa"></div>
                </div>

                {{-- Giờ bắt đầu & Kết thúc --}}
                <div class="ch-form-row">
                    <div class="ch-form-group">
                        <label class="ch-form-label" for="gioBatDau">Giờ bắt đầu <span class="req">*</span></label>
                        <input type="time" id="gioBatDau" name="gioBatDau" class="ch-form-control">
                        <div class="ch-error-msg" id="err-gioBatDau"></div>
                    </div>
                    <div class="ch-form-group">
                        <label class="ch-form-label" for="gioKetThuc">Giờ kết thúc <span class="req">*</span></label>
                        <input type="time" id="gioKetThuc" name="gioKetThuc" class="ch-form-control">
                        <div class="ch-error-msg" id="err-gioKetThuc"></div>
                    </div>
                </div>

                {{-- Preview thời lượng --}}
                <div id="durationPreview"
                    style="
                    display:none;
                    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
                    border:1px solid #bbf7d0;
                    border-radius:8px;
                    padding:10px 14px;
                    margin-top:-10px;
                    margin-bottom:14px;
                    font-size:.82rem;
                    color:#166534;
                    display:flex;align-items:center;gap:6px;
                ">
                    <i class="fas fa-hourglass-half"></i>
                    <span id="durationText"></span>
                </div>

                {{-- Mô tả --}}
                <div class="ch-form-group">
                    <label class="ch-form-label" for="moTa">Mô tả</label>
                    <textarea id="moTa" name="moTa" class="ch-form-control" rows="3"
                        placeholder="Ghi chú thêm về ca học này (không bắt buộc)..." style="resize:vertical;min-height:80px"
                        maxlength="500"></textarea>
                    <div class="ch-error-msg" id="err-moTa"></div>
                </div>

                {{-- Trạng thái --}}
                <div class="ch-form-group">
                    <label class="ch-form-label">Trạng thái <span class="req">*</span></label>
                    <div style="display:flex;gap:16px;align-items:center;margin-top:4px">
                        <label
                            style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.875rem;color:#374151">
                            <input type="radio" name="trangThai" value="1" checked style="accent-color:#7c3aed">
                            Hoạt động
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.875rem;color:#374151">
                            <input type="radio" name="trangThai" value="0" style="accent-color:#7c3aed"> Ngừng
                            hoạt động
                        </label>
                    </div>
                </div>

                <div class="ch-modal-footer">
                    <button type="button" class="ch-btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="ch-btn-save" id="btnSave">
                        <i class="fas fa-save"></i>
                        <span id="btnSaveText">Lưu ca học</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BASE = '/admin/ca-hoc';

        // ── Helpers ──────────────────────────────────────────────────
        function showToast(icon, msg) {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3200,
                timerProgressBar: true,
            }).fire({
                icon,
                title: msg
            });
        }

        function setLoading(on) {
            const btn = document.getElementById('btnSave');
            const txt = document.getElementById('btnSaveText');
            btn.disabled = on;
            txt.textContent = on ? 'Đang lưu...' : 'Lưu ca học';
            btn.querySelector('i').className = on ? 'fas fa-spinner fa-spin' : 'fas fa-save';
        }

        function clearErrors() {
            document.querySelectorAll('.ch-error-msg').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
            });
            document.querySelectorAll('.ch-form-control').forEach(el => el.classList.remove('is-invalid'));
        }

        function showErrors(errors) {
            Object.entries(errors).forEach(([field, msgs]) => {
                const err = document.getElementById('err-' + field);
                const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
                if (err) {
                    err.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                    err.classList.add('show');
                }
                if (input) input.classList.add('is-invalid');
            });
        }

        // ── Duration Preview ─────────────────────────────────────────
        function updateDuration() {
            const start = document.getElementById('gioBatDau').value;
            const end = document.getElementById('gioKetThuc').value;
            const preview = document.getElementById('durationPreview');
            const text = document.getElementById('durationText');
            if (!start || !end) {
                preview.style.display = 'none';
                return;
            }
            const [sh, sm] = start.split(':').map(Number);
            const [eh, em] = end.split(':').map(Number);
            const mins = (eh * 60 + em) - (sh * 60 + sm);
            if (mins <= 0) {
                preview.style.display = 'none';
                return;
            }
            const h = Math.floor(mins / 60),
                m = mins % 60;
            text.textContent = 'Thời lượng: ' + (h > 0 ? h + ' giờ ' : '') + (m > 0 ? m + ' phút' : '');
            preview.style.display = 'flex';
        }
        document.getElementById('gioBatDau').addEventListener('change', updateDuration);
        document.getElementById('gioKetThuc').addEventListener('change', updateDuration);

        // ── Modal Open/Close ─────────────────────────────────────────
        function openAdd() {
            clearErrors();
            document.getElementById('editId').value = '';
            document.getElementById('modalTitleText').textContent = 'Thêm Ca Học';
            document.getElementById('caHocForm').reset();
            document.getElementById('durationPreview').style.display = 'none';
            document.querySelector('[name="trangThai"][value="1"]').checked = true;
            document.getElementById('caHocModal').classList.add('open');
            setTimeout(() => document.getElementById('tenCa').focus(), 200);
        }

        function openEdit(ca) {
            clearErrors();
            document.getElementById('editId').value = ca.caHocId;
            document.getElementById('modalTitleText').textContent = 'Sửa Ca Học';
            document.getElementById('tenCa').value = ca.tenCa;
            document.getElementById('gioBatDau').value = ca.gioBatDau;
            document.getElementById('gioKetThuc').value = ca.gioKetThuc;
            document.getElementById('moTa').value = ca.moTa || '';
            document.querySelector(`[name="trangThai"][value="${ca.trangThai}"]`).checked = true;
            updateDuration();
            document.getElementById('caHocModal').classList.add('open');
            setTimeout(() => document.getElementById('tenCa').focus(), 200);
        }

        function closeModal() {
            document.getElementById('caHocModal').classList.remove('open');
        }

        // Close on backdrop click
        document.getElementById('caHocModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        // ESC key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });

        // Button Thêm
        document.getElementById('btnOpenAdd').addEventListener('click', openAdd);

        // ── Form Submit (Add/Edit) ────────────────────────────────────
        document.getElementById('caHocForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();
            setLoading(true);

            const id = document.getElementById('editId').value;
            const isEdit = !!id;
            const url = isEdit ? `${BASE}/${id}` : BASE;
            const method = isEdit ? 'PUT' : 'POST';

            const body = new URLSearchParams({
                _token: CSRF,
                tenCa: document.getElementById('tenCa').value.trim(),
                gioBatDau: document.getElementById('gioBatDau').value,
                gioKetThuc: document.getElementById('gioKetThuc').value,
                moTa: document.getElementById('moTa').value.trim(),
                trangThai: document.querySelector('[name="trangThai"]:checked')?.value ?? '1',
            });

            try {
                const resp = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: method === 'PUT' ? body.toString() + '&_method=PUT' : body.toString(),
                });

                // For PUT, Laravel needs _method override
                if (method === 'PUT') {
                    // Re-send as POST with _method=PUT
                    body.set('_method', 'PUT');
                    const resp2 = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: body.toString(),
                    });
                    const data2 = await resp2.json();
                    if (!resp2.ok) {
                        showErrors(data2.errors || {});
                        if (data2.message) showToast('error', data2.message);
                        setLoading(false);
                        return;
                    }
                    closeModal();
                    handleSuccess(data2, isEdit);
                    setLoading(false);
                    return;
                }

                const data = await resp.json();
                if (!resp.ok) {
                    showErrors(data.errors || {});
                    if (data.message) showToast('error', data.message);
                    setLoading(false);
                    return;
                }
                closeModal();
                handleSuccess(data, isEdit);
            } catch (err) {
                showToast('error', 'Lỗi kết nối máy chủ. Vui lòng thử lại.');
            }
            setLoading(false);
        });

        function handleSuccess(data, isEdit) {
            showToast('success', data.message);
            const ca = data.caHoc;
            if (isEdit) {
                updateRow(ca);
            } else {
                // Reload trang để cập nhật stats + table đúng nhất
                setTimeout(() => location.reload(), 900);
            }
        }

        // ── Cập nhật 1 hàng sau khi sửa ─────────────────────────────
        function updateRow(ca) {
            const row = document.getElementById('row-' + ca.caHocId);
            if (!row) {
                location.reload();
                return;
            }

            // Time badge
            const gio = parseInt(ca.gioBatDau.split(':')[0]);
            const type = gio < 12 ? 'sang' : (gio < 18 ? 'chieu' : 'toi');
            const icon = gio < 12 ? 'fa-sun' : (gio < 18 ? 'fa-cloud-sun' : 'fa-moon');

            row.querySelector('td:nth-child(2) div').textContent = ca.tenCa;
            row.querySelector('td:nth-child(3)').innerHTML = `
            <span class="time-badge ${type}">
                <i class="fas ${icon}"></i>
                ${ca.gioBatDau} – ${ca.gioKetThuc}
            </span>`;
            row.querySelector('td:nth-child(4)').innerHTML = `
            <span class="duration-pill">
                <i class="fas fa-hourglass-half" style="font-size:.7rem"></i>
                ${ca.thoiLuong}
            </span>`;
            row.querySelector('td:nth-child(5)').textContent = ca.moTa || '—';

            const label = document.getElementById('statusLabel-' + ca.caHocId);
            if (label) {
                label.textContent = ca.trangThai ? 'Hoạt động' : 'Ngừng';
                label.className = 'ch-badge ' + (ca.trangThai ? 'ch-badge-active' : 'ch-badge-inactive');
            }
            const toggle = row.querySelector('input[type="checkbox"]');
            if (toggle) toggle.checked = !!ca.trangThai;

            // Flash row
            row.style.transition = 'background .3s';
            row.style.background = '#f5f3ff';
            setTimeout(() => row.style.background = '', 1200);
        }

        // ── Toggle Trạng Thái ─────────────────────────────────────────
        async function toggleStatus(id, checkbox) {
            checkbox.disabled = true;
            try {
                const resp = await fetch(`${BASE}/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    showToast('error', data.message || 'Lỗi cập nhật');
                    checkbox.checked = !checkbox.checked;
                } else {
                    const label = document.getElementById('statusLabel-' + id);
                    if (label) {
                        label.textContent = data.trangThai ? 'Hoạt động' : 'Ngừng';
                        label.className = 'ch-badge ' + (data.trangThai ? 'ch-badge-active' : 'ch-badge-inactive');
                    }
                    showToast('success', data.message);
                }
            } catch {
                showToast('error', 'Lỗi kết nối.');
                checkbox.checked = !checkbox.checked;
            }
            checkbox.disabled = false;
        }

        // ── Xóa Ca Học ───────────────────────────────────────────────
        function confirmDelete(id, name, soLop) {
            if (soLop > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `Ca học <strong>${name}</strong> đang được sử dụng bởi <strong>${soLop} lớp học</strong>.<br>
                       <small style="color:#64748b">Hãy gỡ ca học khỏi các lớp trước khi xóa.</small>`,
                    icon: 'warning',
                    confirmButtonText: '<i class="fas fa-times me-1"></i> Đóng',
                    confirmButtonColor: '#6c757d',
                });
                return;
            }
            Swal.fire({
                title: 'Xóa ca học?',
                html: `Xóa <strong>${name}</strong>?<br><small style="color:#64748b">Hành động này không thể hoàn tác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(async result => {
                if (!result.isConfirmed) return;
                try {
                    const resp = await fetch(`${BASE}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: data.message,
                            confirmButtonColor: '#7c3aed'
                        });
                    } else {
                        showToast('success', data.message);
                        const row = document.getElementById('row-' + id);
                        if (row) {
                            row.style.transition = 'opacity .4s, transform .4s';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                row.remove();
                                checkEmpty();
                            }, 400);
                        }
                    }
                } catch {
                    showToast('error', 'Lỗi kết nối.');
                }
            });
        }

        function checkEmpty() {
            const tbody = document.getElementById('caHocTbody');
            if (tbody && tbody.querySelectorAll('tr').length === 0) {
                location.reload();
            }
        }

        // ── Search on Enter ───────────────────────────────────────────
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('ch-filter-form').submit();
        });
    </script>
@endsection
