@extends('layouts.admin')

@section('title', 'Chỉnh sửa Thông Báo')
@section('page-title', 'Chỉnh sửa Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Chỉnh sửa')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2" style="max-width:780px; margin:auto;">

        {{-- Thông báo về giới hạn chỉnh sửa --}}
        <div class="locked-notice">
            <i class="fas fa-lock"></i>
            Đối tượng nhận đã được ghi nhận khi gửi và không thể thay đổi.
            Chỉ có thể chỉnh sửa nội dung và cài đặt hiển thị.
        </div>

        <form method="POST" action="{{ route('admin.thong-bao.update', $thongBao->thongBaoId) }}">
            @csrf @method('PUT')

            <div class="nb-card">
                <div class="nb-card-title">
                    <div class="nb-icon-tag"><i class="fas fa-pen"></i></div>
                    Nội dung thông báo
                </div>

                {{-- Tiêu đề --}}
                <div class="nb-form-group">
                    <label class="nb-form-label">Tiêu đề <span class="req">*</span></label>
                    <input type="text" name="tieuDe" class="nb-input" value="{{ old('tieuDe', $thongBao->tieuDe) }}"
                        required>
                </div>

                {{-- Nội dung (Quill) --}}
                <div class="nb-form-group">
                    <label class="nb-form-label">Nội dung <span class="req">*</span></label>
                    <div id="quillEditor" style="min-height:160px; border-radius:10px; background:#fff;"></div>
                    <textarea name="noiDung" id="noiDungHidden" style="display:none;">{{ old('noiDung', $thongBao->noiDung) }}</textarea>
                </div>

                {{-- Loại & Ưu tiên --}}
                <div class="nb-grid-2">
                    <div class="nb-form-group mb-0">
                        <label class="nb-form-label">Loại thông báo</label>
                        <select name="loaiGui" class="nb-select">
                            @foreach (App\Models\Interaction\ThongBao::loaiLabels() as $k => $v)
                                <option value="{{ $k }}"
                                    {{ old('loaiGui', $thongBao->loaiGui) == $k ? 'selected' : '' }}>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nb-form-group mb-0">
                        <label class="nb-form-label">Mức ưu tiên</label>
                        <select name="uuTien" class="nb-select">
                            @foreach (App\Models\Interaction\ThongBao::uuTienLabels() as $k => $v)
                                <option value="{{ $k }}"
                                    {{ old('uuTien', $thongBao->uuTien) == $k ? 'selected' : '' }}>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Ghim --}}
                <div class="nb-form-group" style="margin-top:1.25rem;">
                    <label class="nb-toggle-pin">
                        <input type="checkbox" name="ghim" value="1"
                            {{ old('ghim', $thongBao->ghim) ? 'checked' : '' }}>
                        <i class="fas fa-thumbtack"></i> Ghim thông báo này lên đầu danh sách
                    </label>
                </div>
            </div>

            {{-- Nav --}}
            <div style="display:flex; gap:1rem; align-items:center;">
                <a href="{{ route('admin.thong-bao.show', $thongBao->thongBaoId) }}" class="nb-btn nb-btn-secondary">
                    <i class="fas fa-times"></i> Huỷ
                </a>
                <div class="nb-spacer"></div>
                <button type="submit" class="nb-btn nb-btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="{{ asset('assets/admin/js/pages/thong-bao/edit.js') }}"></script>
@endsection
