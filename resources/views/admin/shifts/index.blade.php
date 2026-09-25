@extends('admin.layouts.app')
@section('title', _trans('common.Shifts'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Work Shifts') }}"
        subtitle="{{ _trans('common.Manage organizational working shifts, start and end times, and grace periods') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Shifts')],
        ]"
    >
        <x-slot:actions>
            @can('shift.create')
                <a href="{{ route('shifts.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Shift') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter Card --}}
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('shifts.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5 col-lg-6">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search shift by name...') }}"
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
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset Filters') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Shift List Card --}}
    <x-ui.card :title="_trans('common.Shift List')" icon="bi-clock-history">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">{{ _trans('common.Shift Name') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Schedule') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Grace Period') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Status') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td class="py-3 px-4 text-muted">{{ $loop->iteration + ($shifts->currentPage() - 1) * $shifts->perPage() }}</td>
                            <td class="py-3 px-4">
                                <div class="fw-semibold text-dark">{{ $shift->name }}</div>
                                @if ($shift->description)
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $shift->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 font-monospace">
                                    <i class="bi bi-clock me-1 text-muted"></i>
                                    {{ formatTime($shift->start_time) }} - {{ formatTime($shift->end_time) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                    {{ $shift->grace_minutes }} {{ _trans('common.mins') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="{{ $shift->status?->badgeClass() ?? 'badge bg-secondary' }}">
                                    {{ $shift->status?->label() ?? ucfirst($shift->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('shift.edit')
                                        <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @can('shift.delete')
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteModal"
                                            data-action="{{ route('shifts.destroy', $shift) }}"
                                            data-item-name="{{ $shift->name }}"
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
                                <i class="bi bi-clock-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-medium">{{ _trans('common.No shifts found matching your criteria.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($shifts->hasPages())
            <div class="px-4 py-3 border-top">
                <x-ui.pagination :paginator="$shifts" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
