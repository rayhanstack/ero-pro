<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class TranslationManager
{
    /**
     * In-memory cache of loaded translations: [$locale => [$fileName => [$key => $value]]]
     */
    protected static array $translations = [];

    /**
     * File paths that have missing keys added during the request and need writing to disk.
     */
    protected static array $dirtyFiles = [];

    /**
     * Whether the shutdown callback has been registered.
     */
    protected static bool $shutdownRegistered = false;

    /**
     * Translate the given key.
     */
    public static function trans(?string $key, array $replace = []): string
    {
        if ($key === null || $key === '') {
            return '';
        }

        try {
            $locale = app()->bound('translator') ? app()->getLocale() : config('app.locale', 'en');

            if (str_contains($key, '.') && ! str_contains(explode('.', $key, 2)[0], ' ') && ! str_ends_with($key, '.')) {
                [$fileName, $transKey] = explode('.', $key, 2);
            } else {
                $fileName = $locale;
                $transKey = $key;
            }

            if (! isset(self::$translations[$locale][$fileName])) {
                $dirPath = function_exists('lang_path') ? lang_path($locale) : base_path("lang/{$locale}");
                $filePath = "{$dirPath}/{$fileName}.json";

                if (file_exists($filePath)) {
                    $content = @file_get_contents($filePath);
                    $decoded = json_decode($content, true);
                    self::$translations[$locale][$fileName] = is_array($decoded) ? $decoded : [];
                } else {
                    self::$translations[$locale][$fileName] = [];
                }
            }

            if (! array_key_exists($transKey, self::$translations[$locale][$fileName])) {
                self::$translations[$locale][$fileName][$transKey] = $transKey;

                $dirPath = function_exists('lang_path') ? lang_path($locale) : base_path("lang/{$locale}");
                $filePath = "{$dirPath}/{$fileName}.json";

                self::$dirtyFiles[$filePath] = &self::$translations[$locale][$fileName];

                self::registerShutdown();
            }

            $line = self::$translations[$locale][$fileName][$transKey] ?? $transKey;

            if (! empty($replace) && is_string($line)) {
                foreach ($replace as $replaceKey => $value) {
                    $line = str_replace(
                        [':'.$replaceKey, ':'.Str::upper($replaceKey), ':'.Str::ucfirst($replaceKey)],
                        [$value, Str::upper((string) $value), Str::ucfirst((string) $value)],
                        $line
                    );
                }
            }

            return (string) $line;
        } catch (\Throwable $e) {
            return (string) $key;
        }
    }

    /**
     * Register shutdown function to batch save dirty translation files.
     */
    protected static function registerShutdown(): void
    {
        if (self::$shutdownRegistered) {
            return;
        }

        self::$shutdownRegistered = true;

        register_shutdown_function(function () {
            self::flushPending();
        });
    }

    /**
     * Flush and write all pending dirty translation files to disk.
     */
    public static function flushPending(): void
    {
        foreach (self::$dirtyFiles as $filePath => $data) {
            $dir = dirname($filePath);
            if (! is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            @file_put_contents(
                $filePath,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        self::$dirtyFiles = [];
    }

    /**
     * Reset in-memory cache and dirty files (useful for tests).
     */
    public static function reset(): void
    {
        self::$translations = [];
        self::$dirtyFiles = [];
    }
}
