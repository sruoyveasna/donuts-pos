{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.orders_title', [], $locale) ?: 'Orders')

@push('head')
<style>
  .card-sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(16px);
  }
  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  #rows tr:hover { background: rgba(244,114,182,.05); }

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

  /* ✅ Hide scrollbar but keep scroll */
  .no-scrollbar::-webkit-scrollbar { width: 0px; height: 0px; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Page heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🧾 {{ __('messages.orders_title', [], $locale) ?: 'Orders' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.orders_subtitle', [], $locale) ?: 'Browse and review POS orders and payments.' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.orders_summary_loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    {{-- Tiny legend --}}
    <div class="flex flex-wrap items-center gap-2 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        {{ __('messages.orders_status_paid', [], $locale) ?: 'Paid' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
        {{ __('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid' }}
      </span>
    </div>
  </div>

  {{-- Card --}}
  <div
    class="rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
           backdrop-blur-2xl overflow-hidden
           flex flex-col flex-1 min-h-0">

    {{-- Toolbar --}}
    <div
      class="card-sticky-header px-4 md:px-5 py-3 md:py-3.5
             border-b border-white/60 dark:border-slate-800/80
             bg-gradient-to-r from-white/95 via-white/80 to-white/70
             dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
      <div class="flex flex-wrap items-center gap-2">

        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-receipt text-[12px] text-rose-300"></i>
          <span>{{ __('messages.orders_badge_title', [], $locale) ?: 'Order manager' }}</span>
        </div>

        <div class="flex-1"></div>

        {{-- Search --}}
        <div class="grow md:grow-0 min-w-[220px] max-w-[320px]">
          <div
            class="flex items-stretch rounded-full border border-slate-200 dark:border-slate-700
                   bg-slate-50/80 dark:bg-slate-900/80
                   focus-within:border-rose-400 focus-within:ring-1 focus-within:ring-rose-300">
            <span class="px-2 flex items-center text-slate-400">
              <i class="bi bi-search text-[13px]"></i>
            </span>
            <input
              id="q"
              name="q"
              class="w-full px-2 py-1.5 text-xs md:text-sm outline-none rounded-e-full
                     bg-transparent text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
              placeholder="{{ __('messages.orders_search_placeholder', [], $locale) ?: 'Search by order code or cashier…' }}">
            <button
              class="tw-tip px-2 text-slate-400 hover:text-rose-500"
              id="clearSearch"
              type="button"
              data-tooltip="{{ __('messages.orders_tooltip_clear', [], $locale) ?: 'Clear search' }}">
              <i class="bi bi-x-lg text-[11px]"></i>
            </button>
          </div>
        </div>

        {{-- Filters --}}
        <button id="filterToggle" type="button"
                class="tw-tip inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                       px-2.5 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                       hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200
                       transition"
                data-tooltip="{{ __('messages.orders_filters_label', [], $locale) ?: 'Filters' }}">
          <i class="bi bi-sliders text-[12px]"></i>
          <span class="hidden sm:inline">{{ __('messages.orders_filters_label', [], $locale) ?: 'Filters' }}</span>
        </button>

        {{-- Refresh --}}
        <button
          class="tw-tip inline-flex items-center justify-center rounded-full border border-slate-300/80
                 dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                 h-8 w-8 text-slate-600 dark:text-slate-200 hover:text-rose-500 hover:border-rose-400
                 transition"
          id="refreshBtn"
          type="button"
          data-tooltip="{{ __('messages.orders_refresh_label', [], $locale) ?: 'Refresh' }}">
          <i class="bi bi-arrow-clockwise text-[13px]"></i>
        </button>
      </div>
    </div>

    {{-- Filters panel --}}
    <div id="filterPanel" class="hidden shrink-0">
      <div class="px-4 md:px-5 pt-3 pb-3.5">
        <form id="filterForm" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end text-[11px] md:text-xs">

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.orders_filter_status', [], $locale) ?: 'Status' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="status" name="status">
              <option value="">{{ __('messages.orders_filter_any', [], $locale) ?: 'Any' }}</option>
              <option value="paid">{{ __('messages.orders_status_paid', [], $locale) ?: 'Paid' }}</option>
              <option value="unpaid">{{ __('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.orders_filter_sort_by', [], $locale) ?: 'Sort by' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="sort" name="sort">
              <option value="created_at">{{ __('messages.orders_filter_sort_created', [], $locale) ?: 'Created' }}</option>
              <option value="total_khr">{{ __('messages.orders_filter_sort_total', [], $locale) ?: 'Total' }}</option>
              <option value="order_code">{{ __('messages.orders_filter_sort_code', [], $locale) ?: 'Order code' }}</option>
              <option value="status">{{ __('messages.orders_filter_sort_status', [], $locale) ?: 'Status' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.orders_filter_direction', [], $locale) ?: 'Direction' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="dir" name="dir">
              <option value="desc">{{ __('messages.sort_desc', [], $locale) ?: 'DESC' }}</option>
              <option value="asc">{{ __('messages.sort_asc', [], $locale) ?: 'ASC' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.orders_filter_per_page', [], $locale) ?: 'Per page' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="per_page" name="per_page">
              <option>10</option>
              <option>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="col-span-2 sm:col-span-2">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.orders_filter_date_range', [], $locale) ?: 'Date range' }}
            </label>
            <div class="grid grid-cols-2 gap-2">
              <input id="from" name="from" type="date"
                     class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                            px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
              <input id="to" name="to" type="date"
                     class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                            px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
            </div>
          </div>

          <div class="col-span-2 sm:col-span-1 sm:ml-auto">
            <div class="flex gap-2 justify-end">
              <button
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300
                       px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80"
                type="submit">
                <i class="bi bi-funnel text-[12px]"></i>
                <span>{{ __('messages.orders_filter_apply', [], $locale) ?: 'Apply' }}</span>
              </button>
              <button
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400
                       px-3 py-1.5 text-[11px] text-slate-800 hover:bg-slate-100
                       dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                type="button"
                id="resetBtn">
                <i class="bi bi-arrow-counterclockwise text-[12px]"></i>
                <span>{{ __('messages.orders_filter_reset', [], $locale) ?: 'Reset' }}</span>
              </button>
            </div>
          </div>

        </form>
      </div>
      <div class="soft-divider"></div>
    </div>

    {{-- ✅ Scroll area: removed padding so the header sticks flush --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm border-collapse">
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/95 dark:bg-slate-950/90 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium w-[22%]">{{ __('messages.orders_col_code', [], $locale) ?: 'Order' }}</th>
            <th class="px-3 py-2 text-left font-medium w-[20%]">{{ __('messages.orders_col_cashier', [], $locale) ?: 'Cashier' }}</th>
            <th class="px-3 py-2 text-center font-medium w-[10%]">{{ __('messages.orders_col_items', [], $locale) ?: 'Items' }}</th>
            <th class="px-3 py-2 text-right font-medium w-[16%]">{{ __('messages.orders_col_total', [], $locale) ?: 'Total (KHR)' }}</th>
            <th class="px-3 py-2 text-center font-medium w-[12%]">{{ __('messages.orders_col_status', [], $locale) ?: 'Status' }}</th>
            <th class="px-3 py-2 text-right font-medium w-[20%]">{{ __('messages.orders_col_actions', [], $locale) ?: 'Actions' }}</th>
          </tr>
        </thead>

        <tbody id="rows" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr>
            <td colspan="6" class="py-6 text-center text-slate-500 dark:text-slate-400">
              {{ __('messages.orders_summary_loading', [], $locale) ?: 'Loading…' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Footer / Pagination --}}
    <div
      class="shrink-0 flex flex-wrap items-center justify-between gap-2
             px-4 md:px-5 py-3
             border-t border-white/60 dark:border-slate-800/80
             bg-white/70 dark:bg-slate-950/35 backdrop-blur-xl">
      <small id="pageMeta" class="text-[11px] text-slate-500 dark:text-slate-400"></small>

      <nav class="w-full sm:w-auto">
        <ul id="pager" class="inline-flex items-center gap-1 justify-center sm:justify-end w-full"></ul>
      </nav>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
const ORD_I18N = {
  summaryLoading: @json(__('messages.orders_summary_loading', [], $locale) ?: 'Loading…'),
  summaryRange: @json(__('messages.orders_summary_range', [], $locale) ?: 'Showing :from–:to of :total'),

  loadFailedTitle: @json(__('messages.orders_load_failed_title', [], $locale) ?: "Couldn’t load orders"),
  loadFailedMessage: @json(__('messages.orders_load_failed_message', [], $locale) ?: 'Please try again.'),
  retry: @json(__('messages.orders_retry', [], $locale) ?: 'Retry'),

  emptyTitle: @json(__('messages.orders_empty_title', [], $locale) ?: 'No orders found'),
  emptyBody: @json(__('messages.orders_empty_body', [], $locale) ?: 'Try adjusting your filters.'),

  statusPaid: @json(__('messages.orders_status_paid', [], $locale) ?: 'Paid'),
  statusUnpaid: @json(__('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid'),

  view: @json(__('messages.orders_action_view', [], $locale) ?: 'View'),
};

const state = {
  q: '',
  status: '',
  sort: 'created_at',
  dir: 'desc',
  per_page: 10,
  from: '',
  to: '',
  page: 1,
};

const rows        = document.getElementById('rows');
const pager       = document.getElementById('pager');
const pageMeta    = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm  = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

const elStatus = document.getElementById('status');
const elSort = document.getElementById('sort');
const elDir = document.getElementById('dir');
const elPerPage = document.getElementById('per_page');
const elFrom = document.getElementById('from');
const elTo = document.getElementById('to');

document.getElementById('filterToggle').addEventListener('click', () => {
  document.getElementById('filterPanel').classList.toggle('hidden');
});

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g, '&quot;'); }
function fmtKHR(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US') + '៛';
}
function debounce(fn, ms=350){ let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args), ms); } }

function qs(obj) {
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k,v])=>{
    if (v === '' || v === null || v === false || v === undefined) return;
    p.set(k, String(v));
  });
  return p.toString();
}

function fmtRange(p) {
  if (!p?.total) return '';
  const from  = ((p.current_page - 1) * p.per_page) + 1;
  const to    = Math.min(p.current_page * p.per_page, p.total);
  const total = p.total;
  return ORD_I18N.summaryRange.replace(':from', from).replace(':to', to).replace(':total', total);
}

function skeleton() {
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-12">
      <td class="px-3"><div class="skeleton h-4 w-40"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-32"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-12 mx-auto"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-24 ml-auto"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-20 mx-auto"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-24 ml-auto"></div></td>
    </tr>
  `).join('');
}

/* ============================
   ✅ NEW: URL ↔ state sync
   ============================ */
function hydrateStateFromUrl(){
  const url = new URL(window.location.href);
  const p = url.searchParams;

  const getStr = (k, def='') => (p.get(k) ?? def).toString();
  const getInt = (k, def) => {
    const v = parseInt(p.get(k) ?? '', 10);
    return Number.isFinite(v) && v > 0 ? v : def;
  };

  state.q        = getStr('q', state.q).trim();
  state.status   = getStr('status', state.status);
  state.sort     = getStr('sort', state.sort) || state.sort;
  state.dir      = getStr('dir', state.dir) || state.dir;
  state.per_page = getInt('per_page', state.per_page);
  state.from     = getStr('from', state.from);
  state.to       = getStr('to', state.to);
  state.page     = getInt('page', state.page);

  // Fill UI controls so user sees applied filters
  searchInput.value = state.q;

  if (elStatus) elStatus.value = state.status;
  if (elSort) elSort.value = state.sort;
  if (elDir) elDir.value = state.dir;
  if (elPerPage) elPerPage.value = String(state.per_page);
  if (elFrom) elFrom.value = state.from;
  if (elTo) elTo.value = state.to;

  // If URL has any filters, auto-open filter panel (nice UX)
  const hasFilters =
    state.status || state.from || state.to || (state.sort && state.sort !== 'created_at') ||
    (state.dir && state.dir !== 'desc') || (state.per_page && state.per_page !== 10);

  if (hasFilters) document.getElementById('filterPanel')?.classList.remove('hidden');
}

function syncUrlFromState(replace=true){
  const url = new URL(window.location.href);
  url.search = ''; // rebuild clean

  const params = {
    q: state.q || '',
    status: state.status || '',
    sort: state.sort || '',
    dir: state.dir || '',
    per_page: state.per_page || '',
    from: state.from || '',
    to: state.to || '',
    page: state.page || '',
  };

  const sp = new URLSearchParams();
  Object.entries(params).forEach(([k,v])=>{
    if (v === '' || v === null || v === undefined) return;
    // Don’t keep defaults in URL (cleaner links)
    if (k === 'sort' && v === 'created_at') return;
    if (k === 'dir' && v === 'desc') return;
    if (k === 'per_page' && String(v) === '10') return;
    if (k === 'page' && String(v) === '1') return;
    sp.set(k, String(v));
  });

  url.search = sp.toString();

  if (replace) history.replaceState({}, '', url.toString());
  else history.pushState({}, '', url.toString());
}

/* ============================
   Load
   ============================ */
async function load() {
  skeleton();
  const params = {
    q: state.q,
    status: state.status,
    sort: state.sort,
    dir: state.dir,
    per_page: state.per_page,
    from: state.from,
    to: state.to,
    page: state.page,
  };

  try {
    const res = await api('/api/orders?' + qs(params));
    render(res);
  } catch (e) {
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(ORD_I18N.loadFailedTitle)}</div>
          <div class="text-sm mb-3">${esc(ORD_I18N.loadFailedMessage)}</div>
          <button id="retryBtn"
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${esc(ORD_I18N.retry)}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = ORD_I18N.loadFailedTitle;
    pageMeta.textContent = '';
  }
}

function render(paged) {
  const list = paged.data || [];

  if (!list.length) {
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(ORD_I18N.emptyTitle)}</div>
          <div class="text-sm">${esc(ORD_I18N.emptyBody)}</div>
        </div>
      </td></tr>`;
  } else {
    rows.innerHTML = list.map(o => {
      const isPaid = (o.status === 'paid') || !!o.is_paid || (Number(o.due_khr ?? 0) <= 0);
      const status = isPaid
        ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span> ${esc(ORD_I18N.statusPaid)}
           </span>`
        : `<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span> ${esc(ORD_I18N.statusUnpaid)}
           </span>`;

      const cashier = o.user?.name || o.user?.email || '—';
      const items   = o.total_items ?? o.items_count ?? '—';
      const total   = fmtKHR(o.total_khr);
      const when    = o.created_at ? new Date(o.created_at).toLocaleString() : '';
      const showUrl = `/orders/${o.id}`;

      return `
        <tr class="align-middle">
          <td class="px-3 py-2">
            <div class="font-medium text-slate-900 dark:text-slate-50">${esc(o.order_code || ('#'+o.id))}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">${esc(when)}</div>
          </td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(cashier)}</td>
          <td class="px-3 py-2 text-center text-slate-700 dark:text-slate-200">${esc(items)}</td>
          <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(total)}</td>
          <td class="px-3 py-2 text-center">${status}</td>
          <td class="px-3 py-2 text-right">
            <div class="flex justify-end gap-2">
              <a href="${escAttr(showUrl)}"
                 class="tw-tip inline-flex items-center justify-center rounded-full border border-indigo-600 h-7 px-3 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200"
                 data-tooltip="${escAttr(ORD_I18N.view)}">
                <i class="bi bi-eye text-[13px] leading-none mr-1"></i>
                <span class="text-[11px]">${esc(ORD_I18N.view)}</span>
              </a>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  const meta = fmtRange(paged);
  summaryText.textContent = meta || '—';
  pageMeta.textContent = meta || '';
  buildPager(paged);
}

function buildPager(p) {
  const { current_page, last_page } = p;
  pager.innerHTML = '';
  if (!last_page || last_page <= 1) return;

  const add = (label, page, disabled=false, active=false) => {
    const li = document.createElement('li');
    const base = 'px-3 py-1.5 text-xs md:text-sm rounded-full border';
    li.innerHTML = `<a href="#" class="${
      disabled
        ? base+' text-slate-400 border-slate-200 cursor-not-allowed dark:border-slate-700'
        : active
          ? base+' text-white bg-rose-500 border-rose-500 shadow-sm shadow-rose-300/70'
          : base+' text-slate-700 border-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-800/80'
    }">${label}</a>`;

    li.querySelector('a').addEventListener('click', e => {
      e.preventDefault();
      if (disabled || active) return;
      state.page = page;
      syncUrlFromState(true); // keep URL updated
      load();
    });
    pager.appendChild(li);
  };

  add('«', 1, current_page === 1);
  add('‹', Math.max(1, current_page - 1), current_page === 1);

  const windowSize = 2;
  const start = Math.max(1, current_page - windowSize);
  const end   = Math.min(last_page, current_page + windowSize);
  for (let i = start; i <= end; i++) add(String(i), i, false, i === current_page);

  add('›', Math.min(last_page, current_page + 1), current_page === last_page);
  add('»', last_page, current_page === last_page);
}

/* Filters */
filterForm.addEventListener('submit', e => {
  e.preventDefault();
  state.status   = elStatus.value;
  state.sort     = elSort.value;
  state.dir      = elDir.value;
  state.per_page = parseInt(elPerPage.value || '10', 10);
  state.from     = elFrom.value || '';
  state.to       = elTo.value || '';
  state.page = 1;

  syncUrlFromState(true);
  load();
});

document.getElementById('resetBtn').addEventListener('click', ()=>{
  filterForm.reset();
  elPerPage.value = '10';
  elSort.value = 'created_at';
  elDir.value = 'desc';

  state.q=''; state.status=''; state.sort='created_at'; state.dir='desc'; state.per_page=10; state.from=''; state.to='';
  state.page=1;
  searchInput.value = '';

  syncUrlFromState(true); // clears URL params
  load();
});

/* Search */
searchInput.addEventListener('input', debounce(() => {
  state.q = searchInput.value.trim();
  state.page = 1;
  syncUrlFromState(true);
  load();
}, 300));

document.getElementById('clearSearch').addEventListener('click', () => {
  searchInput.value = '';
  state.q = '';
  state.page = 1;
  syncUrlFromState(true);
  load();
});

document.getElementById('refreshBtn').addEventListener('click', load);

/* ✅ Init: read query params first, then load */
(function initUI(){
  // defaults
  elPerPage.value = '10';
  elSort.value = 'created_at';
  elDir.value = 'desc';

  hydrateStateFromUrl();
  load();
})();
</script>

@endpush
