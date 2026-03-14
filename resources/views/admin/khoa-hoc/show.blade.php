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

    {{-- ── Chính Sách Giá ────────────────────────────────────────── --}}
    <div class="kf-card" style="margin-bottom:22px">
        <div class="kf-card-title">
            <span><i class="fas fa-file-invoice-dollar"></i> Chính sách giá</span>
        </div>
        <p style="font-size:.84rem;color:#475569;margin:0">
            Học phí không còn được cấu hình ở cấp khóa học. Mỗi lớp học sẽ có chính sách giá riêng, cho phép trung tâm mở
            lớp trước rồi bổ sung học phí trước khi tuyển sinh.
        </p>
    </div>

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
                                    <span class="tt-badge tt-{{ $lop->trangThai }}">{{ $lop->trangThaiLabel }}</span>
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
