{{-- resources/views/menu/partials/edit-modal.blade.php --}}
@if($canWrite)
@php
    $locale = app()->getLocale();
@endphp

<dialog id="editModal" class="bg-transparent">
    <div
        class="rounded-2xl border border-white/60 dark:border-slate-800/80
               bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-violet-200/40
               overflow-hidden w-full max-w-3xl">

        <form id="editForm" class="px-4 md:px-5 py-4 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_id" name="id">

            {{-- Header --}}
            <div
                class="-mx-4 -mt-4 mb-3 px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                       bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-500
                       text-slate-50 flex items-center justify-between gap-2">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full
                                   bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
                            <i class="bi bi-pencil-square text-[13px]"></i>
                        </span>
                        <span>{{ __('messages.menu_edit_title', [], $locale) ?? 'Edit menu item' }}</span>
                    </div>
                    <p class="mt-0.5 text-[11px] text-violet-100/90">
                        {{ __('messages.menu_edit_subtitle', [], $locale) ?? 'Update name, price, category and discounts for this item.' }}
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

            {{-- Image --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                    {{ __('messages.menu_edit_image_label', [], $locale) ?? 'Image' }}
                </label>
                <input
                    type="file"
                    id="edit_image"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs
                           text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
                <img
                    id="edit_image_preview"
                    class="hidden h-20 mt-2 rounded-lg object-cover border border-slate-200 dark:border-slate-700" />
            </div>

            {{-- Basic info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.menu_edit_name_label', [], $locale) ?? 'Name' }}
                    </label>
                    <input
                        id="edit_name"
                        name="name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
                        placeholder="{{ __('messages.menu_edit_name_placeholder', [], $locale) ?? 'e.g. Iced coffee' }}">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.menu_edit_price_label', [], $locale) ?? 'Price ($)' }}
                    </label>
                    <input
                        id="edit_price"
                        name="price"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
                        placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Category --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.menu_edit_category_label', [], $locale) ?? 'Category' }}
                    </label>
                    <select
                        id="edit_category_id"
                        name="category_id"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
                        <option value="">
                            {{ __('messages.menu_edit_category_placeholder', [], $locale) ?? 'Select category' }}
                        </option>
                    </select>
                </div>

                {{-- Active toggle --}}
                <div class="flex items-center sm:items-end justify-start sm:justify-end pt-1 sm:pt-0">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
                        <input
                            id="edit_is_active"
                            name="is_active"
                            type="checkbox"
                            class="h-3.5 w-3.5 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70">
                        <span>{{ __('messages.menu_edit_is_active_label', [], $locale) ?? 'Visible (active)' }}</span>
                    </label>
                </div>
            </div>

            {{-- Discounts + variants --}}
            <div
                class="rounded-2xl border border-slate-200 dark:border-slate-800
                       bg-slate-50/80 dark:bg-slate-900/60 p-3.5 space-y-3">

                {{-- Discount row --}}
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.menu_edit_discount_section_label', [], $locale) ?? 'Discount' }}
                    </label>
                    <select
                        id="edit_discount_type"
                        class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        <option value="">
                            {{ __('messages.menu_edit_discount_none', [], $locale) ?? 'None' }}
                        </option>
                        <option value="percent">
                            {{ __('messages.menu_edit_discount_percent', [], $locale) ?? 'Percent (%)' }}
                        </option>
                        <option value="fixed">
                            {{ __('messages.menu_edit_discount_fixed', [], $locale) ?? 'Fixed ($)' }}
                        </option>
                    </select>
                    <input
                        id="edit_discount_value"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-28 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100"
                        placeholder="{{ __('messages.menu_edit_discount_value_placeholder', [], $locale) ?? 'Value' }}">
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                            {{ __('messages.menu_edit_discount_starts', [], $locale) ?? 'Starts' }}
                        </label>
                        <input
                            id="edit_discount_starts_at"
                            type="datetime-local"
                            class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                            {{ __('messages.menu_edit_discount_ends', [], $locale) ?? 'Ends' }}
                        </label>
                        <input
                            id="edit_discount_ends_at"
                            type="datetime-local"
                            class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                {{-- Apply to variants block --}}
                <div id="applyVariantsBlock" class="hidden pt-2 space-y-2">
                    <div class="flex items-center gap-2">
                        <input
                            id="apply_to_variants"
                            type="checkbox"
                            class="h-3.5 w-3.5 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70">
                        <label for="apply_to_variants" class="text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_edit_apply_variants_label', [], $locale)
                                ?? 'Apply this discount to selected variants' }}
                        </label>
                    </div>

                    <div
                        id="variantsPicker"
                        class="hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 overflow-hidden">
                        <div
                            class="flex items-center justify-between px-3 py-2 border-b border-slate-100 dark:border-slate-800
                                   bg-slate-50/70 dark:bg-slate-900/60">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ __('messages.menu_edit_variants_header', [], $locale) ?? 'Select variants' }}
                            </div>
                            <label class="text-[11px] inline-flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-300">
                                <input type="checkbox" id="edit_select_all" class="h-3 w-3 rounded border-slate-300">
                                <span>{{ __('messages.menu_edit_variants_select_all', [], $locale) ?? 'Select all' }}</span>
                            </label>
                        </div>

                        <div id="variantsList" class="max-h-48 overflow-auto text-xs"></div>

                        <div class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400">
                            {{ __('messages.menu_edit_variants_hint', [], $locale)
                                ?? 'Selected variants will get their own discount and override the parent item discount.' }}
                        </div>
                    </div>
                </div>

                {{-- Hint + error --}}
                <div
                    id="edit_discount_hint"
                    class="text-[11px] text-slate-500 dark:text-slate-400 min-h-[1rem]">
                </div>
                <div
                    id="editErr"
                    class="text-[11px] text-rose-500 min-h-[1rem]">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex flex-col sm:flex-row justify-between gap-2 pt-2">
                <button
                    type="button"
                    id="manageVariantsBtn"
                    class="inline-flex items-center gap-1.5 rounded-full
                           bg-indigo-600/95 hover:bg-indigo-500
                           px-3.5 py-1.5 text-xs font-semibold text-white
                           shadow-sm shadow-indigo-300/70 transition">
                    <i class="bi bi-columns-gap text-[13px]"></i>
                    <span>{{ __('messages.menu_edit_manage_variants', [], $locale) ?? 'Manage variants' }}</span>
                </button>

                <div class="flex gap-2 justify-end">
                    <button
                        type="button"
                        data-close
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                               px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                               dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
                        <i class="bi bi-arrow-left-short text-[13px]"></i>
                        <span>{{ __('messages.menu_edit_cancel', [], $locale) ?? __('messages.cancel', [], $locale) ?? 'Cancel' }}</span>
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-full
                               bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400
                               px-4 py-1.5 text-xs font-semibold text-white
                               shadow-md shadow-violet-300/70 hover:shadow-violet-400/80
                               transition">
                        <i class="bi bi-check2-circle text-[12px]"></i>
                        <span>{{ __('messages.menu_edit_save', [], $locale) ?? 'Save changes' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
(function editModalSetup(){
    const editModalEl = document.getElementById('editModal');
    if (!editModalEl) return;

    // ---- helpers ----
    function money(n){
        const num = Number(n ?? 0);
        return `${num.toFixed(2)} $`;
    }
    function esc(s){
        return (s ?? '').toString().replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
    }
    function toLocalDT(iso){
        if (!iso) return '';
        const d = new Date(iso);
        const pad = n => String(n).padStart(2,'0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    // ---- translation strings for JS ----
    const TXT_HINT_PERCENT   = @json(__('messages.menu_edit_discount_hint_percent', [], $locale) ?? 'Max 100%');
    const TXT_HINT_FIXED     = @json(__('messages.menu_edit_discount_hint_fixed', [], $locale) ?? 'Cannot exceed price');
    const TXT_VARIANTS_LOAD  = @json(__('messages.menu_edit_variants_loading', [], $locale) ?? 'Loading…');
    const TXT_VARIANTS_NONE  = @json(__('messages.menu_edit_variants_none', [], $locale) ?? 'No variants');
    const TXT_VARIANTS_FAIL  = @json(__('messages.menu_edit_variants_failed', [], $locale) ?? 'Failed to load variants');
    const TXT_TOAST_UPDATED  = @json(__('messages.menu_edit_toast_updated', [], $locale) ?? 'Menu item updated');
    const TXT_UPDATE_FAILED  = @json(__('messages.menu_edit_error_generic', [], $locale) ?? 'Update failed');
    const TXT_OPEN_FIRST     = @json(__('messages.menu_edit_manage_variants_warning', [], $locale) ?? 'Open an item to edit first');

    // ---- populate categories from global cache ----
    const catSel = document.getElementById('edit_category_id');
    function fillCategories(){
        if (!catSel) return;
        const list = Array.isArray(window.categoriesCache) ? window.categoriesCache : [];
        catSel.innerHTML = '<option value="">' +
            esc(@json(__('messages.menu_edit_category_placeholder', [], $locale) ?? 'Select category')) +
            '</option>' +
            list.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
    }
    fillCategories();
    document.addEventListener('categories-loaded', fillCategories);

    // ---- discount hint + picker visibility ----
    const typeSel     = document.getElementById('edit_discount_type');
    const valInp      = document.getElementById('edit_discount_value');
    const hintEl      = document.getElementById('edit_discount_hint');
    const applyToggle = document.getElementById('apply_to_variants');
    const picker      = document.getElementById('variantsPicker');

    function syncHint(){
        const t = typeSel.value;
        if (!hintEl) return;
        if (t === 'percent') {
            hintEl.textContent = TXT_HINT_PERCENT;
        } else if (t === 'fixed') {
            hintEl.textContent = TXT_HINT_FIXED;
        } else {
            hintEl.textContent = '';
        }
    }
    function syncPicker(){
        if (!picker || !applyToggle) return;
        const show = !!typeSel.value && !!applyToggle.checked;
        picker.classList.toggle('hidden', !show);
    }

    typeSel.addEventListener('change', () => { syncHint(); syncPicker(); });
    valInp.addEventListener('input', syncHint);
    applyToggle.addEventListener('change', syncPicker);

    // ---- select all variants ----
    const selectAll = document.getElementById('edit_select_all');
    if (selectAll) {
        selectAll.addEventListener('change', (e)=>{
            document.querySelectorAll('#variantsList input[type="checkbox"]').forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
    }

    // ---- image preview ----
    const imgInput   = document.getElementById('edit_image');
    const imgPreview = document.getElementById('edit_image_preview');
    imgInput.addEventListener('change', (e)=>{
        const f = e.target.files?.[0];
        if (f) {
            imgPreview.src = URL.createObjectURL(f);
            imgPreview.classList.remove('hidden');
        } else {
            imgPreview.classList.add('hidden');
        }
    });

    // ---- load variants list (used from index) ----
    window.loadVariantsForEdit = async function(itemId){
        const listWrap = document.getElementById('variantsList');
        const block    = document.getElementById('applyVariantsBlock');
        if (!listWrap || !block) return;

        listWrap.innerHTML = `<div class="px-3 py-2 text-[11px] text-slate-500">${esc(TXT_VARIANTS_LOAD)}</div>`;
        try{
            const res = await api(`/api/menu/items/${itemId}/variants?visible_only=0&with_trashed=0`);
            const variants = Array.isArray(res) ? res : [];
            if (!variants.length) {
                listWrap.innerHTML =
                    `<div class="px-3 py-2 text-[11px] text-slate-500">${esc(TXT_VARIANTS_NONE)}</div>`;
            } else {
                listWrap.innerHTML = variants.map(v => `
                    <label class="flex items-center gap-2 px-3 py-1.5 text-xs">
                        <input type="checkbox" class="rounded border-slate-300 variant-check" value="${v.id}">
                        <span class="truncate">
                            ${esc(v.name ?? '')}
                            <span class="opacity-60">( ${esc(v.sku || '—')} )</span>
                        </span>
                        <span class="ml-auto text-[11px] opacity-70">${money(v.price)}</span>
                    </label>
                `).join('');
            }
            block.classList.remove('hidden');
        } catch (e) {
            console.error(e);
            listWrap.innerHTML =
                `<div class="px-3 py-2 text-[11px] text-rose-600">${esc(TXT_VARIANTS_FAIL)}</div>`;
        }
    };

    // ---- open manage variants modal ----
    const manageBtn = document.getElementById('manageVariantsBtn');
    manageBtn.addEventListener('click', () => {
        const item = window.__editItem;
        if (!item?.id) {
            if (typeof showToast === 'function') showToast(TXT_OPEN_FIRST, 'warning');
            return;
        }
        if (typeof window.openManageVariants === 'function') {
            window.openManageVariants(item);
        } else {
            document.dispatchEvent(new CustomEvent('open-manage-variants', { detail: { item } }));
        }
    });

    // ---- submit ----
    const form = document.getElementById('editForm');
    const errBox = document.getElementById('editErr');

    form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        errBox.textContent = '';

        const id   = document.getElementById('edit_id').value;
        const name = document.getElementById('edit_name').value.trim();
        const price = String(document.getElementById('edit_price').value || '');
        const categoryId = document.getElementById('edit_category_id').value || '';
        const isActive = document.getElementById('edit_is_active').checked ? '1' : '0';

        const t  = document.getElementById('edit_discount_type').value || '';
        const dv = document.getElementById('edit_discount_value').value;
        const ds = document.getElementById('edit_discount_starts_at').value;
        const de = document.getElementById('edit_discount_ends_at').value;

        const fd = new FormData();
        fd.append('name', name);
        fd.append('price', price);
        fd.append('category_id', categoryId);
        fd.append('is_active', isActive);

        fd.append('discount_type', t);
        fd.append('discount_value', dv !== '' ? String(dv) : '');
        fd.append('discount_starts_at', ds || '');
        fd.append('discount_ends_at', de || '');

        const f = document.getElementById('edit_image').files?.[0];
        if (f) fd.append('image', f);

        fd.append('_method', 'PUT');

        try{
            await api(`/api/menu/items/${id}`, { method: 'POST', body: fd });

            // apply same discount to selected variants if toggled
            if (document.getElementById('apply_to_variants').checked && t) {
                const chosen = Array.from(
                    document.querySelectorAll('#variantsList .variant-check:checked')
                ).map(cb => cb.value);

                if (chosen.length) {
                    const payload = {
                        discount_type: t || null,
                        discount_value: dv || null,
                        discount_starts_at: ds || null,
                        discount_ends_at: de || null,
                    };
                    await Promise.all(chosen.map(vid =>
                        api(`/api/variants/${vid}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload),
                        })
                    ));
                }
            }

            if (typeof showToast === 'function') {
                showToast(TXT_TOAST_UPDATED, 'success');
            }
            if (typeof closeDialog === 'function') {
                closeDialog(editModalEl);
            } else {
                editModalEl.close?.();
            }
            if (typeof load === 'function') load();
        } catch (err) {
            console.error(err);
            errBox.textContent = err?.data?.message || TXT_UPDATE_FAILED;
        }
    });

    // ---- expose prep function for index openEdit() ----
    window.__prepEditModal = function(item){
        if (!item) return;

        if ((catSel?.options?.length || 0) <= 1) {
            fillCategories();
        }

        document.getElementById('edit_id').value = item.id ?? '';
        document.getElementById('edit_name').value = item.name ?? '';
        document.getElementById('edit_price').value = item.price ?? '';
        document.getElementById('edit_category_id').value = item.category_id ?? '';
        document.getElementById('edit_is_active').checked = !!item.is_active;

        document.getElementById('edit_discount_type').value  = item.discount_type || '';
        document.getElementById('edit_discount_value').value = item.discount_value ?? '';
        document.getElementById('edit_discount_starts_at').value = toLocalDT(item.discount_starts_at);
        document.getElementById('edit_discount_ends_at').value   = toLocalDT(item.discount_ends_at);

        syncHint();
        syncPicker();

        // image preview from existing item
        const prev = document.getElementById('edit_image_preview');
        if (item.image) {
            const url = (window.imgUrl ? window.imgUrl(item.image) : item.image);
            prev.src = url;
            prev.classList.remove('hidden');
        } else {
            prev.classList.add('hidden');
        }

        // load variants list
        if (typeof window.loadVariantsForEdit === 'function') {
            window.loadVariantsForEdit(item.id);
        }
    };
})();
</script>
@endpush
@endif
