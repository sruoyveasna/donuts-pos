{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', __('messages.login_title', [], app()->getLocale()) ?? 'Login')

@push('head')
<style>
    @keyframes float {
        from { transform: translate3d(0, 0, 0) scale(1); }
        to   { transform: translate3d(25px, -18px, 0) scale(1.05); }
    }
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0 1000px transparent inset;
        -webkit-text-fill-color: inherit;
        background-color: transparent !important;
        transition: background-color 5000s ease-in-out 0s;
    }
</style>
@endpush

@section('content')
@php
    $locale = app()->getLocale();
@endphp

<div class="relative min-h-screen flex flex-col
    bg-[radial-gradient(circle_at_top,_#ffe4e6_0,_#fef3c7_40%,_#fdf2ff_100%)]
    dark:bg-[radial-gradient(circle_at_top,_#4c1d95_0,_#111827_55%,_#020617_100%)]
    text-slate-900 dark:text-slate-100">

    {{-- Donut blobs --}}
    <div class="pointer-events-none fixed -top-20 -left-10 h-80 w-80 rounded-full
                bg-[#fb7185] opacity-40 blur-3xl
                animate-[float_16s_ease-in-out_infinite_alternate]"></div>

    <div class="pointer-events-none fixed -bottom-20 -right-10 h-64 w-64 rounded-full
                bg-[#facc15] opacity-40 blur-3xl
                animate-[float_18s_ease-in-out_infinite_alternate-reverse]"></div>

    <div class="pointer-events-none fixed top-1/3 right-10 h-56 w-56 rounded-full
                bg-[#a855f7] opacity-40 blur-3xl
                animate-[float_22s_ease-in-out_infinite_alternate]"></div>

    {{-- TOPBAR --}}
    <header class="sticky top-0 z-20 border-b border-white/40 dark:border-slate-700/70
                   bg-gradient-to-b from-white/80 via-white/40 to-transparent
                   dark:from-slate-950/90 dark:via-slate-900/60 dark:to-transparent
                   backdrop-blur-xl">
        <div class="max-w-5xl mx-auto px-4 py-2.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full
                            bg-[conic-gradient(at_50%_50%,#f97316,#fb7185,#facc15,#a855f7,#f97316)]
                            shadow-lg shadow-rose-300/60 text-white font-semibold text-xs">
                    🍩
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-semibold leading-tight">
                        {{ __('messages.app_name', [], $locale) ?? 'NovaPOS Donuts' }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('messages.pos_login', [], $locale) ?? 'POS Login' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                @php $currentLocale = app()->getLocale(); @endphp
                <div class="flex items-center rounded-full border border-slate-300/70 dark:border-slate-700/70
                            bg-white/90 dark:bg-slate-950/70 px-1 py-0.5">
                    <a href="{{ route('locale.switch', 'en') }}"
                       class="px-2 py-0.5 rounded-full transition
                              {{ $currentLocale === 'en'
                                    ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'
                                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100' }}">
                        EN
                    </a>
                    <a href="{{ route('locale.switch', 'km') }}"
                       class="px-2 py-0.5 rounded-full transition
                              {{ $currentLocale === 'km'
                                    ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'
                                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100' }}">
                        ខ្មែរ
                    </a>
                </div>

                <button type="button" id="themeToggle"
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/70
                               dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70
                               px-2 py-1 text-[11px] text-slate-600 dark:text-slate-300
                               hover:border-rose-300/80 dark:hover:border-rose-400/70
                               transition">
                    <span id="themeIcon"
                          class="flex h-4 w-4 items-center justify-center rounded-full text-[10px]
                                 bg-gradient-to-br from-amber-200 to-amber-500
                                 dark:from-slate-900 dark:to-slate-700">
                        ☾
                    </span>
                    <span id="themeLabel">{{ __('messages.theme_dark', [], $locale) ?? 'Dark' }}</span>
                </button>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="flex-1 flex items-center justify-center px-4 py-6 md:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-5xl grid grid-cols-1 md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]
                    gap-6 md:gap-8 items-center">

            {{-- Left intro (hidden on mobile) --}}
            <section class="hidden md:block space-y-4">
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight leading-snug">
                    {{ __('messages.login_headline_prefix', [], $locale) ?? 'Fast, simple' }}
                    <span class="bg-gradient-to-r from-rose-500 via-orange-400 to-amber-400 bg-clip-text text-transparent">
                        {{ __('messages.login_headline_highlight', [], $locale) ?? 'cashier login' }}
                    </span>
                    {{ __('messages.login_headline_suffix', [], $locale) ?? 'for your donut shop.' }}
                </h1>

                <p class="text-sm text-slate-600 dark:text-slate-300 max-w-md">
                    {{ __('messages.login_intro', [], $locale)
                        ?? 'Switch shifts in seconds. Track sales, print receipts, and keep your team focused on serving customers — not fighting with the system.' }}
                </p>

                <div class="flex flex-wrap gap-2 text-[11px] text-slate-600 dark:text-slate-300">
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/70
                               dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70 px-3 py-1">
                        <span>⚡</span>
                        <span>{{ __('messages.login_pill_fast', [], $locale) ?? 'Under 5 seconds to log in' }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/70
                               dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70 px-3 py-1">
                        <span>👩‍💼</span>
                        <span>{{ __('messages.login_pill_roles', [], $locale) ?? 'Cashier & admin roles' }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/70
                               dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70 px-3 py-1">
                        <span>🍩</span>
                        <span>{{ __('messages.login_pill_donuts', [], $locale) ?? 'Optimized for donut shops' }}</span>
                    </div>
                </div>
            </section>

            {{-- Login card --}}
            <section class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70
                            bg-white/90 dark:bg-slate-900/95 shadow-2xl backdrop-blur-2xl
                            px-6 py-7 w-full max-w-md mx-auto">

                <div class="mb-4">
                    <h2 class="text-sm font-semibold">
                        {{ __('messages.login_title_card', [], $locale) ?? 'Sign in to continue' }}
                    </h2>
                    <p class="mt-1 text-[12px] text-slate-500 dark:text-slate-400" id="login-subtitle">
                        {{ __('messages.login_subtitle', [], $locale) ?? 'Use your staff account to access the POS.' }}
                    </p>
                </div>

                {{-- error box --}}
                <div id="err"
                     class="hidden mb-3 rounded-lg border border-red-400/70 bg-red-50/90 text-xs text-red-700
                            px-3 py-2 dark:bg-red-900/40 dark:text-red-100 dark:border-red-500/70">
                </div>

                <form id="loginForm" class="space-y-4" autocomplete="on" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="space-y-1.5">
                        <label for="email" class="text-xs font-medium text-slate-600 dark:text-slate-300">
                            {{ __('messages.login_email_label', [], $locale) ?? 'Username or Email' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200/70 dark:border-slate-700/70
                                   bg-slate-50/80 dark:bg-slate-900 px-3 py-2
                                   focus-within:border-rose-400 focus-within:ring-1 focus-within:ring-rose-300
                                   dark:focus-within:border-rose-400/80">
                            <span class="text-sm opacity-80">👤</span>
                            <input id="email" name="email" type="email" inputmode="email" autocomplete="username" required
                                   class="flex-1 appearance-none bg-transparent border-none outline-none text-sm
                                          text-slate-900 dark:text-slate-50 placeholder:text-slate-400
                                          dark:placeholder:text-slate-500"
                                   placeholder="cashier@example.com">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="space-y-1.5">
                        <label for="password" class="text-xs font-medium text-slate-600 dark:text-slate-300">
                            {{ __('messages.login_password_label', [], $locale) ?? 'Password' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200/70 dark:border-slate-700/70
                                   bg-slate-50/80 dark:bg-slate-900 px-3 py-2
                                   focus-within:border-rose-400 focus-within:ring-1 focus-within:ring-rose-300
                                   dark:focus-within:border-rose-400/80">
                            <span class="text-sm opacity-80">🔒</span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="flex-1 appearance-none bg-transparent border-none outline-none text-sm
                                          text-slate-900 dark:text-slate-50 placeholder:text-slate-400
                                          dark:placeholder:text-slate-500"
                                   placeholder="••••••••">
                        </div>

                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                            <label class="inline-flex items-center gap-1 cursor-pointer">
                                <input id="remember" name="remember" type="checkbox"
                                       class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/60">
                                <span>{{ __('messages.login_remember', [], $locale) ?? 'Remember this device' }}</span>
                            </label>
                            <button type="button"
                                    class="text-rose-500 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200">
                                {{ __('messages.login_forgot', [], $locale) ?? 'Forgot password?' }}
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button id="submitBtn" type="submit"
                            class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-full
                                   bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                                   px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-300/50
                                   hover:shadow-rose-400/60 transition">
                        <span class="btn-label flex items-center gap-1">
                            <i class="bi bi-box-arrow-in-right text-xs"></i>
                            <span>{{ __('messages.login_button', [], $locale) ?? 'Sign in' }}</span>
                        </span>
                        <span class="btn-busy hidden items-center gap-2 text-xs">
                            <span class="h-4 w-4 rounded-full border border-white/30 border-t-transparent animate-spin"></span>
                            <span>{{ __('messages.login_busy', [], $locale) ?? 'Signing in…' }}</span>
                        </span>
                    </button>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 flex-wrap gap-2">
                        <span>
                            {{ __('messages.login_demo', [], $locale) ?? 'Cashier demo:' }}
                            <span class="text-rose-500 dark:text-rose-300">cashier@example.com</span>
                        </span>
                        <span class="rounded-full border border-slate-200/70 dark:border-slate-700/70
                                     px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {{ __('messages.login_badge_roles', [], $locale) ?? 'Role-based access' }}
                        </span>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <footer class="text-center text-[11px] text-slate-500 dark:text-slate-400 pb-4 pt-1">
        {{ __('messages.app_footer', [], $locale) ?? 'NovaPOS Donuts' }} ·
        <span id="year"></span> ·
        {{ __('messages.all_rights', [], $locale) ?? 'All rights reserved.' }}
    </footer>
</div>
@endsection

@push('scripts')
<script>
    const TOKEN_KEY = 'donuts_token';

    function getCookie(name){
        const m = document.cookie.match(new RegExp('(^| )'+name+'=([^;]+)'));
        return m ? decodeURIComponent(m[2]) : '';
    }
    async function ensureXsrfCookie() {
        if (!getCookie('XSRF-TOKEN')) {
            await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        }
    }
    function setBusy(on) {
        const btn = document.getElementById('submitBtn');
        btn.disabled = on;
        btn.querySelector('.btn-label').classList.toggle('hidden', on);
        btn.querySelector('.btn-busy').classList.toggle('hidden', !on);
    }
    function showError(msg) {
        const el = document.getElementById('err');
        el.textContent = msg || 'Something went wrong';
        el.classList.remove('hidden');
    }
    function clearError() {
        const el = document.getElementById('err');
        el.classList.add('hidden');
        el.textContent = '';
    }

    // ✅ Redirect map by role_id
    function redirectByRoleId(roleId) {
        roleId = Number(roleId || 0);

        // You asked: role_id 4 -> /customer
        if (roleId === 4) return '/customers';

        // Keep default behavior for others
        return '/dashboard';
    }

    // Submit login
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();
        setBusy(true);

        const body = {
            email: e.target.email.value.trim(),
            password: e.target.password.value,
            remember: e.target.remember.checked
        };

        try {
            await ensureXsrfCookie();
            const xsrf = getCookie('XSRF-TOKEN');

            const res = await fetch('/api/auth/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrf
                },
                body: JSON.stringify(body)
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const msg = data?.message
                    || (data?.errors ? Object.values(data.errors).flat().join(' ') : `HTTP ${res.status}`);
                showError(msg);
                setBusy(false);
                return;
            }

            if (data.token) {
                try { localStorage.setItem(TOKEN_KEY, data.token); } catch {}
            }

            // ✅ role-based redirect (reads role_id from API response)
            const roleId = data?.user?.role_id ?? data?.role_id ?? 0;
            window.location.href = redirectByRoleId(roleId);

        } catch (err) {
            console.error(err);
            showError('Network error. Please try again.');
            setBusy(false);
        }
    });

    // THEME TOGGLE
    const htmlEl      = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');
    const themeLabel  = document.getElementById('themeLabel');

    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlEl.classList.add('dark');
            themeIcon.textContent = '☾';
            themeLabel.textContent = '{{ __('messages.theme_dark', [], $locale) ?? 'Dark' }}';
        } else {
            htmlEl.classList.remove('dark');
            themeIcon.textContent = '☀';
            themeLabel.textContent = '{{ __('messages.theme_light', [], $locale) ?? 'Light' }}';
        }
        localStorage.setItem('pos-theme', theme);
    }

    const savedTheme = localStorage.getItem('pos-theme') || 'dark';
    applyTheme(savedTheme);

    themeToggle.addEventListener('click', () => {
        const current = htmlEl.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });

    // Year
    document.getElementById('year').textContent = new Date().getFullYear();
</script>
@endpush
