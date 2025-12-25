<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Donuts POS'))</title>

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

    {{-- Khmer/English font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    />

    {{-- Bootstrap icons only (for the <i class="bi ..."> you use) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @stack('head')
</head>

<body class="min-h-screen antialiased font-['Kantumruy_Pro',system-ui]
             bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

    @yield('content')

    @stack('scripts')
</body>
</html>
