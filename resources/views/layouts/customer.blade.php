<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title', 'Donuts Customer')</title>

  <script>
    (function () {
      try {
        var t = localStorage.getItem('pos-theme') || 'light'; // customer default light
        if (t === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      } catch (_) {}
    })();
  </script>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { colors: { brand: { DEFAULT: '#5b63ff' } } } }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  @stack('head')
</head>

<body class="min-h-screen antialiased font-['Kantumruy_Pro',system-ui]
             bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  {{-- Customer app container (mobile) --}}
  <div class="mx-auto max-w-[420px] min-h-screen bg-slate-50 dark:bg-slate-950">
    @yield('content')
  </div>

  @stack('scripts')
</body>
</html>
