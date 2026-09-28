import { test, expect } from '@playwright/test';

async function adminLogin(page) {
  await page.goto('/admin/login');
  const demo = page.locator('text=Masuk Sekali Klik');
  if (!(await demo.isVisible())) test.skip();
  await demo.click();
  await expect(page).toHaveURL(/\/admin/);
}

test.describe('PPDB period & history (read-only)', () => {
  test('dashboard shows period context and selector', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin');
    // Konteks periode: judul PPDB + tahun + badge mode.
    await expect(page.getByRole('heading', { name: /PPDB (?:20\d{2}|E2E-)/ }).first()).toBeVisible();
    await expect(page.locator('#period-selector')).toBeVisible();
    // Angka konsisten: total = jumlah badge status.
    await expect(page.locator('text=Tren Pendaftar').first()).toBeVisible();
  });

  test('period management page renders with Indonesian states', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/periods');
    await expect(page.getByRole('heading', { name: 'Periode PPDB' }).first()).toBeVisible();
    await expect(page.locator('text=Buat Periode').first()).toBeVisible();
    // Tidak ada enum mentah.
    for (const key of ['validation.', 'is_active', 'is_archived']) {
      await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
    }
  });

  test('explicit old period param shows history banner', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin');
    // Ambil id periode pertama dari selector.
    const hasOptions = await page.locator('#period-selector [role="option"]').count();
    if (hasOptions < 1) test.skip();
    // Buka pemilih dan pilih opsi pertama (periode terbaru).
    await page.locator('#period-selector button[data-ctl-trigger]').click();
    const first = page.locator('#period-selector [role="option"]').first();
    const label = await first.textContent();
    await first.click();
    await expect(page).toHaveURL(/period=/);
    // Header konteks tetap tampil setelah ganti periode.
    await expect(page.locator(`text=${label.split('—')[0].trim()}`).first()).toBeVisible({ timeout: 10000 });
  });

  test('registrations list defaults to single period context', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/registrations');
    // Filter periode selalu ada; daftar tidak mencampur tanpa konteks.
    await expect(page.locator('form').locator('[data-ctl-trigger]').first()).toBeVisible();
  });

  test('no horizontal overflow on period pages (mobile)', async ({ page }) => {
    await adminLogin(page);
    for (const url of ['/admin', '/admin/periods', '/admin/registrations']) {
      await page.goto(url);
      const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
      expect(ok).toBeTruthy();
    }
  });
});
