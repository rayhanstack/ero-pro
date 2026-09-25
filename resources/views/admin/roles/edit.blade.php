@extends('admin.layouts.app')
@section('title', _trans('common.Edit Role'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Role') }}: {{ $role->name }}"
        subtitle="{{ _trans('common.Update role name and manage module permissions') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Roles & Permissions'), 'url' => route('roles.index')],
            ['label' => $role->name],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Roles') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('roles.update', $role) }}" class="needs-validation">
        @csrf
        @method('PUT')

        <x-ui.card :title="_trans('common.Role Details')" icon="bi-shield-check" class="mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ _trans('common.Role Name') }} <span class="text-danger">*</span></label>
                    <input type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $role->name) }}"
                        {{ $role->name === 'Super Admin' ? 'readonly' : '' }}
                        required>
                    @if ($role->name === 'Super Admin')
                        <small class="text-muted mt-1 d-block">{{ _trans('common.Super Admin role has full access to all features.') }}</small>
                    @endif
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="_trans('common.Permissions Matrix')" icon="bi-grid-3x3-gap" class="mb-4">
            @include('admin.roles.partials.permission-matrix', [
                'groupedPermissions' => $groupedPermissions,
                'rolePermissions' => old('permissions', $rolePermissions),
            ])
        </x-ui.card>

        <div class="d-flex align-items-center justify-content-end gap-2 mb-5">
            <a href="{{ route('roles.index') }}" class="btn btn-light px-4">{{ _trans('common.Cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                <i class="bi bi-check-lg"></i>
                <span>{{ _trans('common.Save Changes') }}</span>
            </button>
        </div>
    </form>
@endsection
