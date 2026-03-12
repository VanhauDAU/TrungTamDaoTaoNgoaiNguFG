@extends('layouts.client')
@section('title', 'Thiết bị đã đăng nhập')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
    <style>
        .device-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .device-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #10454f;
            margin-bottom: 4px;
        }

        .device-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .device-meta-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .device-meta-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 4px;
        }

        .device-meta-value {
            color: #0f172a;
            font-weight: 600;
            word-break: break-word;
        }

        .device-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .device-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .device-badge.current {
            background: #dcfce7;
            color: #166534;
        }

        .device-badge.remembered {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .device-badge.portal {
            background: #fef3c7;
            color: #b45309;
        }

        .device-badge.method {
            background: #f1f5f9;
            color: #334155;
        }
    </style>
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row g-4">
                @include('components.client.account-sidebar')

                <div class="col-lg-9">
                    <div class="account-content">
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Thiết bị đã đăng nhập'],
                        ]" />

                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                            <div>
                                <h2 class="content-title mb-2">
                                    <i class="fas fa-laptop-house me-2"></i> Thiết bị đã đăng nhập
                                </h2>
                                <p class="text-muted mb-0">
                                    Danh sách các phiên đang hoạt động của tài khoản. Bạn có thể thu hồi từng thiết bị
                                    hoặc đăng xuất khỏi tất cả thiết bị.
                                </p>
                            </div>
                            <form action="{{ route('home.student.devices.logout-all') }}" method="POST"
                                onsubmit="return confirm('Đăng xuất khỏi tất cả thiết bị, bao gồm thiết bị hiện tại?')">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-power-off me-2"></i> Đăng xuất khỏi tất cả thiết bị
                                </button>
                            </form>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($devices->isEmpty())
                            <div class="profile-section text-center">
                                <i class="fas fa-laptop-house fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">Chưa có dữ liệu thiết bị hoạt động</h5>
                                <p class="text-muted mb-0">
                                    Hệ thống sẽ tự ghi nhận từ các phiên đăng nhập mới và remembered login tiếp theo.
                                </p>
                            </div>
                        @endif

                        <div class="d-grid gap-3">
                            @foreach ($devices as $device)
                                @php
                                    $loginMethodLabel = match ($device['loginMethod']) {
                                        'google' => 'Google',
                                        'password' => 'Mật khẩu',
                                        default => 'Không xác định',
                                    };

                                    $portalLabel = match ($device['portal']) {
                                        'admin' => 'Cổng nhân sự',
                                        default => 'Cổng học viên',
                                    };
                                @endphp

                                <div class="device-card">
                                    <div class="device-header">
                                        <div>
                                            <div class="device-title">{{ $device['deviceName'] }}</div>
                                            <div class="text-muted small">
                                                {{ $device['platform'] }} · {{ $device['browser'] }}
                                            </div>
                                            <div class="device-badges">
                                                @if ($device['isCurrent'])
                                                    <span class="device-badge current">
                                                        <i class="fas fa-circle"></i> Thiết bị hiện tại
                                                    </span>
                                                @endif
                                                @if ($device['remembered'])
                                                    <span class="device-badge remembered">
                                                        <i class="fas fa-clock-rotate-left"></i> Có ghi nhớ đăng nhập
                                                    </span>
                                                @endif
                                                <span class="device-badge method">
                                                    <i class="fas fa-right-to-bracket"></i> {{ $loginMethodLabel }}
                                                </span>
                                                <span class="device-badge portal">
                                                    <i class="fas fa-door-open"></i> {{ $portalLabel }}
                                                </span>
                                            </div>
                                        </div>
                                        <form action="{{ route('home.student.devices.logout', $device['sessionId']) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ $device['isCurrent'] ? 'Đăng xuất thiết bị hiện tại?' : 'Thu hồi thiết bị này?' }}')">
                                            @csrf
                                            <button type="submit"
                                                class="btn {{ $device['isCurrent'] ? 'btn-outline-danger' : 'btn-outline-secondary' }}">
                                                <i class="fas {{ $device['isCurrent'] ? 'fa-sign-out-alt' : 'fa-ban' }} me-1"></i>
                                                {{ $device['isCurrent'] ? 'Đăng xuất thiết bị này' : 'Thu hồi thiết bị' }}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="device-meta">
                                        <div class="device-meta-item">
                                            <div class="device-meta-label">Địa chỉ IP</div>
                                            <div class="device-meta-value">{{ $device['ipAddress'] ?: 'Không xác định' }}</div>
                                        </div>
                                        <div class="device-meta-item">
                                            <div class="device-meta-label">Đăng nhập lúc</div>
                                            <div class="device-meta-value">
                                                {{ optional($device['loggedInAt'])->format('d/m/Y H:i:s') ?? 'Không xác định' }}
                                            </div>
                                        </div>
                                        <div class="device-meta-item">
                                            <div class="device-meta-label">Hoạt động cuối</div>
                                            <div class="device-meta-value">
                                                {{ optional($device['lastSeenAt'])->format('d/m/Y H:i:s') ?? 'Không xác định' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if (!empty($device['userAgent']))
                                        <div class="mt-3">
                                            <div class="device-meta-label">User-Agent</div>
                                            <div class="small text-muted">{{ $device['userAgent'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
