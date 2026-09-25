@props(['route' => null, 'icon' => 'bi-circle', 'title' => '', 'active' => false, 'url' => '#'])

@php
    $href = $route ? route($route) : $url;
    $routeBase = $route ? explode('.', $route)[0] : '';
    $isActive = $active || ($route && (
        request()->routeIs($route) ||
        request()->routeIs($route . '.*') ||
        ($routeBase && request()->routeIs($routeBase . '.*'))
    ));
@endphp

<a href="{{ $href }}" class="sidebar-nav__item {{ $isActive ? 'active' : '' }}"
    data-bs-title="{{ $title }}">
    <i class="bi {{ $icon }}"></i> <span>{{ $title }}</span>
</a>
