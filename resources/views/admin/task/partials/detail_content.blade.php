<div class="task-detail-wrapper">
    {{-- Header: Status & Priority --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $task->status->badgeClass() }} px-3 py-1.5 rounded-pill fs-7">
                {{ $task->status->label() }}
            </span>
            <span class="badge {{ $task->priority->badgeClass() }} px-3 py-1.5 rounded-pill fs-7">
                <i class="bi bi-flag-fill me-1"></i>{{ $task->priority->label() }}
            </span>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                @can('task.edit')
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center gap-2" onclick="editTaskModal({{ $task->id }})">
                            <i class="bi bi-pencil text-primary"></i>
                            <span>{{ _trans('common.Edit Task') }}</span>
                        </button>
                    </li>
                @endcan
                @can('task.delete')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this task?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i class="bi bi-trash"></i>
                                <span>{{ _trans('common.Delete Task') }}</span>
                            </button>
                        </form>
                    </li>
                @endcan
            </ul>
        </div>
    </div>

    {{-- Title & Project --}}
    <h4 class="fw-bold text-dark mb-1">{{ $task->title }}</h4>
    @if ($task->project)
        <p class="text-primary fw-semibold small mb-3">
            <i class="bi bi-folder2-open me-1"></i><a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">{{ $task->project->name }}</a>
        </p>
    @endif

    {{-- Key Metadata Badges --}}
    <div class="bg-light p-3 rounded-3 mb-4">
        <div class="row g-3 small">
            <div class="col-sm-6">
                <span class="text-muted d-block mb-1">{{ _trans('common.Start Date') }}</span>
                <span class="fw-semibold text-dark">
                    <i class="bi bi-calendar-event me-1 text-primary"></i>
                    {{ $task->start_date ? $task->start_date->format('M d, Y') : _trans('common.Not set') }}
                </span>
            </div>
            <div class="col-sm-6">
                <span class="text-muted d-block mb-1">{{ _trans('common.Due Date') }}</span>
                <span class="fw-semibold {{ $task->is_overdue ? 'text-danger' : ($task->is_due_today ? 'text-warning' : 'text-dark') }}">
                    <i class="bi bi-calendar-check me-1"></i>
                    {{ $task->due_date ? $task->due_date->format('M d, Y') : _trans('common.No deadline') }}
                    @if ($task->is_overdue)
                        <span class="badge bg-danger ms-1">{{ _trans('common.Overdue') }}</span>
                    @elseif ($task->is_due_today)
                        <span class="badge bg-warning text-dark ms-1">{{ _trans('common.Due Today') }}</span>
                    @endif
                </span>
            </div>
            @if ($task->estimated_hours)
                <div class="col-sm-6">
                    <span class="text-muted d-block mb-1">{{ _trans('common.Estimated Time') }}</span>
                    <span class="fw-semibold text-dark">
                        <i class="bi bi-clock me-1 text-info"></i>{{ $task->estimated_hours }} {{ _trans('common.hrs') }}
                    </span>
                </div>
            @endif
            <div class="col-sm-6">
                <span class="text-muted d-block mb-1">{{ _trans('common.Created By') }}</span>
                <span class="fw-semibold text-dark">
                    {{ $task->creator?->name ?? _trans('common.System') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if ($task->description)
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Description') }}</h6>
            <div class="text-muted small lh-base p-3 bg-white border rounded-3">
                {!! nl2br(e($task->description)) !!}
            </div>
        </div>
    @endif

    {{-- Assignees --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Assignees') }} ({{ $task->assignees->count() }})</h6>
        @if ($task->assignees->isEmpty())
            <p class="text-muted small mb-0">{{ _trans('common.Unassigned') }}</p>
        @else
            <div class="d-flex flex-wrap gap-2">
                @foreach ($task->assignees as $assignee)
                    <div class="d-flex align-items-center gap-2 bg-light px-2.5 py-1.5 rounded-pill border">
                        <img src="{{ $assignee->avatar_url }}" class="rounded-circle" width="24" height="24" alt="{{ $assignee->name }}">
                        <span class="small fw-semibold text-dark">{{ $assignee->name }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <hr class="my-4">

    {{-- Checklist Section --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-check2-square text-success me-1"></i>
                {{ _trans('common.Checklist') }}
                <span class="text-muted small" id="checklistCounter">
                    ({{ $task->checklist_progress['completed'] }}/{{ $task->checklist_progress['total'] }})
                </span>
            </h6>
        </div>

        {{-- Progress Bar --}}
        <div class="progress mb-3" style="height: 6px;">
            <div class="progress-bar bg-success" role="progressbar"
                style="width: {{ $task->checklist_progress['percent'] }}%;"
                aria-valuenow="{{ $task->checklist_progress['percent'] }}"
                aria-valuemin="0" aria-valuemax="100" id="checklistProgressBar"></div>
        </div>

        {{-- Items list --}}
        <div class="checklist-items-list mb-3" id="checklistItemsList">
            @foreach ($task->checklists as $item)
                <div class="d-flex align-items-center justify-content-between py-1.5 border-bottom" id="checklist-row-{{ $item->id }}">
                    <div class="form-check m-0">
                        <input class="form-check-input checklist-toggle" type="checkbox"
                            data-url="{{ route('tasks.checklists.toggle', $item) }}"
                            id="chk-{{ $item->id }}" {{ $item->is_completed ? 'checked' : '' }}>
                        <label class="form-check-label small {{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark' }}" for="chk-{{ $item->id }}">
                            {{ $item->title }}
                        </label>
                    </div>
                    @can('task.edit')
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 delete-checklist-btn"
                            data-url="{{ route('tasks.checklists.destroy', $item) }}"
                            data-row="checklist-row-{{ $item->id }}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    @endcan
                </div>
            @endforeach
        </div>

        {{-- Add checklist item input --}}
        @can('task.edit')
            <form id="addChecklistForm" data-url="{{ route('tasks.checklists.store', $task) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="title" class="form-control form-control-sm" placeholder="{{ _trans('common.Add a checklist item...') }}" required>
                <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                    <i class="bi bi-plus"></i> {{ _trans('common.Add') }}
                </button>
            </form>
        @endcan
    </div>

    <hr class="my-4">

    {{-- Attachments Section --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-3">
            <i class="bi bi-paperclip text-primary me-1"></i>
            {{ _trans('common.Attachments') }} ({{ $task->attachments->count() }})
        </h6>

        <div class="attachments-list mb-3" id="attachmentsList">
            @foreach ($task->attachments as $att)
                <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded-3 border" id="attachment-row-{{ $att->id }}">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <span class="badge bg-secondary text-uppercase fs-8">{{ $att->file_type ?? 'FILE' }}</span>
                        <div class="text-truncate">
                            <span class="fw-semibold text-dark small text-truncate d-block">{{ $att->file_name }}</span>
                            <span class="text-muted fs-8">{{ $att->file_size }} &bull; {{ $att->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('tasks.attachments.download', $att) }}" class="btn btn-sm btn-outline-secondary p-1" title="{{ _trans('common.Download') }}">
                            <i class="bi bi-download"></i>
                        </a>
                        @can('task.edit')
                            <button type="button" class="btn btn-sm btn-outline-danger p-1 delete-attachment-btn"
                                data-url="{{ route('tasks.attachments.destroy', $att) }}"
                                data-row="attachment-row-{{ $att->id }}"
                                title="{{ _trans('common.Delete') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        @can('task.edit')
            <form id="uploadAttachmentForm" data-url="{{ route('tasks.attachments.upload', $task) }}" enctype="multipart/form-data">
                @csrf
                <div class="input-group input-group-sm">
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-upload me-1"></i>{{ _trans('common.Upload') }}
                    </button>
                </div>
            </form>
        @endcan
    </div>

    <hr class="my-4">

    {{-- Comments Section --}}
    <div class="mb-3">
        <h6 class="fw-bold text-dark mb-3">
            <i class="bi bi-chat-dots text-info me-1"></i>
            {{ _trans('common.Comments & Activity') }} ({{ $task->comments->count() }})
        </h6>

        <div class="comments-stream mb-3" id="commentsStream" style="max-height: 300px; overflow-y: auto;">
            @forelse ($task->comments as $comment)
                <div class="d-flex gap-2 mb-3" id="comment-row-{{ $comment->id }}">
                    <img src="{{ $comment->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User' }}" class="rounded-circle flex-shrink-0" width="32" height="32" alt="{{ $comment->user?->name }}">
                    <div class="flex-grow-1 bg-light p-2.5 rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small">{{ $comment->user?->name ?? _trans('common.User') }}</span>
                            <span class="text-muted fs-8">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="small text-dark mb-0 lh-sm">{{ $comment->comment }}</p>
                    </div>
                    @if (Auth::id() === $comment->user_id || Auth::user()?->can('task.edit'))
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 align-self-start delete-comment-btn"
                            data-url="{{ route('tasks.comments.destroy', $comment) }}"
                            data-row="comment-row-{{ $comment->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                </div>
            @empty
                <p class="text-muted small text-center py-3 mb-0" id="noCommentsText">{{ _trans('common.No comments yet. Start the conversation!') }}</p>
            @endforelse
        </div>

        {{-- Add comment form --}}
        <form id="addCommentForm" data-url="{{ route('tasks.comments.store', $task) }}">
            @csrf
            <div class="d-flex gap-2">
                <textarea name="comment" class="form-control form-control-sm" rows="2" placeholder="{{ _trans('common.Write a comment...') }}" required></textarea>
                <button type="submit" class="btn btn-sm btn-primary align-self-end px-3">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </form>
    </div>
</div>
