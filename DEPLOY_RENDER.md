# Panduan Hosting Laravel di Render

Dokumen ini berisi panduan langkah demi langkah untuk menghosting aplikasi Laravel Anda di Render menggunakan Docker.

---

## Langkah 1: Persiapan Database (PostgreSQL)

Karena Render menyediakan PostgreSQL terkelola secara gratis, kami merekomendasikan penggunaan PostgreSQL:

1. Masuk ke **[Render Dashboard](https://dashboard.render.com/)**.
2. Klik tombol **New** di kanan atas lalu pilih **PostgreSQL**.
3. Isi informasi berikut:
   - **Name**: `ta-database` (atau nama lain)
   - **Database**: `riccsl` (sesuai nama DB Anda)
   - **User**: `riccsl_user` (sesuai kebutuhan Anda)
   - **Region**: Pilih region terdekat dengan pengguna Anda (misal `Singapore` / `ap-southeast-1` jika ada, atau region lain).
4. Klik **Create Database**.
5. Setelah database dibuat, cari kolom **Internal Database URL**. Kita akan membutuhkan URL ini nanti untuk dikoneksikan ke Web Service kita. Formatnya seperti ini:
   `postgresql://username:password@host/database`

---

## Langkah 2: Deploy Web Service Laravel

Setelah database siap, mari kita deploy web aplikasi Laravel:

1. Klik tombol **New** di kanan atas dashboard Render lalu pilih **Web Service**.
2. Hubungkan akun GitHub/GitLab Anda, lalu pilih repositori proyek ini (`Ta Dashboard`).
3. Isi informasi dasar:
   - **Name**: `ta-dashboard` (nama web service Anda)
   - **Environment**: Pilih **Docker** (Render otomatis akan membaca `Dockerfile` di proyek Anda).
   - **Region**: Samakan dengan region database Anda sebelumnya agar koneksinya cepat.
   - **Branch**: `main` atau `master` (cabang repositori yang ingin di-deploy).
   - **Plan**: Pilih **Free** (atau berbayar jika ingin resource lebih besar).
4. Klik ke bagian **Advanced** di bawah sebelum menekan Create Web Service untuk menambahkan **Environment Variables**.

---

## Langkah 3: Menambahkan Environment Variables (Wajib)

Di bagian **Environment Variables**, tambahkan variabel-variabel berikut:

| Key | Value | Catatan |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Menandakan aplikasi berjalan di mode produksi. |
| `APP_DEBUG` | `false` | Mematikan mode debug (demi keamanan). |
| `APP_KEY` | `base64:W/MxnyDkgQowoV4WpHG8turFpXYcyLS3ip0sDLH3YDg=` | Gunakan key ini atau jalankan `php artisan key:generate --show` secara lokal untuk mendapatkan yang baru. |
| `APP_URL` | *Biarkan kosong dulu* | Setelah deploy selesai, masukkan URL web service yang diberikan oleh Render. |
| `DB_CONNECTION` | `pgsql` | Menggunakan koneksi PostgreSQL. |
| `DATABASE_URL` | *Masukkan Internal Database URL* | Tempelkan **Internal Database URL** yang Anda salin dari langkah 1 (misal `postgresql://...`). |
| `RUN_MIGRATIONS` | `true` | Membuat Render otomatis menjalankan `php artisan migrate --force` saat aplikasi dijalankan pertama kali. |
| `SESSION_DRIVER` | `cookie` atau `database` | Gunakan `cookie` agar session tetap bertahan meskipun container di-restart. |

*Catatan: Render secara otomatis mengurai `DATABASE_URL` dan menghubungkannya dengan konfigurasi `pgsql` milik Laravel.*

---

## Langkah 4: Mulai Deployment

1. Setelah semua variabel terisi, klik **Create Web Service**.
2. Render akan mengunduh kode Anda, menjalankan build Docker (menginstal PHP, dependensi Composer, mengompilasi aset CSS/JS dengan Vite), dan mendeploy web server Nginx.
3. Anda dapat memantau proses build melalui tab **Logs** di halaman Web Service.
4. Setelah selesai, Render akan memberikan tautan publik (misal: `https://ta-dashboard.onrender.com`).
5. Salin tautan tersebut, lalu perbarui variabel lingkungan `APP_URL` di dashboard Render menggunakan tautan tersebut.

---

## Troubleshooting & Tips Penting

### 1. Ephemeral Storage (Penyimpanan Sementara)
Sistem file di Render bersifat dinamis dan akan di-reset setiap kali terjadi deploy ulang atau restart server. Berkas yang di-upload oleh user (seperti inovasi atau laporan) ke direktori lokal server akan hilang.
*   **Solusi**: Gunakan integrasi *Cloud Storage* seperti AWS S3 atau Cloudinary untuk menyimpan berkas upload secara permanen.

### 2. Memaksa HTTPS di Produksi
Render mengamankan koneksi dengan SSL/HTTPS secara default, namun terkadang Laravel perlu dipaksa agar selalu menggunakan skema HTTPS demi menghindari isu mixed-content.
Tambahkan kode ini pada method `boot()` di berkas [AppServiceProvider.php](file:///c:/Ta%20Dashboard/app/Providers/AppServiceProvider.php):

```php
if (env('APP_ENV') === 'production') {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```
