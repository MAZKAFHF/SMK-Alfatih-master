# QA Report

Validasi final 28 September 2026:

- PHPUnit: **228 tes, 1.319 assertion, 0 gagal**.
- Feature regression setelah hardening terakhir: **60 tes, 238 assertion, 0 gagal**.
- Browser laptop: **35/35** skenario PPDB, publik, dan validasi lulus.
- Date/time picker: **4/4** skenario laptop dan **2/2** regresi portal mobile lulus.
- Lifecycle PPDB penuh: akun → dokumen → submit → review admin → wawancara → keputusan → rilis hasil lulus.
- Build Vite produksi berhasil.
- Cache config, route, dan Blade berhasil dibuat lalu dibersihkan.
- Lint PHP seluruh aplikasi, konfigurasi, migrasi, route, dan tes berhasil.
- Audit media: 35 referensi, 0 hilang, 0 orphan setelah cleanup.
- Cleanup final: 0 periode/akun E2E tersisa.

Data operasional setelah QA: 1 periode, 16 aplikasi, 2 pengguna, dan 3 pengumuman. Data ini tidak diubah oleh cleanup E2E.

Konfigurasi eksternal yang tetap harus diselesaikan saat deployment: domain/HTTPS, kredensial SMTP, scheduler host, queue worker bila digunakan, serta volume backup terenkripsi/off-site.
