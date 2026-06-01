<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <header class="mb-8">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">محلي فقط — لا يظهر في الإنتاج</p>
            <h1 class="mt-1 text-2xl font-extrabold">Testing Lab</h1>
            <p class="mt-2 text-sm text-slate-600">مختبر اختبار SaaS — يستخدم قاعدة <code class="rounded bg-white px-1">clinic_test_db</code> عبر PHPUnit</p>
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
        @endif

        <div class="mb-6 flex flex-wrap gap-3">
            @if ($canRun)
                <form method="post" action="{{ route('testing-lab.run') }}" onsubmit="return confirm('تشغيل suite Lab؟ قد يستغرق ~30 ثانية.');">
                    @csrf
                    <button type="submit" class="rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-[#0c3d66]">
                        تشغيل الاختبارات الآن
                    </button>
                </form>
            @endif
            <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">العودة للتطبيق</a>
        </div>

        @if ($latest)
            <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold">آخر تشغيل</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">النتيجة</dt>
                        <dd class="text-lg font-bold {{ ($latest['verdict'] ?? '') === 'PASS' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $latest['verdict'] ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">الدرجة</dt>
                        <dd class="text-lg font-bold">{{ $latest['score'] ?? 0 }}/100</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">اختبارات</dt>
                        <dd class="font-semibold">{{ $latest['tests'] ?? 0 }} (فشل: {{ ($latest['failures'] ?? 0) + ($latest['errors'] ?? 0) }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">المدة</dt>
                        <dd class="font-semibold">{{ $latest['duration_seconds'] ?? '—' }} ث</dd>
                    </div>
                </dl>
                @if (! empty($latest['run_id']))
                    <a href="{{ route('testing-lab.show', $latest['run_id']) }}" class="mt-4 inline-block text-sm font-bold text-[#0F4C81] hover:underline">عرض التقرير الكامل ←</a>
                @endif
            </section>
        @else
            <p class="mb-6 rounded-xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                لا يوجد تقرير بعد. اضغط «تشغيل الاختبارات» أو نفّذ: <code class="rounded bg-slate-100 px-1">php artisan lab:smoke</code>
            </p>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <h2 class="border-b border-slate-100 px-6 py-4 text-lg font-bold">سجل التشغيلات</h2>
            @if ($runs === [])
                <p class="px-6 py-8 text-sm text-slate-500">لا توجد تشغيلات محفوظة.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-start">التشغيل</th>
                            <th class="px-4 py-3">النتيجة</th>
                            <th class="px-4 py-3">الدرجة</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($runs as $run)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $run['id'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="{{ ($run['report']['verdict'] ?? '') === 'PASS' ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                                        {{ $run['report']['verdict'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $run['report']['score'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('testing-lab.show', $run['id']) }}" class="font-bold text-[#0F4C81] hover:underline">تفاصيل</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p class="mt-8 text-center text-xs text-slate-400">PHPUnit Lab: يوم عمل كامل · عزل عيادات · دفع مزدوج · أدوار طبيب/استقبال/محاسب</p>
    </div>
</body>
</html>
