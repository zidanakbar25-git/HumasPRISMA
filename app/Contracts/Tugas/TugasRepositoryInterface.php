<?php

declare(strict_types=1);

namespace App\Contracts\Tugas;

use App\Models\Tugas\Tugas;
use Illuminate\Database\Eloquent\Collection;

interface TugasRepositoryInterface
{
    /**
     * Ambil koleksi tugas untuk Kabag berdasarkan tab.
     * Tab: 'daftar_tugas', 'selesai', 'riwayat'.
     *
     * @return Collection<int, Tugas>
     */
    public function getForKabag(string $tab = 'daftar_tugas'): Collection;

    /**
     * Ambil koleksi tugas untuk Staff/Intern berdasarkan tab.
     * Tab: 'tugas_saya', 'revisi', 'pengumpulan_intern', 'riwayat'.
     *
     * @return Collection<int, Tugas>
     */
    public function getForUser(int $userId, string $tab = 'tugas_saya'): Collection;

    /**
     * Ambil tugas berdasarkan ID beserta seluruh relasi yang dibutuhkan.
     */
    public function findWithDetails(int $id): ?Tugas;

    /**
     * Ambil satu tugas berdasarkan ID atau null.
     */
    public function findById(int $id): ?Tugas;

    /**
     * Buat tugas baru.
     */
    public function create(array $data): Tugas;

    /**
     * Perbarui data tugas.
     */
    public function update(Tugas $tugas, array $data): bool;

    /**
     * Hapus tugas.
     */
    public function delete(Tugas $tugas): bool;

    /**
     * Daftar user yang dapat dipilih sebagai PIC (staff dan intern aktif, tanpa kabag).
     */
    public function getAvailablePics(): Collection;

    /**
     * Daftar kegiatan untuk dropdown tautan kegiatan.
     */
    public function getAvailableKegiatan(): Collection;

    /**
     * Ambil daftar tugas yang ditautkan ke suatu kegiatan.
     *
     * @return Collection<int, Tugas>
     */
    public function getByKegiatanId(int $kegiatanId): Collection;
}
