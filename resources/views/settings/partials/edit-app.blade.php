{{-- resources/views/settings/partials/edit-app.blade.php --}}
@php
    $locale     = app()->getLocale();
    $settings   = $settings ?? [];
    $defLocale  = $settings['app.locale_default'] ?? 'en';
@endphp

<dialog id="editAppDialog">
    <div class="bg-slate-900/95 dark:bg-slate-950 text-slate-50 rounded-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 md:px-5 py-3 border-b border-slate-800/80">
            <div>
                <h2 class="text-sm md:text-base font-semibold">
                    {{ __('messages.settings_app_tab', [], $locale) ?? 'App settings' }}
                </h2>
                <p class="text-[11px] text-slate-400">
                    {{ __('messages.settings_edit_app_help', [], $locale) ?? 'Update logo, language, tax and currency.' }}
                </p>
            </div>

            <button type="button"
                    data-close
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full
                           text-slate-400 hover:text-slate-100 hover:bg-slate-800/80 transition">
                <i class="bi bi-x-lg text-[11px]"></i>
            </button>
        </div>

        {{-- Error banner --}}
        <div id="appSettingsError"
             class="hidden mx-4 mt-3 rounded-lg border border-red-500/70 bg-red-900/40
                    text-[11px] text-red-100 px-3 py-2">
        </div>

        {{-- Form --}}
        <form id="appSettingsForm"
              class="px-4 md:px-5 py-4 md:py-5 space-y-5"
              enctype="multipart/form-data">
            @csrf

            {{-- Logo + name / locale --}}
            <div class="flex flex-col md:flex-row md:items-center md:gap-6 gap-4">
                {{-- Logo picker --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="relative">
                        <div
                            class="h-16 w-16 rounded-2xl overflow-hidden border border-slate-700
                                   bg-slate-900 flex items-center justify-center shadow-md shadow-rose-900/60">
                            <img
                                id="shopLogoPreview"
                                src="{{ $settings['app.logo'] ?? '/logo.png' }}"
                                alt="Shop logo"
                                class="h-full w-full object-cover"
                                onerror="this.src='/logo.png';">
                        </div>
                        <button type="button"
                                id="shopLogoTrigger"
                                class="absolute -bottom-2 -right-2 rounded-full bg-rose-500 text-white
                                       border border-white/80 shadow-md shadow-rose-500/70 w-7 h-7
                                       flex items-center justify-center text-xs">
                            <i class="bi bi-camera-fill"></i>
                        </button>
                    </div>
                    <input
                        id="shopLogoInput"
                        name="logo"
                        type="file"
                        accept="image/*"
                        class="hidden">
                    <p class="text-[11px] text-slate-400">
                        {{ __('messages.settings_logo_hint', [], $locale) ?? 'PNG/JPG, up to 2 MB' }}
                    </p>
                </div>

                {{-- Name + locale --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.settings_shop_name', [], $locale) ?? 'Shop name' }}
                        </label>
                        <input
                            type="text"
                            name="shop_name"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm
                                   text-slate-50 placeholder:text-slate-500
                                   focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                            value="{{ $settings['app.name'] ?? 'My POS' }}"
                            placeholder="NovaPOS Donuts">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.settings_default_locale', [], $locale) ?? 'Default language' }}
                        </label>
                        <select
                            name="locale_default"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm
                                   text-slate-50 focus:outline-none
                                   focus:ring-1 focus:ring-rose-400 focus:border-rose-400">
                            <option value="en" {{ $defLocale === 'en' ? 'selected' : '' }}>English</option>
                            <option value="km" {{ $defLocale === 'km' ? 'selected' : '' }}>ខ្មែរ</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tax & bank --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-200">
                        <input type="checkbox"
                               name="tax_enabled"
                               class="h-3 w-3 rounded border-slate-600 text-rose-400 focus:ring-rose-400/70"
                               {{ ($settings['pos.tax.enabled'] ?? 'true') === 'true' ? 'checked' : '' }}>
                        <span>{{ __('messages.settings_tax_enabled', [], $locale) ?? 'Enable tax' }}</span>
                    </label>
                    <p class="text-[11px] text-slate-400">
                        {{ __('messages.settings_tax_hint', [], $locale) ?? 'Applied on each receipt.' }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-200">
                        {{ __('messages.settings_tax_rate', [], $locale) ?? 'Tax rate (%)' }}
                    </label>
                    <input
                        type="number" step="0.01"
                        name="tax_rate"
                        class="w-full rounded-xl border border-slate-700
                               bg-slate-900 px-3 py-2 text-sm text-slate-50
                               focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                        value="{{ $settings['pos.tax.rate'] ?? '10' }}">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-200">
                        {{ __('messages.settings_bank_id', [], $locale) ?? 'Bank ID / eWallet' }}
                    </label>
                    <input
                        type="text"
                        name="bank_id"
                        class="w-full rounded-xl border border-slate-700
                               bg-slate-900 px-3 py-2 text-sm text-slate-50
                               placeholder:text-slate-500
                               focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                        value="{{ $settings['bank_id'] ?? '' }}"
                        placeholder="veasna_sruoy@wing">
                </div>
            </div>

            {{-- Currency --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-200">
                        {{ __('messages.settings_currency_code', [], $locale) ?? 'Default currency' }}
                    </label>
                    <input
                        type="text"
                        name="currency_default"
                        class="w-full rounded-xl border border-slate-700
                               bg-slate-900 px-3 py-2 text-sm text-slate-50
                               focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                        value="{{ $settings['pos.currency.default'] ?? 'KHR' }}">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-200">
                        {{ __('messages.settings_currency_symbol', [], $locale) ?? 'Currency symbol' }}
                    </label>
                    <input
                        type="text"
                        name="currency_symbol"
                        class="w-full rounded-xl border border-slate-700
                               bg-slate-900 px-3 py-2 text-sm text-slate-50
                               focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                        value="{{ $settings['pos.currency.symbol'] ?? '៛' }}">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-200">
                        {{ __('messages.settings_exchange_rate', [], $locale) ?? '1 USD = ? KHR' }}
                    </label>
                    <input
                        type="number" step="1" min="0"
                        name="exchange_usd"
                        class="w-full rounded-xl border border-slate-700
                               bg-slate-900 px-3 py-2 text-sm text-slate-50
                               focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                        value="{{ $settings['pos.currency.exchange_usd'] ?? '4100' }}">
                </div>
            </div>

            {{-- Receipt footer --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-200">
                    {{ __('messages.settings_receipt_footer', [], $locale) ?? 'Receipt footer note' }}
                </label>
                <textarea
                    name="receipt_footer"
                    rows="2"
                    class="w-full rounded-xl border border-slate-700
                           bg-slate-900 px-3 py-2 text-sm text-slate-50
                           placeholder:text-slate-500
                           focus:outline-none focus:ring-1 focus:ring-rose-400 focus:border-rose-400"
                    placeholder="Thank you for your purchase!">{{ trim($settings['pos.receipt.footer_note'] ?? 'Thank you for your purchase!') }}</textarea>
            </div>

            {{-- Footer buttons --}}
            <div class="flex justify-between items-center pt-3">
                <button type="button"
                        data-close
                        class="text-[11px] md:text-xs text-slate-400 hover:text-slate-100">
                    {{ __('messages.cancel', [], $locale) ?? 'Cancel' }}
                </button>

                <button type="submit"
                        id="btnSaveApp"
                        class="inline-flex items-center gap-2 rounded-full
                               bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                               px-4 py-2 text-xs md:text-sm font-semibold text-white
                               shadow-md shadow-rose-400/70 hover:shadow-rose-500/80
                               disabled:opacity-60 disabled:cursor-not-allowed
                               transition">
                    <span class="spinner hidden h-4 w-4 rounded-full border border-white/40 border-t-transparent animate-spin"></span>
                    <span class="label inline-flex items-center gap-1">
                        <i class="bi bi-save2 text-[12px]"></i>
                        <span>{{ __('messages.settings_save_app', [], $locale) ?? 'Save app settings' }}</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // avoid double wiring if this partial is included twice for some reason
    if (window.__appSettingsWired) return;
    window.__appSettingsWired = true;

    const form     = document.getElementById('appSettingsForm');
    const errorBox = document.getElementById('appSettingsError');
    const btnSave  = document.getElementById('btnSaveApp');

    // Logo preview
    const logoInput   = document.getElementById('shopLogoInput');
    const logoPreview = document.getElementById('shopLogoPreview');
    const logoTrigger = document.getElementById('shopLogoTrigger');

    if (logoTrigger && logoInput && logoPreview) {
        logoTrigger.addEventListener('click', () => logoInput.click());
        logoInput.addEventListener('change', () => {
            const file = logoInput.files[0];
            if (file) {
                logoPreview.src = URL.createObjectURL(file);
            }
        });
    }

    function setBusy(on) {
        if (!btnSave) return;
        btnSave.disabled = on;
        const spinner = btnSave.querySelector('.spinner');
        const label   = btnSave.querySelector('.label');
        if (spinner) spinner.classList.toggle('hidden', !on);
        if (label)   label.classList.toggle('hidden', on);
    }

    function showError(msg) {
        if (!errorBox) return;
        errorBox.textContent = msg || 'Something went wrong';
        errorBox.classList.remove('hidden');
    }

    function clearError() {
        if (!errorBox) return;
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError();
            setBusy(true);

            const fd = new FormData(form);

            // map simple fields to settings[...] expected by API
            fd.set('settings[app.name]',                 form.shop_name.value.trim());
            fd.set('settings[app.locale_default]',       form.locale_default.value);
            fd.set('settings[pos.tax.enabled]',          form.tax_enabled.checked ? 'true' : 'false');
            fd.set('settings[pos.tax.rate]',             form.tax_rate.value || '0');
            fd.set('settings[pos.currency.default]',     form.currency_default.value || 'KHR');
            fd.set('settings[pos.currency.symbol]',      form.currency_symbol.value || '៛');
            fd.set('settings[pos.currency.exchange_usd]',form.exchange_usd.value || '4100');
            fd.set('settings[bank_id]',                  form.bank_id.value || '');
            fd.set('settings[pos.receipt.footer_note]',  form.receipt_footer.value.trim());

            try {
                await api('/api/settings', {
                    method: 'POST',
                    body: fd,
                });
                if (typeof showToast === 'function') {
                    showToast('App settings updated.', 'success');
                }
                // refresh read-only values
                window.location.reload();
            } catch (err) {
                console.error(err);
                let msg = err?.message || 'Failed to save settings';
                if (err?.data?.errors) {
                    const parts = [];
                    Object.values(err.data.errors).forEach(arr => {
                        (arr || []).forEach(t => parts.push(String(t)));
                    });
                    if (parts.length) msg = parts.join(' ');
                }
                showError(msg);
                if (typeof showToast === 'function') {
                    showToast(msg, 'danger');
                }
            } finally {
                setBusy(false);
            }
        });
    }
});
</script>
@endpush
