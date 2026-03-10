@props([
    'id' => null,
    'label' => null,
    'type' => 'text',
    'name' => null,
    'placeholder' => null,
    'value' => null,
    'class' => '',
    'columns' => 'col-md-6',
    'required' => false,
])

@php
    $inputId = $id ?? ($name ?? uniqid('input_'));
@endphp

<div class="{{ $columns }} mb-3">

    {{-- Label --}}
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ _trans($label) }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{-- Input --}}
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $inputId }}"
        class="form-control {{ $class }} @error($name) is-invalid @enderror"
        placeholder="{{ $placeholder ? _trans($placeholder) : '' }}" value="{{ old($name, $value) }}"
        @if ($required) required @endif {{ $attributes }}>

    {{-- Error Message --}}
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>
