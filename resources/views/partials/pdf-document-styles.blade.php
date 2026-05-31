{{-- Document styles for print/PDF export. DomPDF adds font rules via $forDompdf. --}}
        @php
            $forDompdf = $forDompdf ?? false;
            $docRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
        @endphp
        @if($forDompdf)
            @include('partials.pdf-font-rules')
        @else
        html, body {
            direction: {{ $docRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $docRtl ? 'right' : 'left' }};
        }
        @endif
        * { box-sizing: border-box; }
        body {
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 24px;
            background: #fff;
        }
        h1, h2, p { margin: 0; }
        .doc-brand {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .doc-tag {
            font-size: 10px;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
        }
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #0F4C81;
            margin-top: 6px;
        }
        .doc-sub {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }
        .doc-logo {
            max-height: 56px;
            max-width: 140px;
        }
        .doc-stats {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 16px;
        }
        .doc-stat {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            vertical-align: top;
            width: 33%;
        }
        .doc-stat--slate { background: #f8fafc; }
        .doc-stat--teal { border-color: #99f6e4; background: #f0fdfa; }
        .doc-stat--rose { border-color: #fecdd3; background: #fff1f2; }
        .doc-stat-label { font-size: 10px; color: #475569; margin: 0; }
        .doc-stat-value { font-size: 15px; font-weight: bold; margin: 6px 0 0; }
        .doc-section {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin: 14px 0 8px;
        }
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 12px;
        }
        .doc-table th, .doc-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: {{ $docRtl ? 'right' : 'left' }};
        }
        .doc-table th {
            background: #f8fafc;
            font-weight: bold;
            color: #475569;
        }
        .doc-debit { color: #be123c; }
        .doc-credit { color: #047857; }
        .doc-meta {
            margin: 0 0 14px;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        .doc-meta p { font-size: 10px; color: #374151; margin: 0; }
        .doc-meta p + p { margin-top: 4px; color: #6b7280; font-size: 9px; }
        .doc-panel {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
        .doc-panel-title {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 10px;
            color: #111827;
        }
        .doc-kv {
            display: table;
            width: 100%;
            font-size: 11px;
            padding: 4px 0;
        }
        .doc-kv span {
            display: table-cell;
            color: #6b7280;
            width: 42%;
        }
        .doc-kv strong {
            display: table-cell;
            font-weight: 600;
            color: #111827;
        }
        .doc-cur { color: #6b7280; font-size: 10px; }
        .doc-totals {
            border: 1px solid rgba(15, 76, 129, 0.25);
            background: #F7F9FC;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 16px;
        }
        .doc-totals-row {
            overflow: hidden;
            padding: 6px 0;
            font-size: 12px;
        }
        .doc-totals-row span { float: right; color: #4b5563; clear: right; }
        .doc-totals-row strong { float: left; }
        .doc-totals-row--final {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 6px;
        }
        .doc-totals-big { font-size: 15px; color: #0F4C81; }
        .doc-notes {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            margin-top: 16px;
        }
        .doc-notes--dashed {
            border-style: dashed;
            background: #f9fafb;
        }
        .doc-notes h2 { font-size: 12px; margin: 0 0 6px; color: #111827; }
        .doc-notes p {
            font-size: 11px;
            line-height: 1.6;
            color: #374151;
            white-space: pre-wrap;
            margin: 0;
        }
