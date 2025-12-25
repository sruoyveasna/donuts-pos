{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);

  // ✅ No /api/roles call. Load roles here.
  $rolesList = \App\Models\Role::query()->select('id','name')->orderBy('name')->get();
@endphp

@section('title', __('messages.users_title', [], $locale) ?: 'Users')

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

  .card-sticky-header{
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(16px);
  }

  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }

  #rows tr:hover { background: rgba(244,114,182,.05); }

  .skeleton{ position: relative; overflow: hidden; background: #f3f4f6; border-radius: .375rem; }
  .dark .skeleton{ background: rgba(15,23,42,.9); }
  .skeleton::after{
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
  @keyframes shimmer{ 100%{ transform: translateX(100%); } }

  /* ✅ icon-only action buttons */
  .icon-btn{
    height: 32px;
    width: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
  }

  /* ✅ hide scrollbar but keep scroll */
  .no-scrollbar::-webkit-scrollbar{ width:0; height:0; }
  .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
</style>
@endpush

@section('content')
{{-- ✅ Fill available height + only table area scrolls --}}
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Page heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        👥 {{ __('messages.users_title', [], $locale) ?: 'Users' }}
      </h1>
      <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.users_subtitle', [], $locale) ?: 'Manage staff accounts, roles, and access.' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        {{ __('messages.users_summary_loading', [], $locale) ?: 'Loading…' }}
      </p>
    </div>

    {{-- Tiny legend --}}
    <div class="flex flex-wrap items-center gap-2 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        {{ __('messages.users_status_active', [], $locale) ?: 'Active' }}
      </span>
      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
        {{ __('messages.users_status_archived', [], $locale) ?: 'Archived' }}
      </span>
    </div>
  </div>

  {{-- Card --}}
  <div
    class="rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
           backdrop-blur-2xl overflow-hidden
           flex flex-col flex-1 min-h-0">

    {{-- Toolbar (sticky) --}}
    <div
      class="card-sticky-header px-4 md:px-5 py-3 md:py-3.5
             border-b border-white/60 dark:border-slate-800/80
             bg-gradient-to-r from-white/95 via-white/80 to-white/70
             dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
      <div class="flex flex-wrap items-center gap-2">

        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-[11px] text-slate-100 px-3 py-1
                    dark:bg-slate-800 shadow-sm shadow-slate-900/40">
          <i class="bi bi-people text-[12px] text-rose-300"></i>
          <span>{{ __('messages.users_badge_title', [], $locale) ?: 'User manager' }}</span>
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
              placeholder="{{ __('messages.users_search_placeholder', [], $locale) ?: 'Search by name, email, role…' }}">
            <button
              class="tw-tip px-2 text-slate-400 hover:text-rose-500"
              id="clearSearch"
              type="button"
              data-tooltip="{{ __('messages.users_tooltip_clear', [], $locale) ?: 'Clear search' }}">
              <i class="bi bi-x-lg text-[11px]"></i>
            </button>
          </div>
        </div>

        {{-- Filter toggle --}}
        <button id="filterToggle" type="button"
                class="tw-tip inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                       px-2.5 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                       hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200
                       transition"
                data-tooltip="{{ __('messages.users_filters_label', [], $locale) ?: 'Filters' }}">
          <i class="bi bi-sliders text-[12px]"></i>
          <span class="hidden sm:inline">
            {{ __('messages.users_filters_label', [], $locale) ?: 'Filters' }}
          </span>
        </button>

        {{-- Refresh --}}
        <button
          class="tw-tip inline-flex items-center justify-center rounded-full border border-slate-300/80
                 dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                 h-8 w-8 text-slate-600 dark:text-slate-200 hover:text-rose-500 hover:border-rose-400
                 transition"
          id="refreshBtn"
          type="button"
          data-tooltip="{{ __('messages.users_refresh_label', [], $locale) ?: 'Refresh' }}">
          <i class="bi bi-arrow-clockwise text-[13px]"></i>
        </button>

        {{-- New --}}
        @if($canWrite)
        <button id="openCreateBtn"
                class="inline-flex items-center gap-1.5 rounded-full
                       bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                       px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
                       shadow-md shadow-rose-300/70 hover:shadow-rose-400/80
                       transition">
          <i class="bi bi-plus-lg text-[12px]"></i>
          <span class="hidden sm:inline">{{ __('messages.users_new_button', [], $locale) ?: 'New User' }}</span>
          <span class="sm:hidden">{{ __('messages.users_new_button_short', [], $locale) ?: 'New' }}</span>
        </button>
        @endif
      </div>
    </div>

    {{-- Filters panel --}}
    <div id="filterPanel" class="hidden shrink-0">
      <div class="px-4 md:px-5 pt-3 pb-3.5">
        <form id="filterForm"
              class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end text-[11px] md:text-xs">

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.users_filter_sort_by', [], $locale) ?: 'Sort by' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="sort" name="sort">
              <option value="name">{{ __('messages.users_filter_sort_name', [], $locale) ?: 'Name' }}</option>
              <option value="email">{{ __('messages.users_filter_sort_email', [], $locale) ?: 'Email' }}</option>
              <option value="role">{{ __('messages.users_filter_sort_role', [], $locale) ?: 'Role' }}</option>
              <option value="created_at">{{ __('messages.users_filter_sort_created', [], $locale) ?: 'Created' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.users_filter_direction', [], $locale) ?: 'Direction' }}
            </label>
            <select
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                     px-2 py-1.5 text-xs text-slate-800 dark:text-slate-100"
              id="dir" name="dir">
              <option value="asc">{{ __('messages.sort_asc', [], $locale) ?: 'ASC' }}</option>
              <option value="desc">{{ __('messages.sort_desc', [], $locale) ?: 'DESC' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
              {{ __('messages.users_filter_per_page', [], $locale) ?: 'Per page' }}
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
              {{ __('messages.users_filter_visibility', [], $locale) ?: 'Visibility' }}
            </label>
            <div class="flex flex-wrap gap-4 mt-1.5">
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input
                  class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70"
                  type="checkbox"
                  id="with_trashed"
                  name="with_trashed">
                <span>{{ __('messages.users_filter_with_trashed', [], $locale) ?: 'Include archived' }}</span>
              </label>
              <label class="inline-flex items-center gap-2 text-[11px] text-slate-700 dark:text-slate-200">
                <input
                  class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70"
                  type="checkbox"
                  id="only_trashed"
                  name="only_trashed">
                <span>{{ __('messages.users_filter_only_archived', [], $locale) ?: 'Only archived' }}</span>
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
                <span>{{ __('messages.users_filter_apply', [], $locale) ?: 'Apply' }}</span>
              </button>
              <button
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400
                       px-3 py-1.5 text-[11px] text-slate-800 hover:bg-slate-100
                       dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                type="button"
                id="resetBtn">
                <i class="bi bi-arrow-counterclockwise text-[12px]"></i>
                <span>{{ __('messages.users_filter_reset', [], $locale) ?: 'Reset' }}</span>
              </button>
            </div>
          </div>

        </form>
      </div>
      <div class="soft-divider"></div>
    </div>

    {{-- ✅ Scroll area: ONLY this section scrolls; no top padding --}}
    <div class="flex-1 min-h-0 overflow-x-auto overflow-y-auto no-scrollbar">
      <table class="min-w-full text-xs md:text-sm border-collapse">
        <thead class="sticky top-0 z-10 text-[11px] uppercase tracking-wide
                      text-slate-500 dark:text-slate-400
                      bg-slate-50/90 dark:bg-slate-950/80 backdrop-blur
                      border-b border-slate-200/70 dark:border-slate-800/80">
          <tr>
            <th class="px-3 py-2 text-left font-medium w-[34%]">
              {{ __('messages.users_col_user', [], $locale) ?: 'User' }}
            </th>
            <th class="px-3 py-2 text-left font-medium w-[30%]">
              {{ __('messages.users_col_email', [], $locale) ?: 'Email' }}
            </th>
            <th class="px-3 py-2 text-left font-medium w-[18%]">
              {{ __('messages.users_col_role', [], $locale) ?: 'Role' }}
            </th>
            <th class="px-3 py-2 text-center font-medium w-[10%]">
              {{ __('messages.users_col_status', [], $locale) ?: 'Status' }}
            </th>
            <th class="px-3 py-2 text-right font-medium w-[8%]">
              {{ __('messages.users_col_actions', [], $locale) ?: 'Actions' }}
            </th>
          </tr>
        </thead>
        <tbody id="rows" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr>
            <td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">
              {{ __('messages.users_summary_loading', [], $locale) ?: 'Loading…' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- ✅ Footer / Pagination (glass) --}}
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

{{-- ✅ Modals moved to partials (same idea as categories) --}}
@include('users.partials.create-modal', ['canWrite' => $canWrite, 'rolesList' => $rolesList])
@include('users.partials.edit-modal',   ['canWrite' => $canWrite, 'rolesList' => $rolesList])

@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);

const USERS_I18N = {
  summaryLoading: @json(__('messages.users_summary_loading', [], $locale) ?: 'Loading…'),
  summaryRange: @json(__('messages.users_summary_range', [], $locale) ?: 'Showing :from–:to of :total'),

  statusActive: @json(__('messages.users_status_active', [], $locale) ?: 'Active'),
  statusArchived: @json(__('messages.users_status_archived', [], $locale) ?: 'Archived'),

  loadFailedTitle: @json(__('messages.users_load_failed_title', [], $locale) ?: "Couldn’t load users"),
  loadFailedMessage: @json(__('messages.users_load_failed_message', [], $locale) ?: 'Please try again.'),
  retry: @json(__('messages.users_retry', [], $locale) ?: 'Retry'),

  emptyTitle: @json(__('messages.users_empty_title', [], $locale) ?: 'No users found'),
  emptyBody: @json(__('messages.users_empty_body', [], $locale) ?: 'Try adjusting your filters or add a new user.'),
  newButton: @json(__('messages.users_new_button', [], $locale) ?: 'New User'),

  tooltipEdit: @json(__('messages.users_tooltip_edit', [], $locale) ?: 'Edit'),
  tooltipArchive: @json(__('messages.users_tooltip_archive', [], $locale) ?: 'Archive'),
  tooltipRestore: @json(__('messages.users_tooltip_restore', [], $locale) ?: 'Restore'),

  toastCreated: @json(__('messages.users_toast_created', [], $locale) ?: 'User created'),
  toastCreateFailed: @json(__('messages.users_toast_create_failed', [], $locale) ?: 'Create failed'),
  toastUpdated: @json(__('messages.users_toast_updated', [], $locale) ?: 'User updated'),
  toastUpdateFailed: @json(__('messages.users_toast_update_failed', [], $locale) ?: 'Update failed'),
  toastArchived: @json(__('messages.users_toast_archived', [], $locale) ?: 'Archived'),
  toastArchiveFailed: @json(__('messages.users_toast_archive_failed', [], $locale) ?: 'Archive failed'),
  toastRestored: @json(__('messages.users_toast_restored', [], $locale) ?: 'Restored'),
  toastRestoreFailed: @json(__('messages.users_toast_restore_failed', [], $locale) ?: 'Restore failed'),

  confirmArchive: @json(__('messages.users_confirm_archive', [], $locale) ?: 'Archive this user?'),
};

const state = {
  q: '',
  sort: 'name',
  dir: 'asc',
  per_page: 10,
  with_trashed: false,
  only_trashed: false,
  page: 1,
};

const rows        = document.getElementById('rows');
const pager       = document.getElementById('pager');
const pageMeta    = document.getElementById('pageMeta');
const summaryText = document.getElementById('summaryText');
const filterForm  = document.getElementById('filterForm');
const searchInput = document.getElementById('q');

document.getElementById('filterToggle')?.addEventListener('click', () => {
  document.getElementById('filterPanel')?.classList.toggle('hidden');
});

document.getElementById('openCreateBtn')?.addEventListener('click', () => {
  openDialog(document.getElementById('createModal'));
});

function qs(obj) {
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k,v])=>{
    if (v === '' || v === null || v === false) return;
    p.set(k, String(v));
  });
  return p.toString();
}

function fmtRange(p) {
  if (!p?.total) return '';
  const from  = ((p.current_page - 1) * p.per_page) + 1;
  const to    = Math.min(p.current_page * p.per_page, p.total);
  const total = p.total;
  return USERS_I18N.summaryRange.replace(':from', from).replace(':to', to).replace(':total', total);
}

function skeleton() {
  rows.innerHTML = Array.from({length: 6}).map(()=>`
    <tr class="h-12">
      <td class="px-3 py-2">
        <div class="flex items-center gap-3">
          <div class="skeleton h-9 w-9 rounded-full"></div>
          <div class="space-y-2">
            <div class="skeleton h-3 w-40"></div>
            <div class="skeleton h-3 w-28"></div>
          </div>
        </div>
      </td>
      <td class="px-3 py-2"><div class="skeleton h-4 w-48"></div></td>
      <td class="px-3 py-2"><div class="skeleton h-4 w-28"></div></td>
      <td class="px-3 py-2 text-center"><div class="skeleton h-4 w-16 mx-auto"></div></td>
      <td class="px-3 py-2 text-right"><div class="skeleton h-4 w-20 inline-block"></div></td>
    </tr>
  `).join('');
}

async function load() {
  skeleton();
  const params = {
    q: state.q,
    sort: state.sort,
    dir: state.dir,
    per_page: state.per_page,
    with_trashed: state.with_trashed ? 1 : 0,
    only_trashed: state.only_trashed ? 1 : 0,
    page: state.page,
  };

  try {
    const res = await api('/api/users?' + qs(params));
    render(res);
  } catch (e) {
    console.error(e);
    rows.innerHTML = `
      <tr><td colspan="5">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-wifi-off block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(USERS_I18N.loadFailedTitle)}</div>
          <div class="text-sm mb-3">${esc(USERS_I18N.loadFailedMessage)}</div>
          <button id="retryBtn"
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-clockwise text-[12px]"></i> ${esc(USERS_I18N.retry)}
          </button>
        </div>
      </td></tr>`;
    document.getElementById('retryBtn')?.addEventListener('click', load);
    summaryText.textContent = USERS_I18N.loadFailedTitle;
    pageMeta.textContent = '';
  }
}

function render(paged) {
  const list = paged.data || [];

  if (!list.length) {
    rows.innerHTML = `
      <tr><td colspan="5">
        <div class="py-12 text-center text-slate-500 dark:text-slate-400">
          <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
          <div class="font-medium mb-1">${esc(USERS_I18N.emptyTitle)}</div>
          <div class="text-sm">${esc(USERS_I18N.emptyBody)}</div>
          ${CAN_WRITE ? `
            <button
              class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-3.5 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition"
              id="openCreateBtnEmpty">
              <i class="bi bi-plus-lg text-[12px]"></i> ${esc(USERS_I18N.newButton)}
            </button>` : ``}
        </div>
      </td></tr>`;
    document.getElementById('openCreateBtnEmpty')?.addEventListener('click', () => {
      openDialog(document.getElementById('createModal'));
    });
  } else {
    rows.innerHTML = list.map(u => {
      const isTrashed = !!u.deleted_at;

      const status = isTrashed
        ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-slate-400"></span> ${esc(USERS_I18N.statusArchived)}
           </span>`
        : `<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]">
             <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span> ${esc(USERS_I18N.statusActive)}
           </span>`;

      const avatar = u.avatar_url
        ? `<img src="${escAttr(u.avatar_url)}" class="h-9 w-9 rounded-full object-cover border border-white/70 dark:border-slate-800" alt="avatar"
                onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">`
        : '';

      const fallback = `<div class="${u.avatar_url ? 'hidden' : ''} h-9 w-9 rounded-full flex items-center justify-center
                               bg-rose-500/90 text-white text-[11px] font-semibold">
                          ${esc(u.avatar_fallback || 'U')}
                        </div>`;

      let actions = '';
      if (CAN_WRITE) {
        if (isTrashed) {
          actions = `
            <div class="flex items-center gap-2 justify-end flex-nowrap whitespace-nowrap">
              <button
                onclick='onRestore(${u.id})'
                class="tw-tip icon-btn border border-emerald-600 text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring focus:ring-emerald-200"
                data-tooltip="${escAttr(USERS_I18N.tooltipRestore)}"
                aria-label="${escAttr(USERS_I18N.tooltipRestore)}">
                <i class="bi bi-arrow-counterclockwise text-[14px] leading-none"></i>
              </button>
            </div>`;
        } else {
          actions = `
            <div class="flex items-center gap-2 justify-end flex-nowrap whitespace-nowrap">
              <button
                onclick='openEdit(${u.id}, ${js(u.name)}, ${js(u.email)}, ${u.role?.id ?? 'null'})'
                class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200"
                data-tooltip="${escAttr(USERS_I18N.tooltipEdit)}"
                aria-label="${escAttr(USERS_I18N.tooltipEdit)}">
                <i class="bi bi-pencil text-[14px] leading-none"></i>
              </button>

              <button
                onclick='onArchive(${u.id})'
                class="tw-tip icon-btn border border-rose-600 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring focus:ring-rose-200"
                data-tooltip="${escAttr(USERS_I18N.tooltipArchive)}"
                aria-label="${escAttr(USERS_I18N.tooltipArchive)}">
                <i class="bi bi-archive text-[14px] leading-none"></i>
              </button>
            </div>`;
        }
      }

      return `
        <tr class="align-middle">
          <td class="px-3 py-2">
            <div class="flex items-center gap-3 min-w-0">
              <div class="shrink-0">
                ${avatar}${fallback}
              </div>
              <div class="min-w-0">
                <div class="font-medium text-slate-900 dark:text-slate-50 truncate">${esc(u.name || '—')}</div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">${esc(u.role?.name || '—')}</div>
              </div>
            </div>
          </td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(u.email || '—')}</td>
          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(u.role?.name || '—')}</td>
          <td class="px-3 py-2 text-center">${status}</td>
          <td class="px-3 py-2 text-right">${actions}</td>
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

// helpers
function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escAttr(s){ return esc(s).replace(/"/g, '&quot;'); }
function js(v){ return JSON.stringify(v ?? ''); }
function debounce(fn, ms=350){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), ms); } }

// Filters/Search
filterForm.addEventListener('submit', e => {
  e.preventDefault();
  state.sort = document.getElementById('sort').value;
  state.dir = document.getElementById('dir').value;
  state.per_page = parseInt(document.getElementById('per_page').value || '10', 10);
  state.with_trashed = document.getElementById('with_trashed').checked;
  state.only_trashed = document.getElementById('only_trashed').checked;
  state.page = 1;
  load();
});

document.getElementById('resetBtn').addEventListener('click', ()=>{
  filterForm.reset();
  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value = 'name';
  document.getElementById('dir').value = 'asc';
  state.sort='name'; state.dir='asc'; state.per_page=10;
  state.with_trashed=false; state.only_trashed=false;
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

// ✅ Create/Edit now use partial modals forms like categories
@if($canWrite)
document.getElementById('createForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const err  = document.getElementById('createErr');
  err.textContent = '';

  const payload = {
    name: form.name.value.trim(),
    email: form.email.value.trim(),
    password: form.password.value,
    role_id: parseInt(form.role_id.value || '0', 10),
  };

  try {
    await api('/api/users', {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    showToast(USERS_I18N.toastCreated,'success');
    form.reset();
    closeDialog(document.getElementById('createModal'));
    load();
  } catch (e) {
    console.error(e);
    err.textContent = e?.data?.message || USERS_I18N.toastCreateFailed;
  }
});

function openEdit(id, name, email, roleId) {
  // name/email are passed as JSON strings via js()
  const sp = (v, fb='') => { try { return JSON.parse(v); } catch { return fb; } };

  document.getElementById('editErr').textContent = '';
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = sp(name, String(name ?? ''));
  document.getElementById('edit_email').value = sp(email, String(email ?? ''));
  document.getElementById('edit_password').value = '';
  if (roleId) document.getElementById('edit_role_id').value = String(roleId);
  openDialog(document.getElementById('editModal'));
}

document.getElementById('editForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const err = document.getElementById('editErr');
  err.textContent = '';

  const id = document.getElementById('edit_id').value;

  const payload = {
    name: document.getElementById('edit_name').value.trim(),
    email: document.getElementById('edit_email').value.trim(),
    role_id: parseInt(document.getElementById('edit_role_id').value || '0', 10),
  };

  const pwd = document.getElementById('edit_password').value;
  if (pwd) payload.password = pwd;

  try {
    await api(`/api/users/${id}`, {
      method:'PATCH',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    showToast(USERS_I18N.toastUpdated,'success');
    closeDialog(document.getElementById('editModal'));
    load();
  } catch (e) {
    console.error(e);
    err.textContent = e?.data?.message || USERS_I18N.toastUpdateFailed;
  }
});

async function onArchive(id) {
  if (!confirm(USERS_I18N.confirmArchive)) return;
  try {
    const res = await api(`/api/users/${id}`, { method:'DELETE' });
    showToast(res?.message || USERS_I18N.toastArchived,'success');
    load();
  } catch (e) {
    console.error(e);
    showToast(e?.data?.message || USERS_I18N.toastArchiveFailed,'danger');
  }
}

async function onRestore(id) {
  try {
    const res = await api(`/api/users/${id}/restore`, { method:'POST' });
    showToast(res?.message || USERS_I18N.toastRestored,'success');
    load();
  } catch (e) {
    console.error(e);
    showToast(e?.data?.message || USERS_I18N.toastRestoreFailed,'danger');
  }
}
@endif

// init
(function initUI(){
  document.getElementById('per_page').value = '10';
  document.getElementById('sort').value = 'name';
  document.getElementById('dir').value = 'asc';
  searchInput.value = state.q;
  load();
})();
</script>
@endpush
