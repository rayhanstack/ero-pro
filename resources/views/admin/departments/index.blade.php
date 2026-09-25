@extends('admin.layouts.app')
@section('title', _trans('common.Departments'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Departments') }}"
        subtitle="{{ _trans('common.Manage organizational departments and functional units') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Departments')],
        ]"
    >
        <x-slot:actions>
            @can('department.create')
                <a href="{{ route('departments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Department') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter Card --}}
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('departments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5 col-lg-6">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search by department name or code...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <label for="status" class="form-label small fw-semibold text-muted">{{ _trans('common.Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ _trans('common.All Statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel"></i>
                    <span>{{ _trans('common.Filter') }}</span>
                </button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset Filters') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Department List Card --}}
    <x-ui.card :title="_trans('common.Department List')" icon="bi-building">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">{{ _trans('common.Department Name') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Code') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Designations') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Status') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $dept)
                        <tr>
                            <td class="py-3 px-4 text-muted">{{ $loop->iteration + ($departments->currentPage() - 1) * $departments->perPage() }}</td>
                            <td class="py-3 px-4">
                                <div class="fw-semibold text-dark">{{ $dept->name }}</div>
                                @if ($dept->description)
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $dept->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-light text-dark border font-monospace">{{ $dept->code }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    {{ $dept->designations_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="{{ $dept->status?->badgeClass() ?? 'badge bg-secondary' }}">
                                    {{ $dept->status?->label() ?? ucfirst($dept->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('department.edit')
                                        <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @can('department.delete')
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteModal"
                                            data-action="{{ route('departments.destroy', $dept) }}"
                                            data-item-name="{{ $dept->name }}"
                                            title="{{ _trans('common.Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-building-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-medium">{{ _trans('common.No departments found matching your criteria.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->hasPages())
            <div class="px-4 py-3 border-top">
                <x-ui.pagination :paginator="$departments" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
