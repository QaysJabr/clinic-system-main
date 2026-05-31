<script @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
    function tv(k) {
        try {
            var m = window.AppI18n && window.AppI18n.messages;
            return (m && m[k]) ? String(m[k]) : '';
        } catch (e) { return ''; }
    }
    const visitInputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6]';
    let procIdx = document.querySelectorAll('#proc-rows .proc-row').length;
    let rxIdx = document.querySelectorAll('#rx-rows .rx-row').length;
    document.getElementById('add-proc')?.addEventListener('click', function() {
        const wrap = document.getElementById('proc-rows');
        const div = document.createElement('div');
        div.className = 'proc-row grid grid-cols-1 gap-3 md:grid-cols-2';
        div.innerHTML =
            '<input type="text" name="procedure_rows[' + procIdx + '][name]" placeholder="' + tv('visitJsProcedureName').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="' + visitInputClass + '">' +
            '<div class="flex gap-2">' +
            '<input type="text" name="procedure_rows[' + procIdx + '][notes]" placeholder="' + tv('visitJsProcedureNotes').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="flex-1 ' + visitInputClass + '">' +
            '<button type="button" class="remove-proc shrink-0 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-red-700">' + tv('visitJsRemove').replace(/</g, '&lt;') + '</button>' +
            '</div>';
        wrap.appendChild(div);
        procIdx++;
    });
    document.getElementById('add-rx')?.addEventListener('click', function() {
        const wrap = document.getElementById('rx-rows');
        const div = document.createElement('div');
        div.className = 'rx-row grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3';
        div.innerHTML =
            '<input type="text" name="rx_rows[' + rxIdx + '][medication_name]" placeholder="' + tv('visitJsMedication').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="' + visitInputClass + '">' +
            '<input type="text" name="rx_rows[' + rxIdx + '][dosage]" placeholder="' + tv('visitJsDosage').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="' + visitInputClass + '">' +
            '<input type="text" name="rx_rows[' + rxIdx + '][frequency]" placeholder="' + tv('visitJsFrequency').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="' + visitInputClass + '">' +
            '<input type="text" name="rx_rows[' + rxIdx + '][duration]" placeholder="' + tv('visitJsDuration').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="' + visitInputClass + '">' +
            '<input type="text" name="rx_rows[' + rxIdx + '][notes]" placeholder="' + tv('visitJsRxNotes').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" class="md:col-span-2 ' + visitInputClass + '">' +
            '<div class="md:col-span-3 flex justify-end"><button type="button" class="remove-rx rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-red-700">' + tv('visitJsRemoveRow').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;') + '</button></div>';
        wrap.appendChild(div);
        rxIdx++;
    });
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-proc')) {
            e.target.closest('.proc-row')?.remove();
        }
        if (e.target.classList.contains('remove-rx')) {
            e.target.closest('.rx-row')?.remove();
        }
    });
</script>
