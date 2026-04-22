@extends('layouts.internal')

@section('title', 'Đợt đánh giá')
@section('page-title', 'Đợt đánh giá')
@section('breadcrumb', 'Giáo viên · Danh sách đợt đánh giá')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lớp học</label>
                        <select name="lopHocId" class="form-select">
                            <option value="">Tất cả lớp</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->lopHocId }}" @selected($selectedClassId == $class->lopHocId)>
                                    [{{ $class->maLopHoc }}] {{ $class->tenLopHoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái đợt</label>
                        <select name="trangThai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary">Lọc</button>
                        <a href="{{ route('teacher.evaluations.periods.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($periods->isEmpty())
                    <div class="alert alert-light border mb-0">Chưa có đợt đánh giá nào phù hợp bộ lọc.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Đợt đánh giá</th>
                                    <th>Lớp</th>
                                    <th>Tiến độ</th>
                                    <th>Hạn nộp</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periods as $period)
                                    @php
                                        $total = $period->baoCaos->count();
                                        $done = $period->baoCaos->whereIn('trangThai', ['submitted', 'approved', 'published'])->count();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $period->tenDot }}</div>
                                            <div class="text-muted small">{{ $period->trangThaiLabel }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $period->lopHoc?->tenLopHoc }}</div>
                                            <div class="text-muted small">{{ $period->lopHoc?->khoaHoc?->tenKhoaHoc }}</div>
                                        </td>
                                        <td>
                                            <div class="small">{{ $done }}/{{ $total }} báo cáo hoàn tất</div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar" style="width: {{ $total > 0 ? round(($done / $total) * 100) : 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td>{{ optional($period->hanNop)->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('teacher.evaluations.periods.show', $period->dotDanhGiaId) }}"
                                                class="btn btn-sm btn-outline-dark">Xem chi tiết</a>
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
