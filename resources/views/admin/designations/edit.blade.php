@extends('admin.layouts.app')
@section('title', _trans('common.Edit Designation'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Designation') }}: {{ $designation->name }}"
        subtitle="{{ _trans('common.Update organizational job title and department assignment') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Designations'), 'url' => route('designations.index')],
            ['label' => $designation->name],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Designations') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Designation Details')" icon="bi-pencil-square">
                <form method="POST" action="{{ route('designations.update', $designation) }}" class="needs-validation">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="name" class="form-label fw-semibold">{{ _trans('common.Designation Title') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $designation->name) }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="department_id" class="form-label fw-semibold">{{ _trans('common.Department') }} <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $designation->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="level" class="form-label fw-semibold">{{ _trans('common.Hierarchy Level') }} <span class="text-danger">*</span></label>
                            <input type="number"
                                name="level"
                                id="level"
                                min="1"
                                max="100"
                                class="form-control @error('level') is-invalid @enderror"
                                value="{{ old('level', $designation->level) }}"
                                required>
                            <div class="form-text text-muted">{{ _trans('common.Numeric level from 1 (Junior) to 10 (Executive)') }}</div>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $designation->status?->value ?? $designation->status) === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">{{ _trans('common.Job Description & Responsibilities') }}</label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3">{{ old('description', $designation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('designations.index') }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Update Designation') }}</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection
