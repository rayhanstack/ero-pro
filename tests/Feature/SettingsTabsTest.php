<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTabsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            CurrencySeeder::class,
            LanguageSeeder::class,
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    public function test_settings_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings'));

        $response->assertStatus(200)
            ->assertSee('Settings')
            ->assertSee('Company Details & Branding')
            ->assertSee('Localization & Formats');
    }

    public function test_can_update_company_settings(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('company_logo.png');
        $favicon = UploadedFile::fake()->image('favicon.png');

        $response = $this->actingAs($this->admin)->put(route('settings.company'), [
            'company_name' => 'Acme Enterprise Solutions',
            'company_email' => 'contact@acme.test',
            'company_phone' => '+8801700112233',
            'company_address' => 'Dhaka, Bangladesh',
            'company_description' => 'Leading ERP provider',
            'company_logo' => $logo,
            'company_favicon' => $favicon,
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'company']))
            ->assertSessionHas('success');

        $this->assertEquals('Acme Enterprise Solutions', globalSetting('company_name'));
        $this->assertEquals('contact@acme.test', globalSetting('company_email'));
        $this->assertNotEmpty(globalSetting('company_logo'));
    }

    public function test_can_update_localization_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.localization'), [
            'timezone' => 'Asia/Dhaka',
            'date_format' => 'd-m-Y',
            'time_format' => 'h:i A',
            'default_currency' => 'BDT',
            'default_language' => 'en',
            'week_start_day' => 'saturday',
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'localization']))
            ->assertSessionHas('success');

        $this->assertEquals('Asia/Dhaka', globalSetting('timezone'));
        $this->assertEquals('d-m-Y', globalSetting('date_format'));
        $this->assertEquals('saturday', globalSetting('week_start_day'));
    }

    public function test_can_update_attendance_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.attendance'), [
            'office_start' => '09:30',
            'office_end' => '18:30',
            'grace_minutes' => 20,
            'late_after' => '09:50',
            'half_day_hours' => 4.5,
            'overtime_enabled' => '1',
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'attendance']))
            ->assertSessionHas('success');

        $this->assertEquals('09:30', globalSetting('office_start'));
        $this->assertEquals('20', globalSetting('grace_minutes'));
        $this->assertEquals('1', globalSetting('overtime_enabled'));
    }

    public function test_can_update_leave_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.leave'), [
            'annual_leave_quota' => 18,
            'sick_leave_quota' => 12,
            'casual_leave_quota' => 8,
            'leave_approval_level' => 'multi',
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'leave']))
            ->assertSessionHas('success');

        $this->assertEquals('18', globalSetting('annual_leave_quota'));
        $this->assertEquals('multi', globalSetting('leave_approval_level'));
    }

    public function test_can_update_payroll_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.payroll'), [
            'working_days_mode' => 'working_days',
            'overtime_rate' => 2.0,
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'payroll']))
            ->assertSessionHas('success');

        $this->assertEquals('working_days', globalSetting('working_days_mode'));
        $this->assertEquals('2', globalSetting('overtime_rate'));
    }

    public function test_can_update_mail_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.mail'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'system@erp.test',
            'mail_from_name' => 'ERP System',
        ]);

        $response->assertRedirect(route('settings', ['tab' => 'mail']))
            ->assertSessionHas('success');

        $this->assertEquals('smtp.mailtrap.io', globalSetting('mail_host'));
        $this->assertEquals('system@erp.test', globalSetting('mail_from_address'));
    }
}
