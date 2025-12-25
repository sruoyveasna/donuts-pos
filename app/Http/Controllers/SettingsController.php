<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $keys = [
            'app.name',
            'app.logo',
            'app.locale_default',
            'pos.tax.enabled',
            'pos.tax.rate',
            'pos.currency.default',
            'pos.currency.symbol',
            'pos.currency.exchange_usd',
            'bank_id',
            'pos.receipt.footer_note',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::getCached($key);
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],

            // 👇 note the escaped dots: app\.name, app\.locale_default, etc.
            'settings.app\.name'                   => ['nullable', 'string', 'max:100'],
            'settings.app\.locale_default'         => ['required', Rule::in(['en', 'km'])],

            'settings.pos\.tax\.enabled'           => ['required', 'in:true,false'],
            'settings.pos\.tax\.rate'              => ['required', 'numeric', 'min:0'],

            'settings.pos\.currency\.default'      => ['required', 'string', 'max:10'],
            'settings.pos\.currency\.symbol'       => ['required', 'string', 'max:10'],
            'settings.pos\.currency\.exchange_usd' => ['required', 'numeric', 'min:0'],

            'settings.bank_id'                     => ['nullable', 'string', 'max:191'],
            'settings.pos\.receipt\.footer_note'   => ['nullable', 'string', 'max:500'],

            // logo file
            'logo'                                 => ['nullable', 'image', 'max:2048'],
        ]);

        $settings = $validated['settings'];

        // Save all simple key/value settings
        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }

        // Save logo file if uploaded
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::setValue('app.logo', '/storage/'.$path);
        }

        return response()->json([
            'status'  => 'ok',
            'message' => 'Settings updated.',
        ]);
    }
}
