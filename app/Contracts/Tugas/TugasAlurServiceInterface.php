<?php

declare(strict_types=1);

namespace App\Contracts\Tugas;

use App\Models\ManajemenUser\User;
use App\Models\Tugas\Pengumpulan;
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\RevisiCatatan;
use App\Models\Tugas\Tugas;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface TugasAlurServiceInterface
{
    /**
     * Dapatkan daftar tujuan pengumpulan yang valid untuk seorang aktor (PIC).
     *
     * @return Collection<int, User>
     */
    public function getPilihanTujuanPengumpulan(User $aktor): Collection;

    /**
     * Eksekusi alur Kumpulkan tugas dari PIC.
     *
     * @param array<string, mixed> $payload ['tipe' => TipePengumpulan, 'link' => ?string, 'teks' => ?string, 'files' => ?array]
     * @param int|null $tujuanUserId (Wajib jika aktor adalah Intern, diabaikan jika aktor adalah Staff karena otomatis ke Kabag)
     */
    public function kumpulkan(Tugas $tugas, User $aktor, array $payload, ?int $tujuanUserId = null): Pengumpulan;

    /**
     * Staff penerima mengirimkan hasil pengumpulan ke Kabag.
     */
    public function kirimKeKabag(Tugas $tugas, User $aktor): Tugas;

    /**
     * Staff atau Kabag meminta revisi kepada PIC / staff.
     *
     * @param array<int, string> $poinRevisi
     */
    public function mintaRevisi(Tugas $tugas, User $aktor, array $poinRevisi, ?int $targetUserId = null): RevisiCatatan;

    /**
     * PIC mengumpulkan kembali tugas yang sedang dalam tahap revisi.
     */
    public function kumpulkanKembali(Tugas $tugas, User $aktor, ?int $tujuanUserId = null): Tugas;

    /**
     * Kabag menerima hasil pengumpulan tugas (memindahkan ke tahap diterima / riwayat).
     */
    public function terima(Tugas $tugas, User $aktor): Tugas;

    /**
     * Menambahkan file ke pengumpulan yang sudah ada (review staff / revisi intern).
     */
    public function tambahFile(Tugas $tugas, User $aktor, UploadedFile $file): PengumpulanFile;

    /**
     * Menghapus satu file dari pengumpulan (review staff / revisi intern).
     */
    public function hapusFile(Tugas $tugas, User $aktor, int $fileId): bool;

    /**
     * Mengedit link pada pengumpulan.
     */
    public function editLink(Tugas $tugas, User $aktor, string $link): void;

    /**
     * Mengedit teks narasi pada pengumpulan.
     */
    public function editTeks(Tugas $tugas, User $aktor, string $teks): void;
}
