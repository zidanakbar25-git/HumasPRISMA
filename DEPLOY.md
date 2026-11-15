# Panduan Deployment ke Shared Hosting — Sistem Biro Humas

Dokumen ini berisi panduan teknis untuk menggelar (*deploy*) aplikasi web internal Biro Humas pada lingkungan **Shared Hosting** murah berbasis cPanel/DirectAdmin/Plesk.

---

## 1. Prasyarat Server & Ekstensi PHP

- **Versi PHP**: PHP **8.2** atau **8.3** (PHP 8.2+ wajib).
- **MySQL / MariaDB**: MySQL 5.7+ / 8.0+ atau MariaDB 10.3+.
- **Ekstensi PHP Wajib**:
  - `pdo`, `pdo_mysql` (koneksi database)
  - `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` (standar Laravel)
  - `fileinfo` (validasi MIME berkas pengumpulan di server)
  - `gd` (pemrosesan gambar / PDF dompdf)
  - `zip` (pembuatan arsip ZIP unduh berkas pengumpulan)
  - `curl` (pemanggilan Google Calendar REST API via Socialite / HTTP Client)

### Konfigurasi `php.ini` (Melalui menu *MultiPHP INI Editor* di cPanel):
```ini
upload_max_filesize = 50M
post_max_size = 55M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```
> **PENTING**: Batas upload berkas pengumpulan tugas adalah 50 MB per file. Nilai `post_max_size` wajib diatur lebih besar dari `upload_max_filesize`.

---

## 2. Struktur Folder & Pengaturan Document Root

Agar kode aplikasi dan berkas privat terlindungi secara optimal:

1. **Letakkan berkas aplikasi di luar `public_html`**:
   - Buat direktori aplikasi, misalnya: `/home/username/app_humas/`.
   - Unggah seluruh folder proyek ke direktori tersebut (`app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `storage/`, `vendor/`, dll.).
2. **Document Root Web Server**:
   - Arahkan **Document Root** domain/subdomain ke folder `public/`, misalnya: `/home/username/app_humas/public`.
   - Jika shared hosting tidak mengizinkan pergantian Document Root dari `public_html`:
     - Pindahkan seluruh isi folder `public/*` ke dalam `public_html/`.
     - Sesuaikan file `public_html/index.php` untuk memanggil path `vendor/autoload.php` dan `bootstrap/app.php` yang berada di `/home/username/app_humas/`.

---

## 3. Hak Akses & Tautan Storage (Symlink)

1. **Hak Akses Folder (Permissions)**:
   Pastikan folder-folder berikut memiliki izin tulis (*writeable*) oleh web server:
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```
2. **Tautan Simbolik Storage**:
   Jalankan perintah berikut via terminal SSH atau cron job sekali jalan:
   ```bash
   php artisan storage:link
   ```
   > **Keamanan Berkas**: 
   > - Berkas pengumpulan tugas disimpan di direktori privat: `storage/app/private/pengumpulan/`.
   > - Berkas backup database disimpan di direktori privat: `storage/app/backups/`.
   > - Kedua direktori ini **tidak dapat diakses via URL publik** dan wajib melewati controller berotorisasi (`PengumpulanFileController` & `BackupDBController`).

---

## 4. Konfigurasi Environment Produksi (`.env`)

Buat atau edit file `.env` di server hosting dengan konfigurasi berikut:

```env
APP_NAME="Biro Humas"
APP_ENV=production
APP_KEY=base64:... (hasil dari php artisan key:generate)
APP_DEBUG=false
APP_URL=https://humas.namadomain.go.id

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_cpanel_humasdb
DB_USERNAME=nama_cpanel_humasuser
DB_PASSWORD="PasswordKuatDatabase123!"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
CACHE_STORE=database

# Integrasi Google Calendar OAuth
GOOGLE_CLIENT_ID="xxxx-xxxx.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="GOCSPX-xxxx"
GOOGLE_REDIRECT_URI="https://humas.namadomain.go.id/kabag/google-calendar/callback"
```

---

## 5. Konfigurasi SSL / HTTPS & Google OAuth

1. **Aktifkan Sertifikat SSL**:
   - Aktifkan SSL/TLS gratis (Let's Encrypt / cPanel AutoSSL) agar website berjalan dengan protokol `https://`.
   - Pastikan seluruh lalu lintas HTTP diarahkan otomatis ke HTTPS melalui file `public/.htaccess`:
     ```apache
     RewriteEngine On
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     ```
2. **Google Cloud Console OAuth 2.0**:
   - Buka **Google Cloud Console** > *APIs & Services* > *Credentials*.
   - Pada OAuth 2.0 Client IDs, masukkan URL Redirect persis:
     `https://humas.namadomain.go.id/kabag/google-calendar/callback`
   - Pastikan **Google Calendar API** berstatus *Enabled* pada project tersebut.

---

## 6. Langkah-Langkah Deployment (Artisan Commands)

Jalankan perintah-perintah berikut melalui terminal SSH di server hosting:

```bash
# 1. Masuk ke direktori aplikasi
cd /home/username/app_humas

# 2. Pasang dependensi PHP mode produksi (tanpa dependensi testing/dev)
composer install --no-dev --optimize-autoloader

# 3. Generate application key (jika belum ada di .env)
php artisan key:generate --force

# 4. Jalankan migrasi database
php artisan migrate --force

# 5. Jalankan seeder akun awal (hanya saat pertama kali pasang)
php artisan db:seed --force

# 6. Buat tautan storage
php artisan storage:link

# 7. Optimalkan performa aplikasi dengan caching konfigurasi & rute
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 7. Pembaruan Aplikasi di Kemudian Hari

Bila melakukan pembaruan berkas kode di hosting:
```bash
# Bersihkan cache lama
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Jalankan migrasi baru (bila ada)
php artisan migrate --force

# Cache kembali untuk produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
