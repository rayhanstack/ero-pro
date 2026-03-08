@props(['route' => null, 'icon' => 'bi-circle', 'title' => '', 'active' => false, 'url' => '#'])

@php
    $href = $route ? route($route) : $url;
    $isActive = $active || ($route && request()->routeIs($route));
@endphp

<a href="{{ $href }}" class="sidebar-nav__item {{ $isActive ? 'active' : '' }}"
    data-bs-title="{{ $title }}">
    <i class="bi {{ $icon }}"></i> <span>{{ $title }}</span>
</a>
