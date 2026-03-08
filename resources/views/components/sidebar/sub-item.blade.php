@props(['route' => null, 'icon' => 'bi-circle small', 'title' => '', 'active' => false, 'url' => '#'])

@php
    $href = $route ? route($route) : $url;
    $isActive = $active || ($route && request()->routeIs($route));
@endphp

<a href="{{ $href }}" class="sidebar-nav__item py-2 text-muted {{ $isActive ? 'active' : '' }}" data-bs-title="{{ $title }}"
    style="font-size: 0.9rem;">
    <i class="bi {{ $icon }}" style="font-size: 10px;"></i>
    <span>{{ $title }}</span>
</a>
