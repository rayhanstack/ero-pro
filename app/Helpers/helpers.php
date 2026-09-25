<?php

use App\Helpers\MediaHelper;
use App\Helpers\TranslationManager;
use App\Models\Language;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

if (! function_exists('_trans')) {
    /**
     * Translate string using static JSON cache and batched missing-key writing on shutdown.
     */
    function _trans(?string $key = null, array $replace = []): string
    {
        return TranslationManager::trans($key, $replace);
    }
}

if (! function_exists('globalSetting')) {
    /**
     * Get a global setting value from cached base settings or return default.
     * Automatically decodes {disk, files} / {disk, file} JSON into public storage URLs.
     */
    function globalSetting(?string $key = null, $default = null)
    {
        try {
            $settings = Cache::rememberForever('settings.base', function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            if ($key === null) {
                return $settings;
            }

            if (! is_array($settings) || ! array_key_exists($key, $settings)) {
                return $default;
            }

            $value = $settings[$key];

            if ($value === null) {
                return $default;
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_array($decoded) && isset($decoded['disk'])) {
                    if (isset($decoded['files']) && is_array($decoded['files'])) {
                        $fileUrls = [];
                        foreach ($decoded['files'] as $filePath) {
                            $fileUrls[] = Storage::disk($decoded['disk'])->url($filePath);
                        }

                        return count($fileUrls) === 1 ? $fileUrls[0] : $fileUrls;
                    }

                    if (isset($decoded['file']) && is_string($decoded['file'])) {
                        return Storage::disk($decoded['disk'])->url($decoded['file']);
                    }
                }
            }

            return $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('formatDate')) {
    /**
     * Format a date string or Carbon instance using settings date_format and timezone.
     */
    function formatDate($date, ?string $format = null, ?string $timezone = null): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            $tz = $timezone
                ?? Auth::user()?->time_zone
                ?? Auth::user()?->timezone
                ?? globalSetting('timezone')
                ?? globalSetting('time_zone')
                ?? config('app.timezone', 'UTC');

            $carbon = $date instanceof Carbon
                ? $date->copy()->setTimezone($tz)
                : Carbon::parse($date)->setTimezone($tz);

            $format = $format ?? globalSetting('date_format') ?: 'Y-m-d';

            if ($format === 'humanDiff') {
                return $carbon->diffForHumans();
            }

            return $carbon->format($format);
        } catch (\Throwable $e) {
            return is_string($date) ? $date : '-';
        }
    }
}

if (! function_exists('formatTime')) {
    /**
     * Format a time string or Carbon instance using settings time_format and timezone.
     */
    function formatTime($time, ?string $format = null, ?string $timezone = null): string
    {
        if (empty($time)) {
            return '-';
        }

        try {
            $tz = $timezone
                ?? Auth::user()?->time_zone
                ?? Auth::user()?->timezone
                ?? globalSetting('timezone')
                ?? globalSetting('time_zone')
                ?? config('app.timezone', 'UTC');

            $carbon = $time instanceof Carbon
                ? $time->copy()->setTimezone($tz)
                : Carbon::parse($time)->setTimezone($tz);

            $format = $format ?? globalSetting('time_format') ?: 'H:i:s';

            if ($format === 'humanDiff') {
                return $carbon->diffForHumans();
            }

            return $carbon->format($format);
        } catch (\Throwable $e) {
            return is_string($time) ? $time : '-';
        }
    }
}

if (! function_exists('formatDateTime')) {
    /**
     * Format a datetime string or Carbon instance using settings format and timezone.
     */
    function formatDateTime($date, ?string $format = null, ?string $timezone = null): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            $tz = $timezone
                ?? Auth::user()?->time_zone
                ?? Auth::user()?->timezone
                ?? globalSetting('timezone')
                ?? globalSetting('time_zone')
                ?? config('app.timezone', 'UTC');

            $carbon = $date instanceof Carbon
                ? $date->copy()->setTimezone($tz)
                : Carbon::parse($date)->setTimezone($tz);

            if ($format === 'humanDiff') {
                return $carbon->diffForHumans();
            }

            if ($format === null) {
                $dateFormat = globalSetting('date_format') ?: 'Y-m-d';
                $timeFormat = globalSetting('time_format') ?: 'H:i:s';
                $format = $dateFormat.' '.$timeFormat;
            }

            return $carbon->format($format);
        } catch (\Throwable $e) {
            return is_string($date) ? $date : '-';
        }
    }
}

if (! function_exists('hasPermission')) {
    /**
     * Check if user has permission.
     */
    function hasPermission(string $permission, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}

if (! function_exists('hasAnyPermission')) {
    /**
     * Check if user has any of the given permissions.
     */
    function hasAnyPermission(array|string $permissions, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        $permissions = (array) $permissions;

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('hasAllPermissions')) {
    /**
     * Check if user has all given permissions.
     */
    function hasAllPermissions(array $permissions, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (! $user->can($permission)) {
                return false;
            }
        }

        return true;
    }
}

if (! function_exists('getFallbackImage')) {
    /**
     * Return fallback placeholder image based on type.
     */
    function getFallbackImage(string $type = 'default'): string
    {
        return match ($type) {
            'avatar', 'user', 'profile' => asset('assets/images/avatars/default.webp'),
            default => asset('assets/images/avatars/default-fallback-image.png'),
        };
    }
}

if (! function_exists('getFilePath')) {
    /**
     * Get file path or URL from JSON/array metadata with fallback support.
     */
    function getFilePath($json = null, string $fallback = 'default')
    {
        if (empty($json)) {
            return getFallbackImage($fallback);
        }

        $data = is_array($json) ? $json : (is_string($json) ? json_decode($json, true) : null);

        if (is_array($data)) {
            $disk = $data['disk'] ?? 'public';

            if (! empty($data['files']) && is_array($data['files'])) {
                $urls = array_map(function ($file) use ($disk) {
                    if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
                        return $file;
                    }

                    return Storage::disk($disk)->url($file);
                }, $data['files']);

                return count($urls) === 1 ? $urls[0] : $urls;
            }

            if (! empty($data['file']) && is_string($data['file'])) {
                if (str_starts_with($data['file'], 'http://') || str_starts_with($data['file'], 'https://')) {
                    return $data['file'];
                }

                return Storage::disk($disk)->url($data['file']);
            }
        }

        if (is_string($json)) {
            if (str_starts_with($json, 'http://') || str_starts_with($json, 'https://')) {
                return $json;
            }
            if (str_starts_with($json, 'assets/')) {
                return asset($json);
            }

            return Storage::disk('public')->url($json);
        }

        return getFallbackImage($fallback);
    }
}

if (! function_exists('formatTitleCase')) {
    /**
     * Format a keyword into Title Case.
     */
    function formatTitleCase(?string $keyword): string
    {
        if (! $keyword) {
            return '';
        }

        return ucwords(str_replace(['_', '-', '.'], ' ', strtolower($keyword)));
    }
}

if (! function_exists('isRTL')) {
    /**
     * Check if current locale is RTL.
     */
    function isRTL(): bool
    {
        try {
            $locale = app()->getLocale();
            $language = Language::where('code', $locale)->first();

            return $language && (int) $language->rtl === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }
}