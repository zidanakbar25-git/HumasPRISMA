# DATABASE — Skema MySQL

Gunakan migration Laravel (satu migration per tabel, urut sesuai dependensi). Charset `utf8mb4`. Semua tabel punya `timestamps` kecuali dinyatakan lain. Enum dibuat sebagai PHP backed enum di `app/Enums` dan kolom `string`/`enum` di database.

## users
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| username | varchar(50) unique | |
| password | varchar | hash bcrypt |
| role | enum(`kabag`,`staff`,`intern`) | index |
| is_active | boolean default 1 | |
| is_trusted | boolean default 0 | Hanya bermakna untuk intern. Staff selalu dianggap trusted lewat accessor, tanpa mengubah kolom. |
| remember_token | varchar null | |

Seeder: 1 akun kabag (username/password dari `.env` atau default yang wajib diganti), plus akun contoh staff & intern **hanya di environment lokal**.

## kegiatan
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| judul | varchar(150) | |
| deskripsi | text null | |
| tanggal | date | index |
| waktu_mulai | time null | null = seharian |
| waktu_selesai | time null | |
| tempat | varchar(150) | |
| sumber | enum(`manual`,`google_calendar`) default `manual` | |
| google_event_id | varchar null | index; hanya penanda impor |
| created_by | FK users null on delete set null | |

## kegiatan_pic (pivot)
`kegiatan_id` FK cascade, `user_id` FK cascade, PK gabungan (`kegiatan_id`,`user_id`). Tanpa timestamps.

## kegiatan_dilihat
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| kegiatan_id | FK cascade | |
| user_id | FK cascade | |
| dilihat_at | datetime | |
Unique (`kegiatan_id`,`user_id`). Ada baris = sudah dilihat; tidak ada baris = belum (titik merah / badge Baru).

## tugas
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| judul | varchar(150) | |
| deadline | datetime | |
| prioritas | enum(`rendah`,`sedang`,`tinggi`) | |
| pic_id | FK users restrict | satu orang |
| kegiatan_id | FK kegiatan null on delete set null | |
| status | enum(`belum_dikerjakan`,`sedang_dikerjakan`,`selesai`) default `belum_dikerjakan` | |
| tahap | enum(`belum_dikumpulkan`,`menunggu_staff`,`menunggu_kabag`,`revisi`,`diterima`) default `belum_dikumpulkan` | index |
| pemegang_id | FK users null on delete set null | index; user yang harus bertindak |
| dilihat_pemegang_at | datetime null | null = badge Baru untuk pemegang |
| staff_penerima_id | FK users null on delete set null | staff yang menerima dari intern; null jika langsung ke kabag atau PIC staff |
| created_by | FK users null on delete set null | |
| selesai_at | datetime null | |
| diterima_at | datetime null | |

Index tambahan: (`pic_id`,`tahap`), (`pemegang_id`,`tahap`).

## pengumpulan
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| tugas_id | FK tugas cascade, unique | satu pengumpulan aktif per tugas |
| tipe | enum(`file`,`link`,`teks`) | tetap sepanjang hidup tugas |
| link | varchar(500) null | |
| teks | longText null | |
| dikumpulkan_at | datetime | diperbarui tiap kumpul ulang |

## pengumpulan_file
`id`, `pengumpulan_id` FK cascade, `path` (disk privat, folder `pengumpulan/{tugas_id}/`), `nama_asli`, `mime`, `ukuran` (bigint, byte).

## pengumpulan_edit_log
`id`, `pengumpulan_id` FK cascade, `user_id` FK, `aksi` enum(`tambah_file`,`hapus_file`,`edit_link`,`edit_teks`), `created_at` (tanpa `updated_at`). Dipakai untuk catatan "Diedit oleh ...".

## revisi_catatan
`id`, `tugas_id` FK cascade, `dari_user_id` FK, `ke_user_id` FK, `catatan` text (satu poin per baris), `created_at`. Catatan terbaru = yang aktif untuk revisi berjalan; yang lama tetap disimpan sebagai riwayat.

## backup_database
`id`, `nama_file`, `ukuran` bigint, `created_by` FK users null, `created_at`.

## Tabel bawaan Laravel
`sessions` (driver `database` atau `file`), `cache`, `jobs`, `migrations`. Queue: `sync`.

## Penyimpanan file
- Disk privat `local` (`storage/app/private/...`); jangan taruh di `public`.
- Backup SQL di `storage/app/backups/`.
- Batas upload: 50 MB per file. Atur juga `upload_max_filesize` dan `post_max_size` ≥ 50M di server, serta aturan temporary upload Livewire di `config/livewire.php`.
