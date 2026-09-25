@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'options' => [],
    'selected' => null,
    'class' => '',
])

@php
    $inputId = $id ?? ($name ?? uniqid('select_'));
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" class="form-select {{ $class }} @if($name) @error($name) is-invalid @enderror @endif" id="{{ $inputId }}"
        {{ $attributes }}>
        {{ $slot }}
        @foreach ($options as $val => $text)
            <option value="{{ $val }}" {{ $val == $selected ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
    @if ($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>

