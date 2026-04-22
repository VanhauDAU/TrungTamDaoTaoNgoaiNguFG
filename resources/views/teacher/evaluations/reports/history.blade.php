@extends('layouts.internal')

@section('title', 'Lịch sử báo cáo')
@section('page-title', 'Lịch sử báo cáo')
@section('breadcrumb', 'Giáo viên · Nhật ký xử lý báo cáo')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1">{{ $report->dangKyLopHoc?->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</h4>
                    <div class="text-muted">{{ $report->dotDanhGia?->tenDot }} · {{ $report->dotDanhGia?->lopHoc?->tenLopHoc }}</div>
                </div>
                <a href="{{ route('teacher.evaluations.reports.edit', $report->baoCaoHocTapId) }}" class="btn btn-outline-dark btn-sm">Quay lại báo cáo</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @forelse ($history as $event)
                    <div class="border-start border-3 ps-3 pb-4 ms-2">
                        <div class="fw-semibold">{{ $event->hanhDong }}</div>
                        <div class="small text-muted">
                            {{ optional($event->created_at)->format('d/m/Y H:i') }} ·
                            {{ $event->nguoiThucHien?->hoSoNguoiDung?->hoTen ?? $event->nguoiThucHien?->taiKhoan ?? 'Hệ thống' }}
                        </div>
                        @if ($event->trangThaiTruoc || $event->trangThaiSau)
                            <div class="small mt-1">Trạng thái: {{ $event->trangThaiTruoc ?? '—' }} → {{ $event->trangThaiSau ?? '—' }}</div>
                        @endif
                        @if ($event->ghiChu)
                            <div class="mt-2">{{ $event->ghiChu }}</div>
                        @endif
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">Chưa có lịch sử xử lý nào.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
