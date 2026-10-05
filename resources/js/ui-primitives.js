/* ============================================================
   ALFATIH UI primitives — vanilla JS, tanpa library.
   Select listbox + Date/Time pickers + tooltip. ID-first.
   ============================================================ */
(() => {
    const MONTHS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const DAYS_ID = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const pad = (n) => String(n).padStart(2, '0');
    const isoDate = (y, m, d) => `${y}-${pad(m)}-${pad(d)}`;
    const fmtID = (y, m, d) => `${pad(d)}-${pad(m)}-${y}`;

    // Mask tanggal untuk seluruh DatePicker. Pengguna cukup mengetik delapan
    // angka (DDMMYYYY); pemisah ditambahkan otomatis menjadi DD-MM-YYYY.
    const maskDateInput = (value) => {
        const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
        if (digits.length <= 2) return digits;
        if (digits.length <= 4) return `${digits.slice(0, 2)}-${digits.slice(2)}`;
        return `${digits.slice(0, 2)}-${digits.slice(2, 4)}-${digits.slice(4)}`;
    };

    const caretAfterDateDigits = (value, digitCount) => {
        if (digitCount <= 0) return 0;
        let seen = 0;
        for (let i = 0; i < value.length; i++) {
            if (/\d/.test(value[i])) seen++;
            if (seen === digitCount) {
                // Setelah hari/bulan lengkap, lompat melewati '-' yang baru
                // dibuat agar angka berikutnya masuk ke bagian selanjutnya.
                return i + 1 + ([2, 4].includes(digitCount) && value[i + 1] === '-' ? 1 : 0);
            }
        }
        return value.length;
    };

    function parseDateInput(str) {
        if (!str) return null;
        let m = str.trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (m) return validDateParts(+m[1], +m[2], +m[3]);
        m = str.trim().match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if (m) return validDateParts(+m[3], +m[2], +m[1]);
        return null;
    }

    function validDateParts(y, m, d) {
        if (m < 1 || m > 12 || d < 1 || d > 31) return null;
        const value = new Date(y, m - 1, d);
        return value.getFullYear() === y && value.getMonth() === m - 1 && value.getDate() === d ? { y, m, d } : null;
    }

    function closeAllPopovers(except) {
        document.querySelectorAll('[data-ctl-popover-open]').forEach((p) => {
            if (p !== except) {
                p.removeAttribute('data-ctl-popover-open');
                p.classList.add('hidden');
                const t = document.querySelector(`[data-ctl-trigger="${p.id}"]`);
                if (t) t.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.addEventListener('click', (e) => {
        // Target yang sudah terlepas dari DOM (akibat render ulang sinkron
        // di dalam popover, mis. navigasi bulan) TIDAK BOLEH dianggap
        // "klik di luar" — pemiliknya mengelola state-nya sendiri.
        if (!(e.target instanceof Element) || !e.target.isConnected) return;
        if (!e.target.closest('[data-ctl-popover]') && !e.target.closest('[data-ctl-trigger]')) closeAllPopovers();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { closeAllPopovers(); }
    });

    function togglePopover(trigger, popover) {
        const willOpen = popover.classList.contains('hidden');
        closeAllPopovers();
        if (willOpen) {
            popover.classList.remove('hidden');
            popover.setAttribute('data-ctl-popover-open', 'true');
            trigger.setAttribute('aria-expanded', 'true');
        } else {
            trigger.setAttribute('aria-expanded', 'false');
        }
    }

    /* ---------------- Custom select / listbox ---------------- */
    function initCtlSelects() {
        document.querySelectorAll('[data-ctl-select]').forEach((root) => {
            if (root.dataset.ctlInit) return;
            root.dataset.ctlInit = '1';
            const trigger = root.querySelector('[data-ctl-trigger]');
            const pop = root.querySelector('[data-ctl-popover]');
            const hidden = root.querySelector('input[type="hidden"]');
            const label = root.querySelector('[data-ctl-select-label]');
            const search = root.querySelector('[data-ctl-select-search]');
            const options = [...root.querySelectorAll('[role="option"]')];
            let hi = Math.max(0, options.findIndex((o) => o.getAttribute('aria-selected') === 'true'));

            const highlight = (i) => {
                options.forEach((o) => o.removeAttribute('data-highlighted'));
                if (!options.length) return;
                hi = (i + options.length) % options.length;
                const visible = options.filter((o) => o.style.display !== 'none');
                if (!visible.includes(options[hi])) {
                    const firstVisible = visible[0];
                    hi = firstVisible ? options.indexOf(firstVisible) : hi;
                }
                options[hi].setAttribute('data-highlighted', 'true');
                options[hi].scrollIntoView({ block: 'nearest' });
            };
            const choose = (opt) => {
                if (opt.dataset.disabled === 'true') return;
                options.forEach((o) => o.setAttribute('aria-selected', 'false'));
                opt.setAttribute('aria-selected', 'true');
                hidden.value = opt.dataset.value;
                label.textContent = opt.dataset.label;
                label.classList.remove('ctl-faint');
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                closeAllPopovers();
                trigger.focus();
            };
            trigger.addEventListener('click', () => { togglePopover(trigger, pop); if (!pop.classList.contains('hidden')) highlight(hi); });
            options.forEach((opt, i) => {
                opt.addEventListener('click', () => choose(opt));
                opt.addEventListener('mousemove', () => highlight(i));
            });
            trigger.addEventListener('keydown', (e) => {
                const open = !pop.classList.contains('hidden');
                if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key) && !open) {
                    e.preventDefault(); togglePopover(trigger, pop); highlight(hi);
                } else if (e.key === 'ArrowDown' && open) { e.preventDefault(); highlight(hi + 1); }
                else if (e.key === 'ArrowUp' && open) { e.preventDefault(); highlight(hi - 1); }
                else if ((e.key === 'Enter' || e.key === ' ') && open) { e.preventDefault(); if (options[hi]) choose(options[hi]); }
                else if (e.key.length === 1 && e.key.match(/\S/) && !open) {
                    const found = options.find((o) => o.dataset.label.toLowerCase().startsWith(e.key.toLowerCase()));
                    if (found) choose(found);
                }
            });
            if (search) {
                search.addEventListener('input', () => {
                    const q = search.value.toLowerCase();
                    options.forEach((o) => { o.style.display = o.dataset.label.toLowerCase().includes(q) ? '' : 'none'; });
                    highlight(0);
                });
                search.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') { e.preventDefault(); highlight(hi + 1); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(hi - 1); }
                    else if (e.key === 'Enter') { e.preventDefault(); if (options[hi] && options[hi].style.display !== 'none') choose(options[hi]); }
                });
            }
        });
    }

    /* ---------------- Date picker (kalender ID) ---------------- */
    function buildCalendar(pop, state, onPick) {
        const render = () => {
            const first = new Date(state.y, state.m - 1, 1);
            let startDay = (first.getDay() + 6) % 7; // Senin=0
            const daysInMonth = new Date(state.y, state.m, 0).getDate();
            const daysPrev = new Date(state.y, state.m - 1, 0).getDate();
            let html = `<div class="flex items-center justify-between gap-2 px-3 pt-3">
                <button type="button" data-cal-prev class="ctl-btn ctl-btn-ghost !p-2" aria-label="Bulan sebelumnya"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></button>
                <div class="flex items-center gap-1">
                    <button type="button" data-cal-month class="rounded-lg px-2 py-1 text-sm font-bold" style="color:var(--ctl-text)">${MONTHS_ID[state.m - 1]}</button>
                    <button type="button" data-cal-year class="rounded-lg px-2 py-1 text-sm font-bold" style="color:var(--ctl-text)">${state.y}</button>
                </div>
                <button type="button" data-cal-next class="ctl-btn ctl-btn-ghost !p-2" aria-label="Bulan berikutnya"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></button>
            </div>
            <div class="grid grid-cols-7 gap-0.5 px-3 pb-3 pt-2" role="grid" aria-label="Kalender ${MONTHS_ID[state.m - 1]} ${state.y}">`;
            DAYS_ID.forEach((d) => { html += `<span class="pb-1 text-center text-[11px] font-bold uppercase" style="color:var(--ctl-faint)">${d}</span>`; });
            for (let i = 0; i < 42; i++) {
                const dayNum = i - startDay + 1;
                let y = state.y, m = state.m, d = dayNum, outside = false;
                if (dayNum < 1) { m = state.m - 1; if (m < 1) { m = 12; y--; } d = daysPrev + dayNum; outside = true; }
                else if (dayNum > daysInMonth) { m = state.m + 1; if (m > 12) { m = 1; y++; } d = dayNum - daysInMonth; outside = true; }
                const iso = isoDate(y, m, d);
                const sel = state.value === iso;
                const dis = (state.min && iso < state.min) || (state.max && iso > state.max);
                html += `<button type="button" role="gridcell" class="ctl-cal-day" data-cal-day="${iso}" ${outside ? 'data-outside="true"' : ''} ${sel ? 'aria-selected="true"' : ''} ${d === new Date().getDate() && m === new Date().getMonth() + 1 && y === new Date().getFullYear() ? 'data-today="true"' : ''} ${dis ? 'disabled' : ''} aria-label="${d} ${MONTHS_ID[m - 1]} ${y}">${d}</button>`;
            }
            html += `</div>
            <div class="flex items-center justify-between gap-2 px-3 pb-3">
                <button type="button" data-cal-today class="ctl-btn ctl-btn-ghost ctl-btn-sm">Hari Ini</button>
                ${state.clearable ? '<button type="button" data-cal-clear class="ctl-btn ctl-btn-ghost ctl-btn-sm">Hapus</button>' : '<span></span>'}
            </div>`;
            pop.innerHTML = html;

            const navMonth = (dir, btn) => {
                // stopPropagation: klik nav tidak boleh sampai ke document
                // (outside-click) dalam keadaan apa pun.
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    state.m += dir;
                    if (state.m < 1) { state.m = 12; state.y--; }
                    if (state.m > 12) { state.m = 1; state.y++; }
                    render();
                    // Fokus tetap di dalam DatePicker pasca render ulang.
                    const again = pop.querySelector(dir < 0 ? '[data-cal-prev]' : '[data-cal-next]');
                    if (again) again.focus();
                });
            };
            navMonth(-1, pop.querySelector('[data-cal-prev]'));
            navMonth(1, pop.querySelector('[data-cal-next]'));
            pop.querySelector('[data-cal-month]').addEventListener('click', () => {
                // Siklus bulan cepat: grid 12 bulan
                let mh = '<div class="grid grid-cols-3 gap-1 p-3">';
                MONTHS_ID.forEach((mm, idx) => { mh += `<button type="button" class="ctl-option justify-center rounded-lg" data-cal-pickmonth="${idx + 1}">${mm.slice(0, 3)}</button>`; });
                pop.innerHTML = mh + '</div>';
                pop.querySelectorAll('[data-cal-pickmonth]').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); state.m = +b.dataset.calPickmonth; render(); const back = pop.querySelector('[data-cal-month]'); if (back) back.focus(); }));
            });
            pop.querySelector('[data-cal-year]').addEventListener('click', () => {
                let yh = '<div class="max-h-72 overflow-y-auto p-3 ctl-scrollbar" role="listbox" aria-label="Pilih tahun"><div class="grid grid-cols-3 gap-1">';
                for (let yy = state.maxYear; yy >= state.minYear; yy--) { yh += `<button type="button" role="option" aria-selected="${yy === state.y}" class="ctl-option justify-center rounded-lg" data-cal-pickyear="${yy}">${yy}</button>`; }
                pop.innerHTML = yh + '</div></div>';
                pop.querySelectorAll('[data-cal-pickyear]').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); state.y = +b.dataset.calPickyear; render(); const back = pop.querySelector('[data-cal-year]'); if (back) back.focus(); }));
                pop.querySelector('[aria-selected="true"]')?.scrollIntoView({ block: 'center' });
            });
            const todayBtn = pop.querySelector('[data-cal-today]');
            if (todayBtn) todayBtn.addEventListener('click', () => {
                const t = new Date(); onPick(isoDate(t.getFullYear(), t.getMonth() + 1, t.getDate()));
            });
            const clearBtn = pop.querySelector('[data-cal-clear]');
            if (clearBtn) clearBtn.addEventListener('click', () => onPick(''));
            pop.querySelectorAll('[data-cal-day]').forEach((b) => {
                if (b.disabled) return;
                b.addEventListener('click', () => onPick(b.dataset.calDay));
            });
            // Navigasi panah antartanggal
            pop.addEventListener('keydown', (e) => {
                const cur = document.activeElement;
                if (!cur || !cur.hasAttribute('data-cal-day')) return;
                const days = [...pop.querySelectorAll('[data-cal-day]:not(:disabled)')];
                const i = days.indexOf(cur);
                let n = null;
                if (e.key === 'ArrowRight') n = i + 1;
                else if (e.key === 'ArrowLeft') n = i - 1;
                else if (e.key === 'ArrowDown') n = i + 7;
                else if (e.key === 'ArrowUp') n = i - 7;
                else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onPick(cur.dataset.calDay); return; }
                if (n !== null && days[n]) { e.preventDefault(); days[n].focus(); }
            });
        };
        render();
    }

    function initCtlDatePickers() {
        document.querySelectorAll('[data-ctl-datepicker]').forEach((root) => {
            if (root.dataset.ctlInit) return;
            root.dataset.ctlInit = '1';
            const trigger = root.querySelector('[data-ctl-trigger]');
            const pop = root.querySelector('[data-ctl-popover]');
            const display = root.querySelector('[data-ctl-date-display]');
            const hidden = root.querySelector('input[type="hidden"]');
            const now = new Date();
            const state = {
                value: hidden.value || '',
                min: root.dataset.min || '',
                max: root.dataset.max || '',
                clearable: root.dataset.clearable === '1',
                minYear: +(root.dataset.minYear || '1900'),
                maxYear: +(root.dataset.maxYear || String(now.getFullYear() + 20)),
                y: now.getFullYear(), m: now.getMonth() + 1,
            };
            if (state.min) state.minYear = Math.max(state.minYear, +(state.min.slice(0, 4)));
            if (state.max) state.maxYear = Math.min(state.maxYear, +(state.max.slice(0, 4)));
            if (state.value) {
                const p = parseDateInput(state.value);
                if (p) { state.y = p.y; state.m = p.m; }
            } else {
                // Tampilan awal dijepit ke rentang valid: tanggal lahir -> bulan
                // maks (tak perlu klik mundur belasan tahun), slot -> bulan min.
                const todayIso = isoDate(now.getFullYear(), now.getMonth() + 1, now.getDate());
                if (state.max && todayIso > state.max) {
                    const p = parseDateInput(state.max);
                    if (p) { state.y = p.y; state.m = p.m; }
                } else if (state.min && todayIso < state.min) {
                    const p = parseDateInput(state.min);
                    if (p) { state.y = p.y; state.m = p.m; }
                }
            }
            const sync = () => {
                if (state.value) { const p = parseDateInput(state.value); display.value = p ? fmtID(p.y, p.m, p.d) : ''; }
                else display.value = '';
            };
            const onPick = (iso) => {
                if (iso && state.min && iso < state.min) return;
                if (iso && state.max && iso > state.max) return;
                state.value = iso; hidden.value = iso; sync();
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                closeAllPopovers();
                display.focus();
            };
            sync();

            display.addEventListener('input', () => {
                const caret = display.selectionStart ?? display.value.length;
                const digitsBeforeCaret = display.value.slice(0, caret).replace(/\D/g, '').length;
                const masked = maskDateInput(display.value);
                if (display.value !== masked) display.value = masked;
                const nextCaret = caretAfterDateDigits(masked, digitsBeforeCaret);
                display.setSelectionRange(nextCaret, nextCaret);

                // Jangan pertahankan error atau nilai mesin lama saat pengguna
                // sedang memperbaiki tanggal. Nilai ISO baru disinkronkan segera
                // setelah delapan angka membentuk tanggal yang valid.
                display.removeAttribute('aria-invalid');
                display.setCustomValidity('');
                const previous = hidden.value;
                const p = parseDateInput(masked);
                const iso = p ? isoDate(p.y, p.m, p.d) : '';
                const inRange = iso && (!state.min || iso >= state.min) && (!state.max || iso <= state.max);
                hidden.value = inRange ? iso : '';
                if (inRange) {
                    state.value = iso;
                    state.y = p.y;
                    state.m = p.m;
                }
                if (hidden.value !== previous) hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });
            trigger.addEventListener('click', () => {
                togglePopover(trigger, pop);
                if (!pop.classList.contains('hidden')) {
                    buildCalendar(pop, state, onPick);
                    const sel = pop.querySelector('[aria-selected="true"]') || pop.querySelector('[data-cal-day]:not(:disabled)');
                    if (sel) sel.focus();
                }
            });
            display.addEventListener('change', () => {
                const p = parseDateInput(display.value);
                if (!display.value) { onPick(''); return; }
                if (!p) {
                    display.setAttribute('aria-invalid', 'true');
                    display.setCustomValidity(display.value.includes('/') ? 'Gunakan format DD-MM-YYYY.' : 'Tanggal yang dimasukkan tidak valid.');
                    return;
                }
                const iso = isoDate(p.y, p.m, p.d);
                if ((state.min && iso < state.min) || (state.max && iso > state.max)) {
                    display.setAttribute('aria-invalid', 'true');
                    display.setCustomValidity('Tanggal berada di luar rentang yang diizinkan.');
                    return;
                }
                display.removeAttribute('aria-invalid'); display.setCustomValidity('');
                state.value = iso; hidden.value = iso;
                state.y = p.y; state.m = p.m;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                sync();
            });
        });
    }

    /* ---------------- Time picker (24 jam) ---------------- */
    function initCtlTimePickers() {
        document.querySelectorAll('[data-ctl-timepicker]').forEach((root) => {
            if (root.dataset.ctlInit) return;
            root.dataset.ctlInit = '1';
            const trigger = root.querySelector('[data-ctl-trigger]');
            const pop = root.querySelector('[data-ctl-popover]');
            const display = root.querySelector('[data-ctl-time-display]');
            const hidden = root.querySelector('input[type="hidden"]');
            const step = parseInt(root.dataset.step || '30', 10);
            const valid = (v) => /^([01]\d|2[0-3]):[0-5]\d$/.test(v);
            if (hidden.value && !valid(hidden.value)) hidden.value = '';
            display.value = hidden.value;
            let list = '';
            for (let h = 0; h < 24; h++) for (let m = 0; m < 60; m += step) {
                const v = `${pad(h)}:${pad(m)}`;
                list += `<button type="button" role="option" class="ctl-option justify-center font-mono" data-value="${v}" data-label="${v}" aria-selected="${hidden.value === v}">${v}</button>`;
            }
            pop.innerHTML = `<div class="max-h-60 overflow-y-auto p-2 ctl-scrollbar" role="listbox" aria-label="Pilih jam">${list}</div>`;
            const opts = [...pop.querySelectorAll('[role="option"]')];
            const choose = (v) => {
                hidden.value = v; display.value = v;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                closeAllPopovers(); display.focus();
            };
            opts.forEach((o) => o.addEventListener('click', () => choose(o.dataset.value)));
            trigger.addEventListener('click', () => { togglePopover(trigger, pop); });
            display.addEventListener('change', () => {
                const v = display.value.trim();
                if (!v) { hidden.value = ''; display.setCustomValidity(''); display.removeAttribute('aria-invalid'); return; }
                if (valid(v)) {
                    hidden.value = v; display.setCustomValidity(''); display.removeAttribute('aria-invalid');
                    hidden.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    const hour = Number(v.split(':')[0]);
                    const minute = Number(v.split(':')[1]);
                    display.setAttribute('aria-invalid', 'true');
                    display.setCustomValidity(Number.isFinite(hour) && hour > 23 ? 'Jam harus berada antara 00:00 dan 23:59.' : (Number.isFinite(minute) && minute > 59 ? 'Menit harus berada antara 00 dan 59.' : 'Gunakan format HH:mm.'));
                }
            });
            trigger.addEventListener('keydown', (e) => {
                if ((e.key === 'Enter' || e.key === ' ') && pop.classList.contains('hidden')) { e.preventDefault(); togglePopover(trigger, pop); }
            });
        });
    }

    /* ---------------- Tooltip ---------------- */
    function initCtlTooltips() {
        document.querySelectorAll('[data-ctl-tooltip]').forEach((el) => {
            if (el.dataset.ctlInit) return;
            el.dataset.ctlInit = '1';
            let tip = null;
            const show = () => {
                tip = document.createElement('div');
                tip.className = 'ctl-tooltip';
                tip.setAttribute('role', 'tooltip');
                tip.textContent = el.dataset.ctlTooltip;
                document.body.appendChild(tip);
                const r = el.getBoundingClientRect();
                tip.style.position = 'fixed';
                tip.style.top = `${r.bottom + 6}px`;
                tip.style.left = `${Math.min(Math.max(8, r.left), window.innerWidth - tip.offsetWidth - 8)}px`;
            };
            const hide = () => { if (tip) { tip.remove(); tip = null; } };
            el.addEventListener('mouseenter', show);
            el.addEventListener('mouseleave', hide);
            el.addEventListener('focus', show);
            el.addEventListener('blur', hide);
        });
    }

    /* ---------------- Sidebar sliding indicator (ALFATIH//CONTROL) ----------------
       Satu elemen bersama meluncur via transform ke item aktif.
       Navigasi full-reload: animasi terlihat saat klik (sebelum unload),
       status akhir selalu benar karena dirender server (aria-current). */
    function initSideIndicator() {
        const nav = document.querySelector('[data-side-nav]');
        const bar = document.querySelector('[data-side-indicator]');
        if (!nav || !bar) return;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const place = (item, animate) => {
            if (!item) { bar.style.opacity = '0'; return; }
            bar.style.opacity = '1';
            const navRect = nav.getBoundingClientRect();
            const r = item.getBoundingClientRect();
            const y = r.top - navRect.top + nav.scrollTop;
            if (!animate || reduceMotion) {
                const prev = bar.style.transition;
                bar.style.transition = 'none';
                bar.style.transform = `translateY(${y}px)`;
                bar.style.height = `${r.height}px`;
                void bar.offsetHeight;
                bar.style.transition = prev;
            } else {
                bar.style.transform = `translateY(${y}px)`;
                bar.style.height = `${r.height}px`;
            }
        };

        // Posisi awal: tanpa animasi (tidak ada slide aneh saat load).
        const syncToActive = () => place(nav.querySelector('[data-side-item][aria-current="page"]'), false);
        syncToActive();
        window.addEventListener('resize', syncToActive);
        // Drawer mobile: ukur ulang setelah drawer terbuka.
        document.querySelector('[data-admin-drawer-toggle]')?.addEventListener('click', () => {
            requestAnimationFrame(() => requestAnimationFrame(syncToActive));
        });

        nav.querySelectorAll('[data-side-item]').forEach((item) => {
            item.addEventListener('click', () => {
                // Umpan balik instan + luncuran ke target; navigasi tetap jalan normal.
                nav.querySelectorAll('[data-side-item]').forEach((i) => i.removeAttribute('aria-current'));
                item.setAttribute('aria-current', 'page');
                place(item, true);
            });
        });
    }

    function initAll() {
        initCtlSelects();
        initCtlDatePickers();
        initCtlTimePickers();
        initCtlTooltips();
        initSideIndicator();
    }
    if (document.readyState !== 'loading') initAll();
    else document.addEventListener('DOMContentLoaded', initAll);
    window.ALFATIH = window.ALFATIH || {};
    window.ALFATIH.primitives = { initAll };
})();
