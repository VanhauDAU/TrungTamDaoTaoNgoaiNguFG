@extends('layouts.internal')

@section('title', 'Điểm danh')
@section('page-title', 'Điểm danh')
@section('breadcrumb', 'Giáo viên · Điểm danh theo buổi học')

@section('content')
    <style>
        .ta-wrap {
            display: grid;
            gap: 18px;
        }

        .ta-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .06);
        }

        .ta-card-head {
            padding: 18px 22px 12px;
            border-bottom: 1px solid #eef2f7;
        }

        .ta-card-head h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .ta-card-head p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: .84rem;
        }

        .ta-card-body {
            padding: 18px 22px 22px;
        }

        .ta-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }

        .ta-field label {
            display: block;
            font-size: .77rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
        }

        .ta-field select,
        .ta-note-input {
            width: 100%;
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 12px;
            font-size: .87rem;
            background: #fff;
        }

        .ta-note-input {
            min-height: 40px;
            padding: 8px 10px;
        }

        .ta-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 14px;
        }

        .ta-btn {
            min-height: 42px;
            padding: 0 16px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .ta-btn-primary {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: #fff;
        }

        .ta-btn-secondary {
            background: #eef2ff;
            color: #3730a3;
        }

        .ta-btn-light {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .ta-compact-head {
            display: grid;
            grid-template-columns: 1.8fr .9fr;
            gap: 14px;
            align-items: start;
        }

        .ta-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .ta-summary-chip {
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #f8fafc;
            padding: 8px 12px;
            font-size: .82rem;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .ta-summary-chip strong {
            color: #0f172a;
        }

        .ta-note-box {
            border: 1px solid #dbeafe;
            background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 14px;
        }

        .ta-note-box label {
            display: block;
            font-size: .76rem;
            font-weight: 800;
            color: #334155;
            margin-bottom: 8px;
        }

        .ta-note-box textarea {
            width: 100%;
            min-height: 82px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 12px;
            resize: vertical;
            font-size: .87rem;
            background: #fff;
        }

        .ta-state-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .ta-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .78rem;
            font-weight: 700;
        }

        .ta-badge-open {
            background: #ecfdf5;
            color: #047857;
        }

        .ta-badge-locked {
            background: #fef2f2;
            color: #b91c1c;
        }

        .ta-table-wrap {
            overflow-x: auto;
        }

        .ta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ta-table th,
        .ta-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
            text-align: left;
            font-size: .86rem;
        }

        .ta-table th {
            background: #f8fafc;
            color: #475569;
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 800;
        }

        .ta-table tbody tr:hover td {
            background: #fcfcff;
        }

        .ta-checkbox {
            width: 18px;
            height: 18px;
        }

        .ta-student {
            display: grid;
            gap: 2px;
        }

        .ta-student strong {
            color: #0f172a;
        }

        .ta-student small {
            color: #64748b;
        }

        .ta-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            color: #64748b;
        }

        .ta-loading {
            opacity: .65;
            pointer-events: none;
        }

        .ta-muted {
            color: #64748b;
            font-size: .82rem;
        }

        @media (max-width: 1024px) {
            .ta-filter-grid,
            .ta-compact-head {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .ta-filter-grid,
            .ta-compact-head {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="ta-wrap">
        @if (session('success'))
            <div class="kf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="kf-alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        <div class="ta-card">
            <div class="ta-card-head">
                <h3>Bộ lọc điểm danh</h3>
                <p>Chọn khóa học, lớp học và buổi học để lấy danh sách điểm danh của giáo viên phụ trách.</p>
            </div>
            <div class="ta-card-body">
                <form method="GET" action="{{ route('teacher.attendance.index') }}">
                    <div class="ta-filter-grid">
                        <div class="ta-field">
                            <label for="khoaHocId">Khóa học</label>
                            <select id="khoaHocId" name="khoaHocId"
                                data-classes-url="{{ route('teacher.attendance.classes') }}"
                                data-sessions-url="{{ route('teacher.attendance.sessions') }}">
                                <option value="">Chọn khóa học</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->khoaHocId }}" @selected($selectedCourseId === $course->khoaHocId)>
                                        {{ $course->tenKhoaHoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ta-field">
                            <label for="lopHocId">Lớp học</label>
                            <select id="lopHocId" name="lopHocId">
                                <option value="">Chọn lớp học</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->lopHocId }}" @selected(optional($selectedClass)->lopHocId === $class->lopHocId)>
                                        [{{ $class->maLopHoc }}] {{ $class->tenLopHoc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ta-field">
                            <label for="buoiHocId">Buổi học</label>
                            <select id="buoiHocId" name="buoiHocId">
                                <option value="">Chọn buổi học</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->buoiHocId }}" @selected(optional($selectedSession)->buoiHocId === $session->buoiHocId)>
                                        {{ $session->tenBuoiHoc ?: ('Buổi #' . $session->buoiHocId) }}
                                        - {{ $session->ngayHoc ? \Illuminate\Support\Carbon::parse($session->ngayHoc)->format('d/m/Y') : '—' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ta-field">
                            <label>&nbsp;</label>
                            <button type="submit" class="ta-btn ta-btn-primary">
                                <i class="fas fa-list-check"></i> Lấy danh sách điểm danh
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($selectedSession)
            <div class="ta-card">
                <div class="ta-card-body">
                    <div class="ta-state-row">
                        <span class="ta-badge {{ $isSessionLocked ? 'ta-badge-locked' : 'ta-badge-open' }}">
                            <i class="fas {{ $isSessionLocked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                            {{ $isSessionLocked ? 'Buổi học đã khóa điểm danh' : 'Buổi học đang mở điểm danh' }}
                        </span>
                        <span class="ta-muted">
                            Cập nhật gần nhất:
                            <strong>{{ $latestAttendanceAt ? $latestAttendanceAt->format('d/m/Y H:i:s') : 'Chưa có dữ liệu' }}</strong>
                        </span>
                    </div>

                    <div class="ta-compact-head">
                        <div class="ta-summary">
                            <span class="ta-summary-chip"><i class="fas fa-graduation-cap"></i> <strong>{{ $selectedSession->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-users"></i> <strong>{{ $selectedSession->lopHoc?->tenLopHoc ?? '—' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-calendar-day"></i> <strong>{{ $selectedSession->ngayHoc ? \Illuminate\Support\Carbon::parse($selectedSession->ngayHoc)->format('d/m/Y') : '—' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-clock"></i> <strong>{{ $selectedSession->caHoc?->tenCa ?? '—' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-building"></i> <strong>{{ $selectedSession->lopHoc?->coSo?->tenCoSo ?? '—' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-door-open"></i> <strong>{{ $selectedSession->phongHoc?->tenPhong ?? 'Chưa xếp phòng' }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-signal"></i> <strong>{{ $selectedSession->trangThaiLabel }}</strong></span>
                            <span class="ta-summary-chip"><i class="fas fa-user-check"></i> <strong>{{ $attendanceRows->count() }} học viên</strong></span>
                        </div>
                        <div class="ta-actions" style="margin-top:0;justify-content:flex-end">
                            <a href="{{ route('teacher.attendance.export', $selectedSession->buoiHocId) }}" class="ta-btn ta-btn-secondary">
                                <i class="fas fa-file-export"></i> Xuất danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ta-card">
                <div class="ta-card-head">
                    <h3>Danh sách học viên điểm danh</h3>
                    <p>Tập trung vào danh sách học viên. Nội dung buổi học và ghi chú chung được nhập ngay phía trên bảng để thao tác nhanh hơn.</p>
                </div>
                <div class="ta-card-body">
                    @if ($attendanceRows->isEmpty())
                        <div class="ta-empty">
                            Không có học viên nào thuộc diện điểm danh cho buổi học này.
                        </div>
                    @else
                        <form method="POST" action="{{ route('teacher.attendance.store', $selectedSession->buoiHocId) }}">
                            @csrf
                            <div class="ta-compact-head" style="margin-bottom:16px">
                                <div class="ta-note-box">
                                    <label for="noiDungDiemDanh">Nội dung điểm danh / ghi chú chung của buổi học</label>
                                    <textarea id="noiDungDiemDanh" name="noiDungDiemDanh" placeholder="Ví dụ: kiểm tra bài cũ, luyện speaking chủ đề du lịch, nhắc nộp bài tập unit 4..."
                                        {{ $isSessionLocked ? 'disabled' : '' }}>{{ old('noiDungDiemDanh', $selectedSessionNote) }}</textarea>
                                    <div class="ta-muted" style="margin-top:8px">
                                        Nội dung này được lưu theo buổi học và xuất kèm danh sách điểm danh.
                                    </div>
                                </div>
                                <div class="ta-note-box">
                                    <label>Ràng buộc điểm danh</label>
                                    <div class="ta-muted" style="line-height:1.7">
                                        Mặc định học viên được tính là <strong>có mặt</strong>. Bỏ chọn checkbox nếu học viên vắng.
                                        Khi buổi học đã hoàn thành, đã hủy hoặc đổi lịch, hệ thống sẽ khóa lưu điểm danh.
                                    </div>
                                </div>
                            </div>
                            <div class="ta-table-wrap">
                                <table class="ta-table">
                                    <thead>
                                        <tr>
                                            <th style="width:60px">STT</th>
                                            <th style="min-width:120px">Tài khoản</th>
                                            <th style="min-width:220px">Họ tên</th>
                                            <th style="min-width:120px">Ngày sinh</th>
                                            <th style="width:120px">Có mặt</th>
                                            <th style="width:160px">% vắng hiện tại</th>
                                            <th style="min-width:220px">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attendanceRows as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $row['taiKhoan'] }}</strong></td>
                                                <td>
                                                    <div class="ta-student">
                                                        <strong>{{ $row['hoTen'] }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ $row['ngaySinh'] }}</td>
                                                <td>
                                                    <label style="display:inline-flex;align-items:center;gap:8px;font-weight:700;color:#0f172a">
                                                        <input type="hidden" name="attendance[{{ $row['student_id'] }}]" value="0">
                                                        <input type="checkbox" class="ta-checkbox" name="attendance[{{ $row['student_id'] }}]" value="1"
                                                            {{ $row['is_present'] ? 'checked' : '' }}
                                                            {{ $isSessionLocked ? 'disabled' : '' }}>
                                                        Có mặt
                                                    </label>
                                                </td>
                                                <td>
                                                    <strong>{{ $row['absence_percent'] }}%</strong>
                                                </td>
                                                <td>
                                                    <input type="text" class="ta-note-input" name="ghiChu[{{ $row['student_id'] }}]"
                                                        value="{{ $row['ghiChu'] }}" placeholder="Ghi chú thêm"
                                                        {{ $isSessionLocked ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="ta-actions">
                                <button type="submit" class="ta-btn ta-btn-primary" {{ $isSessionLocked ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i> Lưu điểm danh
                                </button>
                                <span class="ta-muted">
                                    {{ $isSessionLocked ? 'Buổi học đã khóa, chỉ còn xem hoặc xuất danh sách.' : 'Bạn có thể cập nhật lại điểm danh cho đến trước khi buổi học được đánh dấu hoàn thành.' }}
                                </span>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        (() => {
            const courseSelect = document.getElementById('khoaHocId');
            const classSelect = document.getElementById('lopHocId');
            const sessionSelect = document.getElementById('buoiHocId');

            if (!courseSelect || !classSelect || !sessionSelect) {
                return;
            }

            const classesUrl = courseSelect.dataset.classesUrl;
            const sessionsUrl = courseSelect.dataset.sessionsUrl;
            const selectedClassId = @json(optional($selectedClass)->lopHocId);
            const selectedSessionId = @json(optional($selectedSession)->buoiHocId);

            function setLoading(select, isLoading, placeholder) {
                select.classList.toggle('ta-loading', isLoading);
                select.disabled = isLoading;
                if (placeholder) {
                    select.innerHTML = `<option value="">${placeholder}</option>`;
                }
            }

            function fillOptions(select, items, selectedValue, emptyLabel) {
                const options = [`<option value="">${emptyLabel}</option>`];
                items.forEach((item) => {
                    const selected = String(selectedValue || '') === String(item.id) ? 'selected' : '';
                    options.push(`<option value="${item.id}" ${selected}>${item.label}</option>`);
                });
                select.innerHTML = options.join('');
            }

            async function loadClasses(courseId, presetClassId = '') {
                if (!courseId) {
                    fillOptions(classSelect, [], '', 'Chọn lớp học');
                    fillOptions(sessionSelect, [], '', 'Chọn buổi học');
                    return;
                }

                setLoading(classSelect, true, 'Đang tải lớp học...');
                fillOptions(sessionSelect, [], '', 'Chọn buổi học');

                try {
                    const response = await fetch(`${classesUrl}?khoaHocId=${encodeURIComponent(courseId)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();
                    fillOptions(classSelect, payload.data || [], presetClassId, 'Chọn lớp học');
                } catch (error) {
                    fillOptions(classSelect, [], '', 'Không tải được lớp học');
                } finally {
                    classSelect.disabled = false;
                    classSelect.classList.remove('ta-loading');
                }
            }

            async function loadSessions(classId, presetSessionId = '') {
                if (!classId) {
                    fillOptions(sessionSelect, [], '', 'Chọn buổi học');
                    return;
                }

                setLoading(sessionSelect, true, 'Đang tải buổi học...');

                try {
                    const response = await fetch(`${sessionsUrl}?lopHocId=${encodeURIComponent(classId)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();
                    fillOptions(sessionSelect, payload.data || [], presetSessionId, 'Chọn buổi học');
                } catch (error) {
                    fillOptions(sessionSelect, [], '', 'Không tải được buổi học');
                } finally {
                    sessionSelect.disabled = false;
                    sessionSelect.classList.remove('ta-loading');
                }
            }

            courseSelect.addEventListener('change', async () => {
                await loadClasses(courseSelect.value);
            });

            classSelect.addEventListener('change', async () => {
                await loadSessions(classSelect.value);
            });

            if (courseSelect.value && selectedClassId) {
                loadClasses(courseSelect.value, selectedClassId).then(() => {
                    if (selectedClassId && selectedSessionId) {
                        loadSessions(selectedClassId, selectedSessionId);
                    }
                });
            }
        })();
    </script>
@endsection
