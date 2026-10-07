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
        initPasswordToggles();
        initDropdowns();
        initModals();
        initUserEditor();
        initTabs();
        initAlertDismiss();
        initConfirmDialog();
        initToasts();
        initMobileNav();
        initAdminDrawer();
        initGallery();
        initLightbox();
        initPeriodOpenForms();
        initDoubleSubmitGuard();
        initFormValidationFeedback();
        initSlotSelection();
        initReveal();
        initCounters();
        initWordSwap();
        initNavbarScroll();
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

    /* ---------------- Password visibility ---------------- */
    function initPasswordToggles() {
        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-password-toggle]');
            if (!toggle) return;

            const inputId = toggle.getAttribute('aria-controls');
            const input = inputId ? document.getElementById(inputId) : null;
            if (!input || !['password', 'text'].includes(input.type)) return;

            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(willShow));
            toggle.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            toggle.querySelector('[data-password-icon-show]')?.classList.toggle('hidden', willShow);
            toggle.querySelector('[data-password-icon-hide]')?.classList.toggle('hidden', !willShow);

            input.focus({ preventScroll: true });
            const cursor = input.value.length;
            input.setSelectionRange?.(cursor, cursor);
        });
    }

    /* ---------------- Mobile nav ---------------- */
    function initMobileNav() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const menu = document.querySelector('[data-nav-menu]');
        if (!toggle || !menu) return;

        const isOpen = () => !menu.classList.contains('hidden');
        const open = () => {
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Tutup menu navigasi');
            document.body.style.overflow = 'hidden';
            menu.querySelector('a[href], button')?.focus();
        };
        const close = (restoreFocus = true) => {
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
            document.body.style.overflow = '';
            if (restoreFocus) toggle.focus();
        };
        toggle.addEventListener('click', () => (isOpen() ? close() : open()));
        menu.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', () => close(false)));
        document.addEventListener('keydown', (event) => {
            if (!isOpen()) return;
            if (event.key === 'Escape') return close();
            if (event.key !== 'Tab') return;
            const controls = Array.from(menu.querySelectorAll('a[href], button:not([disabled])'));
            if (!controls.length) return;
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && isOpen()) close(false);
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
        const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

        document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
            const menu = dropdown.querySelector('[data-dropdown-menu]');
            const trigger = dropdown.querySelector('[data-dropdown-toggle] button, [data-dropdown-toggle] a');
            if (!menu || !trigger) return;

            let closeTimer = null;
            const open = () => {
                window.clearTimeout(closeTimer);
                closeAllDropdowns(dropdown);
                menu.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
            };
            const scheduleClose = () => {
                window.clearTimeout(closeTimer);
                closeTimer = window.setTimeout(() => closeDropdown(dropdown), 140);
            };

            dropdown.addEventListener('pointerenter', () => {
                if (finePointer.matches) open();
            });
            dropdown.addEventListener('pointerleave', () => {
                if (finePointer.matches) scheduleClose();
            });
            dropdown.addEventListener('focusin', open);
            dropdown.addEventListener('focusout', (event) => {
                if (!dropdown.contains(event.relatedTarget)) scheduleClose();
            });
        });

        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-dropdown-toggle]');
            const menu = toggle?.closest('[data-dropdown]')?.querySelector('[data-dropdown-menu]');

            if (toggle) {
                const isOpen = menu && !menu.classList.contains('hidden');
                closeAllDropdowns();
                if (!isOpen) {
                    menu.classList.remove('hidden');
                    toggle.querySelector('button, a')?.setAttribute('aria-expanded', 'true');
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

    function closeDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.querySelector('[data-dropdown-menu]')?.classList.add('hidden');
        dropdown.querySelector('[data-dropdown-toggle] button, [data-dropdown-toggle] a')?.setAttribute('aria-expanded', 'false');
    }

    function closeAllDropdowns(except = null) {
        document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
            if (dropdown !== except) closeDropdown(dropdown);
        });
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
                // Jika popover primitif (kalender/listbox) sedang terbuka,
                // biarkan ia yang menutup dirinya sendiri — modal tetap buka.
                if (document.querySelector('[data-ctl-popover-open]')) return;
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

    /* ---------------- Admin user editor ---------------- */
    function initUserEditor() {
        const form = document.getElementById('edit-user-form');
        const deleteForm = document.getElementById('delete-user-form');
        if (!form) return;

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-edit-user]');
            if (!button) return;

            const name = document.getElementById('edit-user-name');
            const email = document.getElementById('edit-user-email');
            const password = document.getElementById('edit-user-password');
            const role = document.getElementById('edit-user-role');
            const roleField = document.getElementById('edit-user-role-field');
            const typeNote = document.getElementById('edit-user-type-note');
            const selfNote = document.getElementById('edit-user-self-note');
            if (!name || !email || !password || !role || !roleField || !typeNote || !selfNote) return;

            const isSelf = button.dataset.self === '1';
            const isPortal = button.dataset.accountType === 'portal';
            form.action = button.dataset.url || '';
            if (deleteForm) {
                deleteForm.action = button.dataset.deleteUrl || '';
                deleteForm.classList.toggle('hidden', isSelf || !button.dataset.deleteUrl);
                const code = deleteForm.querySelector('input[name="admin_code"]');
                if (code) code.value = '';
            }
            name.value = button.dataset.name || '';
            email.value = button.dataset.email || '';
            password.value = '';
            const roleValue = button.dataset.role || 'admin';
            role.value = roleValue;
            role.disabled = isSelf || isPortal;
            roleField.classList.toggle('hidden', isPortal);
            typeNote.classList.toggle('hidden', !isPortal);
            const roleRoot = role.closest('[data-ctl-select]');
            const roleTrigger = roleRoot?.querySelector('[data-ctl-trigger]');
            const roleLabel = roleRoot?.querySelector('[data-ctl-select-label]');
            roleRoot?.querySelectorAll('[role="option"]').forEach((option) => {
                const selected = option.dataset.value === roleValue;
                option.setAttribute('aria-selected', selected ? 'true' : 'false');
                const check = option.querySelector('svg');
                if (check) check.style.display = selected ? '' : 'none';
                if (selected && roleLabel) roleLabel.textContent = option.dataset.label;
            });
            if (roleTrigger) roleTrigger.disabled = isSelf || isPortal;
            selfNote.classList.toggle('hidden', !isSelf || isPortal);
            window.openModal('edit-user-modal');
        });
    }

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
            // Fallback TANPA window.confirm bawaan: dialog branded minimal.
            window.confirmDialog = (options = {}) => {
                const overlay = document.createElement('div');
                overlay.setAttribute('role', 'alertdialog');
                overlay.setAttribute('aria-modal', 'true');
                overlay.style.cssText = 'position:fixed;inset:0;z-index:90;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgb(2 6 23 / 0.55);';
                overlay.innerHTML = `<div class="ctl-popover" style="max-width:24rem;width:100%;padding:1.5rem;">
                    <p style="font-weight:700;color:var(--ctl-text);">${options.title || 'Apakah Anda yakin?'}</p>
                    <p class="ctl-muted" style="font-size:0.875rem;margin-top:0.25rem;">${options.message || ''}</p>
                    <div style="display:flex;gap:0.75rem;margin-top:1.25rem;">
                        <button type="button" data-x-cancel class="ctl-btn ctl-btn-ghost" style="flex:1;">${options.cancelText || 'Batal'}</button>
                        <button type="button" data-x-ok class="ctl-btn ctl-btn-danger" style="flex:1;">${options.confirmText || 'Ya, lanjutkan'}</button>
                    </div></div>`;
                // Teks via textContent agar aman dari injeksi HTML.
                overlay.querySelector('p').textContent = options.title || 'Apakah Anda yakin?';
                overlay.querySelector('p.ctl-muted').textContent = options.message || '';
                const done = (ok) => {
                    overlay.remove();
                    document.body.style.overflow = '';
                    if (ok) {
                        if (options.formAction) submitConfirmForm(options);
                        else if (typeof options.onConfirm === 'function') options.onConfirm();
                    }
                };
                overlay.querySelector('[data-x-cancel]').addEventListener('click', () => done(false));
                overlay.querySelector('[data-x-ok]').addEventListener('click', () => done(true));
                overlay.addEventListener('click', (e) => { if (e.target === overlay) done(false); });
                overlay.addEventListener('keydown', (e) => { if (e.key === 'Escape') done(false); });
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
                overlay.querySelector('[data-x-cancel]').focus();
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
            if (options.fields && typeof options.fields === 'object') {
                for (const [k, v] of Object.entries(options.fields)) form.appendChild(input(k, v));
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
        const closeButton = lightbox.querySelector('[data-lightbox-close]');
        const previousButton = lightbox.querySelector('[data-lightbox-prev]');
        const nextButton = lightbox.querySelector('[data-lightbox-next]');
        const items = Array.from(document.querySelectorAll('[data-gallery-item]'));
        let activeIndex = -1;
        let returnFocus = null;

        const visibleItems = () => items.filter((item) => !item.classList.contains('hidden'));

        const render = (item) => {
            const title = item.dataset.title || 'Dokumentasi sekolah';
            const src = item.dataset.src;
            imageContainer.replaceChildren();
            if (src) {
                const image = document.createElement('img');
                image.src = src;
                image.alt = title;
                image.className = 'max-h-[75vh] max-w-full object-contain';
                imageContainer.appendChild(image);
            } else {
                const fallback = document.createElement('div');
                fallback.className = 'flex aspect-video w-full max-w-3xl items-center justify-center bg-gradient-to-br from-primary-800 via-primary-900 to-accent-900 p-16 text-sm font-bold text-white';
                fallback.textContent = 'Dokumentasi tidak tersedia';
                imageContainer.appendChild(fallback);
            }
            caption.textContent = title;
        };

        const move = (direction) => {
            const available = visibleItems();
            if (!available.length) return;
            const current = available.indexOf(items[activeIndex]);
            const next = (Math.max(current, 0) + direction + available.length) % available.length;
            activeIndex = items.indexOf(available[next]);
            render(available[next]);
        };

        const close = () => {
            lightbox.classList.add('hidden');
            document.body.style.overflow = '';
            imageContainer.replaceChildren();
            if (returnFocus) returnFocus.focus();
            returnFocus = null;
        };

        lightbox.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-lightbox-backdrop')) close();
        });
        closeButton.addEventListener('click', close);
        previousButton?.addEventListener('click', () => move(-1));
        nextButton?.addEventListener('click', () => move(1));
        document.addEventListener('keydown', (e) => {
            if (lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') move(-1);
            if (e.key === 'ArrowRight') move(1);
            if (e.key === 'Tab') {
                const controls = [closeButton, previousButton, nextButton].filter(Boolean);
                const first = controls[0];
                const last = controls[controls.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });

        items.forEach((item, index) => {
            item.addEventListener('click', () => {
                activeIndex = index;
                returnFocus = item;
                render(item);
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                closeButton.focus();
            });
        });
    }

    /* ---------------- Toasts ---------------- */
    function initToasts() {
        const container = document.querySelector('[data-toast-container]');
        if (!container) return;

        const variants = {
            success: {
                tone: 'ok',
                path: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                title: 'Berhasil',
            },
            error: {
                tone: 'danger',
                path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
                title: 'Gagal',
            },
            warning: {
                tone: 'warn',
                path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
                title: 'Peringatan',
            },
            info: {
                tone: 'info',
                path: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
                title: 'Info',
            },
        };

        window.toast = (message, type = 'success', duration = 5000) => {
            const v = variants[type] || variants.info;

            const toastEl = document.createElement('div');
            toastEl.className = 'pointer-events-auto w-full overflow-hidden ctl-popover !rounded-xl';
            toastEl.setAttribute('role', 'status');
            toastEl.style.animation = 'toast-slide-in 0.35s cubic-bezier(0.16,1,0.3,1)';
            const toneVar = { ok: '--ctl-ok', danger: '--ctl-danger', warn: '--ctl-warn', info: '--ctl-info' }[v.tone];

            toastEl.innerHTML = `
                <div class="flex items-start gap-3 p-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg" style="background: color-mix(in srgb, var(${toneVar}) 14%, transparent); color: var(${toneVar});">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${v.path}" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-sm font-semibold" style="color: var(${toneVar});">${v.title}</p>
                        <p class="mt-0.5 text-sm leading-snug" style="color: var(--ctl-text);"></p>
                    </div>
                    <button type="button" class="ctl-btn ctl-btn-ghost !p-1.5" aria-label="Tutup">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 w-full" style="background: var(--ctl-sunken);">
                    <div class="h-full rounded-full" style="background: var(${toneVar}); animation: toast-progress ${duration}ms linear forwards;"></div>
                </div>
            `;
            toastEl.querySelector('p + p').textContent = message;

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
            if (form.hasAttribute('data-period-open-form')) return;

            form.addEventListener('submit', (event) => {
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

    function initPeriodOpenForms() {
        document.querySelectorAll('[data-period-open-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (form.dataset.submitting === 'true') return;

                const button = form.querySelector('button[type="submit"]');
                const originalHtml = button?.innerHTML;
                form.dataset.submitting = 'true';
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg> Membuka...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors ? Object.values(payload.errors).flat() : [];
                        throw new Error(errors[0] || payload.message || 'Periode gagal dibuka.');
                    }

                    window.location.assign(payload.redirect || window.location.href);
                } catch (error) {
                    form.dataset.submitting = 'false';
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
                    window.toast?.(error.message || 'Periode gagal dibuka. Silakan coba lagi.', 'error');
                }
            });
        });
    }

    function initFormValidationFeedback() {
        document.querySelectorAll('input, select, textarea').forEach((el) => {
            el.addEventListener('invalid', () => {
                el.classList.add('border-red-300');
            });
        });
        // Ringkasan validasi -> klik item memindahkan fokus ke field terkait
        document.querySelectorAll('[data-validation-summary] a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (e) => {
                const target = document.getElementById(link.getAttribute('href').slice(1));
                if (target && /^(INPUT|SELECT|TEXTAREA)$/.test(target.tagName)) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    target.focus({ preventScroll: true });
                }
            });
        });
        // Saat server mengembalikan formulir yang belum lengkap, tampilkan
        // ringkasannya tanpa memaksa pengguna mencari pesan di halaman panjang.
        const summary = document.querySelector('[data-validation-summary]');
        if (summary) {
            requestAnimationFrame(() => {
                summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
                summary.focus({ preventScroll: true });
            });
        }
    }

    /* ---------------- Portal interview slot selection ---------------- */
    function initSlotSelection() {
        document.querySelectorAll('[data-slot-form]').forEach((form) => {
            const options = [...form.querySelectorAll('[data-slot-option]')];
            const inputs = [...form.querySelectorAll('[data-slot-input]')];
            const panel = form.querySelector('[data-slot-summary-panel]');
            const summary = form.querySelector('[data-slot-summary-text]');
            const submit = form.querySelector('[data-slot-submit]');
            const help = form.querySelector('[data-slot-help]');

            const sync = () => {
                const selected = inputs.find((input) => input.checked && !input.disabled);
                options.forEach((option) => {
                    const active = option.querySelector('[data-slot-input]') === selected;
                    option.dataset.selected = String(active);
                    option.setAttribute('aria-checked', String(active));
                });
                if (submit) submit.disabled = !selected;
                panel?.classList.toggle('hidden', !selected);
                if (summary) summary.textContent = selected?.closest('[data-slot-option]')?.dataset.slotSummary || '';
                if (help) help.textContent = selected ? 'Pastikan jadwal sudah sesuai sebelum dikonfirmasi.' : 'Pilih salah satu kartu jadwal untuk melanjutkan.';
            };

            inputs.forEach((input) => input.addEventListener('change', sync));
            sync();
        });
    }

    /* ---------------- ALFATIH//FUTURE: scroll reveal ---------------- */
    function initReveal() {
        const els = document.querySelectorAll('.reveal:not(.revealed)');
        if (!els.length) return;
        if (!('IntersectionObserver' in window)) {
            els.forEach((el) => el.classList.add('revealed'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        els.forEach((el) => io.observe(el));
    }

    /* ---------------- ALFATIH//FUTURE: animated counters ---------------- */
    function initCounters() {
        const els = document.querySelectorAll('[data-counter]');
        if (!els.length) return;
        const animate = (el) => {
            const target = parseInt(el.dataset.target || '0', 10);
            const suffix = el.dataset.suffix || '';
            if (!target) {
                el.textContent = '0' + suffix;
                return;
            }
            const dur = 1200;
            const start = performance.now();
            const tick = (now) => {
                const p = Math.min((now - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };
        if (!('IntersectionObserver' in window)) {
            els.forEach(animate);
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });
        els.forEach((el) => io.observe(el));
    }

    /* ---------------- ALFATIH//FUTURE: hero word swap ---------------- */
    function initWordSwap() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        document.querySelectorAll('[data-word-swap]').forEach((el) => {
            let words = [];
            try {
                words = JSON.parse(el.dataset.words || '[]');
            } catch (e) {
                return;
            }
            if (!Array.isArray(words) || words.length < 2) return;
            let i = 0;
            const inner = el.querySelector('span') || el;
            setInterval(() => {
                i = (i + 1) % words.length;
                const next = document.createElement('span');
                next.textContent = words[i];
                inner.replaceWith(next);
            }, 3000);
        });
    }

    /* ---------------- Fixed public navbar ---------------- */
    function initNavbarScroll() {
        const header = document.querySelector('[data-navbar]');
        if (!header) return;
        const spacer = document.querySelector('[data-navbar-spacer]');
        const mobileMenu = header.querySelector('[data-nav-menu]');
        const syncHeight = () => {
            if (!spacer) return;
            const openMenuHeight = mobileMenu && !mobileMenu.classList.contains('hidden')
                ? mobileMenu.offsetHeight
                : 0;
            spacer.style.height = `${header.offsetHeight - openMenuHeight}px`;
        };
        const onScroll = () => {
            header.classList.toggle('navbar-solid', window.scrollY > 24);
        };
        syncHeight();
        onScroll();
        document.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', syncHeight);
        if ('ResizeObserver' in window) new ResizeObserver(syncHeight).observe(header);
    }
})();
