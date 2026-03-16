@extends('layouts.admin')

@section('title', $coSo->tenCoSo)
@section('page-title', 'Cơ sở Đào tạo')
@section('breadcrumb', 'Cấu hình hệ thống · Cơ sở · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/co-so/show.css') }}">
@endsection

@section('content')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div
            style="background:#dcfce7; color:#16a34a; padding:12px 20px; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div
            style="background:#fee2e2; color:#dc2626; padding:12px 20px; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Header --}}
    <div class="cs-show-header">
        <div>
            <div class="cs-show-title">{{ $coSo->tenCoSo }}</div>
            <span class="cs-show-code">Mã: {{ $coSo->maCoSo }}</span>
        </div>
        <div class="btn-wrap">
            <a href="{{ route('admin.co-so.index') }}" class="btn-act back"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <a href="{{ route('admin.co-so.edit', $coSo->coSoId) }}" class="btn-act edit"><i class="fas fa-pen"></i> Sửa
                CSĐT</a>
            <button class="btn-act add-room" onclick="openRoomModal()"><i class="fas fa-plus"></i> Thêm phòng học</button>
        </div>
    </div>

    <div class="ops-summary-grid" id="opsSummaryGrid"></div>

    <div class="ops-panels-grid">
        <div class="ops-card">
            <div class="ops-card-head">
                <div>
                    <div class="ops-card-title"><i class="fas fa-triangle-exclamation"></i> Cảnh báo vận hành</div>
                    <div class="ops-card-subtitle">Các tín hiệu cần ưu tiên xử lý tại cơ sở</div>
                </div>
            </div>
            <div id="opsAlertsList"></div>
        </div>

        <div class="ops-card">
            <div class="ops-card-head">
                <div>
                    <div class="ops-card-title"><i class="fas fa-wave-square"></i> Nhật ký gần đây</div>
                    <div class="ops-card-subtitle">Tự làm mới mỗi 60 giây</div>
                </div>
                <div class="ops-generated-at" id="opsGeneratedAt">{{ $operationsSnapshot['generatedAt'] ?? '—' }}</div>
            </div>
            <div id="opsAuditList"></div>
        </div>
    </div>

    <div class="cs-details-grid">
        {{-- THÔNG TIN CƠ SỞ --}}
        <div class="info-card">
            <h3><i class="fas fa-circle-info" style="color:#0284c7;"></i> Thông tin chi tiết</h3>

            <div class="info-row">
                <div class="info-label">Trạng thái</div>
                <div class="info-value">
                    @if ($coSo->trangThai == 1)
                        <span class="status-badge active"><i class="fas fa-circle" style="font-size:0.5em;"></i> Hoạt
                            động</span>
                    @else
                        <span class="status-badge inactive"><i class="fas fa-circle" style="font-size:0.5em;"></i> Tạm
                            ngưng</span>
                    @endif
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Địa chỉ</div>
                <div class="info-value">{{ $coSo->diaChi }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Thành phố</div>
                <div class="info-value">{{ $coSo->tinhThanh->tenTinhThanh ?? '—' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Điện thoại</div>
                <div class="info-value">
                    @if ($coSo->soDienThoai)
                        <a href="tel:{{ $coSo->soDienThoai }}">{{ $coSo->soDienThoai }}</a>
                    @else
                        <span style="color:#94a3b8;">—</span>
                    @endif
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">
                    @if ($coSo->email)
                        <a href="mailto:{{ $coSo->email }}">{{ $coSo->email }}</a>
                    @else
                        <span style="color:#94a3b8;">—</span>
                    @endif
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Ngày khai trương</div>
                <div class="info-value">
                    {{ $coSo->ngayKhaiTruong ? $coSo->ngayKhaiTruong->format('d/m/Y') : '—' }}
                </div>
            </div>

            @if ($coSo->banDoGoogle)
                @php
                    $lazyGoogleMap = preg_replace('/<iframe/i', '<iframe loading="lazy"', $coSo->banDoGoogle, 1);
                @endphp
                <div style="margin-top: 20px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                    {!! $lazyGoogleMap !!}
                </div>
            @endif
        </div>

        <div class="branch-main-column">
            {{-- DANH SÁCH PHÒNG HỌC --}}
            <div class="ops-card">
                <div class="ops-card-head">
                    <div>
                        <div class="ops-card-title"><i class="fas fa-calendar-day"></i> Lịch phòng hôm nay</div>
                        <div class="ops-card-subtitle">Chỉ hiển thị các phòng có lịch trong ngày để dễ theo dõi</div>
                    </div>
                </div>
                <div id="opsTimelineGrid"></div>
            </div>

            <div class="ops-card">
                <div class="ops-card-head">
                    <div>
                        <div class="ops-card-title"><i class="fas fa-layer-group"></i> Sơ đồ mặt bằng số</div>
                        <div class="ops-card-subtitle">Hiển thị phòng theo block và tầng, màu trạng thái tự làm mới mỗi 60 giây</div>
                    </div>
                </div>
                <div class="floor-plan-toolbar">
                    <select id="floorPlanBlockFilter" class="form-control floor-plan-filter">
                        <option value="">Tất cả block</option>
                    </select>
                    <select id="floorPlanLevelFilter" class="form-control floor-plan-filter">
                        <option value="">Tất cả tầng</option>
                    </select>
                </div>
                <div id="floorPlanGrid"></div>
            </div>

            <div class="rooms-card">
                <div class="rooms-header">
                    <div class="rooms-title">
                        <i class="fas fa-chalkboard-user" style="color:#8b5cf6;"></i>
                        Danh sách phòng học
                    </div>
                    <div class="rooms-count">{{ $coSo->phong_hocs_count ?? count($coSo->phongHocs) }} phòng</div>
                </div>

                <div class="room-filter-bar">
                    <div class="room-filter-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="roomSearchInput"
                            placeholder="Tìm theo tên phòng, block, tầng hoặc ghi chú thiết bị...">
                    </div>
                    <select id="roomStatusFilter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Sẵn sàng</option>
                        <option value="3">Bảo trì</option>
                        <option value="0">Vô hiệu hóa</option>
                    </select>
                    <input type="number" id="roomCapacityFilter" min="0" placeholder="Sức chứa tối thiểu">
                    <label class="room-filter-check">
                        <input type="checkbox" id="roomOpenTicketFilter">
                        Chỉ hiện phòng có phiếu bảo trì mở
                    </label>
                </div>

                @if (count($coSo->phongHocs) === 0)
                    <div class="empty-rooms">
                        <i class="fas fa-door-open"></i>
                        <p>Cơ sở này chưa có phòng học nào.</p>
                        <button class="btn-act add-room" style="display:inline-flex; width:auto; justify-content:center;"
                            onclick="openRoomModal()"><i class="fas fa-plus"></i> Thêm phòng đầu tiên</button>
                    </div>
                @else
                    <table class="rooms-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Tên phòng</th>
                                <th>Sức chứa</th>
                                <th>Trạng thái</th>
                                <th>Bảo trì</th>
                                <th>TTB / Ghi chú</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coSo->phongHocs as $index => $room)
                                @php
                                    $roomState = $roomStateMap->get($room->phongHocId) ?? null;
                                    $isLive = (bool) data_get($roomState, 'isCurrentlyInUse', false);
                                    $locationLabel = $room->viTriLabel;
                                    $openTicketCount = (int) ($room->maintenance_ticket_open_count ?? 0);
                                    $ticketCount = (int) ($room->maintenance_ticket_count ?? 0);
                                @endphp
                                <tr id="room-row-{{ $room->phongHocId }}"
                                    data-room-id="{{ $room->phongHocId }}"
                                    data-room-name="{{ \Illuminate\Support\Str::lower($room->tenPhong) }}"
                                    data-room-block="{{ \Illuminate\Support\Str::lower($room->khuBlock ?? '') }}"
                                    data-room-floor="{{ $room->tang ?? '' }}"
                                    data-room-status="{{ $room->trangThai }}"
                                    data-room-capacity="{{ (int) ($room->sucChua ?? 0) }}"
                                    data-room-search="{{ \Illuminate\Support\Str::lower(trim($room->tenPhong . ' ' . ($room->khuBlock ?? '') . ' ' . ($room->tang !== null ? 'tang ' . $room->tang : '') . ' ' . ($room->trangThietBi ?? ''))) }}"
                                    data-room-open-tickets="{{ $openTicketCount }}">
                                    <td style="color:#64748b;" class="row-index">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="room-name">
                                            <i class="fas fa-door-closed" style="color:#cbd5e1;"></i>
                                            {{ $room->tenPhong }}
                                        </div>
                                        <div class="room-location-meta">{{ $locationLabel }}</div>
                                    </td>
                                    <td>
                                        <span class="room-cap"><i class="fas fa-users"
                                                style="color:#94a3b8; margin-right:4px;"></i>
                                            {{ $room->sucChua ?? 0 }}</span>
                                    </td>
                                    <td data-room-status-cell="{{ $room->phongHocId }}">
                                        @if ($room->trangThai == 0)
                                            <div
                                                style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:0.85rem; font-weight:600;">
                                                <i class="fas fa-ban"></i> Vô hiệu hóa
                                            </div>
                                        @elseif ($room->trangThai == 3)
                                            <div
                                                style="display:flex; align-items:center; gap:6px; color:#d97706; font-size:0.85rem; font-weight:600;">
                                                <i class="fas fa-tools"></i> Bảo trì
                                            </div>
                                        @elseif ($isLive)
                                            <div
                                                style="display:flex; align-items:center; gap:6px; color:#0ea5e9; font-size:0.85rem; font-weight:600;">
                                                <i class="fas fa-circle" style="font-size:0.5em;"></i> Đang sử dụng
                                            </div>
                                        @else
                                            <div
                                                style="display:flex; align-items:center; gap:6px; color:#16a34a; font-size:0.85rem; font-weight:600;">
                                                <i class="fas fa-circle" style="font-size:0.5em;"></i> Sẵn sàng
                                            </div>
                                        @endif
                                    </td>
                                    <td data-room-ticket-cell="{{ $room->phongHocId }}">
                                        <div class="room-meta-badge {{ $openTicketCount > 0 ? 'warning' : 'neutral' }}">
                                            <i class="fas fa-screwdriver-wrench"></i>
                                            {{ $ticketCount }} phiếu
                                        </div>
                                        <div class="room-meta-sub">{{ $openTicketCount > 0 ? $openTicketCount . ' đang mở' : 'Không tồn đọng' }}</div>
                                    </td>
                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size:0.85rem; color:#64748b;"
                                        title="{{ $room->trangThietBi }}">
                                        {{ $room->trangThietBi ?: '—' }}
                                    </td>
                                    <td class="room-actions-cell">
                                        <div class="room-actions-wrap">
                                            <button type="button" class="btn-room-inline edit" title="Sửa"
                                                onclick="openRoomModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong ?? '') }}', '{{ $room->sucChua ?? '' }}', '{{ addslashes($room->trangThietBi ?? '') }}', '{{ addslashes($room->khuBlock ?? '') }}', '{{ $room->tang ?? '' }}', {{ $room->trangThai }})">
                                                <i class="fas fa-pen"></i>
                                                <span>Sửa</span>
                                            </button>
                                            <div class="room-actions-menu">
                                                <button type="button" class="btn-room-inline more" title="Thao tác khác"
                                                    onclick="toggleRoomActionMenu(this)">
                                                    <i class="fas fa-ellipsis"></i>
                                                    <span>Khác</span>
                                                </button>
                                                <div class="room-actions-dropdown">
                                                    <button type="button" class="room-action-item info"
                                                        onclick="closeRoomActionMenus(); openLichSuModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')">
                                                        <i class="fas fa-history"></i>
                                                        <span>Lịch sử sử dụng</span>
                                                    </button>
                                                    <button type="button" class="room-action-item teal"
                                                        onclick="closeRoomActionMenus(); openQrModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')">
                                                        <i class="fas fa-qrcode"></i>
                                                        <span>QR phòng</span>
                                                    </button>
                                                    <button type="button" class="room-action-item purple"
                                                        onclick="closeRoomActionMenus(); openMaintenanceModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')">
                                                        <i class="fas fa-screwdriver-wrench"></i>
                                                        <span>Workflow bảo trì</span>
                                                    </button>
                                                    <button type="button"
                                                        class="room-action-item {{ $room->trangThai == 1 ? 'warning' : 'success' }}"
                                                        title="{{ $room->trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng' }}"
                                                        onclick="closeRoomActionMenus(); confirmToggleStatus(this, {{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}', {{ $room->trangThai }})">
                                                        <i
                                                            class="fas {{ $room->trangThai == 1 ? 'fa-tools' : 'fa-check-circle' }}"></i>
                                                        <span>{{ $room->trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng' }}</span>
                                                    </button>
                                                    <button type="button" class="room-action-item danger" title="Xóa"
                                                        onclick="closeRoomActionMenus(); confirmDeleteRoom(this, {{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')">
                                                        <i class="fas fa-trash"></i>
                                                        <span>Xóa phòng</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- DANH SÁCH NHÂN SỰ --}}
            <div class="rooms-card">
                <div class="rooms-header">
                    <div class="rooms-title">
                        <i class="fas fa-id-badge" style="color:#0ea5e9;"></i>
                        Danh sách nhân sự
                    </div>
                    <div class="rooms-count">{{ count($coSo->nhanSus) }} nhân sự</div>
                </div>

                @if (count($coSo->nhanSus) === 0)
                    <div class="empty-rooms">
                        <i class="fas fa-users-slash"></i>
                        <p>Cơ sở này chưa có nhân sự nào được phân công.</p>
                    </div>
                @else
                    <table class="rooms-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Nhân sự</th>
                                <th>Mã NV</th>
                                <th>Chức vụ</th>
                                <th>Liên hệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coSo->nhanSus as $index => $nhanSu)
                                <tr>
                                    <td style="color:#64748b;" class="row-index">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="room-name">
                                            <i class="fas fa-user-circle" style="color:#cbd5e1; font-size: 1.2rem;"></i>
                                            {{ optional(optional($nhanSu->taiKhoan)->hoSoNguoiDung)->hoTen ?? (optional($nhanSu->taiKhoan)->name ?? 'Chưa cập nhật') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="room-cap">{{ $nhanSu->maNhanVien }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background:#e0f2fe; color:#0369a1;"><i
                                                class="fas fa-briefcase"></i> {{ $nhanSu->chucVu }}</span>
                                    </td>
                                    <td style="font-size:0.85rem; color:#64748b; line-height:1.4;">
                                        @if (optional($nhanSu->taiKhoan)->email)
                                            <div><i class="fas fa-envelope" style="width:16px; text-align:center;"></i>
                                                {{ $nhanSu->taiKhoan->email }}</div>
                                        @endif
                                        @if (optional(optional($nhanSu->taiKhoan)->hoSoNguoiDung)->soDienThoai)
                                            <div style="margin-top:2px;"><i class="fas fa-phone"
                                                    style="width:16px; text-align:center;"></i>
                                                {{ $nhanSu->taiKhoan->hoSoNguoiDung->soDienThoai }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL PHÒNG HỌC --}}
    <div class="custom-modal" id="roomModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="roomModalTitle">Thêm phòng học mới</div>
                <button type="button" class="btn-close" onclick="closeRoomModal()"><i
                        class="fas fa-times"></i></button>
            </div>

            <form id="roomForm" method="POST" action="{{ route('admin.phong-hoc.store') }}">
                @csrf
                <input type="hidden" name="_method" id="roomMethod" value="POST">
                <input type="hidden" name="coSoId" value="{{ $coSo->coSoId }}">

                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="tenPhong">Tên/Mã phòng học <span
                                style="color:#ef4444">*</span></label>
                        <input type="text" id="tenPhong" name="tenPhong" class="form-control" required
                            placeholder="VD: P.101, Lab 1...">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="khuBlock">Block / Khu</label>
                            <input type="text" id="khuBlock" name="khuBlock" class="form-control"
                                placeholder="VD: A, B, Khu Lab">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tang">Tầng</label>
                            <input type="number" id="tang" name="tang" class="form-control" min="0"
                                max="50" placeholder="VD: 1, 2, 3">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sucChua">Sức chứa (số chỗ ngồi)</label>
                        <input type="number" id="sucChua" name="sucChua" class="form-control" min="1"
                            max="999" placeholder="VD: 30">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="roomTrangThai">Trạng thái <span
                                style="color:#ef4444">*</span></label>
                        <select id="roomTrangThai" name="trangThai" class="form-control" required>
                            <option value="1">Sẵn sàng sử dụng</option>
                            <option value="3">Bảo trì / Sửa chữa</option>
                            <option value="0">Vô hiệu hóa / Đóng cửa</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="trangThietBi">Trang thiết bị / Ghi chú</label>
                        <textarea id="trangThietBi" name="trangThietBi" class="form-control" rows="3"
                            placeholder="Máy chiếu, bảng điện tử, máy lạnh..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeRoomModal()">Hủy</button>
                    <button type="submit" class="btn-submit" id="roomSubmitBtn">Lưu phòng học</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Form Xóa Phòng Học --}}
    <form id="delete-room-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Modal Lịch Sử Sử Dụng Phòng --}}
    <div class="custom-modal" id="lichSuModal">
        <div class="modal-content" style="max-width:780px;">
            <div class="modal-header">
                <div class="modal-title" id="lichSuModalTitle"><i class="fas fa-history"
                        style="color:#0369a1; margin-right:8px;"></i> Lịch sử sử dụng phòng</div>
                <button type="button" class="btn-close" onclick="closeLichSuModal()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="lichSuBody">
                <div style="text-align:center; padding:2rem; color:#94a3b8;"><i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ghi Chú Bảo Trì --}}
    <div class="custom-modal" id="baoTriModal">
        <div class="modal-content" style="max-width:480px;">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-tools" style="color:#d97706; margin-right:8px;"></i> Chuyển
                    sang bảo trì</div>
                <button type="button" class="btn-close" onclick="closeBaoTriModal()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:1rem; color:#6b7280; font-size:.9rem;">Phòng: <strong
                        id="baoTriRoomName"></strong></p>
                <label class="form-label" for="baoTriGhiChu">Lý do / Ghi chú bảo trì <span
                        style="color:#9ca3af; font-size:.82rem;">(tuỳ chọn)</span></label>
                <textarea id="baoTriGhiChu" class="form-control" rows="3" placeholder="VD: Sửa máy lạnh, thay bảng, v.v..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeBaoTriModal()">Hủy</button>
                <button type="button" class="btn-submit" id="baoTriConfirmBtn" style="background:#d97706;">Xác nhận bảo
                    trì</button>
            </div>
        </div>
    </div>

    <div class="custom-modal" id="qrRoomModal">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <div class="modal-title" id="qrRoomModalTitle"><i class="fas fa-qrcode" style="color:#0f766e; margin-right:8px;"></i> QR phòng học</div>
                <button type="button" class="btn-close" onclick="closeQrModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="qrRoomBody">
                <div style="text-align:center; padding:2rem; color:#94a3b8;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
        </div>
    </div>

    <div class="custom-modal" id="maintenanceModal">
        <div class="modal-content" style="max-width:980px;">
            <div class="modal-header">
                <div class="modal-title" id="maintenanceModalTitle"><i class="fas fa-screwdriver-wrench" style="color:#c2410c; margin-right:8px;"></i> Workflow bảo trì</div>
                <button type="button" class="btn-close" onclick="closeMaintenanceModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div id="maintenanceSummary" class="modal-section-note"></div>
                <div class="dual-pane-modal">
                    <div>
                        <div class="inline-section-title">Phiếu bảo trì</div>
                        <div id="maintenanceList"></div>
                    </div>
                    <div>
                        <div class="inline-section-title" id="maintenanceFormTitle">Tạo phiếu bảo trì</div>
                        <form id="maintenanceForm">
                            @csrf
                            <input type="hidden" id="maintenanceTicketId">
                            <div class="form-group">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" id="maintenanceTitleInput" class="form-control" required>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">Mức độ ưu tiên</label>
                                    <select id="maintenancePriorityInput" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Trạng thái</label>
                                    <select id="maintenanceStatusInput" class="form-control"></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Người phụ trách</label>
                                <select id="maintenanceAssigneeInput" class="form-control"></select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mô tả</label>
                                <textarea id="maintenanceDescriptionInput" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kết quả xử lý</label>
                                <textarea id="maintenanceResolutionInput" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="modal-footer" style="padding:0; margin-top:16px;">
                                <button type="button" class="btn-cancel" onclick="resetMaintenanceForm()">Làm mới</button>
                                <button type="submit" class="btn-submit">Lưu phiếu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        const operationsSnapshotUrl = "{{ route('admin.co-so.operational-snapshot', $coSo->coSoId) }}";
        const initialOperationsSnapshot = @json($operationsSnapshot);
        let latestOperationsSnapshot = initialOperationsSnapshot;
        const branchStaffOptions = @json(
            $coSo->nhanSus->map(function ($nhanSu) {
                return [
                    'taiKhoanId' => $nhanSu->taiKhoanId,
                    'hoTen' => optional(optional($nhanSu->taiKhoan)->hoSoNguoiDung)->hoTen ?? optional($nhanSu->taiKhoan)->taiKhoan ?? 'Chưa cập nhật',
                ];
            })->values(),
        );
        let currentMaintenanceRoomId = null;
        let currentMaintenancePayload = null;

        // Config Toast
        const RoomToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function escapeJsSingleQuoted(value) {
            return String(value ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/\r/g, '\\r')
                .replace(/\n/g, '\\n');
        }

        function getRoomLocationLabel(block, floor) {
            const parts = [];

            if (String(block || '').trim()) {
                parts.push(`Block ${escapeHtml(String(block).trim())}`);
            }

            if (floor !== null && floor !== undefined && String(floor).trim() !== '') {
                parts.push(`Tầng ${escapeHtml(String(floor).trim())}`);
            }

            return parts.length ? parts.join(' · ') : 'Chưa phân khu';
        }

        function createRoomStatusHTML(room) {
            if (room.trangThai == 0) {
                return `<div style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:0.85rem; font-weight:600;"><i class="fas fa-ban"></i> Vô hiệu hóa</div>`;
            }
            if (room.trangThai == 3) {
                return `<div style="display:flex; align-items:center; gap:6px; color:#d97706; font-size:0.85rem; font-weight:600;"><i class="fas fa-tools"></i> Bảo trì</div>`;
            }
            if (room.isCurrentlyInUse) {
                return `<div style="display:flex; align-items:center; gap:6px; color:#0ea5e9; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Đang sử dụng</div>`;
            }

            return `<div style="display:flex; align-items:center; gap:6px; color:#16a34a; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Sẵn sàng</div>`;
        }

        function renderOperationsSummary(summary = {}) {
            const cards = [{
                    label: 'Tổng phòng',
                    value: summary.totalRooms || 0,
                    tone: 'neutral',
                    icon: 'fa-building'
                },
                {
                    label: 'Phòng sẵn sàng',
                    value: summary.readyRooms || 0,
                    tone: 'success',
                    icon: 'fa-check-circle'
                },
                {
                    label: 'Đang sử dụng',
                    value: summary.liveRooms || 0,
                    tone: 'info',
                    icon: 'fa-person-chalkboard'
                },
                {
                    label: 'Bảo trì',
                    value: summary.maintenanceRooms || 0,
                    tone: 'warning',
                    icon: 'fa-tools'
                },
                {
                    label: 'Buổi học hôm nay',
                    value: summary.sessionsToday || 0,
                    tone: 'neutral',
                    icon: 'fa-calendar-day'
                },
                {
                    label: 'Sắp diễn ra',
                    value: summary.upcomingSessions || 0,
                    tone: 'accent',
                    icon: 'fa-hourglass-half',
                    meta: `Lấp đầy realtime ${summary.utilizationRate || 0}%`
                },
                {
                    label: 'Phiếu bảo trì mở',
                    value: summary.openMaintenanceTickets || 0,
                    tone: 'warning',
                    icon: 'fa-screwdriver-wrench'
                }
            ];

            document.getElementById('opsSummaryGrid').innerHTML = cards.map(card => `
                <div class="ops-stat-card ops-stat-card--${card.tone}">
                    <div class="ops-stat-top">
                        <span class="ops-stat-label">${card.label}</span>
                        <i class="fas ${card.icon}"></i>
                    </div>
                    <div class="ops-stat-value">${card.value}</div>
                    <div class="ops-stat-meta">${card.meta || '&nbsp;'}</div>
                </div>
            `).join('');
        }

        function renderOperationsAlerts(alerts = []) {
            const container = document.getElementById('opsAlertsList');

            if (!alerts.length) {
                container.innerHTML = `
                    <div class="ops-empty-state">
                        <i class="fas fa-shield-heart"></i>
                        <div>
                            <strong>Không có cảnh báo nghiêm trọng</strong>
                            <p>Cơ sở đang ở trạng thái vận hành ổn định.</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = alerts.map(alert => `
                <div class="ops-alert ops-alert--${alert.level || 'info'}">
                    <div class="ops-alert-title">${alert.title || 'Thông báo'}</div>
                    <div class="ops-alert-message">${alert.message || ''}</div>
                </div>
            `).join('');
        }

        function renderOperationsAudit(auditLogs = []) {
            const container = document.getElementById('opsAuditList');

            if (!auditLogs.length) {
                container.innerHTML = `
                    <div class="ops-empty-state">
                        <i class="fas fa-clock-rotate-left"></i>
                        <div>
                            <strong>Chưa có nhật ký</strong>
                            <p>Những thao tác trên cơ sở và phòng học sẽ xuất hiện tại đây.</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = auditLogs.map(log => `
                <div class="ops-log-item">
                    <div class="ops-log-main">${log.message}</div>
                    <div class="ops-log-meta">${log.actorName || 'Hệ thống'} · ${log.createdAt || '—'}</div>
                </div>
            `).join('');
        }

        function renderOperationsTimeline(schedule = []) {
            const container = document.getElementById('opsTimelineGrid');

            if (!schedule.length) {
                container.innerHTML = `
                    <div class="ops-empty-state">
                        <i class="fas fa-calendar-xmark"></i>
                        <div>
                            <strong>Hôm nay chưa có lịch phòng</strong>
                            <p>Những phòng không có buổi học sẽ được ẩn khỏi khu vực này.</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = schedule.map(room => `
                <div class="ops-room-block">
                    <div class="ops-room-head">
                        <div class="ops-room-name">${room.tenPhong}</div>
                        <div class="ops-room-status ops-room-status--${room.trangThai == 3 ? 'warning' : (room.trangThai == 0 ? 'muted' : (room.isCurrentlyInUse ? 'info' : 'success'))}">
                            ${room.trangThai == 3 ? 'Bảo trì' : (room.trangThai == 0 ? 'Vô hiệu hóa' : (room.isCurrentlyInUse ? 'Đang sử dụng' : 'Sẵn sàng'))}
                        </div>
                    </div>
                    <div class="ops-room-body">
                        ${(room.sessions || []).length ? room.sessions.map(session => `
                            <div class="ops-session-item">
                                <div class="ops-session-time">${session.timeRange}</div>
                                <div class="ops-session-main">
                                    <strong>${session.classCode}</strong> · ${session.className}
                                </div>
                                <div class="ops-session-meta">${session.teacherName} · ${session.statusLabel}</div>
                            </div>
                        `).join('') : `
                            <div class="ops-session-empty">Không có buổi học nào trong hôm nay.</div>
                        `}
                    </div>
                </div>
            `).join('');
        }

        function normalizeFloorPlanBlock(block) {
            return String(block || '').trim() || 'Chưa phân khu';
        }

        function normalizeFloorPlanLevel(level) {
            return level === null || level === undefined || String(level).trim() === '' ? 'Chưa gán tầng' : `Tầng ${level}`;
        }

        function getFloorPlanRoomTone(room) {
            if (room.trangThai == 3) {
                return {
                    tone: 'warning',
                    label: 'Bảo trì'
                };
            }
            if (room.trangThai == 0) {
                return {
                    tone: 'muted',
                    label: 'Vô hiệu hóa'
                };
            }
            if (room.isCurrentlyInUse) {
                return {
                    tone: 'info',
                    label: 'Đang sử dụng'
                };
            }

            return {
                tone: 'success',
                label: 'Sẵn sàng'
            };
        }

        function renderFloorPlan(roomStates = []) {
            const container = document.getElementById('floorPlanGrid');
            const blockFilter = document.getElementById('floorPlanBlockFilter')?.value || '';
            const levelFilter = document.getElementById('floorPlanLevelFilter')?.value || '';

            if (!roomStates.length) {
                container.innerHTML = `
                    <div class="ops-empty-state">
                        <i class="fas fa-building-circle-xmark"></i>
                        <div>
                            <strong>Chưa có dữ liệu phòng học</strong>
                            <p>Thêm phòng và gán block/tầng để bắt đầu theo dõi sơ đồ số.</p>
                        </div>
                    </div>
                `;
                return;
            }

            const groups = roomStates.reduce((carry, room) => {
                const block = normalizeFloorPlanBlock(room.khuBlock);
                const level = normalizeFloorPlanLevel(room.tang);
                const matchesBlock = !blockFilter || block === blockFilter;
                const matchesLevel = !levelFilter || level === levelFilter;

                if (!matchesBlock || !matchesLevel) {
                    return carry;
                }

                if (!carry[block]) {
                    carry[block] = {};
                }

                if (!carry[block][level]) {
                    carry[block][level] = [];
                }

                carry[block][level].push(room);
                return carry;
            }, {});

            const blockNames = Object.keys(groups).sort((a, b) => a.localeCompare(b, 'vi'));
            if (!blockNames.length) {
                container.innerHTML = `
                    <div class="ops-empty-state">
                        <i class="fas fa-filter-circle-xmark"></i>
                        <div>
                            <strong>Không có phòng phù hợp bộ lọc</strong>
                            <p>Thử đổi block hoặc tầng để xem thêm khu vực khác.</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = blockNames.map(block => {
                const levels = Object.keys(groups[block]).sort((a, b) => {
                    const aNum = parseInt(String(a).replace(/\D/g, ''), 10);
                    const bNum = parseInt(String(b).replace(/\D/g, ''), 10);

                    if (Number.isNaN(aNum) || Number.isNaN(bNum)) {
                        return a.localeCompare(b, 'vi');
                    }

                    return aNum - bNum;
                });

                return `
                    <div class="floor-plan-block">
                        <div class="floor-plan-block-head">
                            <div class="floor-plan-block-title">${escapeHtml(block)}</div>
                            <div class="floor-plan-block-meta">${levels.length} tầng hiển thị</div>
                        </div>
                        ${levels.map(level => `
                            <div class="floor-plan-level">
                                <div class="floor-plan-level-title">${escapeHtml(level)}</div>
                                <div class="floor-plan-room-grid">
                                    ${groups[block][level].map(room => {
                                        const status = getFloorPlanRoomTone(room);
                                        return `
                                            <button type="button" class="floor-plan-room floor-plan-room--${status.tone}" onclick="openMaintenanceModal(${room.phongHocId}, '${escapeJsSingleQuoted(room.tenPhong || '')}')">
                                                <div class="floor-plan-room-name">${escapeHtml(room.tenPhong || 'Phòng chưa đặt tên')}</div>
                                                <div class="floor-plan-room-status">${status.label}</div>
                                                <div class="floor-plan-room-meta">Sức chứa ${escapeHtml(room.sucChua || 0)}</div>
                                                <div class="floor-plan-room-meta">${room.openMaintenanceTickets ? `${escapeHtml(room.openMaintenanceTickets)} phiếu mở` : 'Không tồn đọng bảo trì'}</div>
                                            </button>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }).join('');
        }

        function syncFloorPlanFilters(roomStates = []) {
            const blockSelect = document.getElementById('floorPlanBlockFilter');
            const levelSelect = document.getElementById('floorPlanLevelFilter');
            if (!blockSelect || !levelSelect) return;

            const currentBlock = blockSelect.value;
            const currentLevel = levelSelect.value;
            const blockOptions = [...new Set(roomStates.map(room => normalizeFloorPlanBlock(room.khuBlock)))].sort((a, b) => a.localeCompare(b, 'vi'));
            const levelOptions = [...new Set(roomStates.map(room => normalizeFloorPlanLevel(room.tang)))].sort((a, b) => {
                const aNum = parseInt(String(a).replace(/\D/g, ''), 10);
                const bNum = parseInt(String(b).replace(/\D/g, ''), 10);

                if (Number.isNaN(aNum) || Number.isNaN(bNum)) {
                    return a.localeCompare(b, 'vi');
                }

                return aNum - bNum;
            });

            blockSelect.innerHTML = '<option value="">Tất cả block</option>' + blockOptions.map(block =>
                `<option value="${escapeHtml(block)}" ${block === currentBlock ? 'selected' : ''}>${escapeHtml(block)}</option>`
            ).join('');
            levelSelect.innerHTML = '<option value="">Tất cả tầng</option>' + levelOptions.map(level =>
                `<option value="${escapeHtml(level)}" ${level === currentLevel ? 'selected' : ''}>${escapeHtml(level)}</option>`
            ).join('');
        }

        function updateRoomStatusCells(roomStates = []) {
            roomStates.forEach(room => {
                const cell = document.querySelector(`[data-room-status-cell="${room.phongHocId}"]`);
                if (cell) {
                    cell.innerHTML = createRoomStatusHTML(room);
                }
            });
        }

        function renderOperationsSnapshot(snapshot = {}) {
            latestOperationsSnapshot = snapshot;
            renderOperationsSummary(snapshot.summary || {});
            renderOperationsAlerts(snapshot.alerts || []);
            renderOperationsAudit(snapshot.auditLogs || []);
            renderOperationsTimeline(snapshot.schedule || []);
            updateRoomStatusCells(snapshot.roomStates || []);
            syncFloorPlanFilters(snapshot.roomStates || []);
            renderFloorPlan(snapshot.roomStates || []);
            document.getElementById('opsGeneratedAt').textContent = snapshot.generatedAt || '—';
        }

        async function refreshOperationsSnapshot() {
            try {
                const response = await fetch(operationsSnapshotUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success && data.snapshot) {
                    renderOperationsSnapshot(data.snapshot);
                }
            } catch (error) {
                console.error('Khong the tai snapshot van hanh', error);
            }
        }

        function updateRoomCount(change) {
            const roomsCard = document.querySelector('.rooms-card');
            const countEl = roomsCard?.querySelector('.rooms-count');
            if (countEl) {
                let current = parseInt(countEl.textContent) || 0;
                current += change;
                countEl.textContent = `${current} phòng`;

                // Xử lý Empty State
                const table = roomsCard?.querySelector('.rooms-table');
                const emptyState = roomsCard?.querySelector('.empty-rooms');

                if (current === 0) {
                    if (table) table.style.display = 'none';
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    } else {
                        // Tạo empty state nếu chưa có
                        const emptyHtml = `
                            <div class="empty-rooms">
                                <i class="fas fa-door-open"></i>
                                <p>Cơ sở này chưa có phòng học nào.</p>
                                <button class="btn-act add-room" style="display:inline-flex; width:auto; justify-content:center;"
                                    onclick="openRoomModal()"><i class="fas fa-plus"></i> Thêm phòng đầu tiên</button>
                            </div>
                        `;
                        roomsCard.insertAdjacentHTML('beforeend', emptyHtml);
                    }
                } else {
                    if (emptyState) emptyState.style.display = 'none';
                    if (table) table.style.display = 'table';
                }
            }
        }

        function renderRoomTicketCell(total, open) {
            return `
                <div class="room-meta-badge ${open > 0 ? 'warning' : 'neutral'}">
                    <i class="fas fa-screwdriver-wrench"></i>
                    ${total} phiếu
                </div>
                <div class="room-meta-sub">${open > 0 ? `${open} đang mở` : 'Không tồn đọng'}</div>
            `;
        }

        function syncRoomSupplementaryMeta(roomId, meta = {}) {
            const row = document.getElementById(`room-row-${roomId}`);
            if (!row) return;

            if (meta.openTickets !== undefined) {
                row.dataset.roomOpenTickets = meta.openTickets;
            }
            if (meta.status !== undefined) {
                row.dataset.roomStatus = meta.status;
            }

            if (meta.ticketTotal !== undefined) {
                const cell = row.querySelector(`[data-room-ticket-cell="${roomId}"]`);
                if (cell) {
                    cell.innerHTML = renderRoomTicketCell(meta.ticketTotal, meta.openTickets || 0);
                }
            }
        }

        function applyRoomRowDatasets(row, room, meta = {}) {
            row.dataset.roomId = room.phongHocId;
            row.dataset.roomName = (room.tenPhong || '').toLowerCase();
            row.dataset.roomBlock = (room.khuBlock || '').toLowerCase();
            row.dataset.roomFloor = room.tang ?? '';
            row.dataset.roomStatus = meta.status ?? room.trangThai ?? 1;
            row.dataset.roomCapacity = parseInt(room.sucChua || 0, 10);
            row.dataset.roomOpenTickets = meta.openTickets ?? 0;
            row.dataset.roomSearch = `${row.dataset.roomName} ${row.dataset.roomBlock} ${room.tang ?? ''} ${(room.trangThietBi || '')}`.toLowerCase();
        }

        function filterRooms() {
            const q = (document.getElementById('roomSearchInput')?.value || '').trim().toLowerCase();
            const status = document.getElementById('roomStatusFilter')?.value || '';
            const minCapacity = parseInt(document.getElementById('roomCapacityFilter')?.value || '0', 10);
            const openTicketOnly = document.getElementById('roomOpenTicketFilter')?.checked;

            document.querySelectorAll('.rooms-table tbody tr[id^="room-row-"]').forEach(row => {
                const searchBlob = row.dataset.roomSearch || '';
                const roomStatus = row.dataset.roomStatus || '';
                const roomCapacity = parseInt(row.dataset.roomCapacity || '0', 10);
                const openTickets = parseInt(row.dataset.roomOpenTickets || '0', 10);

                const matches = (!q || searchBlob.includes(q)) &&
                    (!status || roomStatus === status) &&
                    (roomCapacity >= minCapacity) &&
                    (!openTicketOnly || openTickets > 0);

                row.style.display = matches ? '' : 'none';
            });
        }

        function closeRoomActionMenus() {
            document.querySelectorAll('.room-actions-menu.open').forEach(menu => menu.classList.remove('open'));
            document.querySelectorAll('.room-actions-menu.drop-up').forEach(menu => menu.classList.remove('drop-up'));
            document.querySelectorAll('.rooms-card.room-card-menu-active').forEach(card => card.classList.remove('room-card-menu-active'));
        }

        function toggleRoomActionMenu(button) {
            const menu = button.closest('.room-actions-menu');
            if (!menu) return;

            const willOpen = !menu.classList.contains('open');
            closeRoomActionMenus();
            if (willOpen) {
                const card = menu.closest('.rooms-card');
                if (card) {
                    card.classList.add('room-card-menu-active');
                }

                menu.classList.add('open');

                const dropdown = menu.querySelector('.room-actions-dropdown');
                if (!dropdown) return;

                const rect = dropdown.getBoundingClientRect();
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
                const needsDropUp = rect.bottom > (viewportHeight - 12) && rect.top > rect.height;
                menu.classList.toggle('drop-up', needsDropUp);
            }
        }

        function createRowHTML(room, index = '-') {
            const safeNameHtml = escapeHtml(room.tenPhong || '');
            const safeNameJs = escapeJsSingleQuoted(room.tenPhong || '');
            const safeEquipHtml = escapeHtml(room.trangThietBi || '');
            const safeEquipJs = escapeJsSingleQuoted(room.trangThietBi || '');
            const safeBlockJs = escapeJsSingleQuoted(room.khuBlock || '');
            const locationLabel = getRoomLocationLabel(room.khuBlock, room.tang);

            return `
                <td style="color:#64748b;" class="row-index">${index}</td>
                <td>
                    <div class="room-name">
                        <i class="fas fa-door-closed" style="color:#cbd5e1;"></i>
                        ${safeNameHtml}
                    </div>
                    <div class="room-location-meta">${locationLabel}</div>
                </td>
                <td>
                    <span class="room-cap"><i class="fas fa-users" style="color:#94a3b8; margin-right:4px;"></i> ${escapeHtml(room.sucChua || 0)}</span>
                </td>
                <td data-room-status-cell="${room.phongHocId}">${createRoomStatusHTML({
                    trangThai: room.trangThai,
                    isCurrentlyInUse: room.is_currently_in_use
                })}</td>
                <td data-room-ticket-cell="${room.phongHocId}">${renderRoomTicketCell(0, 0)}</td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size:0.85rem; color:#64748b;" title="${safeEquipHtml}">
                    ${safeEquipHtml || '—'}
                </td>
                <td class="room-actions-cell">
                    <div class="room-actions-wrap">
                        <button type="button" class="btn-room-inline edit" title="Sửa"
                            onclick="openRoomModal(${room.phongHocId}, '${safeNameJs}', '${escapeJsSingleQuoted(room.sucChua || '')}', '${safeEquipJs}', '${safeBlockJs}', '${escapeJsSingleQuoted(room.tang ?? '')}', ${room.trangThai})">
                            <i class="fas fa-pen"></i>
                            <span>Sửa</span>
                        </button>
                        <div class="room-actions-menu">
                            <button type="button" class="btn-room-inline more" title="Thao tác khác"
                                onclick="toggleRoomActionMenu(this)">
                                <i class="fas fa-ellipsis"></i>
                                <span>Khác</span>
                            </button>
                            <div class="room-actions-dropdown">
                                <button type="button" class="room-action-item info"
                                    onclick="closeRoomActionMenus(); openLichSuModal(${room.phongHocId}, '${safeNameJs}')">
                                    <i class="fas fa-history"></i>
                                    <span>Lịch sử sử dụng</span>
                                </button>
                                <button type="button" class="room-action-item teal"
                                    onclick="closeRoomActionMenus(); openQrModal(${room.phongHocId}, '${safeNameJs}')">
                                    <i class="fas fa-qrcode"></i>
                                    <span>QR phòng</span>
                                </button>
                                <button type="button" class="room-action-item purple"
                                    onclick="closeRoomActionMenus(); openMaintenanceModal(${room.phongHocId}, '${safeNameJs}')">
                                    <i class="fas fa-screwdriver-wrench"></i>
                                    <span>Workflow bảo trì</span>
                                </button>
                                <button type="button" class="room-action-item ${room.trangThai == 1 ? 'warning' : 'success'}"
                                    title="${room.trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng'}"
                                    onclick="closeRoomActionMenus(); confirmToggleStatus(this, ${room.phongHocId}, '${safeNameJs}', ${room.trangThai})">
                                    <i class="fas ${room.trangThai == 1 ? 'fa-tools' : 'fa-check-circle'}"></i>
                                    <span>${room.trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng'}</span>
                                </button>
                                <button type="button" class="room-action-item danger"
                                    onclick="closeRoomActionMenus(); confirmDeleteRoom(this, ${room.phongHocId}, '${safeNameJs}')">
                                    <i class="fas fa-trash"></i>
                                    <span>Xóa phòng</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
            `;
        }

        // Xóa Phòng học qua AJAX
        function confirmDeleteRoom(btn, id, name) {
            Swal.fire({
                title: 'Xóa phòng học?',
                html: `Bạn có chắc chắn muốn xóa phòng <strong>${escapeHtml(name)}</strong>? Quá trình này không thể hoàn tác.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Có, Xóa!',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/phong-hoc/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (res.status === 422 || !data.success) {
                                // Không cho xóa vì còn lớp đang hoạt động
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Không thể xóa',
                                    text: data.message || 'Phòng đang được sử dụng.',
                                    confirmButtonColor: '#0284c7',
                                });
                            } else if (data.success) {
                                RoomToast.fire({
                                    icon: 'success',
                                    title: data.message
                                });
                                btn.closest('tr').remove();
                                updateRoomCount(-1);
                                refreshOperationsSnapshot();
                            } else {
                                RoomToast.fire({
                                    icon: 'error',
                                    title: data.message || 'Xóa thất bại.'
                                });
                            }
                        }).catch(err => {
                            RoomToast.fire({
                                icon: 'error',
                                title: 'Máy chủ phản hồi lỗi.'
                            });
                        });
                }
            });
        }

        // ─── Toggle trạng thái bảo trì ─────────────────────────────────────
        let _toggleId = null,
            _toggleBtn = null;

        function closeBaoTriModal() {
            document.getElementById('baoTriModal').classList.remove('show');
        }

        function closeQrModal() {
            document.getElementById('qrRoomModal').classList.remove('show');
        }

        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.remove('show');
        }

        function confirmToggleStatus(btn, id, name, currentStatus) {
            if (currentStatus == 1) {
                // Đang sẵn sàng → muốn chuyển bảo trì → hiện modal ghi chú
                _toggleId = id;
                _toggleBtn = btn;
                document.getElementById('baoTriRoomName').textContent = name;
                document.getElementById('baoTriGhiChu').value = '';
                document.getElementById('baoTriModal').classList.add('show');
            } else {
                // Đang bảo trì → muốn về sẵn sàng
                Swal.fire({
                    title: 'Đánh dấu sẵn sàng?',
                    html: `Đánh dấu phòng <strong>${escapeHtml(name)}</strong> là Sẵn sàng sử dụng?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: 'Sẵn sàng!',
                    cancelButtonText: 'Hủy'
                }).then(r => {
                    if (r.isConfirmed) doToggle(btn, id, null);
                });
            }
        }

        document.getElementById('baoTriConfirmBtn').addEventListener('click', function() {
            const ghiChu = document.getElementById('baoTriGhiChu').value;
            closeBaoTriModal();
            doToggle(_toggleBtn, _toggleId, ghiChu);
        });

        function buildMaintenanceImpactHtml(impact = {}) {
            const rows = (impact.sessions || []).map(session => `
                <li style="margin-bottom:8px;">
                    <strong>${session.ngayHoc}</strong> · ${session.timeRange}<br>
                    ${session.maLopHoc} - ${session.tenLopHoc}<br>
                    <span style="color:#64748b;">${session.tenGiaoVien}</span>
                </li>
            `).join('');

            return `
                <div style="text-align:left; font-size:.9rem;">
                    <p style="margin-bottom:12px;">Còn <strong>${impact.count || 0}</strong> buổi học sắp tới đang dùng phòng này.</p>
                    ${rows ? `<ul style="padding-left:18px; margin:0;">${rows}</ul>` : ''}
                </div>
            `;
        }

        function doToggle(btn, id, ghiChu, forceMaintenance = false) {
            const fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', '{{ csrf_token() }}');
            if (ghiChu) fd.append('ghiChuBaoTri', ghiChu);
            if (forceMaintenance) fd.append('forceMaintenance', '1');

            fetch(`/admin/phong-hoc/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: fd
                })
                .then(async res => ({
                    status: res.status,
                    body: await res.json()
                }))
                .then(data => {
                    if (data.status === 409 && data.body.requiresConfirmation) {
                        Swal.fire({
                            title: 'Xung đột lịch bảo trì',
                            html: buildMaintenanceImpactHtml(data.body.impact || {}),
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Vẫn chuyển bảo trì',
                            cancelButtonText: 'Hủy',
                            confirmButtonColor: '#d97706'
                        }).then(result => {
                            if (result.isConfirmed) {
                                doToggle(btn, id, ghiChu, true);
                            }
                        });
                        return;
                    }

                    if (data.success || data.body.success) {
                        const payload = data.success ? data : data.body;
                        RoomToast.fire({
                            icon: 'success',
                            title: payload.message
                        });
                        closeRoomActionMenus();
                        // Cập nhật DOM
                        const newStatus = payload.trangThai;
                        const tr = btn.closest('tr');
                        const tdStatus = tr?.querySelector(`[data-room-status-cell="${id}"]`);
                        if (tdStatus) {
                            tdStatus.innerHTML = createRoomStatusHTML({
                                trangThai: newStatus,
                                isCurrentlyInUse: false
                            });
                        }

                        if (btn.classList.contains('room-action-item')) {
                            btn.className = `room-action-item ${newStatus == 1 ? 'warning' : 'success'}`;
                            btn.title = newStatus == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng';
                            btn.innerHTML = `
                                <i class="fas ${newStatus == 1 ? 'fa-tools' : 'fa-check-circle'}"></i>
                                <span>${newStatus == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng'}</span>
                            `;
                            btn.setAttribute('onclick',
                                `closeRoomActionMenus(); confirmToggleStatus(this, ${id}, '${escapeJsSingleQuoted(payload.room.tenPhong || '')}', ${newStatus})`
                            );
                        }
                        refreshOperationsSnapshot();
                        syncRoomSupplementaryMeta(id, {
                            status: newStatus
                        });
                        filterRooms();
                    } else {
                        RoomToast.fire({
                            icon: 'error',
                            title: data.body?.message || data.message || 'Thao tác thất bại.'
                        });
                    }
                }).catch(() => {
                    RoomToast.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối.'
                    });
                });
        }

        async function openQrModal(id, name) {
            document.getElementById('qrRoomModalTitle').innerHTML =
                `<i class="fas fa-qrcode" style="color:#0f766e; margin-right:8px;"></i> QR phòng: ${escapeHtml(name)}`;
            document.getElementById('qrRoomBody').innerHTML =
                '<div style="text-align:center; padding:2rem; color:#94a3b8;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            document.getElementById('qrRoomModal').classList.add('show');

            try {
                const response = await fetch(`/admin/phong-hoc/${id}/qr`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                const data = payload.data;

                document.getElementById('qrRoomBody').innerHTML = `
                    <div class="qr-room-wrap">
                        <img src="${escapeHtml(data.qrImageUrl)}" alt="QR ${escapeHtml(data.tenPhong)}">
                        <div class="qr-room-meta">
                            <div><strong>Phòng:</strong> ${escapeHtml(data.tenPhong)}</div>
                            <div><strong>Cơ sở:</strong> ${escapeHtml(data.coSo)}</div>
                            <div><strong>Liên kết:</strong></div>
                            <textarea class="form-control" rows="3" readonly>${escapeHtml(data.targetUrl)}</textarea>
                            <button type="button" class="btn-submit" id="copyQrLinkBtn" style="margin-top:12px;">Sao chép link</button>
                        </div>
                    </div>
                `;
                document.getElementById('copyQrLinkBtn')?.addEventListener('click', async function() {
                    try {
                        await navigator.clipboard.writeText(data.targetUrl || '');
                        RoomToast.fire({
                            icon: 'success',
                            title: 'Đã sao chép link QR'
                        });
                    } catch (error) {
                        RoomToast.fire({
                            icon: 'error',
                            title: 'Không thể sao chép link QR'
                        });
                    }
                });
            } catch (error) {
                document.getElementById('qrRoomBody').innerHTML =
                    '<div class="ops-empty-state"><i class="fas fa-circle-exclamation"></i><div><strong>Không thể tải QR phòng</strong><p>Vui lòng thử lại sau.</p></div></div>';
            }
        }

        function populateSelectFromObject(select, options, selectedValue = '', placeholder = null) {
            if (!select) return;

            let html = placeholder ? `<option value="">${placeholder}</option>` : '';
            Object.entries(options || {}).forEach(([value, label]) => {
                html += `<option value="${value}" ${String(value) === String(selectedValue) ? 'selected' : ''}>${label}</option>`;
            });
            select.innerHTML = html;
        }

        function populateAssigneeOptions(selectedValue = '') {
            const select = document.getElementById('maintenanceAssigneeInput');
            if (!select) return;

            let html = '<option value="">-- Chưa phân công --</option>';
            branchStaffOptions.forEach(staff => {
                html += `<option value="${staff.taiKhoanId}" ${String(staff.taiKhoanId) === String(selectedValue) ? 'selected' : ''}>${staff.hoTen}</option>`;
            });
            select.innerHTML = html;
        }

        function resetMaintenanceForm() {
            document.getElementById('maintenanceTicketId').value = '';
            document.getElementById('maintenanceTitleInput').value = '';
            document.getElementById('maintenanceDescriptionInput').value = '';
            document.getElementById('maintenanceResolutionInput').value = '';
            populateAssigneeOptions('');
            if (currentMaintenancePayload) {
                populateSelectFromObject(document.getElementById('maintenancePriorityInput'), currentMaintenancePayload.priorityOptions, '1');
                populateSelectFromObject(document.getElementById('maintenanceStatusInput'), currentMaintenancePayload.statusOptions, '0');
            }
            document.getElementById('maintenanceFormTitle').textContent = 'Tạo phiếu bảo trì';
        }

        function prefillMaintenanceForm(ticketId) {
            const ticket = currentMaintenancePayload?.tickets?.find(item => String(item.phongHocBaoTriId) === String(ticketId));
            if (!ticket) return;

            document.getElementById('maintenanceTicketId').value = ticket.phongHocBaoTriId;
            document.getElementById('maintenanceTitleInput').value = ticket.tieuDe || '';
            document.getElementById('maintenanceDescriptionInput').value = ticket.moTa || '';
            document.getElementById('maintenanceResolutionInput').value = ticket.ketQuaXuLy || '';
            populateSelectFromObject(document.getElementById('maintenancePriorityInput'), currentMaintenancePayload.priorityOptions, ticket.mucDoUuTien);
            populateSelectFromObject(document.getElementById('maintenanceStatusInput'), currentMaintenancePayload.statusOptions, ticket.trangThai);
            populateAssigneeOptions(ticket.assignedToId || '');
            document.getElementById('maintenanceFormTitle').textContent = `Cập nhật phiếu ${ticket.maPhieu}`;
        }

        function renderMaintenancePayload(payload) {
            currentMaintenancePayload = payload;
            populateSelectFromObject(document.getElementById('maintenancePriorityInput'), payload.priorityOptions, document.getElementById('maintenancePriorityInput').value || '1');
            populateSelectFromObject(document.getElementById('maintenanceStatusInput'), payload.statusOptions, document.getElementById('maintenanceStatusInput').value || '0');
            populateAssigneeOptions(document.getElementById('maintenanceAssigneeInput').value || '');
            document.getElementById('maintenanceSummary').innerHTML =
                `<strong>${payload.summary.open}</strong> phiếu đang mở · <strong>${payload.summary.completed}</strong> đã hoàn tất`;

            if (!payload.tickets.length) {
                document.getElementById('maintenanceList').innerHTML =
                    '<div class="ops-empty-state"><i class="fas fa-screwdriver-wrench"></i><div><strong>Chưa có phiếu bảo trì</strong><p>Tạo phiếu để theo dõi quy trình xử lý sự cố phòng học.</p></div></div>';
            } else {
                document.getElementById('maintenanceList').innerHTML = payload.tickets.map(ticket => `
                    <div class="inline-item-card">
                        <div>
                            <div class="inline-item-title">${escapeHtml(ticket.maPhieu)} · ${escapeHtml(ticket.tieuDe)}</div>
                            <div class="inline-item-meta">${escapeHtml(ticket.mucDoUuTienLabel)} · ${escapeHtml(ticket.trangThaiLabel)}</div>
                            <div class="inline-item-meta">Phụ trách: ${escapeHtml(ticket.assignedToName)} · Tạo bởi: ${escapeHtml(ticket.createdByName)}</div>
                            <div class="inline-item-meta">${escapeHtml(ticket.ngayYeuCau || '—')}${ticket.ngayHoanTat ? ' · Hoàn tất ' + escapeHtml(ticket.ngayHoanTat) : ''}</div>
                        </div>
                        <div class="inline-item-actions">
                            <button type="button" class="btn-room-act edit" title="Sửa phiếu" onclick="prefillMaintenanceForm(${ticket.phongHocBaoTriId})">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            syncRoomSupplementaryMeta(currentMaintenanceRoomId, {
                ticketTotal: payload.tickets.length,
                openTickets: payload.summary.open
            });
            filterRooms();
        }

        async function loadMaintenanceTickets(roomId) {
            const response = await fetch(`/admin/phong-hoc/${roomId}/bao-tri`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const payload = await response.json();
            renderMaintenancePayload(payload.data);
        }

        async function openMaintenanceModal(id, name) {
            currentMaintenanceRoomId = id;
            document.getElementById('maintenanceModalTitle').innerHTML =
                `<i class="fas fa-screwdriver-wrench" style="color:#c2410c; margin-right:8px;"></i> Workflow bảo trì: ${escapeHtml(name)}`;
            document.getElementById('maintenanceList').innerHTML =
                '<div class="ops-empty-state"><i class="fas fa-spinner fa-spin"></i><div><strong>Đang tải</strong><p>Vui lòng chờ trong giây lát.</p></div></div>';
            document.getElementById('maintenanceModal').classList.add('show');
            resetMaintenanceForm();

            try {
                await loadMaintenanceTickets(id);
            } catch (error) {
                document.getElementById('maintenanceList').innerHTML =
                    '<div class="ops-empty-state"><i class="fas fa-circle-exclamation"></i><div><strong>Không thể tải workflow bảo trì</strong><p>Vui lòng thử lại sau.</p></div></div>';
            }
        }

        // ─── Lịch sử sử dụng phòng ─────────────────────────────────────────
        function closeLichSuModal() {
            document.getElementById('lichSuModal').classList.remove('show');
        }

        function openLichSuModal(id, name) {
            document.getElementById('lichSuModalTitle').innerHTML =
                `<i class="fas fa-history" style="color:#0369a1; margin-right:8px;"></i> Lịch sử sử dụng: ${escapeHtml(name)}`;
            document.getElementById('lichSuBody').innerHTML =
                '<div style="text-align:center;padding:2rem;color:#94a3b8;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:.75rem;">Đang tải...</p></div>';
            document.getElementById('lichSuModal').classList.add('show');

            fetch(`/admin/phong-hoc/${id}/lich-su`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success || data.data.length === 0) {
                        document.getElementById('lichSuBody').innerHTML =
                            '<div style="text-align:center;padding:2rem;color:#94a3b8;"><i class="fas fa-inbox fa-2x"></i><p style="margin-top:.75rem;">Phòng này chưa được sử dụng trong bất kỳ lớp học nào.</p></div>';
                        return;
                    }

                    const statusColors = {
                        0: '#64748b',
                        1: '#0284c7',
                        2: '#7c3aed',
                        3: '#dc2626',
                        4: '#16a34a',
                        5: '#d97706'
                    };

                    let rows = data.data.map((lop, i) => `
                    <tr>
                        <td style="color:#64748b;font-size:.82rem;">${i+1}</td>
                        <td><span style="font-weight:600;color:#0f172a;">${lop.maLopHoc || '—'}</span><br><small style="color:#64748b;">${lop.tenLopHoc}</small></td>
                        <td style="font-size:.85rem;">${lop.tenKhoaHoc}</td>
                        <td style="font-size:.82rem;color:#64748b;">${lop.tenGiaoVien}</td>
                        <td style="font-size:.82rem;">${lop.ngayBatDau} → ${lop.ngayKetThuc}</td>
                        <td><span style="background:${statusColors[lop.trangThai]}22;color:${statusColors[lop.trangThai]};font-size:.75rem;font-weight:600;padding:.2rem .6rem;border-radius:20px;">${lop.trangThaiLabel}</span></td>
                    </tr>
                `).join('');

                    document.getElementById('lichSuBody').innerHTML = `
                    <p style="color:#64748b;font-size:.85rem;margin-bottom:.75rem;">Tổng cộng <strong>${data.total}</strong> lớp học đã dùng phòng này ${data.total > 20 ? '(hiển thị 20 gần nhất)' : ''}.</p>
                    <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">#</th>
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">Mã / Tên lớp</th>
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">Khóa học</th>
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">Giáo viên</th>
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">Thời gian</th>
                                <th style="padding:.6rem .8rem;text-align:left;color:#6b7280;font-size:.75rem;font-weight:600;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                    </div>
                `;
                }).catch(() => {
                    document.getElementById('lichSuBody').innerHTML =
                        '<div style="text-align:center;padding:2rem;color:#dc2626;">Không thể tải lịch sử sử dụng.</div>';
                });
        }

        document.getElementById('lichSuModal').addEventListener('click', e => {
            if (e.target === document.getElementById('lichSuModal')) closeLichSuModal();
        });
        document.getElementById('baoTriModal').addEventListener('click', e => {
            if (e.target === document.getElementById('baoTriModal')) closeBaoTriModal();
        });

        // Xử lý Modal & AJAX Form Submit
        const modal = document.getElementById('roomModal');
        const form = document.getElementById('roomForm');
        const methodInput = document.getElementById('roomMethod');
        const titleEl = document.getElementById('roomModalTitle');
        const submitBtn = document.getElementById('roomSubmitBtn');
        let currentEditingRowId = null;

        function openRoomModal(id = null, name = '', capacity = '', equip = '', block = '', floor = '', status = 1) {
            if (id) {
                // Edit mode
                titleEl.innerHTML =
                    '<i class="fas fa-pen" style="color:#d97706; margin-right:8px;"></i> Chỉnh sửa phòng học';
                submitBtn.textContent = 'Cập nhật thay đổi';
                form.action = `/admin/phong-hoc/${id}`;
                methodInput.value = 'PUT';
                currentEditingRowId = id;

                document.getElementById('tenPhong').value = name;
                document.getElementById('sucChua').value = capacity;
                document.getElementById('trangThietBi').value = equip;
                document.getElementById('khuBlock').value = block;
                document.getElementById('tang').value = floor;
                document.getElementById('roomTrangThai').value = status;
            } else {
                // Create mode
                titleEl.innerHTML =
                    '<i class="fas fa-plus-circle" style="color:#27c4b5; margin-right:8px;"></i> Thêm phòng học mới';
                submitBtn.textContent = 'Lưu phòng học';
                form.action = "{{ route('admin.phong-hoc.store') }}";
                methodInput.value = 'POST';
                currentEditingRowId = null;

                form.reset();
                document.getElementById('khuBlock').value = '';
                document.getElementById('tang').value = '';
                document.getElementById('roomTrangThai').value = '1'; // Default
            }

            modal.classList.add('show');
        }

        function closeRoomModal() {
            modal.classList.remove('show');
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeRoomModal();
            }
        });

        async function submitRoomForm(forceMaintenance = false) {
            const actionUrl = form.action;
            const formData = new FormData(form);
            if (forceMaintenance) {
                formData.append('forceMaintenance', '1');
            }
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            submitBtn.disabled = true;

            try {
                const res = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const data = await res.json();

                if (res.status === 409 && data.requiresConfirmation) {
                    const result = await Swal.fire({
                        title: 'Xung đột lịch bảo trì',
                        html: buildMaintenanceImpactHtml(data.impact || {}),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Vẫn lưu thay đổi',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#d97706'
                    });

                    if (result.isConfirmed) {
                        await submitRoomForm(true);
                    }
                    return;
                }

                if (!res.ok) {
                    if (data.errors) {
                        const errText = Object.values(data.errors).flat().join('<br>');
                        RoomToast.fire({
                            icon: 'error',
                            title: errText
                        });
                    } else {
                        RoomToast.fire({
                            icon: 'error',
                            title: data.message || 'Lỗi không xác định.'
                        });
                    }
                    return;
                }

                RoomToast.fire({
                    icon: 'success',
                    title: data.message
                });
                closeRoomModal();

                const room = data.room;
                if (currentEditingRowId) {
                    const tr = document.getElementById('room-row-' + currentEditingRowId);
                    if (tr) {
                        const index = tr.querySelector('.row-index').textContent;
                        tr.innerHTML = createRowHTML(room, index);
                        applyRoomRowDatasets(tr, room);
                    }
                } else {
                    updateRoomCount(1);
                    let tbody = document.querySelector('.rooms-table tbody');
                    if (!tbody) {
                        setTimeout(() => window.location.reload(), 500);
                        return;
                    }

                    const rowsCount = tbody.querySelectorAll('tr').length;
                    const tr = document.createElement('tr');
                    tr.id = 'room-row-' + room.phongHocId;
                    tr.innerHTML = createRowHTML(room, rowsCount + 1);
                    applyRoomRowDatasets(tr, room);
                    tbody.appendChild(tr);
                }
                refreshOperationsSnapshot();
                filterRooms();
            } catch (err) {
                RoomToast.fire({
                    icon: 'error',
                    title: 'Mất kết nối tới máy chủ.'
                });
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        }

        // Xử lý Submit AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitRoomForm();
        });

        document.getElementById('maintenanceForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!currentMaintenanceRoomId) return;

            const ticketId = document.getElementById('maintenanceTicketId').value;
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('tieuDe', document.getElementById('maintenanceTitleInput').value);
            formData.append('moTa', document.getElementById('maintenanceDescriptionInput').value);
            formData.append('mucDoUuTien', document.getElementById('maintenancePriorityInput').value);
            formData.append('trangThai', document.getElementById('maintenanceStatusInput').value);
            formData.append('assignedToId', document.getElementById('maintenanceAssigneeInput').value);
            formData.append('ketQuaXuLy', document.getElementById('maintenanceResolutionInput').value);
            if (ticketId) {
                formData.append('_method', 'PATCH');
            }

            const response = await fetch(ticketId ? `/admin/phong-hoc/bao-tri/${ticketId}` : `/admin/phong-hoc/${currentMaintenanceRoomId}/bao-tri`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                RoomToast.fire({
                    icon: 'error',
                    title: payload.message || Object.values(payload.errors || {}).flat().join(', ') || 'Không thể lưu phiếu bảo trì.'
                });
                return;
            }

            RoomToast.fire({
                icon: 'success',
                title: payload.message
            });
            resetMaintenanceForm();
            await loadMaintenanceTickets(currentMaintenanceRoomId);
            refreshOperationsSnapshot();
        });

        ['roomSearchInput', 'roomStatusFilter', 'roomCapacityFilter'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', filterRooms);
            document.getElementById(id)?.addEventListener('change', filterRooms);
        });
        document.getElementById('roomOpenTicketFilter')?.addEventListener('change', filterRooms);
        document.getElementById('floorPlanBlockFilter')?.addEventListener('change', () => renderFloorPlan(latestOperationsSnapshot.roomStates || []));
        document.getElementById('floorPlanLevelFilter')?.addEventListener('change', () => renderFloorPlan(latestOperationsSnapshot.roomStates || []));

        ['qrRoomModal', 'maintenanceModal'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.room-actions-menu')) {
                closeRoomActionMenus();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            renderOperationsSnapshot(initialOperationsSnapshot);
            populateAssigneeOptions('');
            window.setInterval(refreshOperationsSnapshot, 60000);
            filterRooms();

            const params = new URLSearchParams(window.location.search);
            const roomId = params.get('room');
            if (roomId) {
                const row = document.getElementById(`room-row-${roomId}`);
                if (row) {
                    row.classList.add('room-row-highlight');
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });
    </script>
@endsection
