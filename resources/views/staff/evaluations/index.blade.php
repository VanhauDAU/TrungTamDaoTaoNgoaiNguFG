@extends('layouts.internal')

@section('title', 'Duyệt báo cáo học tập')
@section('page-title', 'Duyệt báo cáo học tập')
@section('breadcrumb', 'Nhân viên · Hàng chờ duyệt')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Chờ duyệt</div><div class="display-6 fw-bold">{{ $summary['submitted'] }}</div></div></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Cần chỉnh sửa</div><div class="display-6 fw-bold">{{ $summary['needs_revision'] }}</div></div></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Đã duyệt</div><div class="display-6 fw-bold">{{ $summary['approved'] }}</div></div></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Đã phát hành</div><div class="display-6 fw-bold">{{ $summary['published'] }}</div></div></div></div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái báo cáo</label>
                        <select name="trangThai" class="form-select">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 d-flex gap-2">
                        <button class="btn btn-primary">Lọc</button>
                        <a href="{{ route('staff.evaluations.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                        <a href="{{ route('staff.evaluations.periods.index') }}" class="btn btn-outline-dark">Quản lý đợt đánh giá</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($reports->isEmpty())
                    <div class="alert alert-light border mb-0">Không có báo cáo nào trong hàng chờ hiện tại.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Học viên</th>
                                    <th>Lớp / đợt</th>
                                    <th>Giáo viên</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $report->dangKyLopHoc?->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</div>
                                            <div class="text-muted small">{{ $report->dangKyLopHoc?->taiKhoan?->taiKhoan }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $report->dotDanhGia?->lopHoc?->tenLopHoc }}</div>
                                            <div class="text-muted small">{{ $report->dotDanhGia?->tenDot }}</div>
                                        </td>
                                        <td>{{ $report->giaoVien?->hoSoNguoiDung?->hoTen ?? $report->giaoVien?->taiKhoan ?? '—' }}</td>
                                        <td><span class="badge text-bg-light border">{{ $report->trangThaiLabel }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('staff.evaluations.reports.show', $report->baoCaoHocTapId) }}"
                                                class="btn btn-sm btn-outline-primary">Mở duyệt</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
