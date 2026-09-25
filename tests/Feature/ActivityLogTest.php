<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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

    public function test_activity_logs_index_page_can_be_rendered(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'login',
            'ip' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('activity-logs.index'));

        $response->assertStatus(200)
            ->assertSee('Activity Logs')
            ->assertSee('Login')
            ->assertSee($this->admin->name);
    }

    public function test_logs_activity_trait_automatically_records_model_events(): void
    {
        // 1. Create a user (should trigger created log)
        $this->actingAs($this->admin);
        $user = User::create([
            'name' => 'Auto Logged User',
            'email' => 'autolog@erp.test',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'action' => 'created',
        ]);

        // 2. Update user (should trigger updated log)
        $user->update(['name' => 'Updated Auto Logged User']);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'action' => 'updated',
        ]);

        // 3. Delete user (should trigger deleted log)
        $user->delete();

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'action' => 'deleted',
        ]);
    }

    public function test_activity_logs_filter_by_user_and_action(): void
    {
        $otherUser = User::factory()->create();

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'profile.updated',
            'ip' => '192.168.1.1',
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $otherUser->id,
            'action' => 'password.changed',
            'ip' => '192.168.1.2',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('activity-logs.index', [
            'user_id' => $this->admin->id,
            'action' => 'profile.updated',
        ]));

        $response->assertStatus(200)
            ->assertSee('Profile Updated')
            ->assertDontSee('192.168.1.2');
    }
}
