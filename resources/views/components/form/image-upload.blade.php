@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'value' => null,
    'defaultImage' => 'https://ui-avatars.com/api/?name=IMG&background=f8f9fa&color=adb5bd',
    'accept' => 'image/*',
    'columns' => 'col-md-6',
    'required' => false,
])

@php
    $inputId = $id ?? ($name ?? uniqid('image_upload_'));
    $previewId = $inputId . '_preview';
    $fileNameId = $inputId . '_filename';

    $previewImage = $value ? asset($value) : $defaultImage;
@endphp

<div class="mb-4 {{ $columns }}">

    {{-- Label --}}
    @if ($label)
        <label for="{{ $inputId }}" class="form-label fw-semibold">
            {{ _trans($label) }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="input-group">

        {{-- Preview --}}
        <span class="input-group-text bg-light">
            <img id="{{ $previewId }}" src="{{ $previewImage }}" class="rounded-circle object-fit-cover" width="36"
                height="36" alt="Preview">
        </span>

        {{-- File Name --}}
        <input type="text" id="{{ $fileNameId }}" class="form-control @error($name) is-invalid @enderror"
            placeholder="{{ _trans('Choose a file') }}" readonly>

        {{-- Browse Button --}}
        <label class="btn btn-outline-primary mb-0">
            {{ _trans('Browse') }}

            <input type="file" name="{{ $name }}" id="{{ $inputId }}" class="d-none"
                accept="{{ $accept }}" @if ($required) required @endif {{ $attributes }}>
        </label>

    </div>

    {{-- Error --}}
    @error($name)
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
    @enderror

</div>

@pushonce('script')
    <script>
        document.addEventListener('change', function(e) {

            if (e.target.matches('input[type="file"][accept^="image/"]')) {

                const input = e.target;
                const file = input.files[0];

                if (!file) return;

                const container = input.closest('.input-group');
                const previewImg = container.querySelector('img');
                const nameInput = container.querySelector('input[readonly]');

                if (nameInput) {
                    nameInput.value = file.name;
                }

                const reader = new FileReader();

                reader.onload = function(event) {
                    if (previewImg) {
                        previewImg.src = event.target.result;
                    }
                };

                reader.readAsDataURL(file);
            }

        });
    </script>
@endpushonce
