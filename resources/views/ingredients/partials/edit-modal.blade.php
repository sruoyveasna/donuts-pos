<dialog id="editModal"
        class="bg-transparent p-0 border-0 overflow-visible
               w-[min(520px,92vw)] max-w-none
               max-h-[calc(100dvh-2rem)]">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-full
              max-h-[calc(100dvh-2rem)]
              flex flex-col">

    {{-- Header --}}
    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
          <i class="bi bi-pencil text-[13px]"></i>
        </span>
        <span>{{ __('messages.edit', [], app()->getLocale()) ?: 'Edit Ingredient' }}</span>
      </div>

      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], app()->getLocale()) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body --}}
    <form id="editForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden flex-1 min-h-0">
      @csrf
      <input type="hidden" name="id" id="edit_id">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_name', [], app()->getLocale()) ?: 'Name' }}
          </label>
          <input id="edit_name" name="name" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_unit', [], app()->getLocale()) ?: 'Unit' }}
          </label>
          <input id="edit_unit" name="unit" required
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_low', [], app()->getLocale()) ?: 'Low alert' }}
          </label>
          <input id="edit_low_alert_qty" name="low_alert_qty" type="number" step="0.001" min="0"
                 class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50">
        </div>

        <div class="flex items-center gap-3">
          <label class="w-24 text-xs font-medium text-slate-700 dark:text-slate-200">
            {{ __('messages.ingredients_col_current', [], app()->getLocale()) ?: 'Current' }}
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
          <span>{{ __('messages.cancel', [], app()->getLocale()) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-indigo-500 via-indigo-400 to-sky-400
                       px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-indigo-300/70 hover:shadow-indigo-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.update', [], app()->getLocale()) ?: 'Update' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>

<style>
  #editModal::backdrop { background: rgba(0,0,0,.40); backdrop-filter: blur(2px); }
  #editModal { padding: 0; }
</style>

<script>
/* called by your table button: openEdit(id, name, unit, low_alert_qty, current_qty) */
window.openEdit = function(id, name, unit, low_alert_qty, current_qty){
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name ?? '';
  document.getElementById('edit_unit').value = unit ?? '';
  document.getElementById('edit_low_alert_qty').value = (low_alert_qty ?? '');
  document.getElementById('edit_current_qty').value = (current_qty ?? '');
  document.getElementById('editErr').textContent = '';
  openDialog(document.getElementById('editModal'));
};

document.getElementById('editForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const f = e.target;
  const err = document.getElementById('editErr');
  err.textContent = '';

  const id = f.id.value;

  const payload = {
    name: f.name.value.trim(),
    unit: f.unit.value.trim(),
    low_alert_qty: f.low_alert_qty.value === '' ? null : Number(f.low_alert_qty.value),
    current_qty: f.current_qty.value === '' ? null : Number(f.current_qty.value),
  };

  try{
    await api(`/api/ingredients/${id}`, {
      method:'PUT', // change to PATCH if your API uses PATCH
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
</script>
