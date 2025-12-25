@if($canWrite)
@php $locale = app()->getLocale(); @endphp

<style>
  #editDiscountModal[open]{
    display:flex;
    align-items:center;
    justify-content:center;
  }
  #editDiscountModal::backdrop{
    background: rgba(2,6,23,.55);
    backdrop-filter: blur(6px);
  }
</style>

<dialog id="editDiscountModal" class="m-0 p-0 bg-transparent">
  <div class="w-[92vw] max-w-2xl overflow-hidden rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-indigo-200/40">

    {{-- Header (smaller height) --}}
    <div class="px-4 md:px-5 py-2.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800
                text-slate-50 flex items-center justify-between gap-3">
      <div class="min-w-0">
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
            <i class="bi bi-pencil-square text-[13px]"></i>
          </span>
          <span class="truncate">{{ __('messages.edit', [], $locale) ?: 'Edit discount' }}</span>
        </div>
        <p class="mt-0.5 text-[11px] text-slate-200/80 truncate">
          {{ __('messages.discounts_edit_subtitle', [], $locale) ?: 'Update discount settings.' }}
        </p>
      </div>

      <button type="button"
              class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
              data-close aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body (NO forced scroll) --}}
    <form id="editDiscountForm" class="px-4 md:px-5 py-3 space-y-3">
      @csrf
      @method('PATCH')

      <input type="hidden" id="edit_discount_id">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 min-w-0">

        {{-- Name --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Name</label>
          <input id="edit_discount_name" required
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400">
        </div>

        {{-- Code --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Code (optional)</label>
          <input id="edit_discount_code"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400">
          {{-- Height saver --}}
          <p class="hidden sm:block text-[11px] leading-snug text-slate-400">
            Leave empty to make it “automatic” (POS logic decides).
          </p>
        </div>

        {{-- Type --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Type</label>
          <select id="edit_discount_type"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700
                         bg-slate-50 dark:bg-slate-900 px-3 py-1.5 text-sm
                         text-slate-900 dark:text-slate-50">
            <option value="percent">Percent</option>
            <option value="fixed_khr">Fixed (KHR)</option>
          </select>
        </div>

        {{-- Value --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Value</label>
          <input id="edit_discount_value" type="number" min="0" step="0.01" required
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Min subtotal --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Min subtotal (KHR)</label>
          <input id="edit_discount_min_subtotal_khr" type="number" min="0" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Max cap --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Max discount cap (KHR)</label>
          <input id="edit_discount_max_discount_khr" type="number" min="0" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Starts --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Starts at</label>
          <input id="edit_discount_starts_at" type="date"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Ends --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Ends at</label>
          <input id="edit_discount_ends_at" type="date"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- ✅ Usage limit + ✅ Active in one row --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Usage limit</label>
          <input id="edit_discount_usage_limit" type="number" min="1" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Status</label>
          <div class="mt-1 flex items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-800/80
                      bg-slate-50/60 dark:bg-slate-900/60 px-3 py-2">
            <div class="min-w-0">
              <p class="text-xs font-medium text-slate-700 dark:text-slate-200">Active</p>
              <p class="hidden sm:block text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                Disable to hide it from POS.
              </p>
            </div>
            <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
              <input id="editDiscountActive" type="checkbox"
                     class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-500 focus:ring-indigo-400/70">
              <span>Active</span>
            </label>
          </div>
        </div>
      </div>

      <p id="editDiscountErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>

      <div class="flex justify-between items-center pt-0.5">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i><span>Cancel</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full
                       bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400
                       px-4 py-1.5 text-xs font-semibold text-white
                       shadow-md shadow-sky-300/70 hover:shadow-sky-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i><span>Save changes</span>
        </button>
      </div>
    </form>
  </div>
</dialog>
@endif
