@extends('layouts.internal')

@section('title', $period->tenDot)
@section('page-title', 'Chi tiết đợt đánh giá')
@section('breadcrumb', 'Giáo viên · Đợt đánh giá')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">{{ $period->tenDot }}</h4>
                        <div class="text-muted">
                            {{ $period->lopHoc?->tenLopHoc }} · {{ $period->lopHoc?->khoaHoc?->tenKhoaHoc }} · {{ $period->lopHoc?->coSo?->tenCoSo }}
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge text-bg-light border">{{ $period->trangThaiLabel }}</span>
                        <span class="badge text-bg-light border">Hạn nộp: {{ optional($period->hanNop)->format('d/m/Y') ?? '—' }}</span>
                        <span class="badge text-bg-light border">Hoàn thành: {{ $summary['completed'] }}/{{ $summary['total'] }}</span>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <form method="POST" action="{{ route('teacher.evaluations.periods.bulk-create', $period->dotDanhGiaId) }}">
                        @csrf
                        <button class="btn btn-outline-dark">Tạo nháp cho cả lớp</button>
                    </form>
                    <a href="{{ route('teacher.evaluations.periods.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Mã</th>
                                <th>Trạng thái</th>
                                <th>Cập nhật</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                @php $student = $report->dangKyLopHoc?->taiKhoan; @endphp
                                <tr>
                                    <td>{{ $student?->hoSoNguoiDung?->hoTen ?? '—' }}</td>
                                    <td>{{ $student?->taiKhoan }}</td>
                                    <td><span class="badge text-bg-light border">{{ $report->trangThaiLabel }}</span></td>
                                    <td>{{ optional($report->updated_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="text-end d-flex justify-content-end gap-2">
                                        <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}"
                                            class="btn btn-sm btn-outline-primary">Mở báo cáo</a>
                                        <a href="{{ route('teacher.evaluations.reports.history', $report->baoCaoHocTapId) }}"
                                            class="btn btn-sm btn-outline-secondary">Lịch sử</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
