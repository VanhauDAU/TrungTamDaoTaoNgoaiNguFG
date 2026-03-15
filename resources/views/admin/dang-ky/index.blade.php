@extends('layouts.admin')

@section('title', 'Đăng ký học')
@section('page-title', 'Đăng ký học')
@section('breadcrumb', 'Quản lý học viên / Đăng ký học')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Quản lý đăng ký học</h1>
                <p class="text-muted mb-0">Theo dõi đăng ký, xử lý xác nhận, hủy, bảo lưu, khôi phục và điều chuyển lớp.</p>
            </div>
            @if (auth()->user()->canDo('dang_ky', 'them'))
                <a href="{{ route('admin.dang-ky.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo đăng ký tại quầy
                </a>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Tổng đăng ký</div>
                        <div class="display-6 fw-semibold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Chờ thanh toán</div>
                        <div class="display-6 fw-semibold text-warning">{{ number_format($summary['pending']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Đang chiếm chỗ</div>
                        <div class="display-6 fw-semibold text-primary">{{ number_format($summary['active']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Giữ chỗ quá hạn</div>
                        <div class="display-6 fw-semibold text-danger">{{ number_format($summary['holdsExpired']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.dang-ky.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="q" value="{{ request('q') }}"
                            placeholder="Tên học viên, email, tài khoản, lớp học...">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">Lớp học</label>
                        <select class="form-select" name="lopHocId">
                            <option value="">Tất cả lớp học</option>
                            @foreach ($lopHocs as $class)
                                <option value="{{ $class->lopHocId }}" @selected((string) request('lopHocId') === (string) $class->lopHocId)>
                                    {{ $class->tenLopHoc }} · {{ $class->khoaHoc?->tenKhoaHoc ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trangThai">
                            <option value="">Tất cả</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) request('trangThai') === (string) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($errors->has('registration'))
            <div class="alert alert-danger">{{ $errors->first('registration') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Học viên</th>
                            <th>Lớp học</th>
                            <th>Ngày đăng ký</th>
                            <th>Học phí</th>
                            <th>Trạng thái</th>
                            <th>Giữ chỗ đến</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registrations as $registration)
                            @php
                                $badgeClass = match ((int) $registration->trangThai) {
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN => 'text-bg-warning',
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN => 'text-bg-primary',
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_DANG_HOC => 'text-bg-success',
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI => 'text-bg-danger',
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_BAO_LUU => 'text-bg-secondary',
                                    \App\Models\Education\DangKyLopHoc::TRANG_THAI_HUY => 'text-bg-dark',
                                    default => 'text-bg-light',
                                };
                                $tongDaThu = (float) $registration->hoaDons->sum('daTra');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $registration->taiKhoan?->hoSoNguoiDung?->hoTen ?? $registration->taiKhoan?->taiKhoan ?? '—' }}</div>
                                    <div class="text-muted small">{{ $registration->taiKhoan?->email ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $registration->lopHoc?->tenLopHoc ?? '—' }}</div>
                                    <div class="text-muted small">
                                        {{ $registration->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}
                                        @if ($registration->lopHoc?->coSo?->tenCoSo)
                                            · {{ $registration->lopHoc->coSo->tenCoSo }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $registration->ngayDangKy ? \Carbon\Carbon::parse($registration->ngayDangKy)->format('d/m/Y H:i') : '—' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ number_format((float) $registration->hocPhiTongTien, 0, ',', '.') }}đ</div>
                                    <div class="text-muted small">Đã thu {{ number_format($tongDaThu, 0, ',', '.') }}đ</div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $registration->trangThaiLabel }}</span>
                                </td>
                                <td>
                                    @if ($registration->ngayHetHanGiuCho)
                                        <div>{{ $registration->ngayHetHanGiuCho->format('d/m/Y H:i') }}</div>
                                        @if ($registration->isHoldExpired())
                                            <div class="text-danger small">Đã quá hạn</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        @if (auth()->user()->canDo('dang_ky', 'sua'))
                                            <form action="{{ route('admin.dang-ky.confirm', $registration->dangKyLopHocId) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    @disabled(!in_array((int) $registration->trangThai, [\App\Models\Education\DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN, \App\Models\Education\DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI], true))>
                                                    Xác nhận
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.dang-ky.hold', $registration->dangKyLopHocId) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                    @disabled(!in_array((int) $registration->trangThai, [\App\Models\Education\DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN, \App\Models\Education\DangKyLopHoc::TRANG_THAI_DANG_HOC, \App\Models\Education\DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI], true))>
                                                    Bảo lưu
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.dang-ky.restore', $registration->dangKyLopHocId) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    @disabled(!in_array((int) $registration->trangThai, [\App\Models\Education\DangKyLopHoc::TRANG_THAI_HUY, \App\Models\Education\DangKyLopHoc::TRANG_THAI_BAO_LUU], true))>
                                                    Khôi phục
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-sm btn-outline-dark"
                                                data-bs-toggle="modal" data-bs-target="#transferModal{{ $registration->dangKyLopHocId }}"
                                                @disabled(in_array((int) $registration->trangThai, [\App\Models\Education\DangKyLopHoc::TRANG_THAI_HUY, \App\Models\Education\DangKyLopHoc::TRANG_THAI_HOAN_THANH], true))>
                                                Chuyển lớp
                                            </button>
                                        @endif

                                        @if (auth()->user()->canDo('dang_ky', 'xoa'))
                                            <form action="{{ route('admin.dang-ky.cancel', $registration->dangKyLopHocId) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    @disabled(in_array((int) $registration->trangThai, [\App\Models\Education\DangKyLopHoc::TRANG_THAI_HUY, \App\Models\Education\DangKyLopHoc::TRANG_THAI_HOAN_THANH], true))>
                                                    Hủy
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Chưa có đăng ký học nào phù hợp với bộ lọc hiện tại.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($registrations->hasPages())
                <div class="card-footer bg-white">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    </div>

    @foreach ($registrations as $registration)
        <div class="modal fade" id="transferModal{{ $registration->dangKyLopHocId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.dang-ky.transfer', $registration->dangKyLopHocId) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Điều chuyển lớp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">Chỉ nên điều chuyển trước khi phát sinh thu tiền. Hệ thống sẽ hủy đăng ký cũ và tạo đăng ký mới ở lớp đích.</p>
                            <div class="mb-3">
                                <label class="form-label">Lớp đích</label>
                                <select name="targetLopHocId" class="form-select" required>
                                    <option value="">Chọn lớp đích</option>
                                    @foreach ($lopHocs as $class)
                                        @if ((int) $class->lopHocId !== (int) $registration->lopHocId)
                                            <option value="{{ $class->lopHocId }}">
                                                {{ $class->tenLopHoc }} · {{ $class->khoaHoc?->tenKhoaHoc ?? '—' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Hình thức thanh toán cho đăng ký mới</label>
                                <select name="payment_method" class="form-select" required>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-dark">Xác nhận điều chuyển</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
