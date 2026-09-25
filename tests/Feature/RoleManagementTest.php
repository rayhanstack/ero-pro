<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    public function test_roles_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));

        $response->assertStatus(200)
            ->assertSee('Roles & Permissions')
            ->assertSee('Super Admin')
            ->assertSee('Admin')
            ->assertSee('HR')
            ->assertSee('Manager')
            ->assertSee('Employee');
    }

    public function test_create_role_page_can_be_rendered_with_permission_matrix(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.create'));

        $response->assertStatus(200)
            ->assertSee('Create New Role')
            ->assertSee('Module Permissions')
            ->assertSee('Dashboard')
            ->assertSee('Employee')
            ->assertSee('Payroll');
    }

    public function test_role_can_be_created_with_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Accountant',
            'permissions' => [
                'finance.view',
                'finance.create',
                'finance.edit',
                'payroll.view',
            ],
        ]);

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Accountant']);

        $role = Role::findByName('Accountant', 'web');
        $this->assertTrue($role->hasPermissionTo('finance.view'));
        $this->assertTrue($role->hasPermissionTo('payroll.view'));
        $this->assertFalse($role->hasPermissionTo('user.create'));
    }

    public function test_edit_role_page_can_be_rendered(): void
    {
        $role = Role::findByName('HR', 'web');

        $response = $this->actingAs($this->admin)->get(route('roles.edit', $role));

        $response->assertStatus(200)
            ->assertSee('Edit Role')
            ->assertSee('HR');
    }

    public function test_role_can_be_updated_with_synced_permissions(): void
    {
        $role = Role::create(['name' => 'Support Agent', 'guard_name' => 'web']);
        $role->givePermissionTo('task.view');

        $response = $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name' => 'Senior Support Agent',
            'permissions' => [
                'task.view',
                'task.edit',
                'client.view',
            ],
        ]);

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Senior Support Agent', $role->name);
        $this->assertTrue($role->hasPermissionTo('task.edit'));
        $this->assertTrue($role->hasPermissionTo('client.view'));
    }

    public function test_custom_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $role));

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['name' => 'Temporary Role']);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $superAdminRole = Role::findByName('Super Admin', 'web');

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $superAdminRole));

        $response->assertRedirect(route('roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
    }

    public function test_user_without_permission_cannot_access_roles(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $response = $this->actingAs($employee)->get(route('roles.index'));
        $response->assertStatus(403);
    }
}
