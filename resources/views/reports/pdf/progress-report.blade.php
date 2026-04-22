<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo học tập</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 18px; }
        .title { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
        .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta-grid td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        .section-title { font-size: 15px; font-weight: 700; margin: 20px 0 10px; color: #0f172a; }
        .criteria-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .criteria-table th, .criteria-table td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        .criteria-table th { background: #f3f4f6; text-align: left; }
        .footer { margin-top: 24px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BÁO CÁO HỌC TẬP</div>
        <div>Đợt đánh giá: {{ $report->dotDanhGia?->tenDot }}</div>
        <div>Ngày phát hành: {{ optional($report->publishedAt)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td><strong>Họ tên</strong><br>{{ $metadata['student_name'] ?? '—' }}</td>
            <td><strong>Mã học viên</strong><br>{{ $metadata['student_code'] ?? '—' }}</td>
            <td><strong>Lớp</strong><br>{{ $metadata['class_name'] ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Khóa học</strong><br>{{ $metadata['course_name'] ?? '—' }}</td>
            <td><strong>Cơ sở</strong><br>{{ $metadata['facility_name'] ?? '—' }}</td>
            <td><strong>Giáo viên</strong><br>{{ $metadata['teacher_name'] ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Level hiện tại</strong><br>{{ $metadata['current_level'] ?? '—' }}</td>
            <td><strong>Ngày khai giảng</strong><br>{{ !empty($metadata['start_date']) ? \Carbon\Carbon::parse($metadata['start_date'])->format('d/m/Y') : '—' }}</td>
            <td><strong>Khoảng đánh giá</strong><br>{{ $metadata['period_range'] ?? '—' }}</td>
        </tr>
    </table>

    @foreach ($groupedCriteria as $group => $items)
        <div class="section-title">{{ $group }}</div>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Tiêu chí</th>
                    <th style="width: 18%;">Giá trị</th>
                    <th>Nhận xét</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->tenTieuChi }}</td>
                        <td>{{ $item->giaTriMucDanhGia ?: ($item->giaTriSo ?? $item->noiDungNhanXet ?? '—') }}</td>
                        <td>{{ $item->noiDungNhanXet ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Tài liệu được phát hành từ hệ thống trung tâm. Dữ liệu hiển thị là snapshot tại thời điểm phát hành để đảm bảo tính nhất quán lịch sử.
    </div>
</body>
</html>
