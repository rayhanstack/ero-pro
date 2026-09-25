@extends('admin.layouts.app')
@section('title', _trans('common.Create Holiday'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Create Holiday') }}"
        subtitle="{{ _trans('common.Schedule a public or company holiday') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Holidays'), 'url' => route('holidays.index')],
            ['label' => _trans('common.Create Holiday')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Holidays') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Holiday Details')" icon="bi-calendar-plus">
                <form method="POST" action="{{ route('holidays.store') }}" class="needs-validation">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="title" class="form-label fw-semibold">{{ _trans('common.Holiday Title') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="title"
                                id="title"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. Independence Day') }}"
                                value="{{ old('title') }}"
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="from_date" class="form-label fw-semibold">{{ _trans('common.From Date') }} <span class="text-danger">*</span></label>
                            <input type="date"
                                name="from_date"
                                id="from_date"
                                class="form-control @error('from_date') is-invalid @enderror"
                                value="{{ old('from_date', date('Y-m-d')) }}"
                                required>
                            @error('from_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="to_date" class="form-label fw-semibold">{{ _trans('common.To Date') }} <span class="text-danger">*</span></label>
                            <input type="date"
                                name="to_date"
                                id="to_date"
                                class="form-control @error('to_date') is-invalid @enderror"
                                value="{{ old('to_date', date('Y-m-d')) }}"
                                required>
                            @error('to_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">{{ _trans('common.Holiday Type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type', 'public') === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
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

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">{{ _trans('common.Description') }}</label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="{{ _trans('common.Details or significance of this holiday...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('holidays.index') }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Holiday') }}</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection
