{{-- resources/views/ingredients/show.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.ingredients_show_title', [], $locale) ?: 'Ingredient Details')

@push('head')
<style>
  .card-sticky-header { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(16px); }
  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  #movRows tr:hover { background: rgba(244,114,182,.05); }
  .skeleton{ position:relative; overflow:hidden; background:#f3f4f6; border-radius:.375rem; }
  .dark .skeleton{ background: rgba(15,23,42,.9); }
  .skeleton::after{
    content:""; position:absolute; inset:0; transform: translateX(-100%);
    background: linear-gradient(90deg, rgba(248,250,252,0), rgba(226,232,240,.9), rgba(248,250,252,0));
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
        <a href="{{ url('/ingredients') }}"
           class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                  px-3 py-1.5 text-[11px] md:text-xs text-slate-700 hover:bg-slate-50
                  dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i>
          <span>{{ __('messages.back', [], $locale) ?: 'Back' }}</span>
        </a>

        <div id="ingBadge"
             class="hidden sm:inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-box-seam text-[12px] text-rose-300"></i>
          <span id="ingNameText">—</span>
        </div>
      </div>

      <h1 class="mt-2 text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🧂 {{ __('messages.ingredients_show_title', [], $locale) ?: 'Ingredient Details' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.ingredients_show_subtitle', [], $locale) ?: 'View movements and update this ingredient.' }}
      </p>

      <p id="metaLine" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <span id="statusPill" class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] text-slate-600 dark:text-slate-200">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span> —
      </span>

      @if($canWrite)
      <button id="adjustBtn" type="button"
              class="inline-flex items-center gap-1.5 rounded-full
                     bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                     px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
                     shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
        <i class="bi bi-arrow-left-right text-[12px]"></i>
        <span>{{ __('messages.ingredients_adjust', [], $locale) ?: 'Adjust stock' }}</span>
      </button>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Left: movements --}}
    <div class="lg:col-span-2 space-y-4">

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
              <i class="bi bi-clock-history text-[12px] text-rose-300"></i>
              <span>{{ __('messages.ingredients_movements_title', [], $locale) ?: 'Movements' }}</span>
            </div>
            <div class="flex-1"></div>
            <span id="movCount" class="text-[11px] text-slate-500 dark:text-slate-400">—</span>
          </div>
        </div>

        <div class="px-3 md:px-4 py-3 overflow-x-auto">
          <table class="min-w-full text-xs md:text-sm">
            <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 bg-slate-50/60 dark:bg-slate-950/40">
              <tr>
                <th class="px-3 py-2 text-right font-medium w-[16%]">{{ __('messages.ingredients_col_delta', [], $locale) ?: 'Delta' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[16%]">{{ __('messages.ingredients_col_reason', [], $locale) ?: 'Reason' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[32%]">{{ __('messages.ingredients_col_note', [], $locale) ?: 'Note' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[20%]">{{ __('messages.ingredients_col_user', [], $locale) ?: 'User' }}</th>
                <th class="px-3 py-2 text-left font-medium w-[16%]">{{ __('messages.ingredients_col_time', [], $locale) ?: 'Time' }}</th>
              </tr>
            </thead>
            <tbody id="movRows" class="divide-y divide-slate-100 dark:divide-slate-800">
              @for($i=0;$i<4;$i++)
                <tr class="h-12">
                  <td class="px-3 text-right"><div class="skeleton h-4 w-16 ml-auto"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-20"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-40"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-28"></div></td>
                  <td class="px-3"><div class="skeleton h-4 w-28"></div></td>
                </tr>
              @endfor
            </tbody>
          </table>
        </div>

        <div class="flex flex-wrap items-center justify-between px-4 md:px-5 pb-4 gap-2">
          <small id="pageMeta" class="text-[11px] text-slate-500 dark:text-slate-400"></small>
          <nav><ul id="pager" class="inline-flex items-center gap-1"></ul></nav>
        </div>
      </div>
    </div>

    {{-- Right: summary + update --}}
    <div class="space-y-4">

      <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
                  bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
                  backdrop-blur-2xl overflow-hidden">
        <div class="px-4 md:px-5 py-3 border-b border-white/60 dark:border-slate-800/80
                    bg-gradient-to-r from-white/95 via-white/80 to-white/70
                    dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
          <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                      dark:bg-slate-800 shadow-sm shadow-slate-900/40">
            <i class="bi bi-info-circle text-[12px] text-rose-300"></i>
            <span>{{ __('messages.ingredients_summary_title', [], $locale) ?: 'Details' }}</span>
          </div>
        </div>

        <div class="px-4 md:px-5 py-4 space-y-3 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.ingredients_col_current', [], $locale) ?: 'Current' }}</span>
            <span id="currentText" class="font-bold text-slate-900 dark:text-slate-50">—</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}</span>
            <span id="lowText" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-500 dark:text-slate-400">{{ __('messages.ingredients_col_restocked', [], $locale) ?: 'Restocked' }}</span>
            <span id="restockedText" class="text-slate-700 dark:text-slate-200">—</span>
          </div>

          @if($canWrite)
          <div class="soft-divider"></div>

          <form id="updateForm" class="space-y-3">
            @csrf
            <div class="space-y-1">
              <label class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ __('messages.ingredients_col_name', [], $locale) ?: 'Name' }}</label>
              <input id="u_name" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm text-slate-900 dark:text-slate-50">
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ __('messages.ingredients_col_unit', [], $locale) ?: 'Unit' }}</label>
                <input id="u_unit" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm text-slate-900 dark:text-slate-50">
              </div>

              <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}</label>
                <input id="u_low" type="number" step="0.001" min="0"
                       class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm text-slate-900 dark:text-slate-50">
              </div>
            </div>

            <p id="u_err" class="text-[11px] text-rose-500 min-h-[1rem]"></p>

            <div class="flex justify-end gap-2">
              <button id="deleteBtn" type="button"
                      class="inline-flex items-center gap-1.5 rounded-full border border-slate-400 px-3 py-1.5 text-xs text-slate-800 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800">
                <i class="bi bi-trash text-[12px]"></i>
                <span>{{ __('messages.delete', [], $locale) ?: 'Delete' }}</span>
              </button>

              <button type="submit"
                      class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
                <i class="bi bi-check2-circle text-[12px]"></i>
                <span>{{ __('messages.save', [], $locale) ?: 'Save' }}</span>
              </button>
            </div>
          </form>
          @endif

          <p id="loadErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>
        </div>
      </div>

    </div>
  </div>
</div>

@if($canWrite)
{{-- reuse the same adjust modal idea (inline for show page) --}}
@include('ingredients.partials.adjust-modal')
@endif
@endsection

@push('scripts')
<script>
const SHOW_I18N = {
  loading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  loadFail: @json(__('messages.ingredients_load_failed', [], $locale) ?: 'Couldn’t load ingredient.'),
  range: @json(__('messages.range', [], $locale) ?: 'Showing :from–:to of :total'),
  emptyMoves: @json(__('messages.ingredients_movements_empty', [], $locale) ?: 'No movements yet.'),
  confirmDelete: @json(__('messages.ingredients_confirm_delete', [], $locale) ?: 'Delete this ingredient?'),
  saved: @json(__('messages.ingredients_toast_updated', [], $locale) ?: 'Ingredient updated'),
  deleted: @json(__('messages.ingredients_toast_deleted', [], $locale) ?: 'Ingredient deleted'),
};

const el = (id)=>document.getElementById(id);
const dom = {
  ingBadge: el('ingBadge'),
  ingNameText: el('ingNameText'),
  metaLine: el('metaLine'),
  statusPill: el('statusPill'),
  currentText: el('currentText'),
  lowText: el('lowText'),
  restockedText: el('restockedText'),
  movRows: el('movRows'),
  movCount: el('movCount'),
  pageMeta: el('pageMeta'),
  pager: el('pager'),
  loadErr: el('loadErr'),
  adjustBtn: el('adjustBtn'),

  @if($canWrite)
  updateForm: el('updateForm'),
  u_name: el('u_name'),
  u_unit: el('u_unit'),
  u_low: el('u_low'),
  u_err: el('u_err'),
  deleteBtn: el('deleteBtn'),
  @endif
};

let ING = null;
let MOV_PAGE = 1;

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function fmtNum(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function fmtDate(dt){ try { return dt ? new Date(dt).toLocaleString() : '—'; } catch { return '—'; } }

function getId(){
  const parts = window.location.pathname.split('/').filter(Boolean);
  const idx = parts.indexOf('ingredients');
  const raw = (idx >= 0 && parts[idx + 1]) ? parts[idx + 1] : null;
  const n = raw ? parseInt(raw, 10) : NaN;
  return Number.isFinite(n) ? n : null;
}

function setStatus(isLow){
  dom.statusPill.innerHTML = isLow
    ? `<span class="h-2 w-2 rounded-full bg-rose-500"></span> {{ __("messages.ingredients_status_low", [], $locale) ?: "Low" }}`
    : `<span class="h-2 w-2 rounded-full bg-emerald-500"></span> {{ __("messages.ingredients_status_ok", [], $locale) ?: "OK" }}`;
  dom.statusPill.className =
    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] ' +
    (isLow
      ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-200'
      : 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-200');
}

function buildPager(p){
  dom.pager.innerHTML = '';
  if (!p?.last_page || p.last_page <= 1) return;

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
      MOV_PAGE = page;
      load();
    });
    dom.pager.appendChild(li);
  };

  add('«', 1, p.current_page === 1);
  add('‹', Math.max(1, p.current_page - 1), p.current_page === 1);

  const w = 2;
  const start = Math.max(1, p.current_page - w);
  const end = Math.min(p.last_page, p.current_page + w);
  for (let i=start; i<=end; i++) add(String(i), i, false, i === p.current_page);

  add('›', Math.min(p.last_page, p.current_page + 1), p.current_page === p.last_page);
  add('»', p.last_page, p.current_page === p.last_page);
}

function renderMovements(paged){
  const list = paged?.data || [];
  dom.movCount.textContent = `${list.length} row(s)`;

  if (!list.length){
    dom.movRows.innerHTML = `
      <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
        <i class="bi bi-clock-history block text-3xl opacity-70 mb-2"></i>
        <div class="font-medium">${esc(SHOW_I18N.emptyMoves)}</div>
      </td></tr>`;
  } else {
    dom.movRows.innerHTML = list.map(m => {
      const delta = Number(m.delta_qty ?? 0);
      const sign = delta > 0 ? '+' : '';
      const user = m.user?.name || m.user?.email || '—';
      return `
        <tr class="align-middle">
          <td class="px-3 py-2 text-right font-semibold ${delta < 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'}">
            ${esc(sign + fmtNum(delta))} ${esc(ING?.unit || '')}
          </td>
          <td class="px-3 py-2 text-slate-700 dark:text-slate-200">${esc(m.reason || '—')}</td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(m.note || '—')}</td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(user)}</td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(fmtDate(m.created_at))}</td>
        </tr>`;
    }).join('');
  }

  // meta
  const from = ((paged.current_page - 1) * paged.per_page) + 1;
  const to   = Math.min(paged.current_page * paged.per_page, paged.total);
  dom.pageMeta.textContent = paged.total ? SHOW_I18N.range.replace(':from', from).replace(':to', to).replace(':total', paged.total) : '';
  buildPager(paged);
}

function renderIngredient(ingredient){
  dom.ingBadge.classList.remove('hidden');
  dom.ingNameText.textContent = ingredient.name || ('#' + ingredient.id);

  dom.currentText.textContent = `${fmtNum(ingredient.current_qty)} ${ingredient.unit || ''}`;
  dom.lowText.textContent = `${fmtNum(ingredient.low_alert_qty)} ${ingredient.unit || ''}`;
  dom.restockedText.textContent = fmtDate(ingredient.last_restocked_at);

  const isLow = !!ingredient.is_low;
  setStatus(isLow);

  dom.metaLine.textContent = `${ingredient.unit || ''} · ${fmtDate(ingredient.updated_at || ingredient.created_at)}`;

  @if($canWrite)
  dom.u_name.value = ingredient.name || '';
  dom.u_unit.value = ingredient.unit || '';
  dom.u_low.value = ingredient.low_alert_qty ?? 0;
  @endif
}

async function load(){
  dom.loadErr.textContent = '';
  dom.metaLine.textContent = SHOW_I18N.loading;

  const id = getId();
  if (!id){
    dom.loadErr.textContent = 'Missing ingredient id in URL.';
    dom.metaLine.textContent = '—';
    return;
  }

  try{
    const res = await api(`/api/ingredients/${id}?movements_per_page=20&page=${MOV_PAGE}`);
    ING = res?.ingredient;
    renderIngredient(ING);
    renderMovements(res?.movements);
  }catch(e){
    console.error(e);
    dom.loadErr.textContent = e?.data?.message || SHOW_I18N.loadFail;
    dom.metaLine.textContent = '—';
  }
}

@if($canWrite)
// open adjust
dom.adjustBtn?.addEventListener('click', ()=>{
  if (!ING) return;
  openAdjust(ING.id, JSON.stringify(ING.name), JSON.stringify(ING.unit), JSON.stringify(ING.current_qty), JSON.stringify(ING.low_alert_qty));
});

// update
dom.updateForm?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  dom.u_err.textContent = '';

  try{
    const payload = {
      name: dom.u_name.value.trim(),
      unit: dom.u_unit.value.trim(),
      low_alert_qty: dom.u_low.value === '' ? null : Number(dom.u_low.value),
    };

    const res = await api(`/api/ingredients/${ING.id}`, {
      method:'PATCH',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    if (res?.message === 'Updated') {
      showToast(SHOW_I18N.saved, 'success');
      MOV_PAGE = 1;
      load();
    } else {
      dom.u_err.textContent = res?.message || 'Save failed';
    }
  }catch(err){
    console.error(err);
    dom.u_err.textContent = err?.data?.message || 'Save failed';
  }
});

// delete
dom.deleteBtn?.addEventListener('click', async ()=>{
  if (!ING) return;
  if (!confirm(SHOW_I18N.confirmDelete)) return;

  try{
    await api(`/api/ingredients/${ING.id}`, { method:'DELETE' });
    showToast(SHOW_I18N.deleted, 'success');
    window.location.href = '/ingredients';
  }catch(err){
    console.error(err);
    showToast(err?.data?.message || 'Delete failed', 'danger');
  }
});
@endif

(function init(){ load(); })();
</script>
@endpush
