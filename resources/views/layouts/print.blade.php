<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-white font-sans text-gray-900 antialiased" data-force-theme="light">
    <div class="no-print fixed bottom-6 inset-x-0 z-50 flex justify-center gap-3 px-4 print:hidden">
        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#0F4C81]/20 hover:bg-[#0c3d6b]">
            {{ __('common.print') }}
        </button>
        <a href="{{ $backUrl ?? url()->previous() }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-bold text-gray-800 dark:text-[#F3F4F6] shadow-sm hover:bg-gray-50 dark:hover:bg-[#111827]/80">
            {{ __('common.back') }}
        </a>
    </div>
    <main class="mx-auto max-w-[1400px] px-6 py-10 print:py-6">
        @yield('content')
    </main>
</body>
</html>
