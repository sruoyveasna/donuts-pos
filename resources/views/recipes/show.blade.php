{{-- resources/views/recipes/show.blade.php --}}
@extends('layouts.app')

@php
  $locale   = app()->getLocale();
  $role     = strtolower(optional(auth()->user()->role)->name ?? '');
  $canWrite = in_array($role, ['admin','super admin','super_admin']);

  // comes from web route: /recipes/{menuItemId}
  /** @var int|string $menuItemId */
@endphp

@section('title', __('messages.recipes_show_title', [], $locale) ?: 'Recipe')

@push('head')
<style>
  dialog{ padding:0; border:0; background:transparent; max-width:100vw; overflow:visible; }
  dialog::backdrop{ background: rgba(2, 6, 23, .65); backdrop-filter: blur(10px); }
  dialog[open]{ position: fixed; inset: 0; margin: auto; display: grid; place-items: center; width: 100vw; height: 100vh; }

  .card-sticky-header { position: sticky; top: 0; z-index: 10; backdrop-filter: blur(16px); }
  .soft-divider { border-top: 1px dashed rgba(148,163,184,.4); }
  .no-scrollbar::-webkit-scrollbar { width: 0px; height: 0px; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  .skeleton{ position:relative; overflow:hidden; background:#f3f4f6; border-radius:.375rem; }
  .dark .skeleton{ background: rgba(15,23,42,.9); }
  .skeleton::after{
    content:""; position:absolute; inset:0; transform: translateX(-100%);
    background: linear-gradient(90deg, rgba(248,250,252,0), rgba(226,232,240,.9), rgba(248,250,252,0));
    animation: shimmer 1.2s infinite;
  }
  @keyframes shimmer { 100% { transform: translateX(100%); } }

  .icon-btn{
    height: 32px; width: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 9999px;
  }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto h-full min-h-0 flex flex-col gap-4">

  {{-- Heading --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 shrink-0">
    <div>
      <h1 id="pageTitle" class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
        🍳 {{ __('messages.loading', [], $locale) ?: 'Loading…' }}
      </h1>
      <p id="pageSub" class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
        {{ __('messages.recipes_subtitle', [], $locale) ?: 'Manage recipe lines for stock deduction (base + variants).' }}
      </p>
      <p id="summaryText" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
        —
      </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <a href="{{ route('recipes.index') }}"
         class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                px-3 py-1.5 text-[11px] md:text-xs text-slate-700 dark:text-slate-200
                hover:border-rose-300 dark:hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition">
        <i class="bi bi-arrow-left text-[12px]"></i>
        <span>Back</span>
      </a>

      @if($canWrite)
      <button id="editBaseBtn"
        class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-rose-500 via-rose-400 to-orange-400
               px-3.5 py-1.5 text-[11px] md:text-xs font-semibold text-white
               shadow-md shadow-rose-300/70 hover:shadow-rose-400/80 transition">
        <i class="bi bi-pencil-square text-[12px]"></i>
        <span>Edit base</span>
      </button>
      @endif
    </div>
  </div>

  {{-- Card --}}
  <div class="rounded-2xl border border-white/60 dark:border-slate-800/80
              bg-white/90 dark:bg-slate-900/90 shadow-xl shadow-rose-200/40
              backdrop-blur-2xl overflow-hidden flex flex-col flex-1 min-h-0">

    {{-- Base recipe section --}}
    <div class="px-4 md:px-5 py-3.5 border-b border-white/60 dark:border-slate-800/80
                bg-gradient-to-r from-white/95 via-white/80 to-white/70
                dark:from-slate-950/95 dark:via-slate-900/90 dark:to-slate-900/80">
      <div class="flex items-center justify-between gap-2">
        <div>
          <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">
            Base recipe
            <span id="baseCount" class="text-[11px] font-medium text-slate-500 dark:text-slate-400">—</span>
          </div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">
            For menu item without variant.
          </div>
        </div>

        <button id="refreshBtn"
          class="tw-tip inline-flex items-center justify-center rounded-full border border-slate-300/80
                 dark:border-slate-700/80 bg-white/90 dark:bg-slate-950/80
                 h-8 w-8 text-slate-600 dark:text-slate-200 hover:text-rose-500 hover:border-rose-400 transition"
          type="button" data-tooltip="Refresh">
          <i class="bi bi-arrow-clockwise text-[13px]"></i>
        </button>
      </div>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar">
      <div class="p-4 md:p-5">
        <div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-800/80">
          <table class="min-w-full text-xs md:text-sm border-collapse">
            <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400
                          bg-slate-50/90 dark:bg-slate-950/60 border-b border-slate-200/70 dark:border-slate-800/80">
              <tr>
                <th class="px-3 py-2 text-left font-medium w-[52%]">Ingredient</th>
                <th class="px-3 py-2 text-right font-medium w-[16%]">Qty</th>
                <th class="px-3 py-2 text-left font-medium w-[12%]">Unit</th>
                <th class="px-3 py-2 text-right font-medium w-[12%]">Stock</th>
                <th class="px-3 py-2 text-center font-medium w-[8%]">Status</th>
              </tr>
            </thead>
            <tbody id="baseRows" class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr><td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">Loading…</td></tr>
            </tbody>
          </table>
        </div>

        <div class="soft-divider my-5"></div>

        {{-- Variants --}}
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">
            Variants
            <span id="variantCount" class="text-[11px] font-medium text-slate-500 dark:text-slate-400">—</span>
          </div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">
            Each variant can have its own recipe.
          </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-800/80">
          <table class="min-w-full text-xs md:text-sm border-collapse">
            <thead class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400
                          bg-slate-50/90 dark:bg-slate-950/60 border-b border-slate-200/70 dark:border-slate-800/80">
              <tr>
                <th class="px-3 py-2 text-left font-medium w-[40%]">Variant</th>
                <th class="px-3 py-2 text-center font-medium w-[16%]">Lines</th>
                <th class="px-3 py-2 text-right font-medium w-[18%]">Final price</th>
                <th class="px-3 py-2 text-center font-medium w-[14%]">Active</th>
                <th class="px-3 py-2 text-right font-medium w-[12%]">Actions</th>
              </tr>
            </thead>
            <tbody id="variantRows" class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr><td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">Loading…</td></tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ✅ reuse the same modal partial used by index --}}
@include('recipes.partials.edit-modal', ['canWrite' => $canWrite, 'locale' => $locale])

@endsection

@push('scripts')
<script>
const CAN_WRITE = @json($canWrite);
const MENU_ITEM_ID = Number(@json($menuItemId));

const cache = {
  ingredientsLoaded: false,
  ingredients: [],
  ingIndex: new Map(),
};

const editor = {
  item: null,
  scope: 'base',
  variantId: null,
  lines: [],
  dirty: false,
};

function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function fmtNum(v){
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return '0';
  return n.toLocaleString('en-US', { maximumFractionDigits: 3 });
}
function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

function pill(isLow){
  if (isLow){
    return `<span class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2 py-0.5 text-[11px]
                         dark:bg-rose-900/30 dark:text-rose-200">
      <span class="h-2 w-2 rounded-full bg-rose-500"></span> Low
    </span>`;
  }
  return `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px]
                       dark:bg-emerald-900/40 dark:text-emerald-200">
    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> OK
  </span>`;
}

/* =============================
   Load ingredients (once)
   ============================= */
async function ensureIngredients(){
  if (cache.ingredientsLoaded) return;
  const ingPaged = await api('/api/ingredients?per_page=200&sort=name&dir=asc');
  cache.ingredients = (ingPaged?.data || []);
  cache.ingIndex = new Map(cache.ingredients.map(x => [Number(x.id), x]));
  cache.ingredientsLoaded = true;
}

/* =============================
   Load menu item for this page
   ============================= */
async function loadMenuItem(){
  document.getElementById('baseRows').innerHTML =
    `<tr><td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">Loading…</td></tr>`;
  document.getElementById('variantRows').innerHTML =
    `<tr><td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">Loading…</td></tr>`;

  const item = await api(`/api/menu/items/${MENU_ITEM_ID}?include_variants=1&include_recipes=1`);
  editor.item = item;

  // header text
  document.getElementById('pageTitle').textContent = `🍳 ${item?.name || 'Recipe'}`;
  document.getElementById('summaryText').textContent =
    `Base lines: ${(item.recipes || []).length} · Variants: ${(item.variants || []).length}`;

  renderBaseTable();
  renderVariantsTable();
}

function renderBaseTable(){
  const tbody = document.getElementById('baseRows');
  const lines = editor.item?.recipes || [];
  document.getElementById('baseCount').textContent = `(${lines.length} lines)`;

  if (!lines.length){
    tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
      <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
      <div class="font-medium mb-1">No base recipe lines</div>
      <div class="text-sm">Use “Edit base” to add ingredients.</div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = lines.map(r=>{
    const ingId = Number(r.ingredient_id ?? r.ingredient?.id);
    const ing = cache.ingIndex.get(ingId);
    return `
      <tr class="align-middle">
        <td class="px-3 py-2">
          <div class="font-medium text-slate-900 dark:text-slate-50">${esc(ing?.name || r.ingredient?.name || '—')}</div>
        </td>
        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtNum(r.quantity))}</td>
        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(ing?.unit || r.ingredient?.unit || '—')}</td>
        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(ing ? fmtNum(ing.current_qty) : '—')}</td>
        <td class="px-3 py-2 text-center">${pill(!!ing?.is_low)}</td>
      </tr>
    `;
  }).join('');
}

function renderVariantsTable(){
  const tbody = document.getElementById('variantRows');
  const variants = editor.item?.variants || [];
  document.getElementById('variantCount').textContent = `(${variants.length})`;

  if (!variants.length){
    tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
      <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
      <div class="font-medium mb-1">No variants</div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = variants.map(v=>{
    const lines = (v.recipes || []).length;

    let actions = `<span class="text-[11px] text-slate-400">—</span>`;
    if (CAN_WRITE){
      actions = `
        <button
          class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200
                 dark:border-indigo-500 dark:text-indigo-200 dark:hover:bg-indigo-900/20"
          data-tooltip="Edit variant"
          onclick="openRecipeEditor('variant', ${Number(v.id)})">
          <i class="bi bi-layers text-[14px] leading-none"></i>
        </button>`;
    }

    return `
      <tr class="align-middle">
        <td class="px-3 py-2">
          <div class="font-medium text-slate-900 dark:text-slate-50">${esc(v.name)}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">SKU: ${esc(v.sku || '—')}</div>
        </td>
        <td class="px-3 py-2 text-center font-semibold text-slate-900 dark:text-slate-50">${esc(lines)}</td>
        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(fmtNum(v.final_price ?? v.price))}</td>
        <td class="px-3 py-2 text-center">
          ${v.is_active ? pill(false).replace('OK','Active') : pill(true).replace('Low','Inactive')}
        </td>
        <td class="px-3 py-2 text-right">
          <div class="flex items-center gap-2 justify-end whitespace-nowrap">${actions}</div>
        </td>
      </tr>
    `;
  }).join('');
}

/* =====================================
   Reuse editor modal logic (same as index)
   ===================================== */
function getLinesForScope(scope, variantId){
  if (scope === 'base'){
    return (editor.item?.recipes || []).map(r => ({
      ingredient_id: Number(r.ingredient_id ?? r.ingredient?.id),
      quantity: Number(r.quantity),
    })).filter(x => Number.isFinite(x.ingredient_id));
  }
  const v = (editor.item?.variants || []).find(x => Number(x.id) === Number(variantId));
  return (v?.recipes || []).map(r => ({
    ingredient_id: Number(r.ingredient_id ?? r.ingredient?.id),
    quantity: Number(r.quantity),
  })).filter(x => Number.isFinite(x.ingredient_id));
}

window.openRecipeEditor = function(scope, variantId){
  editor.scope = scope;
  editor.variantId = variantId ?? null;
  editor.dirty = false;

  document.getElementById('recipeErr').textContent = '';

  // fill variants
  const variantSelect = document.getElementById('variantSelect');
  variantSelect.innerHTML = `<option value="">Select variant</option>`;
  (editor.item?.variants || []).forEach(v=>{
    const opt = document.createElement('option');
    opt.value = String(v.id);
    opt.textContent = v.name;
    variantSelect.appendChild(opt);
  });

  // set radios
  document.querySelectorAll('input[name="scope"]').forEach(r => r.checked = (r.value === scope));
  variantSelect.disabled = (scope !== 'variant');

  if (scope === 'variant'){
    if (!editor.variantId){
      const first = (editor.item?.variants || [])[0];
      editor.variantId = first ? Number(first.id) : null;
    }
    variantSelect.value = editor.variantId ? String(editor.variantId) : '';
  } else {
    variantSelect.value = '';
  }

  editor.lines = getLinesForScope(editor.scope, editor.variantId);
  renderEditLines();
  updateRecipeModalTitle();

  wireRecipeModalListeners();
  openDialog(document.getElementById('recipeModal'));
};

function updateRecipeModalTitle(){
  const itemName = editor.item?.name ?? 'Recipe';
  let scopeLabel = 'Base';
  if (editor.scope === 'variant'){
    const v = (editor.item?.variants || []).find(x => Number(x.id) === Number(editor.variantId));
    scopeLabel = `Variant: ${v?.name ?? '—'}`;
  }
  document.getElementById('recipeModalTitle').textContent = `${itemName} · ${scopeLabel}`;
  document.getElementById('lineCount').textContent = `(${editor.lines.length} lines)`;
}

function renderEditLines(){
  const tb = document.getElementById('editLines');
  document.getElementById('lineCount').textContent = `(${editor.lines.length} lines)`;

  if (!editor.lines.length){
    tb.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
      <i class="bi bi-inboxes block text-3xl opacity-70 mb-2"></i>
      <div class="font-medium mb-1">No recipe lines</div>
      <div class="text-sm">Click “Add” to insert ingredients.</div>
    </td></tr>`;
    return;
  }

  tb.innerHTML = editor.lines.map((l, idx) => {
    const ing = cache.ingIndex.get(Number(l.ingredient_id));
    return `
      <tr class="align-middle">
        <td class="px-3 py-2">
          <div class="font-medium text-slate-900 dark:text-slate-50">${esc(ing?.name ?? '—')}</div>
        </td>
        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-50">${esc(fmtNum(l.quantity))}</td>
        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">${esc(ing?.unit ?? '—')}</td>
        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">${esc(ing ? fmtNum(ing.current_qty) : '—')}</td>
        <td class="px-3 py-2 text-center">${pill(!!ing?.is_low)}</td>
        <td class="px-3 py-2 text-right">
          <div class="flex items-center gap-2 justify-end whitespace-nowrap">
            <button class="tw-tip icon-btn border border-indigo-600 text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring focus:ring-indigo-200
                           dark:border-indigo-500 dark:text-indigo-200 dark:hover:bg-indigo-900/20"
                    data-tooltip="Edit" aria-label="Edit"
                    onclick="openLineModal(${idx})">
              <i class="bi bi-pencil text-[14px] leading-none"></i>
            </button>
            <button class="tw-tip icon-btn border border-slate-600 text-slate-700 hover:bg-slate-100
                           dark:border-slate-500 dark:text-slate-200 dark:hover:bg-slate-800"
                    data-tooltip="Remove" aria-label="Remove"
                    onclick="removeLine(${idx})">
              <i class="bi bi-trash text-[14px] leading-none"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

window.removeLine = function(idx){
  editor.lines.splice(idx, 1);
  editor.dirty = true;
  renderEditLines();
  updateRecipeModalTitle();
};

let recipeModalWired = false;
function wireRecipeModalListeners(){
  if (recipeModalWired) return;
  recipeModalWired = true;

  document.querySelectorAll('#recipeModal [data-close], #lineModal [data-close]').forEach(btn=>{
    btn.addEventListener('click', ()=> closeDialog(btn.closest('dialog')));
  });

  document.querySelectorAll('input[name="scope"]').forEach(r=>{
    r.addEventListener('change', ()=>{
      if (editor.dirty && !confirm('You have unsaved changes. Switch scope anyway?')){
        document.querySelector(`input[name="scope"][value="${editor.scope}"]`).checked = true;
        return;
      }
      editor.scope = r.value;
      editor.dirty = false;

      const variantSelect = document.getElementById('variantSelect');
      variantSelect.disabled = (editor.scope !== 'variant');

      if (editor.scope === 'variant' && !editor.variantId){
        const first = (editor.item?.variants || [])[0];
        editor.variantId = first ? Number(first.id) : null;
        variantSelect.value = editor.variantId ? String(editor.variantId) : '';
      }

      editor.lines = getLinesForScope(editor.scope, editor.variantId);
      renderEditLines();
      updateRecipeModalTitle();
    });
  });

  document.getElementById('variantSelect').addEventListener('change', (e)=>{
    if (editor.scope !== 'variant') return;
    if (editor.dirty && !confirm('You have unsaved changes. Switch variant anyway?')){
      e.target.value = editor.variantId ? String(editor.variantId) : '';
      return;
    }
    editor.variantId = e.target.value ? Number(e.target.value) : null;
    editor.dirty = false;
    editor.lines = getLinesForScope('variant', editor.variantId);
    renderEditLines();
    updateRecipeModalTitle();
  });

  document.getElementById('addLineBtn').addEventListener('click', ()=> openLineModal(null));
  document.getElementById('saveRecipeBtn').addEventListener('click', saveRecipeGroup);

  document.getElementById('lineForm').addEventListener('submit', onLineSave);
  document.getElementById('ingSearch').addEventListener('input', debounce(renderIngredientPicker, 200));
}

async function saveRecipeGroup(){
  const err = document.getElementById('recipeErr');
  err.textContent = '';

  if (editor.scope === 'variant' && !editor.variantId){
    err.textContent = 'Please select a variant.';
    return;
  }

  const ids = editor.lines.map(x => Number(x.ingredient_id));
  const uniq = new Set(ids);
  if (uniq.size !== ids.length){
    err.textContent = 'Duplicate ingredients detected.';
    return;
  }

  const payload = {
    menu_item_id: Number(editor.item?.id),
    menu_item_variant_id: editor.scope === 'variant' ? Number(editor.variantId) : null,
    lines: editor.lines.map(l => ({ ingredient_id: Number(l.ingredient_id), quantity: Number(l.quantity) })),
  };

  try{
    await api('/api/recipes/group', {
      method:'PUT',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });

    showToast('Recipe saved', 'success');
    closeDialog(document.getElementById('recipeModal'));

    // reload show data
    await loadMenuItem();
  }catch(ex){
    console.error(ex);
    err.textContent = ex?.data?.message || 'Save failed';
    showToast(err.textContent, 'danger');
  }
}

/* ======================
   Nested line modal
   ====================== */
window.openLineModal = function(index){
  document.getElementById('lineErr').textContent = '';
  document.getElementById('ingSearch').value = '';
  document.getElementById('ingredient_id').value = '';
  document.getElementById('selectedIngName').textContent = '—';
  document.getElementById('selectedIngUnit').textContent = '—';
  document.getElementById('line_qty').value = '';
  document.getElementById('line_index').value = (index === null || index === undefined) ? '' : String(index);
  document.getElementById('lineModalTitle').textContent = (index === null || index === undefined) ? 'Add ingredient' : 'Edit ingredient';

  if (index !== null && index !== undefined){
    const line = editor.lines[index];
    const ing = cache.ingIndex.get(Number(line.ingredient_id));
    document.getElementById('ingredient_id').value = String(line.ingredient_id);
    document.getElementById('selectedIngName').textContent = ing?.name ?? '—';
    document.getElementById('selectedIngUnit').textContent = ing?.unit ?? '—';
    document.getElementById('line_qty').value = String(line.quantity ?? '');
  }

  renderIngredientPicker();
  openDialog(document.getElementById('lineModal'));
};

function renderIngredientPicker(){
  const q = (document.getElementById('ingSearch').value || '').trim().toLowerCase();
  const list = document.getElementById('ingList');

  const used = new Set(editor.lines.map(x => Number(x.ingredient_id)));
  const editingIndex = document.getElementById('line_index').value;
  if (editingIndex !== ''){
    used.delete(Number(editor.lines[Number(editingIndex)]?.ingredient_id));
  }

  const filtered = cache.ingredients
    .filter(i => !q || (String(i.name||'').toLowerCase().includes(q)))
    .slice(0, 60);

  if (!filtered.length){
    list.innerHTML = `<div class="p-3 text-[11px] text-slate-500 dark:text-slate-400">No ingredients</div>`;
    return;
  }

  list.innerHTML = filtered.map(i=>{
    const disabled = used.has(Number(i.id));
    const stat = i.is_low ? 'Low' : 'OK';
    return `
      <button type="button"
        class="w-full text-left px-3 py-2 flex items-center gap-2 ${disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'}"
        onclick="${disabled ? '' : `selectIngredient(${Number(i.id)})`}"
        ${disabled ? 'disabled' : ''}>
        <div>
          <div class="text-xs font-medium text-slate-900 dark:text-slate-50">${esc(i.name)}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400">${esc(i.unit || '—')} · Stock: ${esc(fmtNum(i.current_qty))}</div>
        </div>
        <span class="ml-auto">${pill(!!i.is_low).replace('OK', stat)}</span>
      </button>
    `;
  }).join('');
}

window.selectIngredient = function(id){
  const ing = cache.ingIndex.get(Number(id));
  document.getElementById('ingredient_id').value = String(id);
  document.getElementById('selectedIngName').textContent = ing?.name ?? '—';
  document.getElementById('selectedIngUnit').textContent = ing?.unit ?? '—';
};

function onLineSave(e){
  e.preventDefault();
  const err = document.getElementById('lineErr');
  err.textContent = '';

  const idxRaw = document.getElementById('line_index').value;
  const ingredientId = Number(document.getElementById('ingredient_id').value || 0);
  const qty = Number(document.getElementById('line_qty').value || 0);

  if (!ingredientId){ err.textContent = 'Please select an ingredient.'; return; }
  if (!Number.isFinite(qty) || qty <= 0){ err.textContent = 'Quantity must be greater than 0.'; return; }

  const used = new Set(editor.lines.map(x => Number(x.ingredient_id)));
  if (idxRaw !== '') used.delete(Number(editor.lines[Number(idxRaw)]?.ingredient_id));
  if (used.has(ingredientId)){ err.textContent = 'This ingredient is already in the recipe.'; return; }

  if (idxRaw === ''){
    editor.lines.push({ ingredient_id: ingredientId, quantity: qty });
  } else {
    editor.lines[Number(idxRaw)] = { ingredient_id: ingredientId, quantity: qty };
  }

  editor.dirty = true;
  closeDialog(document.getElementById('lineModal'));
  renderEditLines();
  updateRecipeModalTitle();
}

/* ======================
   Page actions
   ====================== */
document.getElementById('refreshBtn')?.addEventListener('click', async ()=>{
  try{
    await loadMenuItem();
    showToast('Refreshed', 'success');
  }catch(e){
    console.error(e);
    showToast('Refresh failed', 'danger');
  }
});

@if($canWrite)
document.getElementById('editBaseBtn')?.addEventListener('click', ()=>{
  openRecipeEditor('base', null);
});
@endif

(async function init(){
  try{
    await ensureIngredients();
    await loadMenuItem();
  }catch(e){
    console.error(e);
    showToast(e?.data?.message || 'Failed to load recipe page', 'danger');
  }
})();
</script>
@endpush
