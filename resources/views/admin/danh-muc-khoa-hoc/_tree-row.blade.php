{{--
    Partial: _tree-row.blade.php
    Variables: $node, $depth, $parentId (DanhMucId của cha trực tiếp)
--}}
@php
    $indent = $depth * 1.4;
    $hasKids = $node->childrenRecursive->isNotEmpty();
    $soKhoaHoc = $node->khoaHocs->count();
    $soKon = $node->childrenRecursive->count();
@endphp

{{-- data-parent = cha TRỰC TIẾP; data-tree-id = ID của chính nó --}}
<tr class="dm-row-child {{ $hasKids ? 'has-children' : '' }}" data-parent="{{ $parentId }}"
    data-tree-id="{{ $node->danhMucId }}">

    {{-- Toggle --}}
    <td style="text-align:center">
        @if ($hasKids)
            <button type="button" class="dm-toggle-tree" onclick="toggleChildren({{ $node->danhMucId }}, this)"
                title="Thu gọn/Mở rộng">
                <i class="fas fa-chevron-down"></i>
            </button>
        @else
            <span style="color:#d1d5db;font-size:.8rem"><i class="fas fa-minus"></i></span>
        @endif
    </td>

    {{-- Tên --}}
    <td>
        <div style="display:flex;align-items:center;gap:.5rem;padding-left:{{ $indent }}rem">
            <span class="dm-tree-connector" style="color:#cbd5e1;font-size:.8rem">└─</span>
            <span class="dm-node-icon {{ $hasKids ? 'dm-node-root' : 'dm-node-child' }}">
                <i class="fas fa-folder{{ $hasKids ? '' : '-open' }}"></i>
            </span>
            <span style="font-weight:{{ $depth === 1 ? '700' : '500' }};color:#374151">
                {{ $node->tenDanhMuc }}
            </span>
            @if ($hasKids)
                <span class="dm-children-count">{{ $soKon }} con</span>
            @endif
        </div>
    </td>

    {{-- Slug --}}
    <td style="color:#0f766e;font-family:monospace;font-size:.83rem;padding-left:{{ $indent }}rem">
        {{ $node->slug }}
    </td>

    {{-- Mô tả --}}
    <td style="color:#64748b;max-width:220px">{{ Str::limit($node->moTa, 50) ?: '—' }}</td>

    {{-- Khóa học --}}
    <td style="text-align:center">
        <span class="dm-count-badge">{{ $soKhoaHoc }} khóa</span>
    </td>

    {{-- Trạng thái --}}
    <td style="text-align:center">
        @if ($node->trangThai)
            <span class="dm-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Hoạt động</span>
        @else
            <span class="dm-badge-inactive"><i class="fas fa-circle" style="font-size:.4em"></i> Ngừng</span>
        @endif
    </td>

    {{-- Hành động --}}
    <td style="text-align:center">
        <div class="dm-actions" style="justify-content:center">
            <a href="{{ route('admin.danh-muc-khoa-hoc.edit', $node->slug) }}" class="dm-btn-action dm-btn-edit"
                title="Chỉnh sửa">
                <i class="fas fa-pen"></i>
            </a>
            <button type="button" class="dm-btn-action dm-btn-del" title="Xóa"
                onclick="confirmDeleteDM({{ $node->danhMucId }}, '{{ addslashes($node->tenDanhMuc) }}', {{ $soKhoaHoc }}, {{ $soKon }})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

{{-- Đệ quy: truyền ID của $node làm $parentId cho children --}}
@foreach ($node->childrenRecursive as $child)
    @include('admin.danh-muc-khoa-hoc._tree-row', [
        'node' => $child,
        'depth' => $depth + 1,
        'parentId' => $node->danhMucId,
    ])
@endforeach
