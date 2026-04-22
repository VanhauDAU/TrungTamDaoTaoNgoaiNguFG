@extends('layouts.internal')

@section('title', 'Đợt đánh giá học tập')
@section('page-title', 'Đợt đánh giá học tập')
@section('breadcrumb', 'Nhân viên · Tạo và theo dõi đợt đánh giá')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-1">Tạo đợt đánh giá</h5>
                        <p class="text-muted small mb-0">Khi tạo xong, hệ thống sẽ sinh nháp báo cáo cho toàn bộ học viên đang học trong lớp.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('staff.evaluations.periods.store') }}" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label class="form-label fw-semibold">Lớp học</label>
                                <select name="lopHocId" class="form-select" required>
                                    <option value="">Chọn lớp học</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->lopHocId }}">
                                            [{{ $class->maLopHoc }}] {{ $class->tenLopHoc }} · {{ $class->khoaHoc?->tenKhoaHoc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Mẫu rubric</label>
                                <select name="baoCaoHocTapMauId" class="form-select">
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->baoCaoHocTapMauId }}">{{ $template->tenMau }} (v{{ $template->phienBan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Tên đợt</label>
                                <input type="text" name="tenDot" class="form-control" placeholder="Ví dụ: Đợt đánh giá giữa khóa tháng 04/2026" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Từ ngày</label>
                                    <input type="date" name="tuNgay" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Đến ngày</label>
                                    <input type="date" name="denNgay" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hạn nộp</label>
                                    <input type="date" name="hanNop" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Hạn duyệt</label>
                                    <input type="date" name="hanDuyet" class="form-control">
                                </div>
                            </div>
                            <button class="btn btn-primary">Tạo đợt đánh giá</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-1">Đợt đánh giá toàn trung tâm</h5>
                        <p class="text-muted small mb-0">Theo dõi tình trạng sinh nháp, chờ duyệt và phát hành theo từng lớp.</p>
                    </div>
                    <div class="card-body">
                        @if ($periods->isEmpty())
                            <div class="alert alert-light border mb-0">Chưa có đợt đánh giá nào.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Đợt</th>
                                            <th>Lớp</th>
                                            <th>Trạng thái</th>
                                            <th>Tiến độ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($periods as $period)
                                            @php
                                                $total = $period->baoCaos->count();
                                                $published = $period->baoCaos->where('trangThai', 'published')->count();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $period->tenDot }}</div>
                                                    <div class="text-muted small">{{ optional($period->hanNop)->format('d/m/Y') ?? '—' }} / {{ optional($period->hanDuyet)->format('d/m/Y') ?? '—' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $period->lopHoc?->tenLopHoc }}</div>
                                                    <div class="text-muted small">{{ $period->lopHoc?->coSo?->tenCoSo }}</div>
                                                </td>
                                                <td><span class="badge text-bg-light border">{{ $period->trangThaiLabel }}</span></td>
                                                <td>{{ $published }}/{{ $total }} đã phát hành</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
