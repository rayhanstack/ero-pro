@extends('admin.layouts.app')
@section('title', _trans('common.Add Employee'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Add New Employee') }}"
        subtitle="{{ _trans('common.Fill in employee profile, employment, salary, bank, and emergency information') }}"
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

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="needs-validation">
        @csrf

        <div class="row g-4">
            <div class="col-lg-12">
                <!-- Navigation Tabs -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-2">
                        <ul class="nav nav-pills nav-fill gap-2" id="employeeFormTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center justify-content-center gap-2 py-2" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person"></i>
                                    <span>{{ _trans('common.Personal Details') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab">
                                    <i class="bi bi-briefcase"></i>
                                    <span>{{ _trans('common.Job & Employment') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                                    <i class="bi bi-shield-lock"></i>
                                    <span>{{ _trans('common.User Account') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>{{ _trans('common.Salary') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                                    <i class="bi bi-bank"></i>
                                    <span>{{ _trans('common.Bank Details') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab">
                                    <i class="bi bi-telephone-plus"></i>
                                    <span>{{ _trans('common.Emergency Contact') }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center justify-content-center gap-2 py-2" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>{{ _trans('common.Documents') }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="tab-content" id="employeeFormTabContent">
                    <!-- 1. PERSONAL DETAILS -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <x-ui.card :title="_trans('common.Personal Information')" icon="bi-person-badge">
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
                                                accept="image/*"
                                                onchange="previewAvatar(this)">
                                            <small class="text-muted d-block mt-1">{{ _trans('common.Supported formats: JPG, PNG, WEBP. Max size: 2MB') }}</small>
                                            @error('avatar')
                                                <div class="invalid-feedback">{{ $message }}</div>
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
                                        value="{{ old('first_name') }}"
                                        required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-semibold">{{ _trans('common.Last Name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="last_name"
                                        id="last_name"
                                        class="form-control @error('last_name') is-invalid @enderror"
                                        value="{{ old('last_name') }}"
                                        required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">{{ _trans('common.Email Address') }} <span class="text-danger">*</span></label>
                                    <input type="email"
                                        name="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}"
                                        required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">{{ _trans('common.Phone Number') }}</label>
                                    <input type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="dob" class="form-label fw-semibold">{{ _trans('common.Date of Birth') }}</label>
                                    <input type="date"
                                        name="dob"
                                        id="dob"
                                        class="form-control @error('dob') is-invalid @enderror"
                                        value="{{ old('dob') }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label fw-semibold">{{ _trans('common.Gender') }} <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender->value }}" {{ old('gender', 'male') === $gender->value ? 'selected' : '' }}>
                                                {{ $gender->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="nid" class="form-label fw-semibold">{{ _trans('common.National ID (NID) / Passport') }}</label>
                                    <input type="text"
                                        name="nid"
                                        id="nid"
                                        class="form-control @error('nid') is-invalid @enderror"
                                        value="{{ old('nid') }}">
                                    @error('nid')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="state_id" class="form-label fw-semibold">{{ _trans('common.State / Division') }}</label>
                                    <select name="state_id" id="state_id" class="form-select @error('state_id') is-invalid @enderror" onchange="loadCities(this.value)">
                                        <option value="">{{ _trans('common.Select State') }}</option>
                                    </select>
                                    @error('state_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="city_id" class="form-label fw-semibold">{{ _trans('common.City') }}</label>
                                    <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select City') }}</option>
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="present_address" class="form-label fw-semibold">{{ _trans('common.Present Address') }}</label>
                                    <textarea name="present_address" id="present_address" class="form-control @error('present_address') is-invalid @enderror" rows="2">{{ old('present_address') }}</textarea>
                                    @error('present_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="permanent_address" class="form-label fw-semibold">{{ _trans('common.Permanent Address') }}</label>
                                    <textarea name="permanent_address" id="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="2">{{ old('permanent_address') }}</textarea>
                                    @error('permanent_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 2. JOB & EMPLOYMENT -->
                    <div class="tab-pane fade" id="job" role="tabpanel">
                        <x-ui.card :title="_trans('common.Employment Information')" icon="bi-briefcase">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="department_id" class="form-label fw-semibold">{{ _trans('common.Department') }} <span class="text-danger">*</span></label>
                                    <select name="department_id" id="form_department_id" class="form-select @error('department_id') is-invalid @enderror" required onchange="loadDesignations(this.value)">
                                        <option value="">{{ _trans('common.Select Department') }}</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="designation_id" class="form-label fw-semibold">{{ _trans('common.Designation') }} <span class="text-danger">*</span></label>
                                    <select name="designation_id" id="form_designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                                        <option value="">{{ _trans('common.Select Designation') }}</option>
                                        @foreach ($designations as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>
                                                {{ $desig->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="shift_id" class="form-label fw-semibold">{{ _trans('common.Shift') }}</label>
                                    <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Shift') }}</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ formatTime($shift->start_time) }} - {{ formatTime($shift->end_time) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="manager_id" class="form-label fw-semibold">{{ _trans('common.Reporting Manager') }}</label>
                                    <select name="manager_id" id="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                                        <option value="">{{ _trans('common.Select Manager') }}</option>
                                        @foreach ($managers as $mgr)
                                            <option value="{{ $mgr->id }}" {{ old('manager_id') == $mgr->id ? 'selected' : '' }}>
                                                {{ $mgr->full_name }} ({{ $mgr->emp_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="joining_date" class="form-label fw-semibold">{{ _trans('common.Joining Date') }} <span class="text-danger">*</span></label>
                                    <input type="date"
                                        name="joining_date"
                                        id="joining_date"
                                        class="form-control @error('joining_date') is-invalid @enderror"
                                        value="{{ old('joining_date', date('Y-m-d')) }}"
                                        required>
                                    @error('joining_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="confirmation_date" class="form-label fw-semibold">{{ _trans('common.Confirmation Date') }}</label>
                                    <input type="date"
                                        name="confirmation_date"
                                        id="confirmation_date"
                                        class="form-control @error('confirmation_date') is-invalid @enderror"
                                        value="{{ old('confirmation_date') }}">
                                    @error('confirmation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="employment_type" class="form-label fw-semibold">{{ _trans('common.Employment Type') }} <span class="text-danger">*</span></label>
                                    <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                                        @foreach ($employmentTypes as $type)
                                            <option value="{{ $type->value }}" {{ old('employment_type', 'full_time') === $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" {{ old('status', 'active') === $status->value ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 3. USER ACCOUNT (OPTIONAL) -->
                    <div class="tab-pane fade" id="account" role="tabpanel">
                        <x-ui.card :title="_trans('common.User Login Account Setup')" icon="bi-shield-lock">
                            <p class="text-muted small mb-4">
                                {{ _trans('common.Optionally generate a portal user login account for this employee. Credentials will be securely hashed and logged.') }}
                            </p>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch" name="create_user_account" id="create_user_account" value="1" {{ old('create_user_account') ? 'checked' : '' }} onchange="toggleAccountFields(this.checked)">
                                <label class="form-check-label fw-semibold" for="create_user_account">
                                    {{ _trans('common.Create Portal User Account') }}
                                </label>
                            </div>

                            <div id="accountFields" class="{{ old('create_user_account') ? '' : 'd-none' }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role" class="form-label fw-semibold">{{ _trans('common.Assign Role') }} <span class="text-danger">*</span></label>
                                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                                            <option value="">{{ _trans('common.Select Role') }}</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role', 'Employee') === $role->name ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="user_password" class="form-label fw-semibold">{{ _trans('common.Initial Password') }}</label>
                                        <input type="password"
                                            name="user_password"
                                            id="user_password"
                                            class="form-control @error('user_password') is-invalid @enderror"
                                            placeholder="{{ _trans('common.Leave blank to auto-generate') }}">
                                        <small class="text-muted">{{ _trans('common.If blank, a secure 10-character password will be auto-generated.') }}</small>
                                        @error('user_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 4. SALARY & COMPENSATION -->
                    <div class="tab-pane fade" id="salary" role="tabpanel">
                        <x-ui.card :title="_trans('common.Salary & Compensation')" icon="bi-cash-coin">
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
                                            value="{{ old('basic_salary') }}">
                                        @error('basic_salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ _trans('common.Base monthly gross / basic remuneration for payroll calculation.') }}</small>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 5. BANK DETAILS -->
                    <div class="tab-pane fade" id="bank" role="tabpanel">
                        <x-ui.card :title="_trans('common.Primary Bank Account')" icon="bi-bank">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="bank" class="form-label fw-semibold">{{ _trans('common.Bank Name') }}</label>
                                    <input type="text"
                                        name="bank"
                                        id="bank"
                                        class="form-control @error('bank') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. BRAC Bank PLC') }}"
                                        value="{{ old('bank') }}">
                                    @error('bank')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="branch" class="form-label fw-semibold">{{ _trans('common.Branch Name') }}</label>
                                    <input type="text"
                                        name="branch"
                                        id="branch"
                                        class="form-control @error('branch') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Gulshan Branch') }}"
                                        value="{{ old('branch') }}">
                                    @error('branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="account_name" class="form-label fw-semibold">{{ _trans('common.Account Holder Name') }}</label>
                                    <input type="text"
                                        name="account_name"
                                        id="account_name"
                                        class="form-control @error('account_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.Full name on bank account') }}"
                                        value="{{ old('account_name') }}">
                                    @error('account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="account_no" class="form-label fw-semibold">{{ _trans('common.Account Number') }}</label>
                                    <input type="text"
                                        name="account_no"
                                        id="account_no"
                                        class="form-control @error('account_no') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. 15012034567890') }}"
                                        value="{{ old('account_no') }}">
                                    @error('account_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="routing_number" class="form-label fw-semibold">{{ _trans('common.Routing Number') }}</label>
                                    <input type="text"
                                        name="routing_number"
                                        id="routing_number"
                                        class="form-control @error('routing_number') is-invalid @enderror"
                                        value="{{ old('routing_number') }}">
                                    @error('routing_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="swift_code" class="form-label fw-semibold">{{ _trans('common.SWIFT / BIC Code') }}</label>
                                    <input type="text"
                                        name="swift_code"
                                        id="swift_code"
                                        class="form-control @error('swift_code') is-invalid @enderror"
                                        value="{{ old('swift_code') }}">
                                    @error('swift_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 6. EMERGENCY CONTACT -->
                    <div class="tab-pane fade" id="emergency" role="tabpanel">
                        <x-ui.card :title="_trans('common.Emergency Contact')" icon="bi-telephone-plus">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="emergency_name" class="form-label fw-semibold">{{ _trans('common.Contact Name') }}</label>
                                    <input type="text"
                                        name="emergency_name"
                                        id="emergency_name"
                                        class="form-control @error('emergency_name') is-invalid @enderror"
                                        placeholder="{{ _trans('common.Relative or guardian name') }}"
                                        value="{{ old('emergency_name') }}">
                                    @error('emergency_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_relationship" class="form-label fw-semibold">{{ _trans('common.Relationship') }}</label>
                                    <input type="text"
                                        name="emergency_relationship"
                                        id="emergency_relationship"
                                        class="form-control @error('emergency_relationship') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. Spouse, Father, Sister') }}"
                                        value="{{ old('emergency_relationship') }}">
                                    @error('emergency_relationship')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_phone" class="form-label fw-semibold">{{ _trans('common.Primary Phone') }}</label>
                                    <input type="text"
                                        name="emergency_phone"
                                        id="emergency_phone"
                                        class="form-control @error('emergency_phone') is-invalid @enderror"
                                        value="{{ old('emergency_phone') }}">
                                    @error('emergency_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="emergency_alt_phone" class="form-label fw-semibold">{{ _trans('common.Alternative Phone') }}</label>
                                    <input type="text"
                                        name="emergency_alt_phone"
                                        id="emergency_alt_phone"
                                        class="form-control @error('emergency_alt_phone') is-invalid @enderror"
                                        value="{{ old('emergency_alt_phone') }}">
                                    @error('emergency_alt_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="emergency_address" class="form-label fw-semibold">{{ _trans('common.Contact Address') }}</label>
                                    <textarea name="emergency_address" id="emergency_address" class="form-control @error('emergency_address') is-invalid @enderror" rows="2">{{ old('emergency_address') }}</textarea>
                                    @error('emergency_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- 7. DOCUMENTS -->
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <x-ui.card :title="_trans('common.Initial Document Upload')" icon="bi-file-earmark-text">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="document_title" class="form-label fw-semibold">{{ _trans('common.Document Title') }}</label>
                                    <input type="text"
                                        name="document_title"
                                        id="document_title"
                                        class="form-control @error('document_title') is-invalid @enderror"
                                        placeholder="{{ _trans('common.e.g. National ID Copy, Contract') }}"
                                        value="{{ old('document_title') }}">
                                    @error('document_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="document_expiry_date" class="form-label fw-semibold">{{ _trans('common.Expiry Date') }}</label>
                                    <input type="date"
                                        name="document_expiry_date"
                                        id="document_expiry_date"
                                        class="form-control @error('document_expiry_date') is-invalid @enderror"
                                        value="{{ old('document_expiry_date') }}">
                                    @error('document_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="document_file" class="form-label fw-semibold">{{ _trans('common.Attachment File') }}</label>
                                    <input type="file"
                                        name="document_file"
                                        id="document_file"
                                        class="form-control @error('document_file') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="text-muted d-block mt-1">{{ _trans('common.Supported formats: PDF, JPG, PNG, DOC, DOCX. Max size: 5MB') }}</small>
                                    @error('document_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </x-ui.card>
                    </div>
                </div>

                <!-- Action Buttons Footer -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <a href="{{ route('employees.index') }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Employee') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleAccountFields(checked) {
        const fields = document.getElementById('accountFields');
        if (checked) {
            fields.classList.remove('d-none');
        } else {
            fields.classList.add('d-none');
        }
    }

    function loadStates(countryId) {
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        stateSelect.innerHTML = '<option value="">{{ _trans("common.Loading...") }}</option>';
        citySelect.innerHTML = '<option value="">{{ _trans("common.Select City") }}</option>';

        if (!countryId) {
            stateSelect.innerHTML = '<option value="">{{ _trans("common.Select State") }}</option>';
            return;
        }

        fetch('/admin/ajax/states/' + countryId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans("common.Select State") }}</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                stateSelect.innerHTML = options;
            })
            .catch(() => {
                stateSelect.innerHTML = '<option value="">{{ _trans("common.Select State") }}</option>';
            });
    }

    function loadCities(stateId) {
        const citySelect = document.getElementById('city_id');
        citySelect.innerHTML = '<option value="">{{ _trans("common.Loading...") }}</option>';

        if (!stateId) {
            citySelect.innerHTML = '<option value="">{{ _trans("common.Select City") }}</option>';
            return;
        }

        fetch('/admin/ajax/cities/' + stateId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans("common.Select City") }}</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                citySelect.innerHTML = options;
            })
            .catch(() => {
                citySelect.innerHTML = '<option value="">{{ _trans("common.Select City") }}</option>';
            });
    }

    function loadDesignations(departmentId) {
        const desigSelect = document.getElementById('form_designation_id');
        desigSelect.innerHTML = '<option value="">{{ _trans("common.Loading...") }}</option>';

        if (!departmentId) {
            desigSelect.innerHTML = '<option value="">{{ _trans("common.Select Designation") }}</option>';
            return;
        }

        fetch('/admin/ajax/designations/' + departmentId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">{{ _trans("common.Select Designation") }}</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                desigSelect.innerHTML = options;
            })
            .catch(() => {
                desigSelect.innerHTML = '<option value="">{{ _trans("common.Select Designation") }}</option>';
            });
    }
</script>
@endpush
