{{--
    Partial: _cat-tree.blade.php
    Đệ quy đa cấp cho sidebar danh mục khóa học.
    Variables: $node, $depth (int, bắt đầu từ 1), $activeSlug, $activeIds (array), $searchQ, $sortBy
--}}
@php
    $isActive = $node->slug === $activeSlug;
    $isOpen = in_array($node->danhMucId, $activeIds ?? []);
    $hasKids = $node->childrenRecursive->isNotEmpty();
    $linkParams = array_filter([
        'category' => $node->slug,
        'q' => $searchQ ?: null,
        'sort' => $sortBy && $sortBy !== 'newest' ? $sortBy : null,
    ]);
    // Padding tăng theo depth (rem)
    $pl = $depth * 1.0 + 1.1;
@endphp

<div class="cl-cat-group" id="cg-{{ $node->danhMucId }}" style="border-bottom:none">
    <div class="cl-cat-root-row" style="border-bottom:none">
        <a href="{{ route('home.courses.index', $linkParams) }}"
            class="{{ $depth === 1 ? 'cl-cat-child-link' : 'cl-cat-grandchild-link' }} {{ $isActive ? 'active' : '' }}"
            style="padding-left:{{ $pl }}rem">
            {{ $node->tenDanhMuc }}
        </a>
        @if ($hasKids)
            <button type="button" class="cl-cat-toggle {{ $isOpen ? 'open' : '' }}"
                onclick="toggleCat({{ $node->danhMucId }}, this)">
                <i class="fas fa-chevron-down"></i>
            </button>
        @endif
    </div>

    @if ($hasKids)
        <div class="cl-cat-children" id="cc-{{ $node->danhMucId }}" style="{{ $isOpen ? '' : 'display:none' }}">
            @foreach ($node->childrenRecursive as $grandchild)
                @include('clients.khoa-hoc._cat-tree', [
                    'node' => $grandchild,
                    'depth' => $depth + 1,
                    'activeSlug' => $activeSlug,
                    'activeIds' => $activeIds,
                    'searchQ' => $searchQ,
                    'sortBy' => $sortBy,
                ])
            @endforeach
        </div>
    @endif
</div>
