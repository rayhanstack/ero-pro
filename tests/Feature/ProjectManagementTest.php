<?php

namespace Tests\Feature;

use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Team;
use App\Models\User;
use App\Services\Project\ProjectService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
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
    }

    public function test_project_index_displays_projects_and_stats(): void
    {
        $project1 = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Alpha Cloud Replatforming',
            'client_id' => $this->client->id,
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'budget' => 50000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::HIGH,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 50,
            'manager_id' => $this->admin->id,
        ]);

        $project2 = Project::create([
            'code' => 'PRJ-0002',
            'name' => 'Beta Mobile Application',
            'client_id' => $this->client->id,
            'start_date' => '2026-02-01',
            'deadline' => '2026-08-31',
            'budget' => 30000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::COMPLETED,
            'progress' => 100,
            'manager_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Alpha Cloud Replatforming');
        $response->assertSee('Beta Mobile Application');
        $response->assertSee('PRJ-0001');
        $response->assertSee('PRJ-0002');
    }

    public function test_project_index_filters_by_status_and_priority(): void
    {
        Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Active High Priority Project',
            'client_id' => $this->client->id,
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'budget' => 50000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::HIGH,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 50,
        ]);

        Project::create([
            'code' => 'PRJ-0002',
            'name' => 'Planning Low Priority Project',
            'client_id' => $this->client->id,
            'start_date' => '2026-02-01',
            'deadline' => '2026-08-31',
            'budget' => 20000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::LOW,
            'status' => ProjectStatusEnum::PLANNING,
            'progress' => 0,
        ]);

        // Filter by status=active
        $responseActive = $this->actingAs($this->admin)->get(route('projects.index', ['status' => 'active']));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Active High Priority Project');
        $responseActive->assertDontSee('Planning Low Priority Project');

        // Filter by priority=low
        $responseLow = $this->actingAs($this->admin)->get(route('projects.index', ['priority' => 'low']));
        $responseLow->assertStatus(200);
        $responseLow->assertSee('Planning Low Priority Project');
        $responseLow->assertDontSee('Active High Priority Project');
    }

    public function test_project_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('projects.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Project');
        $response->assertSee($this->client->company_name);
    }

    public function test_store_project_creates_project_with_auto_code_and_members(): void
    {
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();

        $payload = [
            'name' => 'E-Commerce Microservices Overhaul',
            'client_id' => $this->client->id,
            'description' => 'Migrating monolith to containerized services.',
            'start_date' => '2026-03-01',
            'deadline' => '2026-09-30',
            'budget' => 75000.00,
            'currency_id' => $this->currency->id,
            'priority' => 'high',
            'status' => 'active',
            'manager_id' => $this->admin->id,
            'members' => [$member1->id, $member2->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'name' => 'E-Commerce Microservices Overhaul',
            'code' => 'PRJ-0001',
            'client_id' => $this->client->id,
            'budget' => 75000.00,
            'priority' => 'high',
            'status' => 'active',
            'manager_id' => $this->admin->id,
        ]);

        $project = Project::where('name', 'E-Commerce Microservices Overhaul')->first();
        $this->assertNotNull($project);
        $this->assertCount(2, $project->members);
    }

    public function test_store_project_assigns_teams_and_syncs_members(): void
    {
        $teamLead = User::factory()->create();
        $teammate1 = User::factory()->create();
        $teammate2 = User::factory()->create();

        $team = Team::create([
            'name' => 'DevOps Squad',
            'lead_id' => $teamLead->id,
            'status' => 'active',
        ]);
        $team->members()->attach([$teammate1->id, $teammate2->id]);

        $payload = [
            'name' => 'Cloud Cluster Provisioning',
            'start_date' => '2026-04-01',
            'deadline' => '2026-10-31',
            'priority' => 'urgent',
            'status' => 'planning',
            'teams' => [$team->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $response->assertRedirect(route('projects.index'));

        $project = Project::where('name', 'Cloud Cluster Provisioning')->first();
        $this->assertNotNull($project);

        // Should include teamLead + teammate1 + teammate2
        $this->assertTrue($project->members->contains($teamLead));
        $this->assertTrue($project->members->contains($teammate1));
        $this->assertTrue($project->members->contains($teammate2));
    }

    public function test_project_show_page_displays_all_tabs_and_data(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Customer Portal NextGen',
            'client_id' => $this->client->id,
            'description' => 'Detailed customer self-service portal.',
            'start_date' => '2026-01-15',
            'deadline' => '2026-07-31',
            'budget' => 64000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::HIGH,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 45,
            'manager_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('Customer Portal NextGen');
        $response->assertSee('PRJ-0001');
        $response->assertSee('Overview');
        $response->assertSee('Tasks');
        $response->assertSee('Milestones');
        $response->assertSee('Files');
        $response->assertSee('Members');
        $response->assertSee('Activity');
    }

    public function test_project_update_modifies_details(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Initial Project Title',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'budget' => 20000.00,
            'priority' => ProjectPriorityEnum::LOW,
            'status' => ProjectStatusEnum::PLANNING,
        ]);

        $payload = [
            'name' => 'Updated Project Title',
            'client_id' => $this->client->id,
            'start_date' => '2026-01-01',
            'deadline' => '2026-09-30',
            'budget' => 45000.00,
            'priority' => 'urgent',
            'status' => 'active',
            'progress' => 30,
        ];

        $response = $this->actingAs($this->admin)->put(route('projects.update', $project), $payload);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Title',
            'budget' => 45000.00,
            'priority' => 'urgent',
            'status' => 'active',
            'progress' => 30,
        ]);
    }

    public function test_project_soft_delete_and_restore(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Project to Delete',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::LOW,
            'status' => ProjectStatusEnum::PLANNING,
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('projects.destroy', $project));
        $deleteResponse->assertRedirect(route('projects.index'));

        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        $restoreResponse = $this->actingAs($this->admin)->post(route('projects.restore', $project->id));
        $restoreResponse->assertRedirect(route('projects.index'));

        $this->assertNotSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_milestone_add_toggle_and_delete(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Milestone Test Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::ACTIVE,
        ]);

        // Add milestone
        $addResponse = $this->actingAs($this->admin)->post(route('projects.milestones.store', $project), [
            'title' => 'Phase 1: DB Schema & Wireframes',
            'due_date' => '2026-03-15',
            'cost' => 12500.00,
            'status' => 'incomplete',
            'description' => 'Complete ER diagrams and basic endpoints.',
        ]);

        $addResponse->assertRedirect();
        $this->assertDatabaseHas('project_milestones', [
            'project_id' => $project->id,
            'title' => 'Phase 1: DB Schema & Wireframes',
            'cost' => 12500.00,
            'status' => 'incomplete',
        ]);

        $milestone = ProjectMilestone::where('project_id', $project->id)->first();

        // Toggle milestone status
        $toggleResponse = $this->actingAs($this->admin)->patch(route('projects.milestones.toggle', [$project, $milestone]));
        $toggleResponse->assertRedirect();

        $this->assertDatabaseHas('project_milestones', [
            'id' => $milestone->id,
            'status' => 'complete',
        ]);

        // Delete milestone
        $deleteResponse = $this->actingAs($this->admin)->delete(route('projects.milestones.destroy', [$project, $milestone]));
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('project_milestones', [
            'id' => $milestone->id,
        ]);
    }

    public function test_project_file_upload_and_delete(): void
    {
        Storage::fake('public');

        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'File Upload Test Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::ACTIVE,
        ]);

        $file = UploadedFile::fake()->create('project_specifications.pdf', 500, 'application/pdf');

        $uploadResponse = $this->actingAs($this->admin)->post(route('projects.files.upload', $project), [
            'file' => $file,
        ]);

        $uploadResponse->assertRedirect();

        $this->assertDatabaseHas('project_files', [
            'project_id' => $project->id,
            'file_name' => 'project_specifications.pdf',
            'file_type' => 'pdf',
        ]);

        $projectFile = ProjectFile::where('project_id', $project->id)->first();
        Storage::disk('public')->assertExists($projectFile->file_path);

        // Delete file
        $deleteResponse = $this->actingAs($this->admin)->delete(route('projects.files.destroy', [$project, $projectFile]));
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('project_files', [
            'id' => $projectFile->id,
        ]);
        Storage::disk('public')->assertMissing($projectFile->file_path);
    }

    public function test_add_and_remove_project_members(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Team Collaboration Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::ACTIVE,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Add members
        $addResponse = $this->actingAs($this->admin)->post(route('projects.members.store', $project), [
            'members' => [$user1->id, $user2->id],
            'role' => 'Senior Backend Developer',
        ]);

        $addResponse->assertRedirect();
        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'employee_id' => $user1->id,
            'role' => 'Senior Backend Developer',
        ]);
        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'employee_id' => $user2->id,
        ]);

        // Remove member
        $removeResponse = $this->actingAs($this->admin)->delete(route('projects.members.destroy', [$project, $user1->id]));
        $removeResponse->assertRedirect();

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'employee_id' => $user1->id,
        ]);
    }

    public function test_assign_team_to_project(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Team Integration Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::ACTIVE,
        ]);

        $lead = User::factory()->create();
        $member = User::factory()->create();

        $team = Team::create([
            'name' => 'Security Team',
            'lead_id' => $lead->id,
            'status' => 'active',
        ]);
        $team->members()->attach($member->id);

        $assignResponse = $this->actingAs($this->admin)->post(route('projects.assign-team', $project), [
            'team_id' => $team->id,
        ]);

        $assignResponse->assertRedirect();
        $this->assertTrue($project->fresh()->members->contains($lead));
        $this->assertTrue($project->fresh()->members->contains($member));
    }

    public function test_recalculate_progress_from_milestones(): void
    {
        $project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'Progress Calculation Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
            'priority' => ProjectPriorityEnum::MEDIUM,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 0,
        ]);

        ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => 'Milestone 1',
            'status' => MilestoneStatusEnum::COMPLETE,
        ]);

        ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => 'Milestone 2',
            'status' => MilestoneStatusEnum::INCOMPLETE,
        ]);

        $service = app(ProjectService::class);
        $progress = $service->recalculateProgress($project);

        $this->assertEquals(50, $progress);
        $this->assertEquals(50, $project->fresh()->progress);
    }

    public function test_sidebar_link_active_state_for_projects(): void
    {
        $response = $this->actingAs($this->admin)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee('sidebar-nav__item active', false);
    }

    public function test_validation_errors_when_creating_project(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'name' => '',
            'priority' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'priority', 'status']);
    }

    public function test_unauthorized_user_cannot_create_project(): void
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)->get(route('projects.create'));
        $response->assertStatus(403);

        $storeResponse = $this->actingAs($userWithoutPermission)->post(route('projects.store'), [
            'name' => 'Unauthorized Project',
            'start_date' => '2026-01-01',
            'deadline' => '2026-06-30',
        ]);
        $storeResponse->assertStatus(403);
    }
}
