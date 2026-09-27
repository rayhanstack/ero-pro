@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Salary Components'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Salary Components') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Manage earnings, allowances, and statutory deduction components for payroll calculation') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.salary-structure.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-person-gear"></i>
                <span>{{ _trans('common.Salary Structure') }}</span>
            </a>
            @can('payroll.create')
                <a href="{{ route('payroll.components.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Component') }}</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Components') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-plus-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Earnings / Allowances') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['earnings'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-dash-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Deductions') }}</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ $stats['deductions'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Active Components') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('payroll.components.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ _trans('common.Search by component name...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">{{ _trans('common.All Types') }}</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" {{ ($filters['type'] ?? '') === $t->value ? 'selected' : '' }}>
                                {{ $t->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="calc_type" class="form-select">
                        <option value="">{{ _trans('common.All Calculation Types') }}</option>
                        @foreach ($calcTypes as $ct)
                            <option value="{{ $ct->value }}" {{ ($filters['calc_type'] ?? '') === $ct->value ? 'selected' : '' }}>
                                {{ $ct->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ _trans('common.All Statuses') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>{{ _trans('common.Filter') }}
                    </button>
                    <a href="{{ route('payroll.components.index') }}" class="btn btn-light border" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Components Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        @if ($components->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-wallet2 text-muted" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark mt-3">{{ _trans('common.No Salary Components Found') }}</h5>
                <p class="text-muted small mb-3">{{ _trans('common.Create salary components like Basic, HRA, Medical Allowance, PF, and Tax to build employee payroll structures.') }}</p>
                @can('payroll.create')
                    <a href="{{ route('payroll.components.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Add First Component') }}
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Component Name') }}</th>
                            <th>{{ _trans('common.Category') }}</th>
                            <th>{{ _trans('common.Calculation Method') }}</th>
                            <th>{{ _trans('common.Default Rate / Amount') }}</th>
                            <th>{{ _trans('common.Taxable') }}</th>
                            <th>{{ _trans('common.Assigned Employees') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($components as $component)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle p-2 {{ $component->type === \App\Enums\SalaryComponentTypeEnum::EARNING ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi {{ $component->type->icon() }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $component->name }}</div>
                                            @if ($component->description)
                                                <div class="text-muted extra-small text-truncate" style="max-width: 250px;">{{ $component->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $component->type->badgeClass() }} px-2.5 py-1">
                                        {{ $component->type->shortLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $component->calc_type->badgeClass() }} px-2.5 py-1">
                                        {{ $component->calc_type->shortLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        @if ($component->calc_type === \App\Enums\SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC)
                                            {{ number_format((float) $component->value, 2) }}% <span class="text-muted extra-small">{{ _trans('common.of basic') }}</span>
                                        @else
                                            {{ currency_format((float) $component->value) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if ($component->is_taxable)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-0.5">{{ _trans('common.Yes') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-0.5">{{ _trans('common.No') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2.5 py-1">
                                        <i class="bi bi-people me-1 text-primary"></i>{{ $component->employee_salary_components_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $component->status->badgeClass() }} px-2.5 py-1">
                                        {{ $component->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        @can('payroll.edit')
                                            <a href="{{ route('payroll.components.edit', $component) }}" class="btn btn-sm btn-outline-secondary p-1 px-2" title="{{ _trans('common.Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('payroll.delete')
                                            <form method="POST" action="{{ route('payroll.components.destroy', $component) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this salary component?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="{{ _trans('common.Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($components->hasPages())
                <div class="card-footer bg-white py-3 border-top">
                    {{ $components->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
