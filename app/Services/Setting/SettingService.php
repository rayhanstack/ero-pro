<?php

namespace App\Services\Setting;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    /**
     * Cache key for base settings.
     */
    public const CACHE_KEY = 'settings.base';

    /**
     * Get a setting value by key.
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    /**
     * Set or update a single setting.
     */
    public function set(string $key, $value, string $group = 'general'): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group,
                'status' => 'active',
            ]
        );

        $this->clearCache();

        return $setting;
    }

    /**
     * Set or update multiple settings at once.
     */
    public function setMany(array $settings, string $group = 'general'): void
    {
        DB::transaction(function () use ($settings, $group) {
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'group' => $group,
                        'status' => 'active',
                    ]
                );
            }
        });

        $this->clearCache();
    }

    /**
     * Get all base settings as key-value pairs (or filtered by group).
     *
     * @return array<string, mixed>
     */
    public function all(?string $group = null): array
    {
        if ($group !== null) {
            return Setting::where('group', $group)->pluck('value', 'key')->toArray();
        }

        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get all settings grouped by group column.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getGrouped(): array
    {
        $all = Setting::all();
        $grouped = [];

        foreach ($all as $setting) {
            $grp = $setting->group ?: 'general';
            $grouped[$grp][$setting->key] = $setting->value;
        }

        return $grouped;
    }

    /**
     * Invalidate the settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
