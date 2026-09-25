@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'attendance' ? 'show active' : '' }}" id="v-pills-attendance" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Attendance Rules & Timing') }}</h5>

            <form method="POST" action="{{ route('settings.attendance') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="office_start" class="form-label fw-semibold small">{{ _trans('common.Office Start Time') }} <span class="text-danger">*</span></label>
                        <input type="time"
                            name="office_start"
                            id="office_start"
                            class="form-control @error('office_start') is-invalid @enderror"
                            value="{{ old('office_start', $settings['office_start'] ?? '09:00') }}"
                            required>
                        @error('office_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="office_end" class="form-label fw-semibold small">{{ _trans('common.Office End Time') }} <span class="text-danger">*</span></label>
                        <input type="time"
                            name="office_end"
                            id="office_end"
                            class="form-control @error('office_end') is-invalid @enderror"
                            value="{{ old('office_end', $settings['office_end'] ?? '18:00') }}"
                            required>
                        @error('office_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="grace_minutes" class="form-label fw-semibold small">{{ _trans('common.Grace Period (Minutes)') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            name="grace_minutes"
                            id="grace_minutes"
                            class="form-control @error('grace_minutes') is-invalid @enderror"
                            value="{{ old('grace_minutes', $settings['grace_minutes'] ?? '15') }}"
                            min="0"
                            max="120"
                            required>
                        @error('grace_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="late_after" class="form-label fw-semibold small">{{ _trans('common.Mark Late After') }} <span class="text-danger">*</span></label>
                        <input type="time"
                            name="late_after"
                            id="late_after"
                            class="form-control @error('late_after') is-invalid @enderror"
                            value="{{ old('late_after', $settings['late_after'] ?? '09:15') }}"
                            required>
                        @error('late_after')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="half_day_hours" class="form-label fw-semibold small">{{ _trans('common.Half Day Minimum Hours') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            step="0.5"
                            name="half_day_hours"
                            id="half_day_hours"
                            class="form-control @error('half_day_hours') is-invalid @enderror"
                            value="{{ old('half_day_hours', $settings['half_day_hours'] ?? '4.0') }}"
                            min="1"
                            max="24"
                            required>
                        @error('half_day_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch pt-3">
                            <input class="form-check-input"
                                type="checkbox"
                                name="overtime_enabled"
                                id="overtime_enabled"
                                value="1"
                                {{ old('overtime_enabled', $settings['overtime_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="overtime_enabled">
                                {{ _trans('common.Enable Overtime Calculation') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Attendance Settings') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
