{{-- resources/views/recipes/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.recipes_title', [], $locale) ?: 'Recipe Management')

@push('head')
<style>
  /* Full-screen centered dialogs */
  dialog{ padding:0; border:0; background:transparent; max-width:100vw; overflow:visible; }
  dialog::backdrop{ background: rgba(2, 6, 23, .65); backdrop-filter: blur(10px); }
  dialog[open]{
    position: fixed;
    inset: 0;
    margin: auto;
    display: grid;
    place-items: center;
    width: 100vw;
    height: 100vh;
  }

  .card-sticky-header { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(16px); }
  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  #rows tr:hover { background: rgba(244,114,182,.05); }

  .skeleton{ position:relative; overflow:hidden; background:#f3f4f6; border-radius:.375rem; }
  .dark .skeleton{ background: rgba(15,23,42,.9); }
  .skeleton::after{
    content:""; position:absolute; inset:0; transform: translateX(-100%);
    background: linear-gradient(90deg, rgba(248,250,252,0), rgba(226,232,240,.9), rgba(248,250,252,0));
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 100% { transform: translateX(100%); } }

  /* icon-only buttons */
  .icon-btn{
    height: 32px;
    width: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
  }

  /* Hide scrollbar but keep scroll */
  .no-scrollbar::-webkit-scrollbar { width: 0px; height: 0px; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🍳 {{ __('messages.recipes_title', [], $locale) ?: 'Recipe Management' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.recipes_subtitle', [], $locale) ?: 'Create recipes for stock deduction (base + variants).' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        {{ __('messages.ingredients_status_ok', [], $locale) ?: 'OK' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
        {{ __('messages.ingredients_status_low', [], $locale) ?: 'Low' }}
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
          <i class="bi bi-journal-text text-[12px] text-rose-300"></i>
          <span>{{ __('messages.recipes_manager', [], $locale) ?: 'Recipe manager' }}</span>
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
              placeholder="{{ __('messages.menu_search_placeholder', [], $locale) ?: 'Search menu item…' }}">
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

      </div>
    </div>

    {{-- Filters panel --}}
    <div id="filterPanel" class="hidden shrink-0">
      <div class="px-4 md:px-5 pt-3 pb-3.5">
        <form id="filterForm" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end text-[11px] md:text-xs">

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

    {{-- Scroll area --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm border-collapse">
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/90 dark:bg-slate-950/80 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium w-[8%]">{{ __('messages.image', [], $locale) ?: 'Image' }}</th>
            <th class="px-3 py-2 text-left font-medium w-[32%]">{{ __('messages.menu_col_name', [], $locale) ?: 'Menu item' }}</th>
            <th class="px-3 py-2 text-center font-medium w-[14%]">{{ __('messages.variants', [], $locale) ?: 'Variants' }}</th>

            {{-- ✅ UPDATED: replace recipes_base with category --}}
            <th class="px-3 py-2 text-center font-medium w-[16%]">{{ __('messages.category', [], $locale) ?: 'Category' }}</th>

            {{-- ✅ UPDATED: replace recipes_variant_lines with status --}}
            <th class="px-3 py-2 text-center font-medium w-[16%]">{{ __('messages.status', [], $locale) ?: 'Status' }}</th>

            <th class="px-3 py-2 text-right font-medium w-[14%]">{{ __('messages.actions', [], $locale) ?: 'Actions' }}</th>
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
    <div class="shrink-0 flex flex-wrap items-center justify-between gap-2
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

{{-- ✅ Reuse shared edit modal partial --}}
@include('recipes.partials.edit-modal', ['canWrite' => $canWrite, 'locale' => $locale])

@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);

const REC_I18N = {
  loading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  range: @json(__('messages.range', [], $locale) ?: 'Showing :from–:to of :total'),
  emptyTitle: @json(__('messages.recipes_empty_title', [], $locale) ?: 'No menu items found'),
  emptyBody: @json(__('messages.recipes_empty_body', [], $locale) ?: 'Try searching a different keyword.'),
  loadFail: @json(__('messages.recipes_load_failed', [], $locale) ?: 'Couldn’t load menu items.'),
  retry: @json(__('messages.retry', [], $locale) ?: 'Retry'),
  view: @json(__('messages.view', [], $locale) ?: 'View'),
  edit: @json(__('messages.edit', [], $locale) ?: 'Edit'),
  saved: @json(__('messages.saved', [], $locale) ?: 'Saved'),
  saveFail: @json(__('messages.save_failed', [], $locale) ?: 'Save failed'),
  statusHas: @json(__('messages.status_has', [], $locale) ?: 'Has'),
  statusMissing: @json(__('messages.status_missing', [], $locale) ?: 'Missing'),
};

const state = { q:'', per_page:10, page:1 };

const rows = document.getElementById('rows');
const pager = document.getElementById('pager');
const pageMeta = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

document.getElementById('filterToggle')?.addEventListener('click', () => {
  document.getElementById('filterPanel')?.classList.toggle('hidden');
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
  const from = ((p.current_page - 1) * p.per_page) + 1;
  const to   = Math.min(p.current_page * p.per_page, p.total);
  return REC_I18N.range.replace(':from', from).replace(':to', to).replace(':total', p.total);
}

function skeleton(){
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-12">
      <td class="px-3"><div class="skeleton h-9 w-9 rounded-xl"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-3/4"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-16 mx-auto"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-16 mx-auto"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-16 mx-auto"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-8 w-20 ml-auto rounded-full"></div></td>
    </tr>
  `).join('');
}

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g,'&quot;'); }
function debounce(fn, ms=350){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), ms); } }

function imgUrl(path){
  const p = (path ?? '').toString().trim();
  if (!p) return '';
  if (p.startsWith('http://') || p.startsWith('https://')) return p;
  // stored like "menu/xxx.jpg" on public disk
  return '/storage/' + p.replace(/^\/+/, '');
}

function buildPager(p){
  const { current_page, last_page } = p;
  pager.innerHTML = '';
  if (!last_page || last_page <= 1) return;

  const add = (label, page, disabled=false, active=false) => {
    const li = document.createElement('li');
    const base = 'px-3 py-1.5 text-xs md:text-sm rounded-full border';
    li.innerHTML = `<a href="#" class="${
      disabled ? base+' text-slate-400 border-slate-200 cursor-not-allowed dark:border-slate-700'
      : active ? base+' text-white bg-rose-500 border-rose-500 shadow-sm shadow-rose-300/70'
      : base+' text-slate-700 border-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-800/80'
    }">${label}</a>`;
    li.querySelector('a').addEventListener('click', (e)=>{
      e.preventDefault();
      if (disabled || active) return;
      state.page = page;
      load();
    });
    pager.appendChild(li);
  };

  add('«', 1, current_page === 1);
  add('‹', Math.max(1, current_page - 1), current_page === 1);

  const w = 2;
  const start = Math.max(1, current_page - w);
  const end = Math.min(last_page, current_page + w);
  for (let i=start; i<=end; i++) add(String(i), i, false, i === current_page);

  add('›', Math.min(last_page, current_page + 1), current_page === last_page);
  add('»', last_page, current_page === last_page);
}

/* ✅ Status pill (Has / Missing recipes) */
function statusPill(hasAnyRecipe){
  if (hasAnyRecipe){
    return `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]
                         dark:bg-emerald-900/40 dark:text-emerald-200">
      <span class="h-2 w-2 rounded-full bg-emerald-500"></span> ${esc(REC_I18N.statusHas)}
    </span>`;
  }
  return `<span class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2 py-0.5 text-[11px]
                       dark:bg-rose-900/30 dark:text-rose-200">
    <span class="h-2 w-2 rounded-full bg-rose-500"></span> ${esc(REC_I18N.statusMissing)}
  </span>`;
}

/* ============================
   Ingredients cache (for modal)
   ============================ */
const cache = {
  ingredientsLoaded: false,
  ingredients: [],
  ingIndex: new Map(),
};

async function ensureIngredients(){
  if (cache.ingredientsLoaded) return;
  const ingPaged = await api('/api/ingredients?per_page=200&sort=name&dir=asc');
  cache.ingredients = (ingPaged?.data || []);
  cache.ingIndex = new Map(cache.ingredients.map(x => [Number(x.id), x]));
  cache.ingredientsLoaded = true;
}

/* ============================
   Load index list
   ============================ */
async function load(){
  skeleton();
  const params = {
    q: state.q,
    per_page: state.per_page,
    page: state.page,
    include_variants: 1,
    include_recipes: 1,
  };

  try{
    const res = await api('/api/menu/items?' + qs(params));
    render(res);
  }catch(e){
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(REC_I18N.loadFail)}</div>
          <button id="retryBtn"
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${esc(REC_I18N.retry)}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = REC_I18N.loadFail;
    pageMeta.textContent = '';
  }
}

function render(paged){
  const list = paged.data || [];

  if(!list.length){
    rows.innerHTML = `
      <tr><td colspan="6">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(REC_I18N.emptyTitle)}</div>
          <div class="text-sm">${esc(REC_I18N.emptyBody)}</div>
        </div>
      </td></tr>`;
  } else {
    rows.innerHTML = list.map(it => {
      const baseLines = (it.recipes || []).length;
      const variants = (it.variants || []);
      const variantCount = variants.length;
      const vWithRecipe = variants.filter(v => (v.recipes || []).length > 0).length;

      // ✅ status: Has if base has lines OR any variant has lines
      const hasAnyRecipe = baseLines > 0 || vWithRecipe > 0;

      const img = imgUrl(it.image);
      const imgHtml = img
        ? `<img src="${escAttr(img)}" class="h-9 w-9 rounded-xl object-cover border border-white/60 dark:border-slate-800/80" alt="img">`
        : `<div class="h-9 w-9 rounded-xl grid place-items-center
                    bg-slate-100 dark:bg-slate-800 border border-white/60 dark:border-slate-800/80
                    text-slate-500 dark:text-slate-300">🍩</div>`;

      let actions = `
        <a href="/recipes/${it.id}"
           class="tw-tip icon-btn border border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80"
           data-tooltip="${escAttr(REC_I18N.view)}" aria-label="${escAttr(REC_I18N.view)}">
          <i class="bi bi-eye text-[14px] leading-none"></i>
        </a>`;

      // ✅ Only ONE edit button (defaults to base)
      if (CAN_WRITE) {
        actions += `
          <button
            onclick="openEditorBase(${Number(it.id)})"
            class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200
                   dark:border-indigo-500 dark:text-indigo-200 dark:hover:bg-indigo-900/20"
            data-tooltip="${escAttr(REC_I18N.edit)}" aria-label="${escAttr(REC_I18N.edit)}">
            <i class="bi bi-pencil-square text-[14px] leading-none"></i>
          </button>`;
      }

      return `
        <tr class="align-middle">
          <td class="px-3 py-2">${imgHtml}</td>

          <td class="px-3 py-2">
            <div class="font-medium text-slate-900 dark:text-slate-50">${esc(it.name)}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
              ID: ${esc(it.id)}
            </div>
          </td>

          <td class="px-3 py-2 text-center">
            <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-[11px]
                         text-slate-700 dark:text-slate-200">
              ${variantCount}
            </span>
          </td>

          {{-- ✅ Category column --}}
          <td class="px-3 py-2 text-center text-slate-700 dark:text-slate-200">
            ${esc(it.category?.name || '—')}
          </td>

          {{-- ✅ Status column --}}
          <td class="px-3 py-2 text-center">
            ${statusPill(hasAnyRecipe)}
          </td>

          <td class="px-3 py-2 text-right">
            <div class="flex items-center gap-2 justify-end flex-nowrap whitespace-nowrap">${actions}</div>
          </td>
        </tr>`;
    }).join('');
  }

  const meta = fmtRange(paged);
  summaryText.textContent = meta || '—';
  pageMeta.textContent = meta || '';
  buildPager(paged);
}

/* Filters */
filterForm?.addEventListener('submit', (e)=>{
  e.preventDefault();
  state.per_page = parseInt(document.getElementById('per_page').value || '10', 10);
  state.page = 1;
  load();
});

document.getElementById('resetBtn')?.addEventListener('click', ()=>{
  filterForm.reset();
  document.getElementById('per_page').value = '10';
  state.per_page = 10;
  state.page = 1;
  load();
});

/* Search */
searchInput?.addEventListener('input', debounce(()=>{
  state.q = searchInput.value.trim();
  state.page = 1;
  load();
}, 300));

document.getElementById('clearSearch')?.addEventListener('click', ()=>{
  searchInput.value = '';
  state.q = '';
  state.page = 1;
  load();
});

document.getElementById('refreshBtn')?.addEventListener('click', load);

/* ============================
   Recipe editor modal integration
   (uses same IDs from partial)
   ============================ */
const editor = {
  item: null,
  scope: 'base',
  variantId: null,
  lines: [],
  dirty: false,
};

function fmtNum(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function pill(isLow){
  if (isLow){
    return `<span class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2 py-0.5 text-[11px]
                         dark:bg-rose-900/30 dark:text-rose-200">
      <span class="h-2 w-2 rounded-full bg-rose-500"></span> Low
    </span>`;
  }
  return `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]
                       dark:bg-emerald-900/40 dark:text-emerald-200">
    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> OK
  </span>`;
}

function getLinesForScope(scope, variantId){
  if (scope === 'base'){
    return (editor.item?.recipes || []).map(r => ({
      ingredient_id: Number(r.ingredient_id ?? r.ingredient?.id),
      quantity: Number(r.quantity),
    })).filter(x => Number.isFinite(x.ingredient_id));
  }
  const v = (editor.item?.variants || []).find(x => Number(x.id) === Number(variantId));
  return (v?.recipes || []).map(r => ({
    ingredient_id: Number(r.ingredient_id ?? r.ingredient?.id),
    quantity: Number(r.quantity),
  })).filter(x => Number.isFinite(x.ingredient_id));
}

function updateRecipeModalTitle(){
  const itemName = editor.item?.name ?? 'Recipe';
  let scopeLabel = 'Base';
  if (editor.scope === 'variant'){
    const v = (editor.item?.variants || []).find(x => Number(x.id) === Number(editor.variantId));
    scopeLabel = `Variant: ${v?.name ?? '—'}`;
  }
  document.getElementById('recipeModalTitle').textContent = `${itemName} · ${scopeLabel}`;
  document.getElementById('lineCount').textContent = `(${editor.lines.length} lines)`;
}

function renderEditLines(){
  const tb = document.getElementById('editLines');
  document.getElementById('lineCount').textContent = `(${editor.lines.length} lines)`;

  if (!editor.lines.length){
    tb.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
      <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
      <div class="font-medium mb-1">No recipe lines</div>
      <div class="text-sm">Click “Add” to insert ingredients.</div>
    </td></tr>`;
    return;
  }

  tb.innerHTML = editor.lines.map((l, idx) => {
    const ing = cache.ingIndex.get(Number(l.ingredient_id));
    return `
      <tr class="align-middle">
        <td class="px-3 py-2">
          <div class="font-medium text-slate-900 dark:text-slate-50">${esc(ing?.name ?? '—')}</div>
        </td>
        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtNum(l.quantity))}</td>
        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(ing?.unit ?? '—')}</td>
        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(ing ? fmtNum(ing.current_qty) : '—')}</td>
        <td class="px-3 py-2 text-center">${pill(!!ing?.is_low)}</td>
        <td class="px-3 py-2 text-right">
          <div class="flex items-center gap-2 justify-end whitespace-nowrap">
            <button class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200
                           dark:border-indigo-500 dark:text-indigo-200 dark:hover:bg-indigo-900/20"
                    data-tooltip="Edit" aria-label="Edit"
                    onclick="openLineModal(${idx})">
              <i class="bi bi-pencil text-[14px] leading-none"></i>
            </button>
            <button class="tw-tip icon-btn border border-slate-600 text-slate-700 hover:bg-slate-100
                           dark:border-slate-500 dark:text-slate-200 dark:hover:bg-slate-800"
                    data-tooltip="Remove" aria-label="Remove"
                    onclick="removeLine(${idx})">
              <i class="bi bi-trash text-[14px] leading-none"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

window.removeLine = function(idx){
  editor.lines.splice(idx, 1);
  editor.dirty = true;
  renderEditLines();
  updateRecipeModalTitle();
};

let recipeModalWired = false;
function wireRecipeModalListeners(){
  if (recipeModalWired) return;
  recipeModalWired = true;

  document.querySelectorAll('#recipeModal [data-close], #lineModal [data-close]').forEach(btn=>{
    btn.addEventListener('click', ()=> closeDialog(btn.closest('dialog')));
  });

  document.querySelectorAll('input[name="scope"]').forEach(r=>{
    r.addEventListener('change', ()=>{
      if (editor.dirty && !confirm('You have unsaved changes. Switch scope anyway?')){
        document.querySelector(`input[name="scope"][value="${editor.scope}"]`).checked = true;
        return;
      }
      editor.scope = r.value;
      editor.dirty = false;

      const variantSelect = document.getElementById('variantSelect');
      variantSelect.disabled = (editor.scope !== 'variant');

      if (editor.scope === 'variant' && !editor.variantId){
        const first = (editor.item?.variants || [])[0];
        editor.variantId = first ? Number(first.id) : null;
        variantSelect.value = editor.variantId ? String(editor.variantId) : '';
      }

      editor.lines = getLinesForScope(editor.scope, editor.variantId);
      renderEditLines();
      updateRecipeModalTitle();
    });
  });

  document.getElementById('variantSelect').addEventListener('change', (e)=>{
    if (editor.scope !== 'variant') return;
    if (editor.dirty && !confirm('You have unsaved changes. Switch variant anyway?')){
      e.target.value = editor.variantId ? String(editor.variantId) : '';
      return;
    }
    editor.variantId = e.target.value ? Number(e.target.value) : null;
    editor.dirty = false;
    editor.lines = getLinesForScope('variant', editor.variantId);
    renderEditLines();
    updateRecipeModalTitle();
  });

  document.getElementById('addLineBtn').addEventListener('click', ()=> openLineModal(null));
  document.getElementById('saveRecipeBtn').addEventListener('click', saveRecipeGroup);

  document.getElementById('lineForm').addEventListener('submit', onLineSave);
  document.getElementById('ingSearch').addEventListener('input', debounce(renderIngredientPicker, 200));
}

async function saveRecipeGroup(){
  const err = document.getElementById('recipeErr');
  err.textContent = '';

  if (editor.scope === 'variant' && !editor.variantId){
    err.textContent = 'Please select a variant.';
    return;
  }

  const ids = editor.lines.map(x => Number(x.ingredient_id));
  const uniq = new Set(ids);
  if (uniq.size !== ids.length){
    err.textContent = 'Duplicate ingredients detected.';
    return;
  }

  const payload = {
    menu_item_id: Number(editor.item?.id),
    menu_item_variant_id: editor.scope === 'variant' ? Number(editor.variantId) : null,
    lines: editor.lines.map(l => ({ ingredient_id: Number(l.ingredient_id), quantity: Number(l.quantity) })),
  };

  try{
    await api('/api/recipes/group', {
      method:'PUT',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    showToast(REC_I18N.saved, 'success');
    closeDialog(document.getElementById('recipeModal'));

    load();
  }catch(ex){
    console.error(ex);
    err.textContent = ex?.data?.message || REC_I18N.saveFail;
    showToast(err.textContent, 'danger');
  }
}

/* ===== nested line modal ===== */
window.openLineModal = function(index){
  document.getElementById('lineErr').textContent = '';
  document.getElementById('ingSearch').value = '';
  document.getElementById('ingredient_id').value = '';
  document.getElementById('selectedIngName').textContent = '—';
  document.getElementById('selectedIngUnit').textContent = '—';
  document.getElementById('line_qty').value = '';
  document.getElementById('line_index').value = (index === null || index === undefined) ? '' : String(index);
  document.getElementById('lineModalTitle').textContent = (index === null || index === undefined) ? 'Add ingredient' : 'Edit ingredient';

  if (index !== null && index !== undefined){
    const line = editor.lines[index];
    const ing = cache.ingIndex.get(Number(line.ingredient_id));
    document.getElementById('ingredient_id').value = String(line.ingredient_id);
    document.getElementById('selectedIngName').textContent = ing?.name ?? '—';
    document.getElementById('selectedIngUnit').textContent = ing?.unit ?? '—';
    document.getElementById('line_qty').value = String(line.quantity ?? '');
  }

  renderIngredientPicker();
  openDialog(document.getElementById('lineModal'));
};

function renderIngredientPicker(){
  const q = (document.getElementById('ingSearch').value || '').trim().toLowerCase();
  const list = document.getElementById('ingList');

  const used = new Set(editor.lines.map(x => Number(x.ingredient_id)));
  const editingIndex = document.getElementById('line_index').value;
  if (editingIndex !== ''){
    used.delete(Number(editor.lines[Number(editingIndex)]?.ingredient_id));
  }

  const filtered = cache.ingredients
    .filter(i => !q || (String(i.name||'').toLowerCase().includes(q)))
    .slice(0, 60);

  if (!filtered.length){
    list.innerHTML = `<div class="p-3 text-[11px] text-slate-500 dark:text-slate-400">No ingredients</div>`;
    return;
  }

  list.innerHTML = filtered.map(i=>{
    const disabled = used.has(Number(i.id));
    const stat = i.is_low ? 'Low' : 'OK';
    return `
      <button type="button"
        class="w-full text-left px-3 py-2 flex items-center gap-2 ${disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'}"
        onclick="${disabled ? '' : `selectIngredient(${Number(i.id)})`}"
        ${disabled ? 'disabled' : ''}>
        <div>
          <div class="text-xs font-medium text-slate-900 dark:text-slate-50">${esc(i.name)}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">${esc(i.unit || '—')} · Stock: ${esc(fmtNum(i.current_qty))}</div>
        </div>
        <span class="ml-auto">${pill(!!i.is_low).replace('OK', stat)}</span>
      </button>
    `;
  }).join('');
}

window.selectIngredient = function(id){
  const ing = cache.ingIndex.get(Number(id));
  document.getElementById('ingredient_id').value = String(id);
  document.getElementById('selectedIngName').textContent = ing?.name ?? '—';
  document.getElementById('selectedIngUnit').textContent = ing?.unit ?? '—';
};

function onLineSave(e){
  e.preventDefault();
  const err = document.getElementById('lineErr');
  err.textContent = '';

  const idxRaw = document.getElementById('line_index').value;
  const ingredientId = Number(document.getElementById('ingredient_id').value || 0);
  const qty = Number(document.getElementById('line_qty').value || 0);

  if (!ingredientId){ err.textContent = 'Please select an ingredient.'; return; }
  if (!Number.isFinite(qty) || qty <= 0){ err.textContent = 'Quantity must be greater than 0.'; return; }

  const used = new Set(editor.lines.map(x => Number(x.ingredient_id)));
  if (idxRaw !== '') used.delete(Number(editor.lines[Number(idxRaw)]?.ingredient_id));
  if (used.has(ingredientId)){ err.textContent = 'This ingredient is already in the recipe.'; return; }

  if (idxRaw === ''){
    editor.lines.push({ ingredient_id: ingredientId, quantity: qty });
  } else {
    editor.lines[Number(idxRaw)] = { ingredient_id: ingredientId, quantity: qty };
  }

  editor.dirty = true;
  closeDialog(document.getElementById('lineModal'));
  renderEditLines();
  updateRecipeModalTitle();
}

/* ✅ Open editor (default base) */
window.openEditorBase = async function(menuItemId){
  try{
    await ensureIngredients();

    editor.item = await api(`/api/menu/items/${Number(menuItemId)}?include_variants=1&include_recipes=1`);
    editor.scope = 'base';
    editor.variantId = null;
    editor.dirty = false;

    document.getElementById('recipeErr').textContent = '';

    const variantSelect = document.getElementById('variantSelect');
    variantSelect.innerHTML = `<option value="">Select variant</option>`;
    (editor.item?.variants || []).forEach(v=>{
      const opt = document.createElement('option');
      opt.value = String(v.id);
      opt.textContent = v.name;
      variantSelect.appendChild(opt);
    });

    document.querySelectorAll('input[name="scope"]').forEach(r => r.checked = (r.value === 'base'));
    variantSelect.disabled = true;
    variantSelect.value = '';

    editor.lines = getLinesForScope('base', null);
    renderEditLines();
    updateRecipeModalTitle();

    wireRecipeModalListeners();
    openDialog(document.getElementById('recipeModal'));
  }catch(e){
    console.error(e);
    showToast(e?.data?.message || 'Failed to open editor', 'danger');
  }
};

(function initUI(){
  document.getElementById('per_page').value = '10';
  searchInput.value = state.q;
  load();
})();
</script>
@endpush
