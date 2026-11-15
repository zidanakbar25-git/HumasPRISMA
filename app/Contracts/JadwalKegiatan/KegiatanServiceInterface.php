<?php

declare(strict_types=1);

namespace App\Contracts\JadwalKegiatan;

use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Collection;

interface KegiatanServiceInterface
{
    /**
     * Ambil data untuk tampilan tab Kalender.
     *
     * @return array{
     *     dots: array<string, string>,
     *     kegiatans: Collection<int, Kegiatan>,
     *     today: string
     * }
     */
    public function getKalenderViewData(int $year, int $month, string $selectedDate, int $userId): array;

    /**
     * Ambil data untuk tampilan tab Kegiatan (daftar bulanan).
     *
     * @return Collection<int, Kegiatan>
     */
    public function getDaftarBulananViewData(int $year, int $month, int $userId): Collection;

    /**
     * Ambil data untuk tampilan tab Riwayat (kegiatan sebelum hari ini WIB).
     *
     * @return Collection<int, Kegiatan>
     */
    public function getRiwayatViewData(int $userId): Collection;

    /**
     * Ambil daftar kegiatan riwayat untuk ekspor PDF berdasarkan bulan atau rentang tanggal.
     *
     * @param 'bulan'|'rentang' $mode
     * @param array<string, mixed> $params
     * @return Collection<int, Kegiatan>
     */
    public function getRiwayatForExport(string $mode, array $params): Collection;

    /**
     * Generate file PDF rekapitulasi riwayat kegiatan.
     *
     * @param Collection<int, Kegiatan> $kegiatans
     */
    public function generateRiwayatPdf(Collection $kegiatans, string $periodeText): \Barryvdh\DomPDF\PDF;

    /**
     * Ambil rincian kegiatan dan tandai sebagai sudah dilihat oleh user.
     */
    public function getDetailAndMarkAsSeen(int $kegiatanId, int $userId): ?Kegiatan;

    /**
     * Tandai semua kegiatan pada tanggal tertentu sebagai dilihat.
     */
    public function markDateAsSeen(string $tanggal, int $userId): void;

    /**
     * Tambah kegiatan baru.
     *
     * @param array<string, mixed> $data
     * @param list<int> $picIds
     */
    public function createKegiatan(array $data, array $picIds, int $creatorId): Kegiatan;

    /**
     * Tambah kegiatan baru beserta tugas terkait opsional dalam satu database transaction.
     *
     * @param array<string, mixed> $data
     * @param list<int> $picIds
     * @param array<int, array<string, mixed>> $draftTasks
     */
    public function createKegiatanWithTasks(array $data, array $picIds, int $creatorId, array $draftTasks = []): Kegiatan;

    /**
     * Perbarui kegiatan.
     *
     * @param array<string, mixed> $data
     * @param list<int> $picIds
     */
    public function updateKegiatan(Kegiatan $kegiatan, array $data, array $picIds): Kegiatan;

    /**
     * Hapus kegiatan.
     */
    public function deleteKegiatan(Kegiatan $kegiatan): bool;

    /**
     * Ambil daftar calon PIC (staff & intern aktif, tanpa kabag).
     *
     * @return Collection<int, User>
     */
    public function getAvailablePics(): Collection;
}
