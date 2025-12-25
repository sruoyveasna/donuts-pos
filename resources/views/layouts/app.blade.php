<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Donuts POS')</title>

  {{-- Apply saved theme (dark/light) ASAP, before Tailwind renders --}}
  <script>
    (function () {
      try {
        var t = localStorage.getItem('pos-theme') || 'dark';
        if (t === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      } catch (_) {}
    })();
  </script>

  {{-- Tailwind (Play CDN) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#ec4899' }, // donut pink
          },
          boxShadow: {
            donut: '0 18px 45px rgba(248,113,113,0.5)',
          },
        }
      }
    }
  </script>

  {{-- Google font for Khmer/English --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
  />

  {{-- Keep Bootstrap CSS + Icons (for now) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  @stack('head')

  <style>
    /* ✅ LOCK GLOBAL SCROLL */
    html, body { height: 100%; overflow: hidden; }

    @keyframes float {
      from { transform: translate3d(0, 0, 0) scale(1); }
      to   { transform: translate3d(25px, -18px, 0) scale(1.05); }
    }

    /* Tooltip helper (unchanged) */
    .tw-tip { position: relative; }
    .tw-tip[data-tooltip]:hover::after,
    .tw-tip[data-tooltip]:focus-visible::after {
      content: attr(data-tooltip);
      position: absolute; right: 0; top: -0.5rem; transform: translateY(-100%);
      white-space: nowrap; font-size: .75rem; line-height: 1rem;
      padding: .25rem .5rem; border-radius: .375rem;
      background: rgb(17 24 39); color: #fff; z-index: 10500;
    }
    .tw-tip[data-tooltip]:hover::before,
    .tw-tip[data-tooltip]:focus-visible::before {
      content: ''; position: absolute; right: .5rem; top: -0.5rem; transform: translateY(-100%);
      border-width: 6px 6px 0 6px; border-style: solid;
      border-color: rgb(17 24 39) transparent transparent transparent; z-index: 10500;
    }

    /* <dialog> defaults (unchanged) */
    dialog {
      border: 0; padding: 0;
      width: min(32rem, calc(100vw - 2rem));
      border-radius: 0.75rem;
      box-shadow: 0 25px 50px -12px rgb(0 0 0 / .25);
      z-index: 10000;
    }
    dialog::backdrop {
      background: rgba(0,0,0,.4);
      backdrop-filter: blur(0.5px);
    }

    /* Tailwind-styled toast (unchanged) */
    .tw-toast-wrap {
      position: fixed; inset-inline-end: 0; top: 0; z-index: 12000;
      padding: .75rem; display: grid; gap: .5rem;
    }
    .tw-toast {
      display: flex; align-items: center; gap: .75rem;
      border-radius: .5rem; padding: .625rem .75rem; color: #fff;
      box-shadow: 0 10px 15px -3px rgb(0 0 0 / .1),
                  0 4px 6px -4px rgb(0 0 0 / .1);
    }
    .tw-toast-success { background: #16a34a; }
    .tw-toast-danger  { background: #dc2626; }
    .tw-toast-primary { background: #ec4899; }
    .tw-toast-warning { background: #d97706; }
  </style>
</head>

{{-- ✅ h-screen + overflow-hidden so app never scrolls --}}
<body class="h-screen overflow-hidden antialiased font-['Kantumruy_Pro',system-ui]
             bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

  {{-- ✅ main app shell locked to viewport --}}
  <div class="relative h-screen overflow-hidden flex flex-col
              bg-[radial-gradient(circle_at_top,_#ffe4e6_0,_#fef3c7_35%,_#f5f3ff_100%)]
              dark:bg-[radial-gradient(circle_at_top,_#4c1d95_0,_#020617_55%,_#020617_100%)]">

    {{-- Donut blobs --}}
    <div class="pointer-events-none fixed -top-20 -left-10 h-80 w-80 rounded-full
                bg-[#fb7185] opacity-40 blur-3xl
                animate-[float_16s_ease-in-out_infinite_alternate]"></div>

    <div class="pointer-events-none fixed -bottom-20 -right-10 h-64 w-64 rounded-full
                bg-[#facc15] opacity-40 blur-3xl
                animate-[float_18s_ease-in-out_infinite_alternate-reverse]"></div>

    <div class="pointer-events-none fixed top-1/3 right-10 h-56 w-56 rounded-full
                bg-[#a855f7] opacity-40 blur-3xl
                animate-[float_22s_ease-in-out_infinite_alternate]"></div>

    {{-- ✅ Main layout: sidebar + content --}}
    <div class="flex flex-1 min-h-0 overflow-hidden">
      <aside
        id="sidebarAside"
        class="hidden md:block md:w-[235px] lg:w-[235px] shrink-0 border-r
               border-white/40 dark:border-slate-800/80
               bg-white/70 dark:bg-slate-950/80 backdrop-blur-xl
               transition-[width] duration-200 ease-in-out">
        @include('layouts.sidebar')
      </aside>

      {{-- ✅ Main must not scroll; children (pages) can define scroll areas --}}
      <main class="flex-1 min-h-0 overflow-hidden px-3 py-4 md:px-6 md:py-6">
        <div class="h-full min-h-0 overflow-hidden">
          @yield('content')
        </div>
      </main>
    </div>
  </div>

  {{-- ✅ Global dynamic UI components (Toast + Confirm) mounted ONCE for every page --}}
  <x-toast />
  <x-confirm-dialog />

  <script>
    // =========================
    // Token helpers
    // =========================
    const TOKEN_KEY = 'donuts_token';
    function getToken() {
      try { return localStorage.getItem(TOKEN_KEY) || ''; } catch { return ''; }
    }
    function setToken(t) {
      try { t ? localStorage.setItem(TOKEN_KEY, t) : localStorage.removeItem(TOKEN_KEY); } catch {}
    }

    // =========================
    // SPA CSRF helpers
    // =========================
    function getCookie(name){
      const m = document.cookie.match(new RegExp('(^| )'+name+'=([^;]+)'));
      return m ? decodeURIComponent(m[2]) : '';
    }

    let __csrfReady = null;
    function ensureCsrfCookie() {
      if (getCookie('XSRF-TOKEN')) return Promise.resolve();
      return fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' }).then(()=>{});
    }

    // =========================
    // api() helper
    // =========================
    async function api(url, opts = {}) {
      const method = (opts.method || 'GET').toUpperCase();
      const needsCsrf = !['GET','HEAD','OPTIONS'].includes(method);
      const token = getToken();

      if (needsCsrf) {
        __csrfReady = __csrfReady || ensureCsrfCookie();
        await __csrfReady;
      }

      async function send() {
        const headers = { 'Accept': 'application/json', ...(opts.headers || {}) };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        if (needsCsrf) {
          const xsrf = getCookie('XSRF-TOKEN');
          if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;
          headers['X-Requested-With'] = 'XMLHttpRequest';
        }

        const res = await fetch(url, { credentials: 'same-origin', ...opts, headers });
        const text = await res.text();
        let data; try { data = text ? JSON.parse(text) : null; } catch { data = { message: text }; }
        return { res, data };
      }

      let { res, data } = await send();
      if (res.status === 419 && needsCsrf) {
        await ensureCsrfCookie();
        ({ res, data } = await send());
      }
      if (res.status === 401) {
        try { localStorage.removeItem(TOKEN_KEY); } catch {}
        throw Object.assign(new Error(data?.message || 'Unauthorized'), { status: 401, data });
      }
      if (!res.ok) {
        throw Object.assign(new Error(data?.message || `HTTP ${res.status}`), { status: res.status, data });
      }
      return data;
    }

    // =========================
    // Global logout
    // =========================
    document.addEventListener('submit', async (e) => {
      if (e.target && e.target.id === 'logoutForm') {
        e.preventDefault();
        try { await api('/api/auth/logout', { method: 'POST' }); } catch {}
        setToken('');
        location.href = '/login';
      }
    });

    // =========================
    // <dialog> helpers (unchanged)
    // =========================
    function wireDialog(dlg){
      if (!dlg || dlg.__wired) return;
      dlg.__wired = true;
      dlg.addEventListener('cancel', e => { e.preventDefault(); dlg.close(); });
      dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
      dlg.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => dlg.close()));
    }
    window.openDialog  = function(dlg){ wireDialog(dlg); if (!dlg.open) dlg.showModal(); dlg.querySelector('input,button,select,textarea')?.focus(); }
    window.closeDialog = function(dlg){ if (dlg?.open) dlg.close(); }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('dialog').forEach(wireDialog);

      // Theme toggle wiring (if any page has a #themeToggle button)
      const htmlEl     = document.documentElement;
      const toggle     = document.getElementById('themeToggle');
      const themeIcon  = document.getElementById('themeIcon');
      const themeLabel = document.getElementById('themeLabel');

      function applyTheme(theme) {
        if (theme === 'dark') {
          htmlEl.classList.add('dark');
          if (themeIcon)  themeIcon.textContent  = '☾';
          if (themeLabel) themeLabel.textContent = 'Dark';
        } else {
          htmlEl.classList.remove('dark');
          if (themeIcon)  themeIcon.textContent  = '☀';
          if (themeLabel) themeLabel.textContent = 'Light';
        }
        try { localStorage.setItem('pos-theme', theme); } catch (_) {}
      }

      const currentSaved = (function () {
        try { return localStorage.getItem('pos-theme') || 'dark'; } catch (_) { return 'dark'; }
      })();
      applyTheme(currentSaved);

      if (toggle) {
        toggle.addEventListener('click', () => {
          const now = htmlEl.classList.contains('dark') ? 'dark' : 'light';
          applyTheme(now === 'dark' ? 'light' : 'dark');
        });
      }
    });
  </script>

  @stack('scripts')
</body>
</html>
