@props([
    'title' => '',
    'value' => 0,
    'icon' => 'bi-activity',
    'color' => 'primary', // primary, success, info, warning, danger, secondary
])

@php
    $bgClass = match ($color) {
        'primary' => 'bg-primary text-white',
        'success' => 'bg-success text-white',
        'info' => 'bg-info text-white',
        'warning' => 'bg-warning text-dark',
        'danger' => 'bg-danger text-white',
        'secondary' => 'bg-secondary text-white',
        default => 'bg-primary text-white',
    };

    $subTitleClass = in_array($color, ['warning']) ? 'text-dark text-opacity-75' : 'text-white-50';
@endphp

<div class="card {{ $bgClass }} h-100 p-3 mb-0 shadow-sm border-0 rounded-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-1 {{ $subTitleClass }}">{{ _trans($title) }}</h6>
            <h3 class="fw-bold mb-0">{{ $value }}</h3>
        </div>
        <div class="fs-1 opacity-50"><i class="bi {{ $icon }}"></i></div>
    </div>
</div>
