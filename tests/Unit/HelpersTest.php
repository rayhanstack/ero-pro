<?php

namespace Tests\Unit;

use App\Helpers\TranslationManager;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TranslationManager::reset();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
        parent::tearDown();
    }

    public function test_trans_translates_existing_keys_and_handles_replacements(): void
    {
        $this->assertEquals('Dashboard', _trans('common.Dashboard'));
        $this->assertEquals('', _trans(null));
        $this->assertEquals('', _trans(''));

        // Missing key gets added and returned
        $uniqueKey = 'common.TestCustomKey_'.uniqid();
        $this->assertStringContainsString('TestCustomKey', _trans($uniqueKey));
    }

    public function test_trans_batches_dirty_keys_to_disk_on_flush(): void
    {
        $testKey = 'UnitTestKey_'.time();
        _trans('test_module.'.$testKey);

        TranslationManager::flushPending();

        $filePath = lang_path('en/test_module.json');
        $this->assertFileExists($filePath);

        $json = json_decode(file_get_contents($filePath), true);
        $this->assertArrayHasKey($testKey, $json);
        $this->assertEquals($testKey, $json[$testKey]);

        // Cleanup created test file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_global_setting_retrieves_values_and_handles_cache(): void
    {
        Setting::create([
            'key' => 'site_name',
            'value' => 'ERP Pro Custom',
            'status' => 'active',
        ]);

        Cache::forget('settings.base');

        $this->assertEquals('ERP Pro Custom', globalSetting('site_name'));
        $this->assertEquals('default_val', globalSetting('non_existent', 'default_val'));
        $this->assertIsArray(globalSetting());
    }

    public function test_global_setting_decodes_json_storage_urls(): void
    {
        Storage::fake('public');

        $fileJson = json_encode([
            'disk' => 'public',
            'files' => ['uploads/branding/logo.png'],
        ]);

        Setting::create([
            'key' => 'site_logo',
            'value' => $fileJson,
            'status' => 'active',
        ]);

        Cache::forget('settings.base');

        $url = globalSetting('site_logo');
        $this->assertStringContainsString('logo.png', $url);
    }

    public function test_date_and_time_format_helpers(): void
    {
        Setting::create(['key' => 'date_format', 'value' => 'd/m/Y', 'status' => 'active']);
        Setting::create(['key' => 'time_format', 'value' => 'h:i A', 'status' => 'active']);
        Setting::create(['key' => 'timezone', 'value' => 'UTC', 'status' => 'active']);
        Cache::forget('settings.base');

        $timestamp = '2026-05-15 14:30:00';

        $this->assertEquals('15/05/2026', formatDate($timestamp));
        $this->assertEquals('-', formatDate(null));

        $this->assertEquals('02:30 PM', formatTime($timestamp));
        $this->assertEquals('-', formatTime(null));

        $this->assertEquals('15/05/2026 02:30 PM', formatDateTime($timestamp));
        $this->assertEquals('-', formatDateTime(null));

        $this->assertNotEmpty(formatDate(now()->subDays(2), 'humanDiff'));
    }

    public function test_has_permission_stubs_return_true(): void
    {
        $this->assertTrue(hasPermission('employee.view'));
        $this->assertTrue(hasAnyPermission(['employee.view', 'employee.create']));
        $this->assertTrue(hasAllPermissions(['employee.view', 'employee.create']));
    }

    public function test_get_file_path_returns_urls_and_fallbacks(): void
    {
        Storage::fake('public');

        $this->assertStringContainsString('default', getFilePath(null));
        $this->assertStringContainsString('default.webp', getFilePath(null, 'avatar'));

        $singleFileJson = json_encode([
            'disk' => 'public',
            'file' => 'avatars/admin.png',
        ]);
        $url = getFilePath($singleFileJson);
        $this->assertStringContainsString('admin.png', $url);

        $directUrl = 'https://example.com/image.png';
        $this->assertEquals($directUrl, getFilePath($directUrl));
    }

    public function test_format_title_case_and_is_rtl(): void
    {
        $this->assertEquals('First Name', formatTitleCase('first_name'));
        $this->assertEquals('User Role', formatTitleCase('user-role'));
        $this->assertEquals('', formatTitleCase(null));

        $this->assertFalse(isRTL());

        Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'rtl' => 1,
            'status' => 'active',
        ]);

        app()->setLocale('ar');
        $this->assertTrue(isRTL());
        app()->setLocale('en');
    }
}
