@php
    $exportCssHref = null;
    $manifestPath = public_path('build/manifest.json');
    if (is_file($manifestPath)) {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $cssEntry = $manifest['resources/css/app.css']['file'] ?? null;
        if (is_string($cssEntry) && $cssEntry !== '') {
            $exportCssHref = asset('build/'.$cssEntry);
        }
    }
@endphp
@if ($exportCssHref)
    <link rel="stylesheet" href="{{ $exportCssHref }}">
@endif
