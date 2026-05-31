@php
    $user = auth()->user();
    $homeUrl = $user
        ? ($user->hasRole('super_admin')
            ? route('platform.dashboard')
            : ($user->can('view dashboard') ? route('dashboard') : route('profile.edit')))
        : route('login');
    $profileUrl = $user ? route('profile.edit') : null;
    $message = isset($exception) && $exception->getMessage()
        ? $exception->getMessage()
        : null;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>403 — {{ config('app.name', 'Clinic System') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            * { font-family: 'Cairo', sans-serif; }
        </style>
    </head>
    <body class="min-h-full bg-[#F8F9FA] text-[#1F2937] dark:bg-[#0F172A] dark:text-[#F3F4F6]">
        <div class="flex min-h-full flex-col items-center justify-center px-4 py-12">
            <div class="w-full max-w-md rounded-2xl border border-slate-200/90 bg-white p-8 shadow-lg dark:border-[#374151] dark:bg-[#111827]">
                <div class="mb-6 flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                        <svg class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                </div>
                <h1 class="mb-2 text-center text-xl font-bold text-[#0F4C81] dark:text-[#93C5FD]">لا يمكنك الوصول لهذه الصفحة</h1>
                <p class="mb-4 text-center text-sm leading-relaxed text-slate-600 dark:text-[#9CA3AF]">
                    ليس لديك الصلاحية المطلوبة (مثلاً: منطقة المنصّة مخصّصة لحساب <span class="font-semibold text-slate-800 dark:text-slate-200">المدير العام</span> فقط).
                </p>
                @if ($message)
                    <p class="mb-6 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-center text-xs text-slate-500 dark:border-[#374151] dark:bg-[#1F2937] dark:text-slate-400">
                        {{ $message }}
                    </p>
                @endif
                <div class="flex flex-col gap-3 sm:flex-row-reverse sm:justify-center">
                    <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white no-underline shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
                        الانتقال للوحة التحكم
                    </a>
                    <button type="button" onclick="history.length > 1 ? history.back() : (window.location.href = @js($homeUrl))" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">
                        الرجوع للخلف
                    </button>
                </div>
                @if ($profileUrl)
                    <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
                        أو
                        <a href="{{ $profileUrl }}" class="font-semibold text-[#1F7A8C] no-underline hover:underline dark:text-[#5EEAD4]">الملف الشخصي</a>
                    </p>
                @endif
            </div>
            <p class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">403 — مرفوض</p>
        </div>
    </body>
</html>
