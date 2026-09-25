<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $this->user->assignRole('Super Admin');
    }

    public function test_settings_page_renders_profile_tab(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings', ['tab' => 'profile']));

        $response->assertStatus(200)
            ->assertSee('Public Profile')
            ->assertSee($this->user->name)
            ->assertSee($this->user->email);
    }

    public function test_user_can_update_profile_details_and_avatar(): void
    {
        Storage::fake('public');
        $avatar = UploadedFile::fake()->image('profile.png');

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => 'Updated User Name',
            'phone' => '+8801812345678',
            'time_zone' => 'Asia/Dhaka',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'profile']))
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('Updated User Name', $this->user->name);
        $this->assertEquals('+8801812345678', $this->user->phone);
        $this->assertEquals('Asia/Dhaka', $this->user->time_zone);
        $this->assertNotNull($this->user->avatar);

        // Check activity log recorded
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'profile.updated',
        ]);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'old-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'security']))
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $this->user->password));

        // Check activity log recorded
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'password.changed',
        ]);
    }

    public function test_password_update_fails_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors('current_password');

        $this->user->refresh();
        $this->assertFalse(Hash::check('new-secure-password', $this->user->password));
    }
}
