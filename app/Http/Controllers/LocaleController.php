<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch application locale.
     */
    public function changeLocale(Request $request, string $locale): RedirectResponse
    {
        $language = Language::where('code', $locale)->where('status', 'active')->first();

        if ($language) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
