@extends('admin.layouts.app')
@section('title', _trans('common.Create Department'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Create Department') }}"
        subtitle="{{ _trans('common.Add a new organizational department') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Departments'), 'url' => route('departments.index')],
            ['label' => _trans('common.Create Department')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Departments') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Department Details')" icon="bi-building-add">
                <form method="POST" action="{{ route('departments.store') }}" class="needs-validation">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">{{ _trans('common.Department Name') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. Software Engineering') }}"
                                value="{{ old('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="code" class="form-label fw-semibold">{{ _trans('common.Code') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="code"
                                id="code"
                                class="form-control text-uppercase @error('code') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. ENG') }}"
                                value="{{ old('code') }}"
                                required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
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

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">{{ _trans('common.Description') }}</label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="{{ _trans('common.Briefly describe the functions of this department...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('departments.index') }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Department') }}</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection
