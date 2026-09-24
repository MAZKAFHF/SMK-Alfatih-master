# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin.spec.js >> Admin >> logout
- Location: e2e\admin.spec.js:50:3

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: locator.click: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('button[aria-label="Keluar"]').first()
    - locator resolved to <button type="submit" title="Keluar" aria-label="Keluar" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white">…</button>
  - attempting click action
    2 × waiting for element to be visible, enabled and stable
      - element is visible, enabled and stable
      - scrolling into view if needed
      - done scrolling
      - element is outside of the viewport
    - retrying click action
    - waiting 20ms
    2 × waiting for element to be visible, enabled and stable
      - element is visible, enabled and stable
      - scrolling into view if needed
      - done scrolling
      - element is outside of the viewport
    - retrying click action
      - waiting 100ms
    42 × waiting for element to be visible, enabled and stable
       - element is visible, enabled and stable
       - scrolling into view if needed
       - done scrolling
       - element is outside of the viewport
     - retrying click action
       - waiting 500ms

```

# Page snapshot

```yaml
- generic [ref=f2e2]:
  - complementary "Navigasi admin" [ref=f2e3]:
    - generic [ref=f2e4]:
      - img "Logo SMK Tahfizh Al-Fatih" [ref=f2e5]
      - generic [ref=f2e6]:
        - generic [ref=f2e7]: SMK TAHFIZH
        - generic [ref=f2e8]: Admin Panel
    - navigation [ref=f2e9]:
      - link "Dashboard" [ref=f2e10] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin
      - link "Pendaftar PPDB" [ref=f2e12] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/registrations
      - link "Program Keahlian" [ref=f2e14] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/programs
      - link "Berita" [ref=f2e16] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/news
      - link "Galeri" [ref=f2e18] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/galleries
      - link "Pengumuman" [ref=f2e20] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/announcements
      - link "Halaman" [ref=f2e22] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/pages
      - link "Pesan Masuk" [ref=f2e24] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/contact-messages
      - link "Pengaturan" [ref=f2e27] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/settings
      - link "Trash" [ref=f2e28] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/trash
      - link "Audit Log" [ref=f2e29] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/audit-logs
      - link "Log Login" [ref=f2e30] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/login-logs
      - link "Kelola User" [ref=f2e31] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/admin/users
    - generic [ref=f2e33]:
      - generic [ref=f2e34]: A
      - generic [ref=f2e35]:
        - paragraph [ref=f2e36]: Admin Al-Fatih
        - paragraph [ref=f2e37]: admin@smkalfatih.sch.id
      - button "Keluar" [ref=f2e39] [cursor=pointer]
  - generic [ref=f2e42]:
    - banner [ref=f2e43]:
      - generic [ref=f2e44]:
        - generic [ref=f2e45]:
          - button "Buka menu navigasi" [ref=f2e46] [cursor=pointer]
          - img "Logo" [ref=f2e49]
          - generic [ref=f2e50]: ADMIN AL-FATIH
        - generic [ref=f2e51]:
          - button "Ganti tema" [ref=f2e52] [cursor=pointer]
          - button "Keluar" [ref=f2e56] [cursor=pointer]
    - main [ref=f2e57]:
      - generic [ref=f2e59]:
        - generic [ref=f2e60]:
          - paragraph [ref=f2e61]: Kamis, 24 September 2026
          - heading "Selamat datang, Admin" [level=2] [ref=f2e62]
          - paragraph [ref=f2e63]: Pantau pendaftaran PPDB, verifikasi data calon siswa, dan kelola status pendaftaran dari satu tempat.
        - generic [ref=f2e64]:
          - link "Kelola Pendaftar" [ref=f2e65] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/admin/registrations
          - link "Lihat Website" [ref=f2e68] [cursor=pointer]:
            - /url: http://127.0.0.1:8000
      - generic [ref=f2e71]:
        - link [ref=f2e72] [cursor=pointer]:
          - /url: http://127.0.0.1:8000/admin/registrations
          - paragraph [ref=f2e79]: Total Pendaftar
          - paragraph [ref=f2e80]: "16"
          - paragraph [ref=f2e81]: Lihat semua pendaftar
        - link [ref=f2e82] [cursor=pointer]:
          - /url: http://127.0.0.1:8000/admin/registrations?status=accepted
          - paragraph [ref=f2e89]: Diterima
          - paragraph [ref=f2e90]: "7"
          - paragraph [ref=f2e91]: Pendaftar diterima
        - link [ref=f2e92] [cursor=pointer]:
          - /url: http://127.0.0.1:8000/admin/registrations?status=pending
          - paragraph [ref=f2e99]: Perlu Diverifikasi
          - paragraph [ref=f2e100]: "4"
          - paragraph [ref=f2e101]: Tunggu pengecekan Anda
      - generic [ref=f2e102]:
        - generic [ref=f2e103]:
          - generic [ref=f2e104]:
            - generic [ref=f2e105]:
              - heading "Tren Pendaftar" [level=3] [ref=f2e106]
              - paragraph [ref=f2e107]: Jumlah pendaftar masuk dalam 7 hari terakhir
            - generic [ref=f2e108]: 16 masuk
          - generic [ref=f2e110]:
            - generic [ref=f2e111]:
              - generic [ref=f2e112]: "0"
              - generic:
                - 'generic "18 Sep: 0 pendaftar"'
              - generic [ref=f2e113]: Jum
            - generic [ref=f2e114]:
              - generic [ref=f2e115]: "0"
              - generic:
                - 'generic "19 Sep: 0 pendaftar"'
              - generic [ref=f2e116]: Sab
            - generic [ref=f2e117]:
              - generic [ref=f2e118]: "0"
              - generic:
                - 'generic "20 Sep: 0 pendaftar"'
              - generic [ref=f2e119]: Min
            - generic [ref=f2e120]:
              - generic [ref=f2e121]: "0"
              - generic:
                - 'generic "21 Sep: 0 pendaftar"'
              - generic [ref=f2e122]: Sen
            - generic [ref=f2e123]:
              - generic [ref=f2e124]: "0"
              - generic:
                - 'generic "22 Sep: 0 pendaftar"'
              - generic [ref=f2e125]: Sel
            - generic [ref=f2e126]:
              - generic [ref=f2e127]: "16"
              - generic:
                - 'generic "23 Sep: 16 pendaftar"'
              - generic [ref=f2e128]: Rab
            - generic [ref=f2e129]:
              - generic [ref=f2e130]: "0"
              - generic:
                - 'generic "24 Sep: 0 pendaftar"'
              - generic [ref=f2e131]: Kam
        - generic [ref=f2e132]:
          - heading "Sebaran Status" [level=3] [ref=f2e133]
          - paragraph [ref=f2e134]: Semua pendaftar berdasarkan status
          - generic [ref=f2e135]:
            - img "Grafik sebaran status pendaftaran" [ref=f2e136]
            - list [ref=f2e141]:
              - listitem [ref=f2e142]:
                - generic [ref=f2e143]: Menunggu Verifikasi
                - generic [ref=f2e145]: "4"
              - listitem [ref=f2e146]:
                - generic [ref=f2e147]: Diterima
                - generic [ref=f2e149]: "7"
              - listitem [ref=f2e150]:
                - generic [ref=f2e151]: Ditolak
                - generic [ref=f2e153]: "3"
              - listitem [ref=f2e154]:
                - generic [ref=f2e155]: Dibatalkan
                - generic [ref=f2e157]: "2"
      - generic [ref=f2e158]:
        - generic [ref=f2e159]:
          - heading "Peminat Program Keahlian" [level=3] [ref=f2e160]
          - paragraph [ref=f2e161]: Program paling banyak dipilih pendaftar
          - list [ref=f2e162]:
            - listitem [ref=f2e163]:
              - generic [ref=f2e164]:
                - generic [ref=f2e165]: DKV
                - generic [ref=f2e166]: "6"
            - listitem [ref=f2e169]:
              - generic [ref=f2e170]:
                - generic [ref=f2e171]: TJKT
                - generic [ref=f2e172]: "5"
            - listitem [ref=f2e175]:
              - generic [ref=f2e176]:
                - generic [ref=f2e177]: PPLG
                - generic [ref=f2e178]: "4"
            - listitem [ref=f2e181]:
              - generic [ref=f2e182]:
                - generic [ref=f2e183]: Multimedia
                - generic [ref=f2e184]: "1"
        - generic [ref=f2e187]:
          - generic [ref=f2e188]:
            - generic [ref=f2e189]:
              - heading "Pendaftar Terbaru" [level=3] [ref=f2e190]
              - paragraph [ref=f2e191]: Pendaftar yang baru saja mendaftar
            - link "Semua Pendaftar" [ref=f2e193] [cursor=pointer]:
              - /url: http://127.0.0.1:8000/admin/registrations
          - list [ref=f2e194]:
            - listitem [ref=f2e195]:
              - link "Detail M AZKA FAHREZI HASAN" [ref=f2e196] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/16
                - generic [ref=f2e197]: M
                - generic [ref=f2e198]:
                  - generic [ref=f2e199]: M AZKA FAHREZI HASAN
                  - generic [ref=f2e200]: PPLG · 11 jam yang lalu
              - generic [ref=f2e201]: Diterima
              - link "Detail" [ref=f2e203] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/16
            - listitem [ref=f2e206]:
              - link "Detail Ida Mayasari S.Gz" [ref=f2e207] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/1
                - generic [ref=f2e208]: I
                - generic [ref=f2e209]:
                  - generic [ref=f2e210]: Ida Mayasari S.Gz
                  - generic [ref=f2e211]: DKV · 11 jam yang lalu
              - generic [ref=f2e212]: Diterima
              - link "Detail" [ref=f2e214] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/1
            - listitem [ref=f2e217]:
              - link "Detail Putri Dina Wulandari" [ref=f2e218] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/2
                - generic [ref=f2e219]: P
                - generic [ref=f2e220]:
                  - generic [ref=f2e221]: Putri Dina Wulandari
                  - generic [ref=f2e222]: TJKT · 11 jam yang lalu
              - generic [ref=f2e223]: Diterima
              - link "Detail" [ref=f2e225] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/2
            - listitem [ref=f2e228]:
              - link "Detail Harja Simanjuntak" [ref=f2e229] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/3
                - generic [ref=f2e230]: H
                - generic [ref=f2e231]:
                  - generic [ref=f2e232]: Harja Simanjuntak
                  - generic [ref=f2e233]: TJKT · 11 jam yang lalu
              - generic [ref=f2e234]: Diterima
              - link "Detail" [ref=f2e236] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/3
            - listitem [ref=f2e239]:
              - link "Detail Umi Hastuti" [ref=f2e240] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/4
                - generic [ref=f2e241]: U
                - generic [ref=f2e242]:
                  - generic [ref=f2e243]: Umi Hastuti
                  - generic [ref=f2e244]: PPLG · 11 jam yang lalu
              - generic [ref=f2e245]: Diterima
              - link "Detail" [ref=f2e247] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/4
            - listitem [ref=f2e250]:
              - link "Detail Labuh Tamba" [ref=f2e251] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/5
                - generic [ref=f2e252]: L
                - generic [ref=f2e253]:
                  - generic [ref=f2e254]: Labuh Tamba
                  - generic [ref=f2e255]: Multimedia · 11 jam yang lalu
              - generic [ref=f2e256]: Diterima
              - link "Detail" [ref=f2e258] [cursor=pointer]:
                - /url: http://127.0.0.1:8000/admin/registrations/5
      - generic [ref=f2e261]:
        - generic [ref=f2e262]:
          - generic [ref=f2e263]:
            - heading "Aktivitas Login Terbaru" [level=3] [ref=f2e264]
            - paragraph [ref=f2e265]: Admin yang baru saja masuk atau keluar
          - link "Lihat Semua" [ref=f2e266] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/admin/login-logs
        - list [ref=f2e267]:
          - listitem [ref=f2e268]:
            - generic [ref=f2e269]: A
            - generic [ref=f2e270]:
              - paragraph [ref=f2e271]: Admin Al-Fatih
              - paragraph [ref=f2e272]: 127.0.0.1 · 1 detik yang lalu
            - generic [ref=f2e273]: Masuk
          - listitem [ref=f2e275]:
            - generic [ref=f2e276]: A
            - generic [ref=f2e277]:
              - paragraph [ref=f2e278]: Admin Al-Fatih
              - paragraph [ref=f2e279]: 127.0.0.1 · 2 detik yang lalu
            - generic [ref=f2e280]: Masuk
          - listitem [ref=f2e282]:
            - generic [ref=f2e283]: A
            - generic [ref=f2e284]:
              - paragraph [ref=f2e285]: Admin Al-Fatih
              - paragraph [ref=f2e286]: 127.0.0.1 · 5 detik yang lalu
            - generic [ref=f2e287]: Masuk
          - listitem [ref=f2e289]:
            - generic [ref=f2e290]: A
            - generic [ref=f2e291]:
              - paragraph [ref=f2e292]: Admin Al-Fatih
              - paragraph [ref=f2e293]: 127.0.0.1 · 11 detik yang lalu
            - generic [ref=f2e294]: Masuk
          - listitem [ref=f2e296]:
            - generic [ref=f2e297]: A
            - generic [ref=f2e298]:
              - paragraph [ref=f2e299]: Admin Al-Fatih
              - paragraph [ref=f2e300]: 127.0.0.1 · 12 detik yang lalu
            - generic [ref=f2e301]: Masuk
    - contentinfo [ref=f2e303]: © 2026 SMK Tahfizh Al-Fatih — Panel Admin
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | test.describe('Admin', () => {
  4   |   test('login page and failed login', async ({ page }) => {
  5   |     await page.goto('/admin/login');
  6   |     await expect(page.getByText('Masuk Admin').first()).toBeVisible();
  7   |     await page.fill('input[name="email"]', 'wrong@example.com');
  8   |     await page.fill('input[name="password"]', 'wrong');
  9   |     await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  10  |     await expect(page.locator('.text-red-600, [role="alert"]').first()).toBeVisible();
  11  |   });
  12  | 
  13  |   test('admin dashboard after login (if demo exists)', async ({ page }) => {
  14  |     await page.goto('/admin/login');
  15  |     const demo = page.locator('text=Masuk Sekali Klik');
  16  |     if (await demo.isVisible()) {
  17  |       await demo.click();
  18  |       await expect(page).toHaveURL(/\/admin/);
  19  |       await expect(page.locator('text=Selamat datang').first()).toBeVisible();
  20  |     } else {
  21  |       test.skip();
  22  |     }
  23  |   });
  24  | 
  25  |   test('admin drawer mobile', async ({ page }) => {
  26  |     await page.goto('/admin/login');
  27  |     const drawerToggle = page.locator('[data-admin-drawer-toggle]');
  28  |     if (await drawerToggle.isVisible()) {
  29  |       await drawerToggle.click();
  30  |       await expect(page.locator('#admin-sidebar')).not.toHaveClass(/-translate-x-full/);
  31  |       await page.keyboard.press('Escape');
  32  |     }
  33  |   });
  34  | 
  35  |   test('modal and confirm dialog accessibility', async ({ page }) => {
  36  |     await page.goto('/admin/login');
  37  |     const demo = page.locator('text=Masuk Sekali Klik');
  38  |     if (await demo.isVisible()) {
  39  |       await demo.click();
  40  |       await page.goto('/admin/users');
  41  |       const tambah = page.getByText('Tambah Admin');
  42  |       if (await tambah.isVisible()) {
  43  |         await tambah.click();
  44  |         await expect(page.locator('#create-user-modal')).not.toHaveClass(/hidden/);
  45  |         await page.keyboard.press('Escape');
  46  |       }
  47  |     }
  48  |   });
  49  | 
  50  |   test('logout', async ({ page }) => {
  51  |     await page.goto('/admin/login');
  52  |     const demo = page.locator('text=Masuk Sekali Klik');
  53  |     if (await demo.isVisible()) {
  54  |       await demo.click();
  55  |       await page.goto('/admin');
> 56  |       await page.locator('button[aria-label="Keluar"]').first().click();
      |                                                                 ^ Error: locator.click: Test timeout of 30000ms exceeded.
  57  |       await expect(page).toHaveURL(/\/admin\/login/);
  58  |     }
  59  |   });
  60  | 
  61  |   test('create published announcement and verify public', async ({ page }) => {
  62  |     const title = `E2E-ANN-${Date.now()}`;
  63  |     await page.goto('/admin/login');
  64  |     const demo = page.locator('text=Masuk Sekali Klik');
  65  |     if (!(await demo.isVisible())) test.skip();
  66  |     await demo.click();
  67  |     await expect(page).toHaveURL(/\/admin/);
  68  |     await page.goto('/admin/announcements/create');
  69  |     await page.fill('input[name="title"]', title);
  70  |     // Trix editor - fill via trix-editor element
  71  |     const editor = page.locator('trix-editor').first();
  72  |     await expect(editor).toBeVisible();
  73  |     await editor.click();
  74  |     await editor.fill('E2E content via Trix');
  75  |     // Ensure hidden input is updated (trix does it automatically, but trigger input)
  76  |     await page.selectOption('select[name="status"]', 'published');
  77  |     await page.getByRole('button', { name: 'Simpan' }).click();
  78  |     await expect(page).toHaveURL(/\/admin\/announcements/);
  79  |     await expect(page.getByText(title).first()).toBeVisible();
  80  |     // public /pengumuman
  81  |     await page.goto('/pengumuman');
  82  |     await expect(page.getByText(title).first()).toBeVisible();
  83  |     // homepage
  84  |     await page.goto('/');
  85  |     await expect(page.getByText(title).first()).toBeVisible();
  86  |     // verify edit does not show raw <p>
  87  |     await page.goto('/admin/announcements');
  88  |     const editLink = page.locator(`a[href*="/admin/announcements/"]`, { hasText: title }).first();
  89  |     // fallback: find row and click Edit
  90  |     const row = page.locator('tr', { hasText: title });
  91  |     if (await row.isVisible()) {
  92  |       await row.getByRole('link', { name: 'Edit' }).first().click();
  93  |       await expect(page.locator('trix-editor').first()).toBeVisible();
  94  |       const html = await page.locator('trix-editor').innerHTML();
  95  |       // Should not contain visible raw <p> as text (trix shows formatted)
  96  |       await expect(page.locator('trix-editor')).not.toContainText('<p>');
  97  |       // cleanup: delete
  98  |       await page.goto('/admin/announcements');
  99  |       const row2 = page.locator('tr', { hasText: title });
  100 |       if (await row2.isVisible()) {
  101 |         await row2.getByRole('button', { name: 'Hapus' }).first().click();
  102 |         const confirmOk = page.locator('[data-confirm-ok]');
  103 |         if (await confirmOk.isVisible()) await confirmOk.click();
  104 |       }
  105 |     }
  106 |   });
  107 | });
  108 | 
```