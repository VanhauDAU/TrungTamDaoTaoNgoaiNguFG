@extends('layouts.admin')

@section('title', 'Chi tiết liên hệ')
@section('page-title', 'Chi tiết Liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Danh sách liên hệ · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/edit.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-file-alt me-2" style="color:#27c4b5"></i>Chi tiết liên hệ
            <span>#{{ $lienHe->LienHeId }}</span>
        </div>
        <div>
            <a href="{{ route('admin.lien-he.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="lh-card">
        <div class="lh-section-title">
            <i class="fas fa-info-circle" style="color: #4a5568"></i> Thông tin khách hàng
        </div>

        <div class="lh-detail-grid">
            <div class="lh-detail-group">
                <div class="lh-detail-label">Người gửi</div>
                <div class="lh-detail-value">{{ $lienHe->hoTen }}</div>
            </div>

            <div class="lh-detail-group">
                <div class="lh-detail-label">Thời gian gửi</div>
                <div class="lh-detail-value">{{ $lienHe->created_at->format('d/m/Y H:i:s') }}</div>
            </div>

            <div class="lh-detail-group">
                <div class="lh-detail-label">Email</div>
                <div class="lh-detail-value">{{ $lienHe->email ?? '—' }}</div>
            </div>

            <div class="lh-detail-group">
                <div class="lh-detail-label">Số điện thoại</div>
                <div class="lh-detail-value">{{ $lienHe->soDienThoai ?? '—' }}</div>
            </div>
        </div>

        <div class="lh-section-title">
            <i class="fas fa-align-left" style="color: #4a5568"></i> Nội dung liên hệ
        </div>

        <div class="lh-detail-group" style="margin-bottom: 32px">
            <div class="lh-detail-label">Tiêu đề: <span
                    style="color: #1a2b3c; font-size: 0.95rem;">{{ $lienHe->tieuDe }}</span></div>
            <div class="lh-detail-value content-box">{{ $lienHe->noiDung }}</div>
        </div>

        <div class="lh-section-title">
            <i class="fas fa-tasks" style="color: #4a5568"></i> Trạng thái xử lý
        </div>

        <form action="{{ route('admin.lien-he.update', $lienHe->LienHeId) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="lh-form-group" style="max-width: 400px">
                <label class="lh-form-label">Cập nhật trạng thái</label>
                <select name="trangThai" class="lh-form-control">
                    <option value="0" {{ $lienHe->trangThai == 0 ? 'selected' : '' }}>Chưa xử lý (Đang chờ)</option>
                    <option value="1" {{ $lienHe->trangThai == 1 ? 'selected' : '' }}>Đã xử lý (Hoàn tất)</option>
                </select>
                @error('trangThai')
                    <div style="color:#e53e3e; font-size:0.875rem; margin-top:4px">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: 24px">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Cập nhật trạng thái
                </button>
            </div>
        </form>
    </div>

@endsection
