@extends('layouts.internal')

@section('title', 'Lịch dạy')
@section('page-title', 'Lịch dạy')
@section('breadcrumb', 'Buổi học theo giáo viên')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-1">Buổi học theo lịch giảng dạy</h5>
                        <p class="text-muted mb-0">Các buổi học được lọc theo lớp mà giáo viên hiện tại phụ trách.</p>
                    </div>
                    <div class="badge bg-primary-subtle text-primary-emphasis">{{ $sessions->total() }} buổi</div>
                </div>

                @if ($sessions->isEmpty())
                    <div class="border rounded-4 p-5 text-center text-muted">
                        <i class="fas fa-calendar-xmark fs-1 mb-3"></i>
                        <p class="mb-0">Chưa có buổi học nào trong lịch.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Buổi học</th>
                                    <th>Lớp</th>
                                    <th>Ngày học</th>
                                    <th>Phòng</th>
                                    <th>Ca học</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sessions as $session)
                                    <tr>
                                        <td>{{ $session->tenBuoiHoc ?: 'Buổi học #' . $session->buoiHocId }}</td>
                                        <td>{{ $session->lopHoc->tenLopHoc ?? '—' }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($session->ngayHoc)->format('d/m/Y') }}</td>
                                        <td>{{ $session->phongHoc->tenPhong ?? 'Chưa gán' }}</td>
                                        <td>{{ $session->caHoc->tenCaHoc ?? 'Chưa gán' }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $session->trang_thai_label }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
