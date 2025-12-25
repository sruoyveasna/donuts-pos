{{-- resources/views/layouts/sidebar.blade.php --}}
@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    // Active helpers (support nested routes too)
    $isDashboard   = request()->routeIs('dashboard');
    $isPos         = request()->routeIs('pos');
    $isCategories  = request()->routeIs('categories') || request()->routeIs('categories.*');
    $isDiscounts   = request()->routeIs('discounts')  || request()->routeIs('discounts.*');
    $isMenu        = request()->routeIs('menu')       || request()->routeIs('menu.*');
    $isOrders      = request()->routeIs('orders')     || request()->routeIs('orders.*');
    $isUsers       = request()->routeIs('users')      || request()->routeIs('users.*');

    // ✅ Stock / Ingredients page
    $isIngredients = request()->routeIs('ingredients') || request()->routeIs('ingredients.*');

    // ✅ Recipe Management page (index + show)
    $isRecipes = request()->routeIs('recipes.index') || request()->routeIs('recipes.show') || request()->routeIs('recipes.*');

    $items = [
        ['text' => 'Dashboard',  'icon' => 'speedometer', 'route' => route('dashboard'),  'active' => $isDashboard],
        ['text' => 'POS',        'icon' => 'cart3',       'route' => route('pos'),        'active' => $isPos],
        ['text' => 'Categories', 'icon' => 'tag',         'route' => route('categories'), 'active' => $isCategories],

        // ✅ Discounts page
        ['text' => 'Discounts',  'icon' => 'percent',     'route' => route('discounts'),  'active' => $isDiscounts],

        ['text' => 'Menu',       'icon' => 'grid-1x2',    'route' => route('menu'),       'active' => $isMenu],
        ['text' => 'Orders',     'icon' => 'receipt',     'route' => route('orders'),     'active' => $isOrders],

        // ✅ Stock page
        ['text' => 'Stock',      'icon' => 'boxes',       'route' => route('ingredients'), 'active' => $isIngredients],

        // ✅ Recipe Management page
        ['text' => 'Recipes',    'icon' => 'journal-text','route' => route('recipes.index'), 'active' => $isRecipes],

        // ✅ Users page
        ['text' => 'Users',      'icon' => 'people',      'route' => route('users'),      'active' => $isUsers],
    ];

    $shopName = Setting::getCached('app.name', config('app.name', 'Donuts POS'));
    $shopLogo = Setting::getCached('app.logo');

    $initial = $user
        ? mb_strtoupper(mb_substr($user->name ?: $user->email ?: 'U', 0, 1))
        : 'U';

    $avatarUrl = null;
    if ($user && $user->profile && $user->profile->avatar) {
        $avatar = $user->profile->avatar;
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $avatarUrl = $avatar;
        } else {
            $avatarUrl = asset(ltrim($avatar, '/'));
        }
    }
@endphp

<div id="sidebarRoot" class="flex h-full flex-col text-xs text-slate-700 dark:text-slate-200">

    {{-- Top: shop logo + name + collapse button --}}
    <div id="sidebarHeader"
         class="flex items-center justify-between gap-1.5 px-2 py-2 border-b
                border-white/60 dark:border-slate-800/80">
        <div class="flex items-center gap-2">
            @if($shopLogo)
                <img src="{{ asset(ltrim($shopLogo, '/')) }}" alt="Logo"
                     class="h-9 w-9 rounded-full object-cover shadow-sm shadow-rose-200/70 dark:shadow-slate-900/70">
            @else
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full
                           bg-[conic-gradient(at_50%_50%,#f97316,#fb7185,#facc15,#a855f7,#f97316)]
                           text-white text-xs font-semibold shadow-md shadow-rose-300/70">
                    🍩
                </div>
            @endif

            <div class="flex flex-col js-sidebar-label">
                <span class="text-[13px] font-semibold leading-tight truncate max-w-[9rem]">
                    {{ $shopName }}
                </span>
                @if($user)
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[9rem]">
                        {{ $user->name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Collapse button --}}
        <button
            id="sidebarCollapseBtn"
            type="button"
            class="inline-flex h-6 w-6 items-center justify-center rounded-full
                   bg-transparent text-slate-400 dark:text-slate-400
                   hover:bg-rose-500/10 hover:text-rose-300
                   transition"
            aria-label="Toggle sidebar"
            aria-pressed="false"
        >
            <i class="bi bi-chevron-left text-[10px]" id="sidebarCollapseIcon"></i>
        </button>
    </div>

    {{-- Navigation items --}}
    <nav class="flex-1 overflow-y-auto no-scrollbar px-2 py-2">
        <ul class="space-y-1">
            @foreach($items as $it)
                <li>
                    <a href="{{ $it['route'] }}"
                       class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-[12px]
                              {{ $it['active']
                                    ? 'bg-rose-500/90 text-white shadow-sm shadow-rose-300/70'
                                    : 'text-slate-600 hover:bg-white/80 hover:text-slate-900
                                       dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-slate-50' }}">
                        <i class="bi bi-{{ $it['icon'] }} text-[15px]"></i>
                        <span class="js-sidebar-label truncate">{{ $it['text'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Bottom: user profile + theme toggle + popover menu --}}
    @if($user)
        <div class="mt-auto border-t border-white/60 dark:border-slate-800/80 px-3 py-3">
            <div class="relative">
                <div id="sidebarFooterRow" class="flex items-center justify-between gap-1.5">

                    {{-- Profile button --}}
                    <button type="button" id="sidebarProfileBtn" class="flex items-center gap-2 focus:outline-none">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar"
                                 class="h-8 w-8 rounded-full object-cover border-2 border-white/80
                                        dark:border-slate-900/90 shadow-md shadow-rose-300/70">
                        @else
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full
                                       bg-rose-500/90 text-white text-xs font-semibold
                                       shadow-md shadow-rose-300/70">
                                {{ $initial }}
                            </div>
                        @endif

                        <div class="flex flex-col js-sidebar-label text-left">
                            <span class="text-[12px] font-medium truncate max-w-[9rem]">
                                {{ $user->name ?? $user->email }}
                            </span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                {{ __('messages.app_role_label', [], app()->getLocale()) ?? 'Staff user' }}
                            </span>
                        </div>
                    </button>

                    {{-- Theme toggle --}}
                    <button
                        type="button"
                        id="sidebarThemeToggle"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full
                               bg-transparent text-slate-500 dark:text-slate-300
                               border border-slate-300/70 dark:border-slate-700/70
                               hover:bg-rose-500/10 hover:border-rose-400/80 hover:text-rose-300
                               transition"
                        aria-label="Toggle theme"
                    >
                        <span id="sidebarThemeIcon"
                              class="flex h-4 w-4 items-center justify-center rounded-full text-[10px]
                                     bg-gradient-to-br from-amber-200 to-amber-500
                                     dark:from-slate-900 dark:to-slate-700">
                            ☾
                        </span>
                    </button>
                </div>

                {{-- Popover menu (Settings + Logout) --}}
                <div
                    id="sidebarProfileMenu"
                    class="hidden absolute z-40 bottom-16 left-0 right-0
                           rounded-xl border border-slate-200/80 dark:border-slate-700/80
                           bg-white/95 dark:bg-slate-900/95 shadow-lg shadow-slate-900/40
                           py-1 text-[12px] transform transition-all duration-200"
                >
                    <a href="{{ Route::has('settings') ? route('settings') : '#' }}"
                       class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50
                              dark:hover:bg-slate-800/80 cursor-pointer">
                        <i class="bi bi-gear text-[13px] text-slate-500 dark:text-slate-300"></i>
                        <span class="js-sidebar-label">Settings</span>
                    </a>

                    {{-- ✅ logout handled by global JS in app.blade.php (calls /api/auth/logout) --}}
                    <form id="logoutForm" method="POST">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left
                                       text-red-600 hover:bg-red-50
                                       dark:text-red-300 dark:hover:bg-red-900/40">
                            <i class="bi bi-box-arrow-right text-[13px]"></i>
                            <span class="js-sidebar-label">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<style>
  /* Hide scrollbar but keep scroll */
  .no-scrollbar::-webkit-scrollbar { width: 0; height: 0; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const aside        = document.getElementById('sidebarAside');
    const root         = document.getElementById('sidebarRoot');
    const header       = document.getElementById('sidebarHeader');
    const footerRow    = document.getElementById('sidebarFooterRow');
    const collapseBtn  = document.getElementById('sidebarCollapseBtn');
    const collapseIcon = document.getElementById('sidebarCollapseIcon');
    const labels       = root ? root.querySelectorAll('.js-sidebar-label') : [];
    const profileBtn   = document.getElementById('sidebarProfileBtn');
    const profileMenu  = document.getElementById('sidebarProfileMenu');
    const themeBtn     = document.getElementById('sidebarThemeToggle');
    const themeIcon    = document.getElementById('sidebarThemeIcon');

    let collapsed = false;
    try { collapsed = localStorage.getItem('sidebar-collapsed') === '1'; } catch (_) {}

    function applyProfileMenuLayout() {
        if (!profileMenu) return;
        const c = profileMenu.classList;

        if (collapsed) {
            c.add('left-1/2', '-translate-x-1/2', 'w-14');
            c.remove('left-0', 'right-0');
        } else {
            c.add('left-0', 'right-0');
            c.remove('left-1/2', '-translate-x-1/2', 'w-14');
        }
    }

    function applySidebarState() {
        labels.forEach(el => el.classList.toggle('hidden', collapsed));

        if (aside) {
            aside.classList.toggle('md:w-64', !collapsed);
            aside.classList.toggle('lg:w-64', !collapsed);
            aside.classList.toggle('md:w-24', collapsed);
            aside.classList.toggle('lg:w-24', collapsed);
        }

        if (header) {
            header.classList.toggle('justify-between', !collapsed);
            header.classList.toggle('justify-start',  collapsed);
        }
        if (footerRow) {
            footerRow.classList.toggle('justify-between', !collapsed);
            footerRow.classList.toggle('justify-start',  collapsed);
        }

        if (collapseBtn && collapseIcon) {
            collapseBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            collapseIcon.classList.toggle('bi-chevron-left', !collapsed);
            collapseIcon.classList.toggle('bi-chevron-right', collapsed);
        }

        applyProfileMenuLayout();
    }

    applySidebarState();

    if (collapseBtn) {
        collapseBtn.addEventListener('click', () => {
            collapsed = !collapsed;
            try { localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0'); } catch (_) {}
            applySidebarState();
        });
    }

    if (profileBtn && profileMenu) {
        function closeProfileMenu() { profileMenu.classList.add('hidden'); }

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                closeProfileMenu();
            }
        });
    }

    if (themeBtn && themeIcon) {
        const htmlEl = document.documentElement;

        function setThemeUI(theme) {
            themeIcon.textContent = theme === 'dark' ? '☾' : '☀';
            const navIcon  = document.getElementById('themeIcon');
            const navLabel = document.getElementById('themeLabel');
            if (navIcon)  navIcon.textContent  = theme === 'dark' ? '☾' : '☀';
            if (navLabel) navLabel.textContent = theme === 'dark' ? 'Dark' : 'Light';
        }

        function applyTheme(theme) {
            if (theme === 'dark') htmlEl.classList.add('dark');
            else htmlEl.classList.remove('dark');
            try { localStorage.setItem('pos-theme', theme); } catch (_) {}
            setThemeUI(theme);
        }

        let savedTheme = 'dark';
        try { savedTheme = localStorage.getItem('pos-theme') || (htmlEl.classList.contains('dark') ? 'dark' : 'light'); }
        catch (_) { savedTheme = htmlEl.classList.contains('dark') ? 'dark' : 'light'; }
        applyTheme(savedTheme);

        themeBtn.addEventListener('click', () => {
            const current = htmlEl.classList.contains('dark') ? 'dark' : 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
});
</script>
@endpush
