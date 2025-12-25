{{-- resources/views/settings/partials/edit-profile.blade.php --}}
@php
    $locale = app()->getLocale();
    $user   = auth()->user()?->load('profile');

    $profile    = $user?->profile;
    $birthValue = optional($profile?->birthdate)->format('Y-m-d');

    // avatar URL (db path or external, fallback to ui-avatars)
    $avatarSrc = null;
    if ($profile && $profile->avatar) {
        $avatar = $profile->avatar;
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $avatarSrc = $avatar;
        } else {
            $avatarSrc = asset(ltrim($avatar, '/'));
        }
    } else {
        $avatarSrc = 'https://ui-avatars.com/api/?background=f97316&color=fff&name='
            . urlencode($user?->name ?? 'User');
    }
@endphp

<dialog id="editProfileDialog">
    <div class="bg-slate-900/95 dark:bg-slate-950 text-slate-50 rounded-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 md:px-5 py-3 border-b border-slate-800/80">
            <div>
                <h2 class="text-sm md:text-base font-semibold">
                    {{ __('messages.settings_profile_tab', [], $locale) ?? 'Profile settings' }}
                </h2>
                <p class="text-[11px] text-slate-400">
                    {{ __('messages.settings_edit_profile_help', [], $locale) ?? 'Update your personal details and avatar.' }}
                </p>
            </div>

            <button type="button"
                    data-close
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full
                           text-slate-400 hover:text-slate-100 hover:bg-slate-800/80 transition">
                <i class="bi bi-x-lg text-[11px]"></i>
            </button>
        </div>

        {{-- Error banner --}}
        <div id="profileError"
             class="hidden mx-4 mt-3 rounded-lg border border-red-500/70 bg-red-900/40
                    text-[11px] text-red-100 px-3 py-2">
        </div>

        {{-- Form --}}
        <form id="profileForm"
              class="px-4 md:px-5 py-4 md:py-5 space-y-5"
              enctype="multipart/form-data">
            @csrf

            <div class="flex flex-col md:flex-row md:items-center gap-4">
                {{-- Avatar picker --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="relative">
                        <div
                            class="h-20 w-20 rounded-full overflow-hidden border border-slate-700
                                   bg-slate-900 flex items-center justify-center
                                   shadow-md shadow-emerald-900/60">
                            <img
                                id="avatarPreview"
                                src="{{ $avatarSrc }}"
                                alt="Avatar"
                                class="h-full w-full object-cover">
                        </div>
                        <button type="button"
                                id="avatarTrigger"
                                class="absolute -bottom-2 -right-2 rounded-full bg-emerald-500 text-white
                                       border border-white/80 shadow-md shadow-emerald-500/70 w-7 h-7
                                       flex items-center justify-center text-xs">
                            <i class="bi bi-camera-fill"></i>
                        </button>
                    </div>

                    <input
                        id="avatarInput"
                        name="avatar"
                        type="file"
                        accept="image/*"
                        class="hidden">

                    <p class="text-[11px] text-slate-400">
                        {{ __('messages.settings_avatar_hint', [], $locale) ?? 'Square image, up to 2 MB' }}
                    </p>
                </div>

                {{-- Fields --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.profile_name', [], $locale) ?? 'Name' }}
                        </label>
                        <input
                            type="text"
                            name="name"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm text-slate-50
                                   placeholder:text-slate-500
                                   focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"
                            value="{{ $user?->name ?? '' }}"
                            placeholder="Cashier Name">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.profile_phone', [], $locale) ?? 'Phone' }}
                        </label>
                        <input
                            type="text"
                            name="phone"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm text-slate-50
                                   placeholder:text-slate-500
                                   focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"
                            value="{{ $profile?->phone ?? '' }}">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.profile_gender', [], $locale) ?? 'Gender' }}
                        </label>
                        @php $g = $profile?->gender; @endphp
                        <select
                            name="gender"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm text-slate-50
                                   focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                            <option value="">
                                {{ __('messages.profile_gender_none', [], $locale) ?? 'Not specified' }}
                            </option>
                            <option value="male"   {{ $g === 'male'   ? 'selected' : '' }}>
                                {{ __('messages.profile_gender_male', [], $locale) ?? 'Male' }}
                            </option>
                            <option value="female" {{ $g === 'female' ? 'selected' : '' }}>
                                {{ __('messages.profile_gender_female', [], $locale) ?? 'Female' }}
                            </option>
                            <option value="other"  {{ $g === 'other'  ? 'selected' : '' }}>
                                {{ __('messages.profile_gender_other', [], $locale) ?? 'Other' }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.profile_birthdate', [], $locale) ?? 'Birthdate' }}
                        </label>
                        <input
                            type="date"
                            name="birthdate"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm text-slate-50
                                   focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"
                            value="{{ $birthValue }}">
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-xs font-medium text-slate-200">
                            {{ __('messages.profile_address', [], $locale) ?? 'Address' }}
                        </label>
                        <textarea
                            name="address"
                            rows="2"
                            class="w-full rounded-xl border border-slate-700
                                   bg-slate-900 px-3 py-2 text-sm text-slate-50
                                   placeholder:text-slate-500
                                   focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"
                            placeholder="Street, city, province">{{ trim($profile?->address ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Footer buttons --}}
            <div class="flex justify-between items-center pt-3">
                <button type="button"
                        data-close
                        class="text-[11px] md:text-xs text-slate-400 hover:text-slate-100">
                    {{ __('messages.cancel', [], $locale) ?? 'Cancel' }}
                </button>

                <button type="submit"
                        id="btnSaveProfile"
                        class="inline-flex items-center gap-2 rounded-full
                               bg-gradient-to-r from-emerald-500 via-teal-400 to-sky-400
                               px-4 py-2 text-xs md:text-sm font-semibold text-white
                               shadow-md shadow-emerald-400/70 hover:shadow-emerald-500/80
                               disabled:opacity-60 disabled:cursor-not-allowed
                               transition">
                    <span class="spinner hidden h-4 w-4 rounded-full border border-white/40 border-t-transparent animate-spin"></span>
                    <span class="label inline-flex items-center gap-1">
                        <i class="bi bi-person-check text-[12px]"></i>
                        <span>{{ __('messages.settings_save_profile', [], $locale) ?? 'Update profile' }}</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.__profileModalWired) return;
    window.__profileModalWired = true;

    const form     = document.getElementById('profileForm');
    const errorBox = document.getElementById('profileError');
    const btnSave  = document.getElementById('btnSaveProfile');

    // Avatar preview
    const avatarInput   = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarTrigger = document.getElementById('avatarTrigger');

    if (avatarTrigger && avatarInput && avatarPreview) {
        avatarTrigger.addEventListener('click', () => avatarInput.click());
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (file) {
                avatarPreview.src = URL.createObjectURL(file);
            }
        });
    }

    function setBusy(on) {
        if (!btnSave) return;
        btnSave.disabled = on;
        const spinner = btnSave.querySelector('.spinner');
        const label   = btnSave.querySelector('.label');
        if (spinner) spinner.classList.toggle('hidden', !on);
        if (label)   label.classList.toggle('hidden', on);
    }

    function showError(msg) {
        if (!errorBox) return;
        errorBox.textContent = msg || 'Something went wrong';
        errorBox.classList.remove('hidden');
    }

    function clearError() {
        if (!errorBox) return;
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError();
            setBusy(true);

            const fd = new FormData(form);

            // ensure clean text values
            fd.set('name',      form.name.value.trim());
            fd.set('phone',     form.phone.value.trim());
            fd.set('gender',    form.gender.value);
            fd.set('birthdate', form.birthdate.value);
            fd.set('address',   form.address.value.trim());

            try {
                await api('/api/profile', {
                    method: 'POST',
                    body: fd,
                });

                if (typeof showToast === 'function') {
                    showToast('Profile updated.', 'success');
                }

                // Reload so read-only view + sidebar avatar update
                window.location.reload();
            } catch (err) {
                console.error(err);
                let msg = err?.message || 'Failed to update profile';
                if (err?.data?.errors) {
                    const parts = [];
                    Object.values(err.data.errors).forEach(arr => {
                        (arr || []).forEach(t => parts.push(String(t)));
                    });
                    if (parts.length) msg = parts.join(' ');
                }
                showError(msg);
                if (typeof showToast === 'function') {
                    showToast(msg, 'danger');
                }
            } finally {
                setBusy(false);
            }
        });
    }
});
</script>
@endpush
