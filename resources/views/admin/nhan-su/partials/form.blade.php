@php
    $isEdit = $formMode === 'edit';
    $isTeacher = (int) $role === \App\Models\Auth\TaiKhoan::ROLE_GIAO_VIEN;
    $routePrefix = $routePrefix ?? ($isTeacher ? 'admin.giao-vien' : 'admin.nhan-vien');
    $record = $record ?? null;
    $profile = $record?->hoSoNguoiDung;
    $staff = $record?->nhanSu;
    $policy = $record?->nhanSuHoSo;
    $currentTemplateId = old('nhanSuMauQuyDinhId', $selectedTemplateId ?? $policy?->nhanSuMauQuyDinhId);
    $contractValue = old('loaiHopDong', $staff?->loaiHopDong);
    $statusValue = old('trangThai', (string) ($record?->trangThai ?? 1));
    $salaryTypes = $loaiLuongOptions ?? [];
    $salaryDetailTypes = $loaiLuongChiTietOptions ?? [];
    $salaryDetailTypeValues = old('salary_details.type', ['']);
    $salaryDetailNameValues = old('salary_details.name', ['']);
    $salaryDetailAmountValues = old('salary_details.amount', ['']);
    $salaryDetailNoteValues = old('salary_details.note', ['']);
    $detailRows = max(count($salaryDetailTypeValues), count($salaryDetailNameValues), count($salaryDetailAmountValues), count($salaryDetailNoteValues), 1);
@endphp

<div class="staff-page">
    <div class="staff-header">
        <div>
            <h1>{{ $isEdit ? 'Cập nhật ' . mb_strtolower($roleLabel) : 'Tạo hồ sơ ' . mb_strtolower($roleLabel) }}</h1>
            <p>
                {{ $isEdit
                    ? 'Chỉnh sửa thông tin tài khoản, hồ sơ cá nhân và dữ liệu nhân sự.'
                    : 'Hoàn tất hồ sơ, gói lương ban đầu và mẫu quy định để bàn giao tài khoản ngay sau khi lưu.' }}
            </p>
        </div>

        <div class="staff-actions">
            <a href="{{ route($routePrefix . '.index') }}" class="staff-btn staff-btn-secondary">
                <i class="fas fa-arrow-left"></i> Danh sách
            </a>
            <button type="submit" class="staff-btn staff-btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Lưu cập nhật' : 'Tạo hồ sơ' }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="staff-error-list">
            <strong>Biểu mẫu còn lỗi, vui lòng kiểm tra lại.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2>Tài khoản và trạng thái</h2>
                <p>Thông tin đăng nhập nội bộ và trạng thái hoạt động trên hệ thống.</p>
            </div>
            @if ($isEdit)
                <span class="staff-badge">
                    <i class="fas fa-circle"></i>
                    {{ (int) $record->trangThai === 1 ? 'Đang hoạt động' : 'Đang khóa' }}
                </span>
            @endif
        </div>
        <div class="staff-card-body">
            <div class="staff-grid">
                <div class="staff-control">
                    <label class="staff-label" for="taiKhoan">
                        <span>Tên đăng nhập</span>
                        <span class="staff-hint">{{ $isEdit ? 'Mã hệ thống cố định' : 'Sinh sau khi lưu hồ sơ' }}</span>
                    </label>
                    <input type="text" id="taiKhoan" value="{{ $record?->taiKhoan ?: 'Sẽ được cấp sau khi lưu thành công' }}" readonly>
                    @unless ($isEdit)
                        <div class="staff-banner" style="margin-top:12px">
                            <strong>Không hiển thị mã giả trước khi tạo tài khoản</strong>
                            Username thật và mật khẩu tạm sẽ chỉ xuất hiện ở màn hồ sơ sau khi lưu thành công.
                        </div>
                    @endunless
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="email">
                        <span>Email <span class="staff-required">*</span></span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $record?->email) }}" placeholder="ten@company.vn">
                    @error('email')
                        <span class="staff-error">{{ $message }}</span>
                    @enderror
                </div>

                @if ($isEdit)
                    <div class="staff-control">
                        <label class="staff-label" for="trangThai">
                            <span>Trạng thái tài khoản <span class="staff-required">*</span></span>
                        </label>
                        <select id="trangThai" name="trangThai">
                            <option value="1" {{ (string) $statusValue === '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ (string) $statusValue === '0' ? 'selected' : '' }}>Bị khóa</option>
                        </select>
                        @error('trangThai')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label">
                            <span>Lần đăng nhập gần nhất</span>
                            <span class="staff-hint">Tham khảo</span>
                        </label>
                        <input type="text" readonly
                            value="{{ $record?->lastLogin ? $record->lastLogin->format('d/m/Y H:i') : 'Chưa có dữ liệu' }}">
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="matKhau">
                            <span>Mật khẩu mới</span>
                            <span class="staff-hint">Để trống nếu không đổi</span>
                        </label>
                        <input type="password" id="matKhau" name="matKhau" placeholder="Tối thiểu 8 ký tự">
                        @error('matKhau')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="matKhau_confirmation">
                            <span>Xác nhận mật khẩu mới</span>
                        </label>
                        <input type="password" id="matKhau_confirmation" name="matKhau_confirmation"
                            placeholder="Nhập lại mật khẩu mới">
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2>Thông tin cá nhân</h2>
                <p>Thông tin nhận diện, liên hệ và hồ sơ nền của {{ mb_strtolower($roleLabel) }}.</p>
            </div>
        </div>
        <div class="staff-card-body">
            <div class="staff-grid">
                <div class="staff-control staff-field-full">
                    <label class="staff-label" for="anhDaiDien">
                        <span>Ảnh đại diện</span>
                        <span class="staff-hint">Định dạng JPG, PNG, WEBP, tối đa 2MB. Nên dùng ảnh tỉ lệ 1:1.</span>
                    </label>
                    <x-upload.image
                        id="avatar-upload"
                        name="anhDaiDien"
                        title="Tải ảnh đại diện"
                        description="Kéo thả ảnh hoặc click để chọn"
                        chooseLabel="Chọn ảnh"
                        mode="deferred"
                        :standalone="false"
                        :previewUrl="$record ? $record->getAvatarUrl() : ''"
                        previewShape="circle"
                        accept="image/jpeg,image/png,image/webp"
                        :allowedTypes="['image/jpeg', 'image/png', 'image/webp']"
                        allowedExtensionsLabel="JPG, PNG, WebP"
                        maxSize="2097152"
                    />
                </div>

                <div class="staff-control staff-field-full">
                    <label class="staff-label" for="hoTen">
                        <span>Họ và tên <span class="staff-required">*</span></span>
                    </label>
                    <input type="text" id="hoTen" name="hoTen" value="{{ old('hoTen', $profile?->hoTen) }}"
                        placeholder="Ví dụ: Nguyễn Văn A">
                    @error('hoTen')
                        <span class="staff-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="ngaySinh"><span>Ngày sinh</span></label>
                    <input type="date" id="ngaySinh" name="ngaySinh"
                        value="{{ old('ngaySinh', optional($profile?->ngaySinh)->format('Y-m-d')) }}" max="{{ now()->toDateString() }}">
                    @error('ngaySinh')
                        <span class="staff-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="gioiTinh"><span>Giới tính</span></label>
                    <select id="gioiTinh" name="gioiTinh">
                        <option value="">Chọn giới tính</option>
                        <option value="1" {{ (string) old('gioiTinh', $profile?->gioiTinh) === '1' ? 'selected' : '' }}>Nam</option>
                        <option value="0" {{ (string) old('gioiTinh', $profile?->gioiTinh) === '0' ? 'selected' : '' }}>Nữ</option>
                        <option value="2" {{ (string) old('gioiTinh', $profile?->gioiTinh) === '2' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="soDienThoai"><span>Số điện thoại</span></label>
                    <input type="text" id="soDienThoai" name="soDienThoai"
                        value="{{ old('soDienThoai', $profile?->soDienThoai) }}" placeholder="0901234567">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="zalo"><span>Zalo</span></label>
                    <input type="text" id="zalo" name="zalo" value="{{ old('zalo', $profile?->zalo) }}"
                        placeholder="Số điện thoại Zalo">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="cccd"><span>CCCD / CMND</span></label>
                    <input type="text" id="cccd" name="cccd" value="{{ old('cccd', $profile?->cccd) }}"
                        placeholder="12 hoặc 9 số">
                    @error('cccd')
                        <span class="staff-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="staff-control staff-field-full">
                    <label class="staff-label" for="diaChi"><span>Địa chỉ</span></label>
                    <input type="text" id="diaChi" name="diaChi" value="{{ old('diaChi', $profile?->diaChi) }}"
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố">
                </div>
            </div>
        </div>
    </div>

    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2>Thông tin nhân sự</h2>
                <p>Thông tin nghiệp vụ, loại hợp đồng và đơn vị làm việc.</p>
            </div>
        </div>
        <div class="staff-card-body">
            <div class="staff-grid">
                <div class="staff-control">
                    <label class="staff-label" for="chucVu"><span>Chức vụ</span></label>
                    <input type="text" id="chucVu" name="chucVu" list="staff-position-options"
                        value="{{ old('chucVu', $staff?->chucVu) }}" placeholder="Ví dụ: Giáo viên chính">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="chuyenMon"><span>Chuyên môn</span></label>
                    <input type="text" id="chuyenMon" name="chuyenMon" list="staff-specialization-options"
                        value="{{ old('chuyenMon', $staff?->chuyenMon) }}" placeholder="Ví dụ: Tiếng Anh">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="bangCap"><span>Bằng cấp</span></label>
                    <input type="text" id="bangCap" name="bangCap" list="staff-degree-options"
                        value="{{ old('bangCap', $staff?->bangCap) }}" placeholder="Ví dụ: Cử nhân">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="hocVi"><span>Học vị / Chứng chỉ</span></label>
                    <input type="text" id="hocVi" name="hocVi" value="{{ old('hocVi', $staff?->hocVi) }}"
                        placeholder="Ví dụ: IELTS 8.0, MOS, N1">
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="loaiHopDong">
                        <span>Loại hợp đồng <span class="staff-required">*</span></span>
                    </label>
                    <select id="loaiHopDong" name="loaiHopDong">
                        <option value="">Chọn loại hợp đồng</option>
                        @foreach ($loaiHopDongOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string) $contractValue === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('loaiHopDong')
                        <span class="staff-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="staff-control">
                    <label class="staff-label" for="ngayVaoLam"><span>Ngày vào làm</span></label>
                    <input type="date" id="ngayVaoLam" name="ngayVaoLam"
                        value="{{ old('ngayVaoLam', optional($staff?->ngayVaoLam)->format('Y-m-d') ?: now()->toDateString()) }}">
                </div>

                @if ($isTeacher)
                    <input type="hidden" name="coSoId" id="coSoId" value="{{ old('coSoId', $selectedCoSoId) }}">
                    <div class="staff-control">
                        <label class="staff-label" for="selectedTinhThanhId">
                            <span>Tỉnh / Thành phố <span class="staff-required">*</span></span>
                        </label>
                        <select id="selectedTinhThanhId" name="selectedTinhThanhId" data-role="province">
                            <option value="">Chọn tỉnh / thành</option>
                            @foreach ($tinhThanhs as $tinhThanh)
                                <option value="{{ $tinhThanh->tinhThanhId }}"
                                    {{ (string) ($selectedTinhThanhId ?? '') === (string) $tinhThanh->tinhThanhId ? 'selected' : '' }}>
                                    {{ $tinhThanh->tenTinhThanh }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="selectedPhuongXa">
                            <span>Phường / Xã <span class="staff-required">*</span></span>
                        </label>
                        <select id="selectedPhuongXa" name="selectedPhuongXa" data-role="ward">
                            <option value="">Chọn phường / xã</option>
                        </select>
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="selectedCoSo">
                            <span>Cơ sở làm việc <span class="staff-required">*</span></span>
                        </label>
                        <select id="selectedCoSo" data-role="branch">
                            <option value="">Chọn cơ sở</option>
                        </select>
                        @error('coSoId')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control staff-field-full">
                        <div class="staff-cascade-preview" id="staff-cascade-preview">
                            <strong>Cơ sở đã chọn:</strong>
                            <div id="staff-cascade-preview-text">Chưa chọn cơ sở làm việc.</div>
                        </div>
                    </div>
                @else
                    <div class="staff-control staff-field-full">
                        <label class="staff-label" for="coSoId">
                            <span>Cơ sở làm việc <span class="staff-required">*</span></span>
                        </label>
                        <select id="coSoId" name="coSoId">
                            <option value="">Chọn cơ sở</option>
                            @foreach ($coSos as $coSo)
                                <option value="{{ $coSo->coSoId }}"
                                    {{ (string) old('coSoId', $selectedCoSoId) === (string) $coSo->coSoId ? 'selected' : '' }}>
                                    {{ $coSo->tenCoSo }} - {{ $coSo->diaChiDayDu }}
                                </option>
                            @endforeach
                        </select>
                        @error('coSoId')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                @if ($isEdit && $policy?->tieuDeMauSnapshot)
                    <div class="staff-control staff-field-full">
                        <label class="staff-label"><span>Mẫu quy định đang áp dụng</span></label>
                        <input type="text" readonly
                            value="{{ $policy->tieuDeMauSnapshot }} @if($policy->nhanSuMauQuyDinh?->phienBan) - v{{ $policy->nhanSuMauQuyDinh->phienBan }} @endif">
                    </div>
                @endif
            </div>
        </div>
    </div>

    @unless ($isEdit)
        <div class="staff-card">
            <div class="staff-card-header">
                <div>
                    <h2>Quy định áp dụng và gói lương ban đầu</h2>
                    <p>Chốt mẫu quy định, gói lương đầu tiên và tài liệu CV ngay khi tiếp nhận nhân sự.</p>
                </div>
                <a href="{{ route('admin.nhan-su.mau-quy-dinh.index') }}" class="staff-btn staff-btn-muted">
                    <i class="fas fa-folder-open"></i> Quản lý mẫu quy định
                </a>
            </div>
            <div class="staff-card-body">
                <div class="staff-grid">
                    <div class="staff-control staff-field-full">
                        <label class="staff-label" for="nhanSuMauQuyDinhId">
                            <span>Mẫu quy định <span class="staff-required">*</span></span>
                        </label>
                        <select id="nhanSuMauQuyDinhId" name="nhanSuMauQuyDinhId">
                            <option value="">Chọn mẫu quy định</option>
                            @foreach ($mauQuyDinhs as $mau)
                                <option value="{{ $mau->nhanSuMauQuyDinhId }}"
                                    {{ (string) $currentTemplateId === (string) $mau->nhanSuMauQuyDinhId ? 'selected' : '' }}>
                                    {{ $mau->maMau }} - {{ $mau->tieuDe }} (v{{ $mau->phienBan }})
                                </option>
                            @endforeach
                        </select>
                        @error('nhanSuMauQuyDinhId')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="loaiLuong">
                            <span>Loại lương <span class="staff-required">*</span></span>
                        </label>
                        <select id="loaiLuong" name="loaiLuong">
                            <option value="">Chọn loại lương</option>
                            @foreach ($salaryTypes as $value => $label)
                                <option value="{{ $value }}" {{ (string) old('loaiLuong') === (string) $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('loaiLuong')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="luongChinh">
                            <span>Lương chính <span class="staff-required">*</span></span>
                        </label>
                        <input type="number" id="luongChinh" name="luongChinh" min="0" step="1000"
                            value="{{ old('luongChinh') }}" placeholder="Ví dụ: 12000000">
                        @error('luongChinh')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="hieuLucTu">
                            <span>Hiệu lực từ <span class="staff-required">*</span></span>
                        </label>
                        <input type="date" id="hieuLucTu" name="hieuLucTu" value="{{ $defaultSalaryStartDate }}">
                        @error('hieuLucTu')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control staff-field-full">
                        <label class="staff-label" for="ghiChuLuong"><span>Ghi chú gói lương</span></label>
                        <textarea id="ghiChuLuong" name="ghiChuLuong"
                            placeholder="Ví dụ: Áp dụng từ ngày nhận việc, đã gồm KPI đầu kỳ.">{{ old('ghiChuLuong') }}</textarea>
                    </div>

                    <div class="staff-control staff-field-full">
                        <label class="staff-label">
                            <span>Chi tiết phụ cấp / khấu trừ tham chiếu</span>
                            <span class="staff-hint">Có thể để trống nếu chưa chốt chi tiết</span>
                        </label>

                        <div class="staff-detail-lines" data-detail-lines>
                            @for ($index = 0; $index < $detailRows; $index++)
                                <div class="staff-detail-row">
                                    <select name="salary_details[type][]">
                                        <option value="">Loại khoản</option>
                                        @foreach ($salaryDetailTypes as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ (string) ($salaryDetailTypeValues[$index] ?? '') === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="salary_details[name][]" value="{{ $salaryDetailNameValues[$index] ?? '' }}"
                                        placeholder="Tên khoản">
                                    <input type="number" name="salary_details[amount][]" min="0" step="1000"
                                        value="{{ $salaryDetailAmountValues[$index] ?? '' }}" placeholder="Số tiền">
                                    <input type="text" name="salary_details[note][]" value="{{ $salaryDetailNoteValues[$index] ?? '' }}"
                                        placeholder="Ghi chú">
                                    <button type="button" class="staff-btn staff-btn-muted" data-remove-detail>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endfor
                        </div>

                        <button type="button" class="staff-btn staff-btn-muted" data-add-detail style="margin-top:12px">
                            <i class="fas fa-plus"></i> Thêm dòng chi tiết
                        </button>
                        @error('salary_details')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="cvXinViec">
                            <span>CV xin việc</span>
                            <span class="staff-hint">PDF/DOC/DOCX, tối đa 15MB</span>
                        </label>
                        <input type="file" id="cvXinViec" name="cvXinViec" accept=".pdf,.doc,.docx">
                        @error('cvXinViec')
                            <span class="staff-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="staff-control">
                        <label class="staff-label" for="cvTenHienThi"><span>Tên hiển thị CV</span></label>
                        <input type="text" id="cvTenHienThi" name="cvTenHienThi" value="{{ old('cvTenHienThi') }}"
                            placeholder="Ví dụ: CV ứng tuyển 2026">
                    </div>

                    <div class="staff-control staff-field-full">
                        <label class="staff-label" for="cvGhiChu"><span>Ghi chú CV</span></label>
                        <textarea id="cvGhiChu" name="cvGhiChu"
                            placeholder="Ví dụ: bản đầy đủ có chứng chỉ đính kèm.">{{ old('cvGhiChu') }}</textarea>
                    </div>

                    <div class="staff-control staff-field-full">
                        <label class="staff-label" for="ghiChuHoSo"><span>Ghi chú hồ sơ nhân sự</span></label>
                        <textarea id="ghiChuHoSo" name="ghiChuHoSo"
                            placeholder="Ghi chú bổ sung cho hồ sơ, không hiển thị trong phiếu bàn giao.">{{ old('ghiChuHoSo') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2>Ghi chú nội bộ</h2>
                <p>Thông tin nội bộ phục vụ vận hành, không dùng làm thông tin đăng nhập.</p>
            </div>
        </div>
        <div class="staff-card-body">
            <div class="staff-control">
                <label class="staff-label" for="ghiChu"><span>Ghi chú</span></label>
                <textarea id="ghiChu" name="ghiChu"
                    placeholder="Ví dụ: ưu tiên cơ sở Hồ Chí Minh, theo dõi thêm chứng chỉ gốc.">{{ old('ghiChu', $profile?->ghiChu) }}</textarea>
            </div>
        </div>
    </div>

    <div class="staff-footer">
        <div class="staff-footer-meta">
            {{ $isEdit ? 'Username không cho sửa. Đổi mật khẩu và khóa tài khoản sẽ tự xoay remember token khi cần.' : 'Sau khi tạo xong, hệ thống chuyển sang hồ sơ chi tiết để bàn giao tài khoản và in hồ sơ.' }}
        </div>

        <div class="staff-actions">
            @if ($isEdit && $record)
                <a href="{{ route($routePrefix . '.show', $record->taiKhoan) }}" class="staff-btn staff-btn-muted">
                    <i class="fas fa-id-card"></i> Xem hồ sơ
                </a>
            @endif
            <button type="submit" class="staff-btn staff-btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Lưu cập nhật' : 'Tạo hồ sơ' }}
            </button>
        </div>
    </div>
</div>

<datalist id="staff-position-options">
    @if ($isTeacher)
        <option value="Giáo viên">
        <option value="Giáo viên chính">
        <option value="Trưởng bộ môn">
        <option value="Phó trưởng bộ môn">
    @else
        <option value="Nhân viên">
        <option value="Quản lý">
        <option value="Trưởng phòng">
        <option value="Phó phòng">
    @endif
</datalist>

<datalist id="staff-specialization-options">
    @if ($isTeacher)
        <option value="Tiếng Anh">
        <option value="Tiếng Nhật">
        <option value="Tiếng Hàn">
        <option value="Tiếng Trung">
        <option value="Tiếng Pháp">
    @else
        <option value="Hành chính nhân sự">
        <option value="Kế toán">
        <option value="Marketing">
        <option value="Tư vấn tuyển sinh">
        <option value="IT">
    @endif
</datalist>

<datalist id="staff-degree-options">
    <option value="Trung cấp">
    <option value="Cao đẳng">
    <option value="Cử nhân">
    <option value="Thạc sĩ">
    <option value="Tiến sĩ">
</datalist>
