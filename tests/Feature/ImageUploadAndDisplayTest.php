<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadAndDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@erp.test',
        ]);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_profile_avatar_upload_and_display(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile_pic.jpg', 200, 200);

        $response = $this->actingAs($this->superAdmin)->put(route('profile.update'), [
            'name' => 'Admin Updated',
            'phone' => '1234567890',
            'time_zone' => 'UTC',
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->superAdmin->refresh();

        $this->assertNotNull($this->superAdmin->avatar);
        $avatarData = json_decode($this->superAdmin->avatar, true);
        $this->assertArrayHasKey('file', $avatarData);
        $this->assertEquals('public', $avatarData['disk']);

        Storage::disk('public')->assertExists($avatarData['file']);

        // Check getFilePath helper with both argument orders
        $url1 = getFilePath('user', $this->superAdmin->avatar);
        $url2 = getFilePath($this->superAdmin->avatar, 'user');
        $this->assertStringContainsString('storage/' . $avatarData['file'], $url1);
        $this->assertEquals($url1, $url2);
        $this->assertEquals($url1, $this->superAdmin->avatar_url);

        // Check avatar in settings page HTML
        $pageResponse = $this->actingAs($this->superAdmin)->get(route('settings'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee($avatarData['file']);
    }

    public function test_user_management_avatar_upload_and_display_in_table(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('john_avatar.png', 100, 100);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'John Doe',
            'email' => 'john@erp.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '01700000000',
            'status' => 'active',
            'role' => 'Employee',
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'john@erp.test')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->avatar);

        $avatarData = json_decode($newUser->avatar, true);
        Storage::disk('public')->assertExists($avatarData['file']);

        // Check users index table contains the avatar image URL
        $indexResponse = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($avatarData['file']);

        // Test updating user avatar deletes old file
        $oldFile = $avatarData['file'];
        $newFile = UploadedFile::fake()->image('john_new_avatar.png', 150, 150);

        $updateResponse = $this->actingAs($this->superAdmin)->put(route('users.update', $newUser), [
            'name' => 'John Doe Updated',
            'email' => 'john@erp.test',
            'phone' => '01700000000',
            'status' => 'active',
            'role' => 'Employee',
            'avatar' => $newFile,
        ]);

        $updateResponse->assertRedirect(route('users.index'));
        $newUser->refresh();

        $newAvatarData = json_decode($newUser->avatar, true);
        Storage::disk('public')->assertMissing($oldFile);
        Storage::disk('public')->assertExists($newAvatarData['file']);
    }

    public function test_company_logo_upload_and_display_in_sidebar_and_navbar(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('company_logo.png', 300, 100);
        $favicon = UploadedFile::fake()->image('company_favicon.ico', 32, 32);

        $response = $this->actingAs($this->superAdmin)->put(route('settings.company'), [
            'company_name' => 'Acme Corporation',
            'company_email' => 'info@acme.test',
            'company_phone' => '+123456789',
            'company_address' => '123 Business St',
            'company_logo' => $logo,
            'company_favicon' => $favicon,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logoUrl = globalSetting('company_logo');
        $this->assertNotNull($logoUrl);
        $this->assertStringContainsString('storage/settings/branding/', $logoUrl);

        $dashboardResponse = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $this->assertStringContainsString('storage/settings/branding/', $dashboardResponse->getContent());
    }

    public function test_fallback_avatar_displays_when_user_has_no_image(): void
    {
        $userWithoutAvatar = User::factory()->create([
            'name' => 'No Avatar User',
            'avatar' => null,
        ]);

        $fallbackUrl = getFilePath('user', $userWithoutAvatar->avatar);
        $this->assertNotEmpty($fallbackUrl);
        $this->assertStringContainsString('assets/images/avatars/default.webp', $fallbackUrl);

        $indexResponse = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('assets/images/avatars/default.webp');
    }
}
