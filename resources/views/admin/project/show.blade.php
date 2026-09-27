@extends('admin.layouts.app')
@section('title', $project->name . ' - ' . _trans('common.Project Details'))

@section('content')
    <x-ui.page-header
        title="{{ $project->name }}"
        subtitle="{{ $project->code }} · {{ $project->client ? $project->client->company_name : _trans('common.Internal Project') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Projects'), 'url' => route('projects.index')],
            ['label' => $project->name],
        ]"
    >
        <x-slot:actions>
            <div class="d-flex gap-2">
                @can('project.edit')
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i>
                        <span>{{ _trans('common.Edit Project') }}</span>
                    </a>
                @endcan
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ _trans('common.Back to Projects') }}</span>
                </a>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Project Header Overview Banner --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="font-monospace text-muted small fw-bold">{{ $project->code }}</span>
                        <span class="badge {{ $project->status->badgeClass() }} rounded-pill px-3 py-1">
                            {{ $project->status->label() }}
                        </span>
                        <span class="badge {{ $project->priority->badgeClass() }} rounded-pill px-3 py-1">
                            {{ $project->priority->label() }}
                        </span>
                    </div>

                    <h3 class="fw-bold text-dark mb-1">{{ $project->name }}</h3>

                    <p class="text-primary fw-semibold mb-3">
                        @if ($project->client)
                            <i class="bi bi-building me-1"></i>
                            <a href="{{ route('clients.show', $project->client) }}" class="text-primary text-decoration-none">
                                {{ $project->client->company_name }}
                            </a>
                        @else
                            <i class="bi bi-briefcase me-1"></i>{{ _trans('common.Internal Company Project') }}
                        @endif
                    </p>

                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-grow-1" style="max-width: 320px;">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span class="text-muted">{{ _trans('common.Completion Progress') }}</span>
                                <span class="text-primary">{{ $project->progress }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ $project->progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Key Project Metrics --}}
                <div class="col-lg-5 border-start-lg ps-lg-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <div class="text-muted extra-small text-uppercase fw-semibold mb-1" style="font-size: 11px;">{{ _trans('common.Budget') }}</div>
                                <div class="fs-5 fw-bold text-dark">{{ $project->formatted_budget }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <div class="text-muted extra-small text-uppercase fw-semibold mb-1" style="font-size: 11px;">{{ _trans('common.Deadline') }}</div>
                                <div class="fs-5 fw-bold text-dark">{{ $project->deadline ? $project->deadline->format('M d, Y') : _trans('common.TBD') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <div class="text-muted extra-small text-uppercase fw-semibold mb-1" style="font-size: 11px;">{{ _trans('common.Team Members') }}</div>
                                <div class="fs-5 fw-bold text-primary">{{ $project->members->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <div class="text-muted extra-small text-uppercase fw-semibold mb-1" style="font-size: 11px;">{{ _trans('common.Milestones') }}</div>
                                <div class="fs-5 fw-bold text-success">{{ $project->milestones->where('status', \App\Enums\MilestoneStatusEnum::COMPLETE)->count() }}/{{ $project->milestones->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills custom-pills mb-4" id="projectTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2 px-3.5 py-2.5" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewTabPane" type="button" role="tab">
                <i class="bi bi-info-circle"></i>
                <span>{{ _trans('common.Overview') }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-3.5 py-2.5" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasksTabPane" type="button" role="tab">
                <i class="bi bi-check2-square"></i>
                <span>{{ _trans('common.Tasks') }}</span>
                <span class="badge bg-primary rounded-pill">{{ $project->tasks->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-3.5 py-2.5" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#milestonesTabPane" type="button" role="tab">
                <i class="bi bi-flag"></i>
                <span>{{ _trans('common.Milestones') }}</span>
                <span class="badge bg-primary rounded-pill">{{ $project->milestones->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-3.5 py-2.5" id="files-tab" data-bs-toggle="tab" data-bs-target="#filesTabPane" type="button" role="tab">
                <i class="bi bi-folder2-open"></i>
                <span>{{ _trans('common.Files') }}</span>
                <span class="badge bg-secondary rounded-pill">{{ $project->files->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-3.5 py-2.5" id="members-tab" data-bs-toggle="tab" data-bs-target="#membersTabPane" type="button" role="tab">
                <i class="bi bi-people"></i>
                <span>{{ _trans('common.Members & Teams') }}</span>
                <span class="badge bg-info text-white rounded-pill">{{ $project->members->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-3.5 py-2.5" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activityTabPane" type="button" role="tab">
                <i class="bi bi-clock-history"></i>
                <span>{{ _trans('common.Activity') }}</span>
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="projectTabsContent">
        {{-- Tab 1: Overview --}}
        <div class="tab-pane fade show active" id="overviewTabPane" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <x-ui.card :title="_trans('common.Project Description & Scope')" icon="bi-card-text">
                        <p class="text-muted lh-base mb-4">
                            {{ $project->description ?: _trans('common.No detailed description provided for this project.') }}
                        </p>

                        <h6 class="fw-bold text-dark mb-3">{{ _trans('common.Timeline & Schedule') }}</h6>
                        <div class="row g-3 text-muted small mb-4">
                            <div class="col-sm-6">
                                <strong><i class="bi bi-calendar-event me-1 text-primary"></i>{{ _trans('common.Start Date') }}:</strong>
                                {{ $project->start_date ? $project->start_date->format('F d, Y') : _trans('common.Not specified') }}
                            </div>
                            <div class="col-sm-6">
                                <strong><i class="bi bi-calendar-check me-1 text-danger"></i>{{ _trans('common.Deadline') }}:</strong>
                                {{ $project->deadline ? $project->deadline->format('F d, Y') : _trans('common.Not specified') }}
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-3">{{ _trans('common.Audit Trail') }}</h6>
                        <div class="row g-2 text-muted small">
                            <div class="col-sm-6">
                                <strong>{{ _trans('common.Created') }}:</strong> {{ $project->created_at->format('M d, Y h:i A') }}
                            </div>
                            <div class="col-sm-6">
                                <strong>{{ _trans('common.Last Updated') }}:</strong> {{ $project->updated_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="col-lg-4">
                    {{-- Project Manager --}}
                    <x-ui.card :title="_trans('common.Project Leadership')" icon="bi-person-badge">
                        @if ($project->manager)
                            <div class="text-center py-2">
                                <img src="{{ $project->manager->avatar_url }}" alt="{{ $project->manager->name }}" class="rounded-circle object-fit-cover shadow-sm mb-2" width="64" height="64">
                                <h6 class="fw-bold text-dark mb-0">{{ $project->manager->name }}</h6>
                                <span class="badge bg-primary bg-opacity-10 text-primary mb-3">
                                    {{ _trans('common.Project Manager') }}
                                </span>
                                <div class="text-muted small text-start border-top pt-2">
                                    <div class="mb-1"><i class="bi bi-briefcase me-2"></i>{{ $project->manager->employeeDetail?->designation?->name ?? _trans('common.Manager') }}</div>
                                    <div class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $project->manager->email }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3 text-muted fst-italic">
                                <i class="bi bi-person-x fs-2"></i>
                                <div class="mt-1">{{ _trans('common.No Project Manager assigned.') }}</div>
                            </div>
                        @endif
                    </x-ui.card>

                    {{-- Client Quick Info --}}
                    @if ($project->client)
                        <div class="mt-4">
                            <x-ui.card :title="_trans('common.Client Information')" icon="bi-building">
                                <h6 class="fw-bold text-dark mb-1">{{ $project->client->company_name }}</h6>
                                <div class="text-muted small mb-2">{{ $project->client->contact_name }}</div>
                                <div class="text-muted small mb-1"><i class="bi bi-envelope me-2"></i>{{ $project->client->email }}</div>
                                @if ($project->client->phone)
                                    <div class="text-muted small mb-1"><i class="bi bi-telephone me-2"></i>{{ $project->client->phone }}</div>
                                @endif
                                <a href="{{ route('clients.show', $project->client) }}" class="btn btn-outline-primary btn-sm mt-3 w-100">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ _trans('common.View Client Profile') }}
                                </a>
                            </x-ui.card>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab 2: Tasks --}}
        <div class="tab-pane fade" id="tasksTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Project Tasks') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.All tasks and deliverables associated with this project') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-kanban"></i>
                            <span>{{ _trans('common.Open in Task Board') }}</span>
                        </a>
                        @can('task.create')
                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createProjectTaskModal">
                                <i class="bi bi-plus-lg"></i>
                                <span>{{ _trans('common.Add Task') }}</span>
                            </button>
                        @endcan
                    </div>
                </div>

                {{-- Task Quick Stats Bar --}}
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-3 text-center">
                        <div class="col-sm-4">
                            <div class="bg-white p-2.5 rounded-3 border">
                                <div class="fs-4 fw-bold text-primary">{{ $project->tasks->count() }}</div>
                                <div class="text-muted small">{{ _trans('common.Total Tasks') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-white p-2.5 rounded-3 border">
                                <div class="fs-4 fw-bold text-success">{{ $project->tasks->where('status', \App\Enums\TaskStatusEnum::DONE)->count() }}</div>
                                <div class="text-muted small">{{ _trans('common.Completed') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-white p-2.5 rounded-3 border">
                                <div class="fs-4 fw-bold text-warning">{{ $project->tasks->where('status', '!=', \App\Enums\TaskStatusEnum::DONE)->count() }}</div>
                                <div class="text-muted small">{{ _trans('common.Pending / In Progress') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($project->tasks->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check2-square text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No tasks created for this project yet') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Assign work items and track completion against project milestones.') }}</p>
                        @can('task.create')
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectTaskModal">
                                <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Create First Task') }}
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Task Title') }}</th>
                                    <th>{{ _trans('common.Priority') }}</th>
                                    <th>{{ _trans('common.Status') }}</th>
                                    <th>{{ _trans('common.Assignees') }}</th>
                                    <th>{{ _trans('common.Due Date') }}</th>
                                    <th>{{ _trans('common.Checklist') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->tasks as $task)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('tasks.index', ['project_id' => $project->id, 'search' => $task->title]) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                            @if ($task->description)
                                                <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $task->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->priority->badgeClass() }} rounded-pill">
                                                {{ $task->priority->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->status->badgeClass() }} rounded-pill">
                                                {{ $task->status->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @forelse ($task->assignees as $assignee)
                                                    <img src="{{ $assignee->avatar_url }}" class="rounded-circle border border-white {{ !$loop->first ? 'ms-n2' : '' }}" width="26" height="26" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                                                @empty
                                                    <span class="text-muted small">—</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small {{ $task->is_overdue ? 'text-danger fw-bold' : ($task->is_due_today ? 'text-warning fw-bold' : 'text-muted') }}">
                                                {{ $task->due_date ? $task->due_date->format('M d, Y') : _trans('common.No deadline') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                <i class="bi bi-check2-square text-success me-1"></i>
                                                {{ $task->checklist_progress['completed'] }}/{{ $task->checklist_progress['total'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab 3: Milestones --}}
        <div class="tab-pane fade" id="milestonesTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Project Milestones') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.Key deliverables and target completion dates') }}</p>
                    </div>
                    @can('project.edit')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ _trans('common.Add Milestone') }}</span>
                        </button>
                    @endcan
                </div>

                @if ($project->milestones->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-flag text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No milestones added yet') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Break this project down into trackable milestone goals.') }}</p>
                        @can('project.edit')
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                                <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Create First Milestone') }}
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Milestone Title') }}</th>
                                    <th>{{ _trans('common.Due Date') }}</th>
                                    <th>{{ _trans('common.Cost') }}</th>
                                    <th>{{ _trans('common.Status') }}</th>
                                    @can('project.edit')
                                        <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->milestones as $milestone)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $milestone->title }}</div>
                                            @if ($milestone->description)
                                                <div class="text-muted small">{{ $milestone->description }}</div>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : _trans('common.No deadline') }}
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            ${{ number_format($milestone->cost ?? 0, 2) }}
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('projects.milestones.toggle', $milestone) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm badge {{ $milestone->status->badgeClass() }} border-0 py-1 px-2.5" title="{{ _trans('common.Click to toggle status') }}">
                                                    @if ($milestone->status === \App\Enums\MilestoneStatusEnum::COMPLETE)
                                                        <i class="bi bi-check-circle-fill me-1"></i>
                                                    @else
                                                        <i class="bi bi-circle me-1"></i>
                                                    @endif
                                                    {{ $milestone->status->label() }}
                                                </button>
                                            </form>
                                        </td>
                                        @can('project.edit')
                                            <td class="text-end pe-4">
                                                <form method="POST" action="{{ route('projects.milestones.destroy', $milestone) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this milestone?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="{{ _trans('common.Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab 4: Files --}}
        <div class="tab-pane fade" id="filesTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Project Files & Documents') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.Upload briefs, contracts, specifications, and assets') }}</p>
                    </div>
                    @can('project.edit')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload"></i>
                            <span>{{ _trans('common.Upload File') }}</span>
                        </button>
                    @endcan
                </div>

                @if ($project->files->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-plus text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No files uploaded yet') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Attach project documentation, assets, or contract agreements.') }}</p>
                        @can('project.edit')
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                <i class="bi bi-upload me-1"></i>{{ _trans('common.Upload Document') }}
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.File Name') }}</th>
                                    <th>{{ _trans('common.Size') }}</th>
                                    <th>{{ _trans('common.Uploaded By') }}</th>
                                    <th>{{ _trans('common.Date') }}</th>
                                    <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->files as $file)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi {{ $file->icon }} fs-4"></i>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $file->file_name }}</div>
                                                    <span class="text-muted extra-small" style="font-size: 11px;">{{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small text-muted">{{ $file->file_size ?: _trans('common.N/A') }}</td>
                                        <td>
                                            @if ($file->uploader)
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $file->uploader->avatar_url }}" alt="{{ $file->uploader->name }}" class="rounded-circle object-fit-cover" width="24" height="24">
                                                    <span class="small text-dark">{{ $file->uploader->name }}</span>
                                                </div>
                                            @else
                                                <span class="small text-muted fst-italic">{{ _trans('common.System') }}</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $file->created_at->format('M d, Y') }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('projects.files.download', $file) }}" class="btn btn-sm btn-light text-primary" title="{{ _trans('common.Download') }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @can('project.edit')
                                                    <form method="POST" action="{{ route('projects.files.destroy', $file) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this file?') }}')">
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
                @endif
            </div>
        </div>

        {{-- Tab 5: Members & Teams --}}
        <div class="tab-pane fade" id="membersTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Project Workforce & Squads') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.Employees assigned to work on this project') }}</p>
                    </div>
                    @can('project.edit')
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#assignTeamModal">
                                <i class="bi bi-people"></i>
                                <span>{{ _trans('common.Assign Squad / Team') }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                <i class="bi bi-person-plus-fill"></i>
                                <span>{{ _trans('common.Add Individual') }}</span>
                            </button>
                        </div>
                    @endcan
                </div>

                @if ($project->members->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No members assigned yet') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Assign individuals or squads to start collaborating.') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Employee') }}</th>
                                    <th>{{ _trans('common.Designation') }}</th>
                                    <th>{{ _trans('common.Department') }}</th>
                                    <th>{{ _trans('common.Role in Project') }}</th>
                                    <th>{{ _trans('common.Email / Phone') }}</th>
                                    @can('project.edit')
                                        <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->members as $member)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover shadow-sm" width="36" height="36">
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $member->name }}</div>
                                                    <span class="font-monospace text-muted extra-small" style="font-size: 11px;">{{ $member->emp_code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small fw-medium text-dark">{{ $member->employeeDetail?->designation?->name ?? _trans('common.N/A') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $member->employeeDetail?->department?->name ?? _trans('common.General') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">
                                                {{ ucfirst($member->pivot->role ?? 'member') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small text-dark">{{ $member->email }}</div>
                                            @if ($member->phone)
                                                <div class="text-muted extra-small" style="font-size: 11px;">{{ $member->phone }}</div>
                                            @endif
                                        </td>
                                        @can('project.edit')
                                            <td class="text-end pe-4">
                                                <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to remove this member from the project?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ _trans('common.Remove') }}">
                                                        <i class="bi bi-person-x"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab 6: Activity Logs --}}
        <div class="tab-pane fade" id="activityTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Project Audit & Activity History') }}</h5>
                </div>
                <div class="card-body p-4">
                    @if ($activities->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clock-history fs-2"></i>
                            <div class="mt-2">{{ _trans('common.No activity records found.') }}</div>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($activities as $act)
                                <li class="list-group-item px-0 py-3 d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-light p-2 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <div class="fw-semibold text-dark">{{ $act->description }}</div>
                                            <span class="text-muted extra-small" style="font-size: 11px;">{{ $act->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-muted small">
                                            {{ _trans('common.By') }}: {{ $act->causer?->name ?? _trans('common.System') }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    {{-- 1. Add Milestone Modal --}}
    @can('project.edit')
        <div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('projects.milestones.store', $project) }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-flag-fill text-primary me-2"></i>{{ _trans('common.Add Milestone') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ _trans('common.Milestone Title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required placeholder="{{ _trans('common.e.g. Beta MVP Release') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">{{ _trans('common.Due Date') }}</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">{{ _trans('common.Cost') }}</label>
                                    <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ _trans('common.Description') }}</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="{{ _trans('common.Milestone scope details...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ _trans('common.Save Milestone') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. Upload File Modal --}}
        <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-upload text-primary me-2"></i>{{ _trans('common.Upload Project File') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ _trans('common.Choose File') }} <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" required>
                                <div class="form-text small text-muted">{{ _trans('common.Supported formats: PDF, DOCX, XLSX, ZIP, PNG, JPG. Max: 20MB') }}</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ _trans('common.Upload') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. Add Individual Member Modal --}}
        <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('projects.members.store', $project) }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-person-plus-fill text-primary me-2"></i>{{ _trans('common.Add Team Members') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if ($availableEmployees->isEmpty())
                                <div class="alert alert-info mb-0">{{ _trans('common.All active employees are already assigned to this project.') }}</div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ _trans('common.Select Employees') }} <span class="text-danger">*</span></label>
                                    <select name="employee_ids[]" id="modal_employee_ids" class="form-select select2-modal" multiple required data-placeholder="{{ _trans('common.Select employees...') }}">
                                        @foreach ($availableEmployees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ _trans('common.Role in Project') }}</label>
                                    <input type="text" name="role" class="form-control" placeholder="{{ _trans('common.e.g. Lead Developer, QA Specialist') }}" value="member">
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            @if (!$availableEmployees->isEmpty())
                                <button type="submit" class="btn btn-primary">{{ _trans('common.Assign Members') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 4. Assign Entire Team Modal --}}
        <div class="modal fade" id="assignTeamModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('projects.assign-team', $project) }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-people-fill text-primary me-2"></i>{{ _trans('common.Assign Squad / Team') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ _trans('common.Select Squad / Team') }} <span class="text-danger">*</span></label>
                                <select name="team_id" class="form-select" required>
                                    <option value="">{{ _trans('common.Select Team...') }}</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">
                                            {{ $team->name }} ({{ $team->members->count() }} {{ _trans('common.members') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small text-muted mt-2">
                                    {{ _trans('common.All members and lead of the selected team will be added to this project workforce.') }}
                                </div>
                            </div>
                        </div>
        {{-- 5. Create Task Modal --}}
        @can('task.create')
            <div class="modal fade" id="createProjectTaskModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow rounded-4">
                        <form method="POST" action="{{ route('tasks.store') }}" class="needs-validation">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-plus-circle text-primary me-2"></i>{{ _trans('common.Add Task to Project') }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">{{ _trans('common.Task Title') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" required placeholder="{{ _trans('common.e.g. Implement user authentication') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Priority') }} <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-select" required>
                                            @foreach (\App\Enums\TaskPriorityEnum::cases() as $priority)
                                                <option value="{{ $priority->value }}" {{ $priority->value === 'medium' ? 'selected' : '' }}>
                                                    {{ $priority->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            @foreach (\App\Enums\TaskStatusEnum::cases() as $status)
                                                <option value="{{ $status->value }}" {{ $status->value === 'todo' ? 'selected' : '' }}>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Assignees') }}</label>
                                        <select name="assignee_ids[]" id="project_task_assignees" class="form-select select2-modal" multiple data-placeholder="{{ _trans('common.Select assignees...') }}">
                                            @foreach ($project->members as $member)
                                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->employeeDetail?->designation?->name ?? _trans('common.Member') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Estimated Hours') }}</label>
                                        <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" placeholder="0.0">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Start Date') }}</label>
                                        <input type="date" name="start_date" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ _trans('common.Due Date') }}</label>
                                        <input type="date" name="due_date" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">{{ _trans('common.Description') }}</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="{{ _trans('common.Provide clear details, goals and acceptance criteria...') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ _trans('common.Create Task') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endcan
@endsection

@push('styles')
<style>
.custom-pills .nav-link {
    color: #4b5563;
    font-weight: 500;
    border-radius: 0.5rem;
    background-color: #f3f4f6;
    margin-right: 0.5rem;
    transition: all 0.2s ease;
}
.custom-pills .nav-link.active {
    color: #ffffff;
    background-color: #4f46e5;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}
@media (min-width: 992px) {
    .border-start-lg {
        border-left: 1px solid #e5e7eb !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#addMemberModal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').each(function() {
            var $el = $(this);
            $el.select2({
                placeholder: $el.data('placeholder') || 'Select option',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#addMemberModal')
            });
        });
    });
});
</script>
@endpush
