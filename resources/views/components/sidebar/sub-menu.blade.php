@props(['id', 'icon', 'title', 'active' => false])

<div class="nav-item-wrapper">
    <a href="#{{ $id }}" data-bs-toggle="collapse" class="sidebar-nav__item {{ $active ? 'active' : '' }}" aria-expanded="{{ $active ? 'true' : 'false' }}"
        data-bs-title="{{ $title }}">
        <i class="bi {{ $icon }}"></i>
        <span class="flex-grow-1">{{ $title }}</span>
        <i class="bi bi-chevron-down ms-auto sub-menu-icon" style="font-size: 14px; margin-right: 0;"></i>
    </a>
    <div class="collapse {{ $active ? 'show' : '' }}" id="{{ $id }}">
        <div class="bg-light bg-opacity-50 ps-4 py-2">
            {{ $slot }}
        </div>
    </div>
</div>
