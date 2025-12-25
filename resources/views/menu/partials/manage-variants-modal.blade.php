{{-- resources/views/menu/partials/manage-variants-modal.blade.php --}}
@if($canWrite)
@php
    $locale = app()->getLocale();
@endphp

<dialog id="variantsModal" class="bg-transparent">
    <div
        class="rounded-2xl border border-white/60 dark:border-slate-800/80
               bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-violet-200/40
               overflow-hidden w-full max-w-4xl">

        {{-- Header --}}
        <div
            class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                   bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-500
                   text-slate-50 flex items-center justify-between gap-2">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-full
                               bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
                        <i class="bi bi-columns-gap text-[13px]"></i>
                    </span>
                    <span>{{ __('messages.menu_variants_title', [], $locale) ?? 'Manage variants' }}</span>
                </div>
                <p class="mt-0.5 text-[11px] text-violet-100/90">
                    {{ __('messages.menu_variants_subtitle', [], $locale) ?? 'Create, update and delete size-based variants for this menu item.' }}
                </p>
            </div>

            <button
                type="button"
                data-close
                class="inline-flex h-7 w-7 items-center justify-center rounded-full
                       bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
                aria-label="{{ __('messages.close', [], $locale) ?? 'Close' }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="px-4 md:px-5 py-4 space-y-4">

            {{-- Item name --}}
            <div class="flex items-center justify-between gap-2 text-xs md:text-sm">
                <div class="text-slate-600 dark:text-slate-300">
                    <span class="opacity-80">
                        {{ __('messages.menu_variants_for_item', [], $locale) ?? 'Item:' }}
                    </span>
                    <span id="mv_item_name" class="font-semibold text-slate-800 dark:text-slate-50"></span>
                </div>
            </div>

            {{-- Create / Edit form --}}
            <form
                id="variantForm"
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                       bg-slate-50/80 dark:bg-slate-900/70 p-3.5 space-y-3">

                <input type="hidden" id="mv_item_id">
                <input type="hidden" id="mv_editing_id">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Size --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_size_label', [], $locale) ?? 'Size' }}
                        </label>
                        <input
                            id="mv_size"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                                   bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100"
                            placeholder="{{ __('messages.menu_variants_size_placeholder', [], $locale) ?? 'e.g. Small / Medium / Large' }}">
                    </div>

                    {{-- Price --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_price_label', [], $locale) ?? 'Price ($)' }}
                        </label>
                        <input
                            id="mv_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                                   bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-end">
                        <button
                            class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 rounded-full
                                   bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400
                                   px-4 py-2 text-xs font-semibold text-white
                                   shadow-md shadow-violet-300/70 hover:shadow-violet-400/80
                                   transition">
                            <span id="mv_submit_label">
                                {{ __('messages.menu_variants_submit_add', [], $locale) ?? 'Add variant' }}
                            </span>
                        </button>
                    </div>

                    <div class="md:col-span-3 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('messages.menu_variants_preview_label', [], $locale) ?? 'Variant name will be:' }}
                        <strong id="mv_preview" class="font-medium">
                            {{ __('messages.menu_variants_preview_empty', [], $locale) ?? '(name will appear here)' }}
                        </strong>
                    </div>
                </div>

                {{-- Variant discount fields --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-1">
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_discount_label', [], $locale) ?? 'Discount' }}
                        </label>
                        <select
                            id="mv_discount_type"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            <option value="">
                                {{ __('messages.menu_variants_discount_none', [], $locale) ?? 'None' }}
                            </option>
                            <option value="percent">
                                {{ __('messages.menu_variants_discount_percent', [], $locale) ?? 'Percent (%)' }}
                            </option>
                            <option value="fixed">
                                {{ __('messages.menu_variants_discount_fixed', [], $locale) ?? 'Fixed ($)' }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_discount_value_label', [], $locale) ?? 'Value' }}
                        </label>
                        <input
                            id="mv_discount_value"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                                   bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_discount_starts_label', [], $locale) ?? 'Starts' }}
                        </label>
                        <input
                            id="mv_discount_starts_at"
                            type="datetime-local"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_variants_discount_ends_label', [], $locale) ?? 'Ends' }}
                        </label>
                        <input
                            id="mv_discount_ends_at"
                            type="datetime-local"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                <div id="mv_message" class="text-[11px] text-slate-500 dark:text-slate-400 min-h-[1rem]"></div>
            </form>

            {{-- Table --}}
            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                       bg-white/90 dark:bg-slate-950/90 overflow-hidden">
                <div
                    class="px-4 py-3 text-xs md:text-sm font-semibold border-b border-slate-100 dark:border-slate-800
                           flex items-center justify-between text-slate-700 dark:text-slate-200">
                    <span>
                        {{ __('messages.menu_variants_table_title', [], $locale) ?? 'Variants' }}
                        <span class="opacity-70" id="mv_count"></span>
                    </span>
                    <span
                        id="mv_loading"
                        class="text-[11px] text-slate-400 dark:text-slate-500 hidden">
                        {{ __('messages.menu_variants_loading', [], $locale) ?? 'Loading…' }}
                    </span>
                </div>

                <div class="max-h-[55vh] overflow-y-auto no-scrollbar">
                    <table class="min-w-full text-xs md:text-sm table-fixed">
                        <colgroup>
                            <col style="width:38%" />
                            <col style="width:12%" />
                            <col style="width:14%" />
                            <col style="width:14%" />
                            <col style="width:22%" />
                        </colgroup>
                        <thead class="bg-slate-50/70 dark:bg-slate-950/40 text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">
                                    {{ __('messages.menu_variants_col_name', [], $locale) ?? 'Name' }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium">
                                    {{ __('messages.menu_variants_col_size', [], $locale) ?? 'Size' }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium">
                                    {{ __('messages.menu_variants_col_price', [], $locale) ?? 'Price' }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium">
                                    {{ __('messages.menu_variants_col_final', [], $locale) ?? 'Final' }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium">
                                    {{ __('messages.menu_variants_col_actions', [], $locale) ?? 'Actions' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody id="mv_rows" class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-400 dark:text-slate-500">
                                    {{ __('messages.menu_variants_empty', [], $locale) ?? 'No variants yet' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</dialog>

@push('scripts')
<script>
(function manageVariantsSetup(){
    const dialogEl = document.getElementById('variantsModal');
    if (!dialogEl) return;

    // ---- i18n strings for JS ----
    const TXT_PREVIEW_EMPTY        = @json(__('messages.menu_variants_preview_empty', [], $locale) ?? '(name will appear here)');
    const TXT_COUNT_FORMAT         = @json(__('messages.menu_variants_count_format', [], $locale) ?? '({count})');
    const TXT_LOADING              = @json(__('messages.menu_variants_loading', [], $locale) ?? 'Loading…');
    const TXT_EMPTY                = @json(__('messages.menu_variants_empty', [], $locale) ?? 'No variants yet');
    const TXT_CONFIRM_DELETE       = @json(__('messages.menu_variants_confirm_delete', [], $locale) ?? 'Permanently delete this variant?');
    const TXT_DELETE_FAILED        = @json(__('messages.menu_variants_delete_failed', [], $locale) ?? 'Delete failed');
    const TXT_SIZE_REQUIRED        = @json(__('messages.menu_variants_error_size_required', [], $locale) ?? 'Size is required.');
    const TXT_PRICE_INVALID        = @json(__('messages.menu_variants_error_price_invalid', [], $locale) ?? 'Price must be ≥ 0.');
    const TXT_DISC_VALUE_INVALID   = @json(__('messages.menu_variants_error_discount_value_invalid', [], $locale) ?? 'Discount value must be ≥ 0.');
    const TXT_DISC_PERCENT_INVALID = @json(__('messages.menu_variants_error_discount_percent_invalid', [], $locale) ?? 'Percent cannot exceed 100%.');
    const TXT_DISC_FIXED_INVALID   = @json(__('messages.menu_variants_error_discount_fixed_invalid', [], $locale) ?? 'Fixed discount cannot exceed price.');
    const TXT_CREATED              = @json(__('messages.menu_variants_toast_created', [], $locale) ?? 'Variant created.');
    const TXT_UPDATED              = @json(__('messages.menu_variants_toast_updated', [], $locale) ?? 'Variant updated.');
    const TXT_SAVE_FAILED          = @json(__('messages.menu_variants_save_failed', [], $locale) ?? 'Failed to save variant.');

    const TXT_DISCOUNT_BADGE       = @json(__('messages.menu_variants_badge_own_discount', [], $locale) ?? 'own discount');

    // ---- helpers ----
    function mvMoney(n){
        const num = Number(n ?? 0);
        return `${num.toFixed(2)} $`;
    }
    function mvHasDiscount(v){
        const t = v.discount_type;
        const dv = v.discount_value;
        if (!t || dv == null) return false;
        const now = new Date();
        const s = v.discount_starts_at ? new Date(v.discount_starts_at) : null;
        const e = v.discount_ends_at ? new Date(v.discount_ends_at) : null;
        if (s && now < s) return false;
        if (e && now > e) return false;
        return true;
    }
    function mvFinal(price, v){
        const base = Number(price ?? 0);
        if (!mvHasDiscount(v)) return null;
        if (v.discount_type === 'percent') {
            return Math.max(0, base * (1 - Number(v.discount_value || 0) / 100));
        }
        if (v.discount_type === 'fixed') {
            return Math.max(0, base - Number(v.discount_value || 0));
        }
        return null;
    }
    function toLocalDT(iso){
        if (!iso) return '';
        const d = new Date(iso);
        const pad = n => String(n).padStart(2,'0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }
    function setMvMsg(msg, isErr){
        const el = document.getElementById('mv_message');
        el.textContent = msg || '';
        el.className = 'text-[11px] ' + (isErr ? 'text-rose-600' : 'text-slate-500 dark:text-slate-400');
    }

    // ---- open from other scripts ----
    window.openManageVariants = function(item){
        const d = dialogEl;
        document.getElementById('mv_item_id').value = item.id;
        document.getElementById('mv_item_name').textContent = item.name || '';
        resetVariantForm(item);
        fetchVariants(item.id);
        if (typeof openDialog === 'function') {
            openDialog(d);
        } else {
            d.showModal?.();
        }
    };

    // ---- reset form ----
    function resetVariantForm(item){
        document.getElementById('mv_editing_id').value = '';
        document.getElementById('mv_size').value = '';
        document.getElementById('mv_price').value = '';
        document.getElementById('mv_discount_type').value = '';
        document.getElementById('mv_discount_value').value = '';
        document.getElementById('mv_discount_starts_at').value = '';
        document.getElementById('mv_discount_ends_at').value = '';
        document.getElementById('mv_submit_label').textContent =
            @json(__('messages.menu_variants_submit_add', [], $locale) ?? 'Add variant');
        setMvMsg('', false);

        const previewEl = document.getElementById('mv_preview');
        previewEl.textContent = TXT_PREVIEW_EMPTY;

        const base = item?.name || '';
        const sizeInput = document.getElementById('mv_size');
        // (re)bind input listener (remove previous)
        sizeInput.oninput = function(){
            const sz = sizeInput.value.trim();
            previewEl.textContent = (base && sz)
                ? `${base} (${sz})`
                : TXT_PREVIEW_EMPTY;
        };
    }

    // ---- fetch variants ----
    async function fetchVariants(itemId){
        const loading = document.getElementById('mv_loading');
        const tbody   = document.getElementById('mv_rows');
        const countEl = document.getElementById('mv_count');

        loading.textContent = TXT_LOADING;
        loading.classList.remove('hidden');

        try {
            const list = await api(`/api/menu/items/${itemId}/variants`);
            const arr  = Array.isArray(list) ? list : [];

            const fmt = TXT_COUNT_FORMAT;
            countEl.textContent = fmt.replace('{count}', arr.length);

            if (!arr.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-slate-400 dark:text-slate-500">${esc(TXT_EMPTY)}</td></tr>`;
                return;
            }

            tbody.innerHTML = arr.map(v => {
                const f = mvFinal(v.price, v);
                const finalCell = (f !== null && f !== Number(v.price))
                    ? `<span class="line-through opacity-60 mr-1">${mvMoney(v.price)}</span><span class="font-semibold">${mvMoney(f)}</span>`
                    : '—';
                const own = (v.discount_type && v.discount_value != null)
                    ? `<span class="ml-2 text-[10px] px-1.5 py-0.5 rounded-full bg-violet-100 text-violet-700" title="${esc(TXT_DISCOUNT_BADGE)}">${esc(TXT_DISCOUNT_BADGE)}</span>`
                    : '';

                return `
                    <tr>
                        <td class="px-3 py-2">
                            <div class="truncate text-slate-800 dark:text-slate-50" title="${esc(v.name)}">
                                ${esc(v.name)} ${own}
                            </div>
                        </td>
                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                            ${esc(v.sku || '—')}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-200">
                            ${mvMoney(v.price)}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-200">
                            ${finalCell}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    class="px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-[11px] font-medium hover:bg-violet-200"
                                    onclick='mvStartEdit(${JSON.stringify(v)})'>
                                    {{ __('messages.menu_variants_action_edit', [], $locale) ?? 'Edit' }}
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium hover:bg-rose-200"
                                    onclick="mvForceDelete(${v.id})">
                                    {{ __('messages.menu_variants_action_delete', [], $locale) ?? 'Delete' }}
                                </button>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
        } finally {
            loading.classList.add('hidden');
        }
    }

    // ---- delete variant ----
    window.mvForceDelete = async function(variantId){
        if (!confirm(TXT_CONFIRM_DELETE)) return;
        const itemId = document.getElementById('mv_item_id').value;
        try {
            await api(`/api/variants/${variantId}?force=1`, { method:'DELETE' });
            await fetchVariants(itemId);
        } catch (e){
            alert(e?.data?.message || TXT_DELETE_FAILED);
        }
    };

    // ---- start editing variant ----
    window.mvStartEdit = function(v){
        document.getElementById('mv_editing_id').value = v.id;
        document.getElementById('mv_size').value = v.sku || '';
        document.getElementById('mv_price').value = v.price ?? '';
        document.getElementById('mv_discount_type').value = v.discount_type || '';
        document.getElementById('mv_discount_value').value = v.discount_value ?? '';
        document.getElementById('mv_discount_starts_at').value = toLocalDT(v.discount_starts_at);
        document.getElementById('mv_discount_ends_at').value = toLocalDT(v.discount_ends_at);
        document.getElementById('mv_submit_label').textContent =
            @json(__('messages.menu_variants_submit_update', [], $locale) ?? 'Save changes');

        const base = document.getElementById('mv_item_name').textContent || '';
        const sz   = v.sku || '';
        document.getElementById('mv_preview').textContent =
            (base && sz) ? `${base} (${sz})` : TXT_PREVIEW_EMPTY;
    };

    // ---- submit create / update ----
    document.getElementById('variantForm').addEventListener('submit', async (e)=>{
        e.preventDefault();

        const itemId    = document.getElementById('mv_item_id').value;
        const editingId = document.getElementById('mv_editing_id').value;

        const size = document.getElementById('mv_size').value.trim();
        const price = document.getElementById('mv_price').value;
        const t  = document.getElementById('mv_discount_type').value;
        const dv = document.getElementById('mv_discount_value').value;
        const s  = document.getElementById('mv_discount_starts_at').value;
        const e2 = document.getElementById('mv_discount_ends_at').value;

        if (!size) {
            setMvMsg(TXT_SIZE_REQUIRED, true);
            return;
        }
        if (price === '' || Number(price) < 0) {
            setMvMsg(TXT_PRICE_INVALID, true);
            return;
        }
        if (t) {
            if (dv === '' || Number(dv) < 0) {
                setMvMsg(TXT_DISC_VALUE_INVALID, true);
                return;
            }
            if (t === 'percent' && Number(dv) > 100) {
                setMvMsg(TXT_DISC_PERCENT_INVALID, true);
                return;
            }
            if (t === 'fixed' && Number(dv) > Number(price)) {
                setMvMsg(TXT_DISC_FIXED_INVALID, true);
                return;
            }
        }

        try {
            const nameBase = document.getElementById('mv_item_name').textContent || '';
            const payload = {
                name: nameBase && size ? `${nameBase} (${size})` : nameBase,
                price: Number(price),
                sku: size,
                discount_type: t || null,
                discount_value: t ? Number(dv) : null,
                discount_starts_at: t ? (s || null) : null,
                discount_ends_at: t ? (e2 || null) : null,
            };

            if (!editingId) {
                await api(`/api/menu/items/${itemId}/variants`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json' },
                    body: JSON.stringify(payload),
                });
                setMvMsg(TXT_CREATED, false);
                if (typeof showToast === 'function') showToast(TXT_CREATED, 'success');
            } else {
                await api(`/api/variants/${editingId}`, {
                    method:'PATCH',
                    headers:{ 'Content-Type':'application/json' },
                    body: JSON.stringify(payload),
                });
                setMvMsg(TXT_UPDATED, false);
                if (typeof showToast === 'function') showToast(TXT_UPDATED, 'success');
            }

            await fetchVariants(itemId);
            resetVariantForm({ name: nameBase });
        } catch (err){
            console.error(err);
            setMvMsg(err?.data?.message || TXT_SAVE_FAILED, true);
        }
    });
})();
</script>
@endpush
@endif
