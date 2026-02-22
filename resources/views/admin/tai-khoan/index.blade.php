@extends('layouts.admin')

@section('title', 'Tài Khoản Hệ Thống')
@section('page-title', 'Tài Khoản Hệ Thống')
@section('breadcrumb', 'Cấu hình hệ thống · Tài khoản')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/tai-khoan/index.css') }}">
@endsection

@section('content')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="stats-card">
            <div class="stats-icon"><i class="fas fa-users"></i></div>
            <div>
                <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Tổng số tài khoản</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $tongSo }}</div>
            </div>
        </div>
        <div class="stats-card">
            <div class="stats-icon" style="color: #10b981;"><i class="fas fa-user-check"></i></div>
            <div>
                <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Đang hoạt động</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $dangHoatDong }}</div>
            </div>
        </div>
        <div class="stats-card">
            <div class="stats-icon" style="color: #ef4444;"><i class="fas fa-user-lock"></i></div>
            <div>
                <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Đang khóa</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $tongSo - $dangHoatDong }}</div>
            </div>
        </div>
    </div>

    <div class="filters-box">
        <form action="" method="GET"
            style="display:flex; width:100%; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group-filter">
                <label>Tìm kiếm</label>
                <input type="text" name="q" value="{{ request('q') }}" class="tk-input"
                    placeholder="Tên ĐN, Email, Tên/SĐT...">
            </div>
            <div class="form-group-filter" style="max-width: 200px;">
                <label>Vai trò</label>
                <select name="role" class="tk-select">
                    <option value="">-- Tất cả --</option>
                    <option value="3" {{ request('role') == '3' ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Nhân viên</option>
                    <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Giáo viên</option>
                    <option value="0" {{ request('role') == '0' ? 'selected' : '' }}>Học viên</option>
                </select>
            </div>
            <div class="form-group-filter" style="max-width: 200px;">
                <label>Trạng thái</label>
                <select name="trangThai" class="tk-select">
                    <option value="">-- Tất cả --</option>
                    <option value="1" {{ request('trangThai') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="0" {{ request('trangThai') == '0' ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
            <div style="display: flex; gap:10px;">
                <button type="submit" class="tk-btn tk-btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                <a href="{{ route('admin.tai-khoan.index') }}" class="tk-btn tk-btn-light">Xóa bộ lọc</a>
            </div>
        </form>
    </div>

    <div class="tk-table-container">
        <table class="tk-table">
            <thead>
                <tr>
                    <th style="padding-left: 20px;">Tài khoản</th>
                    <th>Liên hệ</th>
                    <th>Vai trò</th>
                    <th>Nhóm quyền</th>
                    <th>Tình trạng</th>
                    <th class="text-right" style="padding-right: 20px; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taiKhoans as $tk)
                    @php
                        $hoTen = $tk->hoSoNguoiDung->hoTen ?? $tk->taiKhoan;
                        $char = mb_strtoupper(mb_substr($hoTen, 0, 1));
                        $rc = match ($tk->role) {
                            3 => 'role-admin',
                            2 => 'role-nhanvien',
                            1 => 'role-giaovien',
                            default => 'role-hocvien',
                        };
                    @endphp
                    <tr>
                        <td style="padding-left: 20px;">
                            <div style="display:flex; align-items:center; gap: 12px;">
                                <div class="avatar-circle">{{ $char }}</div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a;">{{ $tk->taiKhoan }}</div>
                                    <div style="font-size: 0.85rem; color: #64748b;">{{ $hoTen }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                            @if ($tk->email)
                                <div><i class="fas fa-envelope fa-fw" style="color:#94a3b8;"></i> {{ $tk->email }}</div>
                            @else
                                <div style="color:#cbd5e1;">Chưa có email</div>
                            @endif
                            @if (optional($tk->hoSoNguoiDung)->soDienThoai)
                                <div><i class="fas fa-phone fa-fw" style="color:#94a3b8;"></i>
                                    {{ $tk->hoSoNguoiDung->soDienThoai }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge-role {{ $rc }}">{{ $tk->getRoleLabel() }}</span>
                        </td>
                        <td>
                            @if ($tk->isStaff())
                                @if ($tk->nhomQuyen)
                                    <span style="font-size: 0.85rem; font-weight:600; color:#334155;"><i
                                            class="fas fa-shield-alt" style="color: #0ea5e9;"></i>
                                        {{ $tk->nhomQuyen->tenNhom }}</span>
                                @else
                                    <span style="font-size: 0.85rem; font-style: italic; color:#94a3b8;">Chưa cấp
                                        quyền</span>
                                @endif
                            @else
                                <span style="font-size: 0.85rem; color:#cbd5e1;">(Mặc định)</span>
                            @endif
                        </td>
                        <td>
                            @if ($tk->trangThai == 1)
                                <div class="status-active" style="font-weight:600; font-size:0.85rem;"><i
                                        class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle;"></i> Hoạt
                                    động</div>
                            @else
                                <div class="status-inactive" style="font-weight:600; font-size:0.85rem;"><i
                                        class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle;"></i> Bị khóa
                                </div>
                            @endif
                            <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;">Lần cuối:
                                {{ $tk->lastLogin ? \Carbon\Carbon::parse($tk->lastLogin)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}
                            </div>
                        </td>
                        <td style="padding-right: 20px; text-align: right;">
                            @if ($tk->isStaff() && $tk->taiKhoanId !== auth()->id())
                                <button class="btn-icon quyen" title="Cấp nhóm quyền"
                                    onclick="openQuyenModal({{ $tk->taiKhoanId }}, '{{ addslashes($tk->taiKhoan) }}', {{ $tk->nhomQuyenId ?? 'null' }})">
                                    <i class="fas fa-user-shield"></i>
                                </button>
                            @endif
                            <button class="btn-icon pass" title="Reset mật khẩu"
                                onclick="openPasswordModal({{ $tk->taiKhoanId }}, '{{ addslashes($tk->taiKhoan) }}')">
                                <i class="fas fa-key"></i>
                            </button>

                            @if ($tk->taiKhoanId !== auth()->id())
                                <button class="btn-icon {{ $tk->trangThai == 1 ? 'lock' : 'unlock' }}"
                                    title="{{ $tk->trangThai == 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"
                                    onclick="toggleStatus({{ $tk->taiKhoanId }}, '{{ addslashes($tk->taiKhoan) }}', {{ $tk->trangThai }})">
                                    <i class="fas {{ $tk->trangThai == 1 ? 'fa-lock' : 'fa-unlock' }}"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 60px 20px;">
                            <i class="fas fa-search-minus"
                                style="font-size: 3rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                            <div style="color: #64748b; font-weight: 500;">Không tìm thấy tài khoản nào phù hợp</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($taiKhoans->hasPages())
            <div class="tk-pagination">
                {{ $taiKhoans->links() }}
            </div>
        @endif
    </div>

    {{-- Custom Modal Cấp Quyền --}}
    <div class="tk-modal-overlay" id="quyenModal" onclick="closeModal('quyenModal', event)">
        <div class="tk-modal-content">
            <div class="tk-modal-header">
                <h5 class="tk-modal-title"><i class="fas fa-shield-alt" style="color:#0ea5e9;"></i> Cấp nhóm quyền</h5>
                <button type="button" class="tk-modal-close" onclick="closeModal('quyenModal')"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="tk-modal-body">
                <p style="margin-bottom: 15px;">Cấp nhóm quyền quản lý cho tài khoản: <strong id="q-username"
                        style="color:#0ea5e9;"></strong></p>
                <form id="quyenForm">
                    <input type="hidden" id="q-id">
                    <div style="margin-bottom: 10px;">
                        <select id="q-role" class="tk-select">
                            <option value="">-- Chọn nhóm quyền --</option>
                            @foreach ($nhomQuyens as $nq)
                                <option value="{{ $nq->nhomQuyenId }}">{{ $nq->tenNhom }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="tk-modal-footer">
                <button type="button" class="tk-btn tk-btn-light" onclick="closeModal('quyenModal')">Hủy</button>
                <button type="button" class="tk-btn tk-btn-primary" onclick="submitQuyen()">Lưu thay đổi</button>
            </div>
        </div>
    </div>

    {{-- Custom Modal Đổi Mật Khẩu --}}
    <div class="tk-modal-overlay" id="passModal" onclick="closeModal('passModal', event)">
        <div class="tk-modal-content">
            <div class="tk-modal-header">
                <h5 class="tk-modal-title"><i class="fas fa-key" style="color:#eab308;"></i> Cấp lại mật khẩu</h5>
                <button type="button" class="tk-modal-close" onclick="closeModal('passModal')"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="tk-modal-body">
                <p style="margin-bottom: 15px;">Tài khoản: <strong id="p-username" style="color:#eab308;"></strong></p>
                <form id="passForm">
                    <input type="hidden" id="p-id">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Mật khẩu
                            mới</label>
                        <input type="password" id="p-pass" class="tk-input" placeholder="Ít nhất 8 ký tự">
                    </div>
                    <div>
                        <label style="display: block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Xác nhận mật
                            khẩu</label>
                        <input type="password" id="p-passConfirm" class="tk-input" placeholder="Nhập lại mật khẩu mới">
                    </div>
                </form>
            </div>
            <div class="tk-modal-footer">
                <button type="button" class="tk-btn tk-btn-light" onclick="closeModal('passModal')">Hủy</button>
                <button type="button" class="tk-btn" style="background:#eab308;" onclick="submitPass()">Reset Mật
                    Khẩu</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Custom Modal Logic
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id, event) {
            // Only close if clicking the close btn, cancel btn, or overlay background. Not the content.
            if (event) {
                if (event.target.id === id) {
                    document.getElementById(id).classList.remove('active');
                }
            } else {
                document.getElementById(id).classList.remove('active');
            }
        }

        // Handle Quyền
        function openQuyenModal(id, username, currentQuyen) {
            document.getElementById('q-id').value = id;
            document.getElementById('q-username').textContent = username;
            document.getElementById('q-role').value = currentQuyen || '';
            openModal('quyenModal');
        }

        function submitQuyen() {
            let id = document.getElementById('q-id').value;
            let qId = document.getElementById('q-role').value;

            if (!qId) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Vui lòng chọn 1 nhóm quyền!'
                });
                return;
            }

            fetch(`/admin/tai-khoan/${id}/nhom-quyen`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nhomQuyenId: qId
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    closeModal('quyenModal');
                    Swal.fire('Thành công', data.message, 'success').then(() => window.location.reload());
                } else {
                    Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }

        // Handle Lock
        function toggleStatus(id, username, status) {
            let text = status == 1 ? `Bạn có chắc muốn KHÓA tài khoản ${username}?` :
                `Bạn muốn MỞ KHÓA tài khoản ${username}?`;
            let confirmBtn = status == 1 ? 'Khóa Ngay' : 'Mở Khóa';
            let color = status == 1 ? '#dc2626' : '#16a34a';

            Swal.fire({
                title: 'Xác nhận!',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/tai-khoan/${id}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            Swal.fire('Thành công', data.message, 'success').then(() => window.location
                                .reload());
                        } else {
                            Swal.fire('Thất bại', data.message, 'error');
                        }
                    });
                }
            });
        }

        // Handle Pass
        function openPasswordModal(id, username) {
            document.getElementById('p-id').value = id;
            document.getElementById('p-username').textContent = username;
            document.getElementById('p-pass').value = '';
            document.getElementById('p-passConfirm').value = '';
            openModal('passModal');
        }

        function submitPass() {
            let id = document.getElementById('p-id').value;
            let p1 = document.getElementById('p-pass').value;
            let p2 = document.getElementById('p-passConfirm').value;

            if (!p1 || p1.length < 8) {
                Toast.fire({
                    icon: 'error',
                    title: 'Mật khẩu phải từ 8 ký tự'
                });
                return;
            }

            if (p1 !== p2) {
                Toast.fire({
                    icon: 'error',
                    title: 'Xác nhận mật khẩu không khớp'
                });
                return;
            }

            fetch(`/admin/tai-khoan/${id}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    matKhau: p1,
                    matKhau_confirmation: p2
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    closeModal('passModal');
                    Swal.fire('Thành công', data.message, 'success');
                } else {
                    if (data.errors) {
                        Swal.fire('Lỗi', Object.values(data.errors).flat().join('<br>'), 'error');
                    } else {
                        Swal.fire('Lỗi', data.message || 'Có lỗi', 'error');
                    }
                }
            });
        }
    </script>
@endsection
