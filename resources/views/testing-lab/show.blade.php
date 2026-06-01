<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <a href="{{ route('testing-lab.index') }}" class="text-sm font-bold text-[#0F4C81] hover:underline">← العودة للوحة Lab</a>
        <h1 class="mt-4 text-xl font-extrabold font-mono">{{ $runId }}</h1>

        @if ($report)
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-2xl font-bold {{ ($report['verdict'] ?? '') === 'PASS' ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $report['verdict'] ?? '—' }} — {{ $report['score'] ?? 0 }}/100
                </p>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $report['tests'] ?? 0 }} اختبار · {{ $report['duration_seconds'] ?? '—' }} ثانية
                </p>
            </div>
        @endif

        @if ($summaryMd !== '')
            <article class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <pre class="max-h-[70vh] overflow-auto p-6 text-xs leading-relaxed whitespace-pre-wrap font-mono text-slate-800">{{ $summaryMd }}</pre>
            </article>
        @endif
    </div>
</body>
</html>
