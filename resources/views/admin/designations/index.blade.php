@extends('admin.layouts.app')
@section('title', _trans('common.Designations'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Designations') }}"
        subtitle="{{ _trans('common.Manage organizational job titles, hierarchy levels, and departments') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Designations')],
        ]"
    >
        <x-slot:actions>
            @can('designation.create')
                <a href="{{ route('designations.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Designation') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter Card --}}
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('designations.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-5">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search designation name...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3 col-lg-3">
                <label for="department_id" class="form-label small fw-semibold text-muted">{{ _trans('common.Department') }}</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">{{ _trans('common.All Departments') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
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

            <div class="col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel"></i>
                    <span>{{ _trans('common.Filter') }}</span>
                </button>
                @if (request()->hasAny(['search', 'department_id', 'status']))
                    <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset Filters') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Designation List Card --}}
    <x-ui.card :title="_trans('common.Designation List')" icon="bi-award">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">{{ _trans('common.Designation') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Department') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Hierarchy Level') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Status') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($designations as $desig)
                        <tr>
                            <td class="py-3 px-4 text-muted">{{ $loop->iteration + ($designations->currentPage() - 1) * $designations->perPage() }}</td>
                            <td class="py-3 px-4">
                                <div class="fw-semibold text-dark">{{ $desig->name }}</div>
                                @if ($desig->description)
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $desig->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($desig->department)
                                    <span class="badge bg-light text-primary border px-2.5 py-1">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $desig->department->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 rounded-pill">
                                    {{ _trans('common.Level') }} {{ $desig->level }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="{{ $desig->status?->badgeClass() ?? 'badge bg-secondary' }}">
                                    {{ $desig->status?->label() ?? ucfirst($desig->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('designation.edit')
                                        <a href="{{ route('designations.edit', $desig) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @can('designation.delete')
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteModal"
                                            data-action="{{ route('designations.destroy', $desig) }}"
                                            data-item-name="{{ $desig->name }}"
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
                                <i class="bi bi-award fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-medium">{{ _trans('common.No designations found matching your criteria.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($designations->hasPages())
            <div class="px-4 py-3 border-top">
                <x-ui.pagination :paginator="$designations" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
