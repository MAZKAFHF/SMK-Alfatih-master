import { test, expect } from '@playwright/test';

const uniq = (prefix) => `${prefix}+${Date.now()}${Math.floor(Math.random() * 1e6)}@example.id`;

async function rawKeysAbsent(page) {
  for (const key of ['validation.', 'auth.', 'passwords.']) {
    await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
  }
}

test.describe('Human validation (Bahasa Indonesia)', () => {
  test('portal register: password mismatch shows human message', async ({ page }) => {
    await page.goto('/portal/daftar');
    await page.locator('input[name="name"]').fill('Orang Tua E2E');
    await page.locator('input[name="email"]').fill(uniq('e2e'));
    await page.locator('input[name="password"]').fill('abc12345');
    await page.locator('input[name="password_confirmation"]').fill('different123');
    await page.getByRole('button', { name: 'Buat Akun' }).click();
    await expect(page.locator('text=Password dan konfirmasi password harus sama').first()).toBeVisible();
    await rawKeysAbsent(page);
  });

  test('portal register: multiple errors show counted summary', async ({ page }) => {
    await page.goto('/portal/daftar');
    // Bypass native validation to reach server-side summary
    await page.evaluate(() => document.querySelectorAll('form').forEach(f => f.setAttribute('novalidate', 'novalidate')));
    await page.locator('input[name="email"]').fill('bukan-email');
    await page.locator('input[name="password"]').fill('pendek');
    await page.locator('input[name="password_confirmation"]').fill('beda');
    await page.getByRole('button', { name: 'Buat Akun' }).click();
    await expect(page.locator('[data-validation-summary]').first()).toBeVisible();
    await expect(page.locator('[data-validation-summary]').first()).toContainText('bagian yang perlu diperbaiki');
    await rawKeysAbsent(page);
  });

  test('portal login: wrong credentials show human error', async ({ page }) => {
    await page.goto('/portal/masuk');
    await page.locator('input[name="email"]').fill(uniq('ghost'));
    await page.locator('input[name="password"]').fill('Salah1234');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page.locator('text=Email atau password').first()).toBeVisible();
    await rawKeysAbsent(page);
  });

  test('removed PPDB entry points redirect to account portal', async ({ page }) => {
    await page.goto('/ppdb/siswa');
    await expect(page).toHaveURL(/\/portal\/daftar$/);
    await page.goto('/ppdb/status');
    await expect(page).toHaveURL(/\/portal\/masuk$/);
    await expect(page.locator('input[name="registration_number"]')).toHaveCount(0);
  });

  test('no horizontal overflow on auth pages', async ({ page }) => {
    for (const url of ['/portal/daftar', '/portal/masuk', '/ppdb']) {
      await page.goto(url);
      const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
      expect(ok).toBeTruthy();
    }
  });
});
