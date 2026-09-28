# ALFATIH UI Primitives — Panduan Wajib

> Aturan permanen: JANGAN membuat kontrol mentah di file fitur.
> Selalu konsumsi komponen di bawah. Tema terang/gelap otomatis mengikuti.

## Form

| Butuh | Pakai | Jangan |
|---|---|---|
| Teks, email, angka, tel | `<x-ui.input>` | `<input>` mentah |
| Paragraf | `<x-ui.textarea>` | `<textarea>` mentah |
| Pilihan tetap (status, gender, program) | `<x-ui.select>` (custom listbox + keyboard) | `<select>` mentah |
| Pilihan banyak + cari (provinsi, sekolah) | `<x-ui.select searchable>` | `<datalist>` |
| Tanggal | `<x-ui.date-picker>` (kalender ID, submit `YYYY-MM-DD`) | `type="date"` |
| Jam (24 jam) | `<x-ui.time-picker>` (submit `HH:mm`) | `type="time"` |
| Tanggal + jam (jadwal publikasi, periode) | `<x-ui.datetime-picker>` (submit `YYYY-MM-DDTHH:mm` WIB) | `type="datetime-local"` |
| Upload | `<x-ui.file-upload>` / `<x-ui.image-preview>` | `type="file"` mentah |
| Ya/tidak tunggal | `<x-ui.checkbox>` | `<input type="checkbox">` mentah |
| Satu dari banyak | `<x-ui.radio>` | `<input type="radio">` mentah |
| ON/OFF (aktif, buka) | `<x-ui.switch>` | checkbox mentah |

Contoh:

```blade
{{-- BENAR --}}
<x-ui.date-picker label="Tanggal Lahir *" name="birth_date" value="{{ old('birth_date') }}" />

{{-- SALAH — dilarang, guardrail akan gagal --}}
<input type="date" name="birth_date">
```

## Overlay & umpan balik

`x-ui.modal` (+ `openModal('id')` / `closeModal('id')`), `x-ui.confirm-dialog`
(via `confirmDialog({...})` — JANGAN `confirm()`/`alert()` bawaan browser),
`x-ui.dropdown` + `x-ui.dropdown-item`, `x-ui.toast` (via `toast(pesan, tipe)`),
`x-ui.alert`, `x-ui.validation-summary`, `x-ui.tabs`, `x-ui.tooltip`
(`data-ctl-tooltip="..."`), `x-ui.badge` (success/warning/danger/info/neutral),
`x-ui.table`, `x-ui.empty-state`, `x-ui.pagination` (otomatis), `x-admin.icon name="..."`.

## Admin

Gunakan token `ctl-*` (`ctl-card`, `ctl-input`, `ctl-btn-*`, `ctl-table`,
`ctl-badge-*`, `ctl-label`, `ctl-help`, `ctl-error-text`, `ctl-muted`,
`ctl-faint`). Dilarang kombinasi warna per-file (`bg-white dark:bg-slate-900`
manual, `hover:bg-slate-100` di area gelap, dsb).

## Aksesibilitas (tidak bisa ditawar)

Listbox: panah/Enter/Escape/ketik-cari, `aria-expanded`, `aria-selected`.
Kalender: panah navigasi tanggal, Enter pilih, Escape tutup, grid `role=grid`.
Semua: label terlihat, `aria-invalid`, `aria-describedby` ke pesan error,
target sentuh ≥ 44px di mobile, kontras AA.
