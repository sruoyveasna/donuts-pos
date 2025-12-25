@if($canWrite)
@php $locale = app()->getLocale(); @endphp

<style>
  /* Center dialog nicely (no weird top positioning) */
  #createDiscountModal[open]{
    display:flex;
    align-items:center;
    justify-content:center;
  }
  /* Optional prettier backdrop */
  #createDiscountModal::backdrop{
    background: rgba(2,6,23,.55);
    backdrop-filter: blur(6px);
  }
</style>

<dialog id="createDiscountModal" class="m-0 p-0 bg-transparent">
  <div class="w-[92vw] max-w-2xl overflow-hidden rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40">

    {{-- Header (smaller height) --}}
    <div class="px-4 md:px-5 py-2.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400
                text-slate-50 flex items-center justify-between gap-3">
      <div class="min-w-0">
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
            <i class="bi bi-ticket-perforated text-[13px]"></i>
          </span>
          <span class="truncate">{{ __('messages.new_discount', [], $locale) ?: 'New discount' }}</span>
        </div>
        <p class="mt-0.5 text-[11px] text-rose-50/90 truncate">
          {{ __('messages.discounts_create_subtitle', [], $locale) ?: 'Create a promo code for your POS.' }}
        </p>
      </div>

      <button type="button"
              class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
              data-close aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body (NO forced scroll) --}}
    <form id="createDiscountForm" class="px-4 md:px-5 py-3 space-y-3">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 min-w-0">

        {{-- Name --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Name</label>
          <input name="name" required
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
                 placeholder="Discount name">
        </div>

        {{-- Code --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Code (optional)</label>
          <input name="code"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
                 placeholder="SUMMER10">
          {{-- Hide helper on small screens to save height --}}
          <p class="hidden sm:block text-[11px] leading-snug text-slate-400">
            If empty, discount can be “automatic” (your POS logic decides).
          </p>
        </div>

        {{-- Type --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Type</label>
          <select name="type"
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
          <input name="value" type="number" min="0" step="0.01" required
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-rose-300 focus:border-rose-400"
                 placeholder="10">
        </div>

        {{-- Min subtotal --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Min subtotal (KHR)</label>
          <input name="min_subtotal_khr" type="number" min="0" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50"
                 placeholder="0">
        </div>

        {{-- Max cap --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Max discount cap (KHR)</label>
          <input name="max_discount_khr" type="number" min="0" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50"
                 placeholder="0">
        </div>

        {{-- Starts --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Starts at</label>
          <input name="starts_at" type="date"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Ends --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Ends at</label>
          <input name="ends_at" type="date"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- ✅ Usage limit + ✅ Active in one row --}}
        <div class="space-y-1 min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Usage limit</label>
          <input name="usage_limit" type="number" min="1" step="1"
                 class="w-full min-w-0 rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-1.5 text-sm
                        text-slate-900 dark:text-slate-50"
                 placeholder="100">
        </div>

        <div class="min-w-0">
          <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Status</label>
          <div class="mt-1 flex items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-800/80
                      bg-slate-50/60 dark:bg-slate-900/60 px-3 py-2">
            <div class="min-w-0">
              <p class="text-xs font-medium text-slate-700 dark:text-slate-200">Active</p>
              {{-- hide hint on small to save height --}}
              <p class="hidden sm:block text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                Disable to keep it hidden on POS.
              </p>
            </div>
            <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
              <input id="createDiscountActive" name="is_active" type="checkbox"
                     class="h-3.5 w-3.5 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70" checked>
              <span>Active</span>
            </label>
          </div>
        </div>
      </div>

      <p id="createDiscountErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>

      {{-- Footer (smaller vertical padding) --}}
      <div class="flex justify-between items-center pt-0.5">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                       px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-arrow-left-short text-[13px]"></i><span>Cancel</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full
                       bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                       px-4 py-1.5 text-xs font-semibold text-white
                       shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-plus-circle text-[12px]"></i><span>Create discount</span>
        </button>
      </div>
    </form>
  </div>
</dialog>
@endif
