# UI_GUIDE — Tema Hijau Army, Profesional

CSS biasa di `public/css/` (tanpa framework, tanpa build step). File: `base.css` (reset, variabel, tipografi), `layout.css` (sidebar, topbar, grid), `components.css` (tombol, kartu, badge, modal, tabel, form, toggle, toast), dan satu file per fitur bila perlu (`jadwal-kegiatan.css`, `tugas.css`, dst.). Semua warna dan ukuran lewat CSS variables.

## 1. Token warna
```css
:root {
  /* hijau army */
  --green-900: #2F3A1A;
  --green-800: #3D4A22;
  --green-700: #4B5D2A;   /* primer */
  --green-600: #5E7335;
  --green-500: #758C47;
  --green-200: #CFD9B8;
  --green-100: #E8EDDC;
  --green-50:  #F3F6EC;
  /* netral */
  --bg: #F6F7F2;
  --surface: #FFFFFF;
  --text: #1E2616;
  --muted: #6B7360;
  --border: #DDE2D0;
  /* semantik */
  --danger: #B3382C;  --danger-bg: #FBEAE8;
  --warning: #A8690F; --warning-bg: #FBF1DD;
  --success: #3F7D3A; --success-bg: #E6F3E4;
  --info: #3B6EA5;    --info-bg: #E6EEF7;
  /* bentuk & gerak */
  --radius-sm: 8px; --radius: 12px;
  --shadow-sm: 0 1px 2px rgba(30,38,22,.06);
  --shadow-md: 0 6px 18px rgba(30,38,22,.10);
  --ease: cubic-bezier(.4,0,.2,1);
  --t-fast: 150ms; --t: 250ms;
}
```
Prinsip: dominan netral hangat + hijau army sebagai aksen; hindari warna jenuh. Kontras teks minimal WCAG AA.

## 2. Tipografi dan spasi
- Font: **Inter** (Google Fonts) dengan fallback `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif`.
- Ukuran dasar 15–16px; judul halaman 24px/600; judul seksi 18px/600; teks kecil 13px.
- Skala spasi kelipatan 4px (4, 8, 12, 16, 24, 32).

## 3. Layout
- **Sidebar** kiri lebar 260px, latar `--green-900`, teks putih. Berisi logo/nama sistem, menu per role (ikon + label), dan blok user di bawah (nama lengkap, username, role, tombol Keluar).
- **Penanda menu aktif**: satu elemen "pill" absolut di dalam sidebar yang berpindah dengan transisi `transform`/`height` 300ms saat menu berganti. Agar animasi tetap terlihat antar halaman, gunakan Livewire `wire:navigate` dan `@persist('sidebar')`, lalu posisikan ulang pill pada event `livewire:navigated`.
- **Main content** di kanan sidebar: header halaman (judul, tab, tombol aksi) lalu konten, `max-width` ~1280px.
- **Responsif**:
  - ≥1024px: sidebar tetap tampil.
  - 640–1023px (tablet): sidebar menjadi drawer geser dengan tombol hamburger di topbar; kalender dan daftar kegiatan tetap berdampingan bila lebar ≥ 820px, jika tidak bertumpuk.
  - <640px (HP): satu kolom; tabel berubah jadi baris-kartu atau digulir horizontal di dalam kontainernya; popup menjadi hampir layar penuh (bottom sheet boleh).

## 4. Komponen
- **Tombol**: primer (hijau 700, hover 800), sekunder (outline hijau), bahaya (merah), ikon. Tinggi 40px, radius 8px. Hover: naik 1px + bayangan; active: skala 0.98; tombol disabled redup. Semua dengan transisi `--t-fast`.
- **Kartu kegiatan/tugas**: latar putih, border `--border`, radius 12px, padding 16px. Hover (jika bisa diklik): `translateY(-2px)`, `--shadow-md`, `cursor: pointer`. Kartu yang tidak bisa diklik tidak mendapat efek hover.
- **Badge** (pill 12px/600): Baru (merah muda, teks `--danger`), Revisi (`--warning-bg`), status — belum dikerjakan (abu), sedang dikerjakan (`--info-bg`), selesai (`--success-bg`); badge kegiatan terkait (`--green-100`); badge prioritas (rendah abu, sedang kuning, tinggi merah muda).
- **Modal/popup**: backdrop gelap transparan; kotak muncul dengan fade + scale (200ms); tutup via tombol X, klik backdrop, atau Esc; fokus berpindah ke dalam modal. Konten panjang menggulir di dalam modal. Perubahan isi modal (mis. beralih ke form revisi, expand form tugas, ganti mode export) memakai transisi tinggi/opasitas halus.
- **Tab**: garis bawah aktif yang bergeser mulus antar tab.
- **Toggle switch** (status user), **checkbox** kustom (trusted; versi staff: tercentang, abu, opacity .55, `cursor: not-allowed`).
- **Tabel**: header `--green-50`, baris hover `--green-50`, garis pembatas antar kelompok (staff / intern) berupa baris pemisah dengan label kecil.
- **Form**: label di atas input, input tinggi 40px, fokus ring hijau, pesan error merah di bawah field.
- **Toast** untuk sukses/gagal (kanan atas, hilang otomatis 3–4 detik).
- **Loading**: `wire:loading` memakai spinner kecil pada tombol; daftar memakai skeleton ringan saat pertama dimuat.
- **Kosong (empty state)**: ikon sederhana + satu kalimat.

## 5. Kalender
- Grid 7 kolom (Sen–Min atau Min–Sab; pilih Senin sebagai awal pekan). Hari ini diberi outline hijau; tanggal terpilih terisi `--green-700` teks putih; bulan lain redup.
- **Titik** 6px di bawah angka tanggal: hijau (`--green-600`) bila ada kegiatan, **merah** (`--danger`) bila ada yang belum dilihat; perubahan warna titik bertransisi 200ms.
- Ganti bulan dengan transisi geser/fade singkat.

## 6. Animasi
- Semua elemen interaktif punya transisi hover/active (150–250ms, `--ease`).
- Wajib animasi: penanda sidebar, buka/tutup modal, expand form tugas, tab, ganti mode export, toggle, perubahan warna titik kalender, munculnya/hilangnya kartu pada daftar (fade).
- Hormati `@media (prefers-reduced-motion: reduce)`: matikan animasi non-esensial.

## 7. Ikon
Gunakan SVG inline sederhana (set ikon garis, mis. gaya Lucide/Feather, ditulis langsung sebagai komponen Blade). Jangan memuat library ikon dari CDN.
