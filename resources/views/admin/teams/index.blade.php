@extends('admin.layouts.app')
@section('title', _trans('common.Teams'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Teams') }}"
        subtitle="{{ _trans('common.Manage organizational teams, project squads, and member assignments') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Teams')],
        ]"
    >
        <x-slot:actions>
            @can('team.create')
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Team') }}</span>
                </button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Total Teams') }}"
                value="{{ $stats['total'] }}"
                icon="bi-people"
                color="primary"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Active Teams') }}"
                value="{{ $stats['active'] }}"
                icon="bi-check-circle"
                color="success"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Assigned Members') }}"
                value="{{ $stats['total_assigned_members'] }}"
                icon="bi-person-check"
                color="info"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Inactive Teams') }}"
                value="{{ $stats['inactive'] }}"
                icon="bi-pause-circle"
                color="warning"
            />
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('teams.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text"
                            name="search"
                            class="form-control border-start-0 ps-0"
                            placeholder="{{ _trans('common.Search by team name, lead, or description...') }}"
                            value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">{{ _trans('common.All Statuses') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="lead_id" class="form-select">
                        <option value="">{{ _trans('common.All Team Leads') }}</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ ($filters['lead_id'] ?? '') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex justify-content-between justify-content-md-end gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-filter"></i>
                        <span>{{ _trans('common.Filter') }}</span>
                    </button>
                    @if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['lead_id']))
                        <a href="{{ route('teams.index', ['view' => $viewMode]) }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset Filters') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif

                    {{-- View Toggle --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('teams.index', array_merge($filters, ['view' => 'grid'])) }}"
                            class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            title="{{ _trans('common.Grid View') }}">
                            <i class="bi bi-grid-fill"></i>
                        </a>
                        <a href="{{ route('teams.index', array_merge($filters, ['view' => 'list'])) }}"
                            class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            title="{{ _trans('common.List View') }}">
                            <i class="bi bi-list-ul"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Teams Listing --}}
    @if ($teams->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-people text-muted" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-semibold text-dark">{{ _trans('common.No teams found') }}</h5>
                <p class="text-muted mb-3">{{ _trans('common.Create your first team to organize squads and assign team members.') }}</p>
                @can('team.create')
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ _trans('common.Create New Team') }}</span>
                    </button>
                @endcan
            </div>
        </div>
    @else
        @if ($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="row g-4 mb-4">
                @foreach ($teams as $team)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-4 d-flex flex-column">
                                {{-- Card Header: Title & Status --}}
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1 min-w-0 me-2">
                                        <h5 class="card-title mb-1 text-truncate">
                                            <a href="{{ route('teams.show', $team) }}" class="text-dark text-decoration-none fw-bold hover-primary">
                                                {{ $team->name }}
                                            </a>
                                        </h5>
                                        <span class="badge {{ $team->status->badgeClass() }} rounded-pill px-2 py-1 small">
                                            {{ $team->status->label() }}
                                        </span>
                                    </div>

                                    {{-- Actions Dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle p-0" type="button" data-bs-toggle="dropdown" style="width: 32px; height: 32px;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('teams.show', $team) }}">
                                                    <i class="bi bi-eye text-primary"></i>
                                                    <span>{{ _trans('common.View Team') }}</span>
                                                </a>
                                            </li>
                                            @can('team.edit')
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('teams.edit', $team) }}">
                                                        <i class="bi bi-pencil text-warning"></i>
                                                        <span>{{ _trans('common.Edit Team') }}</span>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('team.delete')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this team?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                            <i class="bi bi-trash"></i>
                                                            <span>{{ _trans('common.Delete Team') }}</span>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                {{-- Team Description --}}
                                <p class="text-muted small mb-3 text-truncate-2" style="min-height: 2.4rem;">
                                    {{ $team->description ?: _trans('common.No description provided.') }}
                                </p>

                                {{-- Team Lead Section --}}
                                <div class="bg-light rounded-3 p-2.5 mb-3 d-flex align-items-center gap-2">
                                    @if ($team->lead)
                                        <img src="{{ $team->lead->avatar_url }}" alt="{{ $team->lead->name }}" class="rounded-circle object-fit-cover flex-shrink-0" width="34" height="34">
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="fw-semibold small text-dark text-truncate">{{ $team->lead->name }}</div>
                                            <div class="text-muted extra-small text-truncate" style="font-size: 11px;">
                                                <i class="bi bi-star-fill text-warning me-1"></i>{{ _trans('common.Team Lead') }} · {{ $team->lead->employeeDetail?->designation?->name ?? _trans('common.Lead') }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                        <div class="text-muted small fst-italic">
                                            {{ _trans('common.No Team Lead assigned') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Footer: Members Stack & Count --}}
                                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                                    {{-- Avatar Stack --}}
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-group d-flex">
                                            @php
                                                $displayMembers = $team->members->take(4);
                                                $remainingCount = $team->members_count - 4;
                                            @endphp
                                            @foreach ($displayMembers as $m)
                                                <img src="{{ $m->avatar_url }}"
                                                    alt="{{ $m->name }}"
                                                    class="rounded-circle border border-2 border-white shadow-sm object-fit-cover"
                                                    width="30" height="30"
                                                    style="margin-left: -8px;"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $m->name }} ({{ $m->employeeDetail?->designation?->name ?? _trans('common.Member') }})">
                                            @endforeach

                                            @if ($remainingCount > 0)
                                                <div class="rounded-circle border border-2 border-white bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 30px; height: 30px; margin-left: -8px; font-size: 11px;"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $remainingCount }} {{ _trans('common.more members') }}">
                                                    +{{ $remainingCount }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($team->members_count === 0)
                                            <span class="text-muted extra-small fst-italic" style="font-size: 12px;">{{ _trans('common.0 members') }}</span>
                                        @else
                                            <span class="text-muted small ms-2">{{ $team->members_count }} {{ _trans('common.members') }}</span>
                                        @endif
                                    </div>

                                    <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        {{ _trans('common.View') }} <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- List / Table View --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ _trans('common.Team Name') }}</th>
                                <th>{{ _trans('common.Team Lead') }}</th>
                                <th>{{ _trans('common.Members') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th>{{ _trans('common.Created At') }}</th>
                                <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teams as $team)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">
                                            <a href="{{ route('teams.show', $team) }}" class="text-dark text-decoration-none hover-primary">
                                                {{ $team->name }}
                                            </a>
                                        </div>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">
                                            {{ $team->description ?: _trans('common.No description') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($team->lead)
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $team->lead->avatar_url }}" alt="{{ $team->lead->name }}" class="rounded-circle object-fit-cover" width="32" height="32">
                                                <div>
                                                    <div class="fw-semibold small text-dark">{{ $team->lead->name }}</div>
                                                    <div class="text-muted extra-small" style="font-size: 11px;">{{ $team->lead->employeeDetail?->designation?->name ?? _trans('common.Lead') }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">{{ _trans('common.Unassigned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-group d-flex">
                                                @foreach ($team->members->take(3) as $m)
                                                    <img src="{{ $m->avatar_url }}"
                                                        alt="{{ $m->name }}"
                                                        class="rounded-circle border border-2 border-white shadow-sm object-fit-cover"
                                                        width="26" height="26"
                                                        style="margin-left: -6px;"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $m->name }}">
                                                @endforeach
                                            </div>
                                            <span class="badge bg-light text-dark border ms-1">{{ $team->members_count }} {{ _trans('common.members') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $team->status->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $team->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $team->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-light text-primary" title="{{ _trans('common.View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('team.edit')
                                                <a href="{{ route('teams.edit', $team) }}" class="btn btn-sm btn-light text-warning" title="{{ _trans('common.Edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('team.delete')
                                                <form method="POST" action="{{ route('teams.destroy', $team) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this team?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="{{ _trans('common.Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                {{ _trans('common.Showing') }} {{ $teams->firstItem() ?? 0 }} {{ _trans('common.to') }} {{ $teams->lastItem() ?? 0 }} {{ _trans('common.of') }} {{ $teams->total() }} {{ _trans('common.teams') }}
            </div>
            <div>
                {{ $teams->links() }}
            </div>
        </div>
    @endif

    {{-- Quick Add Team Modal --}}
    @can('team.create')
        <div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('teams.store') }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="addTeamModalLabel">
                                <i class="bi bi-people-fill text-primary me-2"></i>{{ _trans('common.Create New Team') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="modal_team_name" class="form-label fw-semibold">
                                        {{ _trans('common.Team Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        name="name"
                                        id="modal_team_name"
                                        class="form-control"
                                        placeholder="{{ _trans('common.e.g. Frontend Engineering, Product Squad A') }}"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <label for="modal_team_status" class="form-label fw-semibold">
                                        {{ _trans('common.Status') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" id="modal_team_status" class="form-select" required>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->value }}" {{ $st->value === 'active' ? 'selected' : '' }}>
                                                {{ $st->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="modal_team_lead" class="form-label fw-semibold">
                                        {{ _trans('common.Team Lead') }}
                                    </label>
                                    <select name="lead_id" id="modal_team_lead" class="form-select select2-element" data-placeholder="{{ _trans('common.Select Team Lead') }}">
                                        <option value="">{{ _trans('common.Select Team Lead') }}</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="modal_team_members" class="form-label fw-semibold">
                                        {{ _trans('common.Team Members') }}
                                    </label>
                                    <select name="member_ids[]" id="modal_team_members" class="form-select select2-element" multiple data-placeholder="{{ _trans('common.Select Team Members (Multiple)') }}">
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small text-muted">{{ _trans('common.You can select multiple employees to assign to this team.') }}</div>
                                </div>

                                <div class="col-12">
                                    <label for="modal_team_description" class="form-label fw-semibold">
                                        {{ _trans('common.Description') }}
                                    </label>
                                    <textarea name="description"
                                        id="modal_team_description"
                                        class="form-control"
                                        rows="3"
                                        placeholder="{{ _trans('common.Describe the purpose, scope, and objectives of this team...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save Team') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('styles')
<style>
.hover-shadow {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
}
.avatar-group img:hover {
    transform: scale(1.15);
    z-index: 5;
    transition: transform 0.2s ease;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Re-initialize select2 inside modal when modal opens
    $('#addTeamModal').on('shown.bs.modal', function () {
        $(this).find('.select2-element').each(function() {
            var $el = $(this);
            $el.select2({
                placeholder: $el.data('placeholder') || 'Select option',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#addTeamModal')
            });
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
