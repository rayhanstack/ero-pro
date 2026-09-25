@extends('admin.layouts.app')
@section('title', $employee->full_name . ' (' . $employee->emp_code . ')')

@section('content')
    <x-ui.page-header
        title="{{ $employee->full_name }}"
        subtitle="{{ $employee->designation?->name ?? _trans('common.Employee') }} &bull; {{ $employee->department?->name ?? _trans('common.Department') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Employees'), 'url' => route('employees.index')],
            ['label' => $employee->full_name],
        ]"
    >
        <x-slot:actions>
            @can('employee.edit')
                <button type="button" class="btn btn-outline-warning d-inline-flex align-items-center gap-1"
                    onclick="openStatusModal({{ $employee->id }}, '{{ $employee->full_name }}', '{{ $employee->status->value }}')">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>{{ _trans('common.Change Status') }}</span>
                </button>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i>
                    <span>{{ _trans('common.Edit Profile') }}</span>
                </a>
            @endcan
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Profile Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-auto">
                    <img src="{{ $employee->avatar_url }}"
                        alt="{{ $employee->full_name }}"
                        class="rounded-circle object-fit-cover shadow-sm border border-3 border-primary-subtle"
                        width="100" height="100">
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="mb-0 fw-bold text-dark">{{ $employee->full_name }}</h4>
                        <span class="badge bg-light text-dark border font-monospace">{{ $employee->emp_code }}</span>
                        <span class="{{ $employee->status->badgeClass() }}">
                            {{ $employee->status->label() }}
                        </span>
                        <span class="{{ $employee->employment_type->badgeClass() }}">
                            {{ $employee->employment_type->label() }}
                        </span>
                    </div>
                    <p class="text-muted mb-3 fs-6">
                        <span class="fw-semibold text-dark">{{ $employee->designation?->name ?? '-' }}</span>
                        <span class="mx-2">&bull;</span>
                        <span>{{ $employee->department?->name ?? '-' }}</span>
                    </p>
                    <div class="d-flex flex-wrap gap-4 text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope text-primary"></i>
                            <span>{{ $employee->email }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-telephone text-primary"></i>
                            <span>{{ $employee->phone ?: '-' }}</span>
                        </div>
                        @if ($employee->manager)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-check text-primary"></i>
                                <span>{{ _trans('common.Reports to:') }} <strong>{{ $employee->manager->full_name }}</strong></span>
                            </div>
                        @endif
                        @if ($employee->shift)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary"></i>
                                <span>{{ $employee->shift->name }} ({{ formatTime($employee->shift->start_time) }} - {{ formatTime($employee->shift->end_time) }})</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">{{ _trans('common.Joining Date') }}</small>
                    <h6 class="mb-0 fw-bold">{{ formatDate($employee->joining_date) }}</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">{{ _trans('common.Tenure / Experience') }}</small>
                    <h6 class="mb-0 fw-bold">{{ $employee->joining_date ? $employee->joining_date->diffForHumans(['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) : '-' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">{{ _trans('common.Basic Salary') }}</small>
                    <h6 class="mb-0 fw-bold text-success">${{ number_format($employee->basic_salary, 2) }}</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">{{ _trans('common.Direct Reports') }}</small>
                    <h6 class="mb-0 fw-bold text-primary">{{ $employee->subordinates()->count() }} {{ _trans('common.Team Members') }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <ul class="nav nav-tabs border-bottom-0 gap-2" id="employeeDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold d-flex align-items-center gap-2" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                        <i class="bi bi-grid"></i>
                        <span>{{ _trans('common.Overview') }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold d-flex align-items-center gap-2" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                        <i class="bi bi-folder2-open"></i>
                        <span>{{ _trans('common.Documents') }} ({{ $employee->documents->count() }})</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold d-flex align-items-center gap-2" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
                        <i class="bi bi-calendar-check"></i>
                        <span>{{ _trans('common.Attendance') }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold d-flex align-items-center gap-2" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab">
                        <i class="bi bi-calendar-range"></i>
                        <span>{{ _trans('common.Leave') }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold d-flex align-items-center gap-2" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab">
                        <i class="bi bi-receipt"></i>
                        <span>{{ _trans('common.Payroll') }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="employeeDetailTabContent">
        <!-- 1. OVERVIEW TAB -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                <!-- Personal & Address Info -->
                <div class="col-lg-6">
                    <x-ui.card :title="_trans('common.Personal Details')" icon="bi-person" class="h-100">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted py-2" style="width: 35%;">{{ _trans('common.Date of Birth') }}</td>
                                    <td class="fw-medium py-2">{{ formatDate($employee->dob) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Gender') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->gender->label() }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Marital Status') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->marital_status ? $employee->marital_status->label() : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Blood Group') }}</td>
                                    <td class="fw-medium py-2">
                                        @if ($employee->blood_group)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">{{ $employee->blood_group->label() }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.National ID / Passport') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->nid ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Country / Location') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->city?->name ?? '' }}{{ $employee->city ? ', ' : '' }}{{ $employee->state?->name ?? '' }}{{ $employee->state ? ', ' : '' }}{{ $employee->country?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Present Address') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->present_address ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Permanent Address') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->permanent_address ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </x-ui.card>
                </div>

                <!-- Employment & System Account -->
                <div class="col-lg-6">
                    <x-ui.card :title="_trans('common.Employment & Account Details')" icon="bi-briefcase" class="h-100">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted py-2" style="width: 35%;">{{ _trans('common.Department') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->department?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Designation') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->designation?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Shift Timing') }}</td>
                                    <td class="fw-medium py-2">{{ $employee->shift?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Employment Type') }}</td>
                                    <td class="fw-medium py-2">
                                        <span class="{{ $employee->employment_type->badgeClass() }}">{{ $employee->employment_type->label() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Joining Date') }}</td>
                                    <td class="fw-medium py-2">{{ formatDate($employee->joining_date) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Confirmation Date') }}</td>
                                    <td class="fw-medium py-2">{{ formatDate($employee->confirmation_date) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2">{{ _trans('common.Portal User Account') }}</td>
                                    <td class="fw-medium py-2">
                                        @if ($employee->user)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle me-2">{{ _trans('common.Linked') }}</span>
                                            <small class="text-muted">({{ $employee->user->role_name }})</small>
                                        @else
                                            <span class="text-muted small">{{ _trans('common.No linked user account') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </x-ui.card>
                </div>

                <!-- Primary Bank Account -->
                <div class="col-lg-6">
                    <x-ui.card :title="_trans('common.Bank Account Details')" icon="bi-bank" class="h-100">
                        @php $primaryBank = $employee->primaryBankAccount; @endphp
                        @if ($primaryBank)
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted py-2" style="width: 35%;">{{ _trans('common.Bank Name') }}</td>
                                        <td class="fw-bold text-dark py-2">{{ $primaryBank->bank }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Branch Name') }}</td>
                                        <td class="fw-medium py-2">{{ $primaryBank->branch ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Account Holder') }}</td>
                                        <td class="fw-medium py-2">{{ $primaryBank->account_name ?: $employee->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Account Number') }}</td>
                                        <td class="font-monospace fw-bold text-dark py-2">{{ $primaryBank->account_no }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Routing Number') }}</td>
                                        <td class="font-monospace py-2">{{ $primaryBank->routing_number ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.SWIFT Code') }}</td>
                                        <td class="font-monospace py-2">{{ $primaryBank->swift_code ?: '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bank fs-2 text-muted"></i>
                                <p class="mb-0 mt-2 small">{{ _trans('common.No bank account details provided.') }}</p>
                            </div>
                        @endif
                    </x-ui.card>
                </div>

                <!-- Emergency Contacts -->
                <div class="col-lg-6">
                    <x-ui.card :title="_trans('common.Emergency Contacts')" icon="bi-telephone-plus" class="h-100">
                        @php $contact = $employee->emergencyContacts->first(); @endphp
                        @if ($contact)
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted py-2" style="width: 35%;">{{ _trans('common.Contact Name') }}</td>
                                        <td class="fw-bold text-dark py-2">{{ $contact->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Relationship') }}</td>
                                        <td class="fw-medium py-2">{{ $contact->relationship }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Primary Phone') }}</td>
                                        <td class="fw-medium py-2">
                                            <a href="tel:{{ $contact->phone }}" class="text-decoration-none">{{ $contact->phone }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Alternative Phone') }}</td>
                                        <td class="fw-medium py-2">{{ $contact->alt_phone ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ _trans('common.Address') }}</td>
                                        <td class="fw-medium py-2">{{ $contact->address ?: '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-telephone fs-2 text-muted"></i>
                                <p class="mb-0 mt-2 small">{{ _trans('common.No emergency contact details provided.') }}</p>
                            </div>
                        @endif
                    </x-ui.card>
                </div>
            </div>
        </div>

        <!-- 2. DOCUMENTS TAB -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <x-ui.card :title="_trans('common.Employee Documents')" icon="bi-folder2-open">
                <x-slot:actions>
                    @can('employee.edit')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <i class="bi bi-upload"></i>
                            <span>{{ _trans('common.Upload Document') }}</span>
                        </button>
                    @endcan
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ _trans('common.Document Title') }}</th>
                                <th>{{ _trans('common.Expiry Date') }}</th>
                                <th>{{ _trans('common.Uploaded At') }}</th>
                                <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employee->documents as $doc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                            <span class="fw-semibold text-dark">{{ $doc->title }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($doc->expiry_date)
                                            <span class="badge {{ $doc->expiry_date->isPast() ? 'bg-danger-subtle text-danger' : 'bg-light text-dark' }} border">
                                                {{ formatDate($doc->expiry_date) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">{{ _trans('common.No Expiry') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ formatDate($doc->created_at) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('employees.documents.download', $doc) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Download') }}">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @can('employee.edit')
                                                <form method="POST" action="{{ route('employees.documents.destroy', $doc) }}" onsubmit="return confirm('{{ _trans('common.Delete this document?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ _trans('common.Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                        <p class="mt-2 mb-0">{{ _trans('common.No documents uploaded for this employee yet.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <!-- 3. ATTENDANCE TAB (PLACEHOLDER) -->
        <div class="tab-pane fade" id="attendance" role="tabpanel">
            <x-ui.card :title="_trans('common.Attendance Logs & Summary')" icon="bi-calendar-check">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">{{ _trans('common.Present Days') }}</small>
                            <h4 class="fw-bold text-success mb-0">21</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">{{ _trans('common.Late Arrivals') }}</small>
                            <h4 class="fw-bold text-warning mb-0">2</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">{{ _trans('common.Absents') }}</small>
                            <h4 class="fw-bold text-danger mb-0">0</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">{{ _trans('common.Overtime Hours') }}</small>
                            <h4 class="fw-bold text-primary mb-0">4.5 hrs</h4>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-info-circle fs-5"></i>
                    <span>{{ _trans('common.Daily attendance check-ins, biometric synchronization, and shift timings will automatically populate here.') }}</span>
                </div>
            </x-ui.card>
        </div>

        <!-- 4. LEAVE TAB (PLACEHOLDER) -->
        <div class="tab-pane fade" id="leave" role="tabpanel">
            <x-ui.card :title="_trans('common.Leave Balances & Applications')" icon="bi-calendar-range">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border bg-light">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1">{{ _trans('common.Annual Leave') }}</h6>
                                <p class="text-muted small mb-2">{{ _trans('common.14 days allocated') }}</p>
                                <div class="d-flex justify-content-between">
                                    <span class="text-success fw-semibold">10 {{ _trans('common.Available') }}</span>
                                    <span class="text-muted">4 {{ _trans('common.Used') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border bg-light">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1">{{ _trans('common.Casual Leave') }}</h6>
                                <p class="text-muted small mb-2">{{ _trans('common.10 days allocated') }}</p>
                                <div class="d-flex justify-content-between">
                                    <span class="text-success fw-semibold">8 {{ _trans('common.Available') }}</span>
                                    <span class="text-muted">2 {{ _trans('common.Used') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border bg-light">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1">{{ _trans('common.Sick Leave') }}</h6>
                                <p class="text-muted small mb-2">{{ _trans('common.14 days allocated') }}</p>
                                <div class="d-flex justify-content-between">
                                    <span class="text-success fw-semibold">14 {{ _trans('common.Available') }}</span>
                                    <span class="text-muted">0 {{ _trans('common.Used') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-info-circle fs-5"></i>
                    <span>{{ _trans('common.Employee leave requests, approvals, and carry-forward balances will link seamlessly in this section.') }}</span>
                </div>
            </x-ui.card>
        </div>

        <!-- 5. PAYROLL TAB (PLACEHOLDER) -->
        <div class="tab-pane fade" id="payroll" role="tabpanel">
            <x-ui.card :title="_trans('common.Salary Structure & Payslips')" icon="bi-receipt">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold mb-3">{{ _trans('common.Compensation Overview') }}</h6>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ _trans('common.Basic Salary') }}</span>
                                <span class="fw-bold">${{ number_format($employee->basic_salary, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ _trans('common.House Rent Allowance (50%)') }}</span>
                                <span class="fw-medium">${{ number_format($employee->basic_salary * 0.5, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ _trans('common.Medical Allowance (10%)') }}</span>
                                <span class="fw-medium">${{ number_format($employee->basic_salary * 0.1, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 fw-bold text-success fs-6">
                                <span>{{ _trans('common.Gross Monthly Pay') }}</span>
                                <span>${{ number_format($employee->basic_salary * 1.6, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-center text-center">
                            <i class="bi bi-file-earmark-check text-primary fs-1 mb-2"></i>
                            <h6 class="fw-bold">{{ _trans('common.Monthly Payslips') }}</h6>
                            <p class="text-muted small mb-0">{{ _trans('common.Generated monthly payslips with tax, provident fund, and bonus calculations will appear here.') }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ _trans('common.Upload New Document') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modal_doc_title" class="form-label fw-semibold">{{ _trans('common.Document Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="modal_doc_title" class="form-control" placeholder="{{ _trans('common.e.g. Passport, Academic Degree') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="modal_doc_expiry" class="form-label fw-semibold">{{ _trans('common.Expiry Date (Optional)') }}</label>
                            <input type="date" name="expiry_date" id="modal_doc_expiry" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="modal_doc_file" class="form-label fw-semibold">{{ _trans('common.File Attachment') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="modal_doc_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            <small class="text-muted">{{ _trans('common.Max size: 5MB (PDF, PNG, JPG, DOCX)') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ _trans('common.Upload') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="statusForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ _trans('common.Change Employee Status') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            {{ _trans('common.Update the current employment status for') }} <strong id="statusEmployeeName"></strong>.
                        </p>
                        <div class="mb-3">
                            <label for="modal_status" class="form-label fw-semibold">{{ _trans('common.Status') }}</label>
                            <select name="status" id="modal_status" class="form-select" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ _trans('common.Update Status') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openStatusModal(employeeId, employeeName, currentStatus) {
        document.getElementById('statusEmployeeName').textContent = employeeName;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('statusForm').action = '/employees/' + employeeId + '/status';
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }
</script>
@endpush
