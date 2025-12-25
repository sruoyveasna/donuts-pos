{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.orders_show_title', [], $locale) ?: 'Order Details')

@push('head')
<style>
  .card-sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(16px);
  }

  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  #itemsRows tr:hover, #payRows tr:hover { background: rgba(244,114,182,.05); }

  .skeleton { position: relative; overflow: hidden; background: #f3f4f6; border-radius: .375rem; }
  .dark .skeleton { background: rgba(15,23,42,.9); }
  .skeleton::after {
    content: "";
    position: absolute;
    inset: 0;
    transform: translateX(-100%);
    background: linear-gradient(90deg,
      rgba(248,250,252,0),
      rgba(226,232,240,.9),
      rgba(248,250,252,0));
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 100% { transform: translateX(100%); } }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-4">

  {{-- Heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <a href="{{ url('/orders') }}"
           class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                  px-3 py-1.5 text-[11px] md:text-xs text-slate-700 hover:bg-slate-50
                  dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i>
          <span>{{ __('messages.back', [], $locale) ?: 'Back' }}</span>
        </a>

        <div id="orderBadge"
             class="hidden sm:inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-receipt text-[12px] text-rose-300"></i>
          <span id="orderCodeText">—</span>
        </div>
      </div>

      <h1 class="mt-2 text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🧾 {{ __('messages.orders_show_title', [], $locale) ?: 'Order Details' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.orders_show_subtitle', [], $locale) ?: 'Review items, totals, and payments for this order.' }}
      </p>

      <p id="metaLine" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <span id="statusPill" class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] text-slate-600 dark:text-slate-200">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
        —
      </span>

      <button id="printBtn" type="button"
              class="tw-tip inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                     dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                     px-3 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                     hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition"
              data-tooltip="{{ __('messages.print', [], $locale) ?: 'Print' }}">
        <i class="bi bi-printer text-[12px]"></i>
        <span class="hidden sm:inline">{{ __('messages.print', [], $locale) ?: 'Print' }}</span>
      </button>

      @if($canWrite)
      <button id="payBtn" type="button"
              class="hidden inline-flex items-center gap-1.5 rounded-full
                     bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                     px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
                     shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
        <i class="bi bi-cash-coin text-[12px]"></i>
        <span>{{ __('messages.orders_record_payment', [], $locale) ?: 'Record payment' }}</span>
      </button>
      @endif
    </div>
  </div>

  {{-- Main layout --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Left: Items + Payments --}}
    <div class="lg:col-span-2 space-y-4">

      {{-- Items card --}}
      <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
                  bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
                  backdrop-blur-2xl overflow-hidden">
        <div class="card-sticky-header px-4 md:px-5 py-3
                    border-b border-white/60 dark:border-slate-800/80
                    bg-gradient-to-r from-white/95 via-white/80 to-white/70
                    dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
          <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                        dark:bg-slate-800 shadow-sm shadow-slate-900/40">
              <i class="bi bi-basket2 text-[12px] text-rose-300"></i>
              <span>{{ __('messages.orders_items_title', [], $locale) ?: 'Items' }}</span>
            </div>
            <div class="flex-1"></div>
            <span id="itemsCount"
                  class="text-[11px] text-slate-500 dark:text-slate-400">—</span>
          </div>
        </div>

        <div class="px-3 md:px-4 py-3 overflow-x-auto">
          <table class="min-w-full text-xs md:text-sm">
            <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 bg-slate-50/60 dark:bg-slate-950/40">
              <tr>
                <th class="px-3 py-2 text-left font-medium w-[44%]">{{ __('messages.orders_col_item', [], $locale) ?: 'Item' }}</th>
                <th class="px-3 py-2 text-center font-medium w-[10%]">{{ __('messages.orders_col_qty', [], $locale) ?: 'Qty' }}</th>
                <th class="px-3 py-2 text-right font-medium w-[16%]">{{ __('messages.orders_col_unit', [], $locale) ?: 'Unit (USD)' }}</th>
                <th class="px-3 py-2 text-right font-medium w-[16%]">{{ __('messages.orders_col_subtotal', [], $locale) ?: 'Subtotal (USD)' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[14%]">{{ __('messages.orders_col_note', [], $locale) ?: 'Note' }}</th>
              </tr>
            </thead>
            <tbody id="itemsRows" class="divide-y divide-slate-100 dark:divide-slate-800">
              {{-- skeleton --}}
              @for($i=0;$i<4;$i++)
                <tr class="h-12">
                  <td class="px-3"><div class="skeleton h-4 w-48"></div><div class="skeleton h-3 w-32 mt-2"></div></td>
                  <td class="px-3 text-center"><div class="skeleton h-4 w-10 mx-auto"></div></td>
                  <td class="px-3 text-right"><div class="skeleton h-4 w-16 ml-auto"></div></td>
                  <td class="px-3 text-right"><div class="skeleton h-4 w-20 ml-auto"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-24"></div></td>
                </tr>
              @endfor
            </tbody>
          </table>
        </div>
      </div>

      {{-- Payments card --}}
      <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
                  bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
                  backdrop-blur-2xl overflow-hidden">
        <div class="card-sticky-header px-4 md:px-5 py-3
                    border-b border-white/60 dark:border-slate-800/80
                    bg-gradient-to-r from-white/95 via-white/80 to-white/70
                    dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
          <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                        dark:bg-slate-800 shadow-sm shadow-slate-900/40">
              <i class="bi bi-credit-card text-[12px] text-rose-300"></i>
              <span>{{ __('messages.orders_payments_title', [], $locale) ?: 'Payments' }}</span>
            </div>
            <div class="flex-1"></div>
            <span id="payCount" class="text-[11px] text-slate-500 dark:text-slate-400">—</span>
          </div>
        </div>

        <div class="px-3 md:px-4 py-3 overflow-x-auto">
          <table class="min-w-full text-xs md:text-sm">
            <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 bg-slate-50/60 dark:bg-slate-950/40">
              <tr>
                <th class="px-3 py-2 text-left font-medium w-[18%]">{{ __('messages.orders_col_method', [], $locale) ?: 'Method' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[12%]">{{ __('messages.orders_col_currency', [], $locale) ?: 'Cur' }}</th>
                <th class="px-3 py-2 text-right font-medium w-[18%]">{{ __('messages.orders_col_amount', [], $locale) ?: 'Amount (KHR)' }}</th>
                <th class="px-3 py-2 text-right font-medium w-[18%]">{{ __('messages.orders_col_tendered', [], $locale) ?: 'Tendered (KHR)' }}</th>
                <th class="px-3 py-2 text-right font-medium w-[18%]">{{ __('messages.orders_col_change', [], $locale) ?: 'Change (KHR)' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[16%]">{{ __('messages.orders_col_time', [], $locale) ?: 'Time' }}</th>
              </tr>
            </thead>
            <tbody id="payRows" class="divide-y divide-slate-100 dark:divide-slate-800">
              {{-- skeleton --}}
              @for($i=0;$i<2;$i++)
                <tr class="h-12">
                  <td class="px-3"><div class="skeleton h-4 w-20"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-12"></div></td>
                  <td class="px-3 text-right"><div class="skeleton h-4 w-20 ml-auto"></div></td>
                  <td class="px-3 text-right"><div class="skeleton h-4 w-24 ml-auto"></div></td>
                  <td class="px-3 text-right"><div class="skeleton h-4 w-24 ml-auto"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-28"></div></td>
                </tr>
              @endfor
            </tbody>
          </table>
        </div>
      </div>

    </div>

    {{-- Right: Summary card --}}
    <div class="space-y-4">

      <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
                  bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
                  backdrop-blur-2xl overflow-hidden">
        <div class="px-4 md:px-5 py-3 border-b border-white/60 dark:border-slate-800/80
                    bg-gradient-to-r from-white/95 via-white/80 to-white/70
                    dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
          <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                      dark:bg-slate-800 shadow-sm shadow-slate-900/40">
            <i class="bi bi-calculator text-[12px] text-rose-300"></i>
            <span>{{ __('messages.orders_summary_title', [], $locale) ?: 'Summary' }}</span>
          </div>
        </div>

        <div class="px-4 md:px-5 py-4 space-y-3 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_cashier', [], $locale) ?: 'Cashier' }}</span>
            <span id="cashierText" class="font-medium text-slate-900 dark:text-slate-50 text-right">—</span>
          </div>

          <div class="soft-divider"></div>

          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_subtotal', [], $locale) ?: 'Subtotal' }}</span>
            <span id="subtotalText" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_discount', [], $locale) ?: 'Discount' }}</span>
            <span id="discountText" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_tax', [], $locale) ?: 'Tax' }}</span>
            <span id="taxText" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
          </div>

          <div class="soft-divider"></div>

          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_total', [], $locale) ?: 'Total' }}</span>
            <span id="totalText" class="text-base font-bold text-slate-900 dark:text-slate-50">—</span>
          </div>

          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_paid', [], $locale) ?: 'Paid' }}</span>
            <span id="paidText" class="font-semibold text-emerald-700 dark:text-emerald-300">—</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.orders_due', [], $locale) ?: 'Due' }}</span>
            <span id="dueText" class="font-semibold text-rose-700 dark:text-rose-300">—</span>
          </div>

          <div class="soft-divider"></div>

          <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
            <div class="flex justify-between gap-4">
              <span>{{ __('messages.orders_exchange_rate', [], $locale) ?: 'Exchange rate' }}</span>
              <span id="rateText">—</span>
            </div>
            <div class="flex justify-between gap-4">
              <span>{{ __('messages.orders_tax_rate', [], $locale) ?: 'Tax rate' }}</span>
              <span id="taxRateText">—</span>
            </div>
          </div>

          <p id="loadErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Optional: Payment modal --}}
@if($canWrite)
<dialog id="payModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40 overflow-hidden">

    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400
                text-slate-50 flex items-center justify-between gap-2">
      <div>
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30 shadow-sm shadow-rose-900/40">
            <i class="bi bi-cash-coin text-[13px]"></i>
          </span>
          <span>{{ __('messages.orders_record_payment', [], $locale) ?: 'Record payment' }}</span>
        </div>
        <p class="mt-0.5 text-[11px] text-rose-50/90">
          {{ __('messages.orders_record_payment_hint', [], $locale) ?: 'Enter tendered amount and confirm.' }}
        </p>
      </div>

      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="payForm" class="px-4 md:px-5 py-4 space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.orders_payment_method', [], $locale) ?: 'Method' }}
          </label>
          <select id="pay_method"
                  class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                         bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                         text-slate-900 dark:text-slate-50
                         focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="aba">ABA</option>
            <option value="wing">Wing</option>
          </select>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.orders_payment_currency', [], $locale) ?: 'Currency' }}
          </label>
          <select id="pay_currency"
                  class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                         bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                         text-slate-900 dark:text-slate-50
                         focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400">
            <option value="KHR">KHR</option>
            <option value="USD">USD</option>
          </select>
          <p class="text-[11px] text-slate-400 dark:text-slate-500">
            {{ __('messages.orders_payment_currency_hint', [], $locale) ?: 'USD will convert to KHR using exchange rate.' }}
          </p>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.orders_tendered', [], $locale) ?: 'Tendered' }}
          </label>
          <input id="pay_tendered"
                 type="number" step="0.01" min="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50
                        placeholder:text-slate-400 dark:placeholder:text-slate-500
                        focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
                 placeholder="0">
          <p id="pay_calc" class="text-[11px] text-slate-400 dark:text-slate-500">—</p>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.orders_exchange_rate', [], $locale) ?: 'Exchange rate' }}
          </label>
          <input id="pay_rate"
                 type="number" step="0.01" min="1"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50
                        placeholder:text-slate-400 dark:placeholder:text-slate-500
                        focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
                 placeholder="4100">
          <p class="text-[11px] text-slate-400 dark:text-slate-500">
            {{ __('messages.orders_exchange_rate_hint', [], $locale) ?: 'Used when currency is USD.' }}
          </p>
        </div>
      </div>

      <p id="payErr" class="text-[11px] text-rose-500 mt-1 min-h-[1rem]"></p>

      <div class="flex justify-between items-center pt-1">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i>
          <span>{{ __('messages.cancel', [], $locale) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full
                       bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                       px-4 py-1.5 text-xs font-semibold text-white
                       shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.confirm', [], $locale) ?: 'Confirm' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>
@endif
@endsection

@push('scripts')
<script>
/**
 * Expected API:
 *   GET /api/orders/{id}
 * returns:
 *   { order: { id, order_code, status, created_at, subtotal_khr, discount_khr, tax_khr, total_khr,
 *              paid_khr, due_khr, exchange_rate, tax_rate, total_items,
 *              user: {name,email},
 *              items: [ {quantity, price, subtotal, note, customizations,
 *                        menu_item: {name}, menu_item_variant: {name} } ],
 *              payments: [ {method,currency,amount_khr,tendered_khr,change_khr,confirmed_at} ]
 *            } }
 */

const ORDER_SHOW_I18N = {
  loading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  loadFailed: @json(__('messages.orders_load_failed_message', [], $locale) ?: 'Couldn’t load this order.'),
  itemsEmpty: @json(__('messages.orders_items_empty', [], $locale) ?: 'No items.'),
  paymentsEmpty: @json(__('messages.orders_payments_empty', [], $locale) ?: 'No payments.'),
  paid: @json(__('messages.orders_status_paid', [], $locale) ?: 'Paid'),
  unpaid: @json(__('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid'),
  paySuccess: @json(__('messages.orders_payment_success', [], $locale) ?: 'Payment recorded'),
  payFail: @json(__('messages.orders_payment_fail', [], $locale) ?: 'Payment failed'),
};

const el = (id) => document.getElementById(id);

const dom = {
  orderBadge: el('orderBadge'),
  orderCodeText: el('orderCodeText'),
  metaLine: el('metaLine'),
  statusPill: el('statusPill'),
  itemsRows: el('itemsRows'),
  payRows: el('payRows'),
  itemsCount: el('itemsCount'),
  payCount: el('payCount'),
  cashierText: el('cashierText'),
  subtotalText: el('subtotalText'),
  discountText: el('discountText'),
  taxText: el('taxText'),
  totalText: el('totalText'),
  paidText: el('paidText'),
  dueText: el('dueText'),
  rateText: el('rateText'),
  taxRateText: el('taxRateText'),
  loadErr: el('loadErr'),
  printBtn: el('printBtn'),
  payBtn: el('payBtn'),
};

let ORDER = null;

function getOrderId() {
  // Works with /orders/{id} or /orders/{id}/...
  const parts = window.location.pathname.split('/').filter(Boolean);
  const idx = parts.indexOf('orders');
  const raw = (idx >= 0 && parts[idx + 1]) ? parts[idx + 1] : null;
  const n = raw ? parseInt(raw, 10) : NaN;
  return Number.isFinite(n) ? n : null;
}

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g, '&quot;'); }
function fmtKHR(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0៛';
  return n.toLocaleString('en-US') + '៛';
}
function fmtUSD(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '$0.00';
  return '$' + n.toFixed(2);
}
function fmtDate(dt){
  try { return dt ? new Date(dt).toLocaleString() : '—'; } catch { return '—'; }
}

function setStatus(isPaid){
  dom.statusPill.innerHTML = isPaid
    ? `<span class="h-2 w-2 rounded-full bg-emerald-500"></span> ${esc(ORDER_SHOW_I18N.paid)}`
    : `<span class="h-2 w-2 rounded-full bg-amber-500"></span> ${esc(ORDER_SHOW_I18N.unpaid)}`;
  dom.statusPill.className =
    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] ' +
    (isPaid
      ? 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-200'
      : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-200');
}

function renderItems(items) {
  if (!items?.length) {
    dom.itemsRows.innerHTML = `
      <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
        <i class="bi bi-basket2 block text-3xl opacity-70 mb-2"></i>
        <div class="font-medium">${esc(ORDER_SHOW_I18N.itemsEmpty)}</div>
      </td></tr>`;
    dom.itemsCount.textContent = '0';
    return;
  }

  dom.itemsCount.textContent = `${items.length} row(s)`;
  dom.itemsRows.innerHTML = items.map(it => {
    const baseName =
      it.menu_item?.name ||
      it.menu_item_name ||
      '—';

    const variant =
    it.menu_item_variant?.name ||
    it.variant?.name ||
    it.variant_name ||
    '';

    const fullName = variant ? `${baseName} · ${variant}` : baseName;

    const note = it.note ? esc(it.note) : '—';
    const cus  = it.customizations ? JSON.stringify(it.customizations) : '';

    return `
      <tr class="align-middle">
        <td class="px-3 py-2">
          <div class="font-medium text-slate-900 dark:text-slate-50">${esc(fullName)}</div>
          ${cus ? `<div class="text-[11px] text-slate-500 dark:text-slate-400 truncate" title="${escAttr(cus)}">${esc(cus)}</div>` : `<div class="text-[11px] text-slate-400 dark:text-slate-500">—</div>`}
        </td>
        <td class="px-3 py-2 text-center text-slate-700 dark:text-slate-200">${esc(it.quantity ?? '—')}</td>
        <td class="px-3 py-2 text-right font-medium text-slate-900 dark:text-slate-50">${esc(fmtUSD(it.price))}</td>
        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtUSD(it.subtotal))}</td>
        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${note}</td>
      </tr>`;
  }).join('');
}

function renderPayments(payments) {
  if (!payments?.length) {
    dom.payRows.innerHTML = `
      <tr><td colspan="6" class="py-10 text-center text-slate-500 dark:text-slate-400">
        <i class="bi bi-credit-card block text-3xl opacity-70 mb-2"></i>
        <div class="font-medium">${esc(ORDER_SHOW_I18N.paymentsEmpty)}</div>
      </td></tr>`;
    dom.payCount.textContent = '0';
    return;
  }

  dom.payCount.textContent = `${payments.length} payment(s)`;
  dom.payRows.innerHTML = payments.map(p => `
    <tr class="align-middle">
      <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-50">${esc(p.method || '—')}</td>
      <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(p.currency || '—')}</td>
      <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtKHR(p.amount_khr))}</td>
      <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(fmtKHR(p.tendered_khr))}</td>
      <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(fmtKHR(p.change_khr))}</td>
      <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(fmtDate(p.confirmed_at || p.created_at))}</td>
    </tr>
  `).join('');
}

function renderSummary(order) {
  dom.orderBadge.classList.remove('hidden');
  dom.orderCodeText.textContent = order.order_code || ('#' + order.id);

  const cashier = order.user?.name || order.user?.email || '—';
  dom.cashierText.textContent = cashier;

  dom.subtotalText.textContent = fmtKHR(order.subtotal_khr);
  dom.discountText.textContent = fmtKHR(order.discount_khr);
  dom.taxText.textContent      = fmtKHR(order.tax_khr);
  dom.totalText.textContent    = fmtKHR(order.total_khr);

  const paid = Number(order.paid_khr ?? 0);
  const due  = Number(order.due_khr ?? (Number(order.total_khr ?? 0) - paid));

  dom.paidText.textContent = fmtKHR(paid);
  dom.dueText.textContent  = fmtKHR(Math.max(0, due));

  dom.rateText.textContent = order.exchange_rate ? `${Number(order.exchange_rate).toLocaleString('en-US')} KHR / USD` : '—';
  dom.taxRateText.textContent = (order.tax_rate !== null && typeof order.tax_rate !== 'undefined')
    ? `${Number(order.tax_rate)}%`
    : '—';

  dom.metaLine.textContent = `${esc(fmtDate(order.created_at))} · ${esc(cashier)}`;

  const isPaid = (order.status === 'paid') || !!order.is_paid || (Math.max(0, due) <= 0);
  setStatus(isPaid);

  @if($canWrite)
  // show pay button only when due > 0
  dom.payBtn.classList.toggle('hidden', isPaid);
  @endif
}

async function loadOrder() {
  dom.loadErr.textContent = '';
  dom.metaLine.textContent = ORDER_SHOW_I18N.loading;

  const id = getOrderId();
  if (!id) {
    dom.loadErr.textContent = 'Missing order id in URL.';
    dom.metaLine.textContent = '—';
    return;
  }

  try {
    // adjust if your API returns {order: {...}} or just {...}
    const res = await api(`/api/orders/${id}`);
    ORDER = res?.order ?? res;

    renderSummary(ORDER);
    renderItems(ORDER.items || []);
    renderPayments(ORDER.payments || []);
  } catch (e) {
    console.error(e);
    dom.loadErr.textContent = e?.data?.message || ORDER_SHOW_I18N.loadFailed;
    dom.metaLine.textContent = '—';
  }
}

// Print
dom.printBtn?.addEventListener('click', () => window.print());

@if($canWrite)
// Payment modal wiring
const payModal = document.getElementById('payModal');
const payForm  = document.getElementById('payForm');
const payErr   = document.getElementById('payErr');
const payCalc  = document.getElementById('pay_calc');

function calcPayPreview() {
  if (!ORDER) return;

  const cur = document.getElementById('pay_currency').value;
  const tendered = Number(document.getElementById('pay_tendered').value || 0);
  const rate = Number(document.getElementById('pay_rate').value || ORDER.exchange_rate || 4100);

  const dueKhr = Number(ORDER.due_khr ?? 0);
  const tenderedKhr = (cur === 'USD') ? Math.round(tendered * rate) : Math.round(tendered);

  const change = Math.max(0, tenderedKhr - dueKhr);

  payCalc.textContent =
    `Due: ${fmtKHR(dueKhr)} · Tendered: ${fmtKHR(tenderedKhr)} · Change: ${fmtKHR(change)} · Rate: ${rate.toLocaleString('en-US')}`;
}

dom.payBtn?.addEventListener('click', () => {
  if (!ORDER) return;
  payErr.textContent = '';

  document.getElementById('pay_currency').value = 'KHR';
  document.getElementById('pay_tendered').value = '';
  document.getElementById('pay_rate').value = ORDER.exchange_rate || 4100;

  calcPayPreview();
  openDialog(payModal);
});

document.getElementById('pay_currency').addEventListener('change', () => {
  calcPayPreview();
});
document.getElementById('pay_tendered').addEventListener('input', () => {
  calcPayPreview();
});
document.getElementById('pay_rate').addEventListener('input', () => {
  calcPayPreview();
});

payForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!ORDER) return;

  payErr.textContent = '';

  const method = document.getElementById('pay_method').value;
  const currency = document.getElementById('pay_currency').value;
  const tendered = Number(document.getElementById('pay_tendered').value || 0);
  const rate = Number(document.getElementById('pay_rate').value || ORDER.exchange_rate || 4100);

  const payload = { method, currency };
  if (currency === 'KHR') payload.tendered_khr = Math.round(tendered);
  else {
    payload.tendered_usd = tendered;
    payload.exchange_rate = rate;
  }

  try {
    await api(`/api/pos/orders/${ORDER.id}/payments`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    showToast(ORDER_SHOW_I18N.paySuccess, 'success');
    closeDialog(payModal);
    await loadOrder();
  } catch (err) {
    console.error(err);
    payErr.textContent = err?.data?.message || ORDER_SHOW_I18N.payFail;
  }
});
@endif

// init
(function init(){
  loadOrder();
})();
</script>
@endpush
