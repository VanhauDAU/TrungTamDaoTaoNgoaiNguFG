@extends('layouts.admin')

@section('title', 'Tạo đăng ký học')
@section('page-title', 'Tạo đăng ký học')
@section('breadcrumb', 'Quản lý học viên / Đăng ký học / Tạo mới')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Tạo đăng ký tại quầy</h1>
                <p class="text-muted mb-0">Sinh đăng ký, hóa đơn học phí và phụ phí mặc định theo chính sách giá của lớp.</p>
            </div>
            <a href="{{ route('admin.dang-ky.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.dang-ky.store') }}" method="POST" class="row g-4">
                    @csrf

                    <div class="col-lg-6">
                        <label for="taiKhoanId" class="form-label fw-semibold">Học viên</label>
                        <select class="form-select @error('taiKhoanId') is-invalid @enderror" id="taiKhoanId" name="taiKhoanId" required>
                            <option value="">Chọn học viên</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->taiKhoanId }}" @selected((string) old('taiKhoanId') === (string) $student->taiKhoanId)>
                                    {{ $student->hoSoNguoiDung?->hoTen ?? $student->taiKhoan }} · {{ $student->taiKhoan }} · {{ $student->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('taiKhoanId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <label for="lopHocId" class="form-label fw-semibold">Lớp học</label>
                        <select class="form-select @error('lopHocId') is-invalid @enderror" id="lopHocId" name="lopHocId" required>
                            <option value="">Chọn lớp học</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->lopHocId }}" @selected((string) old('lopHocId') === (string) $class->lopHocId)>
                                    {{ $class->tenLopHoc }} · {{ $class->khoaHoc?->tenKhoaHoc ?? '—' }} · {{ $class->coSo?->tenCoSo ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                        @error('lopHocId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label for="paymentMethod" class="form-label fw-semibold">Hình thức thanh toán</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" id="paymentMethod" name="payment_method" required>
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('payment_method', '2') === (string) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        @if ($errors->has('registration'))
                            <div class="alert alert-danger mb-0">{{ $errors->first('registration') }}</div>
                        @endif
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.dang-ky.index') }}" class="btn btn-light">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Tạo đăng ký
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
