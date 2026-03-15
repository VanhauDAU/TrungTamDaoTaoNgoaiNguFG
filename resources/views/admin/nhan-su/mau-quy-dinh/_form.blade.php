<div style="display:grid;gap:18px">
    <div class="card" style="border-radius:18px;border:1px solid #e5e7eb">
        <div class="card-body" style="padding:24px;display:grid;gap:18px">
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px">
                <div>
                    <label for="maMau" class="form-label">Mã mẫu</label>
                    <input id="maMau" name="maMau" class="form-control" value="{{ old('maMau', $template->maMau) }}"
                        placeholder="Ví dụ: QD-GV-001">
                    @error('maMau')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="tieuDe" class="form-label">Tiêu đề</label>
                    <input id="tieuDe" name="tieuDe" class="form-control"
                        value="{{ old('tieuDe', $template->tieuDe) }}" placeholder="Tên mẫu quy định">
                    @error('tieuDe')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="phamViApDung" class="form-label">Phạm vi áp dụng</label>
                    <select id="phamViApDung" name="phamViApDung" class="form-control">
                        @foreach ($phamViOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('phamViApDung', $template->phamViApDung) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="loaiHopDongApDung" class="form-label">Loại hợp đồng áp dụng</label>
                    <select id="loaiHopDongApDung" name="loaiHopDongApDung" class="form-control">
                        @foreach ($loaiHopDongOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('loaiHopDongApDung', $template->loaiHopDongApDung) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="phienBan" class="form-label">Phiên bản</label>
                    <input type="number" min="1" id="phienBan" name="phienBan" class="form-control"
                        value="{{ old('phienBan', $template->phienBan ?: 1) }}">
                </div>
                <div>
                    <label for="trangThai" class="form-label">Trạng thái</label>
                    <select id="trangThai" name="trangThai" class="form-control">
                        <option value="1" {{ (string) old('trangThai', $template->trangThai ?? 1) === '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ (string) old('trangThai', $template->trangThai ?? 1) === '0' ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="noiDung" class="form-label">Nội dung mẫu</label>
                <textarea id="noiDung" name="noiDung" class="form-control" rows="14"
                    placeholder="Có thể nhập HTML để render trong hồ sơ và PDF.">{{ old('noiDung', $template->noiDung) }}</textarea>
                @error('noiDung')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <a href="{{ route('admin.nhan-su.mau-quy-dinh.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $formMode === 'edit' ? 'Lưu thay đổi' : 'Tạo mẫu' }}
        </button>
    </div>
</div>
