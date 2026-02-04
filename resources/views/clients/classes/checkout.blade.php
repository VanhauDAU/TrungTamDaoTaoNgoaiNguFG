@extends('layouts.client')
@section('title', 'Xác nhận đăng ký - ' . $class->tenLopHoc)

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/courseDetail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/classesDetail.css') }}">
    <style>
        .checkout-page {
            padding: 60px 0;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .checkout-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .section-header-checkout {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #10454F;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover,
        .payment-option.active {
            border-color: #27C4B5;
            background: #f0fdfb;
        }

        .payment-option input[type="radio"] {
            margin-right: 15px;
            accent-color: #27C4B5;
            width: 20px;
            height: 20px;
        }

        .payment-label {
            flex: 1;
            font-weight: 600;
            color: #2D3436;
        }

        .payment-icon {
            font-size: 24px;
            color: #10454F;
            width: 40px;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
            color: #636e72;
        }

        .summary-row.total {
            border-top: 1px dashed #ddd;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #10454F;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #10454F 0%, #27C4B5 100%);
            color: #fff;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 196, 181, 0.4);
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <section class="checkout-page">
        <div class="custom-container">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a
                            href="{{ route('home.classes.show', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug]) }}">Quay
                            lại lớp học</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Xác nhận đăng ký</li>
                </ol>
            </nav>

            <form
                action="{{ route('home.classes.process', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug]) }}"
                method="POST" id="checkoutForm">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-8">
                        {{-- THÔNG TIN HỌC VIÊN --}}
                        <div class="checkout-card">
                            <div class="section-header-checkout">
                                <h3 class="section-title"><i class="fas fa-user-circle me-2"></i>Thông tin học viên</h3>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Họ và tên</label>
                                    <input type="text" class="form-control fw-bold"
                                        value="{{ $user->hoSoNguoiDung->hoTen ?? $user->name }}" readonly disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Email</label>
                                    <input type="email" class="form-control fw-bold" value="{{ $user->email }}" readonly
                                        disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Số điện thoại</label>
                                    <input type="text" class="form-control"
                                        value="{{ $user->hoSoNguoiDung->soDienThoai ?? 'Chưa cập nhật' }}" readonly
                                        disabled>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="alert alert-info border-0 bg-light mb-0">
                                        <i class="fas fa-info-circle me-1"></i> Thông tin cá nhân được lấy từ hồ sơ của bạn.
                                        Nếu cần thay đổi, vui lòng cập nhật trong phần <a
                                            href="{{ route('home.student.index') }}">Hồ sơ cá nhân</a>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PHƯƠNG THỨC THANH TOÁN --}}
                        <div class="checkout-card">
                            <div class="section-header-checkout">
                                <h3 class="section-title"><i class="fas fa-credit-card me-2"></i>Phương thức thanh toán</h3>
                            </div>

                            @if ($errors->has('payment_method'))
                                <div class="alert alert-danger">{{ $errors->first('payment_method') }}</div>
                            @endif

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="transfer" checked>
                                <span class="payment-icon"><i class="fas fa-university"></i></span>
                                <div class="payment-label">
                                    <div>Chuyển khoản ngân hàng</div>
                                    <small class="text-muted fw-normal">Xem thông tin chuyển khoản sau khi xác nhận</small>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cash">
                                <span class="payment-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="payment-label">
                                    <div>Thanh toán tiền mặt</div>
                                    <small class="text-muted fw-normal">Thanh toán trực tiếp tại văn phòng trung tâm</small>
                                </div>
                            </label>

                            <label class="payment-option text-muted" style="opacity: 0.7; cursor: not-allowed;">
                                <input type="radio" name="payment_method" value="vnpay" disabled>
                                <span class="payment-icon"><i class="fas fa-qrcode"></i></span>
                                <div class="payment-label">
                                    <div>VNPAY - QR (Đang bảo trì)</div>
                                    <small class="text-muted fw-normal">Thanh toán qua ứng dụng ngân hàng/ví điện tử</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="checkout-card sticky-top" style="top: 100px; z-index: 10;">
                            <div class="section-header-checkout">
                                <h3 class="section-title">Thông tin lớp học</h3>
                            </div>

                            <h4 class="fw-bold text-dark mb-2">{{ $class->tenLopHoc }}</h4>
                            <p class="text-muted small mb-3">{{ $class->khoaHoc->tenKhoaHoc }}</p>

                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center mb-2 text-muted small">
                                    <i class="far fa-calendar-alt w-25px"></i>
                                    <span>Khai giảng:
                                        {{ \Carbon\Carbon::parse($class->ngayBatDau)->format('d/m/Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fas fa-map-marker-alt w-25px"></i>
                                    <span>{{ $class->coSo->tenCoSo }}</span>
                                </div>
                            </div>

                            <div class="summary-row">
                                <span>Học phí gốc</span>
                                <span>{{ number_format($class->hocPhi->donGia ?? 0, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="summary-row">
                                <span>Giảm giá</span>
                                <span>0đ</span>
                            </div>
                            <div class="summary-row total">
                                <span>Tổng thanh toán</span>
                                <span
                                    class="text-danger">{{ number_format($class->hocPhi->donGia ?? 0, 0, ',', '.') }}đ</span>
                            </div>
                            {{-- errors --}}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-confirm mt-4">
                                XÁC NHẬN & ĐĂNG KÝ <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <div class="text-center mt-3">
                                <small class="text-muted text-center d-block">Bằng việc xác nhận, bạn đồng ý với <a
                                        href="#">quy định đăng ký</a> của trung tâm.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Active class for radio selection
        const options = document.querySelectorAll('.payment-option');
        options.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active from all
                options.forEach(opt => opt.classList.remove('active'));
                // Add active to current
                this.classList.add('active');
                // Select radio
                this.querySelector('input[type="radio"]').checked = true;
            });
            // Init active state
            if (option.querySelector('input[type="radio"]').checked) {
                option.classList.add('active');
            }
        });

        // Form submission handling
        const checkoutForm = document.getElementById('checkoutForm');
        const submitBtn = checkoutForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        checkoutForm.addEventListener('submit', function(e) {
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';

            // Allow form to submit naturally
            // The button will be re-enabled if there's an error (page reload with errors)
        });

        // Re-enable button if there are validation errors (after page reload)
        window.addEventListener('load', function() {
            if (document.querySelector('.alert-danger')) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    </script>
@endsection
