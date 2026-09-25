@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'payroll' ? 'show active' : '' }}" id="v-pills-payroll" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Payroll Calculation Rules') }}</h5>

            <form method="POST" action="{{ route('settings.payroll') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="working_days_mode" class="form-label fw-semibold small">{{ _trans('common.Monthly Working Days Basis') }} <span class="text-danger">*</span></label>
                        <select name="working_days_mode" id="working_days_mode" class="form-select @error('working_days_mode') is-invalid @enderror" required>
                            @php $currentMode = old('working_days_mode', $settings['working_days_mode'] ?? 'monthly_fixed'); @endphp
                            <option value="monthly_fixed" {{ $currentMode === 'monthly_fixed' ? 'selected' : '' }}>{{ _trans('common.Fixed 30 Days per Month') }}</option>
                            <option value="calendar_days" {{ $currentMode === 'calendar_days' ? 'selected' : '' }}>{{ _trans('common.Actual Days in Month (28-31)') }}</option>
                            <option value="working_days" {{ $currentMode === 'working_days' ? 'selected' : '' }}>{{ _trans('common.Working Days (Excluding Weekends & Holidays)') }}</option>
                        </select>
                        @error('working_days_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="overtime_rate" class="form-label fw-semibold small">{{ _trans('common.Overtime Hourly Rate Multiplier') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            step="0.1"
                            name="overtime_rate"
                            id="overtime_rate"
                            class="form-control @error('overtime_rate') is-invalid @enderror"
                            value="{{ old('overtime_rate', $settings['overtime_rate'] ?? '1.5') }}"
                            min="0"
                            required>
                        <small class="text-muted">{{ _trans('common.e.g. 1.5x of hourly wage') }}</small>
                        @error('overtime_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Payroll Settings') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
