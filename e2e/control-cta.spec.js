import { test, expect } from '@playwright/test';

test.describe('ALFATIH//CONTROL sidebar + CTA + footer', () => {
  test('footer shows Developed by Mafh exactly once, no overflow', async ({ page }) => {
    for (const url of ['/', '/berita']) {
      await page.goto(url);
      await expect(page.locator('footer').getByText('Developed by Mafh', { exact: false })).toBeVisible();
      await expect(page.locator('footer').getByText('Mafh', { exact: true })).toHaveCount(1);
      const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
      expect(ok).toBeTruthy();
    }
  });

  test('hero Daftar PPDB CTA has admission icon and premium shimmer', async ({ page }) => {
    await page.goto('/');
    const cta = page.getByRole('link', { name: 'Daftar PPDB' }).first();
    await expect(cta).toBeVisible();
    await expect(cta).toHaveClass(/btn-shine/);
    // Ikon user-plus (bukan ikon uang): path user-plus khas
    const iconPath = await cta.locator('svg path').first().getAttribute('d');
    expect(iconPath).toContain('19.235');
    expect(iconPath).not.toContain('2.818');
    // Sheen overlay aktif via ::after
    const after = await cta.evaluate((el) => getComputedStyle(el, '::after').animationName);
    expect(after).toContain('shine-sweep');
    const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
    expect(ok).toBeTruthy();
  });

  test('admin sidebar: equal heights, sliding indicator, banner readable', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e)));
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (!(await demo.isVisible())) test.skip();
    await demo.click();
    await expect(page).toHaveURL(/\/admin/);

    // Tinggi item aktif ≈ item nonaktif
    const active = page.locator('[data-side-item][aria-current="page"]').first();
    const inactive = page.locator('[data-side-item]:not([aria-current])').first();
    const ha = await active.evaluate((el) => el.getBoundingClientRect().height);
    const hi = await inactive.evaluate((el) => el.getBoundingClientRect().height);
    expect(Math.abs(ha - hi)).toBeLessThanOrEqual(2);
    expect(ha).toBeGreaterThanOrEqual(40);
    expect(ha).toBeLessThanOrEqual(52);

    // Indikator ada dan sejajar item aktif
    const bar = page.locator('[data-side-indicator]');
    await expect(bar).toBeVisible();
    await expect.poll(async () => {
      const barY = await bar.evaluate((el) => el.getBoundingClientRect().top);
      const itemY = await active.evaluate((el) => el.getBoundingClientRect().top);
      return Math.abs(barY - itemY);
    }, { timeout: 8000 }).toBeLessThanOrEqual(4);

    // Klik item lain: indikator meluncur (transform berubah), route benar
    // Mobile: buka drawer dulu
    const drawerToggle = page.locator('[data-admin-drawer-toggle]');
    if (await drawerToggle.isVisible()) {
      await drawerToggle.click();
      await expect(page.locator('#admin-sidebar')).not.toHaveClass(/-translate-x-full/);
    }
    const target = page.locator('[data-side-item]', { hasText: 'Slot Wawancara' }).first();
    await target.click();
    await expect(page).toHaveURL(/interview-slots/);
    await expect.poll(async () => {
      const barY2 = await bar.evaluate((el) => el.getBoundingClientRect().top);
      const itemY2 = await target.evaluate((el) => el.getBoundingClientRect().top);
      return Math.abs(barY2 - itemY2);
    }, { timeout: 8000 }).toBeLessThanOrEqual(4);

    // Banner dashboard terbaca (bukan putih-di-putih), dark mode juga
    await page.goto('/admin');
    const bannerText = page.locator('text=menunggu verifikasi').first();
    await expect(bannerText).toBeVisible();
    await page.evaluate(() => { localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark'); });
    await page.waitForTimeout(300);
    await expect(page.locator('text=menunggu verifikasi').first()).toBeVisible();
    const darkTableBg = await page.locator('.ctl-table, .ctl-card').first().evaluate((el) => getComputedStyle(el).backgroundColor).catch(() => 'rgb(0,0,0)');
    expect(darkTableBg).not.toBe('rgb(255, 255, 255)');
    expect(errors.filter((e) => !e.includes('favicon'))).toEqual([]);
  });
});
