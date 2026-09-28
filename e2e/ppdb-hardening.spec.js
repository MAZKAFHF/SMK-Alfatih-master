import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

const suffix = `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
const tmpDir = path.join(process.cwd(), 'test-results', `e2e-files-${suffix}`);
const files = {
  kk: 'kk-hardening.pdf',
  ktp_ortu: 'ktp-hardening.pdf',
  akta: 'akta-hardening.pdf',
  rapor: 'rapor-hardening.pdf',
  foto: 'foto-hardening.jpg',
};

test.beforeAll(() => {
  fs.mkdirSync(tmpDir, { recursive: true });
  // Byte magic asli agar lolos validasi MIME server (finfo), bukan sekadar ekstensi.
  const jpg1px = Buffer.from('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////2wBDAf//////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AK//9k=', 'base64');
  const pdfMin = Buffer.from('%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n');
  for (const f of Object.values(files)) {
    const buf = f.endsWith('.jpg') ? jpg1px : pdfMin;
    fs.writeFileSync(path.join(tmpDir, f), Buffer.concat([buf, Buffer.from(`\nE2E ${f} ${suffix}`)]));
  }
});

test.afterAll(() => {
  fs.rmSync(tmpDir, { recursive: true, force: true });
});

async function registerApplicant(page, tag) {
  const email = `hardening+${tag}+${suffix}@example.id`;
  await page.goto('/portal/daftar');
  await page.locator('input[name="name"]').fill('Ortu Hardening');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill('Pass12345');
  await page.locator('input[name="password_confirmation"]').fill('Pass12345');
  await page.getByRole('button', { name: 'Buat Akun' }).click();
  await expect(page).toHaveURL(/\/portal\/?$/);
  return email;
}

async function createDraft(page, nama) {
  await page.goto('/portal/aplikasi/baru');
  await page.locator('input[name="name"]').fill(nama);
  await page.locator('#gender-trigger').click();
  await page.getByRole('option', { name: 'Laki-laki', exact: true }).click();
  await page.locator('#program_id-trigger').click();
  await page.locator('#program_id-list [role="option"]').nth(1).click();
  await page.getByRole('button', { name: 'Simpan Draf' }).click();
}

test.describe('PPDB hardening (browser)', () => {
  test('document cards are isolated: reverse order, reload persists', async ({ page }) => {
    test.slow(); // 5x upload + reload melebihi timeout default
    await registerApplicant(page, 'doc');
    const nama = `Anak Dok ${suffix}`;
    await createDraft(page, nama);
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    await page.getByRole('link', { name: /Dokumen/ }).click();

    // Label tiap kartu menarget input yang benar (tidak ada id ganda).
    for (const type of Object.keys(files)) {
      const inputId = await page.locator(`input[type="file"]#doc-upload-${type}-file`).count();
      expect(inputId).toBe(1);
    }

    // Unggah REVERSE order: foto -> rapor -> akta -> ktp -> kk.
    // Memilih file langsung mengirim form melalui komponen upload.
    for (const type of ['foto', 'rapor', 'akta', 'ktp_ortu', 'kk']) {
      const card = page.locator(`[data-document-card="${type}"]`);
      await page.locator(`#doc-upload-${type}-file`).setInputFiles(path.join(tmpDir, files[type]));
      await expect(card.locator('[data-save-state]')).toHaveText('Terunggah', { timeout: 10000 });
      await expect(card).toContainText(files[type]);
    }

    // Reload: semua tetap terpetakan benar.
    await page.reload();
    for (const f of Object.values(files)) {
      await expect(page.locator(`text=${f}`).first()).toBeVisible();
    }
    // KK tetap kk, bukan file lain.
    const kkRow = page.locator('li', { hasText: 'Kartu Keluarga' }).first();
    await expect(kkRow).toContainText(files.kk);
    await expect(kkRow).not.toContainText(files.rapor);
  });

  test('unchecked declaration shows custom Indonesian error, no native bubble', async ({ page }) => {
    await registerApplicant(page, 'chk');
    const nama = `Anak Cek ${suffix}`;
    await createDraft(page, nama);
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    await page.getByRole('link', { name: /Verifikasi/ }).click();
    await page.getByRole('link', { name: 'Review & Kirim' }).click();
    // Native bubble tidak boleh muncul: form novalidate + custom listbox/checkbox.
    const novalidate = await page.locator('form[action*="/kirim"]').getAttribute('novalidate');
    expect(novalidate).not.toBeNull();
    await page.getByRole('button', { name: 'Kirim Final' }).click();
    await expect(page.locator('text=Centang pernyataan ini sebelum mengirim pendaftaran').first()).toBeVisible();
    for (const key of ['validation.', 'Please check this box']) {
      await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
    }
  });

  test('complete application: all sections complete, submit reaches verification gate', async ({ page }) => {
    test.slow();
    await registerApplicant(page, 'full');
    const nama = `Anak Lengkap ${suffix}`;
    await page.goto('/portal/aplikasi/baru');
    await page.locator('input[name="name"]').fill(nama);
    await page.locator('input[name="nik"]').fill('3201010105990001');
    await page.locator('input[name="nisn"]').fill('5990001001');
    await page.locator('input[name="birth_place"]').fill('Bogor');
    await page.locator('input[data-ctl-date-display]').fill('12/05/2010');
    await page.locator('#gender-trigger').click();
    await page.getByRole('option', { name: 'Laki-laki', exact: true }).click();
    await page.locator('#program_id-trigger').click();
    await page.locator('#program_id-list [role="option"]').nth(1).click();
    await page.locator('textarea[name="address"]').fill('Jl. Merdeka No. 1');
    await page.locator('input[name="province"]').fill('Jawa Barat');
    await page.locator('input[name="city"]').fill('Kota Bogor');
    await page.locator('input[name="district"]').fill('Bogor Tengah');
    await page.locator('input[name="village"]').fill('Pabaton');
    await page.locator('input[name="postal_code"]').fill('16121');
    await page.locator('input[name="school_origin"]').fill('SMPN 1 Bogor');
    await page.locator('input[name="father_name"]').fill('Ayah Lengkap');
    await page.locator('input[name="father_phone"]').fill('081234567891');
    await page.locator('input[name="mother_name"]').fill('Ibu Lengkap');
    await page.getByRole('button', { name: 'Simpan Draf' }).click();
    // Tanggal lahir via kalender kustom
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    await expect(page.locator(`text=${nama}`).first()).toBeVisible();
    await page.getByRole('link', { name: /Dokumen/ }).click();
    // Upload SEMUA 5 dokumen wajib
    for (const type of ['kk', 'ktp_ortu', 'akta', 'rapor', 'foto']) {
      const card = page.locator(`[data-document-card="${type}"]`);
      await page.locator(`#doc-upload-${type}-file`).setInputFiles(path.join(tmpDir, files[type]));
      await expect(card.locator('[data-save-state]')).toHaveText('Terunggah', { timeout: 10000 });
      await expect(card).toContainText(files[type]);
    }
    // Review checklist tampil; submit terblokir hanya oleh verifikasi email (data lengkap)
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    await page.getByRole('link', { name: /Verifikasi/ }).click();
    await page.getByRole('link', { name: 'Review & Kirim' }).click();
    await expect(page.locator('text=Kelengkapan Pengiriman').first()).toBeVisible();
    await page.getByLabel(/Saya menyatakan/).check();
    await page.getByRole('button', { name: 'Kirim Final' }).click();
    await expect(page.locator('text=Verifikasi email terlebih dahulu').first()).toBeVisible();
    for (const key of ['validation.', 'Please ']) {
      await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
    }
  });

  test('admin period selector and history context (read-only)', async ({ page }) => {
    await page.goto('/admin/login');
    const demo = page.locator('text=Masuk Sekali Klik');
    if (!(await demo.isVisible())) test.skip();
    await demo.click();
    await expect(page).toHaveURL(/\/admin/);
    // Selector periode ada di dashboard.
    await expect(page.locator('#period-selector')).toBeVisible();
    // Halaman periode ter-render tanpa error.
    await page.goto('/admin/periods');
    await expect(page.getByRole('heading', { name: 'Periode PPDB' }).first()).toBeVisible();
    await expect(page.locator('text=Buat Periode').first()).toBeVisible();
  });

  test('no console errors on portal document page', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e)));
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    await registerApplicant(page, 'con');
    await createDraft(page, `Anak Konsol ${suffix}`);
    await page.goto('/portal');
    await page.getByRole('link', { name: 'Lihat Tahapan' }).first().click();
    expect(errors.filter((e) => !e.includes('favicon'))).toEqual([]);
  });
});
