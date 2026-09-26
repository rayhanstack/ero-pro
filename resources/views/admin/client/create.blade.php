@extends('admin.layouts.app')
@section('title', _trans('common.Add Client'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Add New Client') }}"
        subtitle="{{ _trans('common.Register a new company and primary contact') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Clients'), 'url' => route('clients.index')],
            ['label' => _trans('common.Add Client')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Clients') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data" class="needs-validation">
        @csrf

        <div class="row g-4">
            {{-- Left Column: Company & Contact Information --}}
            <div class="col-lg-8">
                {{-- Company Details --}}
                <x-ui.card :title="_trans('common.Company Information')" icon="bi-building">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="company_name" class="form-label fw-semibold">
                                {{ _trans('common.Company Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="company_name"
                                id="company_name"
                                class="form-control @error('company_name') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. Acme Corporation') }}"
                                value="{{ old('company_name') }}"
                                required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="industry" class="form-label fw-semibold">
                                {{ _trans('common.Industry') }}
                            </label>
                            <input type="text"
                                name="industry"
                                id="industry"
                                class="form-control @error('industry') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. Technology, Healthcare, Finance') }}"
                                value="{{ old('industry') }}">
                            @error('industry')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_name" class="form-label fw-semibold">
                                {{ _trans('common.Primary Contact Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="contact_name"
                                id="contact_name"
                                class="form-control @error('contact_name') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. John Doe') }}"
                                value="{{ old('contact_name') }}"
                                required>
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                {{ _trans('common.Email Address') }} <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. contact@acme.com') }}"
                                value="{{ old('email') }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">
                                {{ _trans('common.Phone Number') }}
                            </label>
                            <input type="text"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. +1 555-0199') }}"
                                value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="website" class="form-label fw-semibold">
                                {{ _trans('common.Website URL') }}
                            </label>
                            <input type="url"
                                name="website"
                                id="website"
                                class="form-control @error('website') is-invalid @enderror"
                                placeholder="{{ _trans('common.https://www.example.com') }}"
                                value="{{ old('website') }}">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                {{-- Location & Address --}}
                <div class="mt-4">
                    <x-ui.card :title="_trans('common.Location & Address')" icon="bi-geo-alt">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="country_id" class="form-label fw-semibold">{{ _trans('common.Country') }}</label>
                                <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                    <option value="">{{ _trans('common.Select Country') }}</option>
                                    @foreach ($countries as $c)
                                        <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="state_id" class="form-label fw-semibold">{{ _trans('common.State / Province') }}</label>
                                <select name="state_id" id="state_id" class="form-select @error('state_id') is-invalid @enderror" disabled>
                                    <option value="">{{ _trans('common.Select State') }}</option>
                                </select>
                                @error('state_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="city_id" class="form-label fw-semibold">{{ _trans('common.City') }}</label>
                                <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" disabled>
                                    <option value="">{{ _trans('common.Select City') }}</option>
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">{{ _trans('common.Street Address') }}</label>
                                <textarea name="address"
                                    id="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    rows="2"
                                    placeholder="{{ _trans('common.Suite, Building, Street Address...') }}">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Additional Notes --}}
                <div class="mt-4">
                    <x-ui.card :title="_trans('common.Notes & Remarks')" icon="bi-card-text">
                        <div>
                            <label for="notes" class="form-label fw-semibold">{{ _trans('common.Client Notes') }}</label>
                            <textarea name="notes"
                                id="notes"
                                class="form-control @error('notes') is-invalid @enderror"
                                rows="3"
                                placeholder="{{ _trans('common.Internal notes regarding this client...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </x-ui.card>
                </div>
            </div>

            {{-- Right Column: Settings, Currency, Logo --}}
            <div class="col-lg-4">
                {{-- Status & Currency --}}
                <x-ui.card :title="_trans('common.Client Settings')" icon="bi-sliders">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}" {{ old('status', 'active') === $st->value ? 'selected' : '' }}>
                                        {{ $st->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="currency_id" class="form-label fw-semibold">{{ _trans('common.Default Currency') }}</label>
                            <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                <option value="">{{ _trans('common.Default Currency') }}</option>
                                @foreach ($currencies as $curr)
                                    <option value="{{ $curr->id }}" {{ old('currency_id') == $curr->id ? 'selected' : '' }}>
                                        {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                {{-- Logo Upload --}}
                <div class="mt-4">
                    <x-ui.card :title="_trans('common.Company Logo')" icon="bi-image">
                        <div class="text-center">
                            <div class="mb-3">
                                <div id="logoPreviewContainer" class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border shadow-sm" style="width: 100px; height: 100px; overflow: hidden;">
                                    <i id="logoPlaceholderIcon" class="bi bi-building fs-1 text-muted"></i>
                                    <img id="logoPreviewImg" src="#" alt="Logo Preview" class="w-100 h-100 object-fit-cover d-none">
                                </div>
                            </div>
                            <label class="btn btn-outline-primary btn-sm mb-1" for="logoInput">
                                <i class="bi bi-upload me-1"></i> {{ _trans('common.Upload Logo') }}
                            </label>
                            <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                            <div class="text-muted small">{{ _trans('common.Allowed: JPG, PNG, WEBP, SVG. Max: 2MB') }}</div>
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </x-ui.card>
                </div>

                {{-- Actions --}}
                <div class="mt-4">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>{{ _trans('common.Create Client') }}</span>
                        </button>
                        <a href="{{ route('clients.index') }}" class="btn btn-light">
                            {{ _trans('common.Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Logo Preview
    $('#logoInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreviewImg').attr('src', e.target.result).removeClass('d-none');
                $('#logoPlaceholderIcon').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Cascading Country -> State -> City
    $('#country_id').on('change', function() {
        const countryId = $(this).val();
        const $stateSelect = $('#state_id');
        const $citySelect = $('#city_id');

        $stateSelect.html('<option value="">' + "{{ _trans('common.Select State') }}" + '</option>').prop('disabled', true);
        $citySelect.html('<option value="">' + "{{ _trans('common.Select City') }}" + '</option>').prop('disabled', true);

        if (!countryId) return;

        $.ajax({
            url: '/states/' + countryId,
            type: 'GET',
            dataType: 'json',
            success: function(states) {
                if (states && states.length > 0) {
                    states.forEach(function(st) {
                        $stateSelect.append('<option value="' + st.id + '">' + st.name + '</option>');
                    });
                    $stateSelect.prop('disabled', false);
                }
            }
        });
    });

    $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const $citySelect = $('#city_id');

        $citySelect.html('<option value="">' + "{{ _trans('common.Select City') }}" + '</option>').prop('disabled', true);

        if (!stateId) return;

        $.ajax({
            url: '/cities/' + stateId,
            type: 'GET',
            dataType: 'json',
            success: function(cities) {
                if (cities && cities.length > 0) {
                    cities.forEach(function(c) {
                        $citySelect.append('<option value="' + c.id + '">' + c.name + '</option>');
                    });
                    $citySelect.prop('disabled', false);
                }
            }
        });
    });
});
</script>
@endpush
