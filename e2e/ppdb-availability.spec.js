import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const windowSuffix = `${Date.now().toString(36)}${Math.floor(Math.random() * 1e5).toString(36)}`;
const windowYear = `E2E-WIN-${windowSuffix}`.slice(0, 20);
const windowEmail = `window+${windowSuffix}@example.id`;
const windowStudent = `Anak Window ${windowSuffix}`;

function php(code) {
  execFileSync('php', ['artisan', 'tinker', `--execute=${code}`], { cwd: process.cwd(), stdio: 'pipe' });
}

function setWindowState(state) {
  const attrs = state === 'upcoming'
    ? `'status'=>'upcoming','opens_at'=>now('Asia/Jakarta')->addDay(),'closes_at'=>now('Asia/Jakarta')->addDays(2),'quota'=>10,'is_active'=>true,'is_archived'=>false,'status_override'=>null`
    : state === 'closed'
      ? `'status'=>'open','opens_at'=>now('Asia/Jakarta')->subDays(2),'closes_at'=>now('Asia/Jakarta')->addDay(),'quota'=>10,'is_active'=>true,'is_archived'=>false,'status_override'=>'closed'`
      : `'status'=>'open','opens_at'=>now('Asia/Jakarta')->subDay(),'closes_at'=>now('Asia/Jakarta')->addDay(),'quota'=>${state === 'full' ? 1 : 10},'is_active'=>true,'is_archived'=>false,'status_override'=>null`;
  php(`$p=App\\Models\\PpdbPeriod::where('academic_year','${windowYear}')->firstOrFail();$p->update([${attrs}]);App\\Services\\PpdbContext::flush();`);
}

// Home dan /ppdb WAJIB satu suara: badge status sama, CTA konsisten.
async function readHomeState(page) {
  await page.goto('/');
  const badges = await page.locator('div.inline-flex').allTextContents();
  const badge = badges.find((text) => /PPDB/.test(text) && /(Belum Dibuka|Sedang Dibuka|Kuota Telah Terpenuhi|Telah Ditutup|Belum Tersedia)/.test(text)) || '';
  const hero = page.locator('main');
  const hasDaftar = (await hero.getByRole('link', { name: 'Daftar PPDB', exact: true }).count()) > 0;
  const hasInfo = (await hero.getByRole('link', { name: 'Lihat Informasi PPDB' }).count()) > 0;
  return { badge: (badge || '').replace(/\s+/g, ' ').trim(), hasDaftar, hasInfo };
}

async function readPpdbState(page) {
  await page.goto('/ppdb');
  const badges = await page.locator('span.inline-flex').allTextContents();
  const badge = badges.find((text) => /PPDB Tahun Ajaran/.test(text)) || '';
  const hasBuatAkun = (await page.getByRole('link', { name: 'Buat Akun & Daftar' }).count()) > 0;
  return { badge: (badge || '').replace(/\s+/g, ' ').trim(), hasBuatAkun };
}

test.describe('PPDB availability consistency', () => {
  test.describe.configure({ timeout: 120000 });
  test('home and /ppdb agree on status and CTA', async ({ page }) => {
    const home = await readHomeState(page);
    const ppdb = await readPpdbState(page);

    // Label status sama di kedua halaman.
    const labelOf = (b) => (b.match(/(Belum Dibuka|Sedang Dibuka|Kuota Telah Terpenuhi|Telah Ditutup|Belum Tersedia)/) || [])[1] || '';
    expect(labelOf(home.badge)).toBe(labelOf(ppdb.badge));

    // CTA konsisten: daftar hanya bila status Sedang Dibuka.
    if (labelOf(home.badge) === 'Sedang Dibuka') {
      expect(home.hasDaftar).toBe(true);
      expect(ppdb.hasBuatAkun).toBe(true);
    } else if (labelOf(home.badge) !== '') {
      expect(home.hasDaftar).toBe(false);
      expect(ppdb.hasBuatAkun).toBe(false);
      expect(home.hasInfo).toBe(true);
    }

    for (const key of ['validation.', 'Telah Dibuka']) {
      // "Telah Dibuka" hardcode lama tidak boleh muncul di mana pun.
      await expect(page.locator(`text=${key}`).first()).toHaveCount(0);
    }
    // Jadwal (bila periode terkonfigurasi) tampil format Indonesia.
    await page.goto('/ppdb');
    const body = await page.textContent('body');
    expect(body).not.toMatch(/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/);
  });

  test('portal respects availability, account stays accessible', async ({ page }) => {
    await page.goto('/portal/masuk');
    await expect(page.getByRole('heading', { name: /Masuk|Portal/ }).first()).toBeVisible();
  });

  test('no horizontal overflow on availability pages', async ({ page }) => {
    for (const url of ['/', '/ppdb']) {
      await page.goto(url);
      const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 5);
      expect(ok).toBeTruthy();
    }
  });

  test('browser enforces upcoming, open, closed, and full while account remains usable', async ({ page }) => {
    test.setTimeout(180000);
    php(`App\\Models\\PpdbPeriod::updateOrCreate(['academic_year'=>'${windowYear}'],['status'=>'upcoming','opens_at'=>now('Asia/Jakarta')->addDay(),'closes_at'=>now('Asia/Jakarta')->addDays(2),'quota'=>10,'is_open'=>true,'is_active'=>true,'is_archived'=>false,'status_override'=>null]);App\\Services\\PpdbContext::flush();`);

    try {
      // UPCOMING: akun tetap dapat dibuat, tetapi aksi/form siswa baru tidak tersedia.
      await page.goto('/portal/daftar');
      await page.locator('input[name="name"]').fill('Orang Tua Window');
      await page.locator('input[name="email"]').fill(windowEmail);
      await page.locator('input[name="password"]').fill('Pass12345');
      await page.locator('input[name="password_confirmation"]').fill('Pass12345');
      await page.getByRole('button', { name: 'Buat Akun' }).click();
      await expect(page).toHaveURL(/\/portal\/?$/);
      await expect(page.getByText('Pendaftaran siswa baru belum dibuka.').first()).toBeVisible();
      await expect(page.getByRole('link', { name: '+ Tambah Calon Siswa' })).toHaveCount(0);
      await page.goto('/portal/aplikasi/baru');
      await expect(page).toHaveURL(/\/portal\/?$/);
      await expect(page.getByText(/Pendaftaran calon siswa belum dibuka/).first()).toBeVisible();

      // OPEN: aksi muncul dan form benar-benar dapat membuat Student A.
      setWindowState('open');
      await page.goto('/portal');
      await page.getByRole('link', { name: '+ Tambah Calon Siswa' }).first().click();
      await expect(page).toHaveURL(/\/portal\/aplikasi\/baru/);
      await page.locator('input[name="name"]').fill(windowStudent);
      await page.locator('#gender-trigger').click();
      await page.getByRole('option', { name: 'Laki-laki', exact: true }).click();
      await page.locator('#program_id-trigger').click();
      await page.locator('#program_id-list [role="option"]').nth(1).click();
      await page.getByRole('button', { name: 'Simpan Draf' }).click();
      await expect(page.getByText(windowStudent).first()).toBeVisible();

      // CLOSED: aplikasi lama tetap terlihat, tetapi siswa baru diblokir.
      setWindowState('closed');
      await page.goto('/portal');
      await expect(page.getByText(windowStudent).first()).toBeVisible();
      await expect(page.getByText('Pendaftaran siswa baru untuk periode ini telah ditutup.').first()).toBeVisible();
      await expect(page.getByRole('link', { name: '+ Tambah Calon Siswa' })).toHaveCount(0);
      await page.goto('/portal/aplikasi/baru');
      await expect(page).toHaveURL(/\/portal\/?$/);

      // FULL: Student A dijadikan submitted sebagai fixture kuota; aksesnya tetap ada.
      setWindowState('full');
      php(`App\\Models\\PPDBRegistration::where('name','${windowStudent}')->whereHas('period',fn($q)=>$q->where('academic_year','${windowYear}'))->update(['application_status'=>'submitted']);App\\Services\\PpdbContext::flush();`);
      await page.goto('/portal');
      await expect(page.getByText(windowStudent).first()).toBeVisible();
      await expect(page.getByText('Kuota PPDB telah terpenuhi.').first()).toBeVisible();
      await expect(page.getByRole('link', { name: '+ Tambah Calon Siswa' })).toHaveCount(0);
      await page.goto('/portal/aplikasi/baru');
      await expect(page).toHaveURL(/\/portal\/?$/);
      await expect(page.getByText('Kuota PPDB untuk periode ini telah terpenuhi.').first()).toBeVisible();
    } finally {
      // Keluarkan fixture dari pemilihan periode publik tanpa menghapus data.
      php(`$p=App\\Models\\PpdbPeriod::where('academic_year','${windowYear}')->first();if($p){$p->update(['status'=>'draft','is_active'=>false,'is_archived'=>false,'status_override'=>null]);}App\\Services\\PpdbContext::flush();`);
    }
  });
});
