@props([
    'id' => null,
    'label' => '',
    'name' => '',
    'value' => null,
    'defaultImage' => 'https://ui-avatars.com/api/?name=IM&background=f8f9fa&color=adb5bd',
    'accept' => 'image/*',
    'helpText' => 'Allowed types: JPG, PNG, GIF. Max size 2MB.',
    'columns' => 'col-12',
])

@php
    $inputId = $id ?? ($name ?? uniqid('image_upload_'));
    $previewId = $inputId . '_preview';
    $fileNameId = $inputId . '_filename';

    $previewImage = $value ? asset($value) : $defaultImage;
@endphp

<div class="mb-4 {{ $columns }}">
    @if ($label)
        <label class="form-label fw-semibold">{{ $label }}</label>
    @endif

    <div class="input-group align-items-center">

        <!-- Preview -->
        <span class="input-group-text bg-light">
            <img id="{{ $previewId }}" src="{{ $previewImage }}" class="rounded-circle object-fit-cover" width="32"
                height="32" alt="Preview">
        </span>

        <!-- File name -->
        <input type="text" id="{{ $fileNameId }}" class="form-control" placeholder="Choose a file..." readonly>

        <!-- Browse button -->
        <label class="btn btn-outline-primary mb-0">
            Browse
            <input type="file" name="{{ $name }}" id="{{ $inputId }}" class="d-none"
                accept="{{ $accept }}" {{ $attributes }}>
        </label>

    </div>

    @if ($helpText)
        <div class="form-text mt-2">
            {{ $helpText }}
        </div>
    @endif
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
                }

                reader.readAsDataURL(file);
            }

        });
    </script>
@endpushonce
