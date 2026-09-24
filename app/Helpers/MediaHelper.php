<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaHelper
{
    /**
     * Upload a file or array of files to storage.
     *
     * @param  UploadedFile|array|null  $file
     * @param  string  $folder
     * @param  string  $disk
     * @return array|null
     */
    public static function upload($file, string $folder = 'uploads', string $disk = 'public'): ?array
    {
        if (empty($file)) {
            return null;
        }

        if (is_array($file)) {
            $uploadedFiles = [];
            foreach ($file as $singleFile) {
                if ($singleFile instanceof UploadedFile) {
                    $uploadedFiles[] = $singleFile->store($folder, $disk);
                }
            }

            return [
                'disk' => $disk,
                'files' => $uploadedFiles,
            ];
        }

        if ($file instanceof UploadedFile) {
            $path = $file->store($folder, $disk);

            return [
                'disk' => $disk,
                'file' => $path,
            ];
        }

        return null;
    }

    /**
     * Delete file(s) from storage.
     *
     * @param  string|array|null  $path
     * @param  string|null  $disk
     * @return bool
     */
    public static function delete($path, ?string $disk = null): bool
    {
        if (empty($path)) {
            return false;
        }

        $data = is_array($path) ? $path : (is_string($path) ? json_decode($path, true) : null);

        if (is_array($data)) {
            $targetDisk = $disk ?? ($data['disk'] ?? 'public');

            if (! empty($data['files']) && is_array($data['files'])) {
                $deletedAny = false;
                foreach ($data['files'] as $file) {
                    if (Storage::disk($targetDisk)->exists($file)) {
                        Storage::disk($targetDisk)->delete($file);
                        $deletedAny = true;
                    }
                }

                return $deletedAny;
            }

            if (! empty($data['file']) && is_string($data['file'])) {
                if (Storage::disk($targetDisk)->exists($data['file'])) {
                    return Storage::disk($targetDisk)->delete($data['file']);
                }

                return false;
            }
        }

        if (is_string($path)) {
            $targetDisk = $disk ?? 'public';
            if (Storage::disk($targetDisk)->exists($path)) {
                return Storage::disk($targetDisk)->delete($path);
            }
        }

        return false;
    }

    /**
     * Get URL for stored media path or json.
     *
     * @param  string|array|null  $path
     * @param  string|null  $disk
     * @param  string  $fallback
     * @return string
     */
    public static function url($path, ?string $disk = null, string $fallback = 'default'): string
    {
        if (empty($path)) {
            return getFallbackImage($fallback);
        }

        $data = is_array($path) ? $path : (is_string($path) ? json_decode($path, true) : null);

        if (is_array($data)) {
            $targetDisk = $disk ?? ($data['disk'] ?? 'public');

            if (! empty($data['files']) && is_array($data['files'])) {
                $first = $data['files'][0] ?? null;
                if ($first) {
                    if (str_starts_with($first, 'http://') || str_starts_with($first, 'https://')) {
                        return $first;
                    }

                    return Storage::disk($targetDisk)->url($first);
                }
            }

            if (! empty($data['file']) && is_string($data['file'])) {
                if (str_starts_with($data['file'], 'http://') || str_starts_with($data['file'], 'https://')) {
                    return $data['file'];
                }

                return Storage::disk($targetDisk)->url($data['file']);
            }
        }

        if (is_string($path)) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            if (str_starts_with($path, 'assets/')) {
                return asset($path);
            }

            $targetDisk = $disk ?? 'public';

            return Storage::disk($targetDisk)->url($path);
        }

        return getFallbackImage($fallback);
    }
}
