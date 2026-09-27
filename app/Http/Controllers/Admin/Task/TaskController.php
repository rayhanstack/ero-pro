<?php

namespace App\Http\Controllers\Admin\Task;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Http\Requests\Task\StoreTaskAttachmentRequest;
use App\Http\Requests\Task\StoreTaskChecklistRequest;
use App\Http\Requests\Task\StoreTaskCommentRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\Task\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Display Kanban board or List view of tasks.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'project_id', 'assignee_id', 'priority', 'due', 'my_tasks']);
        $viewMode = $request->input('view', 'kanban');

        $stats = $this->taskService->getStats($filters);
        $projects = Project::orderBy('name')->get();
        $employees = User::with('employeeDetail.designation')->active()->get();
        $statuses = TaskStatusEnum::cases();
        $priorities = TaskPriorityEnum::cases();

        if ($viewMode === 'list') {
            $tasks = $this->taskService->getPaginatedTasks($filters, 15);
            $boardTasks = [];
        } else {
            $tasks = null;
            $boardTasks = $this->taskService->getBoardTasks($filters);
        }

        $title = _trans('common.Task Board');

        return view('admin.task.index', compact(
            'title',
            'boardTasks',
            'tasks',
            'stats',
            'filters',
            'viewMode',
            'projects',
            'employees',
            'statuses',
            'priorities'
        ));
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): RedirectResponse|JsonResponse
    {
        $task = $this->taskService->create($request->validated(), Auth::id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Task created successfully.'),
                'data' => $task,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', _trans('common.Task created successfully.'));
    }

    /**
     * Display the specified task details (for offcanvas / modal / json).
     */
    public function show(Request $request, Task $task): View|JsonResponse
    {
        $task->load([
            'project',
            'creator',
            'assignees.employeeDetail.designation',
            'checklists',
            'comments.user',
            'attachments.user',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'data' => $task,
                'html' => view('admin.task.partials.detail_content', compact('task'))->render(),
            ]);
        }

        return view('admin.task.show', compact('task'));
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse|JsonResponse
    {
        $updated = $this->taskService->update($task, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Task updated successfully.'),
                'data' => $updated,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', _trans('common.Task updated successfully.'));
    }

    /**
     * Move task status & position via Kanban drag and drop.
     */
    public function move(MoveTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();
        $updated = $this->taskService->move(
            $task,
            $validated['status'],
            $validated['position'],
            $validated['order'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => _trans('common.Task moved successfully.'),
            'status' => $updated->status->value,
            'data' => $updated,
        ]);
    }

    /**
     * Remove the specified task (Soft Delete).
     */
    public function destroy(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        $this->taskService->delete($task);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Task deleted successfully.'),
            ]);
        }

        return redirect()->route('tasks.index')->with('success', _trans('common.Task deleted successfully.'));
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->taskService->restore($id);

        return redirect()->route('tasks.index')->with('success', _trans('common.Task restored successfully.'));
    }

    /**
     * Add a comment to task.
     */
    public function storeComment(StoreTaskCommentRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $comment = $this->taskService->addComment($task, $request->input('comment'), Auth::id());
        $comment->load('user');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Comment added.'),
                'comment' => $comment,
            ]);
        }

        return back()->with('success', _trans('common.Comment added.'));
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Request $request, TaskComment $comment): JsonResponse|RedirectResponse
    {
        $this->taskService->deleteComment($comment);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Comment deleted.'),
            ]);
        }

        return back()->with('success', _trans('common.Comment deleted.'));
    }

    /**
     * Upload an attachment to task.
     */
    public function uploadAttachment(StoreTaskAttachmentRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $attachment = $this->taskService->addAttachment($task, $request->file('file'), Auth::id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.File uploaded successfully.'),
                'attachment' => $attachment,
            ]);
        }

        return back()->with('success', _trans('common.File uploaded successfully.'));
    }

    /**
     * Download an attached file.
     */
    public function downloadAttachment(TaskAttachment $attachment): StreamedResponse
    {
        if (! Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, _trans('common.File not found.'));
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(Request $request, TaskAttachment $attachment): JsonResponse|RedirectResponse
    {
        $this->taskService->deleteAttachment($attachment);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Attachment deleted.'),
            ]);
        }

        return back()->with('success', _trans('common.Attachment deleted.'));
    }

    /**
     * Add a checklist item.
     */
    public function storeChecklist(StoreTaskChecklistRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $checklist = $this->taskService->addChecklist($task, $request->input('title'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Checklist item added.'),
                'checklist' => $checklist,
            ]);
        }

        return back()->with('success', _trans('common.Checklist item added.'));
    }

    /**
     * Toggle a checklist item.
     */
    public function toggleChecklist(Request $request, TaskChecklist $checklist): JsonResponse|RedirectResponse
    {
        $this->taskService->toggleChecklist($checklist);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $checklist->is_completed,
            ]);
        }

        return back();
    }

    /**
     * Delete a checklist item.
     */
    public function deleteChecklist(Request $request, TaskChecklist $checklist): JsonResponse|RedirectResponse
    {
        $this->taskService->deleteChecklist($checklist);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Checklist item deleted.'),
            ]);
        }

        return back()->with('success', _trans('common.Checklist item deleted.'));
    }
}
