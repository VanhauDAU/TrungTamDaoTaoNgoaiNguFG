@extends('layouts.internal')

@section('title', 'Biên soạn báo cáo học tập')
@section('page-title', 'Biên soạn báo cáo học tập')
@section('breadcrumb', 'Giáo viên · Soạn báo cáo')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">{{ $metadata['student_name'] ?? 'Học viên' }}</h4>
                        <div class="text-muted">
                            {{ $metadata['class_name'] ?? '—' }} · {{ $metadata['course_name'] ?? '—' }} · {{ $report->dotDanhGia?->tenDot }}
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-start">
                        <span class="badge text-bg-light border">{{ $report->trangThaiLabel }}</span>
                        <a href="{{ route('teacher.evaluations.reports.preview', $report->baoCaoHocTapId) }}" target="_blank"
                            class="btn btn-outline-dark btn-sm">Xem trước PDF</a>
                        <a href="{{ route('teacher.evaluations.reports.history', $report->baoCaoHocTapId) }}"
                            class="btn btn-outline-secondary btn-sm">Lịch sử</a>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-lg-8">
                        <div class="row row-cols-md-3 row-cols-1 g-3">
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Mã học viên</div><div class="fw-semibold">{{ $metadata['student_code'] ?? '—' }}</div></div></div>
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Cơ sở</div><div class="fw-semibold">{{ $metadata['facility_name'] ?? '—' }}</div></div></div>
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Level hiện tại</div><div class="fw-semibold">{{ $metadata['current_level'] ?? '—' }}</div></div></div>
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Giáo viên</div><div class="fw-semibold">{{ $metadata['teacher_name'] ?? '—' }}</div></div></div>
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Ngày khai giảng</div><div class="fw-semibold">{{ $metadata['start_date'] ? \Carbon\Carbon::parse($metadata['start_date'])->format('d/m/Y') : '—' }}</div></div></div>
                            <div class="col"><div class="border rounded-4 p-3 h-100"><div class="small text-muted">Khoảng đánh giá</div><div class="fw-semibold">{{ $metadata['period_range'] ?: '—' }}</div></div></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="border rounded-4 p-3 h-100 bg-light">
                            <div class="fw-semibold mb-2">Dữ liệu hệ thống</div>
                            <div class="small text-muted">Số buổi học: {{ data_get($metadata, 'attendance.total_sessions', 0) }}</div>
                            <div class="small text-muted">Vắng không phép: {{ data_get($metadata, 'attendance.absent_unexcused', 0) }}</div>
                            <div class="small text-muted">Tỷ lệ tham gia: {{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div>
                            <div id="autosaveStatus" class="small text-success mt-3">Nháp đang đồng bộ thủ công.</div>
                        </div>
                    </div>
                </div>

                @if ($report->staffReviewNote)
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Ghi chú từ staff:</strong> {{ $report->staffReviewNote }}
                    </div>
                @endif
            </div>
        </div>

        <form id="reportForm" method="POST" action="{{ route('teacher.evaluations.reports.save', $report->baoCaoHocTapId) }}" class="d-grid gap-4">
            @csrf
            @foreach ($sections as $section)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-1">{{ $section['group'] }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            @foreach ($section['items'] as $item)
                                <div class="col-12">
                                    <div class="border rounded-4 p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <div class="fw-semibold">{{ $item['title'] }}</div>
                                                <div class="small text-muted">Mã tiêu chí: {{ $item['code'] }}</div>
                                            </div>
                                            @if ($item['required'])
                                                <span class="badge text-bg-warning">Bắt buộc</span>
                                            @endif
                                        </div>

                                        @if ($item['type'] === 'readonly_system')
                                            <div class="form-control bg-light">{{ $item['value'] ?? '—' }}</div>
                                        @elseif ($item['type'] === 'rating')
                                            <div class="row g-3">
                                                <div class="col-lg-4">
                                                    <label class="form-label">Mức đánh giá</label>
                                                    <select class="form-select" name="criteria[{{ $item['id'] }}][rating]" @disabled($readOnly)>
                                                        <option value="">Chọn mức đánh giá</option>
                                                        @foreach ($item['options'] as $option)
                                                            <option value="{{ $option }}" @selected($item['rating'] === $option)>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-8">
                                                    <label class="form-label">Nhận xét</label>
                                                    <textarea class="form-control" rows="3" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                                </div>
                                            </div>
                                        @elseif (in_array($item['type'], ['number', 'ratio'], true))
                                            <div class="row g-3">
                                                <div class="col-lg-4">
                                                    <label class="form-label">Giá trị</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="criteria[{{ $item['id'] }}][number]" value="{{ $item['number'] }}" @disabled($readOnly)>
                                                </div>
                                                <div class="col-lg-8">
                                                    <label class="form-label">Ghi chú</label>
                                                    <textarea class="form-control" rows="3" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                                </div>
                                            </div>
                                        @else
                                            <label class="form-label">Nội dung</label>
                                            <textarea class="form-control" rows="4" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </form>

        <div class="sticky-bottom mt-4 pb-3">
            <div class="card border-0 shadow">
                <div class="card-body d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div class="small text-muted">Autosave sẽ gửi nháp mỗi 20 giây khi có thay đổi.</div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($previousReportAvailable && ! $readOnly)
                            <form method="POST" action="{{ route('teacher.evaluations.reports.copy-previous', $report->baoCaoHocTapId) }}">
                                @csrf
                                <button class="btn btn-outline-secondary">Sao chép từ đợt trước</button>
                            </form>
                        @endif
                        <button type="submit" form="reportForm" class="btn btn-primary" @disabled($readOnly)>Lưu nháp</button>
                        @if (! $readOnly)
                            <form method="POST" action="{{ route('teacher.evaluations.reports.submit', $report->baoCaoHocTapId) }}">
                                @csrf
                                <button class="btn btn-success">Gửi duyệt</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if (! $readOnly)
        <script>
            (() => {
                const form = document.getElementById('reportForm');
                const statusEl = document.getElementById('autosaveStatus');
                let dirty = false;
                let saving = false;

                if (!form || !statusEl) {
                    return;
                }

                form.addEventListener('input', () => {
                    dirty = true;
                    statusEl.textContent = 'Có thay đổi chưa lưu.';
                    statusEl.className = 'small text-warning mt-3';
                });

                const autosave = async () => {
                    if (!dirty || saving) {
                        return;
                    }

                    saving = true;
                    statusEl.textContent = 'Đang lưu nháp...';
                    statusEl.className = 'small text-muted mt-3';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                        });

                        if (!response.ok) {
                            throw new Error('Autosave failed');
                        }

                        const data = await response.json();
                        dirty = false;
                        statusEl.textContent = 'Đã tự lưu lúc ' + (data.savedAt || '--:--:--');
                        statusEl.className = 'small text-success mt-3';
                    } catch (error) {
                        statusEl.textContent = 'Autosave lỗi. Vui lòng bấm "Lưu nháp" thủ công.';
                        statusEl.className = 'small text-danger mt-3';
                    } finally {
                        saving = false;
                    }
                };

                setInterval(autosave, 20000);
            })();
        </script>
    @endif
@endsection
