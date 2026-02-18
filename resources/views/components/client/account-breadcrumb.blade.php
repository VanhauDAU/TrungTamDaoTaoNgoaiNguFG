{{-- 
    Breadcrumb Component
    Props:
    - $items: Array of breadcrumb items
      Example: [
          ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
          ['label' => 'Tài khoản', 'url' => route('home.student.index')],
          ['label' => 'Thông tin cá nhân'] // Last item without URL (current page)
      ]
--}}

@props(['items' => []])

@if (count($items) > 0)
    <nav aria-label="breadcrumb" class="account-breadcrumb-wrapper">
        <ol class="account-breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            @foreach ($items as $index => $item)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">

                    @if (!$loop->last && isset($item['url']))
                        <a href="{{ $item['url'] }}" itemprop="item" class="breadcrumb-link">
                            @if (isset($item['icon']) && $index === 0)
                                <i class="{{ $item['icon'] }} me-1"></i>
                            @endif
                            <span itemprop="name">{{ $item['label'] }}</span>
                        </a>
                    @else
                        @if (isset($item['icon']) && $index === 0)
                            <i class="{{ $item['icon'] }} me-1"></i>
                        @endif
                        <span itemprop="name">{{ $item['label'] }}</span>
                    @endif

                    <meta itemprop="position" content="{{ $index + 1 }}" />
                </li>
            @endforeach
        </ol>
    </nav>
@endif
