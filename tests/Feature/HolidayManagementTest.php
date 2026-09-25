<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\HolidaySeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            HolidaySeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_holidays_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('holidays.index'));

        $response->assertStatus(200);
        $response->assertSee('Holidays');
        $response->assertSee('International Mother Language Day');
    }

    public function test_holidays_index_filters_by_year_and_type(): void
    {
        $year = (int) date('Y');

        $response = $this->actingAs($this->superAdmin)->get(route('holidays.index', [
            'year' => $year,
            'type' => 'public',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Independence Day');
    }

    public function test_holiday_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('holidays.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Holiday');
    }

    public function test_holiday_can_be_created(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('holidays.store'), [
            'title' => 'New Year Celebration',
            'from_date' => '2027-01-01',
            'to_date' => '2027-01-01',
            'type' => 'public',
            'status' => 'active',
            'description' => 'Welcoming the new year.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'title' => 'New Year Celebration',
            'type' => 'public',
            'status' => 'active',
        ]);
    }

    public function test_holiday_edit_page_can_be_rendered(): void
    {
        $holiday = Holiday::first();

        $response = $this->actingAs($this->superAdmin)->get(route('holidays.edit', $holiday));

        $response->assertStatus(200);
        $response->assertSee($holiday->title);
    }

    public function test_holiday_can_be_updated(): void
    {
        $holiday = Holiday::first();

        $response = $this->actingAs($this->superAdmin)->put(route('holidays.update', $holiday), [
            'title' => 'Updated Holiday Title',
            'from_date' => $holiday->from_date->format('Y-m-d'),
            'to_date' => $holiday->to_date->format('Y-m-d'),
            'type' => 'company',
            'status' => 'active',
            'description' => 'Updated holiday description.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'title' => 'Updated Holiday Title',
            'type' => 'company',
        ]);
    }

    public function test_holiday_can_be_soft_deleted(): void
    {
        $holiday = Holiday::first();

        $response = $this->actingAs($this->superAdmin)->delete(route('holidays.destroy', $holiday));

        $response->assertRedirect(route('holidays.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('holidays', ['id' => $holiday->id]);
    }

    public function test_unauthorized_user_cannot_access_holidays(): void
    {
        $response = $this->actingAs($this->employee)->get(route('holidays.index'));
        $response->assertStatus(403);
    }
}
