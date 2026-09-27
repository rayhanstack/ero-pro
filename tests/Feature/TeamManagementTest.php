<?php

namespace Tests\Feature;

use App\Enums\TeamStatusEnum;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ShiftSeeder::class,
            EmployeeSeeder::class,
            TeamSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_teams_index_page_can_be_rendered_in_grid_and_list_modes(): void
    {
        // Grid mode
        $response = $this->actingAs($this->superAdmin)->get(route('teams.index', ['view' => 'grid']));
        $response->assertStatus(200);
        $response->assertSee('Teams');
        $response->assertSee('Frontend Engineering Squad');
        $response->assertSee('Total Teams');
        $response->assertSee('Active Teams');

        // List mode
        $listResponse = $this->actingAs($this->superAdmin)->get(route('teams.index', ['view' => 'list']));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Frontend Engineering Squad');
    }

    public function test_teams_index_can_be_filtered_by_search_status_and_lead(): void
    {
        $lead = User::where('email', 'tariqul.lead@erp.test')->first();

        $response = $this->actingAs($this->superAdmin)->get(route('teams.index', [
            'search' => 'Frontend',
            'status' => 'active',
            'lead_id' => $lead?->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Frontend Engineering Squad');
    }

    public function test_team_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('teams.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Team');
        $response->assertSee('Team Details');
    }

    public function test_team_can_be_created_with_lead_and_members(): void
    {
        $lead = User::first();
        $members = User::take(3)->pluck('id')->toArray();

        $response = $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'name' => 'AI Research & Innovation Squad',
            'lead_id' => $lead->id,
            'description' => 'Dedicated unit for generative AI and agent workflows.',
            'status' => 'active',
            'member_ids' => $members,
        ]);

        $response->assertRedirect(route('teams.index'));
        $response->assertSessionHas('success');

        $team = Team::where('name', 'AI Research & Innovation Squad')->first();
        $this->assertNotNull($team);
        $this->assertEquals($lead->id, $team->lead_id);
        $this->assertEquals(TeamStatusEnum::ACTIVE, $team->status);
        $this->assertCount(3, $team->members);
    }

    public function test_team_show_page_renders_members_and_projects_tabs(): void
    {
        $team = Team::first();

        $response = $this->actingAs($this->superAdmin)->get(route('teams.show', $team));

        $response->assertStatus(200);
        $response->assertSee($team->name);
        $response->assertSee('Team Members');
        $response->assertSee('Projects & Squad Tasks');
        $response->assertSee('Team Details');
    }

    public function test_team_edit_page_can_be_rendered(): void
    {
        $team = Team::first();

        $response = $this->actingAs($this->superAdmin)->get(route('teams.edit', $team));

        $response->assertStatus(200);
        $response->assertSee('Edit Team');
        $response->assertSee($team->name);
    }

    public function test_team_can_be_updated(): void
    {
        $team = Team::first();
        $users = User::take(2)->pluck('id')->toArray();

        $response = $this->actingAs($this->superAdmin)->put(route('teams.update', $team), [
            'name' => 'Updated Team Name Squad',
            'lead_id' => $team->lead_id,
            'description' => 'Updated team mission statement.',
            'status' => 'inactive',
            'member_ids' => $users,
        ]);

        $response->assertRedirect(route('teams.show', $team));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Team Name Squad',
            'status' => 'inactive',
        ]);
        $this->assertCount(2, $team->fresh()->members);
    }

    public function test_team_can_be_soft_deleted_and_restored(): void
    {
        $team = Team::create([
            'name' => 'Deletable Team',
            'status' => TeamStatusEnum::ACTIVE,
        ]);

        // Soft delete
        $response = $this->actingAs($this->superAdmin)->delete(route('teams.destroy', $team));
        $response->assertRedirect(route('teams.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('teams', ['id' => $team->id]);

        // Restore
        $restoreResponse = $this->actingAs($this->superAdmin)->post(route('teams.restore', $team->id));
        $restoreResponse->assertRedirect(route('teams.index'));
        $restoreResponse->assertSessionHas('success');

        $this->assertNotSoftDeleted('teams', ['id' => $team->id]);
    }

    public function test_members_can_be_added_to_team(): void
    {
        $team = Team::create([
            'name' => 'Growth Squad',
            'status' => TeamStatusEnum::ACTIVE,
        ]);

        $users = User::take(2)->pluck('id')->toArray();

        $response = $this->actingAs($this->superAdmin)->post(route('teams.members.store', $team), [
            'member_ids' => $users,
        ]);

        $response->assertSessionHas('success');
        $this->assertCount(2, $team->fresh()->members);
    }

    public function test_member_can_be_removed_from_team(): void
    {
        $team = Team::first();
        $member = $team->members()->first();

        $this->assertNotNull($member);

        $response = $this->actingAs($this->superAdmin)->delete(route('teams.members.destroy', [$team, $member]));
        $response->assertSessionHas('success');

        $this->assertFalse($team->fresh()->members->contains($member->id));
    }

    public function test_validation_errors_when_creating_team(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'name' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_sidebar_link_active_state_for_teams(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('teams.index'));

        $response->assertStatus(200);
        $response->assertSee('sidebar-nav__item active', false);
    }

    public function test_unauthorized_user_cannot_access_team_module(): void
    {
        $response = $this->actingAs($this->employee)->get(route('teams.index'));
        $response->assertStatus(403);
    }
}
