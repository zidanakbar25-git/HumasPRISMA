# AGENT_RULES — Aturan Kerja, Struktur Folder, dan Fase

## 1. Aturan perilaku agent
1. Sebelum bekerja, baca file di `docs/` yang disebut prompt. Jika prompt bertentangan dengan docs, **berhenti dan tanya**.
2. Kerjakan **hanya fase yang diminta**. Jangan menyentuh fitur fase lain dan jangan refactor besar di luar scope.
3. Jangan menambah paket di luar daftar `PROJECT_CONTEXT.md` tanpa izin. Jangan memakai Tailwind/Bootstrap/CDN CSS.
4. Jangan hardcode rahasia; semua lewat `.env` (+ `.env.example` diperbarui).
5. Setiap fase harus lolos `php artisan migrate:fresh --seed` dan tidak merusak fase sebelumnya.
6. Jika ada keputusan yang tidak tercantum di docs, pilih opsi paling sederhana, **catat sebagai asumsi** di laporan akhir.
7. Jangan menjalankan perintah destruktif di luar proyek ini. Jangan commit/push kecuali diminta.
8. **Laporan akhir tiap fase** (singkat): file dibuat/diubah, perintah yang perlu dijalankan, langkah tes manual, asumsi, hal yang belum selesai. Lalu berhenti dan tunggu instruksi.

## 2. Struktur folder (wajib diikuti)
Fitur: `JadwalKegiatan`, `Tugas`, `ManajemenUser`, `BackupDB`, `ManajemenAkun`. Dipisah menurut role: `Kabag` dan `Staff` (halaman khusus intern juga di `Staff`). Bagian yang dipakai semua role ada di `Shared`.

```
app/
  Contracts/{Fitur}/              # interface (DIP)
  Enums/                          # Role, StatusTugas, TahapTugas, Prioritas, TipePengumpulan, SumberKegiatan
  Http/
    Controllers/
      Auth/AuthController.php
      Kabag/{JadwalKegiatan,Tugas,ManajemenUser,BackupDB}/...Controller.php
      Staff/{JadwalKegiatan,Tugas,ManajemenAkun}/...Controller.php
    Middleware/                   # EnsureRole, EnsureActive
    Requests/{Kabag|Staff}/{Fitur}/...Request.php
  Livewire/
    {Kabag|Staff|Shared}/{Fitur}/...
  Models/{JadwalKegiatan,Tugas,ManajemenUser,BackupDB}/...
  Policies/
  Repositories/{Fitur}/
  Services/{Fitur}/               # logika bisnis (mis. Tugas/TugasAlurService)
resources/views/
  layouts/app.blade.php
  components/                     # sidebar, modal, badge, toast, ikon, dst.
  Auth/login.blade.php
  Kabag/{JadwalKegiatan,Tugas,ManajemenUser,BackupDB}/index.blade.php
  Staff/{JadwalKegiatan,Tugas,ManajemenAkun}/index.blade.php
  livewire/{kabag|staff|shared}/{fitur}/...   # view komponen Livewire
public/css/ ...
routes/web.php                    # grup: /kabag/* (role kabag), /staff/* (role staff & intern)
```
Catatan:
- **Model dikelompokkan per fitur, bukan per role**, karena entitas yang sama dipakai semua role. Controller, request, dan view dipisah per role sesuai struktur di atas.
- Halaman utama tiap fitur selalu `index.blade.php` di foldernya. Controller bersifat tipis: otorisasi, ambil data ringan, return view yang menanam komponen Livewire.
- Komponen yang dipakai beberapa role ditaruh di `Livewire/Shared/...` dan perilakunya dibedakan lewat **Policy/Gate** (mis. `kegiatan.manage`), bukan `if ($role === ...)` yang tersebar.
- Gunakan penamaan folder PascalCase persis seperti di atas (server Linux peka huruf besar/kecil), dan view dirujuk konsisten, mis. `view('Kabag.JadwalKegiatan.index')`.
- Jika sebuah halaman intern khusus (mis. tab Revisi), tempatkan di folder `Staff/Tugas` dan batasi aksesnya dengan role.

## 3. Standar kode
- PHP 8.2+, `declare(strict_types=1)`, type hint dan return type di semua method publik.
- **OOP + SOLID secukupnya** (jangan over-engineering):
  - *S*: controller tipis, komponen Livewire hanya UI/state, logika bisnis di Service, query kompleks di Repository.
  - *O/L*: perilaku yang bervariasi (mis. strategi tujuan pengumpulan intern vs staff) lewat interface/kelas terpisah, bukan if-else panjang.
  - *I/D*: interface kecil di `Contracts`, di-bind di `AppServiceProvider`, di-inject lewat konstruktor.
- Validasi memakai Form Request atau aturan validasi Livewire; otorisasi memakai Policy; hindari N+1 (eager loading); perubahan multi-tabel dibungkus `DB::transaction`.
- Nama domain memakai istilah Indonesia sesuai docs (`Kegiatan`, `Tugas`, `Pengumpulan`); kode framework tetap Inggris. Komentar singkat berbahasa Indonesia hanya bila perlu.

## 4. Aturan Livewire
- Komponen kecil dan terfokus (kalender, daftar, popup rincian, form kegiatan, dst.). Beri `wire:key` pada item perulangan.
- Polling `wire:poll.8s` **hanya** pada daftar (bukan form/popup yang sedang diisi).
- Popup dikendalikan lewat state/event Livewire; jangan reload halaman.
- Upload file memakai `WithFileUploads` dengan batas 50 MB dan validasi mime (`jpg,jpeg,png`) di server.

## 5. Keamanan (wajib)
- Password hash bcrypt; CSRF aktif; throttle login; `EnsureActive` mencegah akun nonaktif.
- File pengumpulan dan backup disimpan di disk privat, diunduh lewat controller yang memeriksa otorisasi (Policy) — bukan URL publik.
- Link pengumpulan divalidasi sebagai URL http/https dan dirender dengan `rel="noopener noreferrer"`.
- Jangan simpan token Google. Jangan mencatat password/token di log.
- Semua aksi tulis memeriksa Policy di sisi server (jangan hanya menyembunyikan tombol).

## 6. Urutan fase
| Fase | Isi |
|---|---|
| 1 | Fondasi: struktur, auth, role, layout + sidebar, CSS dasar, halaman kosong per menu |
| 2 | Manajemen Akun (staff/intern) + Manajemen User (kabag) |
| 3 | Jadwal Kegiatan inti: kalender, daftar bulanan, riwayat, CRUD, PIC, titik merah/badge Baru |
| 4 | Export PDF riwayat kegiatan |
| 5 | Tugas A: tabel tugas, CRUD kabag, daftar, "Tugas Saya", status, tautan ke kegiatan, tambah tugas dari popup kegiatan |
| 6 | Tugas B: pengumpulan (file/link/teks), popup hasil untuk kabag (view, download, ZIP) |
| 7 | Tugas C: alur staff (Pengumpulan Intern, edit, kirim ke kabag, revisi) + tab Revisi intern |
| 8 | Tugas D: revisi/terima oleh kabag, "diedit oleh", trusted, Riwayat semua role |
| 9 | Impor Google Calendar |
| 10 | Backup Database |
| 11 | Penyempurnaan: responsif, animasi, polling, keamanan, tes alur, perbaikan bug |
