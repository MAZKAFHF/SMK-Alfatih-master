# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: public.spec.js >> Public Website >> navbar mobile drawer opens
- Location: e2e\public.spec.js:13:3

# Error details

```
Error: expect(locator).not.toHaveClass(expected) failed

Locator: locator('[data-nav-menu]')
Expected pattern: not /hidden/
Received string: "max-h-[80vh] overflow-y-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:hidden"
Timeout: 5000ms

Call log:
  - Expect "not toHaveClass" locator('[data-nav-menu]') with timeout 5000ms
  - waiting for locator('[data-nav-menu]')
    14 × locator resolved to <div data-nav-menu="" class="max-h-[80vh] overflow-y-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:hidden">…</div>
       - unexpected value "max-h-[80vh] overflow-y-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:hidden"

```

```yaml
- link "Beranda":
  - /url: http://127.0.0.1:8000
- text: Profil
- link "Profil Sekolah":
  - /url: http://127.0.0.1:8000/profil
- link "Sejarah":
  - /url: http://127.0.0.1:8000/sejarah
- link "Visi & Misi":
  - /url: http://127.0.0.1:8000/visi-misi
- link "Sambutan Kepala Sekolah":
  - /url: http://127.0.0.1:8000/sambutan-kepala-sekolah
- link "Fasilitas":
  - /url: http://127.0.0.1:8000/fasilitas
- link "Program Keahlian":
  - /url: http://127.0.0.1:8000/program-keahlian
- link "Berita":
  - /url: http://127.0.0.1:8000/berita
- link "Galeri":
  - /url: http://127.0.0.1:8000/galeri
- link "Pengumuman":
  - /url: http://127.0.0.1:8000/pengumuman
- link "Kontak":
  - /url: http://127.0.0.1:8000/kontak
- link "Pendaftaran":
  - /url: http://127.0.0.1:8000/ppdb
  - button "Pendaftaran"
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test.describe('Public Website', () => {
  4  |   test('homepage loads and no console errors', async ({ page }) => {
  5  |     const errors = [];
  6  |     page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  7  |     await page.goto('/');
  8  |     await expect(page.locator('h1')).toContainText('SMK Tahfizh');
  9  |     await expect(page.getByRole('navigation', { name: 'Navigasi utama' })).toBeVisible();
  10 |     expect(errors).toEqual([]);
  11 |   });
  12 | 
  13 |   test('navbar mobile drawer opens', async ({ page }) => {
  14 |     await page.goto('/');
  15 |     const toggle = page.locator('[data-nav-toggle]');
  16 |     if (await toggle.isVisible()) {
  17 |       await toggle.click();
> 18 |       await expect(page.locator('[data-nav-menu]')).not.toHaveClass(/hidden/);
     |                                                         ^ Error: expect(locator).not.toHaveClass(expected) failed
  19 |       await page.keyboard.press('Escape');
  20 |     }
  21 |   });
  22 | 
  23 |   test('theme toggle works', async ({ page }) => {
  24 |     await page.goto('/');
  25 |     const toggle = page.locator('[data-theme-toggle]').first();
  26 |     if (await toggle.isVisible()) {
  27 |       await toggle.click();
  28 |       await expect(page.locator('html')).toHaveClass(/dark|light/);
  29 |     }
  30 |   });
  31 | 
  32 |   test('program list and detail', async ({ page }) => {
  33 |     await page.goto('/program-keahlian');
  34 |     await expect(page.getByRole('heading', { name: 'Program Keahlian' })).toBeVisible();
  35 |     const first = page.locator('a[href*="/program-keahlian/"]').first();
  36 |     if (await first.isVisible()) {
  37 |       await first.click();
  38 |       await expect(page.locator('h1, h2').first()).toBeVisible();
  39 |     }
  40 |   });
  41 | 
  42 |   test('news list', async ({ page }) => {
  43 |     await page.goto('/berita');
  44 |     await expect(page.getByRole('heading', { name: 'Berita Sekolah' }).first()).toBeVisible();
  45 |   });
  46 | 
  47 |   test('gallery and lightbox', async ({ page }) => {
  48 |     await page.goto('/galeri');
  49 |     await expect(page.getByRole('heading', { name: 'Galeri Sekolah' })).toBeVisible();
  50 |     const item = page.locator('[data-gallery-item]').first();
  51 |     if (await item.isVisible()) {
  52 |       await item.click();
  53 |       await expect(page.locator('[data-lightbox]')).not.toHaveClass(/hidden/);
  54 |       await page.keyboard.press('Escape');
  55 |       await expect(page.locator('[data-lightbox]')).toHaveClass(/hidden/);
  56 |     }
  57 |   });
  58 | 
  59 |   test('contact form validation', async ({ page }) => {
  60 |     await page.goto('/kontak');
  61 |     await page.getByRole('button', { name: 'Kirim Pesan' }).click();
  62 |     // HTML5 validation should prevent submit; check required
  63 |     await expect(page.locator('input[name="name"]')).toBeVisible();
  64 |   });
  65 | 
  66 |   test('ppdb flow', async ({ page }) => {
  67 |     await page.goto('/ppdb');
  68 |     await expect(page.getByRole('heading', { name: 'PPDB Online' }).first()).toBeVisible();
  69 |     await page.goto('/ppdb/siswa');
  70 |     if (await page.getByText('PPDB Ditutup').first().isVisible().catch(() => false)) {
  71 |       await expect(page.getByText('PPDB Ditutup').first()).toBeVisible();
  72 |     } else {
  73 |       await expect(page.locator('form').first()).toBeVisible();
  74 |     }
  75 |     await page.goto('/ppdb/status');
  76 |     await expect(page.getByRole('heading', { name: 'Cek Status Pendaftaran' }).first()).toBeVisible();
  77 |     await page.locator('input[name="registration_number"]').fill('PPDB-2026-99999');
  78 |     await page.locator('input[name="birth_date"]').fill('2010-01-01');
  79 |     await page.getByRole('button', { name: 'Cek Status' }).click();
  80 |     await expect(page.locator('text=Data tidak ditemukan').first()).toBeVisible();
  81 |   });
  82 | 
  83 |   test('no horizontal overflow', async ({ page }) => {
  84 |     await page.goto('/');
  85 |     const width = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
  86 |     expect(width).toBeTruthy();
  87 |   });
  88 | });
  89 | 
```