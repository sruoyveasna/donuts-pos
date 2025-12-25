<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // 1) from session (user just chose language)
        $locale = $request->session()->get('locale');

        // 2) or from settings table (your Setting model)
        if (! $locale) {
            $locale = Setting::getCached('app.locale_default', config('app.locale'));
        }

        // 3) fallback to config locale if still null
        if (! in_array($locale, ['en', 'km'])) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
