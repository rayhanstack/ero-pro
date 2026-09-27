<?php

namespace App\Services\Task;

use App\Enums\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Services\Project\ProjectService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Get tasks grouped by status for Kanban Board.
     */
    public function getBoardTasks(array $filters = []): array
    {
        $query = $this->buildFilteredQuery($filters);

        $tasks = $query->orderBy('position')->get();

        $grouped = [
            TaskStatusEnum::TODO->value => new Collection(),
            TaskStatusEnum::IN_PROGRESS->value => new Collection(),
            TaskStatusEnum::REVIEW->value => new Collection(),
            TaskStatusEnum::DONE->value => new Collection(),
        ];

        foreach ($tasks as $task) {
            $statusKey = $task->status instanceof TaskStatusEnum ? $task->status->value : $task->status;
            if (isset($grouped[$statusKey])) {
                $grouped[$statusKey]->push($task);
            }
        }

        return $grouped;
    }

    /**
     * Get paginated tasks for List view.
     */
    public function getPaginatedTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters);

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * Get task statistics.
     */
    public function getStats(array $filters = []): array
    {
        $baseQuery = Task::query();

        if (! empty($filters['project_id'])) {
            $baseQuery->project($filters['project_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $baseQuery->assignee($filters['assignee_id']);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'todo' => (clone $baseQuery)->where('status', TaskStatusEnum::TODO->value)->count(),
            'in_progress' => (clone $baseQuery)->where('status', TaskStatusEnum::IN_PROGRESS->value)->count(),
            'review' => (clone $baseQuery)->where('status', TaskStatusEnum::REVIEW->value)->count(),
            'done' => (clone $baseQuery)->where('status', TaskStatusEnum::DONE->value)->count(),
            'overdue' => (clone $baseQuery)->where('due_date', '<', today())->where('status', '!=', TaskStatusEnum::DONE->value)->count(),
        ];
    }

    /**
     * Create a new task.
     */
    public function create(array $data, ?int $createdBy = null): Task
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $assigneeIds = $data['assignees'] ?? $data['assignee_ids'] ?? [];
            unset($data['assignees'], $data['assignee_ids']);

            $data['created_by'] = $createdBy;

            // Default position to bottom of column if not specified
            if (! isset($data['position'])) {
                $statusVal = $data['status'] instanceof TaskStatusEnum ? $data['status']->value : $data['status'];
                $maxPos = Task::where('status', $statusVal)
                    ->when(! empty($data['project_id']), fn ($q) => $q->where('project_id', $data['project_id']))
                    ->max('position');
                $data['position'] = ($maxPos !== null) ? $maxPos + 1 : 0;
            }

            $task = Task::create($data);

            if (! empty($assigneeIds)) {
                $task->assignees()->sync($assigneeIds);
            }

            // Recalculate project progress
            if ($task->project_id) {
                $this->projectService->recalculateProgress($task->project);
            }

            return $task->fresh(['project', 'creator', 'assignees', 'checklists', 'comments', 'attachments']);
        });
    }

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $hasAssignees = array_key_exists('assignees', $data) || array_key_exists('assignee_ids', $data);
            $assigneeIds = $data['assignees'] ?? $data['assignee_ids'] ?? [];
            unset($data['assignees'], $data['assignee_ids']);

            $oldStatus = $task->status;
            $oldProjectId = $task->project_id;

            $task->update($data);

            if ($hasAssignees) {
                $task->assignees()->sync($assigneeIds);
            }

            // Recalculate project progress if status or project changed
            if ($task->project_id) {
                $this->projectService->recalculateProgress($task->project);
            }

            if ($oldProjectId && $oldProjectId !== $task->project_id) {
                $oldProject = Project::find($oldProjectId);
                if ($oldProject) {
                    $this->projectService->recalculateProgress($oldProject);
                }
            }

            return $task->fresh(['project', 'creator', 'assignees', 'checklists', 'comments', 'attachments']);
        });
    }

    /**
     * Move task status and position (Kanban Drag & Drop).
     */
    public function move(Task $task, string|TaskStatusEnum $newStatus, int $newPosition, ?array $columnOrder = null): Task
    {
        return DB::transaction(function () use ($task, $newStatus, $newPosition, $columnOrder) {
            $statusVal = $newStatus instanceof TaskStatusEnum ? $newStatus->value : $newStatus;

            $task->update([
                'status' => $statusVal,
                'position' => $newPosition,
            ]);

            // Re-sequence ordered column if array provided
            if (! empty($columnOrder)) {
                foreach ($columnOrder as $pos => $taskId) {
                    Task::where('id', $taskId)->update([
                        'status' => $statusVal,
                        'position' => $pos,
                    ]);
                }
            }

            // Recalculate project progress
            if ($task->project_id) {
                $this->projectService->recalculateProgress($task->project);
            }

            return $task->fresh(['project', 'creator', 'assignees', 'checklists', 'comments', 'attachments']);
        });
    }

    /**
     * Delete a task (Soft Delete).
     */
    public function delete(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            $project = $task->project;
            $res = (bool) $task->delete();

            if ($project) {
                $this->projectService->recalculateProgress($project);
            }

            return $res;
        });
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $task = Task::onlyTrashed()->findOrFail($id);
            $res = (bool) $task->restore();

            if ($task->project_id) {
                $this->projectService->recalculateProgress($task->project);
            }

            return $res;
        });
    }

    /**
     * Add comment to task.
     */
    public function addComment(Task $task, string $commentText, int $userId): TaskComment
    {
        return $task->comments()->create([
            'user_id' => $userId,
            'comment' => $commentText,
        ]);
    }

    /**
     * Delete comment from task.
     */
    public function deleteComment(TaskComment $comment): bool
    {
        return (bool) $comment->delete();
    }

    /**
     * Upload and attach a file to task.
     */
    public function addAttachment(Task $task, UploadedFile $file, ?int $userId = null): TaskAttachment
    {
        $originalName = $file->getClientOriginalName();
        $fileSize = $this->formatBytes($file->getSize());
        $fileType = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'file'));
        $path = $file->store("tasks/{$task->id}", 'public');

        return $task->attachments()->create([
            'user_id' => $userId,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'file_type' => $fileType,
        ]);
    }

    /**
     * Delete attachment from task.
     */
    public function deleteAttachment(TaskAttachment $attachment): bool
    {
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        return (bool) $attachment->delete();
    }

    /**
     * Add a checklist item.
     */
    public function addChecklist(Task $task, string $title): TaskChecklist
    {
        $maxPos = $task->checklists()->max('position') ?? 0;

        return $task->checklists()->create([
            'title' => $title,
            'is_completed' => false,
            'position' => $maxPos + 1,
        ]);
    }

    /**
     * Toggle checklist item completion.
     */
    public function toggleChecklist(TaskChecklist $checklist): TaskChecklist
    {
        $checklist->update(['is_completed' => ! $checklist->is_completed]);

        return $checklist;
    }

    /**
     * Delete a checklist item.
     */
    public function deleteChecklist(TaskChecklist $checklist): bool
    {
        return (bool) $checklist->delete();
    }

    /**
     * Helper to build filtered query with eager loading.
     */
    protected function buildFilteredQuery(array $filters = [])
    {
        $query = Task::with([
            'project',
            'creator',
            'assignees.employeeDetail.designation',
            'checklists',
            'comments',
            'attachments',
        ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['project_id'])) {
            $query->project($filters['project_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->assignee($filters['assignee_id']);
        }

        if (! empty($filters['priority'])) {
            $query->priority($filters['priority']);
        }

        if (! empty($filters['due'])) {
            $query->dueFilter($filters['due']);
        }

        if (! empty($filters['my_tasks']) && auth()->check()) {
            $query->assignee(auth()->id());
        }

        return $query;
    }

    /**
     * Format bytes.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
