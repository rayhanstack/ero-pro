<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_users_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200)
            ->assertSee('User Management')
            ->assertSee($this->admin->name)
            ->assertSee($this->admin->email);
    }

    public function test_users_index_filters_by_search_query(): void
    {
        $targetUser = User::factory()->create(['name' => 'Unique Developer', 'email' => 'unique@erp.test']);
        $otherUser = User::factory()->create(['name' => 'John Regular', 'email' => 'john@erp.test']);

        $response = $this->actingAs($this->admin)->get(route('users.index', ['search' => 'Unique']));

        $response->assertStatus(200)
            ->assertSee('Unique Developer')
            ->assertDontSee('John Regular');
    }

    public function test_create_user_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertStatus(200)
            ->assertSee('Create New User')
            ->assertSee('Assign Role');
    }

    public function test_user_can_be_created_with_role_and_avatar(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Jane HR Specialist',
            'email' => 'jane.hr@erp.test',
            'password' => 'secret123',
            'phone' => '+8801712345678',
            'role' => 'HR',
            'status' => 'active',
            'time_zone' => 'Asia/Dhaka',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Jane HR Specialist',
            'email' => 'jane.hr@erp.test',
            'phone' => '+8801712345678',
            'status' => 'active',
        ]);

        $createdUser = User::where('email', 'jane.hr@erp.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('HR'));
        $this->assertTrue(Hash::check('secret123', $createdUser->password));
        $this->assertNotNull($createdUser->avatar);
    }

    public function test_edit_user_page_can_be_rendered(): void
    {
        $user = User::factory()->create(['name' => 'Test Employee']);
        $user->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('users.edit', $user));

        $response->assertStatus(200)
            ->assertSee('Edit User')
            ->assertSee('Test Employee');
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@erp.test']);
        $user->assignRole('Employee');

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@erp.test',
            'phone' => '+8801999999999',
            'role' => 'Manager',
            'status' => 'active',
            'time_zone' => 'Asia/Dhaka',
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@erp.test', $user->email);
        $this->assertTrue($user->hasRole('Manager'));
    }

    public function test_user_status_can_be_toggled(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->patch(route('users.status', $user));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('inactive', $user->status);

        $this->actingAs($this->admin)->patch(route('users.status', $user));
        $user->refresh();
        $this->assertEquals('active', $user->status);
    }

    public function test_user_cannot_deactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('users.status', $this->admin));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->admin->refresh();
        $this->assertEquals('active', $this->admin->status);
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Employee');

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_without_permission_cannot_access_user_management(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $response = $this->actingAs($employee)->get(route('users.index'));
        $response->assertStatus(403);
    }
}
