# PROJECT_CONTEXT — Sistem Alur Kerja Biro Humas

Dokumen ini adalah sumber kebenaran utama. Baca bersama `FEATURES.md`, `DATABASE.md`, `UI_GUIDE.md`, `AGENT_RULES.md`.

## 1. Tujuan
Aplikasi web internal Biro Humas dengan dua fitur utama:
1. **Jadwal Kegiatan** — kalender, daftar bulanan, riwayat, export PDF, impor sekali dari Google Calendar.
2. **Penugasan** — kabag memberi tugas; PIC mengerjakan, mengumpulkan, lalu melewati alur review dan revisi berjenjang (intern → staff → kabag).

## 2. Role dan hierarki
Kabag (admin) > Staff > Intern. Login memakai **username + password** (tanpa email). Nama pengguna disimpan di kolom `name` sebagai identitas tampilan di seluruh UI (pilihan PIC, kartu, tabel, dan laporan PDF).

| Role | Ringkasan hak akses |
|---|---|
| Kabag | Satu akun (dibuat seeder). CRUD kegiatan & tugas, review hasil akhir, manajemen user (tambah/edit/nonaktifkan, tidak ada hapus), backup database. |
| Staff | Lihat jadwal, kerjakan tugas sendiri, review & perbaiki pengumpulan intern, teruskan ke kabag atau minta revisi. Otomatis "trusted". |
| Intern | Lihat jadwal, kerjakan tugas sendiri, kumpulkan ke staff (atau ke kabag jika diberi akses *trusted*), revisi. |

Halaman khusus intern diletakkan di folder `Staff/` (lihat `AGENT_RULES.md`).

## 3. Tech stack (final)
- Laravel versi stabil terbaru yang kompatibel dengan PHP 8.2+, MySQL.
- Blade + **Livewire 4** (`livewire/livewire ^4.4`, boleh memakai Alpine bawaan Livewire).
- **CSS biasa** di `public/css/` — tanpa Tailwind/Bootstrap/framework CSS, tanpa build step Node untuk CSS.
- Paket tambahan yang diizinkan: `livewire/livewire`, `laravel/socialite` (hanya untuk Google OAuth saat impor kalender), `barryvdh/laravel-dompdf`, `ifsnop/mysqldump-php`. Paket lain harus minta izin dulu.
- Zona waktu aplikasi: `Asia/Jakarta` (WIB). Locale: `id`.
- Hosting target: shared hosting murah. Karena itu: tanpa WebSocket, tanpa queue worker (`QUEUE_CONNECTION=sync`), update tanpa refresh memakai polling Livewire.

## 4. Di luar scope (jangan dibuat)
Aplikasi desktop, PWA, notifikasi email/push, WebSocket, upload video (mp4), restore database, sinkronisasi berkelanjutan dengan Google Calendar, login via Google.

## 5. Keputusan final dari pemilik proyek
1. Google Calendar: **impor satu kali**, setelah itu data kegiatan tidak terhubung lagi ke Google; perubahan dilakukan di sistem. Akun Google yang dipakai = akun yang dipilih kabag saat popup Google muncul (OAuth).
2. PIC **kegiatan** boleh lebih dari satu (ada tombol pilih semua). PIC **tugas** hanya satu orang. Kabag tidak pernah muncul di pilihan PIC.
3. Status tugas diubah manual oleh PIC. Boleh diselesaikan walau lewat deadline; tidak ada penanda terlambat.
4. Hanya kabag yang menambah kegiatan. Kabag CRUD kegiatan dan tugas.
5. Akun user tidak bisa dihapus, hanya dinonaktifkan.
6. Penanda "Baru" dan "Revisi" menempel pada item terkait, bukan pada halaman/menu. Penanda "Baru" hilang atau kembali default saat item diklik untuk dilihat (klik tanggal membuat titik kembali default/hijau; klik kartu tugas membuat badge Baru hilang).
7. Pengumpulan: file **jpg/jpeg/png** (maks **50 MB per file**), **link** (mis. Google Drive), atau **teks**. mp4 tidak didukung.
8. Tabel backup tanpa aksi edit.
9. Kolom `name` pada tabel `users` digunakan untuk seluruh tampilan identitas di UI (pilihan PIC, penanda PIC, kartu, tabel, PDF), sedangkan `username` hanya sebagai identitas akun/login.
10. PIC tugas pada popup kegiatan dapat dipilih dari seluruh staff/intern aktif, tidak terbatas pada daftar PIC kegiatan tersebut.
11. Jika staff penerima dinonaktifkan saat kabag hendak meminta revisi tugas intern, kabag memilih staff pengganti aktif.
12. Input rentang tanggal pada export PDF di tab Riwayat dibatasi maksimal hari kemarin (sebelum hari ini WIB).

## 6. Asumsi yang dibuat penyusun dokumen (boleh diubah pemilik proyek)
- Kabag punya aksi **Terima** pada hasil pengumpulan; setelah diterima tugas masuk Riwayat.
- Mengganti password (semua role) mewajibkan password lama. Password minimal 6 karakter.
- Impor Google Calendar memilih **satu event per impor** dan mengisi form tambah kegiatan (judul, tanggal, waktu, tempat, deskripsi); kabag melengkapi PIC lalu menyimpan.
- Event Google seharian disimpan tanpa jam (`waktu_mulai` kosong, tampil "Seharian"). Event multi-hari hanya diimpor pada tanggal mulai.
- Model Eloquent dikelompokkan per **fitur** (bukan per role) karena entitasnya dipakai bersama; controller dan view dipisah per role.
- Export PDF mencakup kegiatan yang termasuk Riwayat (tanggal sebelum hari ini WIB) pada periode terpilih, tersedia untuk semua role.
- Tujuan pengumpulan ulang (revisi) otomatis ke penerima sebelumnya.

## 7. Menu sidebar per role
- **Kabag:** Jadwal Kegiatan, Tugas, Manajemen User, Backup Database. (Ganti username/password kabag ada di atas halaman Manajemen User.)
- **Staff:** Jadwal Kegiatan, Tugas (tab: Tugas Saya, Pengumpulan Intern, Riwayat), Akun Saya.
- **Intern:** Jadwal Kegiatan, Tugas (tab: Tugas Saya, Revisi, Riwayat), Akun Saya.

Sidebar sama di semua halaman (komponen bersama); hanya isi menu yang berbeda per role. Konten fitur selalu di area main content di kanan sidebar.
