@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'outline' => false,
    'block' => false,
])

@php
    $btnClass = $outline ? "btn-outline-$variant" : "btn-$variant";

    if ($size) {
        $btnClass .= " btn-$size";
    }

    if ($block) {
        $btnClass .= ' w-100';
    }
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "btn $btnClass"]) }}>
    {{ $slot }}
</button>
