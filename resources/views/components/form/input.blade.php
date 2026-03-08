@props([
    'id' => null,
    'label' => null,
    'type' => 'text',
    'name' => null,
    'placeholder' => null,
    'value' => null,
    'class' => '',
])

@php
    $inputId = $id ?? $name ?? uniqid('input_');
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" class="form-control {{ $class }}" id="{{ $inputId }}"
        placeholder="{{ $placeholder }}" value="{{ $value }}" {{ $attributes }}>
</div>
