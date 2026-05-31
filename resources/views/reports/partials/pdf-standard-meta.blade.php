@if(! empty($reportMeta ?? null))
    <div class="doc-meta">
        <p><strong>@pdfStr(__('common.period')):</strong> @pdfStr($reportMeta['date_range_label'] ?? __('common.em_dash'))</p>
        @if(! empty($reportMeta['filter_lines']))
            @foreach($reportMeta['filter_lines'] as $line)
                @if(filled($line))
                    <p>@pdfStr($line)</p>
                @endif
            @endforeach
        @endif
    </div>
@endif
