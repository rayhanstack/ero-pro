<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDueSoonNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Task $task
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dueDateFormatted = $this->task->due_date ? $this->task->due_date->format('M d, Y') : 'Soon';
        $projectName = $this->task->project?->name ?? 'General';

        return [
            'task_id' => $this->task->id,
            'title' => _trans('common.Task Due Soon Reminder'),
            'message' => _trans('common.Task ":title" in project ":project" is due on :date.', [
                'title' => $this->task->title,
                'project' => $projectName,
                'date' => $dueDateFormatted,
            ]),
            'status' => $this->task->status->value,
            'action_url' => route('tasks.index', ['search' => $this->task->title]),
        ];
    }
}
