@extends('layouts.admin')

@section('title', 'Cấu Hình Hệ Thống')
@section('page-title', 'Cấu Hình Hệ Thống')
@section('breadcrumb', 'Hệ thống · Cấu hình')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/cau-hinh/index.css') }}">
@endsection

@section('content')

@php
    $nhomMeta    = $nhomMeta ?? \App\Models\CauHinhHeThong::labelNhom();
    $colorMap    = [
        'he_thong'  => '#7c3aed',
        'giao_duc'  => '#0891b2',
        'bao_mat'   => '#dc2626',
        'thong_bao' => '#d97706',
        'tai_chinh' => '#059669',
        'giao_dien' => '#db2777',
        'tich_hop'  => '#6366f1',
    ];
@endphp

{{-- ── Page Header ──────────────────────────────────────────── --}}
<div class="cfg-page-header">
    <div class="cfg-page-title">
        <div class="cfg-icon-wrap">
            <i class="fas fa-sliders-h"></i>
        </div>
        Cấu Hình Hệ Thống
        <span class="cfg-sub">{{ count($nhomMeta) }} nhóm cấu hình</span>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <button type="button" class="cfg-btn-action cfg-btn-reset-all" id="btnResetGroup"
            title="Khôi phục nhóm hiện tại về mặc định">
            <i class="fas fa-undo-alt"></i> Khôi phục mặc định
        </button>
        <button type="button" class="cfg-btn-action cfg-btn-save" id="btnSaveAll">
            <i class="fas fa-save"></i>
            <span id="btnSaveText">Lưu cấu hình</span>
        </button>
    </div>
</div>

{{-- ── Layout ───────────────────────────────────────────────── --}}
<div class="cfg-layout">

    {{-- ── Sidebar Navigation ───────────────────────────────── --}}
    <aside class="cfg-sidebar">
        <div class="cfg-sidebar-header">Nhóm cấu hình</div>
        @foreach ($nhomMeta as $slug => $meta)
            <a href="{{ route('admin.cau-hinh.index', ['nhom' => $slug]) }}"
               class="cfg-nav-item {{ $nhomHienTai === $slug ? 'active' : '' }}"
               title="{{ $meta['label'] }}">
                <span class="cfg-nav-icon" style="{{ $nhomHienTai === $slug ? "background:".($colorMap[$slug] ?? '#7c3aed')."22;color:".($colorMap[$slug] ?? '#7c3aed') : '' }}">
                    <i class="fas {{ $meta['icon'] }}"></i>
                </span>
                {{ $meta['label'] }}
                <span class="cfg-nav-badge">{{ $demTheoNhom[$slug] ?? 0 }}</span>
            </a>
        @endforeach
    </aside>

    {{-- ── Main Content ───────────────────────────────────────── --}}
    <main class="cfg-content" data-nhom="{{ $nhomHienTai }}">

        {{-- Section title --}}
        <div class="cfg-section-header">
            @php $meta = $nhomMeta[$nhomHienTai]; @endphp
            <div class="cfg-section-title">
                <div class="cfg-section-icon"
                     style="background:{{ $colorMap[$nhomHienTai] ?? '#7c3aed' }}22;color:{{ $colorMap[$nhomHienTai] ?? '#7c3aed' }}">
                    <i class="fas {{ $meta['icon'] }}"></i>
                </div>
                {{ $meta['label'] }}
            </div>
            <span style="font-size:.8rem;color:#94a3b8">
                {{ $cauHinhs->count() }} mục cấu hình
            </span>
        </div>

        @if ($cauHinhs->isEmpty())
            <div class="cfg-card"><div class="cfg-empty">
                <i class="fas fa-inbox"></i>
                <p>Chưa có cấu hình nào trong nhóm này.</p>
            </div></div>
        @else
            <form id="cfgForm" novalidate>
                @csrf
                <input type="hidden" name="nhom_hien_tai" value="{{ $nhomHienTai }}">

                <div class="cfg-card">
                    <div class="cfg-card-header">
                        <div class="cfg-card-title">
                            <i class="fas {{ $meta['icon'] }}"></i>
                            Cài đặt {{ $meta['label'] }}
                        </div>
                        <span style="font-size:.75rem;color:#94a3b8">
                            <i class="fas fa-info-circle me-1"></i>Thay đổi sẽ có hiệu lực ngay sau khi lưu
                        </span>
                    </div>

                    @foreach ($cauHinhs as $cfg)
                    <div class="cfg-row">
                        {{-- Label --}}
                        <div class="cfg-row-label">
                            <div class="cfg-label-text">
                                {{ $cfg->ten_hien_thi }}
                                @if ($cfg->yeu_cau)
                                    <span class="req" title="Bắt buộc">*</span>
                                @endif
                            </div>
                            @if ($cfg->mo_ta)
                                <div class="cfg-label-desc">{{ $cfg->mo_ta }}</div>
                            @endif
                            <div style="margin-top:5px">
                                <code style="font-size:.68rem;color:#94a3b8;background:#f1f5f9;padding:1px 6px;border-radius:4px">{{ $cfg->khoa }}</code>
                            </div>
                        </div>

                        {{-- Input --}}
                        <div class="cfg-row-input">
                            @switch($cfg->kieu_du_lieu)

                                @case('boolean')
                                    <div class="cfg-toggle-wrap">
                                        <label class="cfg-toggle" title="{{ $cfg->ten_hien_thi }}">
                                            <input type="checkbox"
                                                   id="cfg_{{ $cfg->khoa }}"
                                                   name="cau_hinh[{{ $cfg->khoa }}]"
                                                   value="1"
                                                   {{ $cfg->getGiaTriThucTe() ? 'checked' : '' }}
                                                   onchange="markDirty()">
                                            <span class="cfg-toggle-slider"></span>
                                        </label>
                                        <span class="cfg-toggle-label" id="lbl_{{ $cfg->khoa }}">
                                            {{ $cfg->getGiaTriThucTe() ? 'Đang bật' : 'Đang tắt' }}
                                        </span>
                                    </div>
                                    <script>
                                        document.getElementById('cfg_{{ $cfg->khoa }}').addEventListener('change', function() {
                                            document.getElementById('lbl_{{ $cfg->khoa }}').textContent = this.checked ? 'Đang bật' : 'Đang tắt';
                                        });
                                    </script>
                                    @break

                                @case('select')
                                    <select class="cfg-select"
                                            id="cfg_{{ $cfg->khoa }}"
                                            name="cau_hinh[{{ $cfg->khoa }}]"
                                            onchange="markDirty()">
                                        @foreach ($cfg->tuy_chon ?? [] as $opt)
                                            <option value="{{ $opt['value'] }}"
                                                {{ ($cfg->gia_tri ?? $cfg->gia_tri_mac_dinh) == $opt['value'] ? 'selected' : '' }}>
                                                {{ $opt['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('textarea')
                                    <textarea class="cfg-textarea"
                                              id="cfg_{{ $cfg->khoa }}"
                                              name="cau_hinh[{{ $cfg->khoa }}]"
                                              rows="4"
                                              oninput="markDirty()"
                                              placeholder="{{ $cfg->gia_tri_mac_dinh ?? '' }}">{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}</textarea>
                                    @break

                                @case('color')
                                    <div class="cfg-color-wrap">
                                        <input type="color"
                                               class="cfg-input"
                                               id="cfg_{{ $cfg->khoa }}"
                                               name="cau_hinh[{{ $cfg->khoa }}]"
                                               value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                               oninput="markDirty(); document.getElementById('preview_{{ $cfg->khoa }}').style.background = this.value; document.getElementById('hex_{{ $cfg->khoa }}').value = this.value;">
                                        <div class="cfg-color-preview"
                                             id="preview_{{ $cfg->khoa }}"
                                             style="background:{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}">
                                        </div>
                                        <input type="text"
                                               class="cfg-input"
                                               id="hex_{{ $cfg->khoa }}"
                                               value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                               style="max-width:110px;font-family:monospace"
                                               oninput="markDirty(); if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('cfg_{{ $cfg->khoa }}').value=this.value; document.getElementById('preview_{{ $cfg->khoa }}').style.background=this.value;}">
                                    </div>
                                    @break

                                @case('number')
                                    <div class="cfg-input-group">
                                        <input type="number"
                                               class="cfg-input"
                                               id="cfg_{{ $cfg->khoa }}"
                                               name="cau_hinh[{{ $cfg->khoa }}]"
                                               value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                               min="0"
                                               step="1"
                                               oninput="markDirty()"
                                               {{ $cfg->yeu_cau ? 'required' : '' }}>
                                        @if (str_contains($cfg->ten_hien_thi, '%'))
                                            <span class="cfg-input-unit">%</span>
                                        @elseif (str_contains($cfg->ten_hien_thi, 'ngày') || str_contains($cfg->ten_hien_thi, 'ngày'))
                                            <span class="cfg-input-unit">ngày</span>
                                        @elseif (str_contains($cfg->ten_hien_thi, 'phút'))
                                            <span class="cfg-input-unit">phút</span>
                                        @elseif (str_contains($cfg->ten_hien_thi, 'giây'))
                                            <span class="cfg-input-unit">giây</span>
                                        @elseif (str_contains($cfg->ten_hien_thi, 'tháng'))
                                            <span class="cfg-input-unit">tháng</span>
                                        @elseif (str_contains($cfg->ten_hien_thi, 'VNĐ'))
                                            <span class="cfg-input-unit">VNĐ</span>
                                        @endif
                                    </div>
                                    @break

                                @case('email')
                                    <input type="email"
                                           class="cfg-input"
                                           id="cfg_{{ $cfg->khoa }}"
                                           name="cau_hinh[{{ $cfg->khoa }}]"
                                           value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                           oninput="markDirty()"
                                           placeholder="example@domain.com"
                                           {{ $cfg->yeu_cau ? 'required' : '' }}>
                                    @break

                                @case('url')
                                    <input type="url"
                                           class="cfg-input"
                                           id="cfg_{{ $cfg->khoa }}"
                                           name="cau_hinh[{{ $cfg->khoa }}]"
                                           value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                           oninput="markDirty()"
                                           placeholder="https://"
                                           {{ $cfg->yeu_cau ? 'required' : '' }}>
                                    @break

                                @default
                                    {{-- text --}}
                                    <input type="text"
                                           class="cfg-input"
                                           id="cfg_{{ $cfg->khoa }}"
                                           name="cau_hinh[{{ $cfg->khoa }}]"
                                           value="{{ $cfg->gia_tri ?? $cfg->gia_tri_mac_dinh }}"
                                           oninput="markDirty()"
                                           {{ $cfg->yeu_cau ? 'required' : '' }}>
                            @endswitch
                        </div>
                    </div>
                    @endforeach
                </div>

            </form>
        @endif

    </main>
</div>

{{-- ── Floating Save Bar ────────────────────────────────────── --}}
<div class="cfg-save-bar" id="cfgSaveBar">
    <i class="fas fa-pencil-alt" style="color:#7c3aed"></i>
    <span class="cfg-save-bar-text">Có <strong id="dirtyCount">0</strong> thay đổi chưa lưu</span>
    <button type="button" class="cfg-btn-action cfg-btn-save" onclick="submitForm()"
            style="padding:8px 18px;font-size:.82rem">
        <i class="fas fa-save"></i> Lưu ngay
    </button>
</div>

{{-- ── Toast ───────────────────────────────────────────────── --}}
<div class="cfg-toast" id="cfgToast">
    <i class="fas fa-check-circle cfg-toast-icon" id="cfgToastIcon"></i>
    <span class="cfg-toast-msg" id="cfgToastMsg"></span>
</div>

@endsection

@section('script')
<script>
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const SAVE_URL  = '{{ route("admin.cau-hinh.update") }}';
    const RESET_URL = '{{ route("admin.cau-hinh.reset") }}';
    const NHOM    = '{{ $nhomHienTai }}';

    let dirtyFields = 0;
    let toastTimer  = null;

    // ── Dirty tracking ─────────────────────────────────────────
    function markDirty() {
        dirtyFields++;
        document.getElementById('dirtyCount').textContent = dirtyFields;
        document.getElementById('cfgSaveBar').classList.add('show');
    }

    // ── Toast helper ───────────────────────────────────────────
    function showToast(type, msg) {
        const el   = document.getElementById('cfgToast');
        const icon = document.getElementById('cfgToastIcon');
        const text = document.getElementById('cfgToastMsg');

        el.className = 'cfg-toast ' + type;
        icon.className = 'fas cfg-toast-icon ' + (type === 'success' ? 'fa-check-circle' : 'fa-times-circle');
        text.textContent = msg;

        el.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.remove('show'), 3500);
    }

    // ── Submit form ────────────────────────────────────────────
    async function submitForm() {
        const btn  = document.getElementById('btnSaveAll');
        const text = document.getElementById('btnSaveText');
        btn.disabled = true;
        text.textContent = 'Đang lưu...';
        btn.querySelector('i').className = 'fas cfg-spin fa-spinner';

        const form = document.getElementById('cfgForm');
        if (!form) {
            showToast('error', 'Không có cấu hình nào để lưu.');
            btn.disabled = false;
            text.textContent = 'Lưu cấu hình';
            btn.querySelector('i').className = 'fas fa-save';
            return;
        }
        const data = new FormData(form);

        // Thêm các checkbox boolean không được check (gửi giá trị 0)
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            const name = cb.name;
            if (name && !data.has(name)) {
                data.append(name, '0');
            }
        });

        try {
            const resp = await fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: data,
            });
            const json = await resp.json();

            if (resp.ok && json.success) {
                showToast('success', json.message || 'Đã lưu cấu hình!');
                dirtyFields = 0;
                document.getElementById('dirtyCount').textContent = '0';
                document.getElementById('cfgSaveBar').classList.remove('show');
            } else {
                showToast('error', json.message || 'Lưu thất bại. Vui lòng thử lại.');
            }
        } catch (err) {
            showToast('error', 'Lỗi kết nối máy chủ.');
        }

        btn.disabled = false;
        text.textContent = 'Lưu cấu hình';
        btn.querySelector('i').className = 'fas fa-save';
    }

    // ── Button Lưu chính ──────────────────────────────────────
    document.getElementById('btnSaveAll')?.addEventListener('click', submitForm);

    // ── Reset nhóm ────────────────────────────────────────────
    document.getElementById('btnResetGroup')?.addEventListener('click', function () {
        Swal.fire({
            title: 'Khôi phục mặc định?',
            html: `Tất cả cấu hình trong nhóm <strong>{{ $meta['label'] ?? $nhomHienTai }}</strong> sẽ được đặt lại về giá trị mặc định ban đầu.<br><small style="color:#64748b">Hành động này không thể hoàn tác.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo me-1"></i> Khôi phục',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(async res => {
            if (!res.isConfirmed) return;
            try {
                const resp = await fetch(RESET_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ nhom: NHOM }),
                });
                const json = await resp.json();
                if (json.success) {
                    showToast('success', json.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', json.message || 'Lỗi khôi phục.');
                }
            } catch {
                showToast('error', 'Lỗi kết nối.');
            }
        });
    });

    // ── Keyboard shortcut Ctrl+S ──────────────────────────────
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            submitForm();
        }
    });
</script>
@endsection
