<?php

use App\Exceptions\DepictableException;
use App\Helpers\MediaSeoHelper;
use App\Models\Company;
use App\Models\Language;
use App\Models\Setting\AddonSetting;
use App\Models\Setting\BrandSetting;
use App\Models\Setting\IntegrationSetting;
use App\Models\Setting\Setting;
use App\Models\Setting\SocialIconSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Facades\Module;

if (! function_exists('_trans')) {
    function _trans($value)
    {
        try {
            $local = app()->getLocale();

            $langPath = base_path("lang/{$local}/");
            if (! file_exists($langPath)) {
                mkdir($langPath, 0777, true);
            }

            if (str_contains($value, '.')) {
                [$file_name, $trans_key] = explode('.', $value, 2);
                $file_path = $langPath.$file_name.'.json';
            } else {
                $trans_key = $value;
                $file_path = $langPath.$local.'.json';
            }

            if (! file_exists($file_path)) {
                file_put_contents($file_path, json_encode(new stdClass, JSON_PRETTY_PRINT));
            }

            $file_data = json_decode(file_get_contents($file_path), true);
            if (! is_array($file_data)) {
                $file_data = [];
            }

            if (! array_key_exists($trans_key, $file_data)) {
                $file_data[$trans_key] = $trans_key;

                file_put_contents(
                    $file_path,
                    json_encode($file_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }

            return $file_data[$trans_key] ?? $value;
        } catch (Exception $exception) {
            return $value;
        }
    }
}

// if (! function_exists('catchHandler')) {
//     function catchHandler($exception)
//     {
//         $statusCode = (int) $exception->getCode();

//         if ($statusCode < 100 || $statusCode > 599) {
//             $statusCode = 500;
//         }

//         if ($exception instanceof DepictableException) {
//             $exception->report();

//             return $exception->render(request());
//         }

//         $isJson = request()->expectsJson();
//         $isDebug = config('app.debug');

//         report($exception);

//         $message = $isDebug ? $exception->getMessage() : _trans('common.Something went wrong. Please try again later.');

//         if ($isJson) {
//             return response()->json([
//                 'success' => false,
//                 'message' => $message,
//             ], $statusCode);
//         }

//         return redirect()->back()->with('error', $message);
//     }
// }


// if (! function_exists('globalSetting')) {
//     function globalSetting($key, $form = 'addon')
//     {
//         try {
//             $cacheKey = "settings.{$form}";

//             $settings = Cache::remember($cacheKey, 3600, function () use ($form) {
//                 switch ($form) {
//                     case 'base':
//                         return Setting::pluck('value', 'key')->toArray();

//                     case 'brand':
//                         return BrandSetting::pluck('value', 'key')->toArray();

//                     case 'addon':
//                         return AddonSetting::pluck('value', 'key')->toArray();

//                     case 'integration':
//                         return IntegrationSetting::pluck('value', 'key')->toArray();

//                     case 'social':
//                         return SocialIconSetting::pluck('value', 'key')->toArray();
//                 }

//                 return [];
//             });

//             $value = $settings[$key] ?? '';

//             // Try to decode JSON
//             $decoded = json_decode($value, true);

//             if (
//                 is_array($decoded)
//                 && isset($decoded['disk'], $decoded['files'])
//                 && is_array($decoded['files'])
//             ) {
//                 $fileUrls = [];

//                 foreach ($decoded['files'] as $filePath) {
//                     $fileUrls[] = Storage::disk($decoded['disk'])->url($filePath);
//                 }

//                 return count($fileUrls) === 1 ? $fileUrls[0] : $fileUrls; // Return string or array
//             }

//             return $value;
//         } catch (Exception $e) {
//             return null;
//         }
//     }
// }

// if (! function_exists('getFilePath')) {
//     function getFilePath($json, $fallbackType = 'default')
//     {
//         $data = is_array($json) ? $json : json_decode($json, true);

//         if (! is_array($data)) {
//             return getFallbackImage($fallbackType);
//         }

//         $disk = $data['disk'] ?? 'public';

//         if (! empty($data['files']) && is_array($data['files'])) {
//             return array_map(function ($file) use ($disk) {
//                 if ($disk === 's3') {
//                     return $file;
//                 }
//                 $url = Storage::disk($disk)->url($file);

//                 return request()->getSchemeAndHttpHost().parse_url($url, PHP_URL_PATH);
//             }, $data['files']);
//         }

//         if (! empty($data['file'])) {
//             if ($disk === 's3') {
//                 return $data['file'];
//             }
//             $url = Storage::disk($disk)->url($data['file']);

//             return request()->getSchemeAndHttpHost().parse_url($url, PHP_URL_PATH);
//         }

//         return getFallbackImage($fallbackType);
//     }
// }

// if (! function_exists('getFallbackImage')) {
//     function getFallbackImage($type = 'default')
//     {
//         $randomNumber = rand(1, 30);

//         return match ($type) {
//             'avatar' => asset('assets/images/avatars/default.webp'),
//             default => asset('assets/images/avatars/default-fallback-image.png'),
//         };
//     }
// }


// if (! function_exists('formatTitleCase')) {
//     function formatTitleCase($keyWord)
//     {
//         return ucwords(str_replace('_', ' ', strtolower($keyWord)));
//     }
// }

// if (! function_exists('formatTime')) {
//     function formatTime($time, $format = null, $timezone = null)
//     {
//         if (! $time) {
//             return '-';
//         }

//         $tz = $timezone ?? optional(Auth::user())->time_zone ?? config('app.timezone');

//         $carbon = Carbon::parse($time)->setTimezone($tz);
//         $format = $format ?? globalSetting('time_format', 'base');

//         if ($format == 'humanDiff') {
//             return $carbon->diffForHumans();
//         }

//         return $carbon->format($format);
//     }
// }

// if (! function_exists('formatDate')) {
//     function formatDate($date, $format = null, $timezone = null)
//     {
//         if (! $date) {
//             return '-';
//         }
//         $tz = $timezone ?? Auth::user()->time_zone ?? config('app.timezone', 'UTC');
//         $carbon = Carbon::parse($date)->setTimezone($tz);
//         $hoursDiff = $carbon->diffInHours(now()->setTimezone($tz));
//         $format = $format ?? globalSetting('date_format', 'base');
//         if ($format == 'humanDiff') {
//             return $carbon->diffForHumans();
//         }

//         return $carbon->format($format);
//     }
// }

// if (! function_exists('formatDateTime')) {
//     function formatDateTime($date, $format = null, $timezone = null)
//     {
//         if (! $date) {
//             return '-';
//         }
//         $tz = $timezone ?? optional(Auth::user())->time_zone ?? config('app.timezone', 'UTC');
//         $carbon = Carbon::parse($date)->setTimezone($tz);
//         $hoursDiff = $carbon->diffInHours(now()->setTimezone($tz));

//         if ($format == 'humanDiff') {
//             return $carbon->diffForHumans();
//         }

//         if ($format === null) {
//             $dateFormat = globalSetting('date_format', 'base') ?: 'Y-m-d';
//             $timeFormat = globalSetting('time_format', 'base') ?: 'H:i:s';
//             $format = $dateFormat.' '.$timeFormat;
//         }

//         return $carbon->format($format);
//     }
// }

// if (! function_exists('hasPermission')) {
//     function hasPermission(string $permission, $user = null): bool
//     {
//         return in_array($permission, getUserPermission($user), true);
//     }
// }

// if (! function_exists('hasAnyPermission')) {
//     function hasAnyPermission(array $permissions, $user = null): bool
//     {
//         $userPermissions = getUserPermission($user);

//         return ! empty(array_intersect($permissions, $userPermissions));
//     }
// }

// if (! function_exists('hasAllPermissions')) {
//     function hasAllPermissions(array $permissions, $user = null): bool
//     {
//         $userPermissions = getUserPermission($user);

//         return empty(array_diff($permissions, $userPermissions));
//     }
// }

// if (! function_exists('getUserPermission')) {
//     function getUserPermission($user = null): array
//     {
//         $user = $user ?? Auth::user();

//         if (! $user) {
//             return [];
//         }

//         static $localCache = [];

//         if (! isset($localCache[$user->id])) {
//             $permissions = optional($user->permissions)->permissions ?? [];

//             if (! is_array($permissions)) {
//                 $permissions = json_decode($permissions ?: '[]', true);
//             }

//             $localCache[$user->id] = $permissions;
//         }

//         return $localCache[$user->id];
//     }
// }


// if (! function_exists('isRTL')) {
//     function isRTL(): bool
//     {
//         $locale = app()->getLocale();
//         $language = \App\Models\Language::where('code', $locale)->first();

//         return $language && $language->rtl == 1;
//     }
// }


// if (! function_exists('throwException')) {
//     function throwException($message, $code = 400)
//     {
//         throw new DepictableException($message, $code);
//     }
// }