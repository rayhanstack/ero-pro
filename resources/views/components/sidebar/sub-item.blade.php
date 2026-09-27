@props(['route' => null, 'icon' => 'bi-circle small', 'title' => '', 'active' => null, 'url' => '#', 'patterns' => []])

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

<a href="{{ $href }}" class="sidebar-nav__item py-2 {{ $isActive ? 'active' : 'text-muted' }}" data-bs-title="{{ $title }}"
    style="font-size: 0.9rem;">
    <i class="bi {{ $isActive ? ($icon === 'bi-circle small' ? 'bi-record-circle-fill' : $icon) : $icon }}" style="font-size: 10px;"></i>
    <span>{{ $title }}</span>
</a>
