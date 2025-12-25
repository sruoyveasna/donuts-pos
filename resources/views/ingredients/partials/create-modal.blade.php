<dialog id="createModal" class="bg-transparent">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-[min(460px,92vw)] max-h-[calc(100vh-2rem)]
              flex flex-col">

    {{-- Header --}}
    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400 text-slate-50
                flex items-center justify-between gap-2">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-plus-lg text-[13px]"></i>
        </span>
        <span>{{ __('messages.ingredients_new', [], app()->getLocale()) ?: 'New Ingredient' }}</span>
      </div>

      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], app()->getLocale()) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body --}}
    <form id="createForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden">
      @csrf

      {{-- 2x2 grid, each cell is "label | input" --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

        {{-- Name --}}
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_name', [], app()->getLocale()) ?: 'Name' }}
          </label>
          <input name="name" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Unit --}}
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_unit', [], app()->getLocale()) ?: 'Unit' }}
          </label>
          <input name="unit" required placeholder="g / kg / ml / l / pcs"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Low alert --}}
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_low', [], app()->getLocale()) ?: 'Low alert' }}
          </label>
          <input name="low_alert_qty" type="number" step="0.001" min="0" placeholder="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        {{-- Current --}}
        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_current', [], app()->getLocale()) ?: 'Current' }}
          </label>
          <input name="current_qty" type="number" step="0.001" min="0" placeholder="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

      </div>

      <p id="createErr" class="mt-4 text-[11px] text-rose-500 min-h-[1rem]"></p>

      {{-- Footer buttons --}}
      <div class="mt-4 flex justify-between items-center">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-x-circle text-[12px]"></i>
          <span>{{ __('messages.cancel', [], app()->getLocale()) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400 px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.create', [], app()->getLocale()) ?: 'Create' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>

<script>
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
</script>
