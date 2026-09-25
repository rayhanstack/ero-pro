<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            ShiftSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_shifts_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('shifts.index'));

        $response->assertStatus(200);
        $response->assertSee('Shifts');
        $response->assertSee('Regular Day Shift');
    }

    public function test_shift_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('shifts.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Shift');
    }

    public function test_shift_can_be_created(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('shifts.store'), [
            'name' => 'Afternoon Special Shift',
            'start_time' => '13:00',
            'end_time' => '21:00',
            'grace_minutes' => 15,
            'status' => 'active',
            'description' => 'Custom shift for operations.',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', [
            'name' => 'Afternoon Special Shift',
            'grace_minutes' => 15,
            'status' => 'active',
        ]);
    }

    public function test_shift_edit_page_can_be_rendered(): void
    {
        $shift = Shift::first();

        $response = $this->actingAs($this->superAdmin)->get(route('shifts.edit', $shift));

        $response->assertStatus(200);
        $response->assertSee($shift->name);
    }

    public function test_shift_can_be_updated(): void
    {
        $shift = Shift::first();

        $response = $this->actingAs($this->superAdmin)->put(route('shifts.update', $shift), [
            'name' => 'Updated Shift Name',
            'start_time' => '08:30',
            'end_time' => '17:30',
            'grace_minutes' => 20,
            'status' => 'active',
            'description' => 'Updated timing.',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'name' => 'Updated Shift Name',
            'grace_minutes' => 20,
        ]);
    }

    public function test_shift_can_be_soft_deleted(): void
    {
        $shift = Shift::first();

        $response = $this->actingAs($this->superAdmin)->delete(route('shifts.destroy', $shift));

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('shifts', ['id' => $shift->id]);
    }

    public function test_unauthorized_user_cannot_access_shifts(): void
    {
        $response = $this->actingAs($this->employee)->get(route('shifts.index'));
        $response->assertStatus(403);
    }
}
