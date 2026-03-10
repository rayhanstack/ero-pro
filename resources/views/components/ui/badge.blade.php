@props([
    'variant' => 'primary',
    'pill' => false,
])

@php
    $badgeClass = "bg-$variant";

    if ($pill) {
        $badgeClass .= ' rounded-pill';
    }

    $textDark = in_array($variant, ['warning', 'info', 'light']) ? 'text-dark' : '';
@endphp

<span {{ $attributes->merge(['class' => "badge $badgeClass $textDark"]) }}>
    {{ $slot }}
</span>
