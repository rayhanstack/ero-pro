@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'placeholder' => null,
    'value' => null,
    'rows' => 3,
    'class' => '',
])

@php
    $inputId = $id ?? $name ?? uniqid('textarea_');
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" class="form-control {{ $class }}" id="{{ $inputId }}"
        rows="{{ $rows }}" placeholder="{{ $placeholder }}" {{ $attributes }}>{{ $value ?? $slot }}</textarea>
</div>
