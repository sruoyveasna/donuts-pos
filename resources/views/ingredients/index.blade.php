{{-- resources/views/ingredients/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
@endphp

@section('title', __('messages.ingredients_title', [], $locale) ?: 'Ingredients')

@push('head')
<style>
  /* Full-screen centered dialogs (same template pattern) */
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
{{-- ✅ Fill available height + only table area scrolls --}}
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🧂 {{ __('messages.ingredients_title', [], $locale) ?: 'Ingredients' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.ingredients_subtitle', [], $locale) ?: 'Manage stock levels and track inventory movements.' }}
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
          <i class="bi bi-box-seam text-[12px] text-rose-300"></i>
          <span>{{ __('messages.ingredients_manager', [], $locale) ?: 'Stock manager' }}</span>
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
              placeholder="{{ __('messages.ingredients_search_placeholder', [], $locale) ?: 'Search ingredient…' }}">
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
          <span class="hidden sm:inline">{{ __('messages.ingredients_new', [], $locale) ?: 'New Ingredient' }}</span>
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
              <option value="created_at">{{ __('messages.ingredients_sort_created', [], $locale) ?: 'Created' }}</option>
              <option value="name">{{ __('messages.ingredients_sort_name', [], $locale) ?: 'Name' }}</option>
              <option value="current_qty">{{ __('messages.ingredients_sort_current', [], $locale) ?: 'Current qty' }}</option>
              <option value="low_alert_qty">{{ __('messages.ingredients_sort_low', [], $locale) ?: 'Low alert' }}</option>
              <option value="last_restocked_at">{{ __('messages.ingredients_sort_restocked', [], $locale) ?: 'Restocked' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.direction', [], $locale) ?: 'Direction' }}
            </label>
            <select id="dir" name="dir"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100">
              <option value="desc">{{ __('messages.sort_desc', [], $locale) ?: 'DESC' }}</option>
              <option value="asc">{{ __('messages.sort_asc', [], $locale) ?: 'ASC' }}</option>
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
              {{ __('messages.ingredients_filter_flags', [], $locale) ?: 'Flags' }}
            </label>
            <div class="flex flex-wrap gap-4 mt-1.5">
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input id="low_only" name="low_only" type="checkbox"
                       class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70">
                <span>{{ __('messages.ingredients_filter_low_only', [], $locale) ?: 'Low stock only' }}</span>
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

    {{-- ✅ Scroll area: ONLY this section scrolls --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm border-collapse">
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/90 dark:bg-slate-950/80 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium w-[26%]">{{ __('messages.ingredients_col_name', [], $locale) ?: 'Name' }}</th>
            <th class="px-3 py-2 text-left font-medium w-[10%]">{{ __('messages.ingredients_col_unit', [], $locale) ?: 'Unit' }}</th>
            <th class="px-3 py-2 text-right font-medium w-[14%]">{{ __('messages.ingredients_col_current', [], $locale) ?: 'Current' }}</th>
            <th class="px-3 py-2 text-right font-medium w-[14%]">{{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}</th>
            <th class="px-3 py-2 text-center font-medium w-[10%]">{{ __('messages.ingredients_col_status', [], $locale) ?: 'Status' }}</th>
            <th class="px-3 py-2 text-left font-medium w-[16%]">{{ __('messages.ingredients_col_restocked', [], $locale) ?: 'Restocked' }}</th>
            <th class="px-3 py-2 text-right font-medium w-[10%]">{{ __('messages.actions', [], $locale) ?: 'Actions' }}</th>
          </tr>
        </thead>

        <tbody id="rows" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr>
            <td colspan="7" class="py-6 text-center text-slate-500 dark:text-slate-400">
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

{{-- =========================
   MODALS (fixed widths)
   ========================= --}}

@if($canWrite)
{{-- CREATE MODAL --}}
<dialog id="createModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(460px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">
    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400 text-slate-50
                flex items-center justify-between gap-2">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-plus-lg text-[13px]"></i>
        </span>
        <span>{{ __('messages.ingredients_new', [], $locale) ?: 'New Ingredient' }}</span>
      </div>
      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="createForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_name', [], $locale) ?: 'Name' }}
          </label>
          <input name="name" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_unit', [], $locale) ?: 'Unit' }}
          </label>
          <input name="unit" required placeholder="g / kg / ml / l / pcs"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}
          </label>
          <input name="low_alert_qty" type="number" step="0.001" min="0" placeholder="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_current', [], $locale) ?: 'Current' }}
          </label>
          <input name="current_qty" type="number" step="0.001" min="0" placeholder="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>
      </div>

      <p id="createErr" class="mt-4 text-[11px] text-rose-500 min-h-[1rem]"></p>

      <div class="mt-4 flex justify-between items-center">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-x-circle text-[12px]"></i>
          <span>{{ __('messages.cancel', [], $locale) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.create', [], $locale) ?: 'Create' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>

{{-- EDIT MODAL (✅ FIXED WIDTH: NOT w-full) --}}
<dialog id="editModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(520px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">
    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-pencil text-[13px]"></i>
        </span>
        <span>{{ __('messages.edit', [], $locale) ?: 'Edit Ingredient' }}</span>
      </div>
      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="editForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden flex-1 min-h-0">
      @csrf
      <input type="hidden" name="id" id="edit_id">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_name', [], $locale) ?: 'Name' }}
          </label>
          <input id="edit_name" name="name" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_unit', [], $locale) ?: 'Unit' }}
          </label>
          <input id="edit_unit" name="unit" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}
          </label>
          <input id="edit_low_alert_qty" name="low_alert_qty" type="number" step="0.001" min="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_current', [], $locale) ?: 'Current' }}
          </label>
          <input id="edit_current_qty" name="current_qty" type="number" step="0.001" min="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>
      </div>

      <p id="editErr" class="mt-4 text-[11px] text-rose-500 min-h-[1rem]"></p>

      <div class="mt-4 flex justify-between items-center shrink-0">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs
                       text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-x-circle text-[12px]"></i>
          <span>{{ __('messages.cancel', [], $locale) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-indigo-500 via-indigo-400 to-sky-400
                       px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-indigo-300/70 hover:shadow-indigo-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.update', [], $locale) ?: 'Update' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>

{{-- ADJUST MODAL (✅ FIXED WIDTH: NOT 100%) --}}
<dialog id="adjustModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(520px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">

    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-arrow-left-right text-[13px]"></i>
        </span>
        <span>{{ __('messages.ingredients_adjust', [], $locale) ?: 'Adjust Stock' }}</span>
      </div>

      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="adjustForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden flex-1 min-h-0">
      @csrf
      <input type="hidden" id="adjust_id" name="id">

      <div class="space-y-4">
        <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">
          <span id="adjust_title">—</span>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">
            {{ __('messages.ingredients_col_unit', [], $locale) ?: 'Unit' }}: <span id="adjust_unit">—</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              {{ __('messages.ingredients_col_current', [], $locale) ?: 'Current' }}
            </label>
            <input id="adjust_current" disabled
              class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-100/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50">
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              {{ __('messages.ingredients_col_low', [], $locale) ?: 'Low alert' }}
            </label>
            <input id="adjust_low" disabled
              class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-100/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50">
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              {{ __('messages.ingredients_adjust', [], $locale) ?: 'Adjust' }}
            </label>
            <input id="adjust_delta" name="delta" type="number" step="0.001" placeholder="e.g. +10 or -2.5"
              class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50">
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
              Positive adds stock, negative removes stock.
            </p>
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              {{ __('messages.note', [], $locale) ?: 'Note' }}
            </label>
            <textarea id="adjust_note" name="note" rows="2"
              class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50"
              placeholder="e.g. restock, spoilage, correction..."></textarea>
          </div>
        </div>

        <p id="adjustErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>

        <div class="flex justify-between items-center">
          <button type="button" data-close
            class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs
                   text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-x-circle text-[12px]"></i>
            <span>{{ __('messages.cancel', [], $locale) ?: 'Cancel' }}</span>
          </button>

          <button type="submit"
            class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                   px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
            <i class="bi bi-check2-circle text-[12px]"></i>
            <span>{{ __('messages.save', [], $locale) ?: 'Save' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</dialog>
@endif

@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);

const ING_I18N = {
  loading: @json(__('messages.loading', [], $locale) ?: 'Loading…'),
  range: @json(__('messages.range', [], $locale) ?: 'Showing :from–:to of :total'),
  emptyTitle: @json(__('messages.ingredients_empty_title', [], $locale) ?: 'No ingredients found'),
  emptyBody: @json(__('messages.ingredients_empty_body', [], $locale) ?: 'Try adjusting filters or add a new ingredient.'),
  loadFail: @json(__('messages.ingredients_load_failed', [], $locale) ?: 'Couldn’t load ingredients.'),
  retry: @json(__('messages.retry', [], $locale) ?: 'Retry'),
  low: @json(__('messages.ingredients_status_low', [], $locale) ?: 'Low'),
  ok: @json(__('messages.ingredients_status_ok', [], $locale) ?: 'OK'),

  view: @json(__('messages.view', [], $locale) ?: 'View'),
  edit: @json(__('messages.edit', [], $locale) ?: 'Edit'),
  adjust: @json(__('messages.ingredients_adjust', [], $locale) ?: 'Adjust'),
  deleteAsk: @json(__('messages.ingredients_confirm_delete', [], $locale) ?: 'Delete this ingredient?'),

  created: @json(__('messages.ingredients_toast_created', [], $locale) ?: 'Ingredient created'),
  updated: @json(__('messages.ingredients_toast_updated', [], $locale) ?: 'Ingredient updated'),
  deleted: @json(__('messages.ingredients_toast_deleted', [], $locale) ?: 'Ingredient deleted'),
  savedFail: @json(__('messages.save_failed', [], $locale) ?: 'Save failed'),
};

const state = { q:'', sort:'created_at', dir:'desc', per_page:10, low_only:false, page:1 };

const rows = document.getElementById('rows');
const pager = document.getElementById('pager');
const pageMeta = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

document.getElementById('filterToggle')?.addEventListener('click', () => {
  document.getElementById('filterPanel')?.classList.toggle('hidden');
});

document.getElementById('openCreateBtn')?.addEventListener('click', () => {
  openDialog(document.getElementById('createModal'));
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
  return ING_I18N.range.replace(':from', from).replace(':to', to).replace(':total', p.total);
}

function skeleton(){
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-10">
      <td class="px-3"><div class="skeleton h-4 w-3/4"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-10"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-20 ml-auto"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-20 ml-auto"></div></td>
      <td class="px-3 text-center"><div class="skeleton h-4 w-12 mx-auto"></div></td>
      <td class="px-3"><div class="skeleton h-4 w-28"></div></td>
      <td class="px-3 text-right"><div class="skeleton h-4 w-20 ml-auto"></div></td>
    </tr>
  `).join('');
}

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g,'&quot;'); }
function js(v){ return JSON.stringify(v ?? ''); }
function fmtNum(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function fmtDate(dt){ try { return dt ? new Date(dt).toLocaleString() : '—'; } catch { return '—'; } }
function debounce(fn, ms=350){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), ms); } }

async function load(){
  skeleton();
  const params = {
    q: state.q,
    sort: state.sort,
    dir: state.dir,
    per_page: state.per_page,
    low_only: state.low_only ? 1 : 0,
    page: state.page,
  };

  try{
    const res = await api('/api/ingredients?' + qs(params));
    render(res);
  }catch(e){
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="7">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(ING_I18N.loadFail)}</div>
          <button id="retryBtn"
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${esc(ING_I18N.retry)}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = ING_I18N.loadFail;
    pageMeta.textContent = '';
  }
}

function render(paged){
  const list = paged.data || [];

  if(!list.length){
    rows.innerHTML = `
      <tr><td colspan="7">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(ING_I18N.emptyTitle)}</div>
          <div class="text-sm">${esc(ING_I18N.emptyBody)}</div>
          ${CAN_WRITE ? `
            <button class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-3.5 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition"
                    id="openCreateBtnEmpty">
              <i class="bi bi-plus-lg text-[12px]"></i> {{ __('messages.ingredients_new', [], $locale) ?: 'New Ingredient' }}
            </button>` : ''}
        </div>
      </td></tr>`;
    document.getElementById('openCreateBtnEmpty')?.addEventListener('click', ()=>openDialog(document.getElementById('createModal')));
  } else {
    rows.innerHTML = list.map(i => {
      const isLow = !!i.is_low;
      const pill = isLow
        ? `<span class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2 py-0.5 text-[11px]">
             <span class="h-2 w-2 rounded-full bg-rose-500"></span> ${esc(ING_I18N.low)}
           </span>`
        : `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]">
             <span class="h-2 w-2 rounded-full bg-emerald-500"></span> ${esc(ING_I18N.ok)}
           </span>`;

      let actions = `
        <a href="/ingredients/${i.id}"
           class="tw-tip icon-btn border border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80"
           data-tooltip="${escAttr(ING_I18N.view)}" aria-label="${escAttr(ING_I18N.view)}">
          <i class="bi bi-eye text-[14px] leading-none"></i>
        </a>`;

      if (CAN_WRITE) {
        actions += `
          <button
            onclick='openEdit(${i.id}, ${js(i.name)}, ${js(i.unit)}, ${js(i.low_alert_qty)}, ${js(i.current_qty)})'
            class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200"
            data-tooltip="${escAttr(ING_I18N.edit)}" aria-label="${escAttr(ING_I18N.edit)}">
            <i class="bi bi-pencil text-[14px] leading-none"></i>
          </button>

          <button
            onclick='openAdjust(${i.id}, ${js(i.name)}, ${js(i.unit)}, ${js(i.current_qty)}, ${js(i.low_alert_qty)})'
            class="tw-tip icon-btn border border-rose-600 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring focus:ring-rose-200"
            data-tooltip="${escAttr(ING_I18N.adjust)}" aria-label="${escAttr(ING_I18N.adjust)}">
            <i class="bi bi-arrow-left-right text-[14px] leading-none"></i>
          </button>

          <button
            onclick='onDelete(${i.id})'
            class="tw-tip icon-btn border border-slate-600 text-slate-700 hover:bg-slate-100 dark:border-slate-500 dark:text-slate-200 dark:hover:bg-slate-800"
            data-tooltip="${escAttr('{{ __("messages.delete", [], $locale) ?: "Delete" }}')}"
            aria-label="${escAttr('{{ __("messages.delete", [], $locale) ?: "Delete" }}')}">
            <i class="bi bi-trash text-[14px] leading-none"></i>
          </button>`;
      }

      return `
        <tr class="align-middle">
          <td class="px-3 py-2">
            <div class="font-medium text-slate-900 dark:text-slate-50">${esc(i.name)}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
              ${esc(i.movements_count ?? 0)} {{ __('messages.ingredients_moves', [], $locale) ?: 'moves' }}
            </div>
          </td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(i.unit || '—')}</td>
          <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtNum(i.current_qty))}</td>
          <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(fmtNum(i.low_alert_qty))}</td>
          <td class="px-3 py-2 text-center">${pill}</td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(fmtDate(i.last_restocked_at))}</td>
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

/* Filters */
filterForm?.addEventListener('submit', (e)=>{
  e.preventDefault();
  state.sort = document.getElementById('sort').value;
  state.dir = document.getElementById('dir').value;
  state.per_page = parseInt(document.getElementById('per_page').value || '10', 10);
  state.low_only = document.getElementById('low_only').checked;
  state.page = 1;
  load();
});

document.getElementById('resetBtn')?.addEventListener('click', ()=>{
  filterForm.reset();
  document.getElementById('sort').value = 'created_at';
  document.getElementById('dir').value = 'desc';
  document.getElementById('per_page').value = '10';
  state.sort='created_at'; state.dir='desc'; state.per_page=10; state.low_only=false; state.page=1;
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

/* Delete */
async function onDelete(id){
  if (!confirm(ING_I18N.deleteAsk)) return;
  try{
    await api(`/api/ingredients/${id}`, { method:'DELETE' });
    showToast(ING_I18N.deleted, 'success');
    load();
  }catch(err){
    console.error(err);
    showToast(err?.data?.message || ING_I18N.savedFail, 'danger');
  }
}

/* ===== Modals JS ===== */
@if($canWrite)
/* Create */
document.getElementById('createForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const f = e.target;
  document.getElementById('createErr').textContent = '';

  const payload = {
    name: f.name.value.trim(),
    unit: f.unit.value.trim(),
    low_alert_qty: f.low_alert_qty.value === '' ? null : Number(f.low_alert_qty.value),
    current_qty: f.current_qty.value === '' ? null : Number(f.current_qty.value),
  };

  try{
    const res = await api('/api/ingredients', {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    if (res?.ingredient?.id) {
      showToast(ING_I18N.created, 'success');
      f.reset();
      closeDialog(document.getElementById('createModal'));
      load();
    } else {
      document.getElementById('createErr').textContent = res?.message || ING_I18N.savedFail;
    }
  }catch(err){
    console.error(err);
    document.getElementById('createErr').textContent = err?.data?.message || ING_I18N.savedFail;
  }
});

/* Open Edit */
window.openEdit = function(id, name, unit, low_alert_qty, current_qty){
  const sp = (v, fb='') => { try { return JSON.parse(v); } catch { return fb; } };
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = sp(name, String(name ?? ''));
  document.getElementById('edit_unit').value = sp(unit, String(unit ?? ''));
  document.getElementById('edit_low_alert_qty').value = (sp(low_alert_qty, '') ?? '');
  document.getElementById('edit_current_qty').value = (sp(current_qty, '') ?? '');
  document.getElementById('editErr').textContent = '';
  openDialog(document.getElementById('editModal'));
};

/* Submit Edit */
document.getElementById('editForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const f = e.target;
  const err = document.getElementById('editErr');
  err.textContent = '';

  const id = document.getElementById('edit_id').value;

  const payload = {
    name: document.getElementById('edit_name').value.trim(),
    unit: document.getElementById('edit_unit').value.trim(),
    low_alert_qty: document.getElementById('edit_low_alert_qty').value === '' ? null : Number(document.getElementById('edit_low_alert_qty').value),
    current_qty: document.getElementById('edit_current_qty').value === '' ? null : Number(document.getElementById('edit_current_qty').value),
  };

  try{
    await api(`/api/ingredients/${id}`, {
      method:'PATCH',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });
    showToast(ING_I18N.updated, 'success');
    closeDialog(document.getElementById('editModal'));
    load();
  }catch(ex){
    console.error(ex);
    err.textContent = ex?.data?.message || ING_I18N.savedFail;
  }
});

/* Open Adjust */
window.openAdjust = function(id, name, unit, current_qty, low_alert_qty){
  const sp = (v, fb='') => { try { return JSON.parse(v); } catch { return fb; } };
  document.getElementById('adjust_id').value = id;
  document.getElementById('adjust_title').textContent = sp(name, String(name ?? ''));
  document.getElementById('adjust_unit').textContent = sp(unit, String(unit ?? ''));
  document.getElementById('adjust_current').value = sp(current_qty, '');
  document.getElementById('adjust_low').value = sp(low_alert_qty, '');
  document.getElementById('adjust_delta').value = '';
  document.getElementById('adjust_note').value = '';
  document.getElementById('adjustErr').textContent = '';
  openDialog(document.getElementById('adjustModal'));
};

/* Submit Adjust (update endpoint to your real API) */
document.getElementById('adjustForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const err = document.getElementById('adjustErr');
  err.textContent = '';

  const id = document.getElementById('adjust_id').value;
  const delta = document.getElementById('adjust_delta').value;

  const payload = {
    delta: delta === '' ? null : Number(delta),
    note: document.getElementById('adjust_note').value.trim() || null,
  };

  try{
    // change this route to your real adjust endpoint
    await api(`/api/ingredients/${id}/adjust`, {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });
    showToast(ING_I18N.updated, 'success');
    closeDialog(document.getElementById('adjustModal'));
    load();
  }catch(ex){
    console.error(ex);
    err.textContent = ex?.data?.message || ING_I18N.savedFail;
  }
});
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
