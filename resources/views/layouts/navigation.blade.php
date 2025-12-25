{{-- resources/views/layouts/navigation.blade.php --}}
@php
    $locale  = app()->getLocale();
    $user    = auth()->user();
    $initial = $user
        ? mb_strtoupper(mb_substr($user->name ?: $user->email ?: 'U', 0, 1))
        : 'U';

    $isKm = $locale === 'km';
@endphp

<nav
  class="relative z-30 border-b border-white/40 dark:border-slate-800/70
         bg-gradient-to-b from-white/90 via-white/60 to-white/20
         dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/40
         backdrop-blur-2xl">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-3">

    {{-- Brand --}}
    <div class="flex items-center gap-3">
      <div
        class="flex h-9 w-9 items-center justify-center rounded-full
               bg-[conic-gradient(at_50%_50%,#f97316,#fb7185,#facc15,#a855f7,#f97316)]
               shadow-lg shadow-rose-300/60 text-xs font-semibold text-white">
        🍩
      </div>
      <div class="flex flex-col">
        <span class="text-sm font-semibold leading-tight">
          {{ __('messages.app_name', [], $locale) ?? config('app.name', 'Donuts POS') }}
        </span>
        <span class="text-[11px] text-slate-500 dark:text-slate-400">
          {{ __('messages.app_tagline', [], $locale) ?? 'Point of Sale for donut shops' }}
        </span>
      </div>
    </div>

    {{-- Right side: language, theme, user --}}
    <div class="flex items-center gap-3 text-xs">

      {{-- Language toggle (EN / KM switch) --}}
      <button
          type="button"
          id="langToggle"
          data-locale="{{ $locale }}"
          data-url-en="{{ route('locale.switch', 'en') }}"
          data-url-km="{{ route('locale.switch', 'km') }}"
          class="relative inline-flex items-center h-7 w-20 rounded-full border border-slate-300/70
                 dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70
                 text-[10px] overflow-hidden select-none cursor-pointer
                 shadow-sm hover:border-rose-300/80 dark:hover:border-rose-400/70
                 transition"
      >
          {{-- sliding pill --}}
          <span
              class="pointer-events-none absolute inset-y-0 left-0 w-1/2 rounded-full
                     bg-rose-500/85 dark:bg-rose-400/80 shadow-sm transform transition-transform
                     {{ $isKm ? 'translate-x-full' : 'translate-x-0' }}">
          </span>

          {{-- EN label --}}
          <span class="flex-1 text-center z-10
                       {{ $isKm ? 'text-slate-500 dark:text-slate-400' : 'text-white font-semibold' }}">
              EN
          </span>

          {{-- KM label --}}
          <span class="flex-1 text-center z-10
                       {{ $isKm ? 'text-white font-semibold' : 'text-slate-500 dark:text-slate-400' }}">
              ខ្មែរ
          </span>
      </button>

      {{-- Theme toggle --}}
      <button type="button" id="themeToggle"
              class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/70
                     dark:border-slate-700/70 bg-white/90 dark:bg-slate-950/70
                     px-2.5 py-1 text-[11px] text-slate-600 dark:text-slate-300
                     hover:border-rose-300/80 dark:hover:border-rose-400/70
                     transition">
        <span id="themeIcon"
              class="flex h-4 w-4 items-center justify-center rounded-full text-[10px]
                     bg-gradient-to-br from-amber-200 to-amber-500
                     dark:from-slate-900 dark:to-slate-700">
          ☾
        </span>
        <span id="themeLabel">
          {{ __('messages.theme_dark', [], $locale) ?? 'Dark' }}
        </span>
      </button>

      {{-- User menu --}}
      @if($user)
        <div class="flex items-center gap-2">
          <div
            class="flex h-8 w-8 items-center justify-center rounded-full
                   bg-rose-500/90 text-white text-xs font-semibold shadow-md shadow-rose-300/70">
            {{ $initial }}
          </div>
          <div class="hidden sm:flex flex-col leading-tight">
            <span class="text-[11px] font-medium text-slate-800 dark:text-slate-100 max-w-[140px] truncate">
              {{ $user->name ?? $user->email }}
            </span>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">
              {{ __('messages.app_role_label', [], $locale) ?? 'Staff user' }}
            </span>
          </div>

          <form id="logoutForm" method="POST" class="ml-1">
            @csrf
            <button type="submit"
              class="hidden sm:inline-flex items-center gap-1 rounded-full border border-slate-300/80
                     dark:border-slate-700/80 px-2 py-1 text-[11px]
                     text-slate-600 dark:text-slate-300
                     hover:border-rose-400 hover:text-rose-600
                     dark:hover:border-rose-400/80 dark:hover:text-rose-200
                     transition">
              <i class="bi bi-box-arrow-right text-[10px]"></i>
              <span>{{ __('messages.logout', [], $locale) ?? 'Logout' }}</span>
            </button>
          </form>
        </div>
      @endif
    </div>
  </div>
</nav>
