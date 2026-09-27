@extends('admin.layouts.app')

@section('title', _trans('common.Leave Types'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Leave Types') }}"
        subtitle="{{ _trans('common.Configure annual quotas, paid/unpaid policies, and carry-forward rules') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Leaves'), 'url' => route('leaves.my')],
            ['label' => _trans('common.Leave Types')],
        ]"
    >
        <x-slot:actions>
            @can('leave.manage')
                <a href="{{ route('leave-types.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Leave Type') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter Card --}}
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('leave-types.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-5">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search by name, code, description...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-4 col-lg-3">
                <label for="status" class="form-label small fw-semibold text-muted">{{ _trans('common.Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ _trans('common.All Statuses') }}</option>
                    @foreach (\App\Enums\StatusEnum::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i>{{ _trans('common.Filter') }}
                </button>
                <a href="{{ route('leave-types.index') }}" class="btn btn-light border" title="{{ _trans('common.Reset') }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </x-ui.card>

    {{-- Table Card --}}
    <x-ui.card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">{{ _trans('common.Name & Code') }}</th>
                        <th>{{ _trans('common.Days / Year') }}</th>
                        <th>{{ _trans('common.Type') }}</th>
                        <th>{{ _trans('common.Carry Forward') }}</th>
                        <th>{{ _trans('common.Status') }}</th>
                        <th>{{ _trans('common.Description') }}</th>
                        <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveTypes as $type)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill px-2.5 py-1 text-white" style="background-color: {{ $type->color }}">
                                        {{ $type->code }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $type->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1 fw-bold">
                                    {{ $type->days_per_year }} {{ _trans('common.Days') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $type->is_paid ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill">
                                    {{ $type->is_paid ? _trans('common.Paid Leave') : _trans('common.Unpaid Leave') }}
                                </span>
                            </td>
                            <td>
                                @if ($type->carry_forward)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>{{ _trans('common.Yes') }} (Max: {{ $type->max_carry }}d)
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill">
                                        {{ _trans('common.No') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $type->status->badgeClass() }} rounded-pill">
                                    {{ $type->status->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block text-muted small" style="max-width: 200px;" title="{{ $type->description }}">
                                    {{ $type->description ?: '—' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    @can('leave.manage')
                                        <a href="{{ route('leave-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('leave-types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this leave type?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ _trans('common.Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-tags fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                {{ _trans('common.No leave types found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leaveTypes->hasPages())
            <div class="card-footer bg-transparent border-0 py-3">
                {{ $leaveTypes->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection
