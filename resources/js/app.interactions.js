(() => {
    const onReady = (fn) => {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    };

    onReady(() => {
        initThemeToggle();
        initDropdowns();
        initModals();
        initTabs();
        initAlertDismiss();
        initConfirmDialog();
        initToasts();
        initMobileNav();
        initAdminDrawer();
        initGallery();
        initLightbox();
        initDoubleSubmitGuard();
        initFormValidationFeedback();
    });

    /* ---------------- Theme toggle ---------------- */
    function initThemeToggle() {
        const toggles = document.querySelectorAll('[data-theme-toggle]');
        if (!toggles.length) return;

        const sunIcons = document.querySelectorAll('[data-theme-icon-sun]');
        const moonIcons = document.querySelectorAll('[data-theme-icon-moon]');

        const updateIcons = () => {
            const isDark = document.documentElement.classList.contains('dark');
            sunIcons.forEach((el) => el.classList.toggle('hidden', isDark));
            moonIcons.forEach((el) => el.classList.toggle('hidden', !isDark));
        };

        updateIcons();

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateIcons();
            });
        });
    }

    /* ---------------- Mobile nav ---------------- */
    function initMobileNav() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const menu = document.querySelector('[data-nav-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', () => {
            const isHidden = menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !isHidden);
            toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            toggle.setAttribute('aria-label', isHidden ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        });
    }

    /* ---------------- Admin drawer (mobile) ---------------- */
    function initAdminDrawer() {
        const drawer = document.querySelector('[data-admin-drawer]');
        const backdrop = document.querySelector('[data-admin-drawer-backdrop]');
        const toggle = document.querySelector('[data-admin-drawer-toggle]');
        if (!drawer || !backdrop || !toggle) return;

        const isOpen = () => !drawer.classList.contains('-translate-x-full');

        const open = () => {
            drawer.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Tutup menu navigasi');
            drawer.querySelector('a[href], button')?.focus();
        };

        const close = () => {
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
            toggle.focus();
        };

        toggle.addEventListener('click', () => (isOpen() ? close() : open()));
        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen()) close();
        });
    }

    /* ---------------- Dropdown ---------------- */
    function initDropdowns() {
        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-dropdown-toggle]');
            const menu = toggle?.closest('[data-dropdown]')?.querySelector('[data-dropdown-menu]');

            if (toggle) {
                const isOpen = menu && !menu.classList.contains('hidden');
                closeAllDropdowns();
                if (!isOpen) {
                    menu.classList.remove('hidden');
                }
                return;
            }

            if (e.target.closest('[data-dropdown-close]')) {
                closeAllDropdowns();
                return;
            }

            if (!e.target.closest('[data-dropdown]')) {
                closeAllDropdowns();
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m.classList.add('hidden'));
    }

    /* ---------------- Modal ---------------- */
    function initModals() {
        document.addEventListener('click', (e) => {
            const closeBtn = e.target.closest('[data-modal-close]');
            if (closeBtn) {
                closeModal(closeBtn.closest('[data-modal]'));
                return;
            }
            if (e.target.hasAttribute('data-modal-backdrop')) {
                closeModal(e.target.closest('[data-modal]'));
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal(document.querySelector('[data-modal]:not(.hidden)'));
            }
        });
    }

    window.openModal = (id) => {
        const modal = typeof id === 'string' ? document.getElementById(id) : id;
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        modal.querySelector('input, button, select, textarea, a[href]')?.focus();
    };

    window.closeModal = (id) => {
        const modal = typeof id === 'string' ? document.getElementById(id) : id;
        closeModal(modal);
    };

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        if (!document.querySelector('[data-modal]:not(.hidden)')) {
            document.body.style.overflow = '';
        }
    }

    /* ---------------- Tabs ---------------- */
    function initTabs() {
        document.querySelectorAll('[data-tabs]').forEach((tabsEl) => {
            const triggers = tabsEl.querySelectorAll('[data-tab-trigger]');
            const panels = tabsEl.querySelectorAll('[data-tab-panel]');

            const activate = (target) => {
                triggers.forEach((t) => t.setAttribute('data-active', 'false'));
                panels.forEach((p) => p.classList.add('hidden'));

                const activeTrigger = tabsEl.querySelector(`[data-tab-trigger][data-target="${target}"]`);
                if (activeTrigger) activeTrigger.setAttribute('data-active', 'true');

                const panel = tabsEl.querySelector(target);
                if (panel) panel.classList.remove('hidden');
            };

            triggers.forEach((trigger) => {
                trigger.addEventListener('click', () => activate(trigger.dataset.target));
            });

            if (triggers.length > 0) {
                const firstActive = tabsEl.querySelector('[data-tab-trigger][data-active="true"]');
                activate(firstActive ? firstActive.dataset.target : triggers[0].dataset.target);
            }
        });
    }

    /* ---------------- Alert dismiss ---------------- */
    function initAlertDismiss() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-alert-dismiss]');
            if (!btn) return;
            const target = btn.dataset.alertDismiss;
            const el = target ? document.querySelector(target) : btn.closest('[role="alert"]');
            el?.remove();
        });
    }

    /* ---------------- Confirm dialog ---------------- */
    function initConfirmDialog() {
        const dialog = document.querySelector('[data-confirm-dialog]');
        if (!dialog) {
            window.confirmDialog = (options = {}) => {
                if (typeof options.onConfirm === 'function' && window.confirm(options.message || 'Apakah Anda yakin?')) {
                    options.onConfirm();
                    return;
                }
                if (options.formAction && window.confirm(options.message || 'Apakah Anda yakin?')) {
                    submitConfirmForm(options);
                }
            };
            return;
        }

        const title = dialog.querySelector('#confirm-dialog-title');
        const message = dialog.querySelector('#confirm-dialog-message');
        const okBtn = dialog.querySelector('[data-confirm-ok]');
        const cancelBtn = dialog.querySelector('[data-confirm-cancel]');

        let onConfirm = null;

        const close = () => {
            dialog.classList.add('hidden');
            document.body.style.overflow = '';
        };

        const open = () => {
            dialog.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            cancelBtn.focus();
        };

        function submitConfirmForm(options) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = options.formAction;
            form.className = 'hidden';

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                form.appendChild(input('_token', token));
            }
            if (options.method && options.method.toLowerCase() !== 'post') {
                form.appendChild(input('_method', options.method));
            }

            document.body.appendChild(form);
            form.submit();
        }

        dialog.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-confirm-backdrop')) close();
        });

        cancelBtn.addEventListener('click', close);
        okBtn.addEventListener('click', () => {
            close();
            if (typeof onConfirm === 'function') onConfirm();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !dialog.classList.contains('hidden')) close();
        });

        window.confirmDialog = (options = {}) => {
            title.textContent = options.title || 'Apakah Anda yakin?';
            message.textContent = options.message || '';
            okBtn.textContent = options.confirmText || 'Ya, lanjutkan';
            cancelBtn.textContent = options.cancelText || 'Batal';
            okBtn.classList.toggle('bg-red-600', options.variant !== 'primary');

            if (options.formAction) {
                onConfirm = () => submitConfirmForm(options);
            } else {
                onConfirm = options.onConfirm || null;
            }

            open();
        };

        function input(name, value) {
            const el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            el.value = value;
            return el;
        }
    }

    /* ---------------- Gallery filter ---------------- */
    function initGallery() {
        const container = document.querySelector('[data-gallery-filters]');
        if (!container) return;

        const items = Array.from(document.querySelectorAll('[data-gallery-item]'));

        container.querySelectorAll('[data-gallery-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const filter = button.dataset.galleryFilter;

                container.querySelectorAll('[data-gallery-filter]').forEach((b) => {
                    b.setAttribute('data-active', String(b === button));
                });

                items.forEach((item) => {
                    const show = !filter || item.dataset.category === filter;
                    item.classList.toggle('hidden', !show);
                });
            });
        });
    }

    /* ---------------- Lightbox ---------------- */
    function initLightbox() {
        const lightbox = document.querySelector('[data-lightbox]');
        if (!lightbox) return;

        const imageContainer = lightbox.querySelector('[data-lightbox-image]');
        const caption = lightbox.querySelector('[data-lightbox-caption]');

        const close = () => {
            lightbox.classList.add('hidden');
            document.body.style.overflow = '';
            imageContainer.innerHTML = '';
        };

        lightbox.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-lightbox-backdrop')) close();
        });
        lightbox.querySelector('[data-lightbox-close]').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) close();
        });

        document.querySelectorAll('[data-gallery-item]').forEach((item) => {
            item.addEventListener('click', () => {
                const title = item.dataset.title;
                const src = item.dataset.src;

                imageContainer.innerHTML = src
                    ? `<img src="${src}" alt="${title}" class="max-h-[75vh] max-w-full object-contain" />`
                    : `<div class="flex aspect-video w-full max-w-3xl items-center justify-center bg-gradient-to-br from-primary-800 via-primary-900 to-accent-900 p-16">
                           <svg class="size-20 text-primary-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                           </svg>
                       </div>`;

                caption.textContent = title;
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });
    }

    /* ---------------- Toasts ---------------- */
    function initToasts() {
        const container = document.querySelector('[data-toast-container]');
        if (!container) return;

        const variants = {
            success: {
                bg: 'bg-white dark:bg-slate-800',
                border: 'border-emerald-200 dark:border-emerald-700/60',
                iconBg: 'bg-emerald-100 dark:bg-emerald-900/50',
                icon: 'text-emerald-600 dark:text-emerald-400',
                label: 'text-emerald-700 dark:text-emerald-300',
                bar: 'bg-emerald-500',
                path: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                title: 'Berhasil',
            },
            error: {
                bg: 'bg-white dark:bg-slate-800',
                border: 'border-red-200 dark:border-red-700/60',
                iconBg: 'bg-red-100 dark:bg-red-900/50',
                icon: 'text-red-600 dark:text-red-400',
                label: 'text-red-700 dark:text-red-300',
                bar: 'bg-red-500',
                path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
                title: 'Gagal',
            },
            warning: {
                bg: 'bg-white dark:bg-slate-800',
                border: 'border-amber-200 dark:border-amber-700/60',
                iconBg: 'bg-amber-100 dark:bg-amber-900/50',
                icon: 'text-amber-600 dark:text-amber-400',
                label: 'text-amber-700 dark:text-amber-300',
                bar: 'bg-amber-500',
                path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
                title: 'Peringatan',
            },
            info: {
                bg: 'bg-white dark:bg-slate-800',
                border: 'border-sky-200 dark:border-sky-700/60',
                iconBg: 'bg-sky-100 dark:bg-sky-900/50',
                icon: 'text-sky-600 dark:text-sky-400',
                label: 'text-sky-700 dark:text-sky-300',
                bar: 'bg-sky-500',
                path: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
                title: 'Info',
            },
        };

        window.toast = (message, type = 'success', duration = 5000) => {
            const v = variants[type] || variants.info;

            const toastEl = document.createElement('div');
            toastEl.className = `pointer-events-auto w-full rounded-xl border ${v.bg} ${v.border} shadow-xl shadow-black/5 ring-1 ring-black/5 dark:shadow-black/20 overflow-hidden`;
            toastEl.setAttribute('role', 'status');
            toastEl.style.animation = 'toast-slide-in 0.35s cubic-bezier(0.16,1,0.3,1)';

            toastEl.innerHTML = `
                <div class="flex items-start gap-3 p-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg ${v.iconBg}">
                        <svg class="size-5 ${v.icon}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${v.path}" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-sm font-semibold ${v.label}">${v.title}</p>
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300 leading-snug">${message}</p>
                    </div>
                    <button type="button" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-700 transition-colors" aria-label="Tutup">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 w-full bg-slate-100 dark:bg-slate-700">
                    <div class="h-full ${v.bar} rounded-full" style="animation: toast-progress ${duration}ms linear forwards;"></div>
                </div>
            `;

            toastEl.querySelector('button').addEventListener('click', () => dismissToast(toastEl));
            container.appendChild(toastEl);

            const timer = setTimeout(() => dismissToast(toastEl), duration);
            toastEl._timer = timer;
        };

        function dismissToast(el) {
            if (el._dismissed) return;
            el._dismissed = true;
            clearTimeout(el._timer);
            el.style.animation = 'toast-slide-out 0.3s cubic-bezier(0.16,1,0.3,1) forwards';
            setTimeout(() => el.remove(), 300);
        }

        // Flash session toasts
        const flashTypes = ['success', 'error', 'warning', 'info'];
        flashTypes.forEach((type) => {
            const meta = document.querySelector(`meta[name="flash-${type}"]`);
            if (meta && meta.content) {
                window.toast(meta.content, type);
            }
        });
    }

    function initDoubleSubmitGuard() {
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('button[type="submit"]');
                if (!btn) return;
                if (form.dataset.submitting === 'true') {
                    // prevent double submit
                    event.preventDefault();
                    return;
                }
                form.dataset.submitting = 'true';
                btn.disabled = true;
                const original = btn.textContent;
                btn.dataset.originalText = original;
                btn.innerHTML = '<svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg> Memproses...';
                setTimeout(() => {
                    form.dataset.submitting = 'false';
                    btn.disabled = false;
                    if (btn.dataset.originalText) btn.textContent = btn.dataset.originalText;
                }, 4000);
            });
        });
    }

    function initFormValidationFeedback() {
        document.querySelectorAll('input, select, textarea').forEach((el) => {
            el.addEventListener('invalid', () => {
                el.classList.add('border-red-300');
            });
        });
    }
})();
