@extends('admin.layouts.app')
@section('title', _trans('common.Edit Employee') . ' - ' . $employee->name)

@php
    $accountErrors = $errors->hasAny(['first_name', 'last_name', 'email', 'phone', 'avatar', 'role', 'time_zone', 'password', 'password_confirmation', 'dob', 'gender', 'marital_status', 'blood_group', 'nid', 'country_id', 'state_id', 'city_id', 'present_address', 'permanent_address']);
    $jobErrors = $errors->hasAny(['department_id', 'designation_id', 'shift_id', 'manager_id', 'joining_date', 'confirmation_date', 'employment_type', 'status']);
    $salaryErrors = $errors->hasAny(['basic_salary']);
    $bankErrors = $errors->hasAny(['bank', 'branch', 'account_name', 'account_no', 'routing_number', 'swift_code']);
    $emergencyErrors = $errors->hasAny(['emergency_name', 'emergency_relationship', 'emergency_phone', 'emergency_alt_phone', 'emergency_address']);

    $requestedStep = request()->get('step', 'account');
    $activeTab = $requestedStep;

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
    }
@endphp

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Employee Profile & Details') }}"
        subtitle="{{ _trans('common.Update employee credentials, job details, compensation, bank and emergency records') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Employees'), 'url' => route('employees.index')],
            ['label' => $employee->name, 'url' => route('employees.show', $employee)],
            ['label' => _trans('common.Edit Details')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
                <i class="bi bi-eye"></i>
                <span>{{ _trans('common.View Profile') }}</span>
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to List') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" class="needs-validation" id="employeeEditForm" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-12">
                <!-- Navigation Tabs / Steps -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-2">
                        <ul class="nav nav-pills nav-fill gap-2" id="employeeFormTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'account' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2 py-2" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-selected="{{ $activeTab === 'account' ? 'true' : 'false' }}">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-1 px-2">1</span>
                                    <span>{{ _trans('common.Account & Personal') }}</span>
                                    @if ($accountErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'job' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2 py-2" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-selected="{{ $activeTab === 'job' ? 'true' : 'false' }}">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-1 px-2">2</span>
                                    <span>{{ _trans('common.Job & Employment') }}</span>
                                    @if ($jobErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'salary' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2 py-2" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab" aria-selected="{{ $activeTab === 'salary' ? 'true' : 'false' }}">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-1 px-2">3</span>
                                    <span>{{ _trans('common.Salary') }}</span>
                                    @if ($salaryErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'bank' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2 py-2" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab" aria-selected="{{ $activeTab === 'bank' ? 'true' : 'false' }}">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-1 px-2">4</span>
                                    <span>{{ _trans('common.Bank Details') }}</span>
                                    @if ($bankErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'emergency' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2 py-2" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab" aria-selected="{{ $activeTab === 'emergency' ? 'true' : 'false' }}">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-1 px-2">5</span>
                                    <span>{{ _trans('common.Emergency Contact') }}</span>
                                    @if ($emergencyErrors)
                                        <span class="badge bg-danger rounded-pill px-1 py-0 ms-1" title="{{ _trans('common.Contains errors') }}">!</span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="tab-content" id="employeeFormTabContent">
                    <!-- 1. ACCOUNT & PERSONAL DETAILS -->
                    <div class="tab-pane fade {{ $activeTab === 'account' ? 'show active' : '' }}" id="account" role="tabpanel">
                        <x-ui.card :title="_trans('common.Step 1: Account & Personal Information')" icon="bi-person-badge">
                            <div class="row g-3">
                                <!-- Avatar Upload -->
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold d-block">{{ _trans('common.Profile Photo') }}</label>
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="position-relative">
                                            <img id="avatarPreview"
                                                src="{{ $employee->avatar_url }}"
                                                alt="{{ $employee->name }}"
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
                                        value="{{ old('first_name', $employee->first_name) }}"
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
                                        value="{{ old('last_name', $employee->last_name) }}"
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
                                        value="{{ old('email', $employee->email) }}"
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
                                        value="{{ old('phone', $employee->phone) }}">
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
                                            <option value="{{ $role->name }}" {{ old('role', $employee->roles->first()?->name) === $role->name ? 'selected' : '' }}>
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
                                            <option value="{{ $tz }}" {{ old('time_zone', $employee->time_zone ?: (globalSetting('timezone') ?: config('app.timezone', 'Asia/Dhaka'))) === $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('time_zone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password & Confirm Password (Optional on update) -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">{{ _trans('common.New Password') }}</label>
                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ _trans('common.Leave blank to keep current password') }}"
                                        minlength="8">
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">{{ _trans('common.Confirm New Password') }}</label>
                                    <input type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control"
                                        placeholder="{{ _trans('common.Repeat new password') }}"
                                        minlength="8">
                                </div>

                                <div class="col-12"><hr class="my-2"></div>

                                @php $detail = $employee->detail; @endphp
                                <div class="col-md-4">
                                    <label for="dob" class="form-label fw-semibold">{{ _trans('common.Date of Birth') }}</label>
                                    <input type="date"
                                        name="dob"
                                        id="dob"
                                        class="form-control @error('dob') is-invalid @enderror"
                                        value="{{ old('dob', $detail?->dob?->format('Y-m-d')) }}">
                                    @error('dob')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label fw-semibold">{{ _trans('common.Gender') }}</label>
                                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Gender') }}</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender->value }}" {{ old('gender', $detail?->gender?->value ?? 'male') === $gender->value ? 'selected' : '' }}>
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
                                            <option value="{{ $ms->value }}" {{ old('marital_status', $detail?->marital_status?->value) === $ms->value ? 'selected' : '' }}>
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
                                            <option value="{{ $bg->value }}" {{ old('blood_group', $detail?->blood_group?->value) === $bg->value ? 'selected' : '' }}>
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
                                        value="{{ old('nid', $detail?->nid) }}">
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
                                            <option value="{{ $country->id }}" {{ old('country_id', $detail?->country_id) == $country->id ? 'selected' : '' }}>
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
                                        @foreach ($states as $st)
                                            <option value="{{ $st->id }}" {{ old('state_id', $detail?->state_id) == $st->id ? 'selected' : '' }}>
                                                {{ $st->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="city_id" class="form-label fw-semibold">{{ _trans('common.City') }}</label>
                                    <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select City') }}</option>
                                        @foreach ($cities as $ct)
                                            <option value="{{ $ct->id }}" {{ old('city_id', $detail?->city_id) == $ct->id ? 'selected' : '' }}>
                                                {{ $ct->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="present_address" class="form-label fw-semibold">{{ _trans('common.Present Address') }}</label>
                                    <textarea name="present_address" id="present_address" class="form-control @error('present_address') is-invalid @enderror" rows="2" placeholder="{{ _trans('common.Enter present residential address, house, road...') }}">{{ old('present_address', $detail?->present_address) }}</textarea>
                                    @error('present_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="permanent_address" class="form-label fw-semibold">{{ _trans('common.Permanent Address') }}</label>
                                    <textarea name="permanent_address" id="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="2" placeholder="{{ _trans('common.Enter permanent residential address...') }}">{{ old('permanent_address', $detail?->permanent_address) }}</textarea>
                                    @error('permanent_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 1 Actions: Save & Update -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">{{ _trans('common.Cancel') }}</a>
                                <button type="submit" name="next_step" value="job" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-4 py-2">
                                    <span>{{ _trans('common.Save & Next: Job Details') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 2. JOB & EMPLOYMENT -->
                    <div class="tab-pane fade {{ $activeTab === 'job' ? 'show active' : '' }}" id="job" role="tabpanel">
                        <x-ui.card :title="_trans('common.Step 2: Employment Information')" icon="bi-briefcase">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="form_department_id" class="form-label fw-semibold">{{ _trans('common.Department') }}</label>
                                    <select name="department_id" id="form_department_id" class="form-select @error('department_id') is-invalid @enderror" onchange="loadDesignations(this.value)">
                                        <option value="">{{ _trans('common.Select Department') }}</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $detail?->department_id) == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="form_designation_id" class="form-label fw-semibold">{{ _trans('common.Designation') }}</label>
                                    <select name="designation_id" id="form_designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Designation') }}</option>
                                        @foreach ($designations as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id', $detail?->designation_id) == $desig->id ? 'selected' : '' }}>
                                                {{ $desig->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="shift_id" class="form-label fw-semibold">{{ _trans('common.Shift') }}</label>
                                    <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Shift') }}</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id', $detail?->shift_id) == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ formatTime($shift->start_time) }} - {{ formatTime($shift->end_time) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="manager_id" class="form-label fw-semibold">{{ _trans('common.Reporting Manager') }}</label>
                                    <select name="manager_id" id="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Manager') }}</option>
                                        @foreach ($managers as $mgr)
                                            <option value="{{ $mgr->id }}" {{ old('manager_id', $detail?->manager_id) == $mgr->id ? 'selected' : '' }}>
                                                {{ $mgr->name }} ({{ $mgr->emp_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="joining_date" class="form-label fw-semibold">{{ _trans('common.Joining Date') }}</label>
                                    <input type="date"
                                        name="joining_date"
                                        id="joining_date"
                                        class="form-control @error('joining_date') is-invalid @enderror"
                                        value="{{ old('joining_date', $detail?->joining_date?->format('Y-m-d')) }}">
                                    @error('joining_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="confirmation_date" class="form-label fw-semibold">{{ _trans('common.Confirmation Date') }}</label>
                                    <input type="date"
                                        name="confirmation_date"
                                        id="confirmation_date"
                                        class="form-control @error('confirmation_date') is-invalid @enderror"
                                        value="{{ old('confirmation_date', $detail?->confirmation_date?->format('Y-m-d')) }}">
                                    @error('confirmation_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="employment_type" class="form-label fw-semibold">{{ _trans('common.Employment Type') }}</label>
                                    <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Employment Type') }}</option>
                                        @foreach ($employmentTypes as $type)
                                            <option value="{{ $type->value }}" {{ old('employment_type', $detail?->employment_type?->value ?? 'full_time') === $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employment_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }}</label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" {{ old('status', $employee->status?->value) === $status->value ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 2 Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="goToStep('account')">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>{{ _trans('common.Previous: Account') }}</span>
                                </button>
                                <button type="submit" name="next_step" value="salary" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-4 py-2">
                                    <span>{{ _trans('common.Save & Next: Salary') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 3. SALARY & COMPENSATION -->
                    <div class="tab-pane fade {{ $activeTab === 'salary' ? 'show active' : '' }}" id="salary" role="tabpanel">
                        <x-ui.card :title="_trans('common.Step 3: Salary & Compensation')" icon="bi-cash-coin">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="basic_salary" class="form-label fw-semibold">{{ _trans('common.Basic Salary') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">$</span>
                                        <input type="number"
                                            name="basic_salary"
                                            id="basic_salary"
                                            step="0.01"
                                            min="0"
                                            class="form-control @error('basic_salary') is-invalid @enderror"
                                            placeholder="0.00"
                                            value="{{ old('basic_salary', $detail?->basic_salary) }}">
                                        @error('basic_salary')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ _trans('common.Base monthly gross / basic remuneration for payroll calculation.') }}</small>
                                </div>
                            </div>

                            <!-- Step 3 Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="goToStep('job')">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>{{ _trans('common.Previous: Job') }}</span>
                                </button>
                                <button type="submit" name="next_step" value="bank" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-4 py-2">
                                    <span>{{ _trans('common.Save & Next: Bank Details') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 4. BANK DETAILS -->
                    <div class="tab-pane fade {{ $activeTab === 'bank' ? 'show active' : '' }}" id="bank" role="tabpanel">
                        @php $primaryBank = $employee->primaryBankAccount; @endphp
                        <x-ui.card :title="_trans('common.Step 4: Primary Bank Account')" icon="bi-bank">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="bank" class="form-label fw-semibold">{{ _trans('common.Bank Name') }}</label>
                                    <input type="text"
                                        name="bank"
                                        id="bank"
                                        class="form-control @error('bank') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Standard Chartered Bank') }}"
                                        value="{{ old('bank', $primaryBank?->bank) }}">
                                    @error('bank')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="branch" class="form-label fw-semibold">{{ _trans('common.Branch Name') }}</label>
                                    <input type="text"
                                        name="branch"
                                        id="branch"
                                        class="form-control @error('branch') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Main Branch') }}"
                                        value="{{ old('branch', $primaryBank?->branch) }}">
                                    @error('branch')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="account_name" class="form-label fw-semibold">{{ _trans('common.Account Holder Name') }}</label>
                                    <input type="text"
                                        name="account_name"
                                        id="account_name"
                                        class="form-control @error('account_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. John Doe') }}"
                                        value="{{ old('account_name', $primaryBank?->account_name) }}">
                                    @error('account_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="account_no" class="form-label fw-semibold">{{ _trans('common.Account Number') }}</label>
                                    <input type="text"
                                        name="account_no"
                                        id="account_no"
                                        class="form-control @error('account_no') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. 15012034567890') }}"
                                        value="{{ old('account_no', $primaryBank?->account_no) }}">
                                    @error('account_no')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="routing_number" class="form-label fw-semibold">{{ _trans('common.Routing Number') }}</label>
                                    <input type="text"
                                        name="routing_number"
                                        id="routing_number"
                                        class="form-control @error('routing_number') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. 020261234') }}"
                                        value="{{ old('routing_number', $primaryBank?->routing_number) }}">
                                    @error('routing_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="swift_code" class="form-label fw-semibold">{{ _trans('common.SWIFT / BIC Code') }}</label>
                                    <input type="text"
                                        name="swift_code"
                                        id="swift_code"
                                        class="form-control @error('swift_code') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. SCBLBDDX') }}"
                                        value="{{ old('swift_code', $primaryBank?->swift_code) }}">
                                    @error('swift_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 4 Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="goToStep('salary')">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>{{ _trans('common.Previous: Salary') }}</span>
                                </button>
                                <button type="submit" name="next_step" value="emergency" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-4 py-2">
                                    <span>{{ _trans('common.Save & Next: Emergency Contact') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 5. EMERGENCY CONTACT -->
                    <div class="tab-pane fade {{ $activeTab === 'emergency' ? 'show active' : '' }}" id="emergency" role="tabpanel">
                        @php $contact = $employee->emergencyContacts->first(); @endphp
                        <x-ui.card :title="_trans('common.Step 5: Emergency Contact')" icon="bi-telephone-plus">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="emergency_name" class="form-label fw-semibold">{{ _trans('common.Contact Name') }}</label>
                                    <input type="text"
                                        name="emergency_name"
                                        id="emergency_name"
                                        class="form-control @error('emergency_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Jane Doe') }}"
                                        value="{{ old('emergency_name', $contact?->name) }}">
                                    @error('emergency_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_relationship" class="form-label fw-semibold">{{ _trans('common.Relationship') }}</label>
                                    <input type="text"
                                        name="emergency_relationship"
                                        id="emergency_relationship"
                                        class="form-control @error('emergency_relationship') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Spouse, Father, Sister') }}"
                                        value="{{ old('emergency_relationship', $contact?->relationship) }}">
                                    @error('emergency_relationship')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_phone" class="form-label fw-semibold">{{ _trans('common.Primary Phone') }}</label>
                                    <input type="text"
                                        name="emergency_phone"
                                        id="emergency_phone"
                                        class="form-control @error('emergency_phone') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. +1 234 567 8901') }}"
                                        value="{{ old('emergency_phone', $contact?->phone) }}">
                                    @error('emergency_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_alt_phone" class="form-label fw-semibold">{{ _trans('common.Alternative Phone') }}</label>
                                    <input type="text"
                                        name="emergency_alt_phone"
                                        id="emergency_alt_phone"
                                        class="form-control @error('emergency_alt_phone') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. +1 234 567 8902') }}"
                                        value="{{ old('emergency_alt_phone', $contact?->alt_phone) }}">
                                    @error('emergency_alt_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="emergency_address" class="form-label fw-semibold">{{ _trans('common.Contact Address') }}</label>
                                    <textarea name="emergency_address" id="emergency_address" class="form-control @error('emergency_address') is-invalid @enderror" rows="2" placeholder="{{ _trans('common.Enter emergency contact address...') }}">{{ old('emergency_address', $contact?->address) }}</textarea>
                                    @error('emergency_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Step 5 Actions (Complete & Save) -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="goToStep('bank')">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>{{ _trans('common.Previous: Bank') }}</span>
                                </button>
                                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm px-4 py-2">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>{{ _trans('common.Complete & Save Employee') }}</span>
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

    function loadDesignations(departmentId) {
        const desigSelect = document.getElementById('form_designation_id');
        desigSelect.innerHTML = '<option value="">{{ _trans('common.Loading...') }}</option>';

        if (!departmentId) {
            desigSelect.innerHTML = '<option value="">{{ _trans('common.Select Designation') }}</option>';
            return;
        }

        fetch('/admin/ajax/designations/' + departmentId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans('common.Select Designation') }}</option>';
                data.forEach(desig => {
                    options += `<option value="${desig.id}">${desig.name}</option>`;
                });
                desigSelect.innerHTML = options;
            })
            .catch(() => {
                desigSelect.innerHTML = '<option value="">{{ _trans('common.Select Designation') }}</option>';
            });
    }

    function goToStep(stepId) {
        const trigger = document.querySelector(`[data-bs-target="#${stepId}"]`);
        if (trigger) {
            const tab = bootstrap.Tab.getOrCreateInstance(trigger);
            tab.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('employeeEditForm');
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
        }
    });
</script>
@endpush
