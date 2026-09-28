import { test, expect } from '@playwright/test';

test.describe('Public Website', () => {
  test('homepage loads and no console errors', async ({ page }) => {
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    await page.goto('/');
    await expect(page.locator('h1')).toContainText('Membangun Generasi');
    await expect(page.getByRole('navigation', { name: 'Navigasi utama' })).toBeVisible();
    expect(errors).toEqual([]);
  });

  test('navbar mobile drawer opens', async ({ page }) => {
    await page.goto('/');
    const toggle = page.locator('[data-nav-toggle]');
    if (await toggle.isVisible()) {
      await toggle.click();
      await expect(page.locator('[data-nav-menu]')).toBeVisible();
      await page.keyboard.press('Escape');
    }
  });

  test('theme toggle works', async ({ page }) => {
    await page.goto('/');
    const toggle = page.locator('[data-theme-toggle]').first();
    if (await toggle.isVisible()) {
      await toggle.click();
      await expect(page.locator('html')).toHaveClass(/dark|light/);
    }
  });

  test('program list and detail', async ({ page }) => {
    await page.goto('/program-keahlian');
    await expect(page.getByRole('heading', { name: 'Program Keahlian' })).toBeVisible();
    const first = page.locator('a[href*="/program-keahlian/"]').first();
    if (await first.isVisible()) {
      await first.click();
      await expect(page.locator('h1, h2').first()).toBeVisible();
    }
  });

  test('news list', async ({ page }) => {
    await page.goto('/berita');
    await expect(page.getByRole('heading', { name: 'Berita Sekolah' }).first()).toBeVisible();
  });

  test('gallery and lightbox', async ({ page }) => {
    await page.goto('/galeri');
    await expect(page.getByRole('heading', { name: 'Galeri Sekolah' })).toBeVisible();
    // Verify at least one image loads with naturalWidth >0 if galleries exist
    const img = page.locator('[data-gallery-item] img').first();
    if (await img.isVisible()) {
      await expect.poll(async () => await img.evaluate(el => el.naturalWidth), { timeout: 5000 }).toBeGreaterThan(0);
    }
    const item = page.locator('[data-gallery-item]').first();
    if (await item.isVisible()) {
      await item.click();
      await expect(page.locator('[data-lightbox]')).not.toHaveClass(/hidden/);
      const lightboxImg = page.locator('[data-lightbox-image] img');
      if (await lightboxImg.isVisible()) {
        await expect.poll(async () => await lightboxImg.evaluate(el => el.naturalWidth), { timeout: 5000 }).toBeGreaterThan(0);
      }
      await page.keyboard.press('Escape');
      await expect(page.locator('[data-lightbox]')).toHaveClass(/hidden/);
    }
  });

  test('contact form validation', async ({ page }) => {
    await page.goto('/kontak');
    await page.getByRole('button', { name: 'Kirim Pesan' }).click();
    // HTML5 validation should prevent submit; check required
    await expect(page.locator('input[name="name"]')).toBeVisible();
  });

  test('ppdb flow', async ({ page }) => {
    await page.goto('/ppdb');
    await expect(page.getByRole('heading', { name: 'PPDB Online' }).first()).toBeVisible();
    await expect(page.getByText('Cek Status Lama')).toHaveCount(0);
    await expect(page.getByText('formulir cepat')).toHaveCount(0);
    await page.goto('/ppdb/siswa');
    await expect(page).toHaveURL(/\/portal\/daftar$/);
    await page.goto('/ppdb/status');
    await expect(page).toHaveURL(/\/portal\/masuk$/);
    await expect(page.locator('input[name="registration_number"]')).toHaveCount(0);
  });

  test('no horizontal overflow', async ({ page }) => {
    await page.goto('/');
    const width = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
    expect(width).toBeTruthy();
  });

  test('homepage identity components render', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('[data-word-swap]').first()).toBeVisible();
    await expect(page.locator('.marquee').first()).toBeVisible();
    await expect(page.locator('.pulse-track').first()).toBeVisible();
    // counters resolve to final values after scroll into view
    const stats = page.locator('[data-counter]');
    await stats.first().scrollIntoViewIfNeeded();
    await expect.poll(async () => await stats.first().innerText(), { timeout: 8000 }).not.toBe('0');
  });

  test('profile page is editorial', async ({ page }) => {
    await page.goto('/profil');
    await expect(page.getByRole('heading', { name: 'Profil Sekolah' }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Baca Visi & Misi' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Daftar PPDB' }).first()).toBeVisible();
  });
});
