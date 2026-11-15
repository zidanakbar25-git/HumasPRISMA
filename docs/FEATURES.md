# FEATURES — Spesifikasi Fitur dan Aturan Bisnis

Bagian: §1 Auth & Layout · §2 Jadwal Kegiatan · §3 Tugas · §4 Manajemen User · §5 Backup DB · §6 Manajemen Akun · §7 Impor Google Calendar

---

## §1 Auth & Layout
- Halaman login: username + password. Akun `is_active = false` tidak bisa login; jika dinonaktifkan saat sedang login, request berikutnya otomatis logout (middleware).
- Throttle login (maks 5 percobaan/menit per username+IP).
- Setelah login, redirect ke Jadwal Kegiatan sesuai role.
- Layout: sidebar kiri yang sama di semua halaman (isi menu beda per role), main content di kanan. Penanda menu aktif di sidebar **berpindah dengan animasi** saat pindah halaman.
- Semua perubahan data tampil tanpa refresh halaman (Livewire + polling ±8 detik pada daftar).

---

## §2 Jadwal Kegiatan
Header halaman: judul "Jadwal Kegiatan"; di bawahnya tab **Kalender | Kegiatan | Riwayat**; di sebelah kanan baris tab ada tombol aksi:
- Tab Kalender & Kegiatan: tombol **Tambah Kegiatan** (hanya kabag).
- Tab Riwayat: tombol **Export PDF** (menggantikan posisi tombol tambah; semua role).

### 2.1 Tab Kalender
- Layar dibagi dua: **kiri** kalender bulanan (pilih tanggal, navigasi bulan sebelumnya/berikutnya), **kanan** daftar kegiatan pada tanggal terpilih. Di layar kecil, keduanya bertumpuk (kalender di atas).
- Default tanggal terpilih = hari ini (WIB).
- Tanggal yang punya kegiatan diberi **titik** berwarna tema (hijau).
- **Aturan titik merah & badge "Baru"** (per user, tabel `kegiatan_dilihat`):
  1. Titik merah jika di tanggal itu ada ≥1 kegiatan yang belum pernah dilihat user tersebut.
  2. Kartu kegiatan yang belum pernah dilihat menampilkan badge **Baru**.
  3. Saat user **mengklik tanggal**, semua kegiatan di tanggal itu ditandai dilihat dan titik langsung berubah ke warna default. Badge Baru pada kartu yang sedang tampil tetap terlihat selama tanggal itu terbuka, lalu hilang segera saat kartunya diklik dan tidak muncul lagi di kunjungan berikutnya.
  4. Jika kegiatan baru ditambahkan di tanggal yang sudah pernah dibuka, titik kembali merah dan badge Baru hanya muncul di kartu kegiatan baru itu.
  5. Pada tab Kegiatan, badge Baru juga hilang saat kartu diklik (menandai kartu itu dilihat).
- **Kartu kegiatan**: persegi panjang berisi judul, waktu, tempat. Seluruh kartu bisa diklik → popup rincian.

### 2.2 Popup rincian kegiatan
Menampilkan judul, tanggal, waktu, tempat, deskripsi, daftar PIC, dan daftar tugas terkait.
- **Kabag**: tombol Edit & Hapus; pemilihan PIC multi-pilih dengan tombol **Pilih Semua** (hanya staff & intern aktif, tanpa kabag); tombol **Tambah Tugas** (lihat §2.5).
- **Staff/Intern**: hanya lihat.

### 2.3 Tab Kegiatan (daftar bulanan)
- Daftar kegiatan satu bulan, bulan bisa diganti (sebelumnya/berikutnya).
- Diurutkan berdasarkan tanggal lalu waktu. Dikelompokkan per tanggal: judul tanggal → kartu-kartu → judul tanggal berikutnya → kartu-kartu, dst.
- Kartu dan popup sama seperti §2.1/§2.2.

### 2.4 Tab Riwayat
- Daftar kegiatan dengan tanggal **sebelum hari ini** (patokan WIB), terbaru di atas. Kartu tetap bisa diklik untuk melihat rincian.
- **Export PDF**: klik tombol → popup dengan dua pilihan: **Berdasarkan bulan** atau **Berdasarkan rentang tanggal**.
  - Berdasarkan bulan: pilih bulan dan tahun.
  - Berdasarkan rentang tanggal: area pilih bulan berubah (animasi) menjadi dua input tanggal (dari, sampai).
- PDF berupa tabel: No, Judul Kegiatan, Tanggal, Tempat, PIC (nama-nama dipisah koma). Kop: "Rekap Kegiatan Biro Humas", periode, tanggal cetak. Jika tidak ada data, tampilkan pesan di popup dan jangan buat PDF kosong.

### 2.5 Form Tambah/Edit Kegiatan (kabag)
Popup berisi: judul*, tanggal*, waktu mulai (kosong = seharian), waktu selesai (opsional), tempat*, deskripsi, PIC (multi, Pilih Semua).
- Di bagian atas form (hanya saat tambah): tombol **Tambah dari Google Calendar** (lihat §7).
- Di bagian bawah form: tombol **Tambah Tugas**. Saat diklik, popup **mengembang ke bawah** dan menampilkan form tugas (komponen yang sama dengan halaman Tugas, §3.2) dengan kegiatan terkait terisi otomatis. Boleh menambah lebih dari satu tugas; semuanya disimpan bersama kegiatan dalam satu transaksi.
- Tombol Tambah Tugas juga tersedia di popup rincian (mode kabag) untuk kegiatan yang sudah ada.
- Menghapus kegiatan **tidak** menghapus tugas terkait (`kegiatan_id` menjadi kosong).

---

## §3 Tugas

### 3.1 Konsep status
- `status` (diubah manual oleh PIC): `belum_dikerjakan` → `sedang_dikerjakan` → `selesai`.
- `tahap` (posisi alur pengumpulan): `belum_dikumpulkan`, `menunggu_staff`, `menunggu_kabag`, `revisi`, `diterima`.
- `pemegang_id` = user yang saat ini harus bertindak. Awalnya PIC. Berubah mengikuti alur. Setiap kali pemegang berubah, `dilihat_pemegang_at` dikosongkan; badge **Baru** tampil untuk pemegang sampai ia mengklik item itu.
- Badge **Revisi** tampil selama `tahap = revisi`.
- Tidak ada penanda terlambat. Deadline tidak memblokir apa pun.

### 3.2 Kabag — halaman Tugas
Tab: **Daftar Tugas | Selesai | Riwayat**; tombol **Tambah Tugas** di sebelah tab.
- **Form tugas**: judul*, deadline*, prioritas* (rendah/sedang/tinggi), PIC* (satu orang, staff/intern aktif, tanpa kabag), kegiatan terkait (opsional; "Tidak terkait kegiatan" sebagai default).
- **Daftar Tugas**: tugas dengan tahap `belum_dikumpulkan`, `menunggu_staff`, `revisi`. Kartu persegi panjang: judul, deadline, PIC, badge status (belum/sedang/selesai), badge kegiatan terkait (jika ada), badge tambahan: "Di staff: <username>" bila `menunggu_staff`, "Revisi" bila `revisi`. Klik kartu → popup rincian dengan Edit/Hapus.
- **Selesai**: tugas dengan tahap `menunggu_kabag` (sudah sampai ke kabag). Begitu tugas sampai ke kabag, otomatis **hilang dari Daftar Tugas dan muncul di sini**. Kartu menampilkan petunjuk ringan (ikon + teks kecil "Lihat pengumpulan"), kursor pointer saat hover, dan badge Baru sampai dibuka. Klik → popup hasil pengumpulan (§3.5).
- **Riwayat**: tugas dengan tahap `diterima`, bisa dibuka (read-only).
- Aturan edit: PIC hanya bisa diganti jika tahap `belum_dikumpulkan`. Menghapus tugas menghapus pengumpulan, catatan revisi, dan file fisiknya (dengan konfirmasi).

### 3.3 Staff & Intern — halaman Tugas
Hanya menampilkan tugas milik sendiri (tanpa info PIC). Kartu: judul, deadline, prioritas, badge status, badge kegiatan terkait, Baru/Revisi.
- Tab **Tugas Saya**: tugas dengan `pemegang = saya` dan tahap `belum_dikumpulkan`.
- Tab **Revisi** (intern) / badge Revisi di Tugas Saya (staff): tugas `tahap = revisi` dengan pemegang saya.
- Tab **Pengumpulan Intern** (khusus staff): lihat §3.6.
- Tab **Riwayat**: tugas milik sendiri yang `diterima` (read-only).
- Klik kartu → popup rincian + ubah status (dropdown/segmented). Setelah status `selesai`, muncul tombol **Kumpulkan**.

### 3.4 Popup Kumpulkan
Pilih jenis pengumpulan:
- **File**: jpg/jpeg/png, boleh banyak file, maks 50 MB per file, validasi mime di server.
- **Link**: satu URL valid (mis. Google Drive).
- **Teks**: textarea (untuk narasi berita).

Pilih tujuan:
- **Intern**: daftar staff aktif. Jika `is_trusted = true`, akun **kabag muncul paling atas dan tetap di posisi itu**, di atas daftar staff.
- **Staff (PIC)**: otomatis ke kabag, tanpa pilihan.

Hasil: tugas diteruskan sesuai tujuan (lihat tabel transisi §3.7).

### 3.5 Kabag — popup hasil pengumpulan
- Menampilkan isi sesuai jenis: daftar file / link / teks.
- Setiap file punya tombol **View** dan **Download** (berdampingan).
- **View** membuka penampil gambar layar penuh dengan tombol/keyboard kiri-kanan untuk pindah ke gambar sebelumnya/berikutnya tanpa menutup penampil.
- Jika file > 1, muncul tombol **Download Semua (ZIP)** di bawah popup.
- Link: tombol buka di tab baru (`rel="noopener noreferrer"`). Teks: tampil lengkap.
- Catatan kecil **"Diedit oleh <username, username>"** jika ada log edit oleh staff.
- Kabag **tidak bisa** menambah/menghapus file. Jika jenis teks, kabag bisa **mengedit langsung di tempat**.
- Tombol **Revisi** → isi popup berganti menjadi form catatan revisi (daftar poin, bisa tambah/hapus baris) → **Kirim**. Revisi dikirim ke staff yang meneruskan (`staff_penerima_id`); jika pengumpulan langsung dari intern *trusted* maka ke intern; jika PIC adalah staff maka ke staff itu.
- Tombol **Terima** → tugas masuk Riwayat.
- File diakses lewat controller yang memeriksa otorisasi (bukan URL publik).

### 3.6 Staff — tab Pengumpulan Intern
Berisi tugas intern dengan `pemegang = staff ini` dan tahap `menunggu_staff`, ditambah tugas yang dikembalikan kabag (`tahap = revisi`, pemegang staff ini) dengan badge "Revisi dari Kabag". Klik → popup review:
- Lihat pengumpulan. Jika jenis file: staff bisa **menghapus file** dan **menambah file baru** (jpg/png). Jika link atau teks: staff **mengedit langsung di tempat**. Semua perubahan dicatat di `pengumpulan_edit_log`.
- **Kirimkan ke Kabag** → tahap `menunggu_kabag`.
- **Revisi** → isi popup berganti menjadi catatan revisi (daftar poin) → **Kirim ke Intern**.

### 3.7 Tabel transisi alur
| Aksi | Aktor | Dari | Ke |
|---|---|---|---|
| Kumpulkan | PIC intern | `belum_dikumpulkan`, status selesai | `menunggu_staff` (pemegang = staff dipilih) atau `menunggu_kabag` jika memilih kabag (trusted) |
| Kumpulkan | PIC staff | `belum_dikumpulkan`, status selesai | `menunggu_kabag` |
| Kirim ke Kabag | staff penerima | `menunggu_staff` atau `revisi` (pemegang staff) | `menunggu_kabag` |
| Revisi | staff penerima | `menunggu_staff` atau `revisi` (pemegang staff) | `revisi` (pemegang = intern PIC) |
| Kumpulkan Kembali | intern PIC | `revisi` | `menunggu_staff` (staff penerima sebelumnya) atau `menunggu_kabag` jika sebelumnya langsung ke kabag; bila staff penerima nonaktif, intern memilih ulang |
| Revisi | kabag | `menunggu_kabag` | `revisi` (pemegang = `staff_penerima_id` ?? PIC) |
| Kumpulkan Kembali | staff PIC | `revisi` | `menunggu_kabag` |
| Terima | kabag | `menunggu_kabag` | `diterima` (pemegang kosong) |

Saat intern menerima revisi: badge **Revisi** di kartu (tab Revisi), klik → catatan revisi + edit (hapus/tambah file, ganti link/teks) + tombol **Kumpulkan Kembali**.
Semua transisi diimplementasikan di satu service (`TugasAlurService`) dan dibungkus transaksi DB; aktor dan tahap asal divalidasi.

---

## §4 Manajemen User (Kabag)
1. **Form akun kabag** di bagian paling atas (lebih besar dari form biasa): username, password, tombol Simpan. Password ditampilkan sebagai titik-titik (placeholder, karena password tersimpan ter-hash dan tidak bisa ditampilkan); klik form untuk mengganti; tombol mata menampilkan/menyembunyikan teks yang sedang diketik. Perlu password lama saat mengganti password.
2. Tombol **Tambah User** → popup: username, password, role (staff/intern).
3. **Tabel user**: kolom Username | Role | Status | Trusted | Aksi (tombol edit).
   - Pengelompokan: semua **staff** di baris atas, **garis pembatas** (masih di tabel yang sama), lalu semua **intern** di bawah. Urut alfabet di tiap kelompok.
   - **Status**: toggle switch aktif/nonaktif. Nonaktif = tidak bisa login. Kabag tidak bisa menonaktifkan akunnya sendiri.
   - **Trusted**: checkbox. Untuk **staff**: tercentang, warna abu agak buram, tidak bisa diubah. Untuk **intern**: solid dan bisa dicentang/dilepas (tersimpan otomatis dengan umpan balik toast).
   - **Aksi**: Edit → popup dengan username, password (kosong = tidak diubah), role (hanya staff/intern).
4. Tidak ada tombol hapus akun. Username unik. Akun nonaktif tidak muncul di pilihan PIC dan daftar tujuan pengumpulan, tetapi data historisnya tetap utuh.

---

## §5 Backup Database (Kabag)
- Halaman berisi teks penjelasan dan tombol **Backup Database**.
- Klik tombol → sistem membuat dump **struktur + seluruh data** saat itu (`ifsnop/mysqldump-php`) ke `storage/app/backups/backup_YYYYMMDD_HHMMSS.sql`, mencatatnya di tabel `backup_database`. Tampilkan loading dan toast sukses/gagal.
- Tabel di bawahnya: No | Tanggal | Nama File | Download | Aksi. Kolom Download berisi tombol unduh; kolom Aksi berisi **Hapus** (dengan konfirmasi; file fisik ikut terhapus). **Tidak ada edit, tidak ada restore.**
- File backup tidak bisa diakses publik; unduhan lewat controller khusus kabag.

---

## §6 Manajemen Akun (Staff & Intern)
Halaman **Akun Saya**: ganti username dan/atau password sendiri tanpa persetujuan kabag. Password lama wajib saat mengganti password. Username unik.

---

## §7 Impor Google Calendar (Kabag, sekali impor)
Alur:
1. Kabag membuat kegiatan di Google Calendar secara manual.
2. Di form Tambah Kegiatan → **Tambah dari Google Calendar**.
3. Redirect ke Google OAuth (Socialite, scope `calendar.readonly`, `prompt=select_account` agar kabag bisa memilih akun Google mana pun).
4. Di callback, gunakan access token **hanya pada request itu** untuk memanggil Google Calendar REST API (`GET /calendar/v3/calendars/primary/events` dengan `timeMin` = awal hari ini, `timeMax` = +6 bulan, `singleEvents=true`, `orderBy=startTime`, `maxResults=50`) memakai HTTP client Laravel. **Token tidak disimpan** di database atau session.
5. Simpan daftar event minimal (id, judul, mulai, selesai, lokasi, deskripsi) di session flash, lalu kembali ke halaman Jadwal Kegiatan dan buka popup pilih event.
6. Kabag memilih **satu event** → **OK** → form Tambah Kegiatan terisi otomatis (judul, tanggal, waktu, tempat, deskripsi). Kabag memilih PIC (dan opsional menambah tugas) lalu menyimpan.
7. Disimpan dengan `sumber = google_calendar` dan `google_event_id` (hanya penanda; event yang sudah pernah diimpor ditandai "Sudah ditambahkan" di daftar). Setelah tersimpan, kegiatan **tidak terhubung lagi** ke Google; edit/hapus dilakukan di sistem.
8. Penanganan kondisi khusus: pengguna membatalkan izin → kembali dengan pesan; tidak ada event → pesan kosong; error API → pesan umum + log. Event seharian → tanpa jam; event multi-hari → hanya tanggal mulai.
9. Konfigurasi di `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (callback di dalam route kabag).
