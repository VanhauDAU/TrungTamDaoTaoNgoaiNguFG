@extends('layouts.admin')

@section('title', 'Tạo nhóm quyền')
@section('page-title', 'Tạo nhóm quyền')
@section('breadcrumb', 'Hệ thống · Phân quyền · Tạo mới')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/phan-quyen/create.css') }}">
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
