@extends('admin.layouts.app')
@section('title', _trans('common.User Management'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.User Management') }}"
        subtitle="{{ _trans('common.Manage system user accounts, roles, and status') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.User Management')],
        ]"
    >
        <x-slot:actions>
            @can('user.create')
                <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>{{ _trans('common.Create New User') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text"
                        name="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search by name, email, or phone...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-12 col-md-3">
                <select name="role" class="form-select">
                    <option value="">{{ _trans('common.All Roles') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ _trans('common.All Statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ _trans('common.Active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ _trans('common.Inactive') }}</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">{{ _trans('common.Filter') }}</button>
                @if (request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :title="_trans('common.User Accounts')" icon="bi-people">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">{{ _trans('common.User') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Contact') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Role') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Status') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Last Login') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{getFilePath('user', $user->avatar) }}"
                                        alt="{{ $user->name }}"
                                        class="rounded-circle object-fit-cover shadow-xs"
                                        width="40"
                                        height="40">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="small text-muted">
                                    {{ $user->phone ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @forelse ($user->roles as $role)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">
                                        {{ _trans('common.No Role') }}
                                    </span>
                                @endforelse
                            </td>
                            <td class="py-3 px-4 text-center">
                                @can('user.edit')
                                    @if ($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('users.status', $user) }}" class="d-inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch d-inline-block m-0">
                                                <input class="form-check-input cursor-pointer status-toggle"
                                                    type="checkbox"
                                                    role="switch"
                                                    {{ $user->status === 'active' ? 'checked' : '' }}
                                                    onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill">{{ _trans('common.Active') }}</span>
                                    @endif
                                @else
                                    @if ($user->status === 'active')
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill">{{ _trans('common.Active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill">{{ _trans('common.Inactive') }}</span>
                                    @endif
                                @endcan
                            </td>
                            <td class="py-3 px-4 small text-muted">
                                {{ $user->last_login_at ? formatDateTime($user->last_login_at) : _trans('common.Never') }}
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('user.edit')
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit User') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @if ($user->id !== Auth::id())
                                        @can('user.delete')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteModal"
                                                data-action="{{ route('users.destroy', $user) }}"
                                                data-item-name="{{ $user->name }}"
                                                title="{{ _trans('common.Delete User') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted">{{ _trans('common.No users found matching your criteria.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-3 border-top">
                <x-ui.pagination :paginator="$users" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
