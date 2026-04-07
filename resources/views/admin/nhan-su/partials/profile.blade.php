@php
    $routePrefix = $routePrefix ?? ((int) $role === \App\Models\Auth\TaiKhoan::ROLE_GIAO_VIEN ? 'admin.giao-vien' : 'admin.nhan-vien');
    $profile = $record->hoSoNguoiDung;
    $staff = $record->nhanSu;
    $profileRecord = $hoSoNhanSu;
    $currentSalary = $goiLuongHienHanh;
@endphp

<div class="profile-page">
    <div class="profile-hero">
        {{-- ===== AVATAR ===== --}}
        @php
            $avatarUrl    = $record->getAvatarUrl();
            $isDefault    = str_contains($avatarUrl, 'user-default.png');
            $hoTenFull    = $profile?->hoTen ?? '';
            // Lấy ký tự đầu trong HỌ (từ đầu tiên)
            $initials     = mb_strtoupper(mb_substr(trim(explode(' ', $hoTenFull)[0]), 0, 1, 'UTF-8'), 'UTF-8') ?: '?';
        @endphp

        <div class="avatar-wrapper">
            @if (!$isDefault)
                <img src="{{ $avatarUrl }}" alt="Ảnh đại diện" class="avatar-img">
            @else
                <div class="avatar-initials">{{ $initials }}</div>
            @endif
        </div>

        <div style="flex:1">
            <h1>{{ $profile?->hoTen ?: $record->taiKhoan }}</h1>
            <p>
                Hồ sơ {{ mb_strtolower($roleLabel) }} gồm tài khoản, thông tin cá nhân, quy định áp dụng, gói lương hiện hành
                và tài liệu nhân sự nội bộ.
            </p>
            <div style="margin-top:14px">
                <span class="profile-status {{ (int) $record->trangThai === 1 ? 'profile-status-active' : 'profile-status-locked' }}">
                    <i class="fas fa-circle"></i>
                    {{ (int) $record->trangThai === 1 ? 'Đang hoạt động' : 'Đang khóa' }}
                </span>
            </div>
        </div>

        <div class="profile-actions">
            <a href="{{ route($routePrefix . '.edit', $record->taiKhoan) }}" class="profile-btn profile-btn-ghost">
                <i class="fas fa-pen"></i> Sửa hồ sơ
            </a>
            <a href="{{ route($routePrefix . '.profile.pdf', $record->taiKhoan) }}" class="profile-btn profile-btn-ghost">
                <i class="fas fa-file-pdf"></i> Xuất hồ sơ
            </a>
            <a href="{{ route($routePrefix . '.index') }}" class="profile-btn profile-btn-primary">
                <i class="fas fa-list"></i> Danh sách
            </a>
        </div>
    </div>

    @if ($handover)
        <div class="profile-credential">
            <strong>Phiếu bàn giao tài khoản</strong>
            <div>Tên đăng nhập</div>
            <code id="handover-username">{{ $handover['username'] }}</code>
            <div style="margin-top:10px">Mật khẩu tạm</div>
            <code id="handover-password">{{ $handover['password'] }}</code>
            <p style="margin:12px 0 0;color:#7c2d12">
                Thông tin này chỉ hiển thị trong lượt mở đầu tiên và hết hạn lúc
                {{ \Illuminate\Support\Carbon::parse($handover['expires_at'])->format('d/m/Y H:i') }}.
            </p>
            <div class="profile-credential-actions">
                <button type="button" class="profile-btn profile-btn-primary" data-copy-credentials>
                    <i class="fas fa-copy"></i> Sao chép
                </button>
                <a href="{{ route($routePrefix . '.handover.pdf', ['taiKhoan' => $record->taiKhoan, 'token' => request('handover')]) }}"
                    class="profile-btn profile-btn-ghost">
                    <i class="fas fa-print"></i> Tải phiếu bàn giao
                </a>
            </div>
        </div>
    @endif

    <div class="profile-grid">
        <div class="profile-stack">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Tài khoản và trạng thái</h2>
                </div>
                <div class="profile-card-body">
                    <div class="profile-kv">
                        <div class="profile-kv-item">
                            <strong>Tên đăng nhập</strong>
                            <span>{{ $record->taiKhoan }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Email</strong>
                            <span>{{ $record->email }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Vai trò</strong>
                            <span>{{ $roleLabel }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Lần đăng nhập gần nhất</strong>
                            <span>{{ $record->lastLogin ? $record->lastLogin->format('d/m/Y H:i') : 'Chưa có dữ liệu' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Thông tin cá nhân</h2>
                </div>
                <div class="profile-card-body">
                    <div class="profile-kv">
                        <div class="profile-kv-item">
                            <strong>Họ và tên</strong>
                            <span>{{ $profile?->hoTen ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Ngày sinh</strong>
                            <span>{{ $profile?->ngaySinh ? \Illuminate\Support\Carbon::parse($profile->ngaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Giới tính</strong>
                            <span>
                                {{ match ((string) ($profile?->gioiTinh ?? '')) {
                                    '1' => 'Nam',
                                    '0' => 'Nữ',
                                    '2' => 'Khác',
                                    default => 'Chưa cập nhật',
                                } }}
                            </span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Số điện thoại</strong>
                            <span>{{ $profile?->soDienThoai ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Zalo</strong>
                            <span>{{ $profile?->zalo ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>CCCD / CMND</strong>
                            <span>{{ $profile?->cccd ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item" style="grid-column:1/-1">
                            <strong>Địa chỉ</strong>
                            <span>{{ $profile?->diaChi ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item" style="grid-column:1/-1">
                            <strong>Ghi chú nội bộ</strong>
                            <span>{{ $profile?->ghiChu ?: 'Không có ghi chú' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Thông tin nhân sự</h2>
                </div>
                <div class="profile-card-body">
                    <div class="profile-kv">
                        <div class="profile-kv-item">
                            <strong>Chức vụ</strong>
                            <span>{{ $staff?->chucVu ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Chuyên môn</strong>
                            <span>{{ $staff?->chuyenMon ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Bằng cấp</strong>
                            <span>{{ $staff?->bangCap ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Học vị / Chứng chỉ</strong>
                            <span>{{ $staff?->hocVi ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Loại hợp đồng</strong>
                            <span>{{ $staff?->loaiHopDong ? ($loaiHopDongOptions[$staff->loaiHopDong] ?? $staff->loaiHopDong) : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item">
                            <strong>Ngày vào làm</strong>
                            <span>{{ $staff?->ngayVaoLam ? \Illuminate\Support\Carbon::parse($staff->ngayVaoLam)->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="profile-kv-item" style="grid-column:1/-1">
                            <strong>Cơ sở làm việc</strong>
                            <span>{{ $staff?->coSoDaoTao?->tenCoSo ? $staff->coSoDaoTao->tenCoSo . ' - ' . $staff->coSoDaoTao->diaChiDayDu : 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Quy định áp dụng</h2>
                </div>
                <div class="profile-card-body">
                    @if ($profileRecord)
                        <div class="profile-kv" style="margin-bottom:16px">
                            <div class="profile-kv-item">
                                <strong>Mã hồ sơ</strong>
                                <span>{{ $profileRecord->maHoSo }}</span>
                            </div>
                            <div class="profile-kv-item">
                                <strong>Trạng thái hồ sơ</strong>
                                <span>{{ $profileRecord->isCompleted() ? 'Hoàn tất' : 'Nháp' }}</span>
                            </div>
                        </div>
                        <div class="profile-alert" style="margin-bottom:16px">
                            <strong>{{ $profileRecord->tieuDeMauSnapshot ?: 'Chưa gắn mẫu quy định' }}</strong>
                        </div>
                        <div class="profile-html">
                            {!! $profileRecord->noiDungQuyDinhSnapshot ?: '<p class="profile-empty">Chưa có snapshot quy định.</p>' !!}
                        </div>
                    @else
                        <div class="profile-empty">Chưa có hồ sơ nhân sự mở rộng.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="profile-stack">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Gói lương hiện hành</h2>
                </div>
                <div class="profile-card-body">
                    @if ($currentSalary)
                        <div class="profile-salary-item">
                            <div class="profile-salary-head">
                                <div>
                                    <strong>{{ $loaiLuongOptions[$currentSalary->loaiLuong] ?? $currentSalary->loaiLuong }}</strong>
                                    <div class="profile-salary-meta">
                                        Hiệu lực từ {{ optional($currentSalary->hieuLucTu)->format('d/m/Y') ?: 'N/A' }}
                                    </div>
                                </div>
                                <div style="font-size:1.2rem;font-weight:700;color:#0f172a">
                                    {{ number_format((float) $currentSalary->luongChinh, 0, ',', '.') }} VNĐ
                                </div>
                            </div>

                            @if ($currentSalary->chiTiets->isNotEmpty())
                                <div class="profile-doc-list" style="margin-top:14px">
                                    @foreach ($currentSalary->chiTiets as $chiTiet)
                                        <div class="profile-doc-item">
                                            <div class="profile-doc-head">
                                                <strong>{{ $chiTiet->tenKhoan }}</strong>
                                                <span>{{ number_format((float) $chiTiet->soTien, 0, ',', '.') }} VNĐ</span>
                                            </div>
                                            <div class="profile-doc-meta">
                                                {{ $loaiLuongChiTietOptions[$chiTiet->loai] ?? $chiTiet->loai }}
                                                @if ($chiTiet->ghiChu)
                                                    · {{ $chiTiet->ghiChu }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($currentSalary->ghiChu)
                                <div class="profile-doc-meta" style="margin-top:12px">{{ $currentSalary->ghiChu }}</div>
                            @endif
                        </div>
                    @else
                        <div class="profile-empty">Chưa có gói lương active.</div>
                    @endif

                    <form action="{{ route($routePrefix . '.salary.store', $record->taiKhoan) }}" method="POST" class="profile-mini-form" style="margin-top:18px">
                        @csrf
                        <div class="profile-mini-grid">
                            <div>
                                <label for="salary-loaiLuong">Loại lương mới</label>
                                <select id="salary-loaiLuong" name="loaiLuong">
                                    <option value="">Chọn loại lương</option>
                                    @foreach ($loaiLuongOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="salary-luongChinh">Lương chính</label>
                                <input type="number" id="salary-luongChinh" name="luongChinh" min="0" step="1000"
                                    placeholder="Ví dụ: 12000000">
                            </div>
                            <div>
                                <label for="salary-hieuLucTu">Hiệu lực từ</label>
                                <input type="date" id="salary-hieuLucTu" name="hieuLucTu" value="{{ now()->toDateString() }}">
                            </div>
                            <div>
                                <label for="salary-ghiChuLuong">Ghi chú</label>
                                <input type="text" id="salary-ghiChuLuong" name="ghiChuLuong"
                                    placeholder="Ví dụ: thay đổi sau đánh giá thử việc">
                            </div>
                        </div>

                        <button type="submit" class="profile-btn profile-btn-primary" style="width:max-content">
                            <i class="fas fa-wallet"></i> Cập nhật gói lương
                        </button>
                    </form>

                    @if ($lichSuLuong->isNotEmpty())
                        <div class="profile-salary-history" style="margin-top:18px">
                            @foreach ($lichSuLuong as $salaryPackage)
                                <div class="profile-salary-item">
                                    <div class="profile-salary-head">
                                        <strong>{{ $loaiLuongOptions[$salaryPackage->loaiLuong] ?? $salaryPackage->loaiLuong }}</strong>
                                        <span>{{ number_format((float) $salaryPackage->luongChinh, 0, ',', '.') }} VNĐ</span>
                                    </div>
                                    <div class="profile-salary-meta">
                                        {{ optional($salaryPackage->hieuLucTu)->format('d/m/Y') ?: 'N/A' }}
                                        @if ($salaryPackage->hieuLucDen)
                                            - {{ optional($salaryPackage->hieuLucDen)->format('d/m/Y') }}
                                        @else
                                            - Hiện tại
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Tài liệu đính kèm</h2>
                </div>
                <div class="profile-card-body">
                    <form action="{{ route($routePrefix . '.documents.store', $record->taiKhoan) }}" method="POST" enctype="multipart/form-data"
                        class="profile-mini-form">
                        @csrf
                        <div class="profile-mini-grid">
                            <div>
                                <label for="doc-loaiTaiLieu">Loại tài liệu</label>
                                <select id="doc-loaiTaiLieu" name="loaiTaiLieu">
                                    <option value="">Chọn loại tài liệu</option>
                                    @foreach ($loaiTaiLieuOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="doc-tenHienThi">Tên hiển thị</label>
                                <input type="text" id="doc-tenHienThi" name="tenHienThi" placeholder="Ví dụ: CV bản cập nhật">
                            </div>
                            <div style="grid-column:1/-1">
                                <label for="doc-tep">Tệp tải lên</label>
                                <input type="file" id="doc-tep" name="tep" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.xls,.xlsx">
                            </div>
                            <div style="grid-column:1/-1">
                                <label for="doc-ghiChu">Ghi chú</label>
                                <textarea id="doc-ghiChu" name="ghiChu" placeholder="Ví dụ: CV chính thức sau phỏng vấn."></textarea>
                            </div>
                        </div>
                        <button type="submit" class="profile-btn profile-btn-primary" style="width:max-content">
                            <i class="fas fa-upload"></i> Tải tài liệu
                        </button>
                    </form>

                    <div class="profile-doc-list" style="margin-top:18px">
                        @forelse ($taiLieuHoatDong as $taiLieu)
                            <div class="profile-doc-item">
                                <div class="profile-doc-head">
                                    <div>
                                        <strong>{{ $taiLieu->tenHienThi }}</strong>
                                        <div class="profile-doc-meta">
                                            {{ $loaiTaiLieuOptions[$taiLieu->loaiTaiLieu] ?? $taiLieu->loaiTaiLieu }}
                                            · v{{ $taiLieu->phienBan }}
                                            · {{ $taiLieu->kichThuocHienThi }}
                                        </div>
                                    </div>
                                    <div class="profile-actions">
                                        <a href="{{ route($routePrefix . '.documents.download', ['taiKhoan' => $record->taiKhoan, 'documentId' => $taiLieu->nhanSuTaiLieuId]) }}"
                                            class="profile-btn profile-btn-primary">
                                            <i class="fas fa-download"></i> Tải
                                        </a>
                                        <form action="{{ route($routePrefix . '.documents.archive', ['taiKhoan' => $record->taiKhoan, 'documentId' => $taiLieu->nhanSuTaiLieuId]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="profile-btn profile-btn-ghost" style="color:#0f172a;border-color:#cbd5e1;background:#f8fafc">
                                                <i class="fas fa-box-archive"></i> Lưu trữ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="profile-doc-meta">
                                    {{ $taiLieu->tenGoc }}
                                    @if ($taiLieu->ghiChu)
                                        · {{ $taiLieu->ghiChu }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="profile-empty">Chưa có tài liệu active.</div>
                        @endforelse
                    </div>

                    @if ($taiLieuDaLuuTru->isNotEmpty())
                        <div class="profile-alert" style="margin-top:18px">
                            Có {{ $taiLieuDaLuuTru->count() }} tài liệu đã lưu trữ để giữ lịch sử phiên bản.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
