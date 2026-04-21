@extends('layouts.internal')

@section('title', 'Tra cứu công nợ học viên')
@section('page-title', 'Tài chính')
@section('breadcrumb', 'Quản lý tài chính · Tra cứu công nợ')

@section('content')
    @php
        $portalRouteBase = request()->routeIs('staff.*') ? 'staff' : 'admin';
        $selectedProfile = $selectedStudent?->hoSoNguoiDung;
    @endphp

    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge rounded-pill text-bg-primary-subtle text-primary px-3 py-2">
                            <i class="fas fa-magnifying-glass-dollar me-1"></i> Thu gộp công nợ
                        </span>
                    </div>
                    <h1 class="h4 fw-bold mb-2">Tra cứu học viên và thanh toán toàn bộ công nợ một lần</h1>
                    <p class="text-secondary mb-0">
                        Nhập tài khoản, email, số điện thoại hoặc tên học viên để xem toàn bộ hóa đơn còn nợ. Khi học viên tất toán,
                        hệ thống sẽ tự tạo phiếu thu cho từng hóa đơn ở phía sau chỉ với một lần thao tác.
                    </p>
                </div>
                <div class="d-flex align-items-start">
                    <a href="{{ route($portalRouteBase . '.hoa-don.index') }}" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách hóa đơn
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success rounded-4 border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-4 border-0 shadow-sm">
                <strong>Không thể xử lý thanh toán.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route($portalRouteBase . '.hoa-don.debt-lookup') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Tra cứu học viên</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-secondary"></i></span>
                                <input type="text" name="q" class="form-control"
                                    placeholder="Ví dụ: HV000123, email, số điện thoại hoặc tên học viên"
                                    value="{{ $searchTerm }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <button type="submit" class="btn btn-primary rounded-3 w-100">
                                <i class="fas fa-search me-1"></i> Tìm công nợ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($searchTerm !== '' && $studentMatches->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center text-secondary">
                    <i class="fas fa-user-slash fa-2x mb-3"></i>
                    <div class="fw-semibold text-dark mb-1">Không tìm thấy học viên phù hợp</div>
                    <p class="mb-0">Kiểm tra lại tài khoản, email, số điện thoại hoặc tên học viên rồi thử lại.</p>
                </div>
            </div>
        @endif

        @if ($studentMatches->count() > 1)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h2 class="h6 fw-bold mb-1">Chọn đúng học viên</h2>
                    <p class="text-secondary mb-0">Có {{ $studentMatches->count() }} kết quả khớp với từ khóa bạn nhập.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach ($studentMatches as $student)
                            @php $profile = $student->hoSoNguoiDung; @endphp
                            <div class="col-12 col-xl-6">
                                <div class="border rounded-4 p-3 h-100 d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $profile?->hoTen ?? $student->taiKhoan }}</div>
                                        <div class="text-secondary small">{{ $student->taiKhoan }} · {{ $student->email }}</div>
                                        <div class="text-secondary small">{{ $profile?->soDienThoai ?? 'Chưa có số điện thoại' }}</div>
                                    </div>
                                    <div>
                                        <a href="{{ route($portalRouteBase . '.hoa-don.debt-lookup', ['q' => $searchTerm, 'taiKhoanId' => $student->taiKhoanId]) }}"
                                            class="btn btn-outline-primary rounded-3">
                                            Chọn
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($selectedStudent)
            <form action="{{ route($portalRouteBase . '.hoa-don.debt-lookup.settle') }}" method="POST">
                @csrf
                <input type="hidden" name="taiKhoanId" value="{{ $selectedStudent->taiKhoanId }}">
                <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="badge rounded-pill text-bg-light border px-3 py-2 mb-3">
                                        <i class="fas fa-user-graduate me-1"></i>
                                        {{ $selectedStudent->taiKhoan }}
                                    </div>
                                    <h2 class="h5 fw-bold mb-1">{{ $selectedProfile?->hoTen ?? $selectedStudent->taiKhoan }}</h2>
                                    <div class="text-secondary">{{ $selectedStudent->email }}</div>
                                    <div class="text-secondary">{{ $selectedProfile?->soDienThoai ?? 'Chưa có số điện thoại' }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-start">
                                    <span class="badge rounded-pill text-bg-danger-subtle text-danger px-3 py-2">
                                        {{ $studentDebtSummary['invoiceCount'] }} hóa đơn còn nợ
                                    </span>
                                    <span class="badge rounded-pill text-bg-warning-subtle text-warning px-3 py-2">
                                        {{ $studentDebtSummary['overdueCount'] }} hóa đơn quá hạn
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h2 class="h6 fw-bold mb-1">Danh sách công nợ đang mở</h2>
                            <p class="text-secondary mb-0">Tick các hóa đơn học viên muốn thanh toán trong lần thu này. Hệ thống sẽ chỉ tạo phiếu thu cho các hóa đơn bạn chọn.</p>
                        </div>
                        <div class="card-body p-0">
                            @if ($outstandingInvoices->isEmpty())
                                <div class="p-4 text-secondary">Học viên này hiện không còn công nợ nào cần thu.</div>
                            @else
                                <div class="px-4 pt-3 d-flex justify-content-between align-items-center">
                                    <label class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllInvoices">
                                        <span class="form-check-label fw-semibold">Chọn tất cả hóa đơn đang hiển thị</span>
                                    </label>
                                    <span class="text-secondary small" id="selectedInvoicesHint">Chưa chọn hóa đơn nào</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4" style="width:56px"></th>
                                                <th class="ps-4">Hóa đơn</th>
                                                <th>Nội dung</th>
                                                <th>Hạn thanh toán</th>
                                                <th class="text-end">Còn nợ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($outstandingInvoices as $invoice)
                                                @php
                                                    $code = $invoice->maHoaDon ?: 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT);
                                                    $context = $invoice->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI
                                                        ? ($invoice->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung')
                                                        : ($invoice->lopHocDotThu?->tenDotThu ?? 'Học phí chính');
                                                @endphp
                                                <tr>
                                                    <td class="ps-4">
                                                        <input class="form-check-input debt-invoice-checkbox" type="checkbox"
                                                            name="hoaDonIds[]" value="{{ $invoice->hoaDonId }}"
                                                            data-amount="{{ (float) $invoice->conNo }}"
                                                            @checked(in_array((string) $invoice->hoaDonId, array_map('strval', old('hoaDonIds', [])), true))>
                                                    </td>
                                                    <td class="ps-4">
                                                        <div class="fw-semibold">{{ $code }}</div>
                                                        <div class="small text-secondary">
                                                            {{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Không gắn lớp' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>{{ $context }}</div>
                                                        <div class="small text-secondary">{{ $invoice->nguonThuLabel }}</div>
                                                    </td>
                                                    <td>
                                                        @if ($invoice->ngayHetHan)
                                                            <div>{{ \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') }}</div>
                                                            <div class="small {{ $invoice->isQuaHan ? 'text-danger' : 'text-secondary' }}">
                                                                {{ $invoice->tinhTrangHanLabel ?? 'Đang theo dõi' }}
                                                            </div>
                                                        @else
                                                            <span class="text-secondary">Không đặt hạn</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4 fw-semibold text-danger">
                                                        {{ number_format((float) $invoice->conNo, 0, ',', '.') }}đ
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h2 class="h6 fw-bold mb-3">Tổng hợp công nợ</h2>
                            <div class="d-grid gap-3">
                                <div class="border rounded-4 p-3">
                                    <div class="small text-secondary mb-1">Tổng cần thu ngay</div>
                                    <div class="h4 fw-bold text-danger mb-0">
                                        {{ number_format((float) $studentDebtSummary['outstandingTotal'], 0, ',', '.') }}đ
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <div class="small text-secondary mb-1">Đã thu trước đó</div>
                                            <div class="fw-semibold">
                                                {{ number_format((float) $studentDebtSummary['paidTotal'], 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <div class="small text-secondary mb-1">Sắp hết hạn</div>
                                            <div class="fw-semibold">{{ $studentDebtSummary['dueSoonCount'] }} hóa đơn</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($outstandingInvoices->isNotEmpty())
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h2 class="h6 fw-bold mb-2">Thu các hóa đơn đã chọn</h2>
                                <p class="text-secondary small mb-4">
                                    Một lần xác nhận sẽ tạo phiếu thu cho từng hóa đơn bạn đã tick. Không cần mở từng hóa đơn riêng lẻ.
                                </p>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tài khoản học viên</label>
                                        <input type="text" class="form-control" value="{{ $selectedStudent->taiKhoan }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tổng số tiền của các hóa đơn đã chọn</label>
                                        <input type="text" class="form-control"
                                            value="{{ number_format((float) $studentDebtSummary['outstandingTotal'], 0, ',', '.') }}đ"
                                            id="selectedInvoiceTotalDisplay" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Số hóa đơn được chọn</label>
                                        <input type="text" class="form-control" value="0 hóa đơn" id="selectedInvoiceCountDisplay" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Ngày thu</label>
                                        <input type="date" name="ngayThu" class="form-control"
                                            value="{{ old('ngayThu', now()->format('Y-m-d')) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Phương thức thanh toán</label>
                                        <select name="phuongThucThanhToan" class="form-select" required>
                                            @foreach (\App\Models\Finance\HoaDon::paymentMethodLabels() as $value => $label)
                                                <option value="{{ $value }}" @selected((string) old('phuongThucThanhToan', '1') === (string) $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Ghi chú</label>
                                        <textarea name="ghiChu" rows="3" class="form-control"
                                            placeholder="Ví dụ: học viên đến quầy và tất toán toàn bộ công nợ">{{ old('ghiChu') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary rounded-3 w-100">
                                        <i class="fas fa-cash-register me-1"></i> Thu các hóa đơn đã chọn
                                    </button>
                            </div>
                        </div>
                    @endif
                </div>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('script')
    <script>
        function formatDebtMoney(amount) {
            return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
        }

        function updateSelectedDebtSummary() {
            const checkboxes = [...document.querySelectorAll('.debt-invoice-checkbox')];
            const checked = checkboxes.filter((item) => item.checked);
            const total = checked.reduce((sum, item) => sum + Number(item.dataset.amount || 0), 0);
            const totalDisplay = document.getElementById('selectedInvoiceTotalDisplay');
            const countDisplay = document.getElementById('selectedInvoiceCountDisplay');
            const hintDisplay = document.getElementById('selectedInvoicesHint');
            const selectAllCheckbox = document.getElementById('selectAllInvoices');

            if (totalDisplay) totalDisplay.value = formatDebtMoney(total);
            if (countDisplay) countDisplay.value = `${checked.length} hóa đơn`;
            if (hintDisplay) {
                hintDisplay.textContent = checked.length > 0
                    ? `Đã chọn ${checked.length} hóa đơn · ${formatDebtMoney(total)}`
                    : 'Chưa chọn hóa đơn nào';
            }
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
            }
        }

        document.getElementById('selectAllInvoices')?.addEventListener('change', function () {
            document.querySelectorAll('.debt-invoice-checkbox').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
            updateSelectedDebtSummary();
        });

        document.querySelectorAll('.debt-invoice-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', updateSelectedDebtSummary);
        });

        updateSelectedDebtSummary();
    </script>
@endsection
