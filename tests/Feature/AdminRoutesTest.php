<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_all_admin_routes_render_successfully(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $routes = [
            'dashboard',
            'components',
            'settings',
            'project',
            'client',
            'task',
            'roles.index',
            'roles.create',
            'users.index',
            'users.create',
            'activity-logs.index',
            'languages.index',
            'languages.create',
            'departments.index',
            'departments.create',
            'designations.index',
            'designations.create',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertStatus(200);
        }
    }

    public function test_flash_message_toasts_render_in_session(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->withSession([
            'success' => 'Operation completed successfully!',
            'error' => 'An error occurred.',
        ])->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Operation completed successfully!');
        $response->assertSee('An error occurred.');
    }

    public function test_guest_layout_renders(): void
    {
        $view = $this->view('admin.layouts.guest', ['title' => 'Sign In']);
        $view->assertSee('Sign In');
        $view->assertSee('auth-wrapper');
    }
}
