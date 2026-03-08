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
                <div style="margin-top: 20px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                    {!! $coSo->banDoGoogle !!}
                </div>
            @endif
        </div>

        <div style="display: flex; flex-direction: column; gap: 24px;">
            {{-- DANH SÁCH PHÒNG HỌC --}}
            <div class="rooms-card">
                <div class="rooms-header">
                    <div class="rooms-title">
                        <i class="fas fa-chalkboard-user" style="color:#8b5cf6;"></i>
                        Danh sách phòng học
                    </div>
                    <div class="rooms-count">{{ $coSo->phong_hocs_count ?? count($coSo->phongHocs) }} phòng</div>
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
                                <th>TTB / Ghi chú</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coSo->phongHocs as $index => $room)
                                <tr id="room-row-{{ $room->phongHocId }}">
                                    <td style="color:#64748b;" class="row-index">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="room-name">
                                            <i class="fas fa-door-closed" style="color:#cbd5e1;"></i>
                                            {{ $room->tenPhong }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="room-cap"><i class="fas fa-users"
                                                style="color:#94a3b8; margin-right:4px;"></i>
                                            {{ $room->sucChua ?? 0 }}</span>
                                    </td>
                                    <td>
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
                                        @elseif ($room->isCurrentlyInUse())
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
                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size:0.85rem; color:#64748b;"
                                        title="{{ $room->trangThietBi }}">
                                        {{ $room->trangThietBi ?: '—' }}
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn-room-act edit" title="Sửa"
                                            onclick="openRoomModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}', '{{ $room->sucChua }}', '{{ addslashes($room->trangThietBi) }}', {{ $room->trangThai }})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn-room-act" title="Lịch sử sử dụng"
                                            onclick="openLichSuModal({{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')"
                                            style="background:#e0f2fe; color:#0369a1;">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button
                                            class="btn-room-act {{ $room->trangThai == 1 ? 'btn-bao-tri' : 'btn-san-sang' }}"
                                            title="{{ $room->trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng' }}"
                                            onclick="confirmToggleStatus(this, {{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}', {{ $room->trangThai }})">
                                            <i
                                                class="fas {{ $room->trangThai == 1 ? 'fa-tools' : 'fa-check-circle' }}"></i>
                                        </button>
                                        <button class="btn-room-act del" title="Xóa"
                                            onclick="confirmDeleteRoom(this, {{ $room->phongHocId }}, '{{ addslashes($room->tenPhong) }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

@endsection

@section('script')
    <script>
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

        function updateRoomCount(change) {
            const countEl = document.querySelector('.rooms-count');
            if (countEl) {
                let current = parseInt(countEl.textContent) || 0;
                current += change;
                countEl.textContent = `${current} phòng`;

                // Xử lý Empty State
                const table = document.querySelector('.rooms-table');
                const emptyState = document.querySelector('.empty-rooms');

                if (current === 0) {
                    if (table) table.style.display = 'none';
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    } else {
                        // Tạo empty state nếu chưa có
                        const roomsCard = document.querySelector('.rooms-card');
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

        function createRowHTML(room, index = '-') {
            let statusBadge = '';
            if (room.trangThai == 0) {
                statusBadge =
                    `<div style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:0.85rem; font-weight:600;"><i class="fas fa-ban"></i> Vô hiệu hóa</div>`;
            } else if (room.trangThai == 3) {
                statusBadge =
                    `<div style="display:flex; align-items:center; gap:6px; color:#d97706; font-size:0.85rem; font-weight:600;"><i class="fas fa-tools"></i> Bảo trì</div>`;
            } else if (room.is_currently_in_use) {
                statusBadge =
                    `<div style="display:flex; align-items:center; gap:6px; color:#0ea5e9; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Đang sử dụng</div>`;
            } else {
                statusBadge =
                    `<div style="display:flex; align-items:center; gap:6px; color:#16a34a; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Sẵn sàng</div>`;
            }

            return `
                <td style="color:#64748b;" class="row-index">${index}</td>
                <td>
                    <div class="room-name">
                        <i class="fas fa-door-closed" style="color:#cbd5e1;"></i>
                        ${room.tenPhong}
                    </div>
                </td>
                <td>
                    <span class="room-cap"><i class="fas fa-users" style="color:#94a3b8; margin-right:4px;"></i> ${room.sucChua || 0}</span>
                </td>
                <td>${statusBadge}</td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size:0.85rem; color:#64748b;" title="${room.trangThietBi || ''}">
                    ${room.trangThietBi || '—'}
                </td>
                <td style="text-align: right;">
                    <button class="btn-room-act edit" title="Sửa"
                        onclick="openRoomModal(${room.phongHocId}, '${room.tenPhong}', '${room.sucChua || ''}', '${room.trangThietBi || ''}', ${room.trangThai})">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-room-act" title="Lịch sử sử dụng"
                        onclick="openLichSuModal(${room.phongHocId}, '${room.tenPhong}')"
                        style="background:#e0f2fe; color:#0369a1;">
                        <i class="fas fa-history"></i>
                    </button>
                    <button class="btn-room-act ${room.trangThai == 1 ? 'btn-bao-tri' : 'btn-san-sang'}" title="${room.trangThai == 1 ? 'Chuyển bảo trì' : 'Đánh dấu sẵn sàng'}"
                        onclick="confirmToggleStatus(this, ${room.phongHocId}, '${room.tenPhong}', ${room.trangThai})">
                        <i class="fas ${room.trangThai == 1 ? 'fa-tools' : 'fa-check-circle'}"></i>
                    </button>
                    <button class="btn-room-act del" title="Xóa"
                        onclick="confirmDeleteRoom(this, ${room.phongHocId}, '${room.tenPhong}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        }

        // Xóa Phòng học qua AJAX
        function confirmDeleteRoom(btn, id, name) {
            Swal.fire({
                title: 'Xóa phòng học?',
                html: `Bạn có chắc chắn muốn xóa phòng <strong>${name}</strong>? Quá trình này không thể hoàn tác.`,
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
                    html: `Đánh dấu phòng <strong>${name}</strong> là Sẵn sàng sử dụng?`,
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

        function doToggle(btn, id, ghiChu) {
            const fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', '{{ csrf_token() }}');
            if (ghiChu) fd.append('ghiChuBaoTri', ghiChu);

            fetch(`/admin/phong-hoc/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        RoomToast.fire({
                            icon: 'success',
                            title: data.message
                        });
                        // Cập nhật DOM
                        const tr = btn.closest('tr');
                        const newStatus = data.trangThai;
                        // Cập nhật badge trạng thái
                        const tdStatus = tr.querySelectorAll('td')[3];
                        if (newStatus == 1) {
                            tdStatus.innerHTML =
                                `<div style="display:flex;align-items:center;gap:6px;color:#16a34a;font-size:.85rem;font-weight:600;"><i class="fas fa-circle" style="font-size:.5em;"></i> Sẵn sàng</div>`;
                            btn.className = 'btn-room-act btn-bao-tri';
                            btn.title = 'Chuyển bảo trì';
                            btn.innerHTML = '<i class="fas fa-tools"></i>';
                            btn.setAttribute('onclick', `confirmToggleStatus(this, ${id}, '${data.room.tenPhong}', 1)`);
                        } else {
                            tdStatus.innerHTML =
                                `<div style="display:flex;align-items:center;gap:6px;color:#d97706;font-size:.85rem;font-weight:600;"><i class="fas fa-tools"></i> Bảo trì</div>`;
                            btn.className = 'btn-room-act btn-san-sang';
                            btn.title = 'Đánh dấu sẵn sàng';
                            btn.innerHTML = '<i class="fas fa-check-circle"></i>';
                            btn.setAttribute('onclick', `confirmToggleStatus(this, ${id}, '${data.room.tenPhong}', 3)`);
                        }
                    } else {
                        RoomToast.fire({
                            icon: 'error',
                            title: data.message || 'Thao tác thất bại.'
                        });
                    }
                }).catch(() => {
                    RoomToast.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối.'
                    });
                });
        }

        // ─── Lịch sử sử dụng phòng ─────────────────────────────────────────
        function closeLichSuModal() {
            document.getElementById('lichSuModal').classList.remove('show');
        }

        function openLichSuModal(id, name) {
            document.getElementById('lichSuModalTitle').innerHTML =
                `<i class="fas fa-history" style="color:#0369a1; margin-right:8px;"></i> Lịch sử sử dụng: ${name}`;
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

        function openRoomModal(id = null, name = '', capacity = '', equip = '', status = 1) {
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

        // Xử lý Submit AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = form.action;
            const formData = new FormData(form);
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            submitBtn.disabled = true;

            fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async (res) => {
                    const data = await res.json();
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

                    // Success
                    RoomToast.fire({
                        icon: 'success',
                        title: data.message
                    });
                    closeRoomModal();

                    // Cập nhật DOM
                    const room = data.room;
                    if (currentEditingRowId) {
                        // Update existing row
                        const tr = document.getElementById('room-row-' + currentEditingRowId);
                        if (tr) {
                            const index = tr.querySelector('.row-index').textContent;
                            tr.innerHTML = createRowHTML(room, index);
                        }
                    } else {
                        // Append new row
                        updateRoomCount(1);
                        let tbody = document.querySelector('.rooms-table tbody');
                        if (!tbody) {
                            // Bảng chưa render tbody do empty state -> reload trang hoặc rắc rối hơn thì tạo bảng
                            // Reload nhanh cho case từ rỗng lên 1
                            setTimeout(() => window.location.reload(), 500);
                            return;
                        }

                        const rowsCount = tbody.querySelectorAll('tr').length;
                        const tr = document.createElement('tr');
                        tr.id = 'room-row-' + room.phongHocId;
                        tr.innerHTML = createRowHTML(room, rowsCount + 1);
                        tbody.appendChild(tr);
                    }
                })
                .catch(err => {
                    RoomToast.fire({
                        icon: 'error',
                        title: 'Mất kết nối tới máy chủ.'
                    });
                })
                .finally(() => {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
        });
    </script>
@endsection
