<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale) {
            $locale = globalSetting('default_language', 'en');
        }

        app()->setLocale($locale);

        // Apply app timezone at runtime if configured
        $tz = globalSetting('timezone') ?? globalSetting('time_zone');
        if ($tz) {
            date_default_timezone_set($tz);
            config(['app.timezone' => $tz]);
        }

        return $next($request);
    }
}
