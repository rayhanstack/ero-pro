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
}
