@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'leave' ? 'show active' : '' }}" id="v-pills-leave" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Leave Quota & Policies') }}</h5>

            <form method="POST" action="{{ route('settings.leave') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="annual_leave_quota" class="form-label fw-semibold small">{{ _trans('common.Annual Leave (Days/Year)') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            name="annual_leave_quota"
                            id="annual_leave_quota"
                            class="form-control @error('annual_leave_quota') is-invalid @enderror"
                            value="{{ old('annual_leave_quota', $settings['annual_leave_quota'] ?? '15') }}"
                            min="0"
                            required>
                        @error('annual_leave_quota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sick_leave_quota" class="form-label fw-semibold small">{{ _trans('common.Sick Leave (Days/Year)') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            name="sick_leave_quota"
                            id="sick_leave_quota"
                            class="form-control @error('sick_leave_quota') is-invalid @enderror"
                            value="{{ old('sick_leave_quota', $settings['sick_leave_quota'] ?? '10') }}"
                            min="0"
                            required>
                        @error('sick_leave_quota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="casual_leave_quota" class="form-label fw-semibold small">{{ _trans('common.Casual Leave (Days/Year)') }} <span class="text-danger">*</span></label>
                        <input type="number"
                            name="casual_leave_quota"
                            id="casual_leave_quota"
                            class="form-control @error('casual_leave_quota') is-invalid @enderror"
                            value="{{ old('casual_leave_quota', $settings['casual_leave_quota'] ?? '10') }}"
                            min="0"
                            required>
                        @error('casual_leave_quota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="leave_approval_level" class="form-label fw-semibold small">{{ _trans('common.Approval Workflow Level') }}</label>
                        <select name="leave_approval_level" id="leave_approval_level" class="form-select @error('leave_approval_level') is-invalid @enderror">
                            @php $currentLevel = old('leave_approval_level', $settings['leave_approval_level'] ?? 'single'); @endphp
                            <option value="single" {{ $currentLevel === 'single' ? 'selected' : '' }}>{{ _trans('common.Single Level (Manager or HR)') }}</option>
                            <option value="multi" {{ $currentLevel === 'multi' ? 'selected' : '' }}>{{ _trans('common.Multi Level (Manager then HR)') }}</option>
                        </select>
                        @error('leave_approval_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Leave Settings') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
