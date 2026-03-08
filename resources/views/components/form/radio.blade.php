@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => '1',
    'class' => '',
])

@php
    $inputId = $id ?? $name ?? uniqid('radio_');
@endphp

<div class="form-check {{ $class }}">
    <input class="form-check-input" type="radio" name="{{ $name }}" value="{{ $value }}" id="{{ $inputId }}" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
    @if ($label)
        <label class="form-check-label" for="{{ $inputId }}">
            {{ $label }}
        </label>
    @endif
</div>
