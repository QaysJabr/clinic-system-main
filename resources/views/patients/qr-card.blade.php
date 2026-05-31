<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('emr.qr_card') }} — {{ $patient->full_name }}</title>
    <style>
        body { font-family: 'Cairo', system-ui, sans-serif; margin: 0; padding: 2rem; display: flex; justify-content: center; }
        .card { border: 2px solid #0F4C81; border-radius: 16px; padding: 2rem; max-width: 360px; text-align: center; }
        h1 { font-size: 1.25rem; margin: 0 0 0.5rem; color: #0F4C81; }
        p { margin: 0.25rem 0; color: #374151; font-size: 0.9rem; }
        img { margin: 1rem auto; display: block; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="card">
        <h1>{{ $patient->full_name }}</h1>
        <p>{{ $patient->file_number }}</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&amp;data={{ urlencode($lookupUrl) }}" width="220" height="220" alt="QR">
        <p class="text-xs">{{ __('emr.qr_hint') }}</p>
    </div>
</body>
</html>
