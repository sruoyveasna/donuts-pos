<section data-screen="home" class="screen">
  <div class="flex items-center gap-2">
    <div class="flex-1">
      <div class="relative">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input id="searchInput"
               class="w-full h-11 pl-10 pr-3 rounded-xl border border-slate-200 dark:border-slate-800
                      bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-50
                      placeholder:text-slate-400 outline-none"
               placeholder="{{ __('messages.search') ?? 'Search' }}">
      </div>
    </div>

    <button type="button"
            class="w-11 h-11 rounded-xl border border-slate-200 dark:border-slate-800
                   bg-white dark:bg-slate-900 shadow-sm grid place-items-center"
            aria-label="Filter">
      <i class="bi bi-sliders2 text-lg"></i>
    </button>
  </div>

  {{-- categories --}}
  <div class="mt-3 overflow-x-auto no-scrollbar">
    <div id="catPills" class="inline-flex gap-2 pb-1"></div>
  </div>

  {{-- banner --}}
  <div class="mt-3 rounded-2xl overflow-hidden border border-white/70 dark:border-slate-800 shadow-sm">
    <div class="h-28 bg-gradient-to-r from-indigo-100 via-sky-100 to-purple-100
                dark:from-indigo-950 dark:via-slate-900 dark:to-purple-950"></div>
  </div>

  <div class="mt-4 flex items-center justify-between">
    <p class="text-[13px] font-extrabold">{{ __('messages.customer_home_title') ?? 'Product listing' }}</p>
    <button class="text-[12px] font-bold text-slate-500 dark:text-slate-300" type="button">
      {{ __('messages.see_all') ?? 'See all' }}
    </button>
  </div>

  <div id="productsGrid" class="mt-3 grid grid-cols-2 gap-3"></div>

  <div id="productsEmpty" class="hidden mt-8 text-center text-[13px] text-slate-500 dark:text-slate-300">
    {{ __('messages.no_items_found') ?? 'No items found.' }}
  </div>
</section>
