import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const suffix = `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
const email = `lifecycle+${suffix}@example.id`;
const student = `Siswa Lifecycle ${suffix}`;
const password = 'Pass12345';
const tmpDir = path.join(process.cwd(), 'test-results', `lifecycle-${suffix}`);

function php(code) {
  return execFileSync('php', ['-r', `require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); ${code}`], { cwd: process.cwd(), encoding: 'utf8' }).trim();
}

function adminLogin(page) {
  return page.goto('/admin/login').then(async () => {
    await page.getByText('Masuk Sekali Klik', { exact: false }).click();
    await expect(page).toHaveURL(/\/admin/);
  });
}

test.afterEach(() => {
  fs.rmSync(tmpDir, { recursive: true, force: true });
});

test('applicant to released PPDB result is protected end to end', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'laptop', 'Full lifecycle runs once on laptop viewport.');
  test.setTimeout(180000);

  php(`$p=App\\Models\\PpdbPeriod::create(['academic_year'=>'E2E-LIFECYCLE-${suffix}','status'=>'open','is_open'=>true,'opens_at'=>now()->subDay(),'closes_at'=>now()->addDays(30)]); App\\Services\\PpdbContext::flush(); App\\Models\\InterviewSlot::create(['period_id'=>$p->id,'date'=>now('Asia/Jakarta')->addDays(2)->toDateString(),'start_time'=>'09:00','end_time'=>'10:00','location'=>'Ruang E2E','capacity'=>3,'status'=>'active']);`);

  fs.mkdirSync(tmpDir, { recursive: true });
  const pdf = Buffer.from('%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n');
  const jpg = Buffer.from('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////2wBDAf//////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AK//9k=', 'base64');
  for (const type of ['kk', 'ktp_ortu', 'akta', 'rapor']) fs.writeFileSync(path.join(tmpDir, `${type}.pdf`), pdf);
  fs.writeFileSync(path.join(tmpDir, 'foto.jpg'), jpg);

  await page.goto('/portal/daftar');
  await page.locator('input[name="name"]').fill('Orang Tua Lifecycle');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('input[name="password_confirmation"]').fill(password);
  await page.getByRole('button', { name: 'Buat Akun' }).click();

  await page.goto('/portal/aplikasi/baru');
  const fields = { name: student, nik: '3201010105990001', nisn: '5990001001', birth_place: 'Bogor', province: 'Jawa Barat', city: 'Bogor', district: 'Bogor Tengah', village: 'Pabaton', postal_code: '16121', school_origin: 'SMP E2E', father_name: 'Ayah E2E', father_phone: '081234567890', mother_name: 'Ibu E2E' };
  for (const [name, value] of Object.entries(fields)) await page.locator(`[name="${name}"]`).fill(value);
  await page.locator('input[data-ctl-date-display]').fill('12/05/2010');
  await page.locator('input[name="birth_date"]').evaluate((input) => {
    input.value = '2010-05-12';
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
  await page.locator('textarea[name="address"]').fill('Jalan Pengujian Nomor 1');
  await page.locator('#gender-trigger').click();
  await page.getByRole('option', { name: 'Laki-laki', exact: true }).click();
  await page.locator('#program_id-trigger').click();
  await page.locator('#program_id-list [role="option"]').nth(1).click();
  await page.getByRole('button', { name: 'Simpan Draf' }).click();

  await page.locator('a[href*="tahap=dokumen"]').first().click();
  for (const type of ['kk', 'ktp_ortu', 'akta', 'rapor', 'foto']) {
    await page.locator(`#doc-upload-${type}-file`).setInputFiles(path.join(tmpDir, type === 'foto' ? 'foto.jpg' : `${type}.pdf`));
    await expect(page.locator(`[data-document-card="${type}"] [data-save-state]`)).toHaveText('Terunggah', { timeout: 15000 });
  }
  php(`App\\Models\\User::where('email','${email}')->update(['email_verified_at'=>now()]);`);
  await page.getByRole('link', { name: /Verif/ }).click();
  await page.getByRole('link', { name: /Review/ }).first().click();
  await page.getByLabel(/Saya menyatakan/).check();
  await page.getByRole('button', { name: 'Kirim Final' }).click();
  await expect(page.getByText('Menunggu Verifikasi').first()).toBeVisible();

  await page.getByRole('button', { name: 'Keluar' }).click();
  await adminLogin(page);
  await page.goto('/admin/registrations');
  await page.locator('input[name="search"]').fill(student);
  await page.getByRole('button', { name: 'Cari' }).click();
  await page.locator('tr', { hasText: student }).getByRole('link', { name: 'Detail' }).click();
  for (const review of await page.locator('[data-document-review]').all()) {
    await review.locator('[data-ctl-trigger]').click();
    await review.getByRole('option', { name: 'Valid', exact: true }).click();
    await expect(review.locator('[data-review-state]')).toHaveText('Tersimpan', { timeout: 10000 });
  }
  await page.getByRole('button', { name: 'Verifikasi Aplikasi' }).click();
  await expect(page.getByText('Terverifikasi').first()).toBeVisible();

  await page.getByRole('button', { name: 'Keluar' }).first().click();
  await page.goto('/portal/masuk');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await page.getByRole('link', { name: 'Lihat Tahapan' }).click();
  await page.locator('a[href*="tahap=wawancara"]').first().click();
  await page.getByRole('link', { name: 'Pilih Jadwal Wawancara' }).first().click();
  await page.locator('label', { hasText: 'Ruang E2E' }).click();
  await page.getByRole('button', { name: 'Konfirmasi Pilihan Slot' }).click();
  await expect(page.getByText('Ruang E2E')).toBeVisible();

  await page.getByRole('button', { name: 'Keluar' }).click();
  await adminLogin(page);
  await page.goto('/admin/registrations');
  await page.locator('input[name="search"]').fill(student);
  await page.getByRole('button', { name: 'Cari' }).click();
  await page.locator('tr', { hasText: student }).getByRole('link', { name: 'Detail' }).click();
  await page.getByRole('button', { name: 'Simpan Kehadiran + Asesmen' }).click();
  await page.getByLabel('Saya yakin menetapkan keputusan ini.').check();
  await page.getByRole('button', { name: 'Simpan Keputusan Internal' }).click();
  await page.getByRole('button', { name: /Rilis Hasil/ }).click();

  await page.getByRole('button', { name: 'Keluar' }).first().click();
  await page.goto('/portal/masuk');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.getByRole('button', { name: 'Masuk' }).click();
  await page.getByRole('link', { name: 'Lihat Tahapan' }).click();
  await page.locator('a[href*="tahap=hasil"]').first().click();
  await expect(page.locator('p', { hasText: /^Lulus$/ })).toBeVisible();
});
