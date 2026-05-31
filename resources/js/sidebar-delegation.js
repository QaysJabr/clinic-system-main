/**
 * Single delegated listeners so sidebar mobile menu works after SPA/locale DOM swaps
 * (no duplicate listeners when fragments replace).
 */
export function initSidebarMobileDelegation() {
    document.addEventListener(
        'click',
        (e) => {
            const t = e.target;
            if (!(t instanceof Element)) return;

            const mobileBtn = t.closest('#mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            if (mobileBtn && sidebar) {
                e.stopPropagation();
                sidebar.classList.toggle('active');
                return;
            }

            const mb = document.getElementById('mobile-menu-btn');
            if (
                sidebar &&
                mb &&
                !sidebar.contains(t) &&
                !mb.contains(t)
            ) {
                sidebar.classList.remove('active');
            }

            if (t.closest('#sidebar a[href]') && sidebar) {
                sidebar.classList.remove('active');
            }
        },
        false,
    );
}
