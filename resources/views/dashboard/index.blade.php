{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.dashboard_title', [], $locale) ?: 'Dashboard')

@push('head')
<style>
  .no-scrollbar::-webkit-scrollbar{ width:0; height:0; }
  .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }

  .card-sticky-header{ position: sticky; top: 0; z-index: 10; backdrop-filter: blur(16px); }

  .skeleton{ position:relative; overflow:hidden; background:#f3f4f6; border-radius:.75rem; }
  .dark .skeleton{ background: rgba(15,23,42,.9); }
  .skeleton::after{
    content:""; position:absolute; inset:0; transform: translateX(-100%);
    background: linear-gradient(90deg, rgba(248,250,252,0), rgba(226,232,240,.9), rgba(248,250,252,0));
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 100% { transform: translateX(100%); } }

  /* ===== KPI Cards (enhanced) ===== */
  .kpi-wrap{
    position: relative;
    border-radius: 1.5rem;
    padding: 1px;
    background: linear-gradient(135deg,
      rgba(244,114,182,.55),
      rgba(251,146,60,.35),
      rgba(56,189,248,.22)
    );
    transition: transform .15s ease, filter .15s ease;
    cursor: pointer;
  }
  .kpi-wrap:hover{ transform: translateY(-2px); filter: brightness(1.03); }

  .kpi-card{
    position: relative;
    border-radius: 1.5rem;
    padding: 14px 14px 12px;
    background: rgba(255,255,255,.86);
    border: 1px solid rgba(255,255,255,.55);
    box-shadow: 0 18px 40px rgba(244,114,182,.12);
    overflow: hidden;
    min-height: 118px;
  }
  .dark .kpi-card{
    background: radial-gradient(1200px 180px at 20% 0%,
      rgba(244,114,182,.14),
      rgba(2,6,23,.55) 40%,
      rgba(2,6,23,.72) 100%
    );
    border: 1px solid rgba(148,163,184,.18);
    box-shadow: 0 22px 55px rgba(0,0,0,.35);
  }

  .kpi-top{
    display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem;
    margin-bottom: 10px;
  }

  .kpi-title{
    display:flex; align-items:center; gap:.55rem;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .02em;
    color: rgba(100,116,139,1);
  }
  .dark .kpi-title{ color: rgba(148,163,184,1); }

  .kpi-icon{
    height: 30px; width: 30px;
    display:grid; place-items:center;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.55);
    box-shadow: 0 10px 18px rgba(0,0,0,.08);
  }
  .dark .kpi-icon{
    border: 1px solid rgba(148,163,184,.22);
    box-shadow: 0 12px 24px rgba(0,0,0,.25);
  }

  .kpi-go{
    height: 28px; width: 28px;
    display:inline-flex; align-items:center; justify-content:center;
    border-radius: 9999px;
    border: 1px solid rgba(148,163,184,.25);
    color: rgba(100,116,139,1);
    background: rgba(255,255,255,.35);
    transition: transform .15s ease, opacity .15s ease;
  }
  .dark .kpi-go{
    color: rgba(226,232,240,1);
    background: rgba(2,6,23,.18);
    border: 1px solid rgba(148,163,184,.18);
  }
  .kpi-wrap:hover .kpi-go{ transform: translateX(2px); }

  .kpi-value{
    font-size: 26px;
    line-height: 1.15;
    font-weight: 800;
    color: rgba(15,23,42,1);
  }
  .dark .kpi-value{ color: rgba(248,250,252,1); }

  .kpi-sub{
    margin-top: 6px;
    font-size: 11px;
    color: rgba(148,163,184,1);
  }
  .dark .kpi-sub{ color: rgba(148,163,184,1); }

  /* clickable rows */
  .row-link{ cursor:pointer; }
  .row-link:hover{ background: rgba(244,114,182,.06); }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Header --}}
  <div class="shrink-0 flex flex-col md:flex-row md:items-end md:justify-between gap-2">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        📊 {{ __('messages.dashboard_title', [], $locale) ?: 'Dashboard' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.dashboard_subtitle', [], $locale) ?: 'Today summary + quick insights from Orders and Stock.' }}
      </p>
      <p id="dashMeta" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button id="refreshBtn"
        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
               dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
               px-3 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
               hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition">
        <i class="bi bi-arrow-clockwise text-[12px]"></i>
        <span>{{ __('messages.refresh', [], $locale) ?: 'Refresh' }}</span>
      </button>

      <a href="{{ url('/orders') }}"
        class="inline-flex items-center gap-1.5 rounded-full
               bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
               px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
               shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
        <i class="bi bi-receipt text-[12px]"></i>
        <span>{{ __('messages.view_orders', [], $locale) ?: 'Orders' }}</span>
      </a>

      <a href="{{ url('/ingredients') }}"
        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
               dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
               px-3.5 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
               hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition">
        <i class="bi bi-box-seam text-[12px]"></i>
        <span>{{ __('messages.view_stock', [], $locale) ?: 'Stock' }}</span>
      </a>
    </div>
  </div>

  {{-- KPI Cards --}}
  <div class="shrink-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

    {{-- Sales --}}
    <div id="kpiCardSales" class="kpi-wrap">
      <div class="kpi-card">
        <div class="kpi-top">
          <div class="kpi-title">
            <span class="kpi-icon bg-rose-50 text-rose-600 dark:bg-rose-900/25 dark:text-rose-200">
              <i class="bi bi-cash-coin"></i>
            </span>
            <span>{{ __('messages.today_sales', [], $locale) ?: "Today's Sales" }}</span>
          </div>
          <span class="kpi-go" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
        </div>

        <div id="kpiSales" class="kpi-value">
          <div class="skeleton h-7 w-32"></div>
        </div>
        <div id="kpiSalesSub" class="kpi-sub"></div>
      </div>
    </div>

    {{-- Orders --}}
    <div id="kpiCardOrders" class="kpi-wrap">
      <div class="kpi-card">
        <div class="kpi-top">
          <div class="kpi-title">
            <span class="kpi-icon bg-amber-50 text-amber-700 dark:bg-amber-900/25 dark:text-amber-200">
              <i class="bi bi-bag-check"></i>
            </span>
            <span>{{ __('messages.orders_today', [], $locale) ?: 'Orders (Today)' }}</span>
          </div>
          <span class="kpi-go" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
        </div>

        <div id="kpiOrders" class="kpi-value">
          <div class="skeleton h-7 w-20"></div>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
          <span id="kpiOrdersSub" class="text-slate-400 dark:text-slate-500"></span>

          <a id="ordersPaidLink" href="{{ url('/orders') }}"
             class="inline-flex items-center gap-1 rounded-full border border-emerald-200 dark:border-emerald-900/40
                    bg-emerald-50/60 dark:bg-emerald-900/20 px-2 py-0.5
                    text-emerald-700 dark:text-emerald-200 hover:opacity-80">
            {{ __('messages.orders_status_paid', [], $locale) ?: 'Paid' }}
          </a>

          <a id="ordersUnpaidLink" href="{{ url('/orders') }}"
             class="inline-flex items-center gap-1 rounded-full border border-amber-200 dark:border-amber-900/40
                    bg-amber-50/60 dark:bg-amber-900/20 px-2 py-0.5
                    text-amber-800 dark:text-amber-200 hover:opacity-80">
            {{ __('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid' }}
          </a>
        </div>
      </div>
    </div>

    {{-- Avg Order --}}
    <div id="kpiCardAov" class="kpi-wrap">
      <div class="kpi-card">
        <div class="kpi-top">
          <div class="kpi-title">
            <span class="kpi-icon bg-emerald-50 text-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-200">
              <i class="bi bi-graph-up-arrow"></i>
            </span>
            <span>{{ __('messages.avg_order', [], $locale) ?: 'Avg. Order' }}</span>
          </div>
          <span class="kpi-go" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
        </div>

        <div id="kpiAov" class="kpi-value">
          <div class="skeleton h-7 w-24"></div>
        </div>
        <div id="kpiAovSub" class="kpi-sub"></div>
      </div>
    </div>

    {{-- Cashiers --}}
    <div id="kpiCardCashiers" class="kpi-wrap">
      <div class="kpi-card">
        <div class="kpi-top">
          <div class="kpi-title">
            <span class="kpi-icon bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
              <i class="bi bi-people"></i>
            </span>
            <span>{{ __('messages.cashiers_today', [], $locale) ?: 'Cashiers (Today)' }}</span>
          </div>
          <span class="kpi-go" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
        </div>

        <div id="kpiCashiers" class="kpi-value">
          <div class="skeleton h-7 w-16"></div>
        </div>
        <div id="kpiCashiersSub" class="kpi-sub"></div>
      </div>
    </div>

  </div>

  {{-- Main Grid --}}
  <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-3 gap-3">

    {{-- Recent Orders --}}
    <div class="lg:col-span-2 rounded-2xl border border-white/60 dark:border-slate-800/80
                bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/30 backdrop-blur-2xl
                overflow-hidden flex flex-col min-h-0">
      <div class="card-sticky-header px-4 md:px-5 py-3 border-b border-white/60 dark:border-slate-800/80
                  bg-gradient-to-r from-white/95 via-white/80 to-white/70
                  dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
        <div class="flex items-center justify-between gap-2">
          <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1 dark:bg-slate-800">
            <i class="bi bi-clock-history text-[12px] text-rose-300"></i>
            <span>{{ __('messages.recent_orders', [], $locale) ?: 'Recent Orders' }}</span>
          </div>
          <a id="recentOrdersViewAll" href="{{ url('/orders') }}"
             class="text-[11px] md:text-xs text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-200">
            {{ __('messages.view_all', [], $locale) ?: 'View all' }} →
          </a>
        </div>
      </div>

      <div class="flex-1 min-h-0 overflow-auto no-scrollbar">
        <table class="min-w-full text-xs md:text-sm">
          <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                        text-slate-500 dark:text-slate-400
                        bg-slate-50/95 dark:bg-slate-950/90 backdrop-blur
                        border-b border-slate-200/70 dark:border-slate-800/80">
            <tr>
              <th class="px-3 py-2 text-left font-medium w-[24%]">{{ __('messages.orders_col_code', [], $locale) ?: 'Order' }}</th>
              <th class="px-3 py-2 text-left font-medium w-[28%]">{{ __('messages.orders_col_cashier', [], $locale) ?: 'Cashier' }}</th>
              <th class="px-3 py-2 text-left font-medium w-[20%]">{{ __('messages.time', [], $locale) ?: 'Time' }}</th>
              <th class="px-3 py-2 text-right font-medium w-[16%]">{{ __('messages.orders_col_total', [], $locale) ?: 'Total' }}</th>
              <th class="px-3 py-2 text-center font-medium w-[12%]">{{ __('messages.orders_col_status', [], $locale) ?: 'Status' }}</th>
            </tr>
          </thead>
          <tbody id="recentOrderRows" class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
              {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>

    {{-- Low Stock --}}
    <div class="lg:col-span-1 rounded-2xl border border-white/60 dark:border-slate-800/80
                bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/30 backdrop-blur-2xl
                overflow-hidden flex flex-col min-h-0">
      <div class="card-sticky-header px-4 md:px-5 py-3 border-b border-white/60 dark:border-slate-800/80
                  bg-gradient-to-r from-white/95 via-white/80 to-white/70
                  dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
        <div class="flex items-center justify-between gap-2">
          <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1 dark:bg-slate-800">
            <i class="bi bi-exclamation-diamond text-[12px] text-rose-300"></i>
            <span>{{ __('messages.low_stock', [], $locale) ?: 'Low Stock' }}</span>
          </div>
          <a id="lowStockViewAll" href="{{ url('/ingredients') }}"
             class="text-[11px] md:text-xs text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-200">
            {{ __('messages.view_all', [], $locale) ?: 'View all' }} →
          </a>
        </div>
      </div>

      <div id="lowStockList" class="flex-1 min-h-0 overflow-auto no-scrollbar p-4 md:p-5">
        <div class="skeleton h-6 w-full mb-2"></div>
        <div class="skeleton h-6 w-5/6 mb-2"></div>
        <div class="skeleton h-6 w-2/3"></div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
/* ===== Helpers ===== */
function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g,'&quot;'); }
function fmtKHR(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US') + '៛';
}
function fmtNum(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function fmtDate(dt){ try { return dt ? new Date(dt).toLocaleString() : '—'; } catch { return '—'; } }

function qs(obj){
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k,v])=>{
    if (v === '' || v === null || v === false || v === undefined) return;
    p.set(k, String(v));
  });
  return p.toString();
}

function pill(label, kind='slate'){
  const map = {
    slate: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    green: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-200',
    amber: 'bg-amber-50 text-amber-700 dark:bg-amber-900/25 dark:text-amber-200',
    rose:  'bg-rose-50 text-rose-700 dark:bg-rose-900/25 dark:text-rose-200',
  };
  return `<span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[11px] ${map[kind] || map.slate}">${esc(label)}</span>`;
}

const I18N = {
  loading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  viewAll: @json(__('messages.view_all', [], $locale) ?: 'View all'),
  noOrders: @json(__('messages.orders_empty_title', [], $locale) ?: 'No orders found'),
  paid: @json(__('messages.orders_status_paid', [], $locale) ?: 'Paid'),
  unpaid: @json(__('messages.orders_status_unpaid', [], $locale) ?: 'Unpaid'),
  ok: @json(__('messages.ingredients_status_ok', [], $locale) ?: 'OK'),
  low: @json(__('messages.ingredients_status_low', [], $locale) ?: 'Low'),
};

const els = {
  meta: document.getElementById('dashMeta'),
  kpiSales: document.getElementById('kpiSales'),
  kpiSalesSub: document.getElementById('kpiSalesSub'),
  kpiOrders: document.getElementById('kpiOrders'),
  kpiOrdersSub: document.getElementById('kpiOrdersSub'),
  kpiAov: document.getElementById('kpiAov'),
  kpiAovSub: document.getElementById('kpiAovSub'),
  kpiCashiers: document.getElementById('kpiCashiers'),
  kpiCashiersSub: document.getElementById('kpiCashiersSub'),
  recentOrderRows: document.getElementById('recentOrderRows'),
  lowStockList: document.getElementById('lowStockList'),

  cardSales: document.getElementById('kpiCardSales'),
  cardOrders: document.getElementById('kpiCardOrders'),
  cardAov: document.getElementById('kpiCardAov'),
  cardCashiers: document.getElementById('kpiCardCashiers'),

  ordersPaidLink: document.getElementById('ordersPaidLink'),
  ordersUnpaidLink: document.getElementById('ordersUnpaidLink'),
  recentOrdersViewAll: document.getElementById('recentOrdersViewAll'),
  lowStockViewAll: document.getElementById('lowStockViewAll'),
};

function todayISO(){
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth()+1).padStart(2,'0');
  const dd = String(d.getDate()).padStart(2,'0');
  return `${yyyy}-${mm}-${dd}`;
}

function ordersUrl(extra = {}){
  const date = todayISO();
  const base = '/orders';
  const params = { from: date, to: date, ...extra };
  return base + '?' + qs(params);
}

function ingredientsUrl(extra = {}){
  const base = '/ingredients';
  return base + '?' + qs(extra);
}

/* ===== API wrappers ===== */
async function fetchOrders(params){
  return await api('/api/orders?' + qs(params));
}
async function fetchIngredients(params){
  return await api('/api/ingredients?' + qs(params));
}

/* Fetch all pages (used only for today's KPI calc) */
async function fetchAllOrdersToday(maxPages=12){
  const date = todayISO();
  let page = 1;
  let all = [];
  let last = 1;

  while (page <= last && page <= maxPages) {
    const res = await fetchOrders({
      from: date,
      to: date,
      sort: 'created_at',
      dir: 'desc',
      per_page: 100,
      page
    });
    const list = res?.data || [];
    all = all.concat(list);
    last = Number(res?.last_page ?? 1);
    page++;
    if (!list.length) break;
  }
  return all;
}

/* ===== Renderers ===== */
function renderRecentOrders(list){
  if (!list.length){
    els.recentOrderRows.innerHTML = `
      <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
        <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
        ${esc(I18N.noOrders)}
      </td></tr>`;
    return;
  }

  els.recentOrderRows.innerHTML = list.map(o => {
    const cashier = o.user?.name || o.user?.email || '—';
    const isPaid = (o.status === 'paid') || !!o.is_paid || (Number(o.due_khr ?? 0) <= 0);
    const statusHtml = isPaid ? pill(I18N.paid,'green') : pill(I18N.unpaid,'amber');
    const when = o.created_at ? new Date(o.created_at).toLocaleString() : '—';
    const go = `/orders/${Number(o.id)}`;

    return `
      <tr class="align-middle row-link" onclick="window.location.href='${escAttr(go)}'">
        <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-50">${esc(o.order_code || ('#'+o.id))}</td>
        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(cashier)}</td>
        <td class="px-3 py-2 text-slate-500 dark:text-slate-400">${esc(when)}</td>
        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtKHR(o.total_khr))}</td>
        <td class="px-3 py-2 text-center">${statusHtml}</td>
      </tr>`;
  }).join('');
}

function renderLowStock(list){
  if (!list.length){
    els.lowStockList.innerHTML = `
      <div class="text-sm text-slate-500 dark:text-slate-400">
        <i class="bi bi-check2-circle me-1 text-emerald-600"></i>
        ${esc(I18N.ok)}
      </div>`;
    return;
  }

  els.lowStockList.innerHTML = `
    <div class="space-y-3">
      ${list.map(i => {
        const status = i.is_low ? pill(I18N.low,'rose') : pill(I18N.ok,'green');
        const go = ingredientsUrl({ q: i.name || '', low_only: 1 });
        return `
          <div class="flex items-start justify-between gap-3 p-2 rounded-xl hover:bg-rose-500/5 cursor-pointer"
               onclick="window.location.href='${escAttr(go)}'">
            <div class="min-w-0">
              <div class="text-sm font-semibold text-slate-900 dark:text-slate-50 truncate">${esc(i.name)}</div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400">
                ${esc(fmtNum(i.current_qty))} ${esc(i.unit || '')}
                <span class="opacity-60">•</span>
                Low: ${esc(fmtNum(i.low_alert_qty))}
              </div>
              <div class="text-[11px] text-slate-400 dark:text-slate-500">
                Restocked: ${esc(fmtDate(i.last_restocked_at))}
              </div>
            </div>
            <div class="shrink-0">${status}</div>
          </div>
        `;
      }).join('')}
    </div>`;
}

/* ===== Main loader ===== */
async function loadDashboard(){
  els.meta.textContent = I18N.loading;

  // set “View all” links with useful defaults
  els.recentOrdersViewAll.href = ordersUrl();
  els.lowStockViewAll.href = ingredientsUrl({ low_only: 1 });

  // KPI click navigation (today)
  const goOrdersToday = () => window.location.href = ordersUrl();
  els.cardSales.onclick = goOrdersToday;
  els.cardOrders.onclick = goOrdersToday;
  els.cardAov.onclick = goOrdersToday;
  els.cardCashiers.onclick = goOrdersToday;

  // Paid / Unpaid quick links (today)
  els.ordersPaidLink.href = ordersUrl({ status: 'paid' });
  els.ordersUnpaidLink.href = ordersUrl({ status: 'unpaid' });

  try {
    const recentRes = await fetchOrders({ sort:'created_at', dir:'desc', per_page: 8, page: 1 });
    renderRecentOrders(recentRes?.data || []);

    const lowRes = await fetchIngredients({ low_only: 1, sort:'current_qty', dir:'asc', per_page: 8, page: 1 });
    renderLowStock(lowRes?.data || []);

    const todays = await fetchAllOrdersToday();

    const totalSales = todays.reduce((sum, o) => sum + Number(o.total_khr ?? 0), 0);
    const ordersCount = todays.length;
    const avgOrder = ordersCount ? (totalSales / ordersCount) : 0;

    const cashierSet = new Set(
      todays.map(o => (o.user?.email || o.user?.name || '')).filter(Boolean)
    );
    const cashiers = cashierSet.size;

    const paidCount = todays.filter(o => (o.status === 'paid') || !!o.is_paid || (Number(o.due_khr ?? 0) <= 0)).length;

    els.kpiSales.textContent = fmtKHR(totalSales);
    els.kpiSalesSub.textContent = `${paidCount}/${ordersCount} ${I18N.paid.toLowerCase()}`;

    els.kpiOrders.textContent = String(ordersCount);
    els.kpiOrdersSub.textContent = `${I18N.paid}: ${paidCount} • ${I18N.unpaid}: ${Math.max(0, ordersCount - paidCount)}`;

    els.kpiAov.textContent = fmtKHR(avgOrder);
    els.kpiAovSub.textContent = @json(__('messages.avg_order_sub', [], $locale) ?: 'per order');

    els.kpiCashiers.textContent = String(cashiers);
    els.kpiCashiersSub.textContent = @json(__('messages.cashiers_today_sub', [], $locale) ?: 'unique in today orders');

    els.meta.textContent = `Updated: ${new Date().toLocaleString()}`;

  } catch (err) {
    console.error(err);
    els.meta.textContent = 'Failed to load';
    if (typeof showToast === 'function') showToast(err?.data?.message || 'Dashboard load failed', 'danger');
  }
}

document.getElementById('refreshBtn')?.addEventListener('click', loadDashboard);
loadDashboard();
</script>
@endpush
