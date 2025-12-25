{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')

@section('title', __('messages.settings_title', [], app()->getLocale()) ?? 'Settings')

@section('content')
@php
    $locale     = app()->getLocale();
    $user       = auth()->user()?->load('profile');
    $settings   = $settings ?? [];

    $defLocale  = $settings['app.locale_default'] ?? 'en';
    $currentLocale = $locale;
@endphp

<div class="max-w-5xl mx-auto space-y-4">

    {{-- Page header + local language toggle --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div class="flex flex-col gap-1">
            <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
                {{ __('messages.settings_title', [], $locale) ?? 'Settings' }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
                {{ __('messages.settings_subtitle', [], $locale) ?? 'Manage your POS app appearance and your staff profile.' }}
            </p>
        </div>

        {{-- Language switch (page-level) --}}
        <div class="flex items-center gap-2 text-[11px] md:text-xs">
            <span class="hidden md:inline text-slate-500 dark:text-slate-400">
                {{ __('messages.settings_default_locale', [], $locale) ?? 'Default language' }}
            </span>

            <div class="flex items-center rounded-full border border-slate-300/70 dark:border-slate-700/70
                        bg-white/90 dark:bg-slate-950/70 px-1 py-0.5">
                <a href="{{ route('locale.switch', 'en') }}"
                   class="px-2 py-0.5 rounded-full transition
                          {{ $currentLocale === 'en'
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-200'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100' }}">
                    EN
                </a>
                <a href="{{ route('locale.switch', 'km') }}"
                   class="px-2 py-0.5 rounded-full transition
                          {{ $currentLocale === 'km'
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-200'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100' }}">
                    ខ្មែរ
                </a>
            </div>
        </div>
    </div>

    {{-- Card with tabs --}}
    <div class="rounded-2xl border border-white/50 dark:border-slate-800/80
                bg-white/80 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
                backdrop-blur-2xl">

        {{-- Tabs pills --}}
        <div class="px-4 pt-4 flex justify-center">
            <div class="inline-flex rounded-full bg-slate-900/40 border border-slate-800/80 p-1 text-xs md:text-[13px]">
                {{-- App tab --}}
                <button type="button"
                        id="tab-app-btn"
                        data-tab="app"
                        class="tab-btn inline-flex items-center gap-1 rounded-full px-3 md:px-4 py-1.5
                               bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                               text-white shadow-md shadow-rose-300/70">
                    <span>⚙️</span>
                    <span>{{ __('messages.settings_app_tab', [], $locale) ?? 'App settings' }}</span>
                </button>

                {{-- Profile tab --}}
                <button type="button"
                        id="tab-profile-btn"
                        data-tab="profile"
                        class="tab-btn inline-flex items-center gap-1 rounded-full px-3 md:px-4 py-1.5
                               bg-transparent text-slate-400 hover:text-slate-100">
                    <span>👤</span>
                    <span>{{ __('messages.settings_profile_tab', [], $locale) ?? 'Profile settings' }}</span>
                </button>
            </div>
        </div>

        {{-- Tabs content (READ ONLY) --}}
        <div class="px-4 md:px-6 pb-5 md:pb-6 pt-4 md:pt-5 space-y-6">

            {{-- APP SETTINGS TAB --}}
            <section id="tab-app" class="tab-section space-y-4">
                <div
                    class="rounded-2xl border border-slate-200/60 dark:border-slate-800/80
                           bg-slate-50/40 dark:bg-slate-900/60 px-4 md:px-5 py-4 md:py-5
                           shadow-inner shadow-black/10">

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        {{-- Logo + name --}}
                        <div class="flex items-center gap-3">
                            <div
                                class="h-14 w-14 rounded-2xl overflow-hidden border border-slate-200/70
                                       dark:border-slate-700/80 bg-slate-100 dark:bg-slate-900
                                       flex items-center justify-center shadow-md shadow-rose-200/40">
                                <img
                                    src="{{ $settings['app.logo'] ?? '/logo.png' }}"
                                    alt="Shop logo"
                                    class="h-full w-full object-cover"
                                    onerror="this.src='/logo.png';">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    {{ $settings['app.name'] ?? 'My POS' }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $defLocale === 'km' ? 'ខ្មែរ (Khmer)' : 'English' }}
                                </span>
                            </div>
                        </div>

                        {{-- Open edit modal --}}
                        <button type="button"
                                id="btnOpenEditApp"
                                class="inline-flex items-center gap-2 rounded-full
                                       bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                                       px-4 py-1.5 text-xs md:text-sm font-semibold text-white
                                       shadow-md shadow-rose-300/70 hover:shadow-rose-400/80
                                       transition">
                            <i class="bi bi-pencil-square text-[12px]"></i>
                            <span>{{ __('messages.settings_save_app', [], $locale) ?? 'Edit app settings' }}</span>
                        </button>
                    </div>

                    {{-- Details list --}}
                    <dl class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-[11px] md:text-xs">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.settings_tax_enabled', [], $locale) ?? 'Tax' }}
                            </dt>
                            <dd class="mt-0.5 font-medium text-slate-100">
                                {{ ($settings['pos.tax.enabled'] ?? 'true') === 'true'
                                    ? (($settings['pos.tax.rate'] ?? '10').'%')
                                    : __('messages.settings_tax_disabled', [], $locale) ?? 'Disabled' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.settings_currency_code', [], $locale) ?? 'Currency' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ ($settings['pos.currency.default'] ?? 'KHR').' ('.($settings['pos.currency.symbol'] ?? '៛').')' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.settings_exchange_rate', [], $locale) ?? '1 USD = ? KHR' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ '1 USD = '.($settings['pos.currency.exchange_usd'] ?? '4100').' KHR' }}
                            </dd>
                        </div>

                        <div class="md:col-span-3">
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.settings_bank_id', [], $locale) ?? 'Bank ID / eWallet' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ $settings['bank_id'] ?? '—' }}
                            </dd>
                        </div>

                        <div class="md:col-span-3">
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.settings_receipt_footer', [], $locale) ?? 'Receipt footer note' }}
                            </dt>
                            <dd class="mt-0.5 text-[11px] md:text-xs">
                                {{ $settings['pos.receipt.footer_note'] ?? 'Thank you for your purchase!' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- PROFILE SETTINGS TAB --}}
            <section id="tab-profile" class="tab-section hidden space-y-4">
                <div
                    class="rounded-2xl border border-slate-200/60 dark:border-slate-800/80
                           bg-slate-50/40 dark:bg-slate-900/60 px-4 md:px-5 py-4 md:py-5
                           shadow-inner shadow-black/10">

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-14 w-14 rounded-full overflow-hidden border border-slate-200/70
                                       dark:border-slate-700/80 bg-slate-100 dark:bg-slate-900
                                       flex items-center justify-center shadow-md shadow-emerald-200/60">
                                <img
                                    src="{{ $user?->profile?->avatar
                                            ? $user->profile->avatar
                                            : 'https://ui-avatars.com/api/?background=f97316&color=fff&name='.urlencode($user?->name ?? 'User') }}"
                                    alt="Avatar"
                                    class="h-full w-full object-cover">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold">
                                    {{ $user?->name ?? '—' }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $user?->email ?? '' }}
                                </span>
                            </div>
                        </div>

                        {{-- Open profile edit modal --}}
                        <button type="button"
                                id="btnOpenEditProfile"
                                class="inline-flex items-center gap-2 rounded-full
                                       bg-gradient-to-r from-emerald-500 via-teal-400 to-sky-400
                                       px-4 py-1.5 text-xs md:text-sm font-semibold text-white
                                       shadow-md shadow-emerald-300/70 hover:shadow-emerald-400/80
                                       transition">
                            <i class="bi bi-person-gear text-[12px]"></i>
                            <span>{{ __('messages.settings_save_profile', [], $locale) ?? 'Edit profile' }}</span>
                        </button>
                    </div>

                    <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] md:text-xs">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.profile_phone', [], $locale) ?? 'Phone' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ $user?->profile?->phone ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.profile_gender', [], $locale) ?? 'Gender' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ $user?->profile?->gender ?? __('messages.profile_gender_none', [], $locale) ?? 'Not specified' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.profile_birthdate', [], $locale) ?? 'Birthdate' }}
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ optional($user?->profile?->birthdate)->format('Y-m-d') ?? '—' }}
                            </dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-slate-500 dark:text-slate-400">
                                {{ __('messages.profile_address', [], $locale) ?? 'Address' }}
                            </dt>
                            <dd class="mt-0.5">
                                {{ $user?->profile?->address ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
        </div>
    </div>
</div>

{{-- Edit modals --}}
@include('settings.partials.edit-app')
@include('settings.partials.edit-profile')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ----- Tabs -----
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabApp     = document.getElementById('tab-app');
    const tabProfile = document.getElementById('tab-profile');

    function setActiveTab(name) {
        tabButtons.forEach(btn => {
            const isActive = btn.dataset.tab === name;

            // active styles
            btn.classList.toggle('bg-gradient-to-r',   isActive);
            btn.classList.toggle('from-rose-500',      isActive);
            btn.classList.toggle('via-rose-400',       isActive);
            btn.classList.toggle('to-orange-400',      isActive);
            btn.classList.toggle('text-white',         isActive);
            btn.classList.toggle('shadow-md',          isActive);
            btn.classList.toggle('shadow-rose-300/70', isActive);

            // inactive styles
            btn.classList.toggle('bg-transparent',       !isActive);
            btn.classList.toggle('text-slate-400',       !isActive);
            btn.classList.toggle('hover:text-slate-100', !isActive);
        });

        if (name === 'app') {
            tabApp?.classList.remove('hidden');
            tabProfile?.classList.add('hidden');
        } else {
            tabProfile?.classList.remove('hidden');
            tabApp?.classList.add('hidden');
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => setActiveTab(btn.dataset.tab));
    });

    setActiveTab('app');

    // ----- Open dialogs -----
    const dlgApp     = document.getElementById('editAppDialog');
    const dlgProfile = document.getElementById('editProfileDialog');
    const btnApp     = document.getElementById('btnOpenEditApp');
    const btnProfile = document.getElementById('btnOpenEditProfile');

    if (btnApp && dlgApp && window.openDialog) {
        btnApp.addEventListener('click', () => openDialog(dlgApp));
    }
    if (btnProfile && dlgProfile && window.openDialog) {
        btnProfile.addEventListener('click', () => openDialog(dlgProfile));
    }
});
</script>
@endpush
