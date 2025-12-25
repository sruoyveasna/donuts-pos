{{-- resources/views/menu/partials/create-modal.blade.php --}}
@if($canWrite)
@php
    $locale = app()->getLocale();
@endphp

<dialog id="createModal" class="bg-transparent">
    <div
        class="rounded-2xl border border-white/60 dark:border-slate-800/80
               bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-violet-200/40
               overflow-hidden w-full max-w-lg">

        <form id="createForm" class="px-4 md:px-5 py-4 space-y-4" enctype="multipart/form-data">
            {{-- Header --}}
            <div
                class="mb-2 -mx-4 -mt-4 px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                       bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-500
                       text-slate-50 flex items-center justify-between gap-2">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full
                                   bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
                            <i class="bi bi-plus-lg text-[13px]"></i>
                        </span>
                        <span>
                            {{ __('messages.menu_create_title', [], $locale) ?? 'Add menu item' }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-[11px] text-violet-100/90">
                        {{ __('messages.menu_create_subtitle', [], $locale) ?? 'Upload an image, choose a category and set the price.' }}
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

            {{-- Body --}}
            <div class="space-y-3">
                {{-- Image --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                        {{ __('messages.menu_create_image_label', [], $locale) ?? 'Image' }}
                    </label>
                    <input
                        type="file"
                        id="create_image"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-2 py-1.5 text-xs
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
                    <img
                        id="create_image_preview"
                        class="h-20 mt-2 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hidden">
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                        {{ __('messages.menu_create_name_label', [], $locale) ?? 'Name' }}
                    </label>
                    <input
                        required
                        id="create_name"
                        type="text"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
                        placeholder="{{ __('messages.menu_create_name_placeholder', [], $locale) ?? 'e.g. Glazed donut' }}">
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                        {{ __('messages.menu_create_price_label', [], $locale) ?? 'Price ($)' }}
                    </label>
                    <input
                        required
                        id="create_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900"
                        placeholder="0.00">
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                        {{ __('messages.menu_create_category_label', [], $locale) ?? 'Category' }}
                    </label>
                    <select
                        id="create_category_id"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-900">
                        <option value="">
                            {{ __('messages.menu_create_category_placeholder', [], $locale) ?? 'Select category' }}
                        </option>
                    </select>
                </div>

                {{-- Active toggle --}}
                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200 mt-1">
                    <input
                        type="checkbox"
                        id="create_active"
                        checked
                        class="h-3.5 w-3.5 rounded border-slate-300 text-violet-500 focus:ring-violet-400/70">
                    <span>
                        {{ __('messages.menu_create_is_active_label', [], $locale) ?? 'Visible (active)' }}
                    </span>
                </label>

                {{-- Discount block --}}
                <div class="mt-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-3 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ __('messages.menu_create_discount_section_label', [], $locale) ?? 'Discount' }}
                        </label>
                        <select
                            id="create_discount_type"
                            class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            <option value="">
                                {{ __('messages.menu_create_discount_none', [], $locale) ?? 'None' }}
                            </option>
                            <option value="percent">
                                {{ __('messages.menu_create_discount_percent', [], $locale) ?? 'Percent (%)' }}
                            </option>
                            <option value="fixed">
                                {{ __('messages.menu_create_discount_fixed', [], $locale) ?? 'Fixed ($)' }}
                            </option>
                        </select>
                        <input
                            id="create_discount_value"
                            type="number"
                            step="0.01"
                            min="0"
                            class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 w-28 text-xs
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100"
                            placeholder="{{ __('messages.menu_create_discount_value_placeholder', [], $locale) ?? 'Value' }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.menu_create_discount_starts', [], $locale) ?? 'Starts' }}
                            </label>
                            <input
                                id="create_discount_starts_at"
                                type="datetime-local"
                                class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                                       bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.menu_create_discount_ends', [], $locale) ?? 'Ends' }}
                            </label>
                            <input
                                id="create_discount_ends_at"
                                type="datetime-local"
                                class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs
                                       bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    {{-- Discount hint + error --}}
                    <div
                        id="create_discount_hint"
                        class="text-[11px] text-slate-500 dark:text-slate-400 min-h-[1rem]">
                    </div>
                </div>

                {{-- Error message --}}
                <p id="createErr" class="text-[11px] text-rose-500 mt-1 min-h-[1rem]"></p>
            </div>

            {{-- Footer --}}
            <div class="flex justify-between items-center pt-1">
                <button
                    type="button"
                    data-close
                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                           px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                           dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
                    <i class="bi bi-arrow-left-short text-[13px]"></i>
                    <span>{{ __('messages.menu_create_cancel', [], $locale) ?? __('messages.cancel', [], $locale) ?? 'Cancel' }}</span>
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-full
                           bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-400
                           px-4 py-1.5 text-xs font-semibold text-white
                           shadow-md shadow-violet-300/70 hover:shadow-violet-400/80
                           transition">
                    <i class="bi bi-check2-circle text-[12px]"></i>
                    <span>{{ __('messages.menu_create_save', [], $locale) ?? 'Save item' }}</span>
                </button>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
(function setupMenuCreateModal(){
    const dlg           = document.getElementById('createModal');
    const form          = document.getElementById('createForm');
    const sel           = document.getElementById('create_category_id');
    const imgInput      = document.getElementById('create_image');
    const imgPreview    = document.getElementById('create_image_preview');
    const discountType  = document.getElementById('create_discount_type');
    const discountValue = document.getElementById('create_discount_value');
    const discountHint  = document.getElementById('create_discount_hint');
    const errBox        = document.getElementById('createErr');

    if (!dlg || !form) return;

    const PLACEHOLDER_OPTION = @json(__('messages.menu_create_category_placeholder', [], $locale) ?? 'Select category');
    const TOAST_ADDED        = @json(__('messages.menu_toast_added', [], $locale) ?? 'Menu item added');
    const CREATE_FAILED      = @json(__('messages.menu_create_error_generic', [], $locale) ?? 'Create failed');
    const HINT_PERCENT       = @json(__('messages.menu_create_discount_hint_percent', [], $locale) ?? 'Max 100%');
    const HINT_FIXED         = @json(__('messages.menu_create_discount_hint_fixed', [], $locale) ?? 'Cannot exceed price');

    // Populate categories from global cache (set in menu index) or from state.categories
    function populateCategories() {
        if (!sel) return;

        const list =
            (window.categoriesCache && Array.isArray(window.categoriesCache))
                ? window.categoriesCache
                : (window.state && Array.isArray(window.state.categories))
                    ? window.state.categories
                    : [];

        sel.innerHTML = '<option value="">' + PLACEHOLDER_OPTION + '</option>' +
            list.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
    }

    // Esc helper (matches page helpers)
    function esc(s){
        return (s ?? '').toString().replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
    }

    // If categories are loaded later, listen for custom event
    document.addEventListener('categories-loaded', populateCategories);
    // Try once immediately in case cache already exists
    populateCategories();

    // Image preview
    imgInput?.addEventListener('change', (e)=>{
        const f = e.target.files?.[0];
        if (f) {
            imgPreview.src = URL.createObjectURL(f);
            imgPreview.classList.remove('hidden');
        } else {
            imgPreview.classList.add('hidden');
        }
    });

    // Discount hint
    function syncDiscountHint() {
        const t = discountType?.value || '';
        if (!discountHint) return;
        if (t === 'percent') {
            discountHint.textContent = HINT_PERCENT;
        } else if (t === 'fixed') {
            discountHint.textContent = HINT_FIXED;
        } else {
            discountHint.textContent = '';
        }
    }
    discountType?.addEventListener('change', syncDiscountHint);
    discountValue?.addEventListener('input', syncDiscountHint);

    // Submit handler
    form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        errBox.textContent = '';

        const fd = new FormData();
        fd.append('name',  document.getElementById('create_name').value.trim());
        fd.append('price', String(document.getElementById('create_price').value || ''));

        const cid = document.getElementById('create_category_id').value;
        if (cid) fd.append('category_id', cid);

        fd.append('is_active', document.getElementById('create_active').checked ? '1' : '0');

        const t = document.getElementById('create_discount_type').value;
        if (t) {
            fd.append('discount_type', t);
            fd.append('discount_value', String(document.getElementById('create_discount_value').value || ''));
            const s  = document.getElementById('create_discount_starts_at').value;
            const e2 = document.getElementById('create_discount_ends_at').value;
            if (s)  fd.append('discount_starts_at', s);
            if (e2) fd.append('discount_ends_at', e2);
        }

        const f = document.getElementById('create_image').files?.[0];
        if (f) fd.append('image', f);

        try {
            await api('/api/menu/items', { method:'POST', body: fd });
            if (typeof showToast === 'function') {
                showToast(TOAST_ADDED, 'success');
            }
            if (typeof closeDialog === 'function') {
                closeDialog(dlg);
            } else {
                dlg.close?.();
            }
            if (window.state) window.state.page = 1;
            if (typeof load === 'function') load();
        } catch (err) {
            errBox.textContent = err?.data?.message || CREATE_FAILED;
            console.error(err);
        }
    });
})();
</script>
@endpush
@endif
