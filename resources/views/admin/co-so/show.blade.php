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
                                            style="color:#94a3b8; margin-right:4px;"></i> {{ $room->sucChua ?? 0 }}</span>
                                </td>
                                <td>
                                    @if ($room->trangThai == 1)
                                        <div
                                            style="display:flex; align-items:center; gap:6px; color:#16a34a; font-size:0.85rem; font-weight:600;">
                                            <i class="fas fa-circle" style="font-size:0.5em;"></i> Sẵn sàng
                                        </div>
                                    @else
                                        <div
                                            style="display:flex; align-items:center; gap:6px; color:#dc2626; font-size:0.85rem; font-weight:600;">
                                            <i class="fas fa-circle" style="font-size:0.5em;"></i> Bảo trì
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
    </div>

    {{-- MODAL PHÒNG HỌC --}}
    <div class="custom-modal" id="roomModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="roomModalTitle">Thêm phòng học mới</div>
                <button type="button" class="btn-close" onclick="closeRoomModal()"><i class="fas fa-times"></i></button>
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
                            <option value="0">Đang bảo trì / Khoá</option>
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
            let statusBadge = room.trangThai == 1 ?
                `<div style="display:flex; align-items:center; gap:6px; color:#16a34a; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Sẵn sàng</div>` :
                `<div style="display:flex; align-items:center; gap:6px; color:#dc2626; font-size:0.85rem; font-weight:600;"><i class="fas fa-circle" style="font-size:0.5em;"></i> Bảo trì</div>`;

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
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
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
