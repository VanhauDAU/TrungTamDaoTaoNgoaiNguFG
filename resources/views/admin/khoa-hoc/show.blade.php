@extends('layouts.admin')

@section('title', $khoaHoc->tenKhoaHoc . ' – Chi tiết')
@section('page-title', 'Khóa Học')
@section('breadcrumb', 'Quản lý · Khóa học · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/form.css') }}">
    <style>
        .kh-show-hero {
            background: linear-gradient(135deg, #134e4a 0%, #0f766e 50%, #14b8a6 100%);
            border-radius: 14px;
            padding: 28px 30px;
            color: #fff;
            display: flex;
            gap: 24px;
            align-items: flex-start;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }

        .kh-show-hero::before {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .kh-show-thumb {
            width: 110px;
            height: 110px;
            border-radius: 12px;
            object-fit: cover;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: rgba(255, 255, 255, .8);
            flex-shrink: 0;
        }

        .kh-show-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .kh-show-info {
            flex: 1;
        }

        .kh-show-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .kh-show-info .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: .85rem;
            opacity: .85;
            margin-bottom: 12px;
        }

        .kh-show-info .meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .kh-show-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .kh-hero-btn {
            padding: 8px 16px;
            border-radius: 7px;
            font-size: .84rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }

        .kh-hero-btn-edit {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .kh-hero-btn-edit:hover {
            background: rgba(255, 255, 255, .35);
            color: #fff;
        }

        .kh-hero-btn-add {
            background: #fff;
            color: #0f766e;
        }

        .kh-hero-btn-add:hover {
            background: #f0fdfa;
        }

        .kh-hero-btn-del {
            background: rgba(239, 68, 68, .3);
            color: #fff;
        }

        .kh-hero-btn-del:hover {
            background: rgba(239, 68, 68, .5);
        }

        .kh-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .kh-info-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .kh-info-item label {
            font-size: .72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .kh-info-item .kh-info-val {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 4px;
        }

        .lh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .lh-table th {
            background: #f8fafc;
            padding: 10px 14px;
            text-align: left;
            font-size: .77rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }

        .lh-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .lh-table tr:last-child td {
            border-bottom: none;
        }

        .lh-table tr:hover td {
            background: #f8fafc;
        }

        .tt-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
        }

        .tt-0 {
            background: #fff7ed;
            color: #c2410c;
        }

        .tt-1 {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .tt-2 {
            background: #f8fafc;
            color: #64748b;
        }

        .tt-3 {
            background: #fff1f2;
            color: #be123c;
        }

        .tt-4 {
            background: #f0fdf4;
            color: #15803d;
        }
    </style>
@endsection

@section('content')

    {{-- ── Hero ──────────────────────────────────────────────── --}}
    <div class="kh-show-hero">
        <div class="kh-show-thumb">
            @if ($khoaHoc->anhKhoaHoc)
                <img src="{{ asset('storage/' . $khoaHoc->anhKhoaHoc) }}" alt="{{ $khoaHoc->tenKhoaHoc }}">
            @else
                <i class="fas fa-book-open"></i>
            @endif
        </div>
        <div class="kh-show-info">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                @if ($khoaHoc->danhMuc)
                    <span
                        style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600">
                        {{ $khoaHoc->danhMuc->tenDanhMuc }}
                    </span>
                @endif
                @if ($khoaHoc->trangThai)
                    <span
                        style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600">
                        <i class="fas fa-circle" style="font-size:.4em"></i> Hoạt động
                    </span>
                @else
                    <span
                        style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600">
                        <i class="fas fa-circle" style="font-size:.4em"></i> Ngừng
                    </span>
                @endif
            </div>
            <h1>[<span style="color:#fde68a;">{{ $khoaHoc->maKhoaHoc }}</span>] {{ $khoaHoc->tenKhoaHoc }}</h1>
            <div class="meta">
                @if ($khoaHoc->doiTuong)
                    <span><i class="fas fa-user-graduate"></i> {{ $khoaHoc->doiTuong }}</span>
                @endif
                <span><i class="fas fa-chalkboard"></i> {{ $tongLop }} lớp học</span>
            </div>
            <div class="kh-show-actions">
                <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->slug) }}" class="kh-hero-btn kh-hero-btn-edit">
                    <i class="fas fa-pen"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.lop-hoc.create', ['khoaHocId' => $khoaHoc->khoaHocId]) }}"
                    class="kh-hero-btn kh-hero-btn-add">
                    <i class="fas fa-plus"></i> Thêm lớp học
                </a>
                <a href="{{ route('admin.khoa-hoc.index') }}" class="kh-hero-btn kh-hero-btn-edit">
                    <i class="fas fa-arrow-left"></i> Danh sách
                </a>
            </div>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if (session('success'))
        <div class="kf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- ── Stats ─────────────────────────────────────────────── --}}
    <div class="kh-info-grid">
        <div class="kh-info-item">
            <label><i class="fas fa-chalkboard me-1"></i> Tổng lớp học</label>
            <div class="kh-info-val">{{ $tongLop }}</div>
        </div>
        <div class="kh-info-item">
            <label><i class="fas fa-play-circle me-1"></i> Đang học</label>
            <div class="kh-info-val" style="color:#15803d">{{ $lopDangHoc }}</div>
        </div>
        <div class="kh-info-item">
            <label><i class="fas fa-calendar me-1"></i> Sắp mở</label>
            <div class="kh-info-val" style="color:#c2410c">{{ $lopSapMo }}</div>
        </div>
        <div class="kh-info-item">
            <label><i class="fas fa-users me-1"></i> Học viên (ước tính)</label>
            <div class="kh-info-val">{{ $tongHocVien }}</div>
        </div>
    </div>

    {{-- ── Thông tin chi tiết ───────────────────────────────── --}}
    @if ($khoaHoc->moTa || $khoaHoc->yeuCauDauVao || $khoaHoc->ketQuaDatDuoc)
        <div class="kf-card" style="margin-bottom:22px">
            <div class="kf-card-title"><i class="fas fa-align-left"></i> Thông tin chi tiết</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px">
                @if ($khoaHoc->moTa)
                    <div>
                        <div
                            style="font-size:.8rem;font-weight:700;color:#64748b;margin-bottom:8px;text-transform:uppercase">
                            Mô tả</div>
                        <p style="font-size:.875rem;color:#334155;line-height:1.6;margin:0">{{ $khoaHoc->moTa }}</p>
                    </div>
                @endif
                @if ($khoaHoc->yeuCauDauVao)
                    <div>
                        <div
                            style="font-size:.8rem;font-weight:700;color:#64748b;margin-bottom:8px;text-transform:uppercase">
                            Yêu cầu đầu vào</div>
                        <p style="font-size:.875rem;color:#334155;line-height:1.6;margin:0">{{ $khoaHoc->yeuCauDauVao }}
                        </p>
                    </div>
                @endif
                @if ($khoaHoc->ketQuaDatDuoc)
                    <div>
                        <div
                            style="font-size:.8rem;font-weight:700;color:#64748b;margin-bottom:8px;text-transform:uppercase">
                            Kết quả đạt được</div>
                        <p style="font-size:.875rem;color:#334155;line-height:1.6;margin:0">{{ $khoaHoc->ketQuaDatDuoc }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Gói Học Phí ────────────────────────────────────────── --}}
    <div class="kf-card" style="margin-bottom:22px">
        <div class="kf-card-title" style="justify-content:space-between">
            <span><i class="fas fa-file-invoice-dollar"></i> Gói học phí ({{ $hocPhis->count() }})</span>
            <button type="button" onclick="toggleHpForm()"
                style="font-size:.82rem;background:#f5f3ff;color:#7c3aed;padding:5px 12px;border-radius:6px;border:none;cursor:pointer;font-weight:600">
                <i class="fas fa-plus"></i> Thêm gói
            </button>
        </div>

        {{-- Hướng dẫn --}}
        <p style="font-size:.8rem;color:#64748b;margin:0 0 14px">
            <i class="fas fa-info-circle" style="color:#a78bfa"></i>
            Mỗi gói xác định <strong>số buổi</strong> và <strong>đơn giá/buổi</strong> mà học viên phải đóng.
            Tổng học phí = số buổi × đơn giá. Khi tạo lớp học, chọn gói phù hợp.
        </p>

        {{-- Form thêm gói mới --}}
        <div id="hpAddForm"
            style="display:none;background:#f8f5ff;border:1px solid #ddd6fe;border-radius:10px;padding:14px 16px;margin-bottom:14px">
            <form action="{{ route('admin.hoc-phi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="khoaHocId" value="{{ $khoaHoc->khoaHocId }}">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <div>
                        <label style="font-size:.75rem;font-weight:700;color:#7c3aed;display:block;margin-bottom:4px">Số
                            buổi <span style="color:#dc2626">*</span></label>
                        <input type="number" name="soBuoi" min="1" placeholder="VD: 20"
                            style="width:100%;padding:8px 10px;border:1px solid #ddd6fe;border-radius:7px;font-size:.85rem;outline:none"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#ddd6fe'" required>
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:700;color:#7c3aed;display:block;margin-bottom:4px">Đơn
                            giá/buổi (đ) <span style="color:#dc2626">*</span></label>
                        <input type="number" name="donGia" min="0" step="1000" placeholder="VD: 150000"
                            id="hp-dongia-input"
                            style="width:100%;padding:8px 10px;border:1px solid #ddd6fe;border-radius:7px;font-size:.85rem;outline:none"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#ddd6fe'"
                            oninput="calcHpTotal()" required>
                        <div id="hp-total-preview" style="font-size:.72rem;color:#7c3aed;margin-top:4px;font-weight:600">
                        </div>
                    </div>
                    <div>
                        <label style="font-size:.75rem;font-weight:700;color:#7c3aed;display:block;margin-bottom:4px">Trạng
                            thái</label>
                        <select name="trangThai"
                            style="width:100%;padding:8px 10px;border:1px solid #ddd6fe;border-radius:7px;font-size:.85rem;outline:none">
                            <option value="1">Đang áp dụng</option>
                            <option value="0">Tạm ngưng</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:6px">
                        <button type="submit"
                            style="flex:1;padding:9px;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;border:none;border-radius:7px;font-weight:700;cursor:pointer;font-size:.85rem">
                            <i class="fas fa-save me-1"></i> Lưu
                        </button>
                        <button type="button" onclick="toggleHpForm()"
                            style="padding:9px 12px;background:#f1f5f9;border:none;border-radius:7px;cursor:pointer;font-size:.85rem;color:#64748b">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Bảng danh sách gói --}}
        @if ($hocPhis->isEmpty())
            <div style="text-align:center;padding:30px;color:#94a3b8">
                <i class="fas fa-file-invoice-dollar"
                    style="font-size:1.8rem;opacity:.2;display:block;margin-bottom:10px"></i>
                Chưa có gói học phí nào. Nhấn <strong>Thêm gói</strong> để tạo.
            </div>
        @else
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.85rem">
                    <thead>
                        <tr style="background:#faf5ff">
                            <th
                                style="padding:8px 12px;text-align:left;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Số buổi</th>
                            <th
                                style="padding:8px 12px;text-align:right;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Đơn giá/buổi</th>
                            <th
                                style="padding:8px 12px;text-align:right;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Tổng học phí HV</th>
                            <th
                                style="padding:8px 12px;text-align:center;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Số lớp dùng</th>
                            <th
                                style="padding:8px 12px;text-align:center;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Trạng thái</th>
                            <th
                                style="padding:8px 12px;text-align:center;font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase;border-bottom:1px solid #ede9fe">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hocPhis as $hp)
                            <tr id="hp-row-{{ $hp->hocPhiId }}"
                                style="border-bottom:1px solid #f1f5f9;transition:background .15s"
                                onmouseover="this.style.background='#fdf4ff'" onmouseout="this.style.background=''">
                                {{-- Normal view --}}
                                <td style="padding:10px 12px;font-weight:700;color:#1e293b">
                                    <div id="hp-view-sobuoi-{{ $hp->hocPhiId }}">
                                        <i class="fas fa-calendar-days" style="color:#a78bfa;margin-right:4px"></i>
                                        {{ $hp->soBuoi }} buổi
                                    </div>
                                    <div id="hp-edit-sobuoi-{{ $hp->hocPhiId }}" style="display:none">
                                        <input type="number" id="hp-inp-sobuoi-{{ $hp->hocPhiId }}"
                                            value="{{ $hp->soBuoi }}" min="1"
                                            style="width:80px;padding:5px 8px;border:1px solid #ddd6fe;border-radius:5px;font-size:.85rem">
                                    </div>
                                </td>
                                <td style="padding:10px 12px;text-align:right;color:#7c3aed;font-weight:600">
                                    <div id="hp-view-dongia-{{ $hp->hocPhiId }}">
                                        {{ number_format($hp->donGia, 0, ',', '.') }} đ
                                    </div>
                                    <div id="hp-edit-dongia-{{ $hp->hocPhiId }}" style="display:none">
                                        <input type="number" id="hp-inp-dongia-{{ $hp->hocPhiId }}"
                                            value="{{ $hp->donGia }}" min="0" step="1000"
                                            style="width:110px;padding:5px 8px;border:1px solid #ddd6fe;border-radius:5px;font-size:.85rem">
                                    </div>
                                </td>
                                <td style="padding:10px 12px;text-align:right;font-weight:700;color:#059669"
                                    id="hp-view-tong-{{ $hp->hocPhiId }}">
                                    {{ number_format($hp->tongHocPhi, 0, ',', '.') }} đ
                                </td>
                                <td style="padding:10px 12px;text-align:center">
                                    <span
                                        style="background:#ede9fe;color:#7c3aed;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:700">
                                        {{ $hp->lopHocs->count() }} lớp
                                    </span>
                                </td>
                                <td style="padding:10px 12px;text-align:center">
                                    <div id="hp-view-tt-{{ $hp->hocPhiId }}">
                                        @if ($hp->trangThai)
                                            <span
                                                style="background:#f0fdf4;color:#16a34a;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700"><i
                                                    class="fas fa-check-circle me-1"></i>Áp dụng</span>
                                        @else
                                            <span
                                                style="background:#f8fafc;color:#64748b;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700"><i
                                                    class="fas fa-pause-circle me-1"></i>Tạm ngưng</span>
                                        @endif
                                    </div>
                                    <div id="hp-edit-tt-{{ $hp->hocPhiId }}" style="display:none">
                                        <select id="hp-inp-tt-{{ $hp->hocPhiId }}"
                                            style="padding:4px 8px;border:1px solid #ddd6fe;border-radius:5px;font-size:.82rem">
                                            <option value="1" {{ $hp->trangThai ? 'selected' : '' }}>Áp dụng</option>
                                            <option value="0" {{ !$hp->trangThai ? 'selected' : '' }}>Tạm ngưng
                                            </option>
                                        </select>
                                    </div>
                                </td>
                                <td style="padding:10px 12px;text-align:center">
                                    {{-- View actions --}}
                                    <div id="hp-actions-view-{{ $hp->hocPhiId }}"
                                        style="display:flex;gap:5px;justify-content:center">
                                        <button type="button" title="Sửa"
                                            onclick="toggleHpEdit({{ $hp->hocPhiId }})"
                                            style="padding:4px 10px;background:#f5f3ff;color:#7c3aed;border:none;border-radius:5px;cursor:pointer;font-size:.8rem;font-weight:600">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" title="Xóa"
                                            onclick="deleteHocPhi({{ $hp->hocPhiId }}, {{ $hp->soBuoi }}, {{ $hp->lopHocs->count() }})"
                                            style="padding:4px 10px;background:#fff1f2;color:#dc2626;border:none;border-radius:5px;cursor:pointer;font-size:.8rem;font-weight:600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    {{-- Edit actions --}}
                                    <div id="hp-actions-edit-{{ $hp->hocPhiId }}"
                                        style="display:none;gap:5px;justify-content:center">
                                        <button type="button" title="Lưu" onclick="saveHocPhi({{ $hp->hocPhiId }})"
                                            style="padding:4px 10px;background:#7c3aed;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:.8rem;font-weight:600">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" title="Hủy"
                                            onclick="cancelHpEdit({{ $hp->hocPhiId }})"
                                            style="padding:4px 10px;background:#f1f5f9;color:#64748b;border:none;border-radius:5px;cursor:pointer;font-size:.8rem">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Hidden delete form --}}
    <form id="hp-delete-form" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>
    {{-- Hidden edit form --}}
    <form id="hp-edit-form" method="POST" style="display:none">
        @csrf @method('PUT')
        <input type="hidden" name="soBuoi" id="hp-edit-sobuoi-val">
        <input type="hidden" name="donGia" id="hp-edit-dongia-val">
        <input type="hidden" name="trangThai" id="hp-edit-tt-val">
    </form>

    {{-- ── Danh sách lớp học ────────────────────────────────── --}}

    <div class="kf-card">
        <div class="kf-card-title" style="justify-content:space-between">
            <span><i class="fas fa-chalkboard"></i> Danh sách lớp học ({{ $tongLop }})</span>
            <a href="{{ route('admin.lop-hoc.create', ['khoaHocId' => $khoaHoc->khoaHocId]) }}"
                style="font-size:.82rem;background:#f0fdfa;color:#0f766e;padding:5px 12px;border-radius:6px;text-decoration:none;font-weight:600">
                <i class="fas fa-plus"></i> Thêm lớp
            </a>
        </div>

        @if ($khoaHoc->lopHoc->isEmpty())
            <div class="kh-empty" style="padding:30px">
                <i class="fas fa-chalkboard" style="font-size:2rem"></i>
                <p>Chưa có lớp học nào. <a
                        href="{{ route('admin.lop-hoc.create', ['khoaHocId' => $khoaHoc->khoaHocId]) }}"
                        style="color:#0f766e">Tạo lớp đầu tiên</a></p>
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên lớp</th>
                            <th>Giáo viên</th>
                            <th>Cơ sở</th>
                            <th>Ca học</th>
                            <th>Ngày bắt đầu</th>
                            <th>Trạng thái</th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($khoaHoc->lopHoc as $i => $lop)
                            @php
                                $ttLabels = ['Sắp mở', 'Đang mở', 'Đã đóng', 'Đã hủy', 'Đang học'];
                                $ttLabel = $ttLabels[$lop->trangThai] ?? '?';
                            @endphp
                            <tr>
                                <td style="color:#94a3b8;font-size:.78rem">{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.lop-hoc.show', $lop->slug) }}"
                                        style="font-weight:600;color:#134e4a;text-decoration:none">
                                        {{ $lop->tenLopHoc }}
                                    </a>
                                    @if ($lop->lichHoc)
                                        <div style="font-size:.72rem;color:#94a3b8;margin-top:2px">
                                            <i class="fas fa-calendar-days"></i> Thứ
                                            {{ implode(', ', explode(',', $lop->lichHoc)) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:.83rem">
                                    {{ $lop->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}
                                </td>
                                <td style="font-size:.83rem">{{ $lop->coSo?->tenCoSo ?? '—' }}</td>
                                <td style="font-size:.83rem">{{ $lop->caHoc?->tenCa ?? '—' }}</td>
                                <td style="font-size:.83rem;color:#64748b">
                                    {{ $lop->ngayBatDau ? \Carbon\Carbon::parse($lop->ngayBatDau)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <span class="tt-badge tt-{{ $lop->trangThai }}">{{ $ttLabel }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px;justify-content:center">
                                        <a href="{{ route('admin.lop-hoc.show', $lop->slug) }}"
                                            class="kh-btn-action kh-btn-view" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.lop-hoc.edit', $lop->slug) }}"
                                            class="kh-btn-action kh-btn-edit" title="Sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

@section('script')
    <script>
        // ── Toggle form thêm gói ────────────────────────────────
        function toggleHpForm() {
            const f = document.getElementById('hpAddForm');
            f.style.display = f.style.display === 'block' ? 'none' : 'block';
            if (f.style.display === 'block') f.querySelector('input[name=soBuoi]').focus();
        }

        // ── Live preview tổng học phí khi nhập ─────────────────
        function calcHpTotal() {
            const soBuoi = parseInt(document.querySelector('#hpAddForm input[name=soBuoi]')?.value || 0);
            const donGia = parseFloat(document.getElementById('hp-dongia-input')?.value || 0);
            const preview = document.getElementById('hp-total-preview');
            if (soBuoi > 0 && donGia > 0) {
                const tong = soBuoi * donGia;
                preview.textContent = `→ Tổng: ${tong.toLocaleString('vi-VN')} đ`;
            } else {
                preview.textContent = '';
            }
        }

        // Cũng tính khi số buổi thay đổi
        document.addEventListener('DOMContentLoaded', () => {
            const soBuoiInp = document.querySelector('#hpAddForm input[name=soBuoi]');
            if (soBuoiInp) soBuoiInp.addEventListener('input', calcHpTotal);
        });

        // ── Inline edit ─────────────────────────────────────────
        function toggleHpEdit(id) {
            ['sobuoi', 'dongia', 'tt'].forEach(k => {
                document.getElementById(`hp-view-${k}-${id}`).style.display = 'none';
                document.getElementById(`hp-edit-${k}-${id}`).style.display = 'block';
            });
            document.getElementById(`hp-actions-view-${id}`).style.display = 'none';
            document.getElementById(`hp-actions-edit-${id}`).style.display = 'flex';
        }

        function cancelHpEdit(id) {
            ['sobuoi', 'dongia', 'tt'].forEach(k => {
                document.getElementById(`hp-view-${k}-${id}`).style.display = 'block';
                document.getElementById(`hp-edit-${k}-${id}`).style.display = 'none';
            });
            document.getElementById(`hp-actions-view-${id}`).style.display = 'flex';
            document.getElementById(`hp-actions-edit-${id}`).style.display = 'none';
        }

        function saveHocPhi(id) {
            const soBuoi = document.getElementById(`hp-inp-sobuoi-${id}`).value;
            const donGia = document.getElementById(`hp-inp-dongia-${id}`).value;
            const tt = document.getElementById(`hp-inp-tt-${id}`).value;

            if (!soBuoi || !donGia) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu thông tin',
                    text: 'Vui lòng nhập số buổi và đơn giá.',
                    confirmButtonColor: '#7c3aed'
                });
                return;
            }

            document.getElementById('hp-edit-sobuoi-val').value = soBuoi;
            document.getElementById('hp-edit-dongia-val').value = donGia;
            document.getElementById('hp-edit-tt-val').value = tt;

            const form = document.getElementById('hp-edit-form');
            form.action = `/admin/hoc-phi/${id}`;
            form.submit();
        }

        // ── Xóa gói học phí ────────────────────────────────────
        function deleteHocPhi(id, soBuoi, soLop) {
            if (soLop > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Không thể xóa',
                    html: `Gói <strong>${soBuoi} buổi</strong> đang được dùng bởi <strong>${soLop} lớp học</strong>. Hãy gỡ các lớp khỏi gói này trước.`,
                    confirmButtonColor: '#7c3aed',
                });
                return;
            }
            Swal.fire({
                title: 'Xóa gói học phí?',
                html: `Xóa gói <strong>${soBuoi} buổi</strong>? Hành động này không thể hoàn tác.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('hp-delete-form');
                    form.action = `/admin/hoc-phi/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endsection
