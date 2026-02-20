@extends('layouts.admin')

@section('title', 'Phân quyền')
@section('page-title', 'Phân quyền')
@section('breadcrumb', 'Hệ thống · Phân quyền')

@section('stylesheet')
    <style>
        .pq-card {
            background: #fff;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .pq-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f4f8;
        }

        .pq-title {
            font-size: .95rem;
            font-weight: 600;
            color: #1a2b3c;
        }

        .btn-create {
            background: #27c4b5;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background .2s;
        }

        .btn-create:hover {
            background: #1eafa0;
            color: #fff;
        }

        table.pq-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pq-table thead th {
            background: #f8fafc;
            font-size: .75rem;
            font-weight: 600;
            color: #8899a6;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 12px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f4f8;
        }

        .pq-table tbody tr {
            border-bottom: 1px solid #f7f9fb;
            transition: background .15s;
        }

        .pq-table tbody tr:last-child {
            border-bottom: none;
        }

        .pq-table tbody tr:hover {
            background: #f8faffe0;
        }

        .pq-table tbody td {
            padding: 14px 18px;
            font-size: .875rem;
            color: #1a2b3c;
            vertical-align: middle;
        }

        .badge-count {
            background: rgba(39, 196, 181, .13);
            color: #0f6b63;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .75rem;
            font-weight: 600;
        }

        .btn-act {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 13px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-edit {
            background: #eef6ff;
            color: #3b82f6;
        }

        .btn-edit:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .btn-del {
            background: #fff5f5;
            color: #e53e3e;
        }

        .btn-del:hover {
            background: #fed7d7;
            color: #c53030;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #8899a6;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }
    </style>
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
