<x-guest-layout :shake-on-error="true">
    <div class="mb-8">
        <h1 class="auth-lp-heading text-xl font-bold tracking-tight text-slate-900 dark:text-white">تأكيد كلمة المرور</h1>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">مطلوب قبل المتابعة.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <x-auth-floating-field
            label="كلمة المرور"
            name="password"
            id="password"
            type="password"
            icon="lock"
            autocomplete="current-password"
            :required="true"
            :autofocus="true"
        />

        <div class="pt-1">
            <x-auth-submit-button loading-text="جارٍ التحقق...">
                متابعة
            </x-auth-submit-button>
        </div>
    </form>
</x-guest-layout>
