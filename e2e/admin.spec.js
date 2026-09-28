import { test, expect } from '@playwright/test';

test.describe('Admin', () => {
  test('login page and failed login', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.getByText('Masuk Admin').first()).toBeVisible();
    await page.fill('input[name="email"]', 'wrong@example.com');
    await page.fill('input[name="password"]', 'wrong');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page.locator('.text-red-600, [role="alert"]').first()).toBeVisible();
  });

  test('admin dashboard after login (if demo exists)', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (await demo.isVisible()) {
      await demo.click();
      await expect(page).toHaveURL(/\/admin/);
      await expect(page.locator('text=menunggu verifikasi').first()).toBeVisible();
    } else {
      test.skip();
    }
  });

  test('admin drawer mobile', async ({ page }) => {
    await page.goto('/admin/login');
    const drawerToggle = page.locator('[data-admin-drawer-toggle]');
    if (await drawerToggle.isVisible()) {
      await drawerToggle.click();
      await expect(page.locator('#admin-sidebar')).not.toHaveClass(/-translate-x-full/);
      await page.keyboard.press('Escape');
    }
  });

  test('modal and confirm dialog accessibility', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (await demo.isVisible()) {
      await demo.click();
      await page.goto('/admin/users');
      const tambah = page.getByText('Tambah Admin');
      if (await tambah.isVisible()) {
        await tambah.click();
        await expect(page.locator('#create-user-modal')).not.toHaveClass(/hidden/);
        await page.keyboard.press('Escape');
      }
    }
  });

  test('theme toggle persists and sidebar stays readable', async ({ page }) => {
    await page.goto('/admin/login');
    const toggle = page.locator('[data-theme-toggle]').first();
    await toggle.click();
    const dark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
    await page.reload();
    const darkAfter = await page.evaluate(() => document.documentElement.classList.contains('dark'));
    expect(darkAfter).toBe(dark);
    // Kembalikan ke terang bila gelap agar tes lain stabil
    if (darkAfter) await page.locator('[data-theme-toggle]').first().click();
  });

  test('date picker opens ALFATIH calendar, not native popup', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (!(await demo.isVisible())) test.skip();
    await demo.click();
    await page.goto('/admin/interview-slots');
    await page.getByRole('button', { name: 'Buat Slot' }).click();
    await expect(page.locator('#slot-create-modal')).toBeVisible();
    // Buka kalender kustom
    await page.locator('#slot-create-modal').getByRole('button', { name: /Tanggal/ }).first().click();
    await expect(page.locator('#slot-create-modal [role="grid"]').first()).toBeVisible();
    await expect(page.locator('#slot-create-modal [role="grid"]').first()).toContainText('Sen');
    // Pilih hari ini -> hidden input YYYY-MM-DD
    await page.locator('#slot-create-modal [role="gridcell"][aria-selected="true"], #slot-create-modal [role="gridcell"][data-today="true"]').first().click({ timeout: 5000 }).catch(() => {});
    await page.keyboard.press('Escape');
  });

  test('slot table has no white surface in dark mode', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (!(await demo.isVisible())) test.skip();
    await demo.click();
    await page.goto('/admin/interview-slots');
    // Paksa dark mode
    await page.evaluate(() => { localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark'); });
    await page.waitForTimeout(300);
    const tableBg = await page.locator('.ctl-table').first().evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(tableBg).not.toBe('rgb(255, 255, 255)');
    // Hover baris tidak memutih
    const row = page.locator('.ctl-table tbody tr').first();
    if (await row.count()) {
      await row.hover();
      const hoverBg = await row.evaluate((el) => getComputedStyle(el).backgroundColor);
      expect(hoverBg).not.toBe('rgb(255, 255, 255)');
    }
  });

  test('logout', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (await demo.isVisible()) {
      await demo.click();
      await page.goto('/admin');
      const drawerToggle = page.locator('[data-admin-drawer-toggle]');
      if (await drawerToggle.isVisible()) {
        // Mobile: sidebar tertutup, pakai tombol Keluar di header mobile
        await page.locator('header.lg\\:hidden button[type="submit"]').click();
      } else {
        await page.locator('button[aria-label="Keluar"]').first().click();
      }
      await expect(page).toHaveURL(/\/admin\/login/);
    }
  });

  test('create published announcement and verify public', async ({ page }) => {
    test.slow();
    const title = `E2E-ANN-${Date.now()}`;
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (!(await demo.isVisible())) test.skip();
    await demo.click();
    await expect(page).toHaveURL(/\/admin/);
    await page.goto('/admin/announcements/create');
    await page.fill('input[name="title"]', title);
    // Trix editor - fill via trix-editor element
    const editor = page.locator('trix-editor').first();
    await expect(editor).toBeVisible();
    await editor.click();
    await editor.fill('E2E content via Trix');
    // Ensure hidden input is updated (trix does it automatically, but trigger input)
    await page.getByRole('button', { name: /Status/ }).click();
    await page.getByRole('option', { name: 'Diterbitkan' }).click();
    await page.getByRole('button', { name: 'Simpan' }).click();
    await expect(page).toHaveURL(/\/admin\/announcements/);
    await expect(page.getByText(title).first()).toBeVisible();
    // public /pengumuman
    await page.goto('/pengumuman');
    await expect(page.getByText(title).first()).toBeVisible();
    // homepage
    await page.goto('/');
    await expect(page.getByText(title).first()).toBeVisible();
    // verify edit does not show raw <p>
    await page.goto('/admin/announcements');
    const editLink = page.locator(`a[href*="/admin/announcements/"]`, { hasText: title }).first();
    // fallback: find row and click Edit
    const row = page.locator('tr', { hasText: title });
    if (await row.isVisible()) {
      await row.getByRole('link', { name: 'Edit' }).first().click();
      await expect(page.locator('trix-editor').first()).toBeVisible();
      const html = await page.locator('trix-editor').innerHTML();
      // Should not contain visible raw <p> as text (trix shows formatted)
      await expect(page.locator('trix-editor')).not.toContainText('<p>');
      // cleanup: delete
      await page.goto('/admin/announcements');
      const row2 = page.locator('tr', { hasText: title });
      if ((await row2.count()) > 0) {
        await row2.getByRole('button', { name: 'Hapus' }).first().click();
        const confirmOk = page.locator('[data-confirm-ok]');
        await expect(confirmOk).toBeVisible({ timeout: 5000 });
        await confirmOk.click();
        await expect(page.locator('tr', { hasText: title })).toHaveCount(0, { timeout: 5000 });
      }
    }
  });
});
