@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'placeholder' => 'Select date...',
    'value' => null,
    'format' => 'Y-m-d',
    'enableTime' => false,
    'mode' => 'single',
    'class' => '',
    'columns' => 'col-md-6',
    'required' => false,
])

@php
    $inputId = $id ?? ($name ?? uniqid('date_'));
    $inputValue = $name ? old($name, $value) : $value;
    $inputValue = is_array($inputValue) ? '' : $inputValue;
    $timeFormat = $enableTime ? ($format === 'Y-m-d' ? 'Y-m-d H:i' : $format) : $format;
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

    <div class="input-group">
        <span class="input-group-text bg-light text-muted border-end-0">
            <i class="bi {{ $enableTime ? 'bi-clock' : 'bi-calendar3' }}"></i>
        </span>
        <input type="text"
            name="{{ $name }}"
            id="{{ $inputId }}"
            class="form-control border-start-0 flatpickr-input {{ $class }} @if($name) @error($name) is-invalid @enderror @endif"
            placeholder="{{ $placeholder ? _trans($placeholder) : '' }}"
            value="{{ $inputValue }}"
            data-date-format="{{ $timeFormat }}"
            data-enable-time="{{ $enableTime ? 'true' : 'false' }}"
            data-mode="{{ $mode }}"
            @if ($required) required @endif
            {{ $attributes }}>
    </div>

    @if ($name)
        @error($name)
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>

@pushonce('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof flatpickr !== 'undefined') {
                document.querySelectorAll('.flatpickr-input').forEach(function(el) {
                    if (!el._flatpickr) {
                        var dateFormat = el.getAttribute('data-date-format') || 'Y-m-d';
                        var enableTime = el.getAttribute('data-enable-time') === 'true';
                        var mode = el.getAttribute('data-mode') || 'single';

                        flatpickr(el, {
                            dateFormat: dateFormat,
                            enableTime: enableTime,
                            mode: mode,
                            altInput: true,
                            altFormat: enableTime ? 'F j, Y H:i' : 'F j, Y',
                            allowInput: true
                        });
                    }
                });
            }
        });
    </script>
@endpushonce
