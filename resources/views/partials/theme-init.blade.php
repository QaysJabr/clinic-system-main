{{-- Runs before paint to avoid light flash when dark is stored / system prefers dark --}}
<script @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
(function () {
    try {
        var k = 'clinic-theme';
        var s = localStorage.getItem(k);
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (s === 'dark' || (s !== 'light' && s !== 'dark' && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    } catch (e) {}
})();
</script>
