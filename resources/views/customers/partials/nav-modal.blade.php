<nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[420px]
            h-[72px] bg-white/95 dark:bg-slate-950/95 backdrop-blur
            border-t border-slate-200 dark:border-slate-800 px-3"
     style="padding-bottom: env(safe-area-inset-bottom);">
  <div class="h-full flex items-center justify-around">
    <button class="navbtn is-active flex flex-col items-center gap-1 text-[11px] font-bold
                   text-slate-500 dark:text-slate-300"
            data-go="home" type="button">
      <span class="w-9 h-9 rounded-xl grid place-items-center text-lg">
        <i class="bi bi-house-door"></i>
      </span>
      <span>{{ __('messages.home') ?? 'Home' }}</span>
    </button>

    <button class="navbtn flex flex-col items-center gap-1 text-[11px] font-bold
                   text-slate-500 dark:text-slate-300 relative"
            data-go="cart" type="button">
      <span class="w-9 h-9 rounded-xl grid place-items-center text-lg">
        <i class="bi bi-cart3"></i>
      </span>
      <span>{{ __('messages.cart') ?? 'Cart' }}</span>

      <span id="navCartBadge"
            class="absolute top-0 right-3 min-w-[18px] h-[18px] px-1 rounded-full text-[11px] font-extrabold
                   bg-indigo-600 text-white grid place-items-center">0</span>
    </button>

    <button class="navbtn flex flex-col items-center gap-1 text-[11px] font-bold
                   text-slate-500 dark:text-slate-300"
            data-go="history" type="button">
      <span class="w-9 h-9 rounded-xl grid place-items-center text-lg">
        <i class="bi bi-clock-history"></i>
      </span>
      <span>{{ __('messages.history') ?? 'History' }}</span>
    </button>

    <button class="navbtn flex flex-col items-center gap-1 text-[11px] font-bold
                   text-slate-500 dark:text-slate-300"
            data-go="profile" type="button">
      <span class="w-9 h-9 rounded-xl grid place-items-center text-lg">
        <i class="bi bi-person"></i>
      </span>
      <span>{{ __('messages.account') ?? 'Account' }}</span>
    </button>
  </div>

  <style>
    /* active state (simple css, no @apply) */
    .navbtn.is-active { color: rgb(79 70 229); }
    html.dark .navbtn.is-active { color: rgb(165 180 252); }
    .navbtn.is-active > span:first-child { background: rgba(79,70,229,.1); }
    html.dark .navbtn.is-active > span:first-child { background: rgba(79,70,229,.25); }
  </style>
</nav>
