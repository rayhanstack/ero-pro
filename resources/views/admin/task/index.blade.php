@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Task Board'))

@section('content')
    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Task Board') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Manage deliverables, track agile workflows, and drag tasks across stages') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- My Tasks Toggle --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('page'), ['my_tasks' => request('my_tasks') ? null : 1])) }}"
                class="btn btn-sm {{ request('my_tasks') ? 'btn-primary' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-1">
                <i class="bi bi-person-check"></i>
                <span>{{ _trans('common.My Tasks') }}</span>
            </a>

            {{-- Filter Toggle Button --}}
            <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#taskFilterCollapse">
                <i class="bi bi-filter"></i>
                <span>{{ _trans('common.Filter') }}</span>
                @if(request()->hasAny(['search', 'project_id', 'assignee_id', 'priority', 'due', 'status']))
                    <span class="badge bg-primary rounded-pill ms-1">!</span>
                @endif
            </button>

            {{-- View Switcher (Kanban / List) --}}
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('tasks.index', array_merge(request()->except('page'), ['view' => 'kanban'])) }}"
                    class="btn {{ $viewMode !== 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="{{ _trans('common.Kanban Board') }}">
                    <i class="bi bi-kanban"></i>
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->except('page'), ['view' => 'list'])) }}"
                    class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="{{ _trans('common.List View') }}">
                    <i class="bi bi-list-task"></i>
                </a>
            </div>

            {{-- New Task Button --}}
            @can('task.create')
                <button class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createTaskModal" onclick="prepareCreateTaskModal('todo')">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.New Task') }}</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Filter Collapse Box --}}
    <div class="collapse {{ request()->hasAny(['search', 'project_id', 'assignee_id', 'priority', 'due', 'status']) ? 'show' : '' }} mb-4" id="taskFilterCollapse">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form method="GET" action="{{ route('tasks.index') }}" class="row g-2 align-items-end">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if (request('my_tasks'))
                    <input type="hidden" name="my_tasks" value="1">
                @endif

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Search') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="{{ _trans('common.Search task title or desc...') }}" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Project') }}</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Projects') }}</option>
                        @foreach ($projects as $proj)
                            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Assignee') }}</label>
                    <select name="assignee_id" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Assignees') }}</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('assignee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Priority') }}</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Priorities') }}</option>
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}" {{ request('priority') == $p->value ? 'selected' : '' }}>
                                {{ $p->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Due Date') }}</label>
                    <select name="due" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.Any Time') }}</option>
                        <option value="overdue" {{ request('due') == 'overdue' ? 'selected' : '' }}>{{ _trans('common.Overdue') }}</option>
                        <option value="today" {{ request('due') == 'today' ? 'selected' : '' }}>{{ _trans('common.Due Today') }}</option>
                        <option value="this_week" {{ request('due') == 'this_week' ? 'selected' : '' }}>{{ _trans('common.Due This Week') }}</option>
                        <option value="upcoming" {{ request('due') == 'upcoming' ? 'selected' : '' }}>{{ _trans('common.Upcoming') }}</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="{{ _trans('common.Apply Filters') }}">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->hasAny(['search', 'project_id', 'assignee_id', 'priority', 'due', 'status', 'my_tasks']))
                        <a href="{{ route('tasks.index', ['view' => $viewMode]) }}" class="btn btn-sm btn-light border" title="{{ _trans('common.Clear') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if ($viewMode === 'list')
        {{-- ================= LIST VIEW ================= --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Task') }}</th>
                            <th>{{ _trans('common.Project') }}</th>
                            <th>{{ _trans('common.Priority') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Assignees') }}</th>
                            <th>{{ _trans('common.Due Date') }}</th>
                            <th>{{ _trans('common.Checklist') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td class="ps-4">
                                    <a href="javascript:void(0)" onclick="openTaskDetail({{ $task->id }})" class="fw-bold text-dark text-decoration-none">
                                        {{ $task->title }}
                                    </a>
                                    @if ($task->description)
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $task->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($task->project)
                                        <a href="{{ route('projects.show', $task->project) }}" class="small fw-semibold text-primary text-decoration-none">
                                            {{ $task->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
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
                                        @foreach ($task->assignees->take(3) as $assignee)
                                            <img src="{{ $assignee->avatar_url }}" class="rounded-circle border border-white {{ !$loop->first ? 'ms-n2' : '' }}" width="26" height="26" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                                        @endforeach
                                        @if ($task->assignees->count() > 3)
                                            <span class="badge rounded-circle bg-secondary ms-n1 fs-8" style="width: 22px; height: 22px; line-height: 14px;">+{{ $task->assignees->count() - 3 }}</span>
                                        @endif
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
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" onclick="openTaskDetail({{ $task->id }})" title="{{ _trans('common.View Details') }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @can('task.edit')
                                            <button type="button" class="btn btn-outline-primary" onclick="editTaskModal({{ $task->id }})" title="{{ _trans('common.Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('task.delete')
                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this task?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ _trans('common.Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                    {{ _trans('common.No tasks found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tasks && $tasks->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    @else
        {{-- ================= KANBAN BOARD VIEW ================= --}}
        <div class="kanban-board d-flex gap-3 overflow-auto pb-4" style="min-height: calc(100vh - 220px);">

            {{-- COLUMN 1: TO DO --}}
            <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
                <div class="bg-light rounded-4 p-3 h-100 d-flex flex-column border">
                    <div class="kanban-col-header border-start border-4 border-info ps-2 mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.To Do') }}</h6>
                        <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25" id="count-todo">
                            {{ $boardTasks['todo']->count() }}
                        </span>
                    </div>

                    <div class="kanban-tasks pb-2 flex-grow-1" id="kanban-column-todo" data-status="todo" style="min-height: 250px;">
                        @foreach ($boardTasks['todo'] as $task)
                            @include('admin.task.partials.card', ['task' => $task])
                        @endforeach
                    </div>

                    @can('task.create')
                        <button class="btn btn-sm btn-outline-secondary w-100 border-dashed mt-2" onclick="prepareCreateTaskModal('todo')">
                            <i class="bi bi-plus"></i> {{ _trans('common.Add Task') }}
                        </button>
                    @endcan
                </div>
            </div>

            {{-- COLUMN 2: IN PROGRESS --}}
            <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
                <div class="bg-light rounded-4 p-3 h-100 d-flex flex-column border">
                    <div class="kanban-col-header border-start border-4 border-warning ps-2 mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.In Progress') }}</h6>
                        <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25" id="count-in_progress">
                            {{ $boardTasks['in_progress']->count() }}
                        </span>
                    </div>

                    <div class="kanban-tasks pb-2 flex-grow-1" id="kanban-column-in_progress" data-status="in_progress" style="min-height: 250px;">
                        @foreach ($boardTasks['in_progress'] as $task)
                            @include('admin.task.partials.card', ['task' => $task])
                        @endforeach
                    </div>

                    @can('task.create')
                        <button class="btn btn-sm btn-outline-secondary w-100 border-dashed mt-2" onclick="prepareCreateTaskModal('in_progress')">
                            <i class="bi bi-plus"></i> {{ _trans('common.Add Task') }}
                        </button>
                    @endcan
                </div>
            </div>

            {{-- COLUMN 3: IN REVIEW --}}
            <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
                <div class="bg-light rounded-4 p-3 h-100 d-flex flex-column border">
                    <div class="kanban-col-header border-start border-4 border-primary ps-2 mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.In Review') }}</h6>
                        <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25" id="count-review">
                            {{ $boardTasks['review']->count() }}
                        </span>
                    </div>

                    <div class="kanban-tasks pb-2 flex-grow-1" id="kanban-column-review" data-status="review" style="min-height: 250px;">
                        @foreach ($boardTasks['review'] as $task)
                            @include('admin.task.partials.card', ['task' => $task])
                        @endforeach
                    </div>

                    @can('task.create')
                        <button class="btn btn-sm btn-outline-secondary w-100 border-dashed mt-2" onclick="prepareCreateTaskModal('review')">
                            <i class="bi bi-plus"></i> {{ _trans('common.Add Task') }}
                        </button>
                    @endcan
                </div>
            </div>

            {{-- COLUMN 4: DONE --}}
            <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
                <div class="bg-light rounded-4 p-3 h-100 d-flex flex-column border">
                    <div class="kanban-col-header border-start border-4 border-success ps-2 mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Done') }}</h6>
                        <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25" id="count-done">
                            {{ $boardTasks['done']->count() }}
                        </span>
                    </div>

                    <div class="kanban-tasks pb-2 flex-grow-1" id="kanban-column-done" data-status="done" style="min-height: 250px;">
                        @foreach ($boardTasks['done'] as $task)
                            @include('admin.task.partials.card', ['task' => $task, 'isDone' => true])
                        @endforeach
                    </div>

                    @can('task.create')
                        <button class="btn btn-sm btn-outline-secondary w-100 border-dashed mt-2" onclick="prepareCreateTaskModal('done')">
                            <i class="bi bi-plus"></i> {{ _trans('common.Add Task') }}
                        </button>
                    @endcan
                </div>
            </div>

        </div>
    @endif

    {{-- Offcanvas Task Details Panel --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="taskDetailOffcanvas" style="width: 500px; max-width: 90vw;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="taskDetailOffcanvasLabel">{{ _trans('common.Task Details') }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4" id="taskDetailOffcanvasBody">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted small mt-2">{{ _trans('common.Loading task details...') }}</p>
            </div>
        </div>
    </div>

    {{-- Create Task Modal --}}
    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="createTaskModalLabel">
                        <i class="bi bi-plus-circle text-primary me-2"></i>{{ _trans('common.Create New Task') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('tasks.store') }}" id="createTaskForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="create_title" class="form-label fw-semibold">
                                    {{ _trans('common.Task Title') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="create_title" class="form-control" placeholder="{{ _trans('common.e.g. Implement OAuth2 endpoints') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="create_project_id" class="form-label fw-semibold">
                                    {{ _trans('common.Project') }}
                                </label>
                                <select name="project_id" id="create_project_id" class="form-select select2-modal" data-placeholder="{{ _trans('common.Select Project (Optional)') }}">
                                    <option value="">{{ _trans('common.Select Project (Optional)') }}</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="create_assignees" class="form-label fw-semibold">
                                    {{ _trans('common.Assignees') }}
                                </label>
                                <select name="assignees[]" id="create_assignees" class="form-select select2-modal" multiple data-placeholder="{{ _trans('common.Select Assignees') }}">
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="create_priority" class="form-label fw-semibold">
                                    {{ _trans('common.Priority') }} <span class="text-danger">*</span>
                                </label>
                                <select name="priority" id="create_priority" class="form-select" required>
                                    @foreach ($priorities as $pri)
                                        <option value="{{ $pri->value }}" {{ $pri->value === 'medium' ? 'selected' : '' }}>
                                            {{ $pri->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="create_status" class="form-label fw-semibold">
                                    {{ _trans('common.Status') }} <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="create_status" class="form-select" required>
                                    @foreach ($statuses as $st)
                                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="create_estimated_hours" class="form-label fw-semibold">
                                    {{ _trans('common.Estimated Hours') }}
                                </label>
                                <input type="number" step="0.5" min="0" name="estimated_hours" id="create_estimated_hours" class="form-control" placeholder="e.g. 8.5">
                            </div>

                            <div class="col-md-6">
                                <label for="create_start_date" class="form-label fw-semibold">
                                    {{ _trans('common.Start Date') }}
                                </label>
                                <input type="date" name="start_date" id="create_start_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="create_due_date" class="form-label fw-semibold">
                                    {{ _trans('common.Due Date') }}
                                </label>
                                <input type="date" name="due_date" id="create_due_date" class="form-control">
                            </div>

                            <div class="col-12">
                                <label for="create_description" class="form-label fw-semibold">
                                    {{ _trans('common.Description & Scope') }}
                                </label>
                                <textarea name="description" id="create_description" class="form-control" rows="3" placeholder="{{ _trans('common.Add detailed acceptance criteria and technical notes...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Create Task') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Task Modal --}}
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="editTaskModalLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>{{ _trans('common.Edit Task') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editTaskForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="edit_title" class="form-label fw-semibold">
                                    {{ _trans('common.Task Title') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_project_id" class="form-label fw-semibold">
                                    {{ _trans('common.Project') }}
                                </label>
                                <select name="project_id" id="edit_project_id" class="form-select select2-modal-edit" data-placeholder="{{ _trans('common.Select Project') }}">
                                    <option value="">{{ _trans('common.Select Project (Optional)') }}</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_assignees" class="form-label fw-semibold">
                                    {{ _trans('common.Assignees') }}
                                </label>
                                <select name="assignees[]" id="edit_assignees" class="form-select select2-modal-edit" multiple data-placeholder="{{ _trans('common.Select Assignees') }}">
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_priority" class="form-label fw-semibold">
                                    {{ _trans('common.Priority') }} <span class="text-danger">*</span>
                                </label>
                                <select name="priority" id="edit_priority" class="form-select" required>
                                    @foreach ($priorities as $pri)
                                        <option value="{{ $pri->value }}">{{ $pri->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_status" class="form-label fw-semibold">
                                    {{ _trans('common.Status') }} <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    @foreach ($statuses as $st)
                                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_estimated_hours" class="form-label fw-semibold">
                                    {{ _trans('common.Estimated Hours') }}
                                </label>
                                <input type="number" step="0.5" min="0" name="estimated_hours" id="edit_estimated_hours" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_start_date" class="form-label fw-semibold">
                                    {{ _trans('common.Start Date') }}
                                </label>
                                <input type="date" name="start_date" id="edit_start_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_due_date" class="form-label fw-semibold">
                                    {{ _trans('common.Due Date') }}
                                </label>
                                <input type="date" name="due_date" id="edit_due_date" class="form-control">
                            </div>

                            <div class="col-12">
                                <label for="edit_description" class="form-label fw-semibold">
                                    {{ _trans('common.Description & Scope') }}
                                </label>
                                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Changes') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Local SortableJS Asset --}}
    <script src="{{ asset('assets/admin/js/sortable.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // CSRF Setup for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Select2 on modals
            $('#createTaskModal').on('shown.bs.modal', function () {
                $(this).find('.select2-modal').each(function() {
                    var $el = $(this);
                    $el.select2({
                        placeholder: $el.data('placeholder') || 'Select option',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#createTaskModal')
                    });
                });
            });

            $('#editTaskModal').on('shown.bs.modal', function () {
                $(this).find('.select2-modal-edit').each(function() {
                    var $el = $(this);
                    $el.select2({
                        placeholder: $el.data('placeholder') || 'Select option',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editTaskModal')
                    });
                });
            });

            // ================= KANBAN SORTABLE DRAG & DROP =================
            var columns = ['todo', 'in_progress', 'review', 'done'];
            columns.forEach(function(colStatus) {
                var el = document.getElementById('kanban-column-' + colStatus);
                if (el) {
                    new Sortable(el, {
                        group: 'tasks',
                        animation: 150,
                        handle: '.grip-handle',
                        ghostClass: 'bg-primary-subtle',
                        dragClass: 'shadow-lg',
                        onEnd: function(evt) {
                            var itemEl = evt.item;
                            var taskId = $(itemEl).data('id');
                            var toColumn = evt.to;
                            var newStatus = $(toColumn).data('status');
                            var newIndex = evt.newIndex;

                            // Collect ordered IDs in the target column
                            var order = [];
                            $(toColumn).children('.task-card').each(function() {
                                order.push($(this).data('id'));
                            });

                            // Send AJAX move request
                            $.ajax({
                                url: '/tasks/' + taskId + '/move',
                                type: 'PATCH',
                                data: {
                                    status: newStatus,
                                    position: newIndex,
                                    order: order
                                },
                                success: function(res) {
                                    if (window.toastr) {
                                        window.toastr.success(res.message || 'Task moved successfully.');
                                    }
                                    updateColumnCounters();
                                    applyCardStyles(itemEl, newStatus);
                                },
                                error: function(xhr) {
                                    if (window.toastr) {
                                        window.toastr.error('Failed to move task.');
                                    }
                                    console.error(xhr);
                                }
                            });
                        }
                    });
                }
            });

            function updateColumnCounters() {
                columns.forEach(function(col) {
                    var count = $('#kanban-column-' + col).children('.task-card').length;
                    $('#count-' + col).text(count);
                });
            }

            function applyCardStyles(cardEl, status) {
                var $card = $(cardEl);
                if (status === 'done') {
                    $card.addClass('opacity-75');
                    $card.find('h6').addClass('text-decoration-line-through');
                } else {
                    $card.removeClass('opacity-75');
                    $card.find('h6').removeClass('text-decoration-line-through');
                }
            }

            // ================= TASK DETAIL OFFCANVAS =================
            window.openTaskDetail = function(taskId) {
                var offcanvasEl = document.getElementById('taskDetailOffcanvas');
                var bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                $('#taskDetailOffcanvasBody').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted small mt-2">Loading task details...</p>
                    </div>
                `);
                bsOffcanvas.show();

                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'GET',
                    headers: { 'Accept': 'application/json' },
                    success: function(res) {
                        if (res.html) {
                            $('#taskDetailOffcanvasBody').html(res.html);
                            bindDetailEventHandlers(taskId);
                        }
                    },
                    error: function() {
                        $('#taskDetailOffcanvasBody').html('<div class="alert alert-danger">Error loading task details.</div>');
                    }
                });
            };

            function bindDetailEventHandlers(taskId) {
                // Checklist toggle
                $(document).off('change', '.checklist-toggle').on('change', '.checklist-toggle', function() {
                    var url = $(this).data('url');
                    var isChecked = $(this).is(':checked');
                    var $label = $(this).siblings('label');

                    $.ajax({
                        url: url,
                        type: 'PATCH',
                        success: function() {
                            if (isChecked) {
                                $label.addClass('text-decoration-line-through text-muted').removeClass('text-dark');
                            } else {
                                $label.removeClass('text-decoration-line-through text-muted').addClass('text-dark');
                            }
                        }
                    });
                });

                // Add checklist item
                $('#addChecklistForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    var $input = $(this).find('input[name="title"]');
                    var title = $input.val();

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { title: title },
                        success: function(res) {
                            $input.val('');
                            openTaskDetail(taskId);
                        }
                    });
                });

                // Delete checklist
                $(document).off('click', '.delete-checklist-btn').on('click', '.delete-checklist-btn', function() {
                    var url = $(this).data('url');
                    var rowId = $(this).data('row');
                    if (confirm('Delete checklist item?')) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function() {
                                $('#' + rowId).remove();
                            }
                        });
                    }
                });

                // Add comment
                $('#addCommentForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    var $textarea = $(this).find('textarea[name="comment"]');
                    var comment = $textarea.val();

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { comment: comment },
                        success: function(res) {
                            $textarea.val('');
                            openTaskDetail(taskId);
                        }
                    });
                });

                // Delete comment
                $(document).off('click', '.delete-comment-btn').on('click', '.delete-comment-btn', function() {
                    var url = $(this).data('url');
                    var rowId = $(this).data('row');
                    if (confirm('Delete this comment?')) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function() {
                                $('#' + rowId).remove();
                            }
                        });
                    }
                });

                // Upload attachment
                $('#uploadAttachmentForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    var formData = new FormData(this);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            openTaskDetail(taskId);
                        }
                    });
                });

                // Delete attachment
                $(document).off('click', '.delete-attachment-btn').on('click', '.delete-attachment-btn', function() {
                    var url = $(this).data('url');
                    var rowId = $(this).data('row');
                    if (confirm('Delete this file?')) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function() {
                                $('#' + rowId).remove();
                            }
                        });
                    }
                });
            }

            // Prepare create task modal with prefilled status
            window.prepareCreateTaskModal = function(status) {
                $('#create_status').val(status || 'todo');
            };

            // Edit Task Modal Trigger
            window.editTaskModal = function(taskId) {
                // Close offcanvas if open
                var offcanvasEl = document.getElementById('taskDetailOffcanvas');
                var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (bsOffcanvas) {
                    bsOffcanvas.hide();
                }

                $.ajax({
                    url: '/tasks/' + taskId,
                    type: 'GET',
                    headers: { 'Accept': 'application/json' },
                    success: function(res) {
                        var task = res.data;
                        $('#editTaskForm').attr('action', '/tasks/' + task.id);
                        $('#edit_title').val(task.title);
                        $('#edit_project_id').val(task.project_id).trigger('change');
                        $('#edit_priority').val(task.priority);
                        $('#edit_status').val(task.status);
                        $('#edit_estimated_hours').val(task.estimated_hours);
                        $('#edit_start_date').val(task.start_date ? task.start_date.substring(0, 10) : '');
                        $('#edit_due_date').val(task.due_date ? task.due_date.substring(0, 10) : '');
                        $('#edit_description').val(task.description);

                        var assigneeIds = (task.assignees || []).map(function(a) { return a.id; });
                        $('#edit_assignees').val(assigneeIds).trigger('change');

                        var modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                        modal.show();
                    }
                });
            };
        });
    </script>
@endpush
