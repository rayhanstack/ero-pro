@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'multiple' => false,
    'columns' => 'col-md-6',
    'required' => false,
    'class' => '',
])

@php
    $inputId = $id ?? ($name ? str_replace(['[', ']'], ['_', ''], $name) : uniqid('select2_'));
    $currentSelected = $name ? old($name, $selected) : $selected;
    if ($multiple && !is_array($currentSelected)) {
        $currentSelected = $currentSelected ? (array)$currentSelected : [];
    }
@endphp

<div class="{{ $columns }} mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ _trans($label) }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $inputId }}"
        class="form-select select2-element {{ $class }} @if($name) @error($name) is-invalid @enderror @endif"
        data-placeholder="{{ $placeholder ? _trans($placeholder) : '' }}"
        @if ($multiple) multiple @endif
        @if ($required) required @endif
        {{ $attributes }}>
        @if (!$multiple)
            <option value=""></option>
        @endif

        {{ $slot }}

        @foreach ($options as $val => $text)
            @php
                $isSelected = $multiple
                    ? in_array((string)$val, array_map('strval', (array)$currentSelected), true)
                    : (string)$val === (string)$currentSelected;
            @endphp
            <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                {{ _trans((string)$text) }}
            </option>
        @endforeach
    </select>

    @if ($name)
        @error($name)
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>

@pushonce('script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2-element').each(function() {
                    var $el = $(this);
                    if (!$el.hasClass('select2-hidden-accessible')) {
                        $el.select2({
                            placeholder: $el.data('placeholder') || 'Select an option',
                            allowClear: !$el.prop('required'),
                            width: '100%',
                            dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body)
                        });
                    }
                });
            }
        });
    </script>
@endpushonce
