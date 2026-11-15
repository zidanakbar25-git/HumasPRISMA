<?php

declare(strict_types=1);

namespace App\Contracts\Tugas;

use App\Enums\StatusTugas;
use App\Models\Tugas\Tugas;
use Illuminate\Database\Eloquent\Collection;

interface TugasServiceInterface
{
    /**
     * Ambil daftar tugas untuk Kabag sesuai tab aktif.
     *
     * @return Collection<int, Tugas>
     */
    public function getTugasForKabag(string $tab = 'daftar_tugas'): Collection;

    /**
     * Ambil daftar tugas untuk user (Staff/Intern) sesuai tab aktif.
     *
     * @return Collection<int, Tugas>
     */
    public function getTugasForUser(int $userId, string $tab = 'tugas_saya'): Collection;

    /**
     * Ambil detail tugas dan tandai sudah dilihat jika user adalah pemegang saat ini.
     */
    public function getTugasDetailAndMarkAsSeen(int $tugasId, int $userId): Tugas;

    /**
     * Buat satu tugas baru oleh Kabag.
     */
    public function createTugas(array $data, int $creatorId): Tugas;

    /**
     * Buat banyak tugas untuk satu kegiatan dalam satu database transaction.
     *
     * @param array<int, array<string, mixed>> $tasksData
     * @return array<int, Tugas>
     */
    public function createMultipleTugasForKegiatan(int $kegiatanId, array $tasksData, int $creatorId): array;

    /**
     * Perbarui data tugas (judul, deadline, prioritas, kegiatan_id, pic_id jika diizinkan).
     */
    public function updateTugas(Tugas $tugas, array $data): Tugas;

    /**
     * Hapus tugas.
     */
    public function deleteTugas(Tugas $tugas): void;

    /**
     * Ubah status manual tugas oleh PIC (belum_dikerjakan, sedang_dikerjakan, selesai).
     */
    public function updateStatusByPic(Tugas $tugas, StatusTugas $status, int $userId): Tugas;

    /**
     * Tandai tugas telah dilihat oleh pemegang (menghilangkan badge Baru).
     */
    public function markAsSeenByPemegang(int $tugasId, int $userId): void;

    /**
     * Ambil daftar user aktif yang bisa dipilih sebagai PIC (staff dan intern).
     */
    public function getAvailablePics(): Collection;

    /**
     * Ambil daftar kegiatan untuk opsi tautan kegiatan.
     */
    public function getAvailableKegiatan(): Collection;

    /**
     * Ambil daftar tugas terkait suatu kegiatan.
     *
     * @return Collection<int, Tugas>
     */
    public function getTugasByKegiatan(int $kegiatanId): Collection;
}
