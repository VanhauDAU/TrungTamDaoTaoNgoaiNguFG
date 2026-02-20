@extends('layouts.admin')

@section('title', 'Tạo nhóm quyền')
@section('page-title', 'Tạo nhóm quyền')
@section('breadcrumb', 'Hệ thống · Phân quyền · Tạo mới')

@section('stylesheet')
    <style>
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            max-width: 900px;
        }

        .form-label-custom {
            font-size: .85rem;
            font-weight: 600;
            color: #1a2b3c;
            margin-bottom: 6px;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .875rem;
            outline: none;
            transition: border .2s;
        }

        .form-control-custom:focus {
            border-color: #27c4b5;
        }

        /* ── PERMISSION MATRIX ── */
        .matrix-wrap {
            overflow-x: auto;
            margin-top: 24px;
        }

        .matrix-title {
            font-size: .9rem;
            font-weight: 600;
            color: #1a2b3c;
            margin-bottom: 14px;
        }

        table.matrix {
            width: 100%;
            border-collapse: collapse;
        }

        .matrix thead th {
            background: #f8fafc;
            font-size: .72rem;
            font-weight: 700;
            color: #8899a6;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 10px 14px;
            text-align: center;
            border-bottom: 1px solid #f0f4f8;
        }

        .matrix thead th:first-child {
            text-align: left;
        }

        .matrix tbody tr {
            border-bottom: 1px solid #f7f9fb;
        }

        .matrix tbody tr:last-child {
            border-bottom: none;
        }

        .matrix tbody tr:hover {
            background: #f8fffe;
        }

        .matrix tbody td {
            padding: 12px 14px;
            font-size: .875rem;
            text-align: center;
            vertical-align: middle;
        }

        .matrix tbody td:first-child {
            text-align: left;
            font-weight: 500;
            color: #1a2b3c;
        }

        .cb-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cb-wrap input[type=checkbox] {
            width: 18px;
            height: 18px;
            accent-color: #27c4b5;
            cursor: pointer;
        }

        .action-icon {
            font-size: .75rem;
            color: #8899a6;
            display: block;
            margin-top: 3px;
        }

        .action-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
        }

        .btn-save {
            background: #27c4b5;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 24px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-save:hover {
            background: #1eafa0;
        }

        .btn-back {
            color: #8899a6;
            text-decoration: none;
            font-size: .85rem;
        }

        .btn-back:hover {
            color: #1a2b3c;
        }
    </style>
@endsection

@section('content')
    <div class="form-card">
        <h5 style="margin-bottom:24px;font-size:1rem;font-weight:700;color:#1a2b3c">
            <i class="fas fa-plus-circle me-2" style="color:#27c4b5"></i>Tạo nhóm quyền mới
        </h5>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;border-radius:10px">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.phan-quyen.store') }}">
            @csrf

            {{-- Tên nhóm --}}
            <div class="mb-3">
                <label class="form-label-custom">Tên nhóm <span style="color:#e53e3e">*</span></label>
                <input type="text" name="tenNhom" class="form-control-custom" value="{{ old('tenNhom') }}"
                    placeholder="VD: Kế toán, Nhân sự, Tư vấn học vụ...">
            </div>

            {{-- Mô tả --}}
            <div class="mb-3">
                <label class="form-label-custom">Mô tả</label>
                <input type="text" name="moTa" class="form-control-custom" value="{{ old('moTa') }}"
                    placeholder="Mô tả ngắn về nhóm này...">
            </div>

            {{-- Ma trận quyền --}}
            <div class="matrix-wrap">
                <div class="matrix-title"><i class="fas fa-table me-2" style="color:#27c4b5"></i>Phân quyền theo tính năng
                </div>
                <table class="matrix">
                    <thead>
                        <tr>
                            <th style="width:200px">Tính năng</th>
                            <th><i class="fas fa-eye"></i><span class="action-icon">Xem</span></th>
                            <th><i class="fas fa-plus"></i><span class="action-icon">Thêm</span></th>
                            <th><i class="fas fa-pen"></i><span class="action-icon">Sửa</span></th>
                            <th><i class="fas fa-trash"></i><span class="action-icon">Xóa</span></th>
                            <th>
                                <button type="button" class="btn-act"
                                    style="font-size:.72rem;border-radius:6px;padding:4px 10px;background:#f0fdf4;color:#16a34a;border:none;cursor:pointer"
                                    onclick="checkAll()">Chọn tất cả</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tinhNangs as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                @foreach (['xem', 'them', 'sua', 'xoa'] as $act)
                                    <td>
                                        <div class="cb-wrap">
                                            <input type="checkbox" name="quyen[{{ $key }}][{{ $act }}]"
                                                value="1" id="cb_{{ $key }}_{{ $act }}"
                                                {{ old("quyen.$key.$act") ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                @endforeach
                                <td>
                                    <div class="cb-wrap">
                                        <button type="button" class="btn-act"
                                            style="font-size:.72rem;border-radius:6px;padding:3px 10px;background:#f0f4f8;color:#4a5568;border:none;cursor:pointer"
                                            onclick="checkRow('{{ $key }}')">Chọn hàng</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="action-bar">
                <button type="submit" class="btn-save"><i class="fas fa-save me-1"></i>Tạo nhóm</button>
                <a href="{{ route('admin.phan-quyen.index') }}" class="btn-back"><i class="fas fa-arrow-left me-1"></i>Quay
                    lại</a>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        function checkRow(feature) {
            ['xem', 'them', 'sua', 'xoa'].forEach(act => {
                const cb = document.getElementById(`cb_${feature}_${act}`);
                if (cb) cb.checked = true;
            });
        }

        function checkAll() {
            document.querySelectorAll('.matrix input[type=checkbox]').forEach(cb => cb.checked = true);
        }
    </script>
@endsection
