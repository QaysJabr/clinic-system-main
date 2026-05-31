<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ __('saas.brochure_title') }}</title>
    <style>
        @include('partials.pdf-font-rules')
        body, table, th, td, p, h1, h2, h3, li { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 0; padding: 22px 26px; line-height: 1.45; }
        .header { border-bottom: 3px solid #0F4C81; padding-bottom: 12px; margin-bottom: 18px; }
        h1 { font-size: 22px; color: #0F4C81; margin: 0 0 4px; }
        .subtitle { font-size: 12px; color: #4b5563; margin: 0; }
        .meta { font-size: 10px; color: #6b7280; margin-top: 6px; }
        .intro { background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; }
        .intro p { margin: 0 0 6px; }
        .intro p:last-child { margin: 0; }
        table.plans { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.plans th { background: #0F4C81; color: #fff; padding: 8px 6px; font-size: 11px; text-align: center; }
        table.plans td { border: 1px solid #d1d5db; padding: 8px 6px; vertical-align: top; font-size: 10px; }
        table.plans td.plan-name { background: #f9fafb; font-weight: bold; font-size: 12px; color: #0F4C81; text-align: center; }
        .popular { background: #ecfdf5; color: #047857; font-size: 9px; font-weight: bold; display: block; margin-top: 4px; }
        .price { font-size: 14px; font-weight: bold; color: #111827; text-align: center; }
        .price-sub { font-size: 9px; color: #6b7280; text-align: center; display: block; }
        .save { font-size: 9px; color: #047857; font-weight: bold; text-align: center; }
        ul.features { margin: 6px 0 0; padding: 0 14px 0 0; }
        ul.features li { margin-bottom: 3px; }
        .compare { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 16px; }
        .compare th, .compare td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: center; }
        .compare th { background: #f3f4f6; }
        .compare td:first-child { text-align: right; font-weight: 600; background: #fafafa; }
        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #4b5563; }
        .footer strong { color: #0F4C81; }
        .cta { margin-top: 8px; font-size: 11px; }
        .check { color: #047857; font-weight: bold; }
        .dash { color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p class="subtitle">{{ __('saas.brochure_subtitle') }}</p>
        <p class="meta">{{ __('saas.brochure_printed', ['date' => $printedAt->format('d/m/Y')]) }}</p>
    </div>

    <div class="intro">
        <p><strong>{{ __('saas.brochure_intro_title') }}</strong></p>
        <p>{{ __('saas.brochure_intro_body') }}</p>
    </div>

    <table class="plans">
        <thead>
            <tr>
                @foreach ($cards as $card)
                    <th style="width: {{ 100 / max(count($cards), 1) }}%;">
                        {{ $card['name'] }}
                        @if ($card['is_popular'])
                            <span class="popular">{{ __('saas.pricing_popular_badge') }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($cards as $card)
                    <td class="plan-name">
                        <span class="price">{{ $card['monthly'] }}</span>
                        <span class="price-sub">{{ __('saas.pricing_per_month') }}</span>
                        <span class="price" style="margin-top:6px;">{{ $card['yearly'] }}</span>
                        <span class="price-sub">{{ __('saas.pricing_per_year') }}</span>
                        @if ($card['yearly_save'])
                            <span class="save">{{ __('saas.pricing_yearly_save', ['percent' => $card['yearly_save']]) }}</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($cards as $card)
                    <td>
                        <strong>{{ __('saas.brochure_limits') }}</strong><br>
                        {{ $card['patients'] }}<br>
                        {{ $card['users'] }}<br>
                        @if ($card['trial'])
                            {{ $card['trial'] }}
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($cards as $card)
                    <td>
                        <strong>{{ __('saas.brochure_features_title') }}</strong>
                        <ul class="features">
                            @foreach ($card['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <h2 style="font-size: 13px; color: #0F4C81; margin: 0 0 8px;">{{ __('saas.brochure_compare_title') }}</h2>
    <table class="compare">
        <thead>
            <tr>
                <th style="width: 28%;">{{ __('saas.brochure_compare_feature') }}</th>
                @foreach ($cards as $card)
                    <th>{{ $card['name'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($compareRows as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    @foreach ($cards as $card)
                        @php
                            $slug = $card['plan']->slug;
                            $val = $row[$slug] ?? '—';
                        @endphp
                        <td>
                            @if ($val === true)
                                <span class="check">✓</span>
                            @elseif ($val === false)
                                <span class="dash">—</span>
                            @else
                                {{ $val }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p class="cta"><strong>{{ __('saas.brochure_cta') }}</strong> {{ $pricingUrl }}</p>
        @if ($whatsapp !== '')
            <p>{{ __('saas.brochure_whatsapp') }}: {{ $whatsapp }}</p>
        @endif
        <p>{{ __('saas.brochure_footer_note') }}</p>
    </div>
</body>
</html>
