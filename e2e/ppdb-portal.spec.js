import { test, expect } from '@playwright/test';

const suffix = `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
const uniq = (prefix) => `${prefix}+${suffix}@example.id`;
const childName = (base) => `${base} ${suffix}`;

async function registerApplicant(page, email, password = 'Pass12345') {
  await page.goto('/portal/daftar');
  await page.locator('input[name="name"]').fill('Ortu Portal E2E');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('input[name="password_confirmation"]').fill(password);
  await page.getByRole('button', { name: 'Buat Akun' }).click();
  await expect(page).toHaveURL(/\/portal\/?$/);
}

// Listbox kustom ALFATIH (pengganti <select> bawaan)
async function fillDraft(page, nama, gender) {
  await page.locator('input[name="name"]').fill(nama);
  await page.locator('#gender-trigger').click();
  await page.getByRole('option', { name: gender, exact: true }).click();
  // Pilih program pertama yang tersedia (opsi 0 = placeholder)
  await page.locator('#program_id-trigger').click();
  await page.locator('#program_id-list [role="option"]').nth(1).click();
}

test.describe('PPDB portal applicant journey', () => {
  test.describe.configure({ timeout: 120000 });
  test('register, draft student, logout, login, draft persists', async ({ page }) => {
    const email = uniq('portal');
    const nama = childName('Anak E2E');
    await registerApplicant(page, email);
    await expect(page.locator('text=Tambah Calon Siswa').first()).toBeVisible();

    // Buat draf sebagian
    await page.goto('/portal/aplikasi/baru');
    await fillDraft(page, nama, 'Laki-laki');
    await page.getByRole('button', { name: 'Simpan Draf' }).click();
    await expect(page.locator(`text=${nama}`).first()).toBeVisible();

    // Keluar lalu masuk lagi — draf tetap ada
    await page.locator('button:has-text("Keluar")').click();
    await expect(page).toHaveURL(/portal\/masuk/);
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('Pass12345');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page.locator(`text=${nama}`).first()).toBeVisible();
  });

  test('final submit requires verified email before completeness gate', async ({ page }) => {
    const email = uniq('portal2');
    const nama = childName('Anak Kurang');
    await registerApplicant(page, email);
    await page.goto('/portal/aplikasi/baru');
    await fillDraft(page, nama, 'Perempuan');
    await page.getByRole('button', { name: 'Simpan Draf' }).click();
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    await page.getByRole('link', { name: /Verif/ }).first().click();
    await page.getByRole('link', { name: /Review/ }).first().click();
    // Checklist kelengkapan tampil per seksi
    await expect(page.locator('text=Kelengkapan Pengiriman').first()).toBeVisible();
    await page.getByLabel(/Saya menyatakan/).check();
    await page.getByRole('button', { name: 'Kirim Final' }).click();
    await expect(page.locator('text=Verifikasi email terlebih dahulu').first()).toBeVisible();
    for (const key of ['validation.', 'auth.', 'passwords.']) {
      await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
    }
  });

  test('choosing a document uploads immediately without a save button', async ({ page }) => {
    const email = uniq('portal-upload');
    const nama = childName('Anak Upload');
    await registerApplicant(page, email);
    await page.goto('/portal/aplikasi/baru');
    await fillDraft(page, nama, 'Laki-laki');
    await page.getByRole('button', { name: 'Simpan Draf' }).click();
    await page.locator('a[href*="tahap=dokumen"]').first().click();
    const card = page.locator('[data-document-card="kk"]');
    await expect(card.getByRole('button', { name: /Simpan|Unggah Dokumen/ })).toHaveCount(0);
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=', 'base64');
    await card.locator('input[type="file"]').setInputFiles({ name: 'kk-e2e.png', mimeType: 'image/png', buffer: png });
    await expect(card.locator('[data-save-state]')).toHaveText('Terunggah', { timeout: 15000 });
    await page.reload();
    await expect(page.locator('[data-document-card="kk"]')).toContainText('kk-e2e.png');

    // Admin: status dan catatan adalah aksi-simpan, tanpa tombol Simpan per file.
    await page.getByRole('button', { name: 'Keluar' }).click();
    await page.goto('/admin/login');
    await page.getByText('Masuk Sekali Klik', { exact: false }).click();
    await page.goto('/admin/registrations');
    await page.locator('input[name="search"]').fill(nama);
    await page.getByRole('button', { name: 'Cari' }).click();
    await page.locator('tr', { hasText: nama }).getByRole('link', { name: 'Detail' }).click();
    const review = page.locator('[data-document-review]').first();
    await expect(review.getByRole('button', { name: /^Simpan$/ })).toHaveCount(0);
    await review.locator('[data-ctl-trigger]').click();
    await review.getByRole('option', { name: 'Perlu Perbaikan' }).click();
    await expect(review.locator('[data-review-state]')).toContainText('Catatan wajib');
    await review.locator('input[name="admin_note"]').fill('Foto KK terpotong, unggah ulang.');
    await expect(review.locator('[data-review-state]')).toHaveText('Tersimpan', { timeout: 15000 });
    await page.reload();
    await expect(page.locator('[data-document-review]').first().locator('input[name="admin_note"]')).toHaveValue('Foto KK terpotong, unggah ulang.');
  });

  test('one account can register two students', async ({ page }) => {
    const email = uniq('portal3');
    const names = [childName('Anak Satu'), childName('Anak Dua')];
    await registerApplicant(page, email);
    for (const nama of names) {
      await page.goto('/portal/aplikasi/baru');
      await fillDraft(page, nama, 'Laki-laki');
      await page.getByRole('button', { name: 'Simpan Draf' }).click();
    }
    await page.goto('/portal');
    await expect(page.locator(`text=${names[0]}`).first()).toBeVisible();
    await expect(page.locator(`text=${names[1]}`).first()).toBeVisible();
  });
});
