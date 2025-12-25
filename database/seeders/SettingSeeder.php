<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Basic app info ─────────────────────────────────────────────
            'app.name'           => 'My POS',
            'app.logo'           => '/storage/logo/app-logo.png',
            'app.locale_default' => 'en',

            // ── Font (Khmer-first, used for both km + en) ─────────────────
            // Pick a Khmer font that looks good for Khmer + English
            'ui.font.family_base' => '"Kantumruy Pro", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',

            // ── Theme (simple: mode + primary accent color) ───────────────
            'ui.theme.mode'      => 'dark',      // "dark" or "light"
            'ui.theme.primary'   => '#6366f1',   // main accent color (blue/indigo)

            // ── Tax ───────────────────────────────────────────────────────
            'pos.tax.enabled'    => 'true',
            'pos.tax.rate'       => '10',        // 10%

            // ── Currency / exchange ──────────────────────────────────────
            'pos.currency.default'      => 'KHR',
            'pos.currency.symbol'       => '៛',
            'pos.currency.exchange_usd' => '4100', // 1 USD = 4100 KHR
            'bank_id' => 'veasna_sruoy@wing',

            // ── Receipt ──────────────────────────────────────────────────
            'pos.receipt.footer_note'   => 'Thank you for your purchase!',
        ];

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value); // uses your cached setter
        }
    }
}
