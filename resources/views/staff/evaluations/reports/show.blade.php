@extends('layouts.internal')

@section('title', 'Duyệt báo cáo')
@section('page-title', 'Duyệt báo cáo')
@section('breadcrumb', 'Nhân viên · Xem và duyệt báo cáo')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">{{ $metadata['student_name'] ?? 'Học viên' }}</h4>
                        <div class="text-muted">{{ $metadata['class_name'] ?? '—' }} · {{ $report->dotDanhGia?->tenDot }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge text-bg-light border">{{ $report->trangThaiLabel }}</span>
                        <a href="{{ route('staff.evaluations.reports.preview', $report->baoCaoHocTapId) }}" target="_blank"
                            class="btn btn-outline-dark btn-sm">Xem PDF</a>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-3"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Mã học viên</div><div class="fw-semibold">{{ $metadata['student_code'] ?? '—' }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Giáo viên</div><div class="fw-semibold">{{ $metadata['teacher_name'] ?? '—' }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Tỷ lệ tham gia</div><div class="fw-semibold">{{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div></div></div>
                    <div class="col-md-3"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Vắng không phép</div><div class="fw-semibold">{{ data_get($metadata, 'attendance.absent_unexcused', 0) }}</div></div></div>
                </div>
            </div>
        </div>

        @foreach ($sections as $section)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-1">{{ $section['group'] }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Tiêu chí</th>
                                    <th>Giá trị</th>
                                    <th>Nhận xét</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($section['items'] as $item)
                                    <tr>
                                        <td>{{ $item['title'] }}</td>
                                        <td>{{ $item['type'] === 'rating' ? ($item['rating'] ?: '—') : ($item['number'] ?? $item['value'] ?? '—') }}</td>
                                        <td>{{ $item['comment'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card border-0 shadow-sm">
            <div class="card-body d-grid gap-3">
                @if ($report->trangThai === 'submitted')
                    <form method="POST" action="{{ route('staff.evaluations.reports.request-revision', $report->baoCaoHocTapId) }}" class="d-grid gap-2">
                        @csrf
                        <label class="form-label fw-semibold mb-0">Ghi chú trả chỉnh sửa</label>
                        <textarea name="note" class="form-control" rows="4" placeholder="Nêu rõ phần cần bổ sung hoặc chỉnh sửa..." required></textarea>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-danger">Trả chỉnh sửa</button>
                            <button formaction="{{ route('staff.evaluations.reports.approve', $report->baoCaoHocTapId) }}" class="btn btn-success">Duyệt báo cáo</button>
                        </div>
                    </form>
                @elseif ($report->trangThai === 'approved')
                    <form method="POST" action="{{ route('staff.evaluations.reports.publish', $report->baoCaoHocTapId) }}">
                        @csrf
                        <button class="btn btn-primary">Phát hành tới học viên</button>
                    </form>
                @else
                    <div class="alert alert-light border mb-0">
                        Báo cáo đang ở trạng thái <strong>{{ $report->trangThaiLabel }}</strong>. Các thao tác duyệt tương ứng đã được thực hiện hoặc chưa khả dụng.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
