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
    $textareaValue = $name ? old($name, $value ?? $slot) : ($value ?? $slot);
    $textareaValue = is_array($textareaValue) ? '' : $textareaValue;
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" class="form-control {{ $class }} @if($name) @error($name) is-invalid @enderror @endif" id="{{ $inputId }}"
        rows="{{ $rows }}" placeholder="{{ $placeholder }}" {{ $attributes }}>{{ $textareaValue }}</textarea>
    @if ($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>
