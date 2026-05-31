<x-guest-layout :shake-on-error="true">
    <div class="mb-8">
        <h1 class="auth-lp-heading text-xl font-bold tracking-tight text-slate-900 dark:text-white">استعادة كلمة المرور</h1>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">سنرسل لك رابطاً لإعادة التعيين.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <x-auth-floating-field
            label="البريد الإلكتروني"
            name="email"
            id="email"
            type="email"
            icon="email"
            autocomplete="username"
            :required="true"
            :autofocus="true"
        />

        <div class="space-y-3 pt-1">
            <x-auth-submit-button loading-text="جارٍ الإرسال...">
                إرسال الرابط
            </x-auth-submit-button>
            <a class="block text-center text-sm font-medium text-blue-600 no-underline transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" href="{{ route('login') }}">
                العودة لتسجيل الدخول
            </a>
        </div>
    </form>
</x-guest-layout>
