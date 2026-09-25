@props(['route' => null, 'icon' => 'bi-circle small', 'title' => '', 'active' => false, 'url' => '#'])

@php
    $href = $route ? route($route) : $url;
    $routeBase = $route ? explode('.', $route)[0] : '';
    $isActive = $active || ($route && (
        request()->routeIs($route) ||
        request()->routeIs($route . '.*') ||
        ($routeBase && request()->routeIs($routeBase . '.*'))
    ));
@endphp

<a href="{{ $href }}" class="sidebar-nav__item py-2 {{ $isActive ? 'active' : 'text-muted' }}" data-bs-title="{{ $title }}"
    style="font-size: 0.9rem;">
    <i class="bi {{ $icon }}" style="font-size: 10px;"></i>
    <span>{{ $title }}</span>
</a>
