@php($p = $idPrefix ?? 'cart')
@php($ctx = $context ?? 'cart') {{-- cart | payment --}}

<div class="rounded-2xl p-3 bg-slate-950/40 border border-white/10">

  {{-- Header: Cart count + optional close (mobile drawer) --}}
  <div class="flex items-center justify-between mb-3">
    <div class="text-sm font-semibold text-slate-100">
      Cart (<span id="{{ $p }}_cartCount">0</span>)
    </div>

    {{-- Optional: if you use this summary inside mobile drawer --}}
    @if($p === 'mcart')
      <button type="button"
        class="h-8 px-3 rounded-xl text-xs font-semibold
               bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200"
        data-cart-close>
        Close
      </button>
    @endif
  </div>

  {{-- Cart List (JS will render items here) --}}
  <div id="{{ $p }}_cartList" class="space-y-2">
    <div class="text-sm text-slate-500 dark:text-slate-400 text-center py-8">
      Cart is empty.
    </div>
  </div>

  <div class="h-px bg-white/10 my-3"></div>

  {{-- Totals --}}
  <div class="space-y-1.5 text-[13px] text-slate-200">
    <div class="flex items-center justify-between">
      <span class="text-slate-400">Subtotal (USD)</span>
      <span class="font-medium tabular-nums" id="{{ $p }}_sumSubtotalUsd">$0.00</span>
    </div>

    <div class="flex items-center justify-between">
      <span class="text-slate-400">Subtotal (KHR)</span>
      <span class="font-medium tabular-nums" id="{{ $p }}_sumSubtotalKhr">0 ៛</span>
    </div>

    <div class="flex items-center justify-between">
      <span class="text-slate-400">Discount</span>
      <span class="font-medium tabular-nums" id="{{ $p }}_sumDiscountKhr">0 ៛</span>
    </div>

    <div class="flex items-center justify-between">
      <span class="text-slate-400">Tax</span>
      <span class="font-medium tabular-nums" id="{{ $p }}_sumTaxKhr">0 ៛</span>
    </div>

    <div class="h-px bg-white/10 my-2"></div>

    <div class="flex items-center justify-between text-base">
      <span class="font-semibold text-purple-300">Total</span>
      <span class="font-semibold text-purple-300 tabular-nums" id="{{ $p }}_sumTotalKhr">0 ៛</span>
    </div>
  </div>

  {{-- Buttons --}}
  <div class="mt-3 flex gap-2">
    @if($ctx === 'payment')
      <button type="button"
        class="flex-1 h-10 rounded-xl text-sm font-semibold
               border border-red-500/60 text-red-200
               hover:bg-red-500/10 active:scale-[0.99] transition"
        data-pay-close>
        Close
      </button>

      <button type="button"
        class="flex-[1.2] h-10 rounded-xl text-sm font-semibold
               bg-purple-600 text-white hover:bg-purple-700
               disabled:opacity-60 disabled:cursor-not-allowed
               active:scale-[0.99] transition"
        data-pay-now>
        Pay Now
      </button>
    @else
      <button type="button"
        class="flex-1 h-10 rounded-xl text-sm font-semibold
               bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200
               active:scale-[0.99] transition"
        data-cart-clear>
        Clear
      </button>

      <button type="button"
        class="flex-[1.2] h-10 rounded-xl text-sm font-semibold
               bg-purple-600 text-white hover:bg-purple-700
               disabled:opacity-60 disabled:cursor-not-allowed
               active:scale-[0.99] transition"
        data-cart-checkout>
        Checkout
      </button>
    @endif
  </div>

</div>
