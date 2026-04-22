@props([
    'portalLabel',
    'portalShortLabel',
    'portalHomeRoute',
    'portalAccent' => 'admin',
    'sections' => [],
])

@php
    $currentUser = auth()->user();
    $initial = strtoupper(mb_substr($currentUser?->taiKhoan ?? 'U', 0, 1));
@endphp

<aside class="sidebar" id="sidebar" data-portal="{{ $portalAccent }}">
    <div class="sidebar-backdrop-glow"></div>

    <a href="{{ route($portalHomeRoute) }}" class="sidebar-brand">
        <span class="sidebar-brand-mark">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name', 'Five Genius') }}">
        </span>
        <span class="sidebar-brand-copy">
            <strong>{{ config('app.name', 'Five Genius') }}</strong>
            <small>{{ $portalLabel }}</small>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Điều hướng {{ $portalLabel }}">
        @foreach ($sections as $section)
            <section class="sidebar-cluster">
                @if (!empty($section['title']))
                    <div class="sidebar-section">{{ $section['title'] }}</div>
                @endif

                @foreach ($section['groups'] ?? [] as $group)
                    @php
                        $groupPatterns = $group['active'] ?? [];
                        $groupOpen = !empty($group['open']) || (!empty($groupPatterns) && request()->routeIs(...$groupPatterns));
                    @endphp
                    <div class="nav-group {{ $groupOpen ? 'open' : '' }}">
                        <button type="button" class="nav-group-header" aria-expanded="{{ $groupOpen ? 'true' : 'false' }}">
                            <span class="nav-group-icon">
                                <i class="{{ $group['icon'] }}"></i>
                            </span>
                            <span class="nav-group-copy">
                                <strong>{{ $group['label'] }}</strong>
                                @if (!empty($group['hint']))
                                    <small>{{ $group['hint'] }}</small>
                                @endif
                            </span>
                            <i class="fas fa-chevron-right nav-group-arrow"></i>
                        </button>

                        <div class="nav-sub">
                            @foreach ($group['items'] as $item)
                                @php
                                    $itemPatterns = $item['active'] ?? [];
                                    $isActive = !empty($itemPatterns) && request()->routeIs(...$itemPatterns);
                                @endphp
                                <a href="{{ route($item['route']) }}" class="nav-sub-item {{ $isActive ? 'active' : '' }}">
                                    <span class="nav-sub-icon">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </span>
                                    <span class="nav-sub-copy">
                                        <span>{{ $item['label'] }}</span>
                                        @if (!empty($item['hint']))
                                            <small>{{ $item['hint'] }}</small>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ $initial }}</div>
            <div class="user-info">
                <div class="user-name">{{ $currentUser?->hoSoNguoiDung?->hoTen ?? $currentUser?->taiKhoan }}</div>
                <div class="user-role">{{ $currentUser?->getRoleLabel() }}</div>
            </div>
            <form id="internal-logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="btn-logout" id="btn-logout-internal" title="Đăng xuất">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
