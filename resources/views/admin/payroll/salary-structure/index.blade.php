@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Employee Salary Structures'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Employee Salary Structures') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Configure individual employee basic salary, allowances, and statutory deductions') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.components.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-collection"></i>
                <span>{{ _trans('common.Salary Components') }}</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Employees') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_employees'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Basic Payroll') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ currency_format($stats['total_basic_payroll']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-sliders fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Active Components') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['active_components_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Configured Structures') }}</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ $stats['configured_employees'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('payroll.salary-structure.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ _trans('common.Search by employee name, code, email...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="department_id" class="form-select">
                        <option value="">{{ _trans('common.All Departments') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="designation_id" class="form-select">
                        <option value="">{{ _trans('common.All Designations') }}</option>
                        @foreach ($designations as $desig)
                            <option value="{{ $desig->id }}" {{ ($filters['designation_id'] ?? '') == $desig->id ? 'selected' : '' }}>
                                {{ $desig->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>{{ _trans('common.Filter') }}
                    </button>
                    <a href="{{ route('payroll.salary-structure.index') }}" class="btn btn-light border" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Employees Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        @if ($employees->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark mt-3">{{ _trans('common.No Employees Found') }}</h5>
                <p class="text-muted small mb-0">{{ _trans('common.No employees matched the selected filter criteria.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Employee') }}</th>
                            <th>{{ _trans('common.Department & Role') }}</th>
                            <th>{{ _trans('common.Basic Salary') }}</th>
                            <th>{{ _trans('common.Total Allowances') }}</th>
                            <th>{{ _trans('common.Total Deductions') }}</th>
                            <th>{{ _trans('common.Gross Pay') }}</th>
                            <th>{{ _trans('common.Estimated Net Pay') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            @php
                                $breakdown = $employee->salary_breakdown ?? [
                                    'basic' => 0.00,
                                    'total_earnings' => 0.00,
                                    'total_deductions' => 0.00,
                                    'gross_salary' => 0.00,
                                    'net_pay' => 0.00,
                                    'components_count' => 0,
                                ];
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="rounded-circle object-fit-cover shadow-sm" width="38" height="38">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                            <div class="text-muted extra-small">
                                                <span class="badge bg-light text-dark border me-1">{{ $employee->employeeDetail?->emp_code ?? 'EMP' }}</span>
                                                <span>{{ $employee->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark small">{{ $employee->employeeDetail?->designation?->name ?? _trans('common.N/A') }}</div>
                                    <div class="text-muted extra-small">{{ $employee->employeeDetail?->department?->name ?? _trans('common.General') }}</div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ currency_format($breakdown['basic']) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">+{{ currency_format($breakdown['total_earnings']) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-danger">-{{ currency_format($breakdown['total_deductions']) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ currency_format($breakdown['gross_salary']) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fs-8">
                                        {{ currency_format($breakdown['net_pay']) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    @can('payroll.edit')
                                        <a href="{{ route('payroll.salary-structure.edit', $employee) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-sliders"></i>
                                            <span>{{ _trans('common.Configure') }}</span>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="card-footer bg-white py-3 border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
