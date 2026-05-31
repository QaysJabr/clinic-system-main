/**
 * Landing page — scroll reveal & stat counters (welcome only).
 */
function initLandingReveal() {
    const nodes = document.querySelectorAll('.landing-reveal');
    if (!nodes.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        nodes.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    nodes.forEach((el, index) => {
        el.style.transitionDelay = `${Math.min(index % 6, 5) * 70}ms`;
        observer.observe(el);
    });
}

function animateCounter(el) {
    const target = parseInt(el.dataset.countTo ?? '0', 10);
    if (!Number.isFinite(target) || target <= 0) {
        return;
    }

    const suffix = el.dataset.countSuffix ?? '';
    const duration = 1400;
    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - (1 - progress) ** 3;
        const value = Math.round(target * eased);
        el.textContent = `${value.toLocaleString()}${suffix}`;
        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
}

function initLandingCounters() {
    const counters = document.querySelectorAll('[data-count-to]');
    if (!counters.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting || entry.target.dataset.counted === '1') {
                    return;
                }
                entry.target.dataset.counted = '1';
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.4 },
    );

    counters.forEach((el) => observer.observe(el));
}

function initLandingVideoModal() {
    const modal = document.getElementById('landing-video-modal');
    const modalIframe = document.getElementById('landing-video-modal-iframe');
    const inlineIframe = document.getElementById('landing-demo-iframe');
    const embedSrc =
        inlineIframe?.dataset?.embedSrc ||
        document.querySelector('[data-embed-src]')?.dataset?.embedSrc ||
        '';

    if (!modal || !modalIframe || !embedSrc) {
        return;
    }

    const open = () => {
        if (embedSrc) {
            const sep = embedSrc.includes('?') ? '&' : '?';
            modalIframe.src = `${embedSrc}${sep}autoplay=1`;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modalIframe.src = '';
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-landing-video-open]').forEach((btn) => {
        btn.addEventListener('click', open);
    });

    modal.querySelectorAll('[data-landing-video-close]').forEach((btn) => {
        btn.addEventListener('click', close);
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            close();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });
}

function initLandingPage() {
    if (!document.body.classList.contains('landing-page')) {
        return;
    }
    initLandingReveal();
    initLandingCounters();
    initLandingVideoModal();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLandingPage);
} else {
    initLandingPage();
}
