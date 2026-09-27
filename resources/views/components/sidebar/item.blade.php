@props(['route' => null, 'icon' => 'bi-circle', 'title' => '', 'active' => null, 'url' => '#', 'patterns' => []])

@php
    $href = $route ? route($route) : $url;
    
    if ($active !== null) {
        $isActive = (bool) $active;
    } elseif (!empty($patterns)) {
        $isActive = false;
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                $isActive = true;
                break;
            }
        }
    } elseif ($route) {
        if (str_ends_with($route, '.index')) {
            $resource = substr($route, 0, -6);
            $isActive = request()->routeIs($route) || request()->routeIs($resource . '.*');
        } else {
            $isActive = request()->routeIs($route) || request()->routeIs($route . '.*');
        }
    } else {
        $isActive = false;
    }
@endphp

<a href="{{ $href }}" class="sidebar-nav__item {{ $isActive ? 'active' : '' }}"
    data-bs-title="{{ $title }}">
    <i class="bi {{ $icon }}"></i> <span>{{ $title }}</span>
</a>
