<div class="rounded-2xl p-3
            bg-white/70 dark:bg-slate-900/60
            border border-white/40 dark:border-slate-800/80">

  <div class="flex items-start justify-between gap-2">
    <div class="min-w-0">
      <div class="font-medium leading-snug truncate" data-ci-name>—</div>
      <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" data-ci-meta>—</div>

      <div class="mt-2 flex flex-wrap gap-1" data-ci-tags></div>

      <div class="hidden mt-1 text-xs text-slate-500 dark:text-slate-400" data-ci-note></div>
    </div>

    <button type="button" class="text-slate-400 hover:text-rose-500" data-ci-rm>
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="mt-3 flex items-center justify-between gap-2">
    <div class="flex items-center gap-2">
      <button type="button" class="h-9 w-9 rounded-xl border border-white/40 dark:border-slate-800/80
                                   bg-white/70 dark:bg-slate-900/60 grid place-items-center"
              data-ci-dec>
        <i class="bi bi-dash-lg"></i>
      </button>

      <input type="number" min="1"
             class="h-9 w-14 text-center rounded-xl border border-white/40 dark:border-slate-800/80
                    bg-white/70 dark:bg-slate-900/60 focus:outline-none"
             data-ci-qty />

      <button type="button" class="h-9 w-9 rounded-xl border border-white/40 dark:border-slate-800/80
                                   bg-white/70 dark:bg-slate-900/60 grid place-items-center"
              data-ci-inc>
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>

    <div class="flex items-center gap-2">
      <button type="button"
              class="h-9 px-3 rounded-xl border border-white/40 dark:border-slate-800/80
                     bg-white/70 dark:bg-slate-900/60 hover:bg-white/90 dark:hover:bg-slate-900 text-sm"
              data-ci-edit>
        Edit
      </button>

      <div class="font-semibold tabular-nums" data-ci-line>$0.00</div>
    </div>
  </div>
</div>
