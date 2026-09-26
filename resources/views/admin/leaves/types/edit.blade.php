@extends('admin.layouts.app')

@section('title', _trans('common.Edit Leave Type'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Leave Type') }}"
        subtitle="{{ _trans('common.Update leave policy settings and annual quotas') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Leave Types'), 'url' => route('leave-types.index')],
            ['label' => _trans('common.Edit Leave Type')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('leave-types.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to List') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row">
        <div class="col-lg-8 col-xl-7">
            <x-ui.card>
                <form method="POST" action="{{ route('leave-types.update', $leaveType) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- Name --}}
                        <div class="col-md-8">
                            <label for="name" class="form-label small fw-semibold text-dark">{{ _trans('common.Leave Type Name') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Annual Leave, Casual Leave, Sick Leave"
                                value="{{ old('name', $leaveType->name) }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Code --}}
                        <div class="col-md-4">
                            <label for="code" class="form-label small fw-semibold text-dark">{{ _trans('common.Code') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="code"
                                id="code"
                                class="form-control text-uppercase @error('code') is-invalid @enderror"
                                placeholder="e.g. AL, CL, SL"
                                value="{{ old('code', $leaveType->code) }}"
                                required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Days Per Year --}}
                        <div class="col-md-6">
                            <label for="days_per_year" class="form-label small fw-semibold text-dark">{{ _trans('common.Days Per Year') }} <span class="text-danger">*</span></label>
                            <input type="number"
                                name="days_per_year"
                                id="days_per_year"
                                class="form-control @error('days_per_year') is-invalid @enderror"
                                placeholder="e.g. 15"
                                value="{{ old('days_per_year', $leaveType->days_per_year) }}"
                                min="0"
                                max="365"
                                required>
                            @error('days_per_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Color --}}
                        <div class="col-md-6">
                            <label for="color" class="form-label small fw-semibold text-dark">{{ _trans('common.Calendar Color') }}</label>
                            <div class="input-group">
                                <input type="color"
                                    name="color"
                                    id="color"
                                    class="form-control form-control-color"
                                    value="{{ old('color', $leaveType->color ?: '#4f46e5') }}"
                                    title="{{ _trans('common.Choose calendar badge color') }}">
                                <input type="text"
                                    class="form-control text-muted"
                                    id="colorHex"
                                    value="{{ old('color', $leaveType->color ?: '#4f46e5') }}"
                                    readonly>
                            </div>
                        </div>

                        {{-- Paid Status --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid', $leaveType->is_paid) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark small" for="is_paid">
                                    {{ _trans('common.Paid Leave') }}
                                </label>
                            </div>
                            <div class="form-text small">{{ _trans('common.If disabled, wages will be deducted for unpaid leave.') }}</div>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold text-dark">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $leaveType->status?->value ?? 'active') === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Carry Forward Checkbox --}}
                        <div class="col-12 border-top pt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="carry_forward" id="carry_forward" value="1" {{ old('carry_forward', $leaveType->carry_forward) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark small" for="carry_forward">
                                    {{ _trans('common.Enable Carry Forward to Next Year') }}
                                </label>
                            </div>
                        </div>

                        {{-- Max Carry --}}
                        <div class="col-md-6" id="maxCarryContainer" style="{{ old('carry_forward', $leaveType->carry_forward) ? '' : 'display: none;' }}">
                            <label for="max_carry" class="form-label small fw-semibold text-dark">{{ _trans('common.Max Days to Carry Forward') }}</label>
                            <input type="number"
                                name="max_carry"
                                id="max_carry"
                                class="form-control @error('max_carry') is-invalid @enderror"
                                placeholder="0 = Unlimited"
                                value="{{ old('max_carry', $leaveType->max_carry) }}"
                                min="0"
                                max="365">
                            <div class="form-text small">{{ _trans('common.Maximum unused days carried over to next year balance.') }}</div>
                            @error('max_carry')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label for="description" class="form-label small fw-semibold text-dark">{{ _trans('common.Description / Policy Rules') }}</label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="{{ _trans('common.Provide policy details or guidelines for employees...') }}">{{ old('description', $leaveType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="col-12 text-end pt-3 border-top">
                            <a href="{{ route('leave-types.index') }}" class="btn btn-light me-2">{{ _trans('common.Cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>{{ _trans('common.Update Leave Type') }}
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#carry_forward').on('change', function() {
            if ($(this).is(':checked')) {
                $('#maxCarryContainer').slideDown();
            } else {
                $('#maxCarryContainer').slideUp();
            }
        });

        $('#color').on('input change', function() {
            $('#colorHex').val($(this).val());
        });
    });
</script>
@endpush
