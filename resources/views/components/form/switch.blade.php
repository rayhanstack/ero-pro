@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => '1',
    'class' => '',
])

@php
    $inputId = $id ?? $name ?? uniqid('switch_');
@endphp

<div class="form-check form-switch {{ $class }}">
    <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" value="{{ $value }}" id="{{ $inputId }}" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
    @if ($label)
        <label class="form-check-label" for="{{ $inputId }}">
            {{ $label }}
        </label>
    @endif
</div>
