# E2E Report

Playwright dikonfigurasi untuk mobile 390×844, tablet 768×1024, laptop 1366×768, dan desktop 1920×1080.

Perlindungan browser mencakup:

- website publik, navigasi, tema, galeri, kontak, dan overflow;
- login, dashboard, sidebar, modal, dan operasi admin;
- status periode PPDB dan histori;
- pembuatan akun serta beberapa siswa per akun;
- pemetaan dan penggantian lima dokumen secara terisolasi;
- validasi Bahasa Indonesia dan penolakan entry point PPDB lama;
- pemilih tanggal/tahun/jam;
- lifecycle penuh dari pemohon sampai hasil dirilis.

Validasi final:

- lifecycle penuh laptop: 1/1 lulus;
- PPDB + publik + validasi laptop: 35/35 lulus;
- datepicker laptop: 4/4 lulus;
- regresi datepicker portal mobile: 2/2 lulus;
- build produksi berhasil.

Semua fixture memakai periode `E2E-*` dan domain email uji. Setelah eksekusi, `app:cleanup-e2e --force` dijalankan dan audit mengonfirmasi tidak ada data atau berkas dummy tersisa.
