@extends('admin.layouts.app')
@section('title', _trans('common.Edit Language'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Language') }}: {{ $language->name }}"
        subtitle="{{ _trans('common.Update language details and text direction') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Languages'), 'url' => route('languages.index')],
            ['label' => $language->name],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('languages.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Languages') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('languages.update', $language) }}" class="needs-validation">
        @csrf
        @method('PUT')

        <x-ui.card :title="_trans('common.Language Details')" icon="bi-translate" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-semibold">{{ _trans('common.Language Name') }} <span class="text-danger">*</span></label>
                    <input type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $language->name) }}"
                        required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label fw-semibold">{{ _trans('common.Language Code') }} (ISO 639-1) <span class="text-danger">*</span></label>
                    <input type="text"
                        name="code"
                        id="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code', $language->code) }}"
                        required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="native" class="form-label fw-semibold">{{ _trans('common.Native Name') }}</label>
                    <input type="text"
                        name="native"
                        id="native"
                        class="form-control @error('native') is-invalid @enderror"
                        value="{{ old('native', $language->native) }}">
                    @error('native')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $language->status) === 'active' ? 'selected' : '' }}>{{ _trans('common.Active') }}</option>
                        <option value="inactive" {{ old('status', $language->status) === 'inactive' ? 'selected' : '' }}>{{ _trans('common.Inactive') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="rtl" id="rtl" value="1" {{ old('rtl', $language->rtl) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="rtl">
                            {{ _trans('common.Right to Left (RTL) Layout') }}
                        </label>
                        <div class="text-muted small">{{ _trans('common.Enable this for Arabic, Hebrew, Urdu, or Persian languages.') }}</div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="d-flex align-items-center justify-content-end gap-2 mb-5">
            <a href="{{ route('languages.index') }}" class="btn btn-light px-4">{{ _trans('common.Cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                <i class="bi bi-check-lg"></i>
                <span>{{ _trans('common.Save Changes') }}</span>
            </button>
        </div>
    </form>
@endsection
