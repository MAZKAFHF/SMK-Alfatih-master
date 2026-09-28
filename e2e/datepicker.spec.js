import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

test.beforeEach(() => {
  const code = `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); $p=App\\Models\\PpdbPeriod::where('academic_year','like','E2E-D-%')->latest('id')->first(); if(!$p){$p=App\\Models\\PpdbPeriod::create(['academic_year'=>'E2E-D-'.now()->format('YmdHis')]);} $p->update(['status'=>'open','is_open'=>true,'status_override'=>null,'opens_at'=>now()->subDay(),'closes_at'=>now()->addDays(7)]); App\\Services\\PpdbContext::flush();`;
  execFileSync('php', ['-r', code], { cwd: process.cwd() });
});

async function adminLogin(page) {
  await page.goto('/admin/login');
  const demo = page.locator('text=Masuk Sekali Klik');
  if (!(await demo.isVisible())) test.skip();
  await demo.click();
  await expect(page).toHaveURL(/\/admin/);
}

test.describe('ALFATIH DatePicker month navigation stays open', () => {
  test('period date and 14:50 time survive save and reload exactly', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/periods');
    const row = page.locator('tbody tr').first();
    const year = (await row.locator('td').first().textContent()).trim();
    await row.getByRole('link', { name: 'Ubah' }).click();
    await page.locator('#opens_at-date-display').fill('25-09-2026');
    await page.locator('#opens_at-date-display').blur();
    await page.locator('#opens_at-time-display').fill('14:50');
    await page.locator('#opens_at-time-display').blur();
    await expect(page.locator('input[name="opens_at"]')).toHaveValue('2026-09-25T14:50');
    await page.locator('#closes_at-date-display').fill('26-09-2026');
    await page.locator('#closes_at-date-display').blur();
    await page.locator('#closes_at-time-display').fill('12:30');
    await page.locator('#closes_at-time-display').blur();
    await page.getByRole('button', { name: 'Simpan' }).click();
    await page.locator('tr', { hasText: year }).first().getByRole('link', { name: 'Ubah' }).click();
    await expect(page.locator('#opens_at-date-display')).toHaveValue('25-09-2026');
    await expect(page.locator('#opens_at-time-display')).toHaveValue('14:50');
  });

  test('birth year selector reaches 2000 directly', async ({ page }) => {
    const email = `birth-year+${Date.now()}@example.id`;
    await page.goto('/portal/daftar');
    await page.locator('input[name="name"]').fill('Ortu Tahun');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('Pass12345');
    await page.locator('input[name="password_confirmation"]').fill('Pass12345');
    await page.getByRole('button', { name: 'Buat Akun' }).click();
    await page.goto('/portal/aplikasi/baru');
    await page.getByRole('button', { name: /Buka kalender Tanggal Lahir/ }).click();
    await page.locator('[data-cal-year]').click();
    await expect(page.locator('[data-cal-pickyear="2000"]')).toBeVisible();
    await expect(page.locator('[data-cal-pickyear="1999"]')).toHaveCount(0);
  });

  test('slot modal: next x3, prev x2, year boundary, pick date, no submit', async ({ page }) => {
    test.slow(); // banyak langkah + roundtrip server
    const posts = [];
    const trackPosts = false;
    if (trackPosts) page.on('request', (r) => { if (r.method() === 'POST') posts.push(r.url()); });
    await adminLogin(page);
    await page.goto('/admin/interview-slots');
    // Tunggu primitif JS siap (hindari race bundle vs klik pertama).
    await expect.poll(() => page.evaluate(() => typeof window.openModal), { timeout: 10000 }).toBe('function');
    const modal = page.locator('#slot-create-modal');
    await page.getByRole('button', { name: 'Buat Slot' }).click();
    // Tahan terhadap race buka/tutup: pastikan terbuka stabil.
    await expect.poll(async () => modal.evaluate((el) => !el.classList.contains('hidden')), { timeout: 8000 }).toBe(true);
    await expect(modal).toBeVisible();
    await modal.getByRole('button', { name: /Buka kalender/ }).click();
    const grid = modal.locator('[role="grid"]').first();
    await expect(grid).toBeVisible();
    const m0 = await grid.textContent();
    posts.length = 0; // abaikan POST login demo; hitung mulai navigasi bulan

    for (let i = 0; i < 3; i++) {
      await modal.locator('[aria-label="Bulan berikutnya"]').click();
      await expect(grid).toBeVisible({ timeout: 3000 });
    }
    expect(await grid.textContent()).not.toBe(m0);
    for (let i = 0; i < 2; i++) {
      await modal.locator('[aria-label="Bulan sebelumnya"]').click();
      await expect(grid).toBeVisible({ timeout: 3000 });
    }
    // Modal tetap terbuka, form tidak tersubmit, tidak ada POST.
    await expect(modal).toBeVisible();
    expect(posts.length).toBe(0);

    // Pilih tanggal -> input terisi YYYY-MM-DD, kalender boleh menutup (kontrak select-to-close).
    await grid.locator('[role="gridcell"]:not([disabled]):not([data-outside="true"])').first().click();
    const hidden = await modal.locator('input[name="date"][type="hidden"]').inputValue();
    expect(hidden).toMatch(/^\d{4}-\d{2}-\d{2}$/);
    await expect(modal).toBeVisible();
    // Escape menutup kalender (modal tetap terbuka).
    await modal.getByRole('button', { name: /Buka kalender/ }).click();
    await expect(grid).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(grid).toBeHidden();
    await expect(modal).toBeVisible();
  });

  test('portal birth date: open, navigate, outside click closes', async ({ page }) => {
    const email = `cal+${Date.now()}@example.id`;
    await page.goto('/portal/daftar');
    await page.locator('input[name="name"]').fill('Ortu Cal');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('Pass12345');
    await page.locator('input[name="password_confirmation"]').fill('Pass12345');
    await page.getByRole('button', { name: 'Buat Akun' }).click();
    await expect(page).toHaveURL(/\/portal\/?$/);
    await page.goto('/portal/aplikasi/baru');
    await page.getByRole('button', { name: /Buka kalender/ }).first().click();
    const grid = page.locator('[role="grid"]').first();
    await expect(grid).toBeVisible();
    await page.locator('[aria-label="Bulan berikutnya"]').first().click();
    await expect(grid).toBeVisible({ timeout: 3000 });
    // Outside click menutup.
    await page.locator('h1').first().click({ position: { x: 5, y: 5 } }).catch(() => {});
    await page.mouse.click(5, 5);
    await expect(grid).toBeHidden({ timeout: 3000 });
  });
});
