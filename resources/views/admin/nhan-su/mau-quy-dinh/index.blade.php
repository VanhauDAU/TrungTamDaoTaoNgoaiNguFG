@extends('layouts.admin')

@section('title', 'Mẫu quy định nhân sự')
@section('page-title', 'Mẫu quy định nhân sự')
@section('breadcrumb', 'Hồ sơ nhân sự · Mẫu quy định')

@section('content')
    <div style="display:grid;gap:20px">
        <div class="card" style="border-radius:18px;border:1px solid #e5e7eb">
            <div class="card-body" style="padding:24px;display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center">
                <div>
                    <h3 style="margin:0">Quản lý mẫu quy định nhân sự</h3>
                    <p style="margin:8px 0 0;color:#64748b">Các mẫu này sẽ được snapshot vào hồ sơ khi tạo giáo viên hoặc nhân viên.</p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <form method="GET" action="{{ route('admin.nhan-su.mau-quy-dinh.index') }}" style="display:flex;gap:10px">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm theo mã hoặc tiêu đề">
                        <button type="submit" class="btn btn-outline-secondary">Tìm</button>
                    </form>
                    <a href="{{ route('admin.nhan-su.mau-quy-dinh.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo mẫu
                    </a>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius:18px;border:1px solid #e5e7eb">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã mẫu</th>
                            <th>Tiêu đề</th>
                            <th>Phạm vi</th>
                            <th>Hợp đồng</th>
                            <th>Phiên bản</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $template)
                            <tr>
                                <td><strong>{{ $template->maMau }}</strong></td>
                                <td>{{ $template->tieuDe }}</td>
                                <td>{{ $phamViOptions[$template->phamViApDung] ?? $template->phamViApDung }}</td>
                                <td>{{ $template->loaiHopDongApDung }}</td>
                                <td>v{{ $template->phienBan }}</td>
                                <td>
                                    <span class="badge {{ (int) $template->trangThai === 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ (int) $template->trangThai === 1 ? 'Hoạt động' : 'Tạm khóa' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div style="display:inline-flex;gap:8px">
                                        <a href="{{ route('admin.nhan-su.mau-quy-dinh.edit', $template->nhanSuMauQuyDinhId) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Sửa
                                        </a>
                                        <form method="POST" action="{{ route('admin.nhan-su.mau-quy-dinh.destroy', $template->nhanSuMauQuyDinhId) }}"
                                            onsubmit="return confirm('Xóa mẫu quy định này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Chưa có mẫu quy định nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $templates->links() }}
    </div>
@endsection
