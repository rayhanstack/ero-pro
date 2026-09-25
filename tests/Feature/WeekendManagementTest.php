<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Weekend;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\WeekendSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeekendManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            WeekendSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_weekends_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('weekends.index'));

        $response->assertStatus(200);
        $response->assertSee('Weekly Holidays (Weekends)');
        $response->assertSee('Friday');
        $response->assertSee('Sunday');
    }

    public function test_weekends_can_be_updated(): void
    {
        // Set Friday (5) and Saturday (6) as weekends
        $response = $this->actingAs($this->superAdmin)->put(route('weekends.update'), [
            'weekends' => [5, 6],
        ]);

        $response->assertRedirect(route('weekends.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(1, Weekend::where('day_of_week', 5)->first()->is_weekend);
        $this->assertEquals(1, Weekend::where('day_of_week', 6)->first()->is_weekend);
        $this->assertEquals(0, Weekend::where('day_of_week', 0)->first()->is_weekend);
    }

    public function test_unauthorized_user_cannot_access_weekends(): void
    {
        $response = $this->actingAs($this->employee)->get(route('weekends.index'));
        $response->assertStatus(403);
    }
}
