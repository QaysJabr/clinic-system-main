@php
    $user = auth()->user();
    $homeUrl = $user
        ? ($user->hasRole('super_admin')
            ? route('platform.dashboard')
            : ($user->can('view dashboard') ? route('dashboard') : route('profile.edit')))
        : route('login');
    $message = null;
    if (config('app.debug') && isset($exception) && $exception->getMessage()) {
        $message = $exception->getMessage();
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>500 — {{ config('app.name', 'Clinic System') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-[#F8F9FA] text-[#1F2937] dark:bg-[#0F172A] dark:text-[#F3F4F6]">
        <div class="flex min-h-full flex-col items-center justify-center px-4 py-12">
            <div class="w-full max-w-md rounded-2xl border border-slate-200/90 bg-white p-8 shadow-lg dark:border-[#374151] dark:bg-[#111827]">
                <div class="mb-6 flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-200">
                        <svg class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <h1 class="mb-2 text-center text-xl font-bold text-[#0F4C81] dark:text-[#93C5FD]">حدث خطأ في الخادم</h1>
                <p class="mb-4 text-center text-sm leading-relaxed text-slate-600 dark:text-[#9CA3AF]">
                    تعذّر تحميل الصفحة. جرّب التحديث أو العودة للوحة التحكم. إذا استمر الخطأ، تواصل مع الدعم.
                </p>
                @if ($message)
                    <p class="mb-6 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-center text-xs text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300">
                        {{ $message }}
                    </p>
                @endif
                <div class="flex flex-col gap-3 sm:flex-row-reverse sm:justify-center">
                    <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white no-underline shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
                        الانتقال للوحة التحكم
                    </a>
                    <button type="button" onclick="location.reload()" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">
                        إعادة المحاولة
                    </button>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">500 — خطأ خادم</p>
        </div>
    </body>
</html>
