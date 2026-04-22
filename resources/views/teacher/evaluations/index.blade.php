@extends('layouts.internal')

@section('title', 'Báo cáo học tập')
@section('page-title', 'Báo cáo học tập')
@section('breadcrumb', 'Giáo viên · Báo cáo học tập / nhận xét tiến độ')

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Nháp', 'value' => $summary['draft'] ?? 0, 'class' => 'bg-warning-subtle text-warning-emphasis'],
            ['label' => 'Chờ duyệt', 'value' => $summary['submitted'] ?? 0, 'class' => 'bg-info-subtle text-info-emphasis'],
            ['label' => 'Cần chỉnh sửa', 'value' => $summary['needs_revision'] ?? 0, 'class' => 'bg-danger-subtle text-danger-emphasis'],
            ['label' => 'Đã phát hành', 'value' => $summary['published'] ?? 0, 'class' => 'bg-success-subtle text-success-emphasis'],
            ['label' => 'Quá hạn', 'value' => $summary['overdue'] ?? 0, 'class' => 'bg-dark-subtle text-dark-emphasis'],
        ];
    @endphp

    <div class="container-fluid px-0">
        <div class="row g-3 mb-4">
            @foreach ($summaryCards as $card)
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <span class="badge {{ $card['class'] }}">{{ $card['label'] }}</span>
                            <div class="display-6 fw-bold mt-3">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

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
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="trangThai" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary">Lọc</button>
                        <a href="{{ route('teacher.evaluations.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                        <a href="{{ route('teacher.evaluations.periods.index') }}" class="btn btn-outline-dark">Xem đợt đánh giá</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-1">Báo cáo gần đây</h5>
                        <p class="text-muted small mb-0">Danh sách nháp, báo cáo chờ duyệt và các báo cáo vừa cập nhật.</p>
                    </div>
                    <div class="card-body">
                        @if ($reports->isEmpty())
                            <div class="alert alert-light border mb-0">Chưa có báo cáo nào thuộc bộ lọc hiện tại.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Học viên</th>
                                            <th>Lớp / đợt</th>
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
                                                <td><span class="badge text-bg-light border">{{ $report->trangThaiLabel }}</span></td>
                                                <td class="text-end">
                                                    <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}"
                                                        class="btn btn-sm btn-outline-primary">Mở báo cáo</a>
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

            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-1">Đợt đánh giá đang phụ trách</h5>
                        <p class="text-muted small mb-0">Theo dõi nhanh tiến độ hoàn thành báo cáo từng lớp.</p>
                    </div>
                    <div class="card-body d-grid gap-3">
                        @forelse ($periods as $period)
                            @php
                                $total = $period->baoCaos->count();
                                $done = $period->baoCaos->whereIn('trangThai', ['submitted', 'approved', 'published'])->count();
                            @endphp
                            <div class="border rounded-4 p-3">
                                <div class="d-flex justify-content-between gap-3 align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $period->tenDot }}</div>
                                        <div class="text-muted small">
                                            {{ $period->lopHoc?->tenLopHoc }} · {{ $period->lopHoc?->khoaHoc?->tenKhoaHoc }}
                                        </div>
                                    </div>
                                    <span class="badge text-bg-light border">{{ $period->trangThaiLabel }}</span>
                                </div>
                                <div class="small text-muted mt-2">Hoàn thành {{ $done }}/{{ $total }} báo cáo</div>
                                <div class="progress mt-2" role="progressbar" aria-label="Progress">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? round(($done / $total) * 100) : 0 }}%"></div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('teacher.evaluations.periods.show', $period->dotDanhGiaId) }}"
                                        class="btn btn-sm btn-outline-dark">Chi tiết đợt</a>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-light border mb-0">Chưa có đợt đánh giá nào được tạo cho lớp bạn phụ trách.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
