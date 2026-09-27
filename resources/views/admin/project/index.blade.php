@extends('admin.layouts.app')
@section('title', _trans('common.Projects'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Projects') }}"
        subtitle="{{ _trans('common.Manage all your active and pending projects, milestones, and deliverables') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Projects')],
        ]"
    >
        <x-slot:actions>
            @can('project.create')
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.New Project') }}</span>
                </button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Total Projects') }}"
                value="{{ $stats['total'] }}"
                icon="bi-folder2-open"
                color="primary"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Active Projects') }}"
                value="{{ $stats['active'] }}"
                icon="bi-play-circle"
                color="info"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Completed Projects') }}"
                value="{{ $stats['completed'] }}"
                icon="bi-check-circle"
                color="success"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card
                title="{{ _trans('common.Total Budget') }}"
                value="${{ number_format($stats['total_budget'], 2) }}"
                icon="bi-currency-dollar"
                color="warning"
            />
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text"
                            name="search"
                            class="form-control border-start-0 ps-0"
                            placeholder="{{ _trans('common.Search projects, code, client...') }}"
                            value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ _trans('common.Status: All') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="client_id" class="form-select">
                        <option value="">{{ _trans('common.Client: All') }}</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ ($filters['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                                {{ $client->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">{{ _trans('common.Priority: All') }}</option>
                        @foreach ($priorities as $pr)
                            <option value="{{ $pr->value }}" {{ ($filters['priority'] ?? '') === $pr->value ? 'selected' : '' }}>
                                {{ $pr->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-filter"></i>
                        <span>{{ _trans('common.Filter') }}</span>
                    </button>
                    @if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['client_id']) || !empty($filters['priority']))
                        <a href="{{ route('projects.index', ['view' => $viewMode]) }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif

                    {{-- View Toggle --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('projects.index', array_merge($filters, ['view' => 'grid'])) }}"
                            class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            title="{{ _trans('common.Grid View') }}">
                            <i class="bi bi-grid"></i>
                        </a>
                        <a href="{{ route('projects.index', array_merge($filters, ['view' => 'list'])) }}"
                            class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            title="{{ _trans('common.List View') }}">
                            <i class="bi bi-list-task"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Projects Listing --}}
    @if ($projects->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-semibold text-dark">{{ _trans('common.No projects found') }}</h5>
                <p class="text-muted mb-3">{{ _trans('common.Create a new project to start tracking milestones, budgets, and team assignments.') }}</p>
                @can('project.create')
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ _trans('common.New Project') }}</span>
                    </button>
                @endcan
            </div>
        </div>
    @else
        @if ($viewMode === 'grid')
            {{-- Grid View (Preserving exact aesthetic of existing design) --}}
            <div class="row g-4 mb-4">
                @foreach ($projects as $project)
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100 project-card shadow-sm border-0 rounded-4 position-relative hover-shadow transition-all">
                            <div class="card-body p-4 d-flex flex-column">
                                <span class="badge {{ $project->status->badgeClass() }} rounded-pill position-absolute top-0 end-0 m-3 px-2.5 py-1">
                                    {{ $project->status->label() }}
                                </span>

                                <div class="pe-5 mb-1">
                                    <span class="text-muted extra-small font-monospace" style="font-size: 11px;">{{ $project->code }}</span>
                                    <h6 class="fw-bold mb-0 text-truncate">
                                        <a href="{{ route('projects.show', $project) }}" class="text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                    </h6>
                                </div>

                                <p class="text-primary small fw-bold mb-2">
                                    @if ($project->client)
                                        <a href="{{ route('clients.show', $project->client) }}" class="text-primary text-decoration-none">
                                            {{ $project->client->company_name }}
                                        </a>
                                    @else
                                        <span class="text-muted fst-italic">{{ _trans('common.Internal Project') }}</span>
                                    @endif
                                </p>

                                <p class="text-muted small mb-3 text-truncate-2" style="min-height: 40px;">
                                    {{ $project->description ?: _trans('common.No detailed description provided.') }}
                                </p>

                                {{-- Progress --}}
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-muted">{{ _trans('common.Progress') }}</span>
                                    <span class="small fw-bold text-primary">{{ $project->progress }}%</span>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ $project->progress }}%" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                {{-- Footer --}}
                                <div class="mt-auto d-flex justify-content-between align-items-center border-top pt-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-group d-flex" style="margin-right: 10px;">
                                            @php
                                                $displayMembers = $project->members->take(3);
                                                $remaining = $project->members_count - 3;
                                            @endphp
                                            @foreach ($displayMembers as $idx => $m)
                                                <img src="{{ $m->avatar_url }}"
                                                    class="rounded-circle border border-white shadow-sm object-fit-cover"
                                                    width="30" height="30"
                                                    style="margin-right: -8px; z-index: {{ 4 - $idx }};"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $m->name }}"
                                                    alt="{{ $m->name }}">
                                            @endforeach
                                            @if ($remaining > 0)
                                                <div class="rounded-circle border border-white bg-light text-muted d-flex align-items-center justify-content-center small fw-bold"
                                                    style="width: 30px; height: 30px; z-index: 1;"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $remaining }} {{ _trans('common.more members') }}">
                                                    +{{ $remaining }}
                                                </div>
                                            @endif
                                            @if ($project->members_count === 0)
                                                <span class="small text-muted fst-italic">{{ _trans('common.Unassigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $project->deadline ? $project->deadline->format('M d') : _trans('common.No deadline') }}
                                        </div>
                                        <div class="small fw-bold mt-1 text-dark">
                                            {{ $project->formatted_budget }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light flex-grow-1 text-primary">
                                        <i class="bi bi-eye"></i> {{ _trans('common.View') }}
                                    </a>
                                    @can('project.edit')
                                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-light flex-grow-1 text-secondary">
                                            <i class="bi bi-pencil"></i> {{ _trans('common.Edit') }}
                                        </a>
                                    @endcan
                                    @can('project.delete')
                                        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this project?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger px-3" title="{{ _trans('common.Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- List View --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ _trans('common.Code') }}</th>
                                <th>{{ _trans('common.Project & Client') }}</th>
                                <th>{{ _trans('common.Manager / Team') }}</th>
                                <th>{{ _trans('common.Budget') }}</th>
                                <th>{{ _trans('common.Deadline') }}</th>
                                <th>{{ _trans('common.Priority') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th>{{ _trans('common.Progress') }}</th>
                                <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                <tr>
                                    <td class="ps-4">
                                        <span class="font-monospace text-muted small fw-bold">{{ $project->code }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            <a href="{{ route('projects.show', $project) }}" class="text-dark text-decoration-none hover-primary">
                                                {{ $project->name }}
                                            </a>
                                        </div>
                                        <div class="small text-primary">
                                            {{ $project->client?->company_name ?? _trans('common.Internal') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($project->manager)
                                                <img src="{{ $project->manager->avatar_url }}" alt="{{ $project->manager->name }}" class="rounded-circle object-fit-cover" width="28" height="28" data-bs-toggle="tooltip" title="{{ $project->manager->name }} ({{ _trans('common.Manager') }})">
                                            @endif
                                            <div class="avatar-group d-flex">
                                                @foreach ($project->members->take(3) as $m)
                                                    <img src="{{ $m->avatar_url }}" alt="{{ $m->name }}" class="rounded-circle border border-white" width="24" height="24" style="margin-left: -6px;" data-bs-toggle="tooltip" title="{{ $m->name }}">
                                                @endforeach
                                            </div>
                                            <span class="badge bg-light text-dark border ms-1">{{ $project->members_count }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $project->formatted_budget }}</span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $project->deadline ? $project->deadline->format('M d, Y') : _trans('common.TBD') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $project->priority->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $project->priority->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $project->status->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $project->status->label() }}
                                        </span>
                                    </td>
                                    <td style="min-width: 120px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%"></div>
                                            </div>
                                            <span class="small fw-semibold text-muted">{{ $project->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light text-primary" title="{{ _trans('common.View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('project.edit')
                                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-light text-secondary" title="{{ _trans('common.Edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('project.delete')
                                                <form method="POST" action="{{ route('projects.destroy', $project) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this project?') }}')">
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
                {{ _trans('common.Showing') }} {{ $projects->firstItem() ?? 0 }} {{ _trans('common.to') }} {{ $projects->lastItem() ?? 0 }} {{ _trans('common.of') }} {{ $projects->total() }} {{ _trans('common.projects') }}
            </div>
            <div>
                {{ $projects->links() }}
            </div>
        </div>
    @endif

    {{-- Quick New Project Modal --}}
    @can('project.create')
        <div class="modal fade" id="newProjectModal" tabindex="-1" aria-labelledby="newProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('projects.store') }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="newProjectModalLabel">
                                <i class="bi bi-folder-plus text-primary me-2"></i>{{ _trans('common.Create New Project') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="modal_project_name" class="form-label fw-semibold">
                                        {{ _trans('common.Project Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        name="name"
                                        id="modal_project_name"
                                        class="form-control"
                                        placeholder="{{ _trans('common.e.g. ERP NextGen Redesign') }}"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <label for="modal_project_client" class="form-label fw-semibold">
                                        {{ _trans('common.Client') }}
                                    </label>
                                    <select name="client_id" id="modal_project_client" class="form-select select2-modal" data-placeholder="{{ _trans('common.Select Client') }}">
                                        <option value="">{{ _trans('common.Select Client') }}</option>
                                        @foreach ($clients as $c)
                                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_project_manager" class="form-label fw-semibold">
                                        {{ _trans('common.Project Manager') }}
                                    </label>
                                    <select name="manager_id" id="modal_project_manager" class="form-select select2-modal" data-placeholder="{{ _trans('common.Select Manager') }}">
                                        <option value="">{{ _trans('common.Select Manager') }}</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="modal_project_priority" class="form-label fw-semibold">
                                        {{ _trans('common.Priority') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="priority" id="modal_project_priority" class="form-select" required>
                                        @foreach ($priorities as $pr)
                                            <option value="{{ $pr->value }}" {{ $pr->value === 'medium' ? 'selected' : '' }}>
                                                {{ $pr->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="modal_project_status" class="form-label fw-semibold">
                                        {{ _trans('common.Status') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" id="modal_project_status" class="form-select" required>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->value }}" {{ $st->value === 'planning' ? 'selected' : '' }}>
                                                {{ $st->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="modal_project_budget" class="form-label fw-semibold">
                                        {{ _trans('common.Budget') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="budget" id="modal_project_budget" class="form-control" placeholder="0.00">
                                </div>

                                <div class="col-md-4">
                                    <label for="modal_project_currency" class="form-label fw-semibold">
                                        {{ _trans('common.Currency') }}
                                    </label>
                                    <select name="currency_id" id="modal_project_currency" class="form-select">
                                        <option value="">{{ _trans('common.Default Currency') }}</option>
                                        @foreach ($currencies as $curr)
                                            <option value="{{ $curr->id }}">
                                                {{ $curr->code }} ({{ $curr->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="modal_project_progress" class="form-label fw-semibold">
                                        {{ _trans('common.Initial Progress (%)') }}
                                    </label>
                                    <input type="number" min="0" max="100" name="progress" id="modal_project_progress" class="form-control" value="0">
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_project_start_date" class="form-label fw-semibold">
                                        {{ _trans('common.Start Date') }}
                                    </label>
                                    <input type="date" name="start_date" id="modal_project_start_date" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_project_deadline" class="form-label fw-semibold">
                                        {{ _trans('common.Deadline') }}
                                    </label>
                                    <input type="date" name="deadline" id="modal_project_deadline" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_project_teams" class="form-label fw-semibold">
                                        {{ _trans('common.Assign Teams') }}
                                    </label>
                                    <select name="team_ids[]" id="modal_project_teams" class="form-select select2-modal" multiple data-placeholder="{{ _trans('common.Select Teams...') }}">
                                        @foreach ($teams as $t)
                                            <option value="{{ $t->id }}">
                                                {{ $t->name }} ({{ $t->members->count() }} {{ _trans('common.members') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_project_members" class="form-label fw-semibold">
                                        {{ _trans('common.Individual Members') }}
                                    </label>
                                    <select name="member_ids[]" id="modal_project_members" class="form-select select2-modal" multiple data-placeholder="{{ _trans('common.Select Members...') }}">
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="modal_project_description" class="form-label fw-semibold">
                                        {{ _trans('common.Description') }}
                                    </label>
                                    <textarea name="description" id="modal_project_description" class="form-control" rows="3" placeholder="{{ _trans('common.Key goals, deliverables, and requirements...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Create Project') }}</span>
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
    $('#newProjectModal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').each(function() {
            var $el = $(this);
            $el.select2({
                placeholder: $el.data('placeholder') || 'Select option',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#newProjectModal')
            });
        });
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
