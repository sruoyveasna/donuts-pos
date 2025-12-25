<div
  class="group rounded-2xl overflow-hidden
         bg-white/70 dark:bg-slate-950/60
         border border-white/50 dark:border-slate-800/80
         shadow-sm shadow-indigo-200/20 dark:shadow-none
         hover:shadow-lg hover:shadow-indigo-200/35
         transition"
>
  <!-- Image -->
  <div class="relative aspect-[4/2.6] bg-slate-100 dark:bg-slate-800 overflow-hidden">
    <img class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" src="" alt="" />

    <!-- soft top gradient like modal header vibe -->
    <div class="pointer-events-none absolute inset-x-0 top-0 h-16
                bg-gradient-to-b from-black/25 to-transparent"></div>

    <!-- optional badge area (left empty for now) -->
    <div class="absolute left-2 top-2 flex gap-2"></div>
  </div>

  <!-- Body -->
  <div class="p-3">
    <!-- Name -->
    <div class="font-semibold text-slate-900 dark:text-white leading-snug line-clamp-1" data-name>
      Item name
    </div>

    <!-- Sub row -->
    <div class="mt-2 flex items-end justify-between gap-3">
      <!-- Left info -->
      <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
        <div>
          <span class="uppercase tracking-wide">Size:</span>
          <span class="ml-1 text-slate-800 dark:text-slate-200 font-semibold" data-size>M</span>
        </div>

        <div class="mt-1 text-sm font-extrabold text-rose-500 tabular-nums" data-price>
          $0.00
        </div>
      </div>

      <!-- Button -->
      <button
        data-add
        class="h-10 w-12 rounded-2xl grid place-items-center shrink-0
               bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400
               text-white shadow-md shadow-sky-300/60
               hover:shadow-sky-400/70
               active:scale-[0.98]
               transition
               focus:outline-none focus:ring-2 focus:ring-sky-300/60"
        aria-label="Add item"
      >
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>
  </div>

  <!-- Bottom glow line (subtle) -->
  <div class="h-px bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent dark:via-slate-700/40"></div>
</div>
