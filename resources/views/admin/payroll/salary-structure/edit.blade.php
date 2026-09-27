@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Salary Structure'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Salary Structure Configuration') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Customize basic salary, allowances, and deduction items for this employee') }}</p>
        </div>
        <a href="{{ route('payroll.salary-structure.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>{{ _trans('common.Back to List') }}</span>
        </a>
    </div>

    {{-- Employee Profile Summary Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="rounded-circle object-fit-cover shadow-sm" width="56" height="56">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-dark mb-0">{{ $employee->name }}</h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1">
                                {{ $employee->employeeDetail?->emp_code ?? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5">
                                {{ $employee->status_enum->label() }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-1">
                            <div><i class="bi bi-briefcase me-1 text-primary"></i>{{ $employee->employeeDetail?->designation?->name ?? _trans('common.Staff') }}</div>
                            <div><i class="bi bi-building me-1 text-primary"></i>{{ $employee->employeeDetail?->department?->name ?? _trans('common.General') }}</div>
                            <div><i class="bi bi-envelope me-1 text-primary"></i>{{ $employee->email }}</div>
                            @if ($employee->employeeDetail?->joining_date)
                                <div><i class="bi bi-calendar3 me-1 text-primary"></i>{{ _trans('common.Joined') }}: {{ $employee->employeeDetail->joining_date->format('M d, Y') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('employees.show', $employee) }}" target="_blank" class="btn btn-sm btn-light border">
                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ _trans('common.View Full Profile') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Configuration Form --}}
    <form method="POST" action="{{ route('payroll.salary-structure.update', $employee) }}" id="salaryStructureForm" class="needs-validation">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Column: Components Input --}}
            <div class="col-lg-8">
                {{-- 1. Base Salary Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-cash-stack text-primary fs-5"></i>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Base / Basic Salary') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label fw-bold text-dark">{{ _trans('common.Monthly Basic Salary') }} <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold">$</span>
                                    <input type="number" step="0.01" min="0" name="basic_salary" id="basic_salary_input" class="form-control form-control-lg fw-bold text-primary @error('basic_salary') is-invalid @enderror" required value="{{ old('basic_salary', $structure['basic_salary']) }}" placeholder="0.00">
                                </div>
                                <div class="form-text extra-small text-muted">{{ _trans('common.Fundamental base pay used as the baseline for percentage-calculated components.') }}</div>
                                @error('basic_salary')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 bg-light p-3 rounded-3 border">
                                <div class="text-muted extra-small fw-semibold">{{ _trans('common.Basic Salary Note') }}</div>
                                <div class="small text-dark mt-1">{{ _trans('common.Percentage allowances and deductions will dynamically recalculate whenever this value is modified.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Earnings & Allowances Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle text-success fs-5"></i>
                            <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Earnings & Allowances') }}</h5>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1">
                            {{ count($structure['earnings']) }} {{ _trans('common.Available') }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($structure['earnings']))
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle fs-3"></i>
                                <p class="small mt-2 mb-0">{{ _trans('common.No earning components defined yet.') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 45px;">
                                                <span class="visually-hidden">{{ _trans('common.Select') }}</span>
                                            </th>
                                            <th>{{ _trans('common.Component') }}</th>
                                            <th>{{ _trans('common.Default Rate') }}</th>
                                            <th style="width: 180px;">{{ _trans('common.Custom Override') }}</th>
                                            <th class="text-end pe-4" style="width: 140px;">{{ _trans('common.Calculated Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($structure['earnings'] as $item)
                                            @php
                                                $comp = $item['component'];
                                                $fieldPrefix = "components[{$comp->id}]";
                                                $isAssigned = old("components.{$comp->id}.enabled", $item['is_assigned']);
                                                $overrideVal = old("components.{$comp->id}.value", $item['override_value']);
                                            @endphp
                                            <tr class="component-row" data-component-id="{{ $comp->id }}" data-type="earning" data-calc-type="{{ $comp->calc_type->value }}" data-default-value="{{ $comp->value }}">
                                                <td class="ps-4">
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input component-toggle" type="checkbox" name="{{ $fieldPrefix }}[enabled]" value="1" id="toggle_{{ $comp->id }}" {{ $isAssigned ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="form-check-label fw-bold text-dark d-block cursor-pointer" for="toggle_{{ $comp->id }}">
                                                        {{ $comp->name }}
                                                    </label>
                                                    <div class="extra-small text-muted">
                                                        <span class="badge {{ $comp->calc_type->badgeClass() }} py-0 px-1.5 extra-small me-1">{{ $comp->calc_type->shortLabel() }}</span>
                                                        @if ($comp->is_taxable)
                                                            <span class="text-warning extra-small"><i class="bi bi-shield-lock me-0.5"></i>{{ _trans('common.Taxable') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="small text-muted">{{ $comp->formatted_value }}</span>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        @if ($comp->calc_type === \App\Enums\SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC)
                                                            <input type="number" step="0.01" min="0" name="{{ $fieldPrefix }}[value]" class="form-control form-control-sm component-value-input" placeholder="{{ $comp->value }}" value="{{ $overrideVal }}">
                                                            <span class="input-group-text bg-light">%</span>
                                                        @else
                                                            <span class="input-group-text bg-light">$</span>
                                                            <input type="number" step="0.01" min="0" name="{{ $fieldPrefix }}[value]" class="form-control form-control-sm component-value-input" placeholder="{{ $comp->value }}" value="{{ $overrideVal }}">
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="fw-bold text-success component-calculated-display" id="display_amount_{{ $comp->id }}">
                                                        +{{ currency_format($item['calculated_amount']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 3. Deductions Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-dash-circle text-danger fs-5"></i>
                            <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Deductions & Statutory Contributions') }}</h5>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1">
                            {{ count($structure['deductions']) }} {{ _trans('common.Available') }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($structure['deductions']))
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle fs-3"></i>
                                <p class="small mt-2 mb-0">{{ _trans('common.No deduction components defined yet.') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 45px;">
                                                <span class="visually-hidden">{{ _trans('common.Select') }}</span>
                                            </th>
                                            <th>{{ _trans('common.Component') }}</th>
                                            <th>{{ _trans('common.Default Rate') }}</th>
                                            <th style="width: 180px;">{{ _trans('common.Custom Override') }}</th>
                                            <th class="text-end pe-4" style="width: 140px;">{{ _trans('common.Calculated Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($structure['deductions'] as $item)
                                            @php
                                                $comp = $item['component'];
                                                $fieldPrefix = "components[{$comp->id}]";
                                                $isAssigned = old("components.{$comp->id}.enabled", $item['is_assigned']);
                                                $overrideVal = old("components.{$comp->id}.value", $item['override_value']);
                                            @endphp
                                            <tr class="component-row" data-component-id="{{ $comp->id }}" data-type="deduction" data-calc-type="{{ $comp->calc_type->value }}" data-default-value="{{ $comp->value }}">
                                                <td class="ps-4">
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input component-toggle" type="checkbox" name="{{ $fieldPrefix }}[enabled]" value="1" id="toggle_{{ $comp->id }}" {{ $isAssigned ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="form-check-label fw-bold text-dark d-block cursor-pointer" for="toggle_{{ $comp->id }}">
                                                        {{ $comp->name }}
                                                    </label>
                                                    <div class="extra-small text-muted">
                                                        <span class="badge {{ $comp->calc_type->badgeClass() }} py-0 px-1.5 extra-small me-1">{{ $comp->calc_type->shortLabel() }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="small text-muted">{{ $comp->formatted_value }}</span>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        @if ($comp->calc_type === \App\Enums\SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC)
                                                            <input type="number" step="0.01" min="0" name="{{ $fieldPrefix }}[value]" class="form-control form-control-sm component-value-input" placeholder="{{ $comp->value }}" value="{{ $overrideVal }}">
                                                            <span class="input-group-text bg-light">%</span>
                                                        @else
                                                            <span class="input-group-text bg-light">$</span>
                                                            <input type="number" step="0.01" min="0" name="{{ $fieldPrefix }}[value]" class="form-control form-control-sm component-value-input" placeholder="{{ $comp->value }}" value="{{ $overrideVal }}">
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="fw-bold text-danger component-calculated-display" id="display_amount_{{ $comp->id }}">
                                                        -{{ currency_format($item['calculated_amount']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Sticky Live Breakdown Summary --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 1.5rem; z-index: 10;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-calculator text-primary me-2"></i>{{ _trans('common.Salary Breakdown Summary') }}
                        </h5>
                        <p class="text-muted extra-small mb-0">{{ _trans('common.Live calculated monthly compensation preview') }}</p>
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            {{-- Basic Salary --}}
                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span class="text-muted small">{{ _trans('common.Basic Salary') }}</span>
                                <span class="fw-bold text-dark fs-6" id="summary_basic">$0.00</span>
                            </div>

                            {{-- Total Earnings --}}
                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-plus-circle text-success extra-small"></i>
                                    <span class="text-muted small">{{ _trans('common.Total Allowances') }}</span>
                                </div>
                                <span class="fw-bold text-success fs-6" id="summary_earnings">+$0.00</span>
                            </div>

                            {{-- Gross Salary --}}
                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <span class="fw-bold text-dark small">{{ _trans('common.Gross Salary') }}</span>
                                <span class="fw-bold text-dark fs-6" id="summary_gross">$0.00</span>
                            </div>

                            {{-- Total Deductions --}}
                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-dash-circle text-danger extra-small"></i>
                                    <span class="text-muted small">{{ _trans('common.Total Deductions') }}</span>
                                </div>
                                <span class="fw-bold text-danger fs-6" id="summary_deductions">-$0.00</span>
                            </div>

                            {{-- Net Salary (Highlight) --}}
                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 text-center">
                                <div class="text-success extra-small fw-semibold text-uppercase tracking-wider">{{ _trans('common.Estimated Monthly Net Pay') }}</div>
                                <div class="fs-3 fw-bold text-success mt-1" id="summary_net">$0.00</div>
                                <div class="extra-small text-muted mt-1">{{ _trans('common.Gross Salary minus Total Deductions') }}</div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="bi bi-check2-circle fs-5"></i>
                                <span class="fw-semibold">{{ _trans('common.Save Salary Structure') }}</span>
                            </button>
                            <a href="{{ route('payroll.salary-structure.index') }}" class="btn btn-light border py-2">
                                {{ _trans('common.Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function formatMoney(amount) {
        return '$' + parseFloat(amount || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function calculateLiveSalary() {
        var basicSalary = parseFloat($('#basic_salary_input').val()) || 0.00;
        var totalEarnings = 0.00;
        var totalDeductions = 0.00;

        $('.component-row').each(function() {
            var $row = $(this);
            var isEnabled = $row.find('.component-toggle').is(':checked');
            var type = $row.data('type');
            var calcType = $row.data('calc-type');
            var defaultVal = parseFloat($row.data('default-value')) || 0.00;
            var customValInput = $row.find('.component-value-input').val();
            var effectiveVal = (customValInput !== '' && !isNaN(customValInput)) ? parseFloat(customValInput) : defaultVal;

            var amount = 0.00;
            if (calcType === 'percent_of_basic') {
                amount = Math.round(((basicSalary * effectiveVal) / 100) * 100) / 100;
            } else {
                amount = Math.round(effectiveVal * 100) / 100;
            }

            var $display = $row.find('.component-calculated-display');
            if (type === 'earning') {
                $display.text('+' + formatMoney(amount));
                if (isEnabled) {
                    totalEarnings += amount;
                    $row.removeClass('opacity-50');
                } else {
                    $row.addClass('opacity-50');
                }
            } else {
                $display.text('-' + formatMoney(amount));
                if (isEnabled) {
                    totalDeductions += amount;
                    $row.removeClass('opacity-50');
                } else {
                    $row.addClass('opacity-50');
                }
            }
        });

        var grossSalary = Math.round((basicSalary + totalEarnings) * 100) / 100;
        var netSalary = Math.max(0, Math.round((grossSalary - totalDeductions) * 100) / 100);

        $('#summary_basic').text(formatMoney(basicSalary));
        $('#summary_earnings').text('+' + formatMoney(totalEarnings));
        $('#summary_gross').text(formatMoney(grossSalary));
        $('#summary_deductions').text('-' + formatMoney(totalDeductions));
        $('#summary_net').text(formatMoney(netSalary));
    }

    $('#basic_salary_input').on('input keyup change', calculateLiveSalary);
    $('.component-toggle').on('change', calculateLiveSalary);
    $('.component-value-input').on('input keyup change', calculateLiveSalary);

    // Initial calculation on page load
    calculateLiveSalary();
});
</script>
@endpush
