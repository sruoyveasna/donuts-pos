<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    /**
     * Switch the UI language (EN / KM).
     * Works for Blade + API (returns JSON if requested).
     */
    public function __invoke(string $locale, Request $request)
    {
        // Only allow the languages you support
        if (! in_array($locale, ['en', 'km'])) {
            $locale = config('app.locale', 'en');
        }

        // Save to session so SetLocale middleware can use it next requests
        $request->session()->put('locale', $locale);

        // Also set immediately for this current request
        App::setLocale($locale);

        // If request expects JSON (API / AJAX)
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'locale'  => $locale,
            ]);
        }

        // For normal Blade links: just go back to previous page
        return back();
    }
}
