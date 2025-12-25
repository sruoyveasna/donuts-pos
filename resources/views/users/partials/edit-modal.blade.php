{{-- resources/views/users/partials/edit-modal.blade.php --}}
@if($canWrite)
@php
    $locale = app()->getLocale();
@endphp

<dialog id="editModal" class="bg-transparent">
    <div
        class="rounded-2xl border border-white/60 dark:border-slate-800/80
               bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-indigo-200/40
               overflow-hidden">

        {{-- Header --}}
        <div
            class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
                   bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800
                   text-slate-50 flex items-center justify-between gap-2">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-full
                               bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
                        <i class="bi bi-pencil-square text-[13px]"></i>
                    </span>
                    <span>
                        {{ __('messages.users_edit_title', [], $locale) ?: 'Edit user' }}
                    </span>
                </div>
                <p class="mt-0.5 text-[11px] text-slate-200/80">
                    {{ __('messages.users_edit_subtitle', [], $locale) ?: 'Update user info, role, or reset password.' }}
                </p>
            </div>

            <button
                type="button"
                class="inline-flex h-7 w-7 items-center justify-center rounded-full
                       bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
                data-close
                aria-label="{{ __('messages.users_button_close', [], $locale) ?: 'Close' }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <form id="editForm" class="px-4 md:px-5 py-4 space-y-4">
            @csrf
            @method('PATCH')

            <input type="hidden" id="edit_id" name="id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Name --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.users_field_name_label', [], $locale) ?: 'Name' }}
                    </label>
                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                               bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                               text-slate-900 dark:text-slate-50
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400"
                        placeholder="{{ __('messages.users_field_name_placeholder', [], $locale) ?: 'Full name' }}"
                        required>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        {{ __('messages.users_field_name_hint', [], $locale) ?: 'This name appears in the sidebar and user list.' }}
                    </p>
                </div>

                {{-- Email --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.users_field_email_label', [], $locale) ?: 'Email' }}
                    </label>
                    <input
                        type="email"
                        id="edit_email"
                        name="email"
                        class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                               bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                               text-slate-900 dark:text-slate-50
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400"
                        placeholder="{{ __('messages.users_field_email_placeholder', [], $locale) ?: 'name@example.com' }}"
                        required>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        {{ __('messages.users_field_email_hint', [], $locale) ?: 'Used for login and notifications.' }}
                    </p>
                </div>

                {{-- Role --}}
                <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                    {{ __('messages.users_field_role_label', [], $locale) }}
                </label>

                <select
                    id="edit_role_id"
                    name="role_id"
                    class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                        bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                        text-slate-900 dark:text-slate-50
                        focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400"
                    required>
                    <option value="">
                    {{ __('messages.users_field_role_placeholder', [], $locale) ?: 'Select role…' }}
                    </option>

                    @foreach(($rolesList ?? []) as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>

                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    {{ __('messages.users_field_role_hint', [], $locale) ?: 'Controls what this user can access.' }}
                </p>
                </div>


                {{-- Password (optional) --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">
                        {{ __('messages.users_field_password_label', [], $locale) ?: 'Password' }}
                    </label>
                    <input
                        type="password"
                        id="edit_password"
                        name="password"
                        class="w-full rounded-xl border border-slate-200/80 dark:border-slate-700/80
                               bg-slate-50/80 dark:bg-slate-900 px-3 py-2 text-sm
                               text-slate-900 dark:text-slate-50
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400"
                        placeholder="{{ __('messages.users_field_password_edit_placeholder', [], $locale) ?: 'Leave blank to keep current' }}">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        {{ __('messages.users_field_password_edit_hint', [], $locale) ?: 'Fill only if you want to reset this password.' }}
                    </p>
                </div>
            </div>

            {{-- Error message --}}
            <p id="editErr" class="text-[11px] text-rose-500 mt-1 min-h-[1rem]"></p>

            {{-- Footer --}}
            <div class="flex justify-between items-center pt-1">
                <button
                    type="button"
                    data-close
                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
                           px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50
                           dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
                    <i class="bi bi-arrow-left-short text-[13px]"></i>
                    <span>{{ __('messages.users_button_cancel', [], $locale) ?: 'Cancel' }}</span>
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-full
                           bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400
                           px-4 py-1.5 text-xs font-semibold text-white
                           shadow-md shadow-sky-300/70 hover:shadow-sky-400/80
                           transition">
                    <i class="bi bi-check2-circle text-[12px]"></i>
                    <span>{{ __('messages.users_button_save_changes', [], $locale) ?: 'Save changes' }}</span>
                </button>
            </div>
        </form>
    </div>
</dialog>
@endif
