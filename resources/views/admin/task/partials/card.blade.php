<div class="card task-card shadow-sm mb-3 {{ !empty($isDone) ? 'bg-white opacity-75' : '' }}" data-id="{{ $task->id }}">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge {{ $task->priority->badgeClass() }} rounded-pill">{{ $task->priority->label() }}</span>
            <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event" style="cursor: grab;" title="{{ _trans('common.Drag to reorder or move') }}"></i>
        </div>
        <h6 class="fw-bold mb-1 {{ !empty($isDone) ? 'text-decoration-line-through' : '' }}" onclick="openTaskDetail({{ $task->id }})" style="cursor: pointer;">
            {{ $task->title }}
        </h6>
        @if ($task->project)
            <p class="text-primary small mb-2 fw-bold {{ !empty($isDone) ? 'text-decoration-line-through' : '' }}">
                {{ $task->project->name }}
            </p>
        @endif
        @if ($task->description)
            <p class="text-muted small mb-3 text-truncate {{ !empty($isDone) ? 'text-decoration-line-through' : '' }}">
                {{ $task->description }}
            </p>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small {{ $task->is_overdue ? 'text-danger fw-bold' : ($task->is_due_today ? 'text-warning fw-bold' : 'text-muted') }}">
                <i class="bi bi-calendar3 me-1"></i>{{ $task->due_date ? $task->due_date->format('M d') : _trans('common.No due') }}
            </span>
            <span class="small text-muted">
                <i class="bi bi-check2-square text-success me-1"></i>{{ $task->checklist_progress['completed'] }}/{{ $task->checklist_progress['total'] }}
            </span>
        </div>

        <div class="d-flex justify-content-between align-items-center border-top pt-2">
            <div class="d-flex align-items-center">
                @forelse ($task->assignees->take(2) as $assignee)
                    <img src="{{ $assignee->avatar_url }}" class="rounded-circle {{ !empty($isDone) ? 'opacity-50' : '' }} {{ !$loop->first ? 'ms-n2' : '' }}"
                        width="28" height="28" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                @empty
                    <div class="rounded-circle border border-dashed text-muted d-flex align-items-center justify-content-center"
                        style="width: 28px; height: 28px;"><i class="bi bi-person"></i></div>
                @endforelse
                @if ($task->assignees->count() > 2)
                    <span class="badge rounded-circle bg-secondary ms-n1 fs-8 text-white" style="width: 22px; height: 22px; line-height: 14px;">+{{ $task->assignees->count() - 2 }}</span>
                @endif
            </div>
            <div class="text-muted small">
                <i class="bi bi-chat-dots me-1"></i> {{ $task->comments->count() }}
                <i class="bi bi-paperclip ms-2 me-1"></i> {{ $task->attachments->count() }}
            </div>
        </div>
    </div>
</div>
