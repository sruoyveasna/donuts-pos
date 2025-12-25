@extends('layouts.app')

@section('title', 'POS')

@section('content')

@php
  use App\Models\Setting;

  $taxEnabled = Setting::getCached('pos.tax.enabled', 'true') === 'true';

  // ✅ Inject ALL settings needed by pos-js receipt + totals
  $posConfig = [
    // currency / tax
    'exchange_rate'    => (float) Setting::getCached('pos.currency.exchange_usd', 4100),
    'tax_enabled'      => (bool) $taxEnabled,
    'tax_rate'         => (float) ($taxEnabled ? Setting::getCached('pos.tax.rate', 10) : 0),
    'currency_symbol'  => (string) Setting::getCached('pos.currency.symbol', '៛'),
    'currency_default' => (string) Setting::getCached('pos.currency.default', 'KHR'),

    // ✅ receipt header/footer (what you asked for)
    'app_name'            => (string) Setting::getCached('app.name', config('app.name')),
    'app_logo'            => (string) Setting::getCached('app.logo', ''), // ex: /storage/logos/xxx.png
    'receipt_footer_note' => (string) Setting::getCached('pos.receipt.footer_note', ''),

    // ✅ optional aliases (your JS checks these first)
    'shop_name'           => (string) Setting::getCached('app.name', config('app.name')),
    'shop_logo'           => (string) Setting::getCached('app.logo', ''),
  ];
@endphp

<style>
  /* ===== Desktop layout grid like Vue ===== */
  .content-grid {
    --cart-col: 370px;
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr;
    min-height: 0;
  }
  @media (min-width: 768px) {
    .content-grid { grid-template-columns: 1fr var(--cart-col); }
    .content-grid.cart-hidden { grid-template-columns: 1fr !important; }
    .content-grid.cart-hidden .cart-sidebar { display: none !important; }
  }

  /* Product grid */
  .product-grid {
    --card-min: 200px;
    display: grid;
    gap: .75rem;
    grid-template-columns: repeat(auto-fill, minmax(var(--card-min), 1fr));
  }
  @media (min-width: 1280px) { .product-grid { --card-min: 220px; } }
  @media (min-width: 1536px) { .product-grid { --card-min: 240px; } }

  /* When cart is open on some desktop widths, reduce columns a bit */
  @media (min-width: 1024px) and (max-width: 1200px) {
    .content-grid:not(.cart-hidden) .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (min-width: 1201px) and (max-width: 1449px) {
    .content-grid:not(.cart-hidden) .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  }

  /* ✅ Force 4 columns when cart is hidden (desktop) */
  @media (min-width: 1024px) {
    .content-grid.cart-hidden .product-grid {
      grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
  }
  /* Optional: very wide screens can go 5 columns when cart hidden */
  @media (min-width: 1536px) {
    .content-grid.cart-hidden .product-grid {
      grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
    }
  }

  /* Hide scrollbars utility */
  .no-scrollbar::-webkit-scrollbar { width: 0; height: 0; display:none; }
  .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }

  /* ✅ PRINT ONLY RECEIPT */
  @media print {
    body * { visibility: hidden !important; }
    #receiptPrintArea, #receiptPrintArea * { visibility: visible !important; }
    #receiptPrintArea {
      position: absolute;
      left: 0; top: 0;
      width: 100%;
    }
    dialog { border: 0 !important; }
  }
</style>

<div class="min-h-0">

  {{-- TOP ROW --}}
  <div class="flex items-center gap-4 px-2 md:px-6 pt-2 md:pt-4 pb-3">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white whitespace-nowrap">
      Order List
    </h1>

    <div class="flex-1 max-w-2xl">
      <div class="relative">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input id="searchInput" type="text" placeholder="Search..."
          class="w-full h-10 pl-11 pr-4 rounded-full shadow-sm
                 border border-white/40 dark:border-slate-800/80
                 bg-white/70 dark:bg-slate-900/60
                 text-slate-900 dark:text-white
                 focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
      </div>
    </div>

    {{-- Mobile cart button (drawer) --}}
    <button id="btnMobileCart"
      class="ml-auto relative flex items-center md:hidden
             bg-purple-50 dark:bg-slate-900 hover:bg-purple-100 dark:hover:bg-slate-800
             text-purple-700 dark:text-purple-200 rounded-full px-6 py-2 shadow transition">
      <i class="bi bi-cart3 text-lg mr-2"></i>
      <span>Cart</span>
      <span id="badgeMobile"
        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-6 h-6 rounded-full
               flex items-center justify-center border-2 border-white dark:border-slate-900">
        0
      </span>
    </button>

    {{-- Desktop toggle cart (column hide/show) --}}
    <button id="btnDesktopCartToggle"
      class="ml-auto hidden md:inline-flex items-center gap-2 rounded-full px-4 py-1.5
             text-sm font-semibold transition-all border shadow-sm relative
             bg-purple-600 text-white hover:bg-purple-700 border-transparent">
      <i class="bi bi-cart3"></i>
      <span>Cart</span>
      <span id="badgeDesktop"
        class="absolute -top-2 -right-2 bg-red-500 text-white text-[11px] leading-none w-5 h-5 rounded-full
               flex items-center justify-center border-2 border-white dark:border-slate-900">
        0
      </span>
    </button>
  </div>

  {{-- BOTTOM ROW --}}
  <div class="px-2 md:px-6 pb-6 h-[calc(90vh-32px)] min-h-0 overflow-hidden">
    <div id="contentGrid" class="content-grid h-full" aria-label="Products and Cart">

      {{-- LEFT: products --}}
      <section class="min-h-0 flex flex-col overflow-hidden">
        <div class="flex items-center mb-4 shrink-0 gap-4">
          <div class="max-w-[68%] min-w-[180px] overflow-x-auto no-scrollbar">
            <div id="catPills" class="inline-flex gap-2"></div>
          </div>

          <div class="flex-1"></div>

          {{-- view toggle UI only --}}
          <div class="flex items-center gap-2">
            <button type="button"
              class="p-2 rounded-full bg-white/70 dark:bg-slate-900/60
                     border border-white/40 dark:border-slate-800/80
                     hover:bg-white/90 dark:hover:bg-slate-900 transition">
              <i class="bi bi-grid"></i>
            </button>
            <button type="button"
              class="p-2 rounded-full bg-white/70 dark:bg-slate-900/60
                     border border-white/40 dark:border-slate-800/80
                     hover:bg-white/90 dark:hover:bg-slate-900 transition">
              <i class="bi bi-list-ul"></i>
            </button>
          </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden no-scrollbar">
          <div class="pb-4">
            <div id="productsGrid" class="product-grid"></div>
            <div id="productsEmpty"
              class="hidden mt-10 text-center text-sm text-slate-500 dark:text-slate-400">
              No items found.
            </div>
          </div>
        </div>
      </section>

      {{-- RIGHT: cart (desktop only) --}}
      <aside class="cart-sidebar hidden md:flex flex-col min-h-0">
        <div class="min-h-0 flex-1">
          @include('pos.partials.cart-modal', ['idPrefix' => 'cart', 'isDrawer' => false, 'context' => 'cart'])
        </div>
      </aside>
    </div>
  </div>

  {{-- Mobile drawer --}}
  <div id="mobileDrawer" class="hidden fixed inset-0 z-50">
    <div id="drawerOverlay" class="absolute inset-0 bg-black/50"></div>

    <div id="drawerPanel"
      class="absolute right-0 top-0 h-full w-[92%] max-w-[420px]
             p-4 bg-transparent translate-x-full transition-transform duration-200">
      @include('pos.partials.cart-modal', ['idPrefix' => 'mcart', 'isDrawer' => true, 'context' => 'cart'])
    </div>
  </div>

</div>

{{-- Dialogs --}}
@include('pos.partials.customize-items')
@include('pos.partials.payment-modal')
@include('pos.partials.receipt')

{{-- Templates --}}
<template id="tplProductCard">
  @include('pos.partials.product-card')
</template>
<template id="tplCartItem">
  @include('pos.partials.cart-item')
</template>

@endsection

@push('scripts')
<script>
  // ✅ Inject dynamic settings from DB -> JS
  window.__POS_CONFIG__ = @json($posConfig);
</script>

{{-- POS logic --}}
@include('pos.partials.pos-js')
@endpush
