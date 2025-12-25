<section data-screen="cart" class="screen hidden">
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4">
    <div class="flex items-center justify-between">
      <p class="text-[13px] font-extrabold">{{ __('messages.your_cart') ?? 'Your cart' }}</p>
      <button id="btnClearCart" class="text-[12px] font-bold text-rose-500" type="button">
        {{ __('messages.clear') ?? 'Clear' }}
      </button>
    </div>

    <div id="cartList" class="mt-3 space-y-3"></div>

    <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-3 space-y-2 text-[13px]">
      <div class="flex justify-between">
        <span class="text-slate-500 dark:text-slate-300">{{ __('messages.subtotal') ?? 'Subtotal' }}</span>
        <span class="font-extrabold" id="cartSubtotal">$0.00</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500 dark:text-slate-300">{{ __('messages.shipping') ?? 'Shipping' }}</span>
        <span class="font-extrabold" id="cartShipping">$0.00</span>
      </div>
      <div class="flex justify-between text-[14px]">
        <span class="font-extrabold">{{ __('messages.total') ?? 'Total' }}</span>
        <span class="font-extrabold" id="cartTotal">$0.00</span>
      </div>

      <button id="btnCheckout"
              type="button"
              class="mt-2 w-full h-11 rounded-xl bg-indigo-600 text-white font-extrabold active:scale-[.99] transition">
        {{ __('messages.checkout') ?? 'Checkout' }}
      </button>
    </div>
  </div>
</section>
