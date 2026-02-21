@extends('layouts.admin')

@section('title', 'Phân quyền')
@section('page-title', 'Phân quyền')
@section('breadcrumb', 'Hệ thống · Phân quyền')
@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/phan-quyen/index.css') }}">
@endsection
@section('content')
    <div class="pq-card">
        <div class="pq-header">
            <div class="pq-title"><i class="fas fa-shield-alt me-2" style="color:#27c4b5"></i>Nhóm quyền hệ thống</div>
            <a href="{{ route('admin.phan-quyen.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Tạo nhóm mới
            </a>
        </div>

        @if ($nhomQuyens->isEmpty())
            <div class="empty-state">
                <i class="fas fa-lock-open"></i>
                Chưa có nhóm quyền nào. <a href="{{ route('admin.phan-quyen.create') }}">Tạo ngay</a>
            </div>
        @else
            <table class="pq-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên nhóm</th>
                        <th>Mô tả</th>
                        <th>Số tài khoản</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nhomQuyens as $nhom)
                        <tr>
                            <td style="color:#8899a6">{{ $loop->iteration }}</td>
                            <td><strong>{{ $nhom->tenNhom }}</strong></td>
                            <td style="color:#8899a6">{{ $nhom->moTa ?? '—' }}</td>
                            <td>
                                <span class="badge-count">{{ $nhom->tai_khoans_count ?? 0 }} tài khoản</span>
                            </td>
                            <td style="display:flex;gap:8px;flex-wrap:wrap">
                                <a href="{{ route('admin.phan-quyen.edit', $nhom->nhomQuyenId) }}" class="btn-act btn-edit">
                                    <i class="fas fa-edit"></i> Sửa quyền
                                </a>
                                <form action="{{ route('admin.phan-quyen.destroy', $nhom->nhomQuyenId) }}" method="POST"
                                    onsubmit="return confirm('Xóa nhóm «{{ $nhom->tenNhom }}»? Tài khoản thuộc nhóm này sẽ mất quyền.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-act btn-del">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
