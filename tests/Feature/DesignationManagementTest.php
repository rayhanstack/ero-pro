<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignationManagementTest extends TestCase
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
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_designations_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('designations.index'));

        $response->assertStatus(200);
        $response->assertSee('Designations');
        $response->assertSee('Software Engineer');
    }

    public function test_designations_index_filters_by_department_and_status(): void
    {
        $eng = Department::where('code', 'ENG')->first();

        $response = $this->actingAs($this->superAdmin)->get(route('designations.index', [
            'department_id' => $eng->id,
            'status' => 'active',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Software Engineer');
    }

    public function test_designation_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('designations.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Designation');
    }

    public function test_designation_can_be_created(): void
    {
        $eng = Department::where('code', 'ENG')->first();

        $response = $this->actingAs($this->superAdmin)->post(route('designations.store'), [
            'name' => 'Principal Architect',
            'department_id' => $eng->id,
            'level' => 5,
            'status' => 'active',
            'description' => 'Oversees system architecture across teams.',
        ]);

        $response->assertRedirect(route('designations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('designations', [
            'name' => 'Principal Architect',
            'department_id' => $eng->id,
            'level' => 5,
            'status' => 'active',
        ]);
    }

    public function test_designation_edit_page_can_be_rendered(): void
    {
        $desig = Designation::first();

        $response = $this->actingAs($this->superAdmin)->get(route('designations.edit', $desig));

        $response->assertStatus(200);
        $response->assertSee($desig->name);
    }

    public function test_designation_can_be_updated(): void
    {
        $desig = Designation::where('name', 'Software Engineer')->first();

        $response = $this->actingAs($this->superAdmin)->put(route('designations.update', $desig), [
            'name' => 'Full-Stack Software Engineer',
            'department_id' => $desig->department_id,
            'level' => 2,
            'status' => 'active',
            'description' => 'Updated job description.',
        ]);

        $response->assertRedirect(route('designations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('designations', [
            'id' => $desig->id,
            'name' => 'Full-Stack Software Engineer',
        ]);
    }

    public function test_designation_can_be_soft_deleted(): void
    {
        $desig = Designation::first();

        $response = $this->actingAs($this->superAdmin)->delete(route('designations.destroy', $desig));

        $response->assertRedirect(route('designations.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('designations', ['id' => $desig->id]);
    }

    public function test_unauthorized_user_cannot_access_designations(): void
    {
        $response = $this->actingAs($this->employee)->get(route('designations.index'));
        $response->assertStatus(403);
    }
}
