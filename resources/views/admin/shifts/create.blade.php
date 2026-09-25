@extends('admin.layouts.app')
@section('title', _trans('common.Create Shift'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Create Shift') }}"
        subtitle="{{ _trans('common.Define working hours, shift timings, and grace periods') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Shifts'), 'url' => route('shifts.index')],
            ['label' => _trans('common.Create Shift')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Shifts') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Shift Details')" icon="bi-clock-history">
                <form method="POST" action="{{ route('shifts.store') }}" class="needs-validation">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">{{ _trans('common.Shift Name') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. Regular Day Shift') }}"
                                value="{{ old('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', 'active') === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="start_time" class="form-label fw-semibold">{{ _trans('common.Start Time') }} <span class="text-danger">*</span></label>
                            <input type="time"
                                name="start_time"
                                id="start_time"
                                class="form-control @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time', '09:00') }}"
                                required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="end_time" class="form-label fw-semibold">{{ _trans('common.End Time') }} <span class="text-danger">*</span></label>
                            <input type="time"
                                name="end_time"
                                id="end_time"
                                class="form-control @error('end_time') is-invalid @enderror"
                                value="{{ old('end_time', '18:00') }}"
                                required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="grace_minutes" class="form-label fw-semibold">{{ _trans('common.Grace Period (Minutes)') }} <span class="text-danger">*</span></label>
                            <input type="number"
                                name="grace_minutes"
                                id="grace_minutes"
                                min="0"
                                max="240"
                                class="form-control @error('grace_minutes') is-invalid @enderror"
                                value="{{ old('grace_minutes', 15) }}"
                                required>
                            @error('grace_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">{{ _trans('common.Description') }}</label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="{{ _trans('common.Notes or special instructions about this shift...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('shifts.index') }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Shift') }}</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection
