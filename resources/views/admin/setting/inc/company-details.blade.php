@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="v-pills-company" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Company Details & Branding') }}</h5>

            <form method="POST" action="{{ route('settings.company') }}" enctype="multipart/form-data" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4 pb-4 border-bottom">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">{{ _trans('common.Company Logo') }}</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center" style="width: 120px; height: 60px;">
                                <img id="logoPreview"
                                    src="{{ globalSetting('company_logo') ?: asset('assets/images/avatars/default-fallback-image.png') }}"
                                    class="img-fluid object-fit-contain max-h-100"
                                    alt="Logo">
                            </div>
                            <div>
                                <label class="btn btn-sm btn-outline-primary mb-1 cursor-pointer">
                                    <i class="bi bi-upload me-1"></i>
                                    <span>{{ _trans('common.Upload Logo') }}</span>
                                    <input type="file" name="company_logo" id="logoInput" class="d-none" accept="image/*">
                                </label>
                                <div class="text-muted small" style="font-size: 11px;">PNG, SVG, JPG, WebP (Max 2MB)</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">{{ _trans('common.Favicon') }}</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <img id="faviconPreview"
                                    src="{{ globalSetting('company_favicon') ?: asset('favicon.ico') }}"
                                    class="img-fluid object-fit-contain"
                                    width="32"
                                    height="32"
                                    alt="Favicon">
                            </div>
                            <div>
                                <label class="btn btn-sm btn-outline-primary mb-1 cursor-pointer">
                                    <i class="bi bi-upload me-1"></i>
                                    <span>{{ _trans('common.Upload Favicon') }}</span>
                                    <input type="file" name="company_favicon" id="faviconInput" class="d-none" accept="image/*,.ico">
                                </label>
                                <div class="text-muted small" style="font-size: 11px;">ICO, PNG (32x32px recommended)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="company_name" class="form-label fw-semibold small">{{ _trans('common.Company Name') }} <span class="text-danger">*</span></label>
                        <input type="text"
                            name="company_name"
                            id="company_name"
                            class="form-control @error('company_name') is-invalid @enderror"
                            value="{{ old('company_name', $settings['company_name'] ?? 'ERP Pro') }}"
                            required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="company_email" class="form-label fw-semibold small">{{ _trans('common.Company Email') }}</label>
                        <input type="email"
                            name="company_email"
                            id="company_email"
                            class="form-control @error('company_email') is-invalid @enderror"
                            value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                            placeholder="info@company.com">
                        @error('company_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="company_phone" class="form-label fw-semibold small">{{ _trans('common.Company Phone') }}</label>
                        <input type="text"
                            name="company_phone"
                            id="company_phone"
                            class="form-control @error('company_phone') is-invalid @enderror"
                            value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                            placeholder="+880 1700-000000">
                        @error('company_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="company_address" class="form-label fw-semibold small">{{ _trans('common.Address') }}</label>
                        <input type="text"
                            name="company_address"
                            id="company_address"
                            class="form-control @error('company_address') is-invalid @enderror"
                            value="{{ old('company_address', $settings['company_address'] ?? '') }}"
                            placeholder="Gulshan, Dhaka, Bangladesh">
                        @error('company_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="company_description" class="form-label fw-semibold small">{{ _trans('common.Company Description') }}</label>
                        <textarea name="company_description"
                            id="company_description"
                            class="form-control @error('company_description') is-invalid @enderror"
                            rows="3"
                            placeholder="{{ _trans('common.Short company overview or slogan...') }}">{{ old('company_description', $settings['company_description'] ?? '') }}</textarea>
                        @error('company_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Company Details') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            if (input && preview) {
                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            preview.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        setupImagePreview('logoInput', 'logoPreview');
        setupImagePreview('faviconInput', 'faviconPreview');
    });
</script>
@endpush
