<?php

namespace App\Models;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'description',
        'priority',
        'status',
        'start_date',
        'due_date',
        'estimated_hours',
        'created_by',
        'position',
    ];

    protected $casts = [
        'priority' => TaskPriorityEnum::class,
        'status' => TaskStatusEnum::class,
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'position' => 'integer',
    ];

    /**
     * Project association.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Parent task for subtasks.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * Subtasks.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    /**
     * Creator of the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Task assignees (employees).
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees', 'task_id', 'employee_id')
            ->withTimestamps();
    }

    /**
     * Task comments.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    /**
     * Task file attachments.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    /**
     * Task checklist items.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('position');
    }

    /**
     * Computed checklist progress: completed / total count.
     */
    public function getChecklistProgressAttribute(): array
    {
        $total = $this->checklists->count();
        $completed = $this->checklists->where('is_completed', true)->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $percent,
        ];
    }

    /**
     * Check if task is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (! $this->due_date || $this->status === TaskStatusEnum::DONE) {
            return false;
        }

        return $this->due_date->isPast() && ! $this->due_date->isToday();
    }

    /**
     * Check if task is due today.
     */
    public function getIsDueTodayAttribute(): bool
    {
        if (! $this->due_date || $this->status === TaskStatusEnum::DONE) {
            return false;
        }

        return $this->due_date->isToday();
    }

    /**
     * Scope for searching tasks.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope for status filter.
     */
    public function scopeStatus(Builder $query, mixed $status): Builder
    {
        if (! $status) {
            return $query;
        }

        $val = $status instanceof TaskStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    /**
     * Scope for priority filter.
     */
    public function scopePriority(Builder $query, mixed $priority): Builder
    {
        if (! $priority) {
            return $query;
        }

        $val = $priority instanceof TaskPriorityEnum ? $priority->value : $priority;

        return $query->where('priority', $val);
    }

    /**
     * Scope for project filter.
     */
    public function scopeProject(Builder $query, ?int $projectId): Builder
    {
        if (! $projectId) {
            return $query;
        }

        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for assignee filter.
     */
    public function scopeAssignee(Builder $query, ?int $userId): Builder
    {
        if (! $userId) {
            return $query;
        }

        return $query->whereHas('assignees', function ($q) use ($userId) {
            $q->where('employee_id', $userId);
        });
    }

    /**
     * Scope for due date filter: overdue, today, this_week, upcoming.
     */
    public function scopeDueFilter(Builder $query, ?string $filter): Builder
    {
        if (! $filter) {
            return $query;
        }

        return match ($filter) {
            'overdue' => $query->where('due_date', '<', today())->where('status', '!=', TaskStatusEnum::DONE->value),
            'today' => $query->whereDate('due_date', today()),
            'this_week' => $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'upcoming' => $query->where('due_date', '>', today()),
            default => $query,
        };
    }
}
