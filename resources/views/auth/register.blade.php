<x-guest-layout :shake-on-error="true">
    <header class="mb-7 text-end sm:mb-8">
        <h1 class="auth-lp-heading text-xl font-semibold text-slate-900 dark:text-white">إنشاء حساب جديد</h1>
        <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">أدخل بياناتك للمتابعة</p>
    </header>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <x-auth-floating-field
            label="الاسم الكامل"
            name="name"
            id="name"
            type="text"
            icon="user"
            autocomplete="name"
            placeholder="الاسم كما سيظهر في النظام"
            :required="true"
            :autofocus="true"
        />

        <x-auth-floating-field
            label="البريد الإلكتروني"
            name="email"
            id="email"
            type="email"
            icon="email"
            autocomplete="username"
            placeholder="example@clinic.com"
            :required="true"
        />

        <x-auth-floating-field
            label="كلمة المرور"
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

        <div class="pt-1">
            <x-auth-submit-button loading-text="جارٍ إنشاء الحساب...">
                إنشاء الحساب
            </x-auth-submit-button>
        </div>
    </form>

    <p class="mt-8 border-t border-slate-200 pt-5 text-center text-sm leading-relaxed text-slate-600 dark:border-slate-500 dark:text-slate-300">
        لديك حساب؟
        <a class="ms-1 font-semibold text-teal-800 underline-offset-4 hover:underline dark:text-teal-300" href="{{ route('login') }}">تسجيل الدخول</a>
    </p>
</x-guest-layout>
