<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\Setting\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SettingService::class);
        Cache::flush();
    }

    public function test_get_and_set_settings(): void
    {
        $this->service->set('app_name', 'ERP Test Pro', 'general');

        $this->assertEquals('ERP Test Pro', $this->service->get('app_name'));
        $this->assertEquals('Default Value', $this->service->get('non_existent', 'Default Value'));
    }

    public function test_set_many_updates_all_keys_and_groups(): void
    {
        $this->service->setMany([
            'timezone' => 'Asia/Dhaka',
            'date_format' => 'd/m/Y',
        ], 'localization');

        $this->assertEquals('Asia/Dhaka', $this->service->get('timezone'));
        $this->assertEquals('d/m/Y', $this->service->get('date_format'));

        $this->assertDatabaseHas('settings', [
            'key' => 'timezone',
            'value' => 'Asia/Dhaka',
            'group' => 'localization',
        ]);
    }

    public function test_cache_is_invalidated_when_setting_saved_or_deleted(): void
    {
        // 1. Initial set and cache populating
        $this->service->set('cached_key', 'initial_value', 'general');
        $this->assertEquals('initial_value', $this->service->get('cached_key'));
        $this->assertTrue(Cache::has(SettingService::CACHE_KEY));

        // 2. Direct model update triggers cache bust
        $setting = Setting::where('key', 'cached_key')->first();
        $setting->update(['value' => 'updated_directly']);

        $this->assertFalse(Cache::has(SettingService::CACHE_KEY));
        $this->assertEquals('updated_directly', $this->service->get('cached_key'));

        // 3. Model delete triggers cache bust
        $setting->delete();
        $this->assertFalse(Cache::has(SettingService::CACHE_KEY));
        $this->assertNull($this->service->get('cached_key'));
    }

    public function test_get_grouped_settings(): void
    {
        $this->service->set('company_name', 'Tech Corp', 'company');
        $this->service->set('timezone', 'UTC', 'localization');

        $grouped = $this->service->getGrouped();

        $this->assertArrayHasKey('company', $grouped);
        $this->assertArrayHasKey('localization', $grouped);
        $this->assertEquals('Tech Corp', $grouped['company']['company_name']);
        $this->assertEquals('UTC', $grouped['localization']['timezone']);
    }
}
