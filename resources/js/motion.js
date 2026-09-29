/* ============================================================
   ALFATIH//FUTURE — motion hub (single coherent system)
   Vanilla JS + IntersectionObserver + rAF. No animation library.
   - Progressive enhancement: content is readable without JS
     (initial-hidden styles apply only under `html.js`).
   - One IntersectionObserver for reveals/staggers/counters/journeys.
   - One rAF-throttled scroll listener for progress + parallax.
   - Pointer effects run only on fine pointers + no reduced motion.
   ============================================================ */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(pointer: fine)');
    const calmMotion = () => reducedMotion.matches;

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    /* ---------------- reveal / stagger / journey hub ----------------
       NOTE: legacy `.reveal` + `[data-counter]` keep their own initializer
       in app.interactions.js; this hub only drives the `data-*` system. */
    function initObserverHub() {
        const targets = document.querySelectorAll(
            '[data-reveal]:not(.is-in), [data-stagger]:not(.is-in), [data-media]:not(.is-in), [data-mask-group]:not(.is-in), [data-journey]:not(.is-active)'
        );
        if (!targets.length) return;

        if (calmMotion() || !('IntersectionObserver' in window)) {
            // Reduced motion / no IO: final state immediately.
            targets.forEach((el) => el.classList.add('is-in', 'is-active'));
            document.querySelectorAll('[data-stagger-item]:not(.is-in)').forEach((el) => el.classList.add('is-in'));
            return;
        }

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    io.unobserve(el);
                    el.classList.add('is-in', 'is-active');
                    if (el.hasAttribute('data-journey')) fillJourney(el);
                });
            },
            { rootMargin: '0px 0px -12% 0px', threshold: 0.12 }
        );
        targets.forEach((el) => io.observe(el));
    }

    /* ---------------- journey fill (PPDB steps, build story, timeline) ---------------- */
    function fillJourney(el) {
        const fill = el.querySelector('[data-journey-fill]');
        const steps = el.querySelectorAll('[data-journey-step]');
        if (!fill || !steps.length) return;
        const horizontal = (el.getAttribute('data-journey') || 'y').toLowerCase() === 'x';
        const axis = horizontal ? 'scaleX' : 'scaleY';
        if (calmMotion()) {
            fill.style.transform = `${axis}(1)`;
            steps.forEach((s) => s.classList.add('is-on'));
            return;
        }
        const total = steps.length;
        const active = el.getAttribute('data-journey-active');
        const activeCount = active !== null ? Math.min(parseInt(active, 10) || 0, total) : total;
        // Animate fill to the active step, then light each step in sequence.
        requestAnimationFrame(() => {
            fill.style.transform = `${axis}(${activeCount / total})`;
        });
        steps.forEach((step, i) => {
            window.setTimeout(() => step.classList.add('is-on'), 250 + i * 160);
        });
    }

    /* ---------------- single rAF scroll: progress bars + parallax ---------------- */
    function initScrollLoop() {
        const progressBars = Array.from(document.querySelectorAll('[data-scroll-progress]'));
        const readingBars = Array.from(document.querySelectorAll('[data-reading-progress]'));
        const parallaxEls = calmMotion()
            ? []
            : Array.from(document.querySelectorAll('[data-parallax]'));
        if (!progressBars.length && !readingBars.length && !parallaxEls.length) return;

        let ticking = false;
        const update = () => {
            ticking = false;
            const max = document.documentElement.scrollHeight - window.innerHeight;
            const ratio = max > 0 ? Math.min(Math.max(window.scrollY / max, 0), 1) : 0;
            progressBars.forEach((bar) => {
                bar.style.transform = `scaleX(${ratio})`;
            });
            readingBars.forEach((bar) => {
                const article = document.querySelector('[data-reading-article]');
                let r = ratio;
                if (article) {
                    const rect = article.getBoundingClientRect();
                    const total = rect.height - window.innerHeight * 0.4;
                    r = total > 0 ? Math.min(Math.max((window.innerHeight * 0.3 - rect.top) / total, 0), 1) : 1;
                }
                bar.style.transform = `scaleX(${r})`;
            });
            if (parallaxEls.length && !calmMotion()) {
                const vh = window.innerHeight;
                parallaxEls.forEach((el) => {
                    const rect = el.getBoundingClientRect();
                    if (rect.bottom < -80 || rect.top > vh + 80) return;
                    const speed = parseFloat(el.getAttribute('data-parallax') || '0.08');
                    const shift = (rect.top + rect.height / 2 - vh / 2) * -speed;
                    el.style.transform = `translate3d(0, ${shift.toFixed(1)}px, 0)`;
                });
            }
        };
        const requestUpdate = () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        };
        document.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        update();
    }

    /* ---------------- navbar hide-on-scroll (subtle) ---------------- */
    function initNavbarHide() {
        const header = document.querySelector('[data-navbar]');
        if (!header || calmMotion()) return;
        let lastY = window.scrollY;
        let ticking = false;
        const update = () => {
            ticking = false;
            const y = window.scrollY;
            const scrollingDown = y > lastY && y > 320;
            header.classList.toggle('navbar-hidden', scrollingDown);
            lastY = y;
        };
        document.addEventListener(
            'scroll',
            () => {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(update);
                }
            },
            { passive: true }
        );
    }

    /* ---------------- depth scenes: layered pointer parallax ----------------
       Container [data-depth-scene]; children [data-depth="0.02..0.12"] drift
       at different speeds. Fine pointers only, calm-motion safe. */
    function initDepthScenes() {
        if (!finePointer.matches || calmMotion()) return;
        document.querySelectorAll('[data-depth-scene]').forEach((scene) => {
            const layers = Array.from(scene.querySelectorAll('[data-depth]'));
            if (!layers.length) return;
            let frame = null;
            let tx = 0;
            let ty = 0;
            scene.addEventListener('pointermove', (e) => {
                const rect = scene.getBoundingClientRect();
                tx = (e.clientX - rect.left) / rect.width - 0.5;
                ty = (e.clientY - rect.top) / rect.height - 0.5;
                if (frame) return;
                frame = requestAnimationFrame(() => {
                    frame = null;
                    layers.forEach((layer) => {
                        const depth = parseFloat(layer.getAttribute('data-depth') || '0.05');
                        const x = (-tx * depth * 220).toFixed(1);
                        const y = (-ty * depth * 220).toFixed(1);
                        layer.style.transform = `translate3d(${x}px, ${y}px, 0)`;
                    });
                });
            });
            scene.addEventListener('pointerleave', () => {
                if (frame) cancelAnimationFrame(frame);
                frame = null;
                tx = 0;
                ty = 0;
                layers.forEach((layer) => {
                    layer.style.transform = '';
                });
            });
        });
    }

    /* ---------------- pointer: tilt + spotlight + magnetic ---------------- */
    function initPointerFX() {
        if (!finePointer.matches || calmMotion()) return;
        document.querySelectorAll('[data-tilt]').forEach((card) => {
            const max = parseFloat(card.getAttribute('data-tilt') || '6');
            let frame = null;
            card.addEventListener('pointermove', (e) => {
                if (frame) return;
                frame = requestAnimationFrame(() => {
                    frame = null;
                    const rect = card.getBoundingClientRect();
                    const px = (e.clientX - rect.left) / rect.width - 0.5;
                    const py = (e.clientY - rect.top) / rect.height - 0.5;
                    card.style.transform = `perspective(900px) rotateX(${(-py * max).toFixed(2)}deg) rotateY(${(px * max).toFixed(2)}deg) translateY(-3px)`;
                });
            });
            card.addEventListener('pointerleave', () => {
                if (frame) cancelAnimationFrame(frame);
                frame = null;
                card.style.transform = '';
            });
        });
        document.querySelectorAll('[data-spotlight]').forEach((el) => {
            el.addEventListener('pointermove', (e) => {
                const rect = el.getBoundingClientRect();
                el.style.setProperty('--spot-x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
                el.style.setProperty('--spot-y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
            });
        });
        document.querySelectorAll('[data-magnetic]').forEach((btn) => {
            let frame = null;
            btn.addEventListener('pointermove', (e) => {
                if (frame) return;
                frame = requestAnimationFrame(() => {
                    frame = null;
                    const rect = btn.getBoundingClientRect();
                    const dx = (e.clientX - (rect.left + rect.width / 2)) * 0.12;
                    const dy = (e.clientY - (rect.top + rect.height / 2)) * 0.18;
                    btn.style.transform = `translate(${dx.toFixed(1)}px, ${dy.toFixed(1)}px)`;
                });
            });
            btn.addEventListener('pointerleave', () => {
                if (frame) cancelAnimationFrame(frame);
                frame = null;
                btn.style.transform = '';
            });
        });
    }

    /* ---------------- hero enter sequence ---------------- */
    function initHeroEnter() {
        const hero = document.querySelector('[data-hero]');
        if (!hero) return;
        const items = hero.querySelectorAll('[data-hero-item]');
        if (calmMotion()) {
            hero.classList.add('is-in');
            items.forEach((el) => el.classList.add('is-in'));
            return;
        }
        // Next frame so initial paint carries the pre-state (no FOUC of final state).
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                hero.classList.add('is-in');
                items.forEach((el) => el.classList.add('is-in'));
            });
        });
    }

    /* ---------------- mobile drawer stagger ---------------- */
    function initDrawerStagger() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const menu = document.querySelector('[data-nav-menu]');
        if (!toggle || !menu) return;
        // Hidden pre-state applies only once this initializer has run.
        menu.classList.add('drawer-ready');
        toggle.addEventListener('click', () => {
            // Defer past the existing visibility toggle (import-order independent).
            requestAnimationFrame(() => {
                const items = menu.querySelectorAll(':scope > div > *');
                if (menu.classList.contains('hidden')) {
                    // Reset so the stagger replays on next open.
                    items.forEach((item) => item.classList.remove('drawer-in'));
                    return;
                }
                items.forEach((item, i) => {
                    item.classList.add('drawer-in');
                    if (calmMotion()) {
                        item.style.transitionDelay = '0ms';
                        return;
                    }
                    item.style.transitionDelay = `${Math.min(i * 28, 220)}ms`;
                    void item.offsetWidth;
                });
            });
        });
    }

    function safe(fn) {
        try {
            fn();
        } catch (err) {
            // Motion must never break the page.
            if (window.console && console.error) console.error('[motion]', err);
        }
    }

    // Auto-index stagger children so CMS loops need no manual counters.
    function indexStaggerGroups() {
        document.querySelectorAll('[data-stagger]').forEach((group) => {
            group.querySelectorAll(':scope [data-stagger-item]').forEach((item, i) => {
                if (!item.style.getPropertyValue('--stagger-i')) {
                    item.style.setProperty('--stagger-i', String(i));
                }
            });
        });
    }

    // Auto-sequence mask lines inside a group (90ms steps) unless authored.
    function indexMaskGroups() {
        document.querySelectorAll('[data-mask-group]').forEach((group) => {
            group.querySelectorAll(':scope [data-mask-line]').forEach((line, i) => {
                if (!line.style.getPropertyValue('--reveal-delay')) {
                    line.style.setProperty('--reveal-delay', `${i * 90}ms`);
                }
            });
        });
    }

    // One-shot page enter: instant when reduced motion is on.
    function initPageEnter() {
        const body = document.body;
        if (!body || !body.hasAttribute('data-page-enter')) return;
        if (calmMotion()) {
            body.classList.add('is-in');
            return;
        }
        requestAnimationFrame(() => {
            requestAnimationFrame(() => body.classList.add('is-in'));
        });
    }

    onReady(() => {
        safe(indexStaggerGroups);
        safe(indexMaskGroups);
        safe(initPageEnter);
        safe(initHeroEnter);
        safe(initObserverHub);
        safe(initScrollLoop);
        safe(initNavbarHide);
        safe(initPointerFX);
        safe(initDepthScenes);
        safe(initDrawerStagger);
        // Re-scan after async DOM swaps — cheap guard.
        document.addEventListener('alfatih:motion-scan', () => safe(initObserverHub));
    });
})();
