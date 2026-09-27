<?php

namespace App\Console\Commands;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Console\Command;

class SendTaskDueSoonNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-due-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to assignees for tasks due today or tomorrow.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for tasks due soon...');

        $upcomingDate = today()->addDay();

        $tasks = Task::with(['assignees', 'project'])
            ->where('status', '!=', TaskStatusEnum::DONE->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [today(), $upcomingDate])
            ->get();

        $sentCount = 0;

        foreach ($tasks as $task) {
            foreach ($task->assignees as $assignee) {
                $assignee->notify(new TaskDueSoonNotification($task));
                $sentCount++;
            }
        }

        $this->info("Successfully sent {$sentCount} due-soon notifications for {$tasks->count()} tasks.");

        return Command::SUCCESS;
    }
}
