{{-- resources/views/categories/partials/create-modal.blade.php --}}
@if($canWrite)
@php $locale = app()->getLocale(); @endphp

<dialog id="createModal" class="m-0 bg-transparent">
  <div
    class="w-[92vw] max-w-2xl overflow-hidden
           rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40">

    {{-- Header --}}
    <div
      class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
             bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400
             text-slate-50 flex items-center justify-between gap-3">

      <div class="min-w-0">
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
            <i class="bi bi-folder-plus text-[13px]"></i>
          </span>
          <span class="truncate">
            {{ __('messages.categories_create_title', [], $locale) ?: 'New category' }}
          </span>
        </div>
        <p class="mt-0.5 text-[11px] text-rose-50/90 truncate">
          {{ __('messages.categories_create_subtitle', [], $locale) ?: 'Create a new group for your menu items.' }}
        </p>
      </div>

      <button type="button"
              class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full
                     bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              data-close
              aria-label="{{ __('messages.categories_button_close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body (scrolls vertically if needed) --}}
    <div class="max-h-[75vh] overflow-y-auto">
      <form id="createForm" class="px-4 md:px-5 py-4 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 min-w-0">

          {{-- Name --}}
          <div class="space-y-1 min-w-0">
            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
              {{ __('messages.categories_field_name_label', [], $locale) ?: 'Name' }}
            </label>
            <input
              type="text"
              name="name"
              class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50
                     placeholder:text-slate-400 dark:placeholder:text-slate-500
                     focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
              placeholder="{{ __('messages.categories_field_name_placeholder', [], $locale) ?: 'Category name' }}"
              required>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">
              {{ __('messages.categories_field_name_hint', [], $locale) ?: 'This is visible to cashiers on the POS.' }}
            </p>
          </div>

          {{-- Slug --}}
          <div class="space-y-1 min-w-0">
            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
              {{ __('messages.categories_field_slug_label', [], $locale) ?: 'Slug (optional)' }}
            </label>
            <input
              type="text"
              name="slug"
              class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                     bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                     text-slate-900 dark:text-slate-50
                     placeholder:text-slate-400 dark:placeholder:text-slate-500
                     focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
              placeholder="{{ __('messages.categories_field_slug_placeholder', [], $locale) ?: 'drinks, donuts, hot-drinks…' }}">
            <p class="text-[11px] text-slate-400 dark:text-slate-500">
              {{ __('messages.categories_field_slug_hint', [], $locale) ?: 'Used in exports, reports or APIs.' }}
            </p>
          </div>
        </div>

        {{-- Active toggle --}}
        <div
          class="mt-1 flex items-center justify-between gap-3 rounded-xl border border-slate-200/80
                 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-900/60
                 px-3 py-2.5">
          <div class="min-w-0">
            <p class="text-xs font-medium text-slate-700 dark:text-slate-200">
              {{ __('messages.categories_field_active_label', [], $locale) ?: 'Active on POS' }}
            </p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
              {{ __('messages.categories_field_active_hint', [], $locale) ?: 'Turn off to create this category as inactive.' }}
            </p>
          </div>

          <label class="shrink-0 inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
            <input
              id="create_active"
              type="checkbox"
              name="is_active"
              class="h-3.5 w-3.5 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70"
              checked>
            <span>{{ __('messages.categories_field_active_badge', [], $locale) ?: 'Active' }}</span>
          </label>
        </div>

        {{-- Error --}}
        <p id="createErr" class="text-[11px] text-rose-500 mt-1 min-h-[1rem]"></p>

        {{-- Footer --}}
        <div class="flex justify-between items-center pt-1">
          <button type="button"
                  data-close
                  class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                         px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                         dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
            <i class="bi bi-arrow-left-short text-[13px]"></i>
            <span>{{ __('messages.categories_button_cancel', [], $locale) ?: 'Cancel' }}</span>
          </button>

          <button type="submit"
                  class="inline-flex items-center gap-1.5 rounded-full
                         bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                         px-4 py-1.5 text-xs font-semibold text-white
                         shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
            <i class="bi bi-plus-circle text-[12px]"></i>
            <span>{{ __('messages.categories_button_create', [], $locale) ?: 'Create category' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</dialog>
@endif
