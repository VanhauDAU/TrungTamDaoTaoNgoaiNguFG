@extends('layouts.internal')

@section('title', 'Biên soạn báo cáo học tập')
@section('page-title', 'Biên soạn báo cáo học tập')
@section('breadcrumb', 'Giáo viên · Soạn báo cáo')

@section('content')
    @include('evaluations._theme')

    <div class="container-fluid px-0 report-ui">
        <div class="report-shell">
            <section class="report-hero">
                <div class="report-hero__content">
                    <div>
                        <span class="report-overline">Report Editor</span>
                        <h2 class="report-title">{{ $metadata['student_name'] ?? 'Học viên' }}</h2>
                        <p class="report-subtitle">
                            {{ $metadata['class_name'] ?? '—' }} · {{ $metadata['course_name'] ?? '—' }} · {{ $report->dotDanhGia?->tenDot }}
                        </p>
                    </div>
                    <div class="report-hero__aside">
                        <div class="report-chip-wrap">
                            <span class="report-chip">Mã HV: {{ $metadata['student_code'] ?? '—' }}</span>
                            <span class="report-chip">Level: {{ $metadata['current_level'] ?? '—' }}</span>
                            <span class="report-chip">Trạng thái: {{ $report->trangThaiLabel }}</span>
                        </div>
                        <div class="report-actions">
                            <a href="{{ route('teacher.evaluations.reports.preview', $report->baoCaoHocTapId) }}" target="_blank" class="report-button report-button--secondary">Xem trước PDF</a>
                            <a href="{{ route('teacher.evaluations.reports.history', $report->baoCaoHocTapId) }}" class="report-button report-button--soft">Lịch sử</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel__head">
                    <h4>Thông tin nền để viết báo cáo</h4>
                    <p>Khối thông tin được gom lại để giáo viên đọc nhanh trước khi đi vào từng tiêu chí.</p>
                </div>
                <div class="report-panel__body">
                    <div class="report-section-grid">
                        <div class="report-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                            <div class="report-kv"><div class="report-kv__label">Cơ sở</div><div class="report-kv__value">{{ $metadata['facility_name'] ?? '—' }}</div></div>
                            <div class="report-kv"><div class="report-kv__label">Giáo viên</div><div class="report-kv__value">{{ $metadata['teacher_name'] ?? '—' }}</div></div>
                            <div class="report-kv"><div class="report-kv__label">Ngày khai giảng</div><div class="report-kv__value">{{ $metadata['start_date'] ? \Carbon\Carbon::parse($metadata['start_date'])->format('d/m/Y') : '—' }}</div></div>
                            <div class="report-kv"><div class="report-kv__label">Khoảng đánh giá</div><div class="report-kv__value">{{ $metadata['period_range'] ?: '—' }}</div></div>
                        </div>
                        <div class="report-panel" style="box-shadow:none;">
                            <div class="report-panel__head">
                                <h5>Dữ liệu hệ thống</h5>
                                <p>Tự động lấy từ điểm danh và thông tin lớp học.</p>
                            </div>
                            <div class="report-panel__body">
                                <div class="report-stack">
                                    <div class="report-kv"><div class="report-kv__label">Số buổi học</div><div class="report-kv__value">{{ data_get($metadata, 'attendance.total_sessions', 0) }}</div></div>
                                    <div class="report-kv"><div class="report-kv__label">Vắng không phép</div><div class="report-kv__value">{{ data_get($metadata, 'attendance.absent_unexcused', 0) }}</div></div>
                                    <div class="report-kv"><div class="report-kv__label">Tỷ lệ tham gia</div><div class="report-kv__value">{{ data_get($metadata, 'attendance.attendance_rate', 0) }}%</div></div>
                                    <div id="autosaveStatus" class="report-note">Nháp đang đồng bộ thủ công.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($report->staffReviewNote)
                        <div class="report-alert mt-3">
                            <strong>Ghi chú từ staff:</strong> {{ $report->staffReviewNote }}
                        </div>
                    @endif
                </div>
            </section>

            <form id="reportForm" method="POST" action="{{ route('teacher.evaluations.reports.save', $report->baoCaoHocTapId) }}" class="report-stack">
            @csrf
            @foreach ($sections as $section)
                <section class="report-panel">
                    <div class="report-panel__head">
                        <h4>{{ $section['group'] }}</h4>
                        <p>Nhập nhận xét cụ thể, dễ đọc và nhất quán theo từng tiêu chí.</p>
                    </div>
                    <div class="report-panel__body">
                        <div class="report-criteria">
                            @foreach ($section['items'] as $item)
                                <article class="report-criterion">
                                    <div class="report-criterion__head">
                                        <div>
                                            <div class="report-criterion__title">{{ $item['title'] }}</div>
                                            <div class="report-criterion__code">Mã tiêu chí: {{ $item['code'] }}</div>
                                        </div>
                                        @if ($item['required'])
                                            <span class="report-badge report-badge--warning">Bắt buộc</span>
                                        @endif
                                    </div>

                                        @if ($item['type'] === 'readonly_system')
                                            <div class="report-readonly">{{ $item['value'] ?? '—' }}</div>
                                        @elseif ($item['type'] === 'rating')
                                            <div class="report-form-grid" style="grid-template-columns: minmax(220px, .45fr) minmax(0, 1fr);">
                                                <div class="report-field">
                                                    <label>Mức đánh giá</label>
                                                    <select class="form-select" name="criteria[{{ $item['id'] }}][rating]" @disabled($readOnly)>
                                                        <option value="">Chọn mức đánh giá</option>
                                                        @foreach ($item['options'] as $option)
                                                            <option value="{{ $option }}" @selected($item['rating'] === $option)>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="report-field">
                                                    <label>Nhận xét</label>
                                                    <textarea class="form-control" rows="3" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                                </div>
                                            </div>
                                        @elseif (in_array($item['type'], ['number', 'ratio'], true))
                                            <div class="report-form-grid" style="grid-template-columns: minmax(220px, .45fr) minmax(0, 1fr);">
                                                <div class="report-field">
                                                    <label>Giá trị</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="criteria[{{ $item['id'] }}][number]" value="{{ $item['number'] }}" @disabled($readOnly)>
                                                </div>
                                                <div class="report-field">
                                                    <label>Ghi chú</label>
                                                    <textarea class="form-control" rows="3" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                                </div>
                                            </div>
                                        @else
                                            <div class="report-field">
                                                <label>Nội dung</label>
                                                <textarea class="form-control" rows="4" name="criteria[{{ $item['id'] }}][comment]" @disabled($readOnly)>{{ $item['comment'] }}</textarea>
                                            </div>
                                        @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
            </form>

            <div class="report-sticky">
                <section class="report-panel">
                    <div class="report-panel__body d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <div class="report-note">Autosave sẽ gửi nháp mỗi 20 giây khi có thay đổi.</div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($previousReportAvailable && ! $readOnly)
                            <form method="POST" action="{{ route('teacher.evaluations.reports.copy-previous', $report->baoCaoHocTapId) }}">
                                @csrf
                                <button class="report-button report-button--secondary">Sao chép từ đợt trước</button>
                            </form>
                        @endif
                        <button type="submit" form="reportForm" class="report-button report-button--primary" @disabled($readOnly)>Lưu nháp</button>
                        @if (! $readOnly)
                            <form method="POST" action="{{ route('teacher.evaluations.reports.submit', $report->baoCaoHocTapId) }}">
                                @csrf
                                <button class="report-button report-button--soft">Gửi duyệt</button>
                            </form>
                        @endif
                    </div>
                    </div>
                </section>
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
                    statusEl.className = 'report-note';
                });

                const autosave = async () => {
                    if (!dirty || saving) {
                        return;
                    }

                    saving = true;
                    statusEl.textContent = 'Đang lưu nháp...';
                    statusEl.className = 'report-note';

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
                        statusEl.className = 'report-note';
                    } catch (error) {
                        statusEl.textContent = 'Autosave lỗi. Vui lòng bấm "Lưu nháp" thủ công.';
                        statusEl.className = 'report-note';
                    } finally {
                        saving = false;
                    }
                };

                setInterval(autosave, 20000);
            })();
        </script>
    @endif
@endsection
