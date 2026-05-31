<x-guest-layout>
    <div class="mb-8">
        <h1 class="auth-lp-heading text-xl font-bold tracking-tight text-slate-900 dark:text-white">تأكيد البريد</h1>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">افتح الرابط المرسل إلى بريدك، أو اطلب رابطاً جديداً.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/35 dark:text-emerald-200">
            تم إرسال رابط تحقق جديد إلى بريدك.
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:flex-1" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-auth-submit-button class="w-full sm:w-auto" loading-text="جارٍ الإرسال...">
                إعادة إرسال الرابط
            </x-auth-submit-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 sm:w-auto">
                تسجيل الخروج
            </button>
        </form>
    </div>
</x-guest-layout>
