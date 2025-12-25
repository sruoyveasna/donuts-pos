<dialog id="adjustModal"
        class="bg-transparent p-0 border-0 overflow-visible
               w-[min(560px,92vw)] max-w-none
               max-h-[calc(100dvh-2rem)]">
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-rose-200/40
              overflow-hidden w-full
              max-h-[calc(100dvh-2rem)]
              flex flex-col">

    {{-- Header --}}
    <div class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                bg-gradient-to-r from-rose-600 via-rose-500 to-orange-400 text-slate-50
                flex items-center justify-between gap-2 shrink-0">
      <div>
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 border border-white/30">
            <i class="bi bi-arrow-left-right text-[13px]"></i>
          </span>
          <span>{{ __('messages.ingredients_adjust', [], app()->getLocale()) ?: 'Adjust stock' }}</span>
        </div>
        <p id="adjSub" class="mt-0.5 text-[11px] text-rose-50/90">—</p>
      </div>

      <button type="button" data-close
              class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
              aria-label="{{ __('messages.close', [], app()->getLocale()) ?: 'Close' }}">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Body --}}
    <form id="adjustForm" class="px-4 md:px-5 py-5 overflow-y-auto overflow-x-hidden flex-1 min-h-0">
      @csrf
      <input type="hidden" id="adj_id">

      <div class="grid grid-cols-1 gap-y-5">

  {{-- Row 1: Action + Qty (aligned) --}}
  <div class="grid grid-cols-[96px_1fr_96px_1fr] gap-x-6 items-center">
    {{-- Action --}}
    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
      {{ __('messages.ingredients_adjust_action', [], app()->getLocale()) ?: 'Action' }}
    </label>
    <select id="adj_action"
            class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                   bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                   text-slate-900 dark:text-slate-50">
      <option value="restock">{{ __('messages.ingredients_action_restock', [], app()->getLocale()) ?: 'Restock' }}</option>
      <option value="consume">{{ __('messages.ingredients_action_consume', [], app()->getLocale()) ?: 'Consume' }}</option>
      <option value="adjust">{{ __('messages.ingredients_action_adjust', [], app()->getLocale()) ?: 'Adjust (delta)' }}</option>
    </select>

    {{-- Qty --}}
    <label id="adjQtyLabel" class="text-xs font-medium text-slate-700 dark:text-slate-200">
      {{ __('messages.ingredients_adjust_qty', [], app()->getLocale()) ?: 'Qty' }}
    </label>
    <input id="adj_qty" type="number" step="0.001" min="0" placeholder="0"
           class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                  bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                  text-slate-900 dark:text-slate-50">
  </div>

    {{-- Hint line under Qty (aligned with Qty input) --}}
    <div class="grid grid-cols-[96px_1fr_96px_1fr] gap-x-6">
        <div></div>
        <div></div>
        <div></div>
        <p id="adjHint" class="text-[11px] text-slate-400 dark:text-slate-500">—</p>
    </div>

    {{-- Row 2: Note full width --}}
    <div class="grid grid-cols-[96px_1fr] gap-x-6 items-center">
        <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
        {{ __('messages.note', [], app()->getLocale()) ?: 'Note' }}
        </label>
        <input id="adj_note"
            class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                    bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                    text-slate-900 dark:text-slate-50"
            placeholder="{{ __('messages.ingredients_note_placeholder', [], app()->getLocale()) ?: 'Optional note…' }}">
    </div>

    </div>

      <p id="adjErr" class="mt-4 text-[11px] text-rose-500 min-h-[1rem]"></p>

      <div class="mt-4 flex justify-between items-center shrink-0">
        <button type="button" data-close
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80 px-3 py-1.5 text-xs
                       text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
          <i class="bi bi-x-circle text-[12px]"></i>
          <span>{{ __('messages.cancel', [], app()->getLocale()) ?: 'Cancel' }}</span>
        </button>

        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
                       px-4 py-1.5 text-xs font-semibold text-white shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
          <i class="bi bi-check2-circle text-[12px]"></i>
          <span>{{ __('messages.confirm', [], app()->getLocale()) ?: 'Confirm' }}</span>
        </button>
      </div>
    </form>
  </div>
</dialog>

<style>
  #adjustModal::backdrop { background: rgba(0,0,0,.40); backdrop-filter: blur(2px); }
  #adjustModal { padding: 0; }
</style>


<script>
let ADJ_CTX = { name:'', unit:'', current:0, low:0 };

function openAdjust(id, name, unit, current, low){
  const safe = (v, fb='') => { try { return JSON.parse(v); } catch { return fb; } };
  ADJ_CTX = {
    name: safe(name, String(name ?? '')),
    unit: safe(unit, String(unit ?? '')),
    current: Number(safe(current, String(current ?? 0))),
    low: Number(safe(low, String(low ?? 0))),
  };

  document.getElementById('adj_id').value = id;
  document.getElementById('adj_action').value = 'restock';
  document.getElementById('adj_qty').value = '';
  document.getElementById('adj_note').value = '';
  document.getElementById('adjErr').textContent = '';

  updateAdjUI();
  openDialog(document.getElementById('adjustModal'));
}

function updateAdjUI(){
  const action = document.getElementById('adj_action').value;
  const label = document.getElementById('adjQtyLabel');
  const hint = document.getElementById('adjHint');
  const qty = Number(document.getElementById('adj_qty').value || 0);

  if (action === 'adjust') {
    label.textContent = '{{ __("messages.ingredients_adjust_delta", [], app()->getLocale()) ?: "Delta" }}';
    document.getElementById('adj_qty').removeAttribute('min');
    document.getElementById('adj_qty').setAttribute('step', '0.001');
  } else {
    label.textContent = '{{ __("messages.ingredients_adjust_qty", [], app()->getLocale()) ?: "Qty" }}';
    document.getElementById('adj_qty').setAttribute('min', '0');
  }

  const projected =
    action === 'restock' ? (ADJ_CTX.current + qty)
    : action === 'consume' ? Math.max(0, ADJ_CTX.current - qty)
    : Math.max(0, ADJ_CTX.current + qty);

  hint.textContent = `Ingredient: ${ADJ_CTX.name} · Current: ${ADJ_CTX.current} ${ADJ_CTX.unit} · After: ${projected} ${ADJ_CTX.unit}`;
  document.getElementById('adjSub').textContent = hint.textContent;
}

document.getElementById('adj_action')?.addEventListener('change', updateAdjUI);
document.getElementById('adj_qty')?.addEventListener('input', updateAdjUI);

document.getElementById('adjustForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  document.getElementById('adjErr').textContent = '';

  const id = document.getElementById('adj_id').value;
  const action = document.getElementById('adj_action').value;
  const note = document.getElementById('adj_note').value.trim() || null;
  const raw = document.getElementById('adj_qty').value;

  const payload = { action, note };

  if (action === 'adjust') {
    if (raw === '') return document.getElementById('adjErr').textContent = 'Delta is required.';
    payload.delta_qty = Number(raw);
  } else {
    if (raw === '') return document.getElementById('adjErr').textContent = 'Qty is required.';
    payload.qty = Number(raw);
  }

  try{
    const res = await api(`/api/ingredients/${id}/adjust`, {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    if (res?.ingredient?.id) {
      showToast('{{ __("messages.ingredients_toast_stock_updated", [], app()->getLocale()) ?: "Stock updated" }}', 'success');
      closeDialog(document.getElementById('adjustModal'));
      load();
    } else {
      document.getElementById('adjErr').textContent = res?.message || 'Failed';
    }
  }catch(err){
    console.error(err);
    document.getElementById('adjErr').textContent = err?.data?.message || 'Failed';
  }
});
</script>
