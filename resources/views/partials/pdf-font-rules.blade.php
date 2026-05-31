{{-- DomPDF: Noto Sans Arabic (logical RTL) or DejaVu + ar-php shaping fallback --}}
@php
    $pdfRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $notoPath = public_path('fonts/NotoSansArabic-Regular.ttf');
    $hasNoto = is_file($notoPath);
    $notoFileUrl = $hasNoto
        ? 'file:///'.str_replace('\\', '/', $notoPath)
        : null;
    $pdfFontFamily = $hasNoto
        ? "'Noto Sans Arabic', 'DejaVu Sans', sans-serif"
        : "'DejaVu Sans', sans-serif";
@endphp
        @if($hasNoto && $notoFileUrl)
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $notoFileUrl }}') format('truetype');
        }
        @endif
        body, table, thead, tbody, tr, th, td, div, p, h1, h2, h3, dt, dd, strong, span {
            font-family: {!! $pdfFontFamily !!};
        }
        html, body {
            direction: {{ $pdfRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $pdfRtl ? 'right' : 'left' }};
            unicode-bidi: embed;
        }
