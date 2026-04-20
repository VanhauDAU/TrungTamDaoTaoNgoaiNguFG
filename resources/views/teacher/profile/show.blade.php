@extends('layouts.internal')

@section('title', 'Hồ sơ giáo viên')
@section('page-title', 'Hồ sơ giáo viên')
@section('breadcrumb', 'Thông tin tài khoản và nhân sự')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <img src="{{ $user->getAvatarUrl() }}" alt="Avatar" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;">
                        <h5 class="mb-1">{{ $user->hoSoNguoiDung?->hoTen ?? $user->taiKhoan }}</h5>
                        <div class="text-muted">{{ $user->getRoleLabel() }}</div>
                        <div class="badge bg-primary-subtle text-primary-emphasis mt-3">{{ $user->taiKhoan }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Thông tin cơ bản</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Email</div>
                                <div class="fw-semibold">{{ $user->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Cơ sở</div>
                                <div class="fw-semibold">{{ $user->nhanSu?->coSoDaoTao?->tenCoSo ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Chức vụ</div>
                                <div class="fw-semibold">{{ $user->nhanSu?->chucVu ?? 'Giáo viên' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Chuyên môn</div>
                                <div class="fw-semibold">{{ $user->nhanSu?->chuyenMon ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Ngày vào làm</div>
                                <div class="fw-semibold">{{ optional($user->nhanSu?->ngayVaoLam)->format('d/m/Y') ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Trạng thái tài khoản</div>
                                <div class="fw-semibold">{{ (int) $user->trangThai === 1 ? 'Đang hoạt động' : 'Bị khóa' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
