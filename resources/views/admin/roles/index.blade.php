@extends('admin.layouts.app')
@section('title', _trans('common.Roles & Permissions'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Roles & Permissions') }}"
        subtitle="{{ _trans('common.Manage system roles and configure module access permissions') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Roles & Permissions')],
        ]"
    >
        <x-slot:actions>
            @can('role.create')
                <a href="{{ route('roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Create New Role') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card :title="_trans('common.Role List')" icon="bi-shield-check">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">{{ _trans('common.Role Name') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Assigned Users') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Permissions') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="py-3 px-4 text-muted">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-shield me-1"></i>
                                        {{ $role->name }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-people me-1"></i>
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-info-subtle text-info-emphasis px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-key me-1"></i>
                                    {{ $role->name === 'Super Admin' ? _trans('common.All Access') : $role->permissions_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('role.edit')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit Role') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @if ($role->name !== 'Super Admin')
                                        @can('role.delete')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteModal"
                                                data-action="{{ route('roles.destroy', $role) }}"
                                                data-item-name="{{ $role->name }}"
                                                title="{{ _trans('common.Delete Role') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-shield-x fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted">{{ _trans('common.No roles found.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="p-3 border-top">
                <x-ui.pagination :paginator="$roles" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
