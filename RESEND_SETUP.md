# Persiapan Resend — SMK Tahfizh Al-Fatih

Integrasi kode sudah disiapkan dan dapat tetap memakai `MAIL_MAILER=log` selama pengembangan lokal. Jangan menaruh API key atau webhook secret di Git.

## Saat domain produksi sudah aktif

1. Tambahkan dan verifikasi domain `smkalfatih.sch.id` di Resend. Pasang seluruh record DNS yang diberikan Resend.
2. Buat API key dengan izin **Sending access** yang dibatasi ke domain sekolah.
3. Buat webhook HTTPS menuju:

   `https://smkalfatih.sch.id/webhooks/resend`

4. Aktifkan event berikut:
   - `email.sent`
   - `email.delivered`
   - `email.delivery_delayed`
   - `email.bounced`
   - `email.complained`
5. Simpan API key dan signing secret hanya di environment server:

   ```env
   MAIL_MAILER=resend
   RESEND_API_KEY=re_xxxxxxxxx
   RESEND_WEBHOOK_SECRET=whsec_xxxxxxxxx
   MAIL_FROM_ADDRESS=noreply@smkalfatih.sch.id
   MAIL_FROM_NAME="SMK Tahfizh Al-Fatih"
   MAIL_REPLY_TO_ADDRESS=info@smkalfatih.sch.id
   MAIL_REPLY_TO_NAME="Admin SMK Tahfizh Al-Fatih"
   ```

6. Jalankan migrasi dan bersihkan cache konfigurasi:

   ```text
   php artisan migrate --force
   php artisan optimize:clear
   php artisan config:cache
   ```

7. Pastikan worker antrean `emails` selalu berjalan. Contoh proses yang dikelola hosting:

   ```text
   php artisan queue:work --queue=emails,default --sleep=3 --tries=3 --timeout=120
   ```

## Pemeriksaan sebelum go-live

- `APP_URL` sudah menggunakan domain HTTPS final.
- `APP_ENV=production` dan `APP_DEBUG=false`.
- Email pengirim berasal dari domain yang sudah terverifikasi.
- `info@smkalfatih.sch.id` merupakan kotak masuk aktif untuk balasan.
- Email verifikasi, reset password, pendaftaran, wawancara, perbaikan, dan hasil PPDB sudah diuji ke Gmail serta Outlook.
- Status `delivered`, `bounced`, dan `complained` masuk ke `email_logs` melalui webhook.
- Email gagal/bounce/complaint terlihat pada Antrean Kerja admin.

## Mode localhost

Pertahankan konfigurasi berikut agar tidak mengirim email sungguhan:

```env
MAIL_MAILER=log
```

Isi email dapat diperiksa di `storage/logs/laravel.log`. Untuk memproses antrean lokal, jalankan perintah pengembangan proyek atau worker antrean `emails`.
