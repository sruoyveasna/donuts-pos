{{-- resources/views/discounts/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.discounts_title', [], $locale) ?: 'Discounts')

@push('head')
<style>
  dialog[open]{
    position: fixed;
    inset: 0;
    margin: auto;
    display: grid;
    place-items: center;
    width: 100vw;
    height: 100vh;
  }

  /* Sticky header inside the card (toolbar stays visible while scrolling table) */
  .card-sticky-header { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(16px); }
  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  #rows tr:hover { background: rgba(244,114,182,.05); }

  /* Tailwind-ish skeleton blocks */
  .skeleton { position: relative; overflow: hidden; background: #f3f4f6; border-radius: .375rem; }
  .dark .skeleton { background: rgba(15,23,42,.9); }
  .skeleton::after {
    content: "";
    position: absolute;
    inset: 0;
    transform: translateX(-100%);
    background: linear-gradient(90deg, rgba(248,250,252,0), rgba(226,232,240,.9), rgba(248,250,252,0));
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 100% { transform: translateX(100%); } }

  /* ✅ icon-only buttons (consistent with template) */
  .icon-btn{
    height: 32px;
    width: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
  }

  /* ✅ Hide scrollbar but keep scroll */
  .no-scrollbar::-webkit-scrollbar { width: 0px; height: 0px; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
{{-- ✅ Fill available height + only table area scrolls --}}
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Page heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🏷️ {{ __('messages.discounts_title', [], $locale) ?: 'Discounts' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.discounts_subtitle', [], $locale) ?: 'Create promo codes and automatic discounts for your POS.' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        {{ __('messages.active', [], $locale) ?: 'Active' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
        {{ __('messages.inactive', [], $locale) ?: 'Inactive' }}
      </span>
    </div>
  </div>

  {{-- Card --}}
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
              backdrop-blur-2xl overflow-hidden
              flex flex-col flex-1 min-h-0">

    {{-- Toolbar (sticky) --}}
    <div class="card-sticky-header px-4 md:px-5 py-3 md:py-3.5
                border-b border-white/60 dark:border-slate-800/80
                bg-gradient-to-r from-white/95 via-white/80 to-white/70
                dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
      <div class="flex flex-wrap items-center gap-2">

        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-tags text-[12px] text-rose-300"></i>
          <span>{{ __('messages.discounts_manager', [], $locale) ?: 'Discount manager' }}</span>
        </div>

        <div class="flex-1"></div>

        {{-- Search --}}
        <div class="grow md:grow-0 min-w-[220px] max-w-[320px]">
          <div class="flex items-stretch rounded-full border border-slate-200 dark:border-slate-700
                      bg-slate-50/80 dark:bg-slate-900/80
                      focus-within:border-rose-400 focus-within:ring-1 focus-within:ring-rose-300">
            <span class="px-2 flex items-center text-slate-400">
              <i class="bi bi-search text-[13px]"></i>
            </span>
            <input id="q" name="q"
              class="w-full px-2 py-1.5 text-xs md:text-sm outline-none rounded-e-full
                     bg-transparent text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
              placeholder="{{ __('messages.discounts_search_placeholder', [], $locale) ?: 'Search by name or code…' }}">
            <button class="tw-tip px-2 text-slate-400 hover:text-rose-500"
              id="clearSearch" type="button"
              data-tooltip="{{ __('messages.clear', [], $locale) ?: 'Clear' }}">
              <i class="bi bi-x-lg text-[11px]"></i>
            </button>
          </div>
        </div>

        {{-- Filters --}}
        <button id="filterToggle" type="button"
          class="tw-tip inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                 dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                 px-2.5 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                 hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition"
          data-tooltip="{{ __('messages.filters', [], $locale) ?: 'Filters' }}">
          <i class="bi bi-sliders text-[12px]"></i>
          <span class="hidden sm:inline">{{ __('messages.filters', [], $locale) ?: 'Filters' }}</span>
        </button>

        {{-- Refresh --}}
        <button class="tw-tip inline-flex items-center justify-center rounded-full border border-slate-300/80
                       dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                       h-8 w-8 text-slate-600 dark:text-slate-200 hover:text-rose-500 hover:border-rose-400 transition"
                id="refreshBtn" type="button"
                data-tooltip="{{ __('messages.refresh', [], $locale) ?: 'Refresh' }}">
          <i class="bi bi-arrow-clockwise text-[13px]"></i>
        </button>

        {{-- New --}}
        @if($canWrite)
        <button id="openCreateBtn"
          class="inline-flex items-center gap-1.5 rounded-full
                 bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                 px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
                 shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-plus-lg text-[12px]"></i>
          <span class="hidden sm:inline">{{ __('messages.new_discount', [], $locale) ?: 'New Discount' }}</span>
          <span class="sm:hidden">{{ __('messages.new', [], $locale) ?: 'New' }}</span>
        </button>
        @endif
      </div>
    </div>

    {{-- Filters panel --}}
    <div id="filterPanel" class="hidden shrink-0">
      <div class="px-4 md:px-5 pt-3 pb-3.5">
        <form id="filterForm" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end text-[11px] md:text-xs">
          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.sort_by', [], $locale) ?: 'Sort by' }}
            </label>
            <select id="sort" name="sort"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
              <option value="created_at">Created</option>
              <option value="name">Name</option>
              <option value="code">Code</option>
              <option value="is_active">Active</option>
              <option value="starts_at">Starts</option>
              <option value="ends_at">Ends</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.direction', [], $locale) ?: 'Direction' }}
            </label>
            <select id="dir" name="dir"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
              <option value="desc">DESC</option>
              <option value="asc">ASC</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.per_page', [], $locale) ?: 'Per page' }}
            </label>
            <select id="per_page" name="per_page"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
              <option>10</option><option>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="col-span-2">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.visibility', [], $locale) ?: 'Visibility' }}
            </label>
            <div class="flex flex-wrap gap-4 mt-1.5">
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70"
                       type="checkbox" id="active_only" name="active_only">
                <span>{{ __('messages.active_only', [], $locale) ?: 'Active only' }}</span>
              </label>
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70"
                       type="checkbox" id="with_trashed" name="with_trashed">
                <span>{{ __('messages.include_archived', [], $locale) ?: 'Include archived' }}</span>
              </label>
            </div>
          </div>

          <div class="col-span-2 sm:col-span-1 sm:ml-auto">
            <div class="flex gap-2 justify-end">
              <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300
                       px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
                <i class="bi bi-funnel text-[12px]"></i>
                <span>{{ __('messages.apply', [], $locale) ?: 'Apply' }}</span>
              </button>

              <button type="button" id="resetBtn"
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400
                       px-3 py-1.5 text-[11px] text-slate-800 hover:bg-slate-100
                       dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800">
                <i class="bi bi-arrow-counterclockwise text-[12px]"></i>
                <span>{{ __('messages.reset', [], $locale) ?: 'Reset' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="soft-divider"></div>
    </div>

    {{-- ✅ Scroll area --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm border-collapse">
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/90 dark:bg-slate-950/80 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium w-[26%]">Name</th>
            <th class="px-3 py-2 text-left font-medium w-[16%]">Code</th>
            <th class="px-3 py-2 text-left font-medium w-[18%]">Type / Value</th>
            <th class="px-3 py-2 text-center font-medium w-[12%]">Status</th>
            <th class="px-3 py-2 text-left font-medium w-[16%]">Schedule</th>
            <th class="px-3 py-2 text-right font-medium w-[12%]">Actions</th>
          </tr>
        </thead>
        <tbody id="rows" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr>
            <td colspan="6" class="py-6 text-center text-slate-500 dark:text-slate-400">
              {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Footer / Pagination --}}
    <div
      class="shrink-0 flex flex-wrap items-center justify-between gap-2
             px-4 md:px-5 py-2
             border-t border-white/60 dark:border-slate-800/80
             bg-white/70 dark:bg-slate-950/35 backdrop-blur-xl">
      <small id="pageMeta" class="text-[11px] text-slate-500 dark:text-slate-400"></small>
      <nav class="w-full sm:w-auto">
        <ul id="pager" class="inline-flex items-center gap-1 justify-center sm:justify-end w-full"></ul>
      </nav>
    </div>
  </div>
</div>

@include('discounts.partials.create-modal', ['canWrite' => $canWrite])
@include('discounts.partials.edit-modal',   ['canWrite' => $canWrite])
@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);

// ✅ UI adapter (page only depends on AppUI)
const UI = {
  toast(message, type='primary') {
    if (window.AppUI?.toast) return window.AppUI.toast(message, type);
    // fallback (should exist because toast component sets showToast)
    if (typeof window.showToast === 'function') return window.showToast(message, type);
    console.log(`[${type}]`, message);
  },
  async confirm(opts) {
    if (window.AppUI?.confirm) return await window.AppUI.confirm(opts);
    return window.confirm(opts?.message || 'Are you sure?');
  }
};

const DISC_I18N = {
  summaryLoading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  summaryRange: @json(__('messages.categories_summary_range', [], $locale) ?: 'Showing :from–:to of :total'),
  active: @json(__('messages.active', [], $locale) ?: 'Active'),
  inactive: @json(__('messages.inactive', [], $locale) ?: 'Inactive'),
  loadFailedTitle: @json(__('messages.load_failed', [], $locale) ?: "Couldn’t load discounts"),
  loadFailedMessage: @json(__('messages.try_again', [], $locale) ?: 'Please try again.'),
  retry: @json(__('messages.retry', [], $locale) ?: 'Retry'),
  emptyTitle: @json(__('messages.no_results', [], $locale) ?: 'No discounts found'),
  emptyBody: @json(__('messages.adjust_filters', [], $locale) ?: 'Try adjusting your filters or add a new discount.'),
  tooltipRestore: @json(__('messages.restore', [], $locale) ?: 'Restore'),
  tooltipEdit: @json(__('messages.edit', [], $locale) ?: 'Edit'),
  tooltipArchive: @json(__('messages.archive', [], $locale) ?: 'Archive'),
  newButton: @json(__('messages.new_discount', [], $locale) ?: 'New Discount'),

  toastCreated: @json(__('messages.created', [], $locale) ?: 'Created'),
  toastCreateFailed: @json(__('messages.create_failed', [], $locale) ?: 'Create failed'),
  toastUpdated: @json(__('messages.updated', [], $locale) ?: 'Updated'),
  toastUpdateFailed: @json(__('messages.update_failed', [], $locale) ?: 'Update failed'),
  toastArchived: @json(__('messages.archived', [], $locale) ?: 'Archived'),
  toastArchiveFailed: @json(__('messages.archive_failed', [], $locale) ?: 'Archive failed'),
  toastRestored: @json(__('messages.restored', [], $locale) ?: 'Restored'),
  toastRestoreFailed: @json(__('messages.restore_failed', [], $locale) ?: 'Restore failed'),

  confirmArchive: @json(__('messages.confirm_archive', [], $locale) ?: 'Archive this discount?'),
};

const state = {
  q: '',
  sort: 'created_at',
  dir: 'desc',
  per_page: 10,
  with_trashed: false,
  active_only: false,
  page: 1,
};

const rows        = document.getElementById('rows');
const pager       = document.getElementById('pager');
const pageMeta    = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm  = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

document.getElementById('filterToggle').addEventListener('click', () => {
  document.getElementById('filterPanel').classList.toggle('hidden');
});
document.getElementById('openCreateBtn')?.addEventListener('click', () => {
  openDialog(document.getElementById('createDiscountModal'));
});

function qs(obj){
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k,v])=>{
    if (v === '' || v === null || v === false) return;
    p.set(k, String(v));
  });
  return p.toString();
}
function fmtRange(p){
  if (!p?.total) return '';
  const from  = ((p.current_page - 1) * p.per_page) + 1;
  const to    = Math.min(p.current_page * p.per_page, p.total);
  const total = p.total;
  return DISC_I18N.summaryRange.replace(':from', from).replace(':to', to).replace(':total', total);
}

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g, '&quot;'); }
function js(s){ return JSON.stringify(s ?? ''); }
function debounce(fn, ms=350){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), ms); } }

function moneyKHR(v){
  if (v === null || typeof v === 'undefined') return '—';
  const n = Number(v);
  if (Number.isNaN(n)) return String(v);
  return n.toLocaleString() + '៛';
}

function typeLabel(d){
  if (d.type === 'percent') return `${Number(d.value).toString()}%`;
  return moneyKHR(d.value);
}

function scheduleLabel(d){
  const s = d.starts_at ? new Date(d.starts_at) : null;
  const e = d.ends_at ? new Date(d.ends_at) : null;

  const fmt = (dt) => {
    try { return dt.toISOString().slice(0,10); } catch { return ''; }
  };

  if (!s && !e) return '—';
  if (s && !e) return `from ${fmt(s)}`;
  if (!s && e) return `until ${fmt(e)}`;
  return `${fmt(s)} → ${fmt(e)}`;
}

function skeleton(){
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-10">
      <td class="px-3"><div class="skeleton h-4 w-3/4"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-2/3"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-1/2"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-1/3 mx-auto"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-3/4"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-1/3 inline-block"></div></td>
    </tr>
  `).join('');
}

async function load(){
  skeleton();
  const params = {
    q: state.q,
    sort: state.sort,
    dir: state.dir,
    per_page: state.per_page,
    with_trashed: state.with_trashed ? 1 : 0,
    active_only: state.active_only ? 1 : 0,
    page: state.page,
  };
  try {
    const res = await api('/api/discounts?' + qs(params));
    render(res);
  } catch (e) {
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(DISC_I18N.loadFailedTitle)}</div>
          <div class="text-sm mb-3">${esc(DISC_I18N.loadFailedMessage)}</div>
          <button id="retryBtn"
            class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${esc(DISC_I18N.retry)}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = DISC_I18N.loadFailedTitle;
    pageMeta.textContent = '';
  }
}

function render(paged){
  const list = paged.data || [];
  if (!list.length) {
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(DISC_I18N.emptyTitle)}</div>
          <div class="text-sm">${esc(DISC_I18N.emptyBody)}</div>
          ${CAN_WRITE ? `
            <button class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-3.5 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition"
                    id="openCreateBtnEmpty">
              <i class="bi bi-plus-lg text-[12px]"></i> ${esc(DISC_I18N.newButton)}
            </button>` : ''}
        </div>
      </td></tr>`;
    document.getElementById('openCreateBtnEmpty')?.addEventListener('click', () => {
      openDialog(document.getElementById('createDiscountModal'));
    });
  } else {
    rows.innerHTML = list.map(d => {
      const isTrashed = !!d.deleted_at;
      const status = d.is_active
        ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span> ${esc(DISC_I18N.active)}
           </span>`
        : `<span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-slate-400"></span> ${esc(DISC_I18N.inactive)}
           </span>`;

      let actions = '';
      if (CAN_WRITE) {
        if (isTrashed) {
          actions = `
            <div class="flex items-center gap-2 justify-end flex-nowrap whitespace-nowrap">
              <button onclick='onRestore(${d.id})'
                class="tw-tip icon-btn border border-emerald-600 text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring focus:ring-emerald-200"
                data-tooltip="${escAttr(DISC_I18N.tooltipRestore)}"
                aria-label="${escAttr(DISC_I18N.tooltipRestore)}">
                <i class="bi bi-arrow-counterclockwise text-[14px] leading-none"></i>
              </button>
            </div>`;
        } else {
          actions = `
            <div class="flex items-center gap-2 justify-end flex-nowrap whitespace-nowrap">
              <button
                onclick='openEdit(${d.id}, ${js(d.name)}, ${js(d.code)}, ${js(d.type)}, ${js(d.value)}, ${js(d.min_subtotal_khr)}, ${js(d.max_discount_khr)}, ${js(d.usage_limit)}, ${js(d.starts_at)}, ${js(d.ends_at)}, ${d.is_active ? 'true':'false'})'
                class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200"
                data-tooltip="${escAttr(DISC_I18N.tooltipEdit)}"
                aria-label="${escAttr(DISC_I18N.tooltipEdit)}">
                <i class="bi bi-pencil text-[14px] leading-none"></i>
              </button>

              <button onclick='onArchive(${d.id})'
                class="tw-tip icon-btn border border-rose-600 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring focus:ring-rose-200"
                data-tooltip="${escAttr(DISC_I18N.tooltipArchive)}"
                aria-label="${escAttr(DISC_I18N.tooltipArchive)}">
                <i class="bi bi-archive text-[14px] leading-none"></i>
              </button>
            </div>`;
        }
      }

      return `
        <tr class="align-middle">
          <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-50">
            ${esc(d.name)}
            ${d.usage_limit ? `<div class="text-[11px] text-slate-400">limit: ${esc(String(d.usage_limit))}</div>` : ``}
          </td>
          <td class="px-3 py-2 text-slate-500 dark:text-slate-400">
            ${d.code ? `<span class="font-mono text-[12px]">${esc(d.code)}</span>` : '—'}
          </td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
            <span class="font-medium">${esc(d.type === 'percent' ? 'Percent' : 'Fixed (KHR)')}</span>
            <span class="text-slate-400"> · </span>
            <span class="font-semibold">${esc(typeLabel(d))}</span>
            ${(d.min_subtotal_khr || d.max_discount_khr) ? `
              <div class="text-[11px] text-slate-400">
                ${d.min_subtotal_khr ? `min ${esc(moneyKHR(d.min_subtotal_khr))}` : ''}
                ${(d.min_subtotal_khr && d.max_discount_khr) ? ' · ' : ''}
                ${d.max_discount_khr ? `cap ${esc(moneyKHR(d.max_discount_khr))}` : ''}
              </div>` : ``}
          </td>
          <td class="px-3 py-2 text-center">${status}</td>
          <td class="px-3 py-2 text-slate-500 dark:text-slate-400">${esc(scheduleLabel(d))}</td>
          <td class="px-3 py-2 text-right">${actions}</td>
        </tr>`;
    }).join('');
  }

  const meta = fmtRange(paged);
  summaryText.textContent = meta || '—';
  pageMeta.textContent = meta || '';
  buildPager(paged);
}

function buildPager(p){
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
    const a = li.querySelector('a');
    a.addEventListener('click', e => {
      e.preventDefault();
      if (disabled || active) return;
      state.page = page;
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

filterForm.addEventListener('submit', e => {
  e.preventDefault();
  state.sort = document.getElementById('sort').value;
  state.dir = document.getElementById('dir').value;
  state.per_page = parseInt(document.getElementById('per_page').value || '10', 10);
  state.active_only = document.getElementById('active_only').checked;
  state.with_trashed = document.getElementById('with_trashed').checked;
  state.page = 1;
  load();
});

document.getElementById('resetBtn').addEventListener('click', ()=>{
  filterForm.reset();
  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value = 'created_at';
  document.getElementById('dir').value = 'desc';
  state.sort='created_at'; state.dir='desc'; state.per_page=10;
  state.active_only=false; state.with_trashed=false;
  state.page=1;
  load();
});

searchInput.addEventListener('input', debounce(() => {
  state.q = searchInput.value.trim();
  state.page = 1;
  load();
}, 300));

document.getElementById('clearSearch').addEventListener('click', () => {
  searchInput.value = '';
  state.q = '';
  state.page = 1;
  load();
});

document.getElementById('refreshBtn').addEventListener('click', load);

@if($canWrite)
// CREATE
document.getElementById('createDiscountForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const f = e.target;

  const payload = {
    name: f.name.value.trim(),
    code: f.code.value.trim() || null,
    type: f.type.value,
    value: Number(f.value.value || 0),
    min_subtotal_khr: f.min_subtotal_khr.value ? Number(f.min_subtotal_khr.value) : null,
    max_discount_khr: f.max_discount_khr.value ? Number(f.max_discount_khr.value) : null,
    usage_limit: f.usage_limit.value ? Number(f.usage_limit.value) : null,
    starts_at: f.starts_at.value || null,
    ends_at: f.ends_at.value || null,
    is_active: f.is_active.checked ? true : false,
  };

  try {
    const res = await api('/api/discounts', {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    if (res?.discount?.id) {
      UI.toast(DISC_I18N.toastCreated,'success');
      f.reset();
      document.getElementById('createDiscountActive').checked = true;
      closeDialog(document.getElementById('createDiscountModal'));
      load();
    } else {
      document.getElementById('createDiscountErr').textContent = res?.message || DISC_I18N.toastCreateFailed;
    }
  } catch (err) {
    document.getElementById('createDiscountErr').textContent = err?.data?.message || DISC_I18N.toastCreateFailed;
    console.error(err);
  }
});

// EDIT open
function openEdit(id, name, code, type, value, minSubtotal, maxDiscount, usageLimit, startsAt, endsAt, active) {
  const sp = (v, fb='') => { try { return JSON.parse(v); } catch { return fb; } };

  document.getElementById('edit_discount_id').value = id;
  document.getElementById('edit_discount_name').value = sp(name, String(name ?? ''));
  document.getElementById('edit_discount_code').value = code ? sp(code, String(code ?? '')) : '';
  document.getElementById('edit_discount_type').value = sp(type, 'percent');
  document.getElementById('edit_discount_value').value = sp(value, '');
  document.getElementById('edit_discount_min_subtotal_khr').value = minSubtotal ? sp(minSubtotal, '') : '';
  document.getElementById('edit_discount_max_discount_khr').value = maxDiscount ? sp(maxDiscount, '') : '';
  document.getElementById('edit_discount_usage_limit').value = usageLimit ? sp(usageLimit, '') : '';
  document.getElementById('edit_discount_starts_at').value = startsAt ? sp(startsAt, '').slice(0,10) : '';
  document.getElementById('edit_discount_ends_at').value = endsAt ? sp(endsAt, '').slice(0,10) : '';
  document.getElementById('editDiscountActive').checked = !!active;

  document.getElementById('editDiscountErr').textContent = '';
  openDialog(document.getElementById('editDiscountModal'));
}

// EDIT submit
document.getElementById('editDiscountForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const id = document.getElementById('edit_discount_id').value;

  const payload = {
    name: document.getElementById('edit_discount_name').value.trim(),
    code: document.getElementById('edit_discount_code').value.trim() || null,
    type: document.getElementById('edit_discount_type').value,
    value: Number(document.getElementById('edit_discount_value').value || 0),
    min_subtotal_khr: document.getElementById('edit_discount_min_subtotal_khr').value ? Number(document.getElementById('edit_discount_min_subtotal_khr').value) : null,
    max_discount_khr: document.getElementById('edit_discount_max_discount_khr').value ? Number(document.getElementById('edit_discount_max_discount_khr').value) : null,
    usage_limit: document.getElementById('edit_discount_usage_limit').value ? Number(document.getElementById('edit_discount_usage_limit').value) : null,
    starts_at: document.getElementById('edit_discount_starts_at').value || null,
    ends_at: document.getElementById('edit_discount_ends_at').value || null,
    is_active: document.getElementById('editDiscountActive').checked ? true : false,
  };

  try {
    const res = await api(`/api/discounts/${id}`, {
      method:'PATCH',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    if (res?.message === 'Updated') {
      UI.toast(DISC_I18N.toastUpdated,'success');
      closeDialog(document.getElementById('editDiscountModal'));
      load();
    } else {
      document.getElementById('editDiscountErr').textContent = res?.message || DISC_I18N.toastUpdateFailed;
    }
  } catch (err) {
    document.getElementById('editDiscountErr').textContent = err?.data?.message || DISC_I18N.toastUpdateFailed;
    console.error(err);
  }
});

// ARCHIVE / RESTORE
async function onArchive(id){
  const ok = await UI.confirm({
    title: 'Confirm',
    message: DISC_I18N.confirmArchive,
    confirmText: DISC_I18N.tooltipArchive || 'Archive',
    cancelText: 'Cancel',
    variant: 'danger',
  });
  if (!ok) return;

  try {
    const res = await api(`/api/discounts/${id}`, { method:'DELETE' });
    UI.toast(res?.message || DISC_I18N.toastArchived,'success');
    load();
  } catch (err) {
    UI.toast(err?.data?.message || DISC_I18N.toastArchiveFailed,'danger');
    console.error(err);
  }
}

async function onRestore(id){
  try {
    const res = await api(`/api/discounts/${id}/restore`, { method:'POST' });
    UI.toast(res?.message || DISC_I18N.toastRestored,'success');
    load();
  } catch (err) {
    UI.toast(err?.data?.message || DISC_I18N.toastRestoreFailed,'danger');
    console.error(err);
  }
}
@endif

(function initUI(){
  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value = 'created_at';
  document.getElementById('dir').value = 'desc';
  searchInput.value = state.q;
  load();
})();
</script>
@endpush
