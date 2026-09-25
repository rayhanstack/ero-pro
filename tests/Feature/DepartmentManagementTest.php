<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
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
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_departments_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('departments.index'));

        $response->assertStatus(200);
        $response->assertSee('Departments');
        $response->assertSee('Software Engineering');
    }

    public function test_departments_index_filters_by_search_and_status(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('departments.index', [
            'search' => 'Software',
            'status' => 'active',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Software Engineering');
    }

    public function test_department_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('departments.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Department');
    }

    public function test_department_can_be_created(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('departments.store'), [
            'name' => 'Legal & Compliance',
            'code' => 'LEG',
            'status' => 'active',
            'description' => 'Legal and compliance oversight.',
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'name' => 'Legal & Compliance',
            'code' => 'LEG',
            'status' => 'active',
        ]);
    }

    public function test_department_edit_page_can_be_rendered(): void
    {
        $dept = Department::first();

        $response = $this->actingAs($this->superAdmin)->get(route('departments.edit', $dept));

        $response->assertStatus(200);
        $response->assertSee($dept->name);
    }

    public function test_department_can_be_updated(): void
    {
        $dept = Department::where('code', 'ENG')->first();

        $response = $this->actingAs($this->superAdmin)->put(route('departments.update', $dept), [
            'name' => 'Software & AI Engineering',
            'code' => 'ENG',
            'status' => 'active',
            'description' => 'Updated description.',
        ]);

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Software & AI Engineering',
        ]);
    }

    public function test_department_can_be_soft_deleted(): void
    {
        $dept = Department::create([
            'name' => 'Temporary Department',
            'code' => 'TMP',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('departments.destroy', $dept));

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('departments', ['id' => $dept->id]);
    }

    public function test_unauthorized_user_cannot_access_departments(): void
    {
        $response = $this->actingAs($this->employee)->get(route('departments.index'));
        $response->assertStatus(403);
    }
}
