<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_admin_routes_render_successfully(): void
    {
        $routes = [
            'dashboard',
            'components',
            'settings',
            'project',
            'client',
            'task',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertStatus(200);
        }
    }

    public function test_flash_message_toasts_render_in_session(): void
    {
        $response = $this->withSession([
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
