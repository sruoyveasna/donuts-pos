{{-- resources/views/menu/index.blade.php --}}
@extends('layouts.app')

@section('title', __('messages.menu_title', [], app()->getLocale()) ?? 'Menu')

@push('head')
<style>
  /* Sticky header inside the card (toolbar stays visible while scrolling table) */
  .card-sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(16px);
  }

  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }

  /* table hover – light purple to match menu accent */
  #rows tr:hover { background: rgba(167,139,250,.06); }

  /* Tailwind-ish skeleton blocks */
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

  /* ✅ category chip truncate (single-line + ellipsis) */
  .chip-truncate{
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* ✅ icon-only action buttons */
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
@php
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);
  $locale   = app()->getLocale();
@endphp

{{-- ✅ Fill available height + only table area scrolls --}}
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Page heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🍩 {{ __('messages.menu_title', [], $locale) ?? 'Menu' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.menu_subtitle', [], $locale) ?? 'Manage your donut menu items, sizes and prices.' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.menu_summary_loading', [], $locale) ?? 'Loading…' }}
      </p>
    </div>

    {{-- Tiny legend / hint (status) --}}
    <div class="flex flex-wrap items-center gap-2 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        {{ __('messages.menu_status_active', [], $locale) ?? 'Active (Shown on POS)' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 dark:bg-yellow-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
        {{ __('messages.menu_status_hidden', [], $locale) ?? 'Hidden (Not shown on POS)' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
        {{ __('messages.menu_status_deleted', [], $locale) ?? 'Deleted / archived' }}
      </span>
    </div>
  </div>

  {{-- Card --}}
  <div
    class="rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-violet-200/40
           backdrop-blur-2xl overflow-hidden
           flex flex-col flex-1 min-h-0">

    {{-- Toolbar (sticky) --}}
    <div
      class="card-sticky-header px-4 md:px-5 py-3 md:py-3.5
             border-b border-white/60 dark:border-slate-800/80
             bg-gradient-to-r from-white/95 via-white/80 to-white/70
             dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
      <div class="flex flex-wrap items-center gap-2">

        {{-- Left title badge --}}
        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-shop-window text-[12px] text-violet-300"></i>
          <span>{{ __('messages.menu_manager_badge', [], $locale) ?? 'Menu manager' }}</span>
        </div>

        {{-- Show archived toggle --}}
        <label class="flex items-center gap-2 text-[11px] md:text-xs text-slate-700 dark:text-slate-200 ml-1">
          <input id="showArchived" type="checkbox"
                 class="h-3.5 w-3.5 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70">
          <span id="showArchivedLabel">
            {{ __('messages.menu_show_archived_label_active', [], $locale) ?? 'Showing active' }}
          </span>
        </label>

        <div class="flex-1"></div>

        {{-- Quick Search --}}
        <div class="grow md:grow-0 min-w-[220px] max-w-[320px]">
          <div
            class="flex items-stretch rounded-full border border-slate-200 dark:border-slate-700
                   bg-slate-50/80 dark:bg-slate-900/80
                   focus-within:border-violet-400 focus-within:ring-1 focus-within:ring-violet-300">
            <span class="px-2 flex items-center text-slate-400">
              <i class="bi bi-search text-[13px]"></i>
            </span>
            <input
              id="q"
              name="q"
              class="w-full px-2 py-1.5 text-xs md:text-sm outline-none rounded-e-full
                     bg-transparent text-slate-800 dark:text-slate-100
                     placeholder:text-slate-400 dark:placeholder:text-slate-500"
              placeholder="{{ __('messages.menu_search_placeholder', [], $locale) ?? 'Search name, size…' }}">
            <button
              class="tw-tip px-2 text-slate-400 hover:text-violet-500"
              id="clearSearch"
              type="button"
              data-tooltip="{{ __('messages.menu_tooltip_clear', [], $locale) ?? 'Clear' }}">
              <i class="bi bi-x-lg text-[11px]"></i>
            </button>
          </div>
        </div>

        {{-- Filter toggle --}}
        <button id="filterToggle" type="button"
                class="tw-tip inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                       px-2.5 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                       hover:border-violet-300 dark:hover:border-violet-400 hover:text-violet-600 dark:hover:text-violet-200
                       transition"
                data-tooltip="{{ __('messages.menu_tooltip_filters', [], $locale) ?? 'Filters' }}">
          <i class="bi bi-sliders text-[12px]"></i>
          <span class="hidden sm:inline">{{ __('messages.menu_filters_label', [], $locale) ?? 'Filters' }}</span>
        </button>

        {{-- Refresh --}}
        <button
          class="tw-tip inline-flex items-center justify-center rounded-full border border-slate-300/80
                 dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                 h-8 w-8 text-slate-600 dark:text-slate-200 hover:text-violet-500 hover:border-violet-400
                 transition"
          id="refreshBtn"
          type="button"
          data-tooltip="{{ __('messages.menu_tooltip_refresh', [], $locale) ?? 'Refresh' }}">
          <i class="bi bi-arrow-clockwise text-[13px]"></i>
        </button>

        {{-- New --}}
        @if($canWrite)
        <button id="openCreateBtn"
                class="inline-flex items-center gap-1.5 rounded-full
                       bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400
                       px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
                       shadow-md shadow-violet-300/70 hover:shadow-violet-400/80
                       transition">
          <i class="bi bi-plus-lg text-[12px]"></i>
          <span class="hidden sm:inline">
            {{ __('messages.menu_add_button', [], $locale) ?? 'Add item' }}
          </span>
          <span class="sm:hidden">
            {{ __('messages.menu_add_button_short', [], $locale) ?? 'Add' }}
          </span>
        </button>
        @endif
      </div>
    </div>

    {{-- Filters panel --}}
    <div id="filterPanel" class="hidden shrink-0">
      <div class="px-4 md:px-5 pt-3 pb-3.5">
        <form id="filterForm"
              class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 items-end text-[11px] md:text-xs">

          <div class="col-span-2">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_category', [], $locale) ?? 'Category' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="f_category_id" name="category_id">
              <option value="">{{ __('messages.menu_filter_category_all', [], $locale) ?? 'All' }}</option>
              {{-- options injected via JS --}}
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_min_price', [], $locale) ?? 'Min price' }}
            </label>
            <input type="number" min="0" step="0.01"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                          px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
                   id="min_price" name="min_price">
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_max_price', [], $locale) ?? 'Max price' }}
            </label>
            <input type="number" min="0" step="0.01"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                          px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
                   id="max_price" name="max_price">
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_sort_by', [], $locale) ?? 'Sort by' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="sort" name="sort">
              <option value="name">{{ __('messages.menu_filter_sort_name', [], $locale) ?? 'Name' }}</option>
              <option value="price">{{ __('messages.menu_filter_sort_price', [], $locale) ?? 'Price' }}</option>
              <option value="created_at">{{ __('messages.menu_filter_sort_created', [], $locale) ?? 'Created' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_direction', [], $locale) ?? 'Direction' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="dir" name="dir">
              <option value="asc">{{ __('messages.sort_asc', [], $locale) ?? 'ASC' }}</option>
              <option value="desc">{{ __('messages.sort_desc', [], $locale) ?? 'DESC' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_per_page', [], $locale) ?? 'Per page' }}
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

          <div class="col-span-2">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.menu_filter_flags', [], $locale) ?? 'Flags' }}
            </label>
            <div class="flex flex-wrap gap-4 mt-1.5">
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input
                  class="h-3 w-3 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70"
                  type="checkbox"
                  id="visible_only"
                  name="visible_only">
                <span>{{ __('messages.menu_filter_visible_only', [], $locale) ?? 'Visible only' }}</span>
              </label>
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input
                  class="h-3 w-3 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70"
                  type="checkbox"
                  id="with_trashed"
                  name="with_trashed">
                <span>{{ __('messages.menu_filter_with_trashed', [], $locale) ?? 'Include archived' }}</span>
              </label>
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input
                  class="h-3 w-3 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70"
                  type="checkbox"
                  id="include_variants"
                  name="include_variants"
                  checked>
                <span>{{ __('messages.menu_filter_include_variants', [], $locale) ?? 'Include variants' }}</span>
              </label>
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
                {{ __('messages.menu_filter_apply', [], $locale) ?? 'Apply' }}
              </button>
              <button
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400
                       px-3 py-1.5 text-[11px] text-slate-800 hover:bg-slate-100
                       dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                type="button"
                id="resetBtn">
                <i class="bi bi-arrow-counterclockwise text-[12px]"></i>
                {{ __('messages.menu_filter_reset', [], $locale) ?? 'Reset' }}
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="soft-divider"></div>
    </div>

    {{-- ✅ Scroll area: ONLY this section scrolls (scrollbar hidden)
        ✅ removed outer padding so THEAD sticks flush to divider (no gap above header)
    --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm table-fixed border-collapse">
        <colgroup>
          <col style="width: 52px" />
          <col style="width: 92px" />
          <col style="width: 260px" />
          <col style="width: 240px" />
          <col style="width: 140px" />
          <col style="width: 160px" />
          <col style="width: 200px" />
          <col style="width: 140px" />
        </colgroup>

        {{-- ✅ sticky table header, flush to top of scroll container --}}
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/90 dark:bg-slate-950/80 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium">{{ __('messages.menu_col_index', [], $locale) ?? '#' }}</th>
            <th class="px-2 py-2 text-left font-medium">{{ __('messages.menu_col_image', [], $locale) ?? 'Image' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_name', [], $locale) ?? 'Name' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_sizes', [], $locale) ?? 'Sizes' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_price', [], $locale) ?? 'Price' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_status', [], $locale) ?? 'Status' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_category', [], $locale) ?? 'Category' }}</th>
            <th class="px-4 py-2 text-left font-medium">{{ __('messages.menu_col_actions', [], $locale) ?? 'Actions' }}</th>
          </tr>
        </thead>

        <tbody id="rows" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr>
            <td colspan="8" class="py-6 text-center text-slate-500 dark:text-slate-400">
              {{ __('messages.menu_summary_loading', [], $locale) ?? 'Loading…' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- ✅ Footer / Pagination (glass + tighter vertical padding) --}}
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

{{-- Create Modal --}}
@if($canWrite)
<dialog id="createModal" class="bg-transparent">
  <div
    class="rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-violet-200/40
           overflow-hidden w-full max-w-lg">

    <form id="createForm" method="dialog" class="px-4 md:px-5 py-4 space-y-4" enctype="multipart/form-data">
      {{-- Header --}}
      <div
        class="mb-2 -mx-4 -mt-4 px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
               bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-500
               text-slate-50 flex items-center justify-between gap-2">
        <div>
          <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
              <i class="bi bi-plus-lg text-[13px]"></i>
            </span>
            <span>{{ __('messages.menu_create_title', [], $locale) ?? 'Add menu item' }}</span>
          </div>
          <p class="mt-0.5 text-[11px] text-violet-100/90">
            {{ __('messages.menu_create_subtitle', [], $locale) ?? 'Upload an image, set price and category for this item.' }}
          </p>
        </div>

        <button
          type="button"
          onclick="closeDialog(document.getElementById('createModal'))"
          class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
          aria-label="{{ __('messages.close', [], $locale) ?? 'Close' }}">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      {{-- Body --}}
      <div class="space-y-3">
        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.menu_create_image_label', [], $locale) ?? 'Image' }}
          </label>
          <input type="file" name="image" id="create_image"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-2 py-1.5 text-xs
                        text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
          <img id="create_image_preview" class="hidden h-20 mt-2 rounded-lg object-cover border border-slate-200 dark:border-slate-700" />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.menu_create_name_label', [], $locale) ?? 'Name' }}
          </label>
          <input
            type="text"
            name="name"
            id="create_name"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                   text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
            required>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.menu_create_price_label', [], $locale) ?? 'Price ($)' }}
          </label>
          <input
            type="number"
            step="0.01"
            min="0"
            name="price"
            id="create_price"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                   text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
            required>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.menu_create_category_label', [], $locale) ?? 'Category' }}
          </label>
          <select
            name="category_id"
            id="create_category_id"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                   text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
            <option value="">
              {{ __('messages.menu_create_category_placeholder', [], $locale) ?? 'Select category' }}
            </option>
          </select>
        </div>

        <div class="flex items-center gap-2 mt-1">
          <input type="checkbox" id="create_is_active"
                 class="h-3.5 w-3.5 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70"
                 checked>
          <label for="create_is_active" class="text-xs text-slate-700 dark:text-slate-200">
            {{ __('messages.menu_create_is_active_label', [], $locale) ?? 'Visible on POS' }}
          </label>
        </div>

        {{-- Discount block (unchanged) --}}
        <div class="mt-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-3 space-y-3">
          <div class="flex flex-wrap items-center gap-3">
            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
              {{ __('messages.menu_create_discount_section_label', [], $locale) ?? 'Discount' }}
            </label>
            <select id="create_discount_type"
                    class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                           bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
              <option value="">{{ __('messages.menu_create_discount_none', [], $locale) ?? 'None' }}</option>
              <option value="percent">{{ __('messages.menu_create_discount_percent', [], $locale) ?? 'Percent (%)' }}</option>
              <option value="fixed">{{ __('messages.menu_create_discount_fixed', [], $locale) ?? 'Fixed ($)' }}</option>
            </select>
            <input
              id="create_discount_value"
              type="number"
              step="0.01"
              min="0"
              class="w-28 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                     bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100"
              placeholder="10">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                {{ __('messages.menu_create_discount_starts', [], $locale) ?? 'Starts' }}
              </label>
              <input
                id="create_discount_starts_at"
                type="datetime-local"
                class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                       bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                {{ __('messages.menu_create_discount_ends', [], $locale) ?? 'Ends' }}
              </label>
              <input
                id="create_discount_ends_at"
                type="datetime-local"
                class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                       bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
            </div>
          </div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400" id="create_discount_hint"></div>
        </div>
      </div>

      {{-- Error message --}}
      <p id="createErr" class="text-[11px] text-rose-500 mt-1 min-h-[1rem]"></p>

      {{-- Footer --}}
      <div class="flex justify-between items-center pt-1">
        <button
          type="button"
          onclick="closeDialog(document.getElementById('createModal'))"
          class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i>
          <span>{{ __('messages.menu_create_cancel', [], $locale) ?? __('messages.cancel', [], $locale) ?? 'Cancel' }}</span>
        </button>

        <button
          type="submit"
          class="inline-flex items-center gap-1.5 rounded-full
                 bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400
                 px-4 py-1.5 text-xs font-semibold text-white
                 shadow-md shadow-violet-300/70 hover:shadow-violet-400/80
                 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.menu_create_save', [], $locale) ?? 'Save item' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>
@endif

{{-- Include the shared Edit Modal + Manage Variants Modal --}}
@includeWhen($canWrite, 'menu.partials.edit-modal')
@includeWhen($canWrite, 'menu.partials.manage-variants-modal')

@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);

// i18n pieces used in JS (same pattern as categories page)
const LABEL_SHOW_ACTIVE  = @json(__('messages.menu_show_archived_label_active', [], $locale) ?? 'Showing active');
const LABEL_SHOW_DELETED = @json(__('messages.menu_show_archived_label_deleted', [], $locale) ?? 'Showing deleted');
const SUMMARY_RANGE_TMPL = @json(__('messages.menu_summary_range', [], $locale) ?? 'Showing :from–:to of :total');
const SUMMARY_FAILED     = @json(__('messages.menu_summary_failed', [], $locale) ?? 'Load failed');

const TEXT_LOAD_FAILED_TITLE   = @json(__('messages.menu_load_failed_title', [], $locale) ?? 'Couldn’t load menu');
const TEXT_LOAD_FAILED_MESSAGE = @json(__('messages.menu_load_failed_message', [], $locale) ?? 'Please try again.');
const TEXT_RETRY               = @json(__('messages.menu_retry', [], $locale) ?? 'Retry');

const TEXT_EMPTY_TITLE = @json(__('messages.menu_empty_title', [], $locale) ?? 'No items found');
const TEXT_EMPTY_BODY  = @json(__('messages.menu_empty_body', [], $locale) ?? 'Try adjusting your filters or add a new item.');
const TEXT_ADD_ITEM    = @json(__('messages.menu_add_button', [], $locale) ?? 'Add item');

const TEXT_STATUS_DELETED = @json(__('messages.menu_status_deleted', [], $locale) ?? 'Deleted');
const TEXT_STATUS_ACTIVE  = @json(__('messages.menu_status_active', [], $locale) ?? 'Active (POS)');
const TEXT_STATUS_HIDDEN  = @json(__('messages.menu_status_hidden', [], $locale) ?? 'Hidden (Not on POS)');
const TEXT_SIZES_SUFFIX   = @json(__('messages.menu_sizes_suffix', [], $locale) ?? 'sizes');

const CONFIRM_DELETE  = @json(__('messages.menu_confirm_delete', [], $locale) ?? 'Permanently delete this item? This cannot be undone.');
const CONFIRM_ARCHIVE = @json(__('messages.menu_confirm_archive', [], $locale) ?? 'Archive this item?');

const TOAST_ADDED          = @json(__('messages.menu_toast_added', [], $locale) ?? 'Menu item added');
const TOAST_ARCHIVED       = @json(__('messages.menu_toast_archived', [], $locale) ?? 'Archived');
const TOAST_DELETED        = @json(__('messages.menu_toast_deleted', [], $locale) ?? 'Permanently deleted');
const TOAST_RESTORED       = @json(__('messages.menu_toast_restored', [], $locale) ?? 'Item restored');
const TOAST_DELETE_FAILED  = @json(__('messages.menu_toast_delete_failed', [], $locale) ?? 'Delete failed');
const TOAST_RESTORE_FAILED = @json(__('messages.menu_toast_restore_failed', [], $locale) ?? 'Restore failed');
const CREATE_FAILED        = @json(__('messages.menu_create_error_generic', [], $locale) ?? 'Create failed');

// tooltips for icon-only actions
const TIP_EDIT      = @json(__('messages.menu_action_edit', [], $locale) ?? 'Edit');
const TIP_ARCHIVE   = @json(__('messages.menu_action_archive', [], $locale) ?? 'Archive');
const TIP_RESTORE   = @json(__('messages.menu_action_restore', [], $locale) ?? 'Restore');
const TIP_DEL_PERM  = @json(__('messages.menu_action_delete_permanent', [], $locale) ?? 'Delete permanently');

// ---------- State ----------
const state = {
  q: '',
  sort: 'name',
  dir: 'asc',
  per_page: 10,
  with_trashed: false,
  visible_only: false,
  include_variants: true,
  category_id: '',
  min_price: '',
  max_price: '',
  page: 1,
};

let categoriesCache = []; // [{id,name},...]

// ---------- DOM ----------
const rows        = document.getElementById('rows');
const pager       = document.getElementById('pager');
const pageMeta    = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm  = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

// toggles
document.getElementById('filterToggle').addEventListener('click', () => {
  document.getElementById('filterPanel').classList.toggle('hidden');
});
document.getElementById('refreshBtn').addEventListener('click', load);
document.getElementById('showArchived').addEventListener('change', (e)=>{
  const show = e.target.checked;
  state.with_trashed = show;
  state.visible_only = !show;
  document.getElementById('showArchivedLabel').textContent = show ? LABEL_SHOW_DELETED : LABEL_SHOW_ACTIVE;
  state.page = 1;
  load();
});

// Search
searchInput.addEventListener('input', debounce(() => {
  state.q = searchInput.value.trim();
  state.page = 1;
  load();
}, 300));
document.getElementById('clearSearch').addEventListener('click', () => {
  searchInput.value = ''; state.q = ''; state.page = 1; load();
});

// Filters
filterForm.addEventListener('submit', e => {
  e.preventDefault();
  state.category_id      = document.getElementById('f_category_id').value || '';
  state.min_price        = document.getElementById('min_price').value || '';
  state.max_price        = document.getElementById('max_price').value || '';
  state.sort             = document.getElementById('sort').value;
  state.dir              = document.getElementById('dir').value;
  state.per_page         = parseInt(document.getElementById('per_page').value || '10', 10);
  state.visible_only     = document.getElementById('visible_only').checked;
  state.with_trashed     = document.getElementById('with_trashed').checked;
  state.include_variants = document.getElementById('include_variants').checked;
  state.page = 1;
  load();
});

document.getElementById('resetBtn').addEventListener('click', ()=>{
  filterForm.reset();
  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value     = 'name';
  document.getElementById('dir').value      = 'asc';
  state.category_id      = '';
  state.min_price        = '';
  state.max_price        = '';
  state.sort             = 'name';
  state.dir              = 'asc';
  state.per_page         = 10;
  state.visible_only     = false;
  state.with_trashed     = false;
  state.include_variants = true;
  state.page             = 1;
  load();
});

// ---------- Helpers ----------
function qs(obj){
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k, v]) => {
    if (v === undefined || v === '' || v === null || v === false) return;
    p.set(k, String(v));
  });
  return p.toString();
}

function fmtRange(p){
  if(!p?.total) return '';
  const from = ((p.current_page-1)*p.per_page)+1;
  const to   = Math.min(p.current_page*p.per_page, p.total);
  return SUMMARY_RANGE_TMPL
    .replace(':from', from)
    .replace(':to', to)
    .replace(':total', p.total);
}

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function js(s){ return JSON.stringify(s ?? ''); }
function debounce(fn,ms=350){ let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),ms);} }
function money(n){ const num = Number(n ?? 0); return `${num.toFixed(2)} $`; }
function imgUrl(path){
  if(!path) return '{{ asset('menu.png') }}';
  if(/^https?:\/\//i.test(path)) return path;
  const base = (window.APP_API_BASE || '').replace(/\/$/,'');
  return `${base}/storage/${path}`;
}
function sizesTokens(item){
  const list = Array.isArray(item?.variants) ? item.variants : [];
  const tokens = []; const seen = new Set();
  for (const v of list){
    if(v?.deleted_at) continue;
    const t = (v?.sku || '').toString().trim();
    if(!t) continue;
    const key=t.toUpperCase();
    if(!seen.has(key)){ seen.add(key); tokens.push(t); }
  }
  return tokens;
}
function sizesText(item){
  const t = sizesTokens(item);
  const shown = t.slice(0,6);
  const extra = Math.max(t.length-shown.length,0);
  return {
    display: shown.join(', ')+(extra>0?` +${extra}`:''),
    full: t.join(', '),
    count: t.length
  };
}

// ---------- Loaders ----------
async function loadCategories(){
  try{
    const res  = await api('/api/categories?visible_only=1&per_page=1000&sort=name&dir=asc');
    const list = Array.isArray(res?.data) ? res.data : [];
    categoriesCache        = list;
    window.categoriesCache = list; // for edit modal partial

    const fSel = document.getElementById('f_category_id');
    fSel.innerHTML = '<option value="">' +
      @json(__('messages.menu_filter_category_all', [], $locale) ?? 'All') +
      '</option>' +
      list.map(c=>`<option value="${c.id}">${esc(c.name)}</option>`).join('');

    const cSel = document.getElementById('create_category_id');
    if (cSel) {
      cSel.innerHTML = '<option value="">' +
        @json(__('messages.menu_create_category_placeholder', [], $locale) ?? 'Select category') +
        '</option>' +
        list.map(c=>`<option value="${c.id}">${esc(c.name)}</option>`).join('');
    }

    document.dispatchEvent(new CustomEvent('categories-loaded'));
  }catch(e){
    console.error('Failed to load categories', e);
  }
}

function skeleton(){
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-16">
      <td class="px-3"><div class="skeleton h-4 w-8"></div></td>
      <td class="px-2"><div class="skeleton h-10 w-16"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-3/4 mb-2"></div><div class="skeleton h-3 w-1/3"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-2/3"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-1/3"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-20"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-28"></div></td>
      <td class="px-4"><div class="skeleton h-4 w-20"></div></td>
    </tr>
  `).join('');
}

async function load(){
  skeleton();
  const params = {
    q: state.q,
    per_page: state.per_page,
    with_trashed: state.with_trashed ? 1 : 0,
    visible_only: state.visible_only ? 1 : 0,
    include_variants: state.include_variants ? 1 : 0,
    category_id: state.category_id || undefined,
    min_price: state.min_price || undefined,
    max_price: state.max_price || undefined,
    page: state.page,
  };
  try{
    const res = await api('/api/menu/items?' + qs(params));
    render(res);
  }catch(e){
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="8">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${TEXT_LOAD_FAILED_TITLE}</div>
          <div class="text-sm mb-3">${esc(e?.message || TEXT_LOAD_FAILED_MESSAGE)}</div>
          <button id="retryBtn"
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${TEXT_RETRY}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = SUMMARY_FAILED;
    pageMeta.textContent = '';
  }
}

function render(paged){
  const data = Array.isArray(paged?.data) ? paged.data : [];

  if (state.sort !== 'name') {
    const by  = state.sort;
    const dir = state.dir === 'asc' ? 1 : -1;
    data.sort((a,b)=>{
      const va = by==='price'      ? Number(a.final_price ?? a.price ?? 0)
               : by==='created_at' ? new Date(a.created_at).getTime()
               : (a.name || '').toLowerCase();
      const vb = by==='price'      ? Number(b.final_price ?? b.price ?? 0)
               : by==='created_at' ? new Date(b.created_at).getTime()
               : (b.name || '').toLowerCase();
      if (typeof va === 'string' && typeof vb === 'string') return va.localeCompare(vb)*dir;
      if (va < vb) return -1*dir;
      if (va > vb) return  1*dir;
      return 0;
    });
  }

  if (!data.length){
    rows.innerHTML = `
      <tr><td colspan="8">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${TEXT_EMPTY_TITLE}</div>
          <div class="text-sm">${TEXT_EMPTY_BODY}</div>
          @if($canWrite)
          <button
            class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400 px-3.5 py-1.5 text-xs font-semibold text-white shadow-md shadow-violet-300/70 hover:shadow-violet-400/80 transition"
            id="openCreateBtnEmpty">
            <i class="bi bi-plus-lg text-[12px]"></i> ${TEXT_ADD_ITEM}
          </button>
          @endif
        </div>
      </td></tr>`;
    document.getElementById('openCreateBtnEmpty')?.addEventListener('click', () => {
      openDialog(document.getElementById('createModal'));
    });
  } else {
    rows.innerHTML = data.map((it, idx)=>{
      const sizes = sizesText(it);

      const status = it.deleted_at
        ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 text-slate-700 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-slate-400"></span> ${TEXT_STATUS_DELETED}
           </span>`
        : (it.is_active
          ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]">
               <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span> ${TEXT_STATUS_ACTIVE}
             </span>`
          : `<span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-50 text-yellow-700 px-2 py-0.5 text-[11px]">
               <span class="inline-block h-2 w-2 rounded-full bg-yellow-500"></span> ${TEXT_STATUS_HIDDEN}
             </span>`
        );

      const priceHtml = (it.has_active_discount
        ? `<span class="line-through opacity-60 mr-1">${money(it.price)}</span><span class="font-semibold">${money(it.final_price ?? it.price)}</span>`
        : `<span class="font-semibold">${money(it.final_price ?? it.price)}</span>`
      );

      const cat = it.category?.name
        ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-semibold chip-truncate"
                 title="${esc(it.category.name)}">${esc(it.category.name)}</span>`
        : '—';

      let actions = '';
      if (CAN_WRITE){
        if (it.deleted_at){
          actions = `
            <div class="flex items-center gap-2 justify-start md:justify-end flex-nowrap whitespace-nowrap">
              <button
                class="tw-tip icon-btn border border-emerald-600 text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring focus:ring-emerald-200"
                data-tooltip="${esc(TIP_RESTORE)}"
                aria-label="${esc(TIP_RESTORE)}"
                onclick="onRestore(${it.id})">
                <i class="bi bi-arrow-counterclockwise text-[14px] leading-none"></i>
              </button>

              <button
                class="tw-tip icon-btn border border-rose-600 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring focus:ring-rose-200"
                data-tooltip="${esc(TIP_DEL_PERM)}"
                aria-label="${esc(TIP_DEL_PERM)}"
                onclick="onDelete(${it.id}, true)">
                <i class="bi bi-trash3 text-[14px] leading-none"></i>
              </button>
            </div>`;
        } else {
          actions = `
            <div class="flex items-center gap-2 justify-start md:justify-end flex-nowrap whitespace-nowrap">
              <button
                class="tw-tip icon-btn border border-violet-600 text-violet-700 hover:bg-violet-50 focus:outline-none focus:ring focus:ring-violet-200"
                data-tooltip="${esc(TIP_EDIT)}"
                aria-label="${esc(TIP_EDIT)}"
                onclick='openEdit(${it.id}, ${js(it)})'>
                <i class="bi bi-pencil text-[14px] leading-none"></i>
              </button>

              <button
                class="tw-tip icon-btn border border-rose-600 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring focus:ring-rose-200"
                data-tooltip="${esc(TIP_ARCHIVE)}"
                aria-label="${esc(TIP_ARCHIVE)}"
                onclick="onDelete(${it.id}, false)">
                <i class="bi bi-archive text-[14px] leading-none"></i>
              </button>
            </div>`;
        }
      }

      return `
        <tr class="align-middle hover:bg-violet-50/40">
          <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
            ${(paged.current_page-1)*paged.per_page + idx + 1}
          </td>
          <td class="px-2 py-3">
            <img src="${imgUrl(it.image)}"
                 alt=""
                 class="w-14 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                 onerror="this.onerror=null;this.src='{{ asset('menu.png') }}';">
          </td>
          <td class="px-4 py-3">
            <div class="font-semibold text-slate-900 dark:text-slate-50 truncate" title="${esc(it.name)}">
              ${esc(it.name)}
            </div>
            ${sizes.count>0 ? `<div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">${sizes.count} ${TEXT_SIZES_SUFFIX}</div>` : ''}
          </td>
          <td class="px-4 py-3">
            <div class="truncate" title="${esc(sizes.full)}">
              <span class="text-[11px] text-slate-600 dark:text-slate-300">${esc(sizes.display || '—')}</span>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-900 dark:text-slate-50">
            ${priceHtml}
          </td>
          <td class="px-4 py-3">
            ${status}
          </td>
          <td class="px-4 py-3">
            ${cat}
          </td>
          <td class="px-4 py-3">
            ${actions}
          </td>
        </tr>`;
    }).join('');
  }

  const meta = fmtRange(paged);
  summaryText.textContent = meta || '—';
  pageMeta.textContent    = meta || '';
  buildPager(paged);
}

function buildPager(p){
  const { current_page, last_page } = p;
  pager.innerHTML = '';
  if (!last_page || last_page <= 1) return;

  const add = (label, page, disabled=false, active=false) => {
    const li   = document.createElement('li');
    const base = 'px-3 py-1.5 text-xs md:text-sm rounded-full border';
    li.innerHTML = `<a href="#" class="${
      disabled
        ? base+' text-slate-400 border-slate-200 cursor-not-allowed dark:border-slate-700'
        : active
          ? base+' text-white bg-violet-500 border-violet-500 shadow-sm shadow-violet-300/70'
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

// ---------- Create (unchanged logic) ----------
@if($canWrite)
document.getElementById('openCreateBtn')?.addEventListener('click', () => {
  document.getElementById('createErr').textContent = '';
  document.getElementById('createForm').reset();
  document.getElementById('create_image_preview').classList.add('hidden');
  openDialog(document.getElementById('createModal'));
});
document.getElementById('create_image')?.addEventListener('change', (e)=>{
  const f   = e.target.files?.[0];
  const img = document.getElementById('create_image_preview');
  if (f){
    img.src = URL.createObjectURL(f);
    img.classList.remove('hidden');
  } else {
    img.classList.add('hidden');
  }
});
document.getElementById('create_discount_type')?.addEventListener('change', syncCreateHint);
document.getElementById('create_discount_value')?.addEventListener('input', syncCreateHint);
function syncCreateHint(){
  const t = document.getElementById('create_discount_type').value;
  const h = document.getElementById('create_discount_hint');
  if (t === 'percent') {
    h.textContent = @json(__('messages.menu_create_discount_hint_percent', [], $locale) ?? 'Max 100%');
  } else if (t === 'fixed') {
    h.textContent = @json(__('messages.menu_create_discount_hint_fixed', [], $locale) ?? 'Cannot exceed price');
  } else {
    h.textContent = '';
  }
}
document.getElementById('createForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();

  const fd = new FormData();
  fd.append('name',  document.getElementById('create_name').value.trim());
  fd.append('price', String(document.getElementById('create_price').value || ''));
  const cat = document.getElementById('create_category_id').value;
  if (cat) fd.append('category_id', cat);
  fd.append('is_active', document.getElementById('create_is_active').checked ? '1' : '0');

  const t = document.getElementById('create_discount_type').value;
  if (t){
    fd.append('discount_type', t);
    fd.append('discount_value', String(document.getElementById('create_discount_value').value || ''));
    const s  = document.getElementById('create_discount_starts_at').value;
    const e2 = document.getElementById('create_discount_ends_at').value;
    if (s)  fd.append('discount_starts_at', s);
    if (e2) fd.append('discount_ends_at', e2);
  }
  const f = document.getElementById('create_image').files?.[0];
  if (f) fd.append('image', f);

  try{
    await api('/api/menu/items', { method: 'POST', body: fd });
    showToast(TOAST_ADDED,'success');
    closeDialog(document.getElementById('createModal'));
    state.page = 1;
    load();
  }catch(err){
    document.getElementById('createErr').textContent = err?.data?.message || CREATE_FAILED;
    console.error(err);
  }
});
@endif

// ---------- Edit / Delete / Restore ----------
@if($canWrite)
function openEdit(id, item){
  window.__editItem = { id, ...item };
  if (typeof window.__prepEditModal === 'function') {
    window.__prepEditModal(window.__editItem);
  }
  document.getElementById('editErr').textContent = '';
  openDialog(document.getElementById('editModal'));
}

async function onDelete(id, force){
  if (!confirm(force ? CONFIRM_DELETE : CONFIRM_ARCHIVE)) return;
  try{
    const qs = force ? '?force=1' : '';
    await api(`/api/menu/items/${id}${qs}`, { method:'DELETE' });
    showToast(force ? TOAST_DELETED : TOAST_ARCHIVED,'success');
    load();
  }catch(e){
    showToast(TOAST_DELETE_FAILED,'danger');
  }
}
async function onRestore(id){
  try{
    await api(`/api/menu/items/${id}/restore`, { method:'POST' });
    showToast(TOAST_RESTORED,'success');
    load();
  }catch(e){
    showToast(TOAST_RESTORE_FAILED,'danger');
  }
}
@endif

// ---------- init ----------
(async function initUI(){
  window.APP_API_BASE = (document.querySelector('meta[name="api-base"]')?.content || (location.origin));

  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value     = 'name';
  document.getElementById('dir').value      = 'asc';
  searchInput.value                         = state.q;

  await loadCategories();
  load();
})();
</script>
@endpush
