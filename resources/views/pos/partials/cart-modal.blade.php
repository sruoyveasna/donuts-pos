@php($p = $idPrefix ?? 'cart')
@php($drawer = $isDrawer ?? false)
@php($ctx = $context ?? 'cart') {{-- cart | payment --}}

<div class="h-full w-full rounded-2xl
            bg-white/70 dark:bg-slate-950/60 backdrop-blur
            border border-white/30 dark:border-slate-800/70
            shadow-sm shadow-indigo-200/25 dark:shadow-none
            flex flex-col overflow-hidden">

  {{-- Header --}}
  <div class="px-3.5 pt-3 pb-2.5 flex items-center justify-between gap-3
              border-b border-white/30 dark:border-slate-800/70">
    <div class="min-w-0">
      <div class="text-sm font-semibold text-slate-900 dark:text-white">Your Cart</div>
      <div class="text-[11px] text-slate-500 dark:text-slate-400">
        Items: <span id="{{ $p }}_cartCount">0</span>
      </div>
    </div>

    <button type="button"
      class="h-8 w-8 rounded-xl
             border border-white/30 dark:border-slate-800/70
             bg-white/70 dark:bg-slate-900/50
             hover:bg-white/90 dark:hover:bg-slate-900/70
             transition grid place-items-center
             text-slate-700 dark:text-slate-200"
      data-cart-close
      aria-label="Close cart">
      <i class="bi bi-x-lg text-[12px]"></i>
    </button>
  </div>

  {{-- Items list --}}
  <div id="{{ $p }}_cartList"
       class="flex-1 overflow-auto no-scrollbar px-3.5 py-2.5 space-y-2">
    {{-- JS inject --}}
  </div>

  {{-- Footer --}}
  <div class="px-3.5 pb-3 pt-2.5 border-t border-white/30 dark:border-slate-800/70">
    <div class="rounded-2xl p-3
                bg-white/60 dark:bg-slate-950/50
                border border-white/35 dark:border-slate-800/70">

      <div class="space-y-1.5 text-[12px]">
        <div class="flex items-center justify-between">
          <span class="text-slate-500 dark:text-slate-400">Subtotal (USD)</span>
          <span class="font-semibold tabular-nums text-slate-900 dark:text-slate-100"
                id="{{ $p }}_sumSubtotalUsd">$0.00</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500 dark:text-slate-400">Subtotal (KHR)</span>
          <span class="font-semibold tabular-nums text-slate-900 dark:text-slate-100"
                id="{{ $p }}_sumSubtotalKhr">0 ៛</span>
        </div>
      </div>

      <div class="mt-2.5 flex gap-2">
        <button type="button"
          class="flex-1 h-9 rounded-xl text-xs font-semibold
                 bg-white/70 dark:bg-slate-900/50
                 hover:bg-white/90 dark:hover:bg-slate-900/70
                 border border-white/30 dark:border-slate-800/70
                 text-slate-800 dark:text-slate-200
                 active:scale-[0.99] transition"
          data-cart-clear>
          Clear
        </button>

        <button type="button"
          class="flex-[1.2] h-9 rounded-xl text-xs font-semibold
                 bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400
                 text-white
                 shadow-md shadow-sky-300/60 hover:shadow-sky-400/70
                 disabled:opacity-60 disabled:cursor-not-allowed
                 active:scale-[0.99] transition
                 focus:outline-none focus:ring-2 focus:ring-sky-300/60"
          data-cart-checkout>
          Checkout
        </button>
      </div>
    </div>
  </div>
</div>
