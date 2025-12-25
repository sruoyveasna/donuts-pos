{{-- resources/views/recipes/partials/edit-modal.blade.php --}}
@if($canWrite)
{{-- EDIT RECIPE MODAL --}}
<dialog id="recipeModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(920px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">

    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-pencil-square text-[13px]"></i>
        </span>
        <span id="recipeModalTitle">{{ __('messages.recipes_edit', [], $locale) ?: 'Edit Recipe' }}</span>
      </div>
      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], $locale) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="px-4 md:px-5 py-4 overflow-y-auto overflow-x-hidden flex-1 min-h-0">

      {{-- Scope selector --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <div class="md:col-span-2">
          <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
            {{ __('messages.scope', [], $locale) ?: 'Scope' }}
          </div>
          <div class="flex flex-wrap gap-3">
            <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
              <input type="radio" name="scope" value="base" checked
                     class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70">
              <span>{{ __('messages.base_recipe', [], $locale) ?: 'Base recipe' }}</span>
            </label>
            <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
              <input type="radio" name="scope" value="variant"
                     class="h-3 w-3 rounded border-slate-300 text-rose-500 focus:ring-rose-400/70">
              <span>{{ __('messages.variant_recipe', [], $locale) ?: 'Variant recipe' }}</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
            {{ __('messages.variant', [], $locale) ?: 'Variant' }}
          </label>
          <select id="variantSelect"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900
                   px-2 py-2 text-xs text-slate-800 dark:text-slate-100 disabled:opacity-50"
            disabled>
            <option value="">{{ __('messages.select_variant', [], $locale) ?: 'Select variant' }}</option>
          </select>
        </div>
      </div>

      <div class="soft-divider my-4"></div>

      {{-- Lines table --}}
      <div class="flex items-center justify-between gap-2 mb-2">
        <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">
          {{ __('messages.ingredients', [], $locale) ?: 'Ingredients' }}
          <span id="lineCount" class="text-[11px] font-medium text-slate-500 dark:text-slate-400">—</span>
        </div>

        <button id="addLineBtn"
          class="inline-flex items-center gap-1.5 rounded-full
                 bg-gradient-to-r from-slate-900 to-slate-700
                 dark:from-slate-800 dark:to-slate-700
                 px-3 py-1.5 text-[11px] md:text-xs font-semibold text-white
                 shadow-sm hover:opacity-90 transition">
          <i class="bi bi-plus-lg text-[12px]"></i>
          <span>{{ __('messages.add', [], $locale) ?: 'Add' }}</span>
        </button>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-800/80">
        <table class="min-w-full text-xs md:text-sm border-collapse">
          <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400
                        bg-slate-50/90 dark:bg-slate-950/60 border-b border-slate-200/70 dark:border-slate-800/80">
            <tr>
              <th class="px-3 py-2 text-left font-medium w-[44%]">{{ __('messages.ingredients', [], $locale) ?: 'Ingredient' }}</th>
              <th class="px-3 py-2 text-right font-medium w-[14%]">{{ __('messages.qty', [], $locale) ?: 'Qty' }}</th>
              <th class="px-3 py-2 text-left font-medium w-[10%]">{{ __('messages.unit', [], $locale) ?: 'Unit' }}</th>
              <th class="px-3 py-2 text-right font-medium w-[14%]">{{ __('messages.stock', [], $locale) ?: 'Stock' }}</th>
              <th class="px-3 py-2 text-center font-medium w-[10%]">{{ __('messages.status', [], $locale) ?: 'Status' }}</th>
              <th class="px-3 py-2 text-right font-medium w-[8%]">{{ __('messages.actions', [], $locale) ?: 'Actions' }}</th>
            </tr>
          </thead>
          <tbody id="editLines" class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr><td colspan="6" class="py-6 text-center text-slate-500 dark:text-slate-400">{{ __('messages.loading', [], $locale) ?: 'Loading…' }}</td></tr>
          </tbody>
        </table>
      </div>

      <p id="recipeErr" class="mt-3 text-[11px] text-rose-500 min-h-[1rem]"></p>

      <div class="mt-4 flex justify-between items-center">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                       dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-x-circle text-[12px]"></i>
          <span>{{ __('messages.cancel', [], $locale) ?: 'Cancel' }}</span>
        </button>

        <button id="saveRecipeBtn"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                       px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.save', [], $locale) ?: 'Save' }}</span>
        </button>
      </div>
    </div>
  </div>
</dialog>

{{-- INGREDIENT LINE MODAL (nested) --}}
<dialog id="lineModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(560px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">

    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-plus-lg text-[13px]"></i>
        </span>
        <span id="lineModalTitle">{{ __('messages.add_ingredient', [], $locale) ?: 'Add ingredient' }}</span>
      </div>
      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="lineForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden flex-1 min-h-0">
      @csrf
      <input type="hidden" id="line_index" value="">

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.ingredients', [], $locale) ?: 'Ingredient' }}
          </label>

          <div class="flex items-stretch rounded-full border border-slate-200 dark:border-slate-700
                      bg-slate-50/80 dark:bg-slate-900/80
                      focus-within:border-sky-400 focus-within:ring-1 focus-within:ring-sky-300">
            <span class="px-2 flex items-center text-slate-400"><i class="bi bi-search text-[13px]"></i></span>
            <input id="ingSearch"
              class="w-full px-2 py-1.5 text-xs md:text-sm outline-none rounded-e-full bg-transparent
                     text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500"
              placeholder="{{ __('messages.search_ingredient', [], $locale) ?: 'Search ingredient…' }}">
          </div>

          <div class="mt-2 max-h-52 overflow-y-auto no-scrollbar rounded-2xl border border-slate-200/70 dark:border-slate-800/80">
            <div id="ingList" class="divide-y divide-slate-100 dark:divide-slate-800">
              <div class="p-3 text-[11px] text-slate-500 dark:text-slate-400">{{ __('messages.loading', [], $locale) ?: 'Loading…' }}</div>
            </div>
          </div>

          <input type="hidden" id="ingredient_id" required>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
            {{ __('messages.selected', [], $locale) ?: 'Selected' }}:
            <span id="selectedIngName" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
            · {{ __('messages.unit', [], $locale) ?: 'Unit' }}:
            <span id="selectedIngUnit" class="font-semibold text-slate-900 dark:text-slate-50">—</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
            {{ __('messages.qty', [], $locale) ?: 'Quantity' }}
          </label>
          <input id="line_qty" type="number" step="0.001" min="0.001" required
            class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                   bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                   text-slate-900 dark:text-slate-50">
        </div>

        <p id="lineErr" class="text-[11px] text-rose-500 min-h-[1rem]"></p>

        <div class="flex justify-between items-center">
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
            <span>{{ __('messages.save', [], $locale) ?: 'Save' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</dialog>
@endif
