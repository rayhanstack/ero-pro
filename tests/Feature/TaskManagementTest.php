<?php

namespace Tests\Feature;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use App\Services\Project\ProjectService;
use App\Services\Task\TaskService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected User $otherEmployee;
    protected Project $project;
    protected Client $client;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');

        $this->otherEmployee = User::factory()->create();
        $this->otherEmployee->assignRole('Employee');

        $this->currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => 1.000000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->client = Client::create([
            'code' => 'CLT-0001',
            'company_name' => 'Acme Technologies Ltd',
            'contact_name' => 'Alice Walker',
            'email' => 'contact@acme.test',
            'phone' => '+1 555-0199',
            'status' => 'active',
            'currency_id' => $this->currency->id,
        ]);

        $this->project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'ERP Core Suite',
            'client_id' => $this->client->id,
            'description' => 'ERP development project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-12-31',
            'budget' => 50000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::HIGH,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 0,
            'manager_id' => $this->admin->id,
        ]);
    }

    public function test_authorized_user_can_view_kanban_board(): void
    {
        Task::create([
            'project_id' => $this->project->id,
            'title' => 'Todo Task Item',
            'priority' => TaskPriorityEnum::HIGH,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Todo Task Item');
        $response->assertSee('Kanban');
        $response->assertSee('To Do');
        $response->assertSee('In Progress');
        $response->assertSee('In Review');
        $response->assertSee('Done');
    }

    public function test_authorized_user_can_view_tasks_in_list_mode(): void
    {
        Task::create([
            'project_id' => $this->project->id,
            'title' => 'List View Task',
            'priority' => TaskPriorityEnum::MEDIUM,
            'status' => TaskStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('tasks.index', ['view' => 'list']));

        $response->assertStatus(200);
        $response->assertSee('List View Task');
        $response->assertSee('ERP Core Suite');
    }

    public function test_can_filter_tasks_by_project_and_priority_and_my_tasks(): void
    {
        $task1 = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Assigned to Employee High',
            'priority' => TaskPriorityEnum::HIGH,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);
        $task1->assignees()->attach($this->employee->id);

        $task2 = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Assigned to Other Low',
            'priority' => TaskPriorityEnum::LOW,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 2,
        ]);
        $task2->assignees()->attach($this->otherEmployee->id);

        // Filter by my_tasks as employee
        $this->employee->givePermissionTo('task.view');
        $res = $this->actingAs($this->employee)->get(route('tasks.index', ['my_tasks' => 1]));
        $res->assertStatus(200);
        $res->assertSee('Assigned to Employee High');
        $res->assertDontSee('Assigned to Other Low');

        // Filter by priority
        $resPriority = $this->actingAs($this->admin)->get(route('tasks.index', ['priority' => 'low']));
        $resPriority->assertStatus(200);
        $resPriority->assertSee('Assigned to Other Low');
        $resPriority->assertDontSee('Assigned to Employee High');
    }

    public function test_authorized_user_can_create_task_with_assignees(): void
    {
        $data = [
            'project_id' => $this->project->id,
            'title' => 'New Feature Development',
            'description' => 'Detailed implementation steps.',
            'priority' => TaskPriorityEnum::HIGH->value,
            'status' => TaskStatusEnum::TODO->value,
            'start_date' => '2026-03-01',
            'due_date' => '2026-03-10',
            'estimated_hours' => 24.5,
            'assignee_ids' => [$this->employee->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('tasks.store'), $data);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Feature Development',
            'priority' => 'high',
            'status' => 'todo',
            'estimated_hours' => 24.5,
        ]);

        $task = Task::where('title', 'New Feature Development')->first();
        $this->assertCount(1, $task->assignees);
        $this->assertTrue($task->assignees->contains($this->employee->id));
    }

    public function test_drag_and_drop_status_move_via_ajax_updates_status_position_and_recalculates_project_progress(): void
    {
        $task1 = Task::create([
            'project_id' => $this->project->id,
            'title' => 'First Task',
            'priority' => TaskPriorityEnum::HIGH,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        $task2 = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Second Task',
            'priority' => TaskPriorityEnum::MEDIUM,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 2,
        ]);

        // Initial progress with 2 todo tasks should be 0%
        $this->project->refresh();
        $this->assertEquals(0, $this->project->progress);

        // Move task1 to DONE via AJAX
        $response = $this->actingAs($this->admin)->patchJson(route('tasks.move', $task1), [
            'status' => TaskStatusEnum::DONE->value,
            'position' => 1,
            'order' => [$task1->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'done',
        ]);

        $task1->refresh();
        $this->assertEquals(TaskStatusEnum::DONE, $task1->status);

        // Recalculated project progress should now be 50% (1 out of 2 completed)
        $this->project->refresh();
        $this->assertEquals(50, $this->project->progress);

        // Move task2 to DONE as well
        $this->actingAs($this->admin)->patchJson(route('tasks.move', $task2), [
            'status' => TaskStatusEnum::DONE->value,
            'position' => 2,
            'order' => [$task1->id, $task2->id],
        ]);

        // Recalculated project progress should now be 100%
        $this->project->refresh();
        $this->assertEquals(100, $this->project->progress);
    }

    public function test_task_detail_modal_offcanvas_endpoint_returns_json_and_html(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Detail Panel Task',
            'description' => 'Check the description in detail offcanvas.',
            'priority' => TaskPriorityEnum::URGENT,
            'status' => TaskStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('tasks.show', $task));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'task',
            'html',
        ]);
        $response->assertSee('Detail Panel Task');
    }

    public function test_can_add_toggle_and_delete_checklist_items(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Checklist Container Task',
            'priority' => TaskPriorityEnum::MEDIUM,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        // 1. Add checklist
        $res = $this->actingAs($this->admin)->postJson(route('tasks.checklists.store', $task), [
            'title' => 'Complete step 1',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $checklist = TaskChecklist::where('task_id', $task->id)->first();
        $this->assertNotNull($checklist);
        $this->assertEquals('Complete step 1', $checklist->title);
        $this->assertFalse($checklist->is_completed);

        // 2. Toggle checklist
        $resToggle = $this->actingAs($this->admin)->patchJson(route('tasks.checklists.toggle', [$task, $checklist]));
        $resToggle->assertStatus(200);
        $resToggle->assertJson(['is_completed' => true]);

        $checklist->refresh();
        $this->assertTrue($checklist->is_completed);

        // 3. Delete checklist
        $resDel = $this->actingAs($this->admin)->deleteJson(route('tasks.checklists.destroy', [$task, $checklist]));
        $resDel->assertStatus(200);
        $this->assertDatabaseMissing('task_checklists', ['id' => $checklist->id]);
    }

    public function test_can_add_and_delete_task_comments(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Comment Container Task',
            'priority' => TaskPriorityEnum::LOW,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        // Add comment
        $res = $this->actingAs($this->admin)->postJson(route('tasks.comments.store', $task), [
            'comment' => 'This is a test feedback comment.',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $comment = TaskComment::where('task_id', $task->id)->first();
        $this->assertNotNull($comment);
        $this->assertEquals('This is a test feedback comment.', $comment->comment);

        // Delete comment
        $resDel = $this->actingAs($this->admin)->deleteJson(route('tasks.comments.destroy', [$task, $comment]));
        $resDel->assertStatus(200);
        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }

    public function test_can_upload_download_and_delete_attachments(): void
    {
        Storage::fake('public');

        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Attachment Container Task',
            'priority' => TaskPriorityEnum::MEDIUM,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        $file = UploadedFile::fake()->create('spec.pdf', 500, 'application/pdf');

        // Upload
        $response = $this->actingAs($this->admin)->post(route('tasks.attachments.store', $task), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $attachment = TaskAttachment::where('task_id', $task->id)->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('spec.pdf', $attachment->file_name);
        Storage::disk('public')->assertExists($attachment->file_path);

        // Download
        $dlResponse = $this->actingAs($this->admin)->get(route('tasks.attachments.download', [$task, $attachment]));
        $dlResponse->assertStatus(200);

        // Delete
        $delResponse = $this->actingAs($this->admin)->delete(route('tasks.attachments.destroy', [$task, $attachment]));
        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
    }

    public function test_can_update_and_delete_task(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Old Title',
            'priority' => TaskPriorityEnum::LOW,
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);

        // Update
        $response = $this->actingAs($this->admin)->put(route('tasks.update', $task), [
            'project_id' => $this->project->id,
            'title' => 'Updated Title',
            'priority' => TaskPriorityEnum::URGENT->value,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $task->refresh();
        $this->assertEquals('Updated Title', $task->title);
        $this->assertEquals(TaskPriorityEnum::URGENT, $task->priority);
        $this->assertEquals(TaskStatusEnum::IN_PROGRESS, $task->status);

        // Delete
        $delRes = $this->actingAs($this->admin)->delete(route('tasks.destroy', $task));
        $delRes->assertRedirect(route('tasks.index'));
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_scheduled_due_soon_command_sends_notifications(): void
    {
        Notification::fake();

        $task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Due Soon Task',
            'priority' => TaskPriorityEnum::HIGH,
            'status' => TaskStatusEnum::IN_PROGRESS,
            'due_date' => now()->addDay()->format('Y-m-d'),
            'created_by' => $this->admin->id,
            'position' => 1,
        ]);
        $task->assignees()->attach($this->employee->id);

        $this->artisan('tasks:send-due-notifications')
            ->assertExitCode(0);

        Notification::assertSentTo($this->employee, TaskDueSoonNotification::class, function ($notification) use ($task) {
            return $notification->task->id === $task->id;
        });
    }

    public function test_unauthorized_user_cannot_access_tasks(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)->get(route('tasks.index'));
        $response->assertStatus(403);
    }
}
