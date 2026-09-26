@extends('admin.layouts.app')
@section('title', _trans('common.Add Employee'))

@php
    $accountErrors = $errors->hasAny(['first_name', 'last_name', 'email', 'phone', 'avatar', 'role', 'time_zone', 'password', 'password_confirmation', 'dob', 'gender', 'marital_status', 'blood_group', 'nid', 'country_id', 'state_id', 'city_id', 'present_address', 'permanent_address']);
    $jobErrors = $errors->hasAny(['department_id', 'designation_id', 'shift_id', 'manager_id', 'joining_date', 'confirmation_date', 'employment_type', 'status']);
    $salaryErrors = $errors->hasAny(['basic_salary']);
    $bankErrors = $errors->hasAny(['bank', 'branch', 'account_name', 'account_no', 'routing_number', 'swift_code']);
    $emergencyErrors = $errors->hasAny(['emergency_name', 'emergency_relationship', 'emergency_phone', 'emergency_alt_phone', 'emergency_address']);
    $documentErrors = $errors->hasAny(['document_title', 'document_file', 'document_expiry_date']);

    $activeTab = request()->get('step', 'account');
    if ($accountErrors) {
        $activeTab = 'account';
    } elseif ($jobErrors) {
        $activeTab = 'job';
    } elseif ($salaryErrors) {
        $activeTab = 'salary';
    } elseif ($bankErrors) {
        $activeTab = 'bank';
    } elseif ($emergencyErrors) {
        $activeTab = 'emergency';
    } elseif ($documentErrors) {
        $activeTab = 'documents';
    }
@endphp

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Add New Employee') }}"
        subtitle="{{ _trans('common.Step 1: Create employee account credentials and personal information') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Employees'), 'url' => route('employees.index')],
            ['label' => _trans('common.Add Employee')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Employees') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="needs-validation" id="employeeCreateForm" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-12">
                <!-- Navigation Steps -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-2">
                        <ul class="nav nav-pills nav-fill gap-2" id="employeeFormTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center justify-content-center gap-2 py-2" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                                    <span class="badge bg-primary text-white rounded-circle p-1 px-2">1</span>
                                    <span class="fw-semibold">{{ _trans('common.Account & Personal (Step 1)') }}</span>
                                    @if ($accountErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled text-muted d-flex align-items-center justify-content-center gap-2 py-2" type="button" tabindex="-1" aria-disabled="true" title="{{ _trans('common.Save Step 1 to unlock') }}">
                                    <span class="badge bg-secondary text-white rounded-circle p-1 px-2">2</span>
                                    <span>{{ _trans('common.Job Details') }}</span>
                                    <i class="bi bi-lock-fill text-muted small"></i>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled text-muted d-flex align-items-center justify-content-center gap-2 py-2" type="button" tabindex="-1" aria-disabled="true" title="{{ _trans('common.Save Step 1 to unlock') }}">
                                    <span class="badge bg-secondary text-white rounded-circle p-1 px-2">3</span>
                                    <span>{{ _trans('common.Salary') }}</span>
                                    <i class="bi bi-lock-fill text-muted small"></i>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled text-muted d-flex align-items-center justify-content-center gap-2 py-2" type="button" tabindex="-1" aria-disabled="true" title="{{ _trans('common.Save Step 1 to unlock') }}">
                                    <span class="badge bg-secondary text-white rounded-circle p-1 px-2">4</span>
                                    <span>{{ _trans('common.Bank') }}</span>
                                    <i class="bi bi-lock-fill text-muted small"></i>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled text-muted d-flex align-items-center justify-content-center gap-2 py-2" type="button" tabindex="-1" aria-disabled="true" title="{{ _trans('common.Save Step 1 to unlock') }}">
                                    <span class="badge bg-secondary text-white rounded-circle p-1 px-2">5</span>
                                    <span>{{ _trans('common.Emergency') }}</span>
                                    <i class="bi bi-lock-fill text-muted small"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 1 Content Card -->
                <div class="tab-content" id="employeeFormTabContent">
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        <x-ui.card :title="_trans('common.Step 1: Account Credentials & Personal Information')" icon="bi-person-badge">
                            <div class="alert alert-primary-subtle border-primary-subtle d-flex align-items-center gap-2 mb-4 p-3 rounded-3">
                                <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
                                <div class="small">
                                    <strong>{{ _trans('common.Step 1 of 5:') }}</strong> {{ _trans('common.Submit this form to register the employee account in the database. You will then proceed to update job, salary, bank, and emergency details.') }}
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Avatar Upload -->
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold d-block">{{ _trans('common.Profile Photo') }}</label>
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="position-relative">
                                            <img id="avatarPreview"
                                                src="https://ui-avatars.com/api/?name=New+Employee&background=4f46e5&color=fff"
                                                alt="Avatar Preview"
                                                class="rounded-circle object-fit-cover shadow-sm border border-2 border-primary"
                                                width="90" height="90">
                                        </div>
                                        <div>
                                            <input type="file"
                                                name="avatar"
                                                id="avatar"
                                                class="form-control @error('avatar') is-invalid @enderror"
                                                accept="image/jpeg,image/png,image/webp,image/jpg"
                                                onchange="previewAvatar(this)">
                                            <small class="text-muted d-block mt-1">{{ _trans('common.Supported formats: JPG, PNG, WEBP. Max size: 2MB') }}</small>
                                            @error('avatar')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="first_name" class="form-label fw-semibold">{{ _trans('common.First Name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="first_name"
                                        id="first_name"
                                        class="form-control @error('first_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. John') }}"
                                        value="{{ old('first_name') }}"
                                        required>
                                    @error('first_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-semibold">{{ _trans('common.Last Name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="last_name"
                                        id="last_name"
                                        class="form-control @error('last_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Doe') }}"
                                        value="{{ old('last_name') }}"
                                        required>
                                    @error('last_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">{{ _trans('common.Email Address (Login)') }} <span class="text-danger">*</span></label>
                                    <input type="email"
                                        name="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. john.doe@company.com') }}"
                                        value="{{ old('email') }}"
                                        required>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">{{ _trans('common.Phone Number') }}</label>
                                    <input type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. +1 234 567 8900') }}"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Role & Timezone -->
                                <div class="col-md-6">
                                    <label for="role" class="form-label fw-semibold">{{ _trans('common.Role & Permissions') }} <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">{{ _trans('common.Select Role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role', 'Employee') === $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="time_zone" class="form-label fw-semibold">{{ _trans('common.Timezone') }}</label>
                                    <select name="time_zone" id="time_zone" class="form-select @error('time_zone') is-invalid @enderror">
                                        @foreach (\DateTimeZone::listIdentifiers() as $tz)
                                            <option value="{{ $tz }}" {{ old('time_zone', globalSetting('timezone') ?: config('app.timezone', 'Asia/Dhaka')) === $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('time_zone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password & Confirm Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">{{ _trans('common.Password') }} <span class="text-danger">*</span></label>
                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ _trans('common.Minimum 8 characters') }}"
                                        required
                                        minlength="8">
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">{{ _trans('common.Confirm Password') }} <span class="text-danger">*</span></label>
                                    <input type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control"
                                        placeholder="{{ _trans('common.Re-enter password') }}"
                                        required
                                        minlength="8">
                                </div>

                                <div class="col-12"><hr class="my-2"></div>

                                <div class="col-md-4">
                                    <label for="dob" class="form-label fw-semibold">{{ _trans('common.Date of Birth') }}</label>
                                    <input type="date"
                                        name="dob"
                                        id="dob"
                                        class="form-control @error('dob') is-invalid @enderror"
                                        value="{{ old('dob') }}">
                                    @error('dob')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label fw-semibold">{{ _trans('common.Gender') }} <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">{{ _trans('common.Select Gender') }}</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender->value }}" {{ old('gender', 'male') === $gender->value ? 'selected' : '' }}>
                                                {{ $gender->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="marital_status" class="form-label fw-semibold">{{ _trans('common.Marital Status') }}</label>
                                    <select name="marital_status" id="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Marital Status') }}</option>
                                        @foreach ($maritalStatuses as $ms)
                                            <option value="{{ $ms->value }}" {{ old('marital_status') === $ms->value ? 'selected' : '' }}>
                                                {{ $ms->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marital_status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="blood_group" class="form-label fw-semibold">{{ _trans('common.Blood Group') }}</label>
                                    <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Blood Group') }}</option>
                                        @foreach ($bloodGroups as $bg)
                                            <option value="{{ $bg->value }}" {{ old('blood_group') === $bg->value ? 'selected' : '' }}>
                                                {{ $bg->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('blood_group')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="nid" class="form-label fw-semibold">{{ _trans('common.National ID (NID) / Passport') }}</label>
                                    <input type="text"
                                        name="nid"
                                        id="nid"
                                        class="form-control @error('nid') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. 19901234567890 or passport no.') }}"
                                        value="{{ old('nid') }}">
                                    @error('nid')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cascading Location -->
                                <div class="col-md-4">
                                    <label for="country_id" class="form-label fw-semibold">{{ _trans('common.Country') }}</label>
                                    <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror" onchange="loadStates(this.value)">
                                        <option value="">{{ _trans('common.Select Country') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="state_id" class="form-label fw-semibold">{{ _trans('common.State / Division') }}</label>
                                    <select name="state_id" id="state_id" class="form-select @error('state_id') is-invalid @enderror" onchange="loadCities(this.value)">
                                        <option value="">{{ _trans('common.Select State') }}</option>
                                    </select>
                                    @error('state_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="city_id" class="form-label fw-semibold">{{ _trans('common.City') }}</label>
                                    <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select City') }}</option>
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="present_address" class="form-label fw-semibold">{{ _trans('common.Present Address') }}</label>
                                    <textarea name="present_address" id="present_address" class="form-control @error('present_address') is-invalid @enderror" rows="2" placeholder="{{ _trans('common.Enter present residential address, house, road...') }}">{{ old('present_address') }}</textarea>
                                    @error('present_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="permanent_address" class="form-label fw-semibold">{{ _trans('common.Permanent Address') }}</label>
                                    <textarea name="permanent_address" id="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="2" placeholder="{{ _trans('common.Enter permanent residential address...') }}">{{ old('permanent_address') }}</textarea>
                                    @error('permanent_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 1 Actions: Store in Database and Proceed to Step 2 -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">{{ _trans('common.Cancel') }}</a>
                                <button type="submit" name="next_step" value="job" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 shadow-sm">
                                    <span>{{ _trans('common.Save & Proceed to Job Details') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function loadStates(countryId) {
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        stateSelect.innerHTML = '<option value="">{{ _trans('common.Loading...') }}</option>';
        citySelect.innerHTML = '<option value="">{{ _trans('common.Select City') }}</option>';

        if (!countryId) {
            stateSelect.innerHTML = '<option value="">{{ _trans('common.Select State') }}</option>';
            return;
        }

        fetch('/admin/ajax/states/' + countryId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans('common.Select State') }}</option>';
                data.forEach(state => {
                    options += `<option value="${state.id}">${state.name}</option>`;
                });
                stateSelect.innerHTML = options;
            })
            .catch(() => {
                stateSelect.innerHTML = '<option value="">{{ _trans('common.Select State') }}</option>';
            });
    }

    function loadCities(stateId) {
        const citySelect = document.getElementById('city_id');
        citySelect.innerHTML = '<option value="">{{ _trans('common.Loading...') }}</option>';

        if (!stateId) {
            citySelect.innerHTML = '<option value="">{{ _trans('common.Select City') }}</option>';
            return;
        }

        fetch('/admin/ajax/cities/' + stateId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans('common.Select City') }}</option>';
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
                citySelect.innerHTML = options;
            })
            .catch(() => {
                citySelect.innerHTML = '<option value="">{{ _trans('common.Select City') }}</option>';
            });
    }

    function validateStep1() {
        const form = document.getElementById('employeeCreateForm');
        let isValid = true;
        let firstInvalid = null;

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            let fieldValid = true;
            let errorMsg = '';

            if (!input.checkValidity()) {
                fieldValid = false;
                if (input.validity.valueMissing) {
                    errorMsg = '{{ _trans('common.This field is required.') }}';
                } else if (input.validity.typeMismatch && input.type === 'email') {
                    errorMsg = '{{ _trans('common.Please enter a valid email address.') }}';
                } else if (input.validity.tooShort) {
                    errorMsg = `{{ _trans('common.Minimum characters required:') }} ${input.minLength}`;
                } else {
                    errorMsg = input.validationMessage;
                }
            }

            if (fieldValid && input.name === 'password_confirmation') {
                const passwordInput = form.querySelector('input[name="password"]');
                if (passwordInput && passwordInput.value && input.value !== passwordInput.value) {
                    fieldValid = false;
                    errorMsg = '{{ _trans('common.The password confirmation does not match.') }}';
                }
            }

            if (!fieldValid) {
                isValid = false;
                input.classList.add('is-invalid');
                let fb = input.parentNode.querySelector('.invalid-feedback');
                if (!fb) {
                    fb = document.createElement('div');
                    fb.className = 'invalid-feedback d-block';
                    input.parentNode.appendChild(fb);
                } else {
                    fb.style.display = 'block';
                }
                fb.textContent = errorMsg;

                if (!firstInvalid) firstInvalid = input;
            } else {
                input.classList.remove('is-invalid');
                const fb = input.parentNode.querySelector('.invalid-feedback');
                if (fb && !fb.hasAttribute('data-server-error')) {
                    fb.style.display = 'none';
                }
            }
        });

        if (!isValid && firstInvalid) {
            firstInvalid.focus();
            if (window.toastr) {
                toastr.error('{{ _trans('common.Please fill all required fields in Step 1 correctly before proceeding.') }}');
            }
        }

        return isValid;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('employeeCreateForm');
        if (form) {
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        const fb = this.parentNode.querySelector('.invalid-feedback');
                        if (fb && !fb.hasAttribute('data-server-error')) {
                            fb.style.display = 'none';
                        }
                    }
                });
                input.addEventListener('change', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        const fb = this.parentNode.querySelector('.invalid-feedback');
                        if (fb && !fb.hasAttribute('data-server-error')) {
                            fb.style.display = 'none';
                        }
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                if (!validateStep1()) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
</script>
@endpush
