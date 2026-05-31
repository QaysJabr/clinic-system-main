<x-guest-layout :shake-on-error="true">
    <div class="mb-8">
        <h1 class="auth-lp-heading text-xl font-bold tracking-tight text-slate-900 dark:text-white">كلمة مرور جديدة</h1>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">اختر كلمة مرور قوية.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth-floating-field
            label="البريد الإلكتروني"
            name="email"
            id="email"
            type="email"
            icon="email"
            autocomplete="username"
            :required="true"
            :autofocus="true"
            :value="$request->email"
        />

        <x-auth-floating-field
            label="كلمة المرور الجديدة"
            name="password"
            id="password"
            type="password"
            icon="lock"
            autocomplete="new-password"
            :required="true"
        />

        <x-auth-floating-field
            label="تأكيد كلمة المرور"
            name="password_confirmation"
            id="password_confirmation"
            type="password"
            icon="lock"
            autocomplete="new-password"
            :required="true"
        />

        <div class="pt-3">
            <x-auth-submit-button loading-text="جارٍ الحفظ...">
                حفظ
            </x-auth-submit-button>
        </div>
    </form>
</x-guest-layout>
