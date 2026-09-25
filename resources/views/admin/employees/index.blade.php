@extends('admin.layouts.app')
@section('title', _trans('common.Employees'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Employee Management') }}"
        subtitle="{{ _trans('common.Manage your workforce, profiles, employment details, and documents') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Employees')],
        ]"
    >
        <x-slot:actions>
            @can('employee.create')
                <a href="{{ route('employees.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Employee') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <!-- KPI Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="text-muted small fw-medium mb-1">{{ _trans('common.Total Employees') }}</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="text-muted small fw-medium mb-1">{{ _trans('common.Active Employees') }}</p>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['active']) }}</h4>
                    </div>
                    <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="text-muted small fw-medium mb-1">{{ _trans('common.On Leave') }}</p>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['on_leave']) }}</h4>
                    </div>
                    <div class="bg-warning-subtle text-warning rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-dash fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="text-muted small fw-medium mb-1">{{ _trans('common.New This Month') }}</p>
                        <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['new_this_month']) }}</h4>
                    </div>
                    <div class="bg-info-subtle text-info rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-plus fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3 align-items-end">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <div class="col-md-3">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control border-start-0"
                        placeholder="{{ _trans('common.Name, code, email, phone...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label for="department_id" class="form-label small fw-semibold text-muted">{{ _trans('common.Department') }}</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">{{ _trans('common.All Departments') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="designation_id" class="form-label small fw-semibold text-muted">{{ _trans('common.Designation') }}</label>
                <select name="designation_id" id="designation_id" class="form-select">
                    <option value="">{{ _trans('common.All Designations') }}</option>
                    @foreach ($designations as $desig)
                        <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>
                            {{ $desig->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label small fw-semibold text-muted">{{ _trans('common.Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ _trans('common.All Statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 justify-content-between">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i>
                        <span>{{ _trans('common.Filter') }}</span>
                    </button>
                    @if (request()->hasAny(['search', 'department_id', 'designation_id', 'status', 'employment_type']))
                        <a href="{{ route('employees.index', ['view' => $viewMode]) }}" class="btn btn-light" title="{{ _trans('common.Reset Filters') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>

                <!-- View Switcher -->
                <div class="btn-group" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                        class="btn {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-secondary' }}"
                        title="{{ _trans('common.Table View') }}">
                        <i class="bi bi-list-ul"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                        class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}"
                        title="{{ _trans('common.Grid View') }}">
                        <i class="bi bi-grid-fill"></i>
                    </a>
                </div>
            </div>
        </form>
    </x-ui.card>

    <!-- Employees List -->
    @if ($viewMode === 'grid')
        <!-- GRID VIEW -->
        <div class="row g-3">
            @forelse ($employees as $employee)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 hover-shadow transition-all">
                        <div class="card-body p-3 text-center">
                            <!-- Dropdown Actions -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark border font-monospace small">{{ $employee->emp_code }}</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        @can('employee.view')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('employees.show', $employee) }}">
                                                    <i class="bi bi-eye text-info me-2"></i>{{ _trans('common.View Profile') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('employee.edit')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('employees.edit', $employee) }}">
                                                    <i class="bi bi-pencil text-primary me-2"></i>{{ _trans('common.Edit Employee') }}
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item"
                                                    onclick="openStatusModal({{ $employee->id }}, '{{ $employee->full_name }}', '{{ $employee->status->value }}')">
                                                    <i class="bi bi-arrow-repeat text-warning me-2"></i>{{ _trans('common.Change Status') }}
                                                </button>
                                            </li>
                                        @endcan
                                        @can('employee.delete')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDeleteModal"
                                                    data-action="{{ route('employees.destroy', $employee) }}"
                                                    data-item-name="{{ $employee->full_name }}">
                                                    <i class="bi bi-trash me-2"></i>{{ _trans('common.Delete') }}
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>

                            <!-- Avatar -->
                            <div class="position-relative d-inline-block mb-3">
                                <img src="{{ $employee->avatar_url }}"
                                    alt="{{ $employee->full_name }}"
                                    class="rounded-circle object-fit-cover shadow-sm border border-2 border-white"
                                    width="75" height="75">
                            </div>

                            <!-- Name & Designation -->
                            <h6 class="fw-bold mb-1 text-truncate">
                                <a href="{{ route('employees.show', $employee) }}" class="text-dark text-decoration-none">
                                    {{ $employee->full_name }}
                                </a>
                            </h6>
                            <p class="text-muted small mb-2 text-truncate">{{ $employee->designation?->name ?? '-' }}</p>

                            <!-- Department Badge -->
                            <div class="mb-3">
                                <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 small">
                                    {{ $employee->department?->name ?? '-' }}
                                </span>
                            </div>

                            <!-- Status Badge -->
                            <div class="mb-3">
                                <span class="{{ $employee->status->badgeClass() }}">
                                    {{ $employee->status->label() }}
                                </span>
                            </div>

                            <!-- Contact Info -->
                            <div class="border-top pt-2 text-start small text-muted">
                                <div class="d-flex align-items-center gap-2 mb-1 text-truncate">
                                    <i class="bi bi-envelope text-secondary"></i>
                                    <span class="text-truncate">{{ $employee->email }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <i class="bi bi-telephone text-secondary"></i>
                                    <span>{{ $employee->phone ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <x-ui.card class="text-center py-5">
                        <i class="bi bi-people text-muted display-4"></i>
                        <h5 class="mt-3 text-muted">{{ _trans('common.No employees found') }}</h5>
                        <p class="text-muted small mb-3">{{ _trans('common.Try adjusting your search filters or create a new employee.') }}</p>
                        @can('employee.create')
                            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Add Employee') }}
                            </a>
                        @endcan
                    </x-ui.card>
                </div>
            @endforelse
        </div>
    @else
        <!-- TABLE VIEW -->
        <x-ui.card :noPadding="true">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Employee') }}</th>
                            <th>{{ _trans('common.Code') }}</th>
                            <th>{{ _trans('common.Department & Designation') }}</th>
                            <th>{{ _trans('common.Shift') }}</th>
                            <th>{{ _trans('common.Type') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Joining Date') }}</th>
                            <th class="pe-4 text-end">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $employee->avatar_url }}"
                                            alt="{{ $employee->full_name }}"
                                            class="rounded-circle object-fit-cover flex-shrink-0"
                                            width="40" height="40">
                                        <div class="min-w-0">
                                            <a href="{{ route('employees.show', $employee) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate">
                                                {{ $employee->full_name }}
                                            </a>
                                            <small class="text-muted text-truncate d-block">{{ $employee->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $employee->emp_code }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark d-block">{{ $employee->designation?->name ?? '-' }}</span>
                                    <small class="text-muted">{{ $employee->department?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ $employee->shift?->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="{{ $employee->employment_type->badgeClass() }}">
                                        {{ $employee->employment_type->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $employee->status->badgeClass() }}">
                                        {{ $employee->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate($employee->joining_date) }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @can('employee.view')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employees.show', $employee) }}">
                                                        <i class="bi bi-eye text-info me-2"></i>{{ _trans('common.View Profile') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('employee.edit')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employees.edit', $employee) }}">
                                                        <i class="bi bi-pencil text-primary me-2"></i>{{ _trans('common.Edit') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item"
                                                        onclick="openStatusModal({{ $employee->id }}, '{{ $employee->full_name }}', '{{ $employee->status->value }}')">
                                                        <i class="bi bi-arrow-repeat text-warning me-2"></i>{{ _trans('common.Change Status') }}
                                                    </button>
                                                </li>
                                            @endcan
                                            @can('employee.delete')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmDeleteModal"
                                                        data-action="{{ route('employees.destroy', $employee) }}"
                                                        data-item-name="{{ $employee->full_name }}">
                                                        <i class="bi bi-trash me-2"></i>{{ _trans('common.Delete') }}
                                                    </button>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-people text-muted display-4"></i>
                                    <h6 class="mt-3 text-muted">{{ _trans('common.No employees found') }}</h6>
                                    <p class="text-muted small mb-3">{{ _trans('common.Try adjusting your search filters or create a new employee.') }}</p>
                                    @can('employee.create')
                                        <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Add Employee') }}
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        </x-ui.card>
    @endif

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

    <!-- Soft Delete Modal Component -->
    <x-ui.confirm-delete
        id="confirmDeleteModal"
        title="Confirm Deletion"
        message="Are you sure you want to delete this employee? Their account and records will be soft-deleted."
    />
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
