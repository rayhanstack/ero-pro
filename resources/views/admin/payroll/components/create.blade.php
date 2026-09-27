@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Add Salary Component'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Add Salary Component') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Define a new allowance or deduction rule for payroll calculations') }}</p>
        </div>
        <a href="{{ route('payroll.components.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>{{ _trans('common.Back to Components') }}</span>
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="{{ route('payroll.components.store') }}" class="needs-validation">
                        @csrf

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Component Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required placeholder="{{ _trans('common.e.g. House Rent Allowance (HRA), Medical Allowance, Provident Fund') }}" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Component Type') }} <span class="text-danger">*</span></label>
                                <select name="type" id="component_type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach ($types as $t)
                                        <option value="{{ $t->value }}" {{ old('type', 'earning') === $t->value ? 'selected' : '' }}>
                                            {{ $t->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text extra-small text-muted">{{ _trans('common.Earnings add to gross pay; Deductions reduce from gross pay.') }}</div>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Calculation Method') }} <span class="text-danger">*</span></label>
                                <select name="calc_type" id="component_calc_type" class="form-select @error('calc_type') is-invalid @enderror" required>
                                    @foreach ($calcTypes as $ct)
                                        <option value="{{ $ct->value }}" {{ old('calc_type', 'fixed') === $ct->value ? 'selected' : '' }}>
                                            {{ $ct->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text extra-small text-muted">{{ _trans('common.Choose fixed monthly amount or percentage of employee basic salary.') }}</div>
                                @error('calc_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark" id="value_label">{{ _trans('common.Default Value / Rate') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" id="value_prefix_symbol">$</span>
                                    <input type="number" step="0.01" min="0" name="value" id="component_value" class="form-control @error('value') is-invalid @enderror" required placeholder="0.00" value="{{ old('value', '0.00') }}">
                                    <span class="input-group-text bg-light d-none" id="value_suffix_percent">%</span>
                                </div>
                                <div class="form-text extra-small text-muted" id="value_help_text">{{ _trans('common.Default amount applied to employees unless overridden.') }}</div>
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
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
                                <div class="form-check form-switch p-3 bg-light rounded-3 border">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="is_taxable" name="is_taxable" value="1" {{ old('is_taxable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="is_taxable">
                                        {{ _trans('common.Subject to Tax Computation (Is Taxable)') }}
                                    </label>
                                    <div class="text-muted extra-small ms-4 mt-0.5">{{ _trans('common.If checked, this component will be included in taxable income calculations.') }}</div>
                                </div>
                                @error('is_taxable')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Description / Remarks') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ _trans('common.Optional notes regarding statutory compliance, eligibility criteria, or policy rules...') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                            <a href="{{ route('payroll.components.index') }}" class="btn btn-light px-4">{{ _trans('common.Cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save Component') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function updateCalcTypeUI() {
        var calcType = $('#component_calc_type').val();
        if (calcType === 'percent_of_basic') {
            $('#value_prefix_symbol').addClass('d-none');
            $('#value_suffix_percent').removeClass('d-none');
            $('#value_help_text').text("{{ _trans('common.Enter the percentage of employee basic salary (e.g. 10.00 for 10%).') }}");
        } else {
            $('#value_prefix_symbol').removeClass('d-none');
            $('#value_suffix_percent').addClass('d-none');
            $('#value_help_text').text("{{ _trans('common.Default fixed amount applied to employees unless overridden.') }}");
        }
    }

    $('#component_calc_type').on('change', updateCalcTypeUI);
    updateCalcTypeUI();
});
</script>
@endpush
