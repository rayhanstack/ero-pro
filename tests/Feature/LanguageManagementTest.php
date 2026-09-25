<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            LanguageSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_languages_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('languages.index'));

        $response->assertStatus(200);
        $response->assertSee('Languages');
        $response->assertSee('English');
    }

    public function test_language_can_be_created(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('languages.store'), [
            'name' => 'Arabic',
            'code' => 'ar',
            'native' => 'العربية',
            'rtl' => '1',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('languages', [
            'name' => 'Arabic',
            'code' => 'ar',
            'rtl' => 1,
            'is_default' => 0,
            'status' => 'active',
        ]);
    }

    public function test_language_can_be_updated(): void
    {
        $language = Language::where('code', 'bn')->first();

        $response = $this->actingAs($this->superAdmin)->put(route('languages.update', $language), [
            'name' => 'Bengali Updated',
            'code' => 'bn',
            'native' => 'বাংলা',
            'rtl' => '0',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'name' => 'Bengali Updated',
        ]);
    }

    public function test_language_can_be_set_as_default(): void
    {
        $language = Language::where('code', 'bn')->first();

        $response = $this->actingAs($this->superAdmin)->patch(route('languages.default', $language));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, $language->fresh()->is_default);
        $this->assertEquals(0, Language::where('code', 'en')->first()->is_default);
    }

    public function test_default_language_cannot_be_deleted(): void
    {
        $defaultLang = Language::where('is_default', 1)->first();

        $response = $this->actingAs($this->superAdmin)->delete(route('languages.destroy', $defaultLang));

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('languages', ['id' => $defaultLang->id]);
    }

    public function test_locale_switcher_changes_session_and_app_locale(): void
    {
        $response = $this->get(route('locale.switch', 'bn'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'bn');

        // Test SetLocale middleware with active session
        $response = $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'bn'])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $this->assertEquals('bn', app()->getLocale());
    }
}
