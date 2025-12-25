<section data-screen="profile" class="screen hidden">
  <div class="flex items-center justify-between px-1">
    <div></div>
    <button id="btnLogoutDemo" type="button" class="text-[12px] font-bold text-slate-500 dark:text-slate-300">
      {{ __('messages.logout') ?? 'Log out' }}
    </button>
  </div>

  <div class="mt-3 flex flex-col items-center gap-2">
    <div class="relative">
      <div class="w-20 h-20 rounded-full bg-slate-200 dark:bg-slate-800 border-4 border-white dark:border-slate-950
                  shadow-sm grid place-items-center">
        <i class="bi bi-person-fill text-2xl text-slate-500 dark:text-slate-300"></i>
      </div>
      <div class="absolute -right-1 -bottom-1 w-8 h-8 rounded-full bg-indigo-600 text-white border-4
                  border-white dark:border-slate-950 grid place-items-center">
        <i class="bi bi-pencil-fill text-[12px]"></i>
      </div>
    </div>
  </div>

  <div class="mt-4 space-y-3">
    <div>
      <p class="text-[12px] text-slate-500 dark:text-slate-300 font-semibold mb-1">{{ __('messages.name') ?? 'Name' }}</p>
      <input id="profileName"
             class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 outline-none"
             value="">
    </div>

    <div>
      <p class="text-[12px] text-slate-500 dark:text-slate-300 font-semibold mb-1">{{ __('messages.phone') ?? 'Phone' }}</p>
      <input id="profilePhone"
             class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 outline-none"
             value="">
    </div>

    <div>
      <p class="text-[12px] text-slate-500 dark:text-slate-300 font-semibold mb-1">{{ __('messages.email') ?? 'Email' }}</p>
      <input id="profileEmail"
             class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 outline-none"
             value="">
    </div>

    <button id="btnSaveProfile"
            type="button"
            class="w-full h-11 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-extrabold
                   active:scale-[.99] transition">
      {{ __('messages.save') ?? 'Save' }}
    </button>
  </div>
</section>
