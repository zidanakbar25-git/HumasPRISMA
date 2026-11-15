<?php

declare(strict_types=1);

namespace App\Contracts\JadwalKegiatan;

use App\Models\JadwalKegiatan\Kegiatan;
use Illuminate\Database\Eloquent\Collection;

interface KegiatanRepositoryInterface
{
    /**
     * Ambil kegiatan pada tanggal tertentu beserta status unread untuk user.
     *
     * @return Collection<int, Kegiatan>
     */
    public function getKegiatanByTanggal(string $tanggal, int $userId): Collection;

    /**
     * Ambil kegiatan dalam satu bulan (tahun & bulan) terurut tanggal & waktu.
     *
     * @return Collection<int, Kegiatan>
     */
    public function getKegiatanByBulan(int $year, int $month, int $userId): Collection;

    /**
     * Ambil kegiatan lampau sebelum hari ini (WIB) terurut tanggal terbaru.
     *
     * @return Collection<int, Kegiatan>
     */
    public function getRiwayatKegiatan(string $todayDateWib, int $userId): Collection;

    /**
     * Ambil kegiatan riwayat pada bulan dan tahun tertentu (maksimal hari kemarin WIB).
     *
     * @return Collection<int, Kegiatan>
     */
    public function getRiwayatByMonth(int $year, int $month, string $yesterdayWib): Collection;

    /**
     * Ambil kegiatan riwayat pada rentang tanggal tertentu (maksimal hari kemarin WIB).
     *
     * @return Collection<int, Kegiatan>
     */
    public function getRiwayatByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Ambil status titik kalender untuk tanggal dalam satu bulan:
     * 'red' jika ada kegiatan yang belum dilihat user,
     * 'green' jika semua kegiatan pada tanggal tersebut sudah dilihat.
     *
     * @return array<string, string> Key: 'YYYY-MM-DD', Value: 'green'|'red'
     */
    public function getCalendarMonthDots(int $year, int $month, int $userId): array;

    /**
     * Cari detail kegiatan beserta PIC dan relasi lainnya.
     */
    public function findWithDetails(int $id): ?Kegiatan;

    /**
     * Simpan kegiatan baru beserta PIC.
     *
     * @param array<string, mixed> $data
     * @param list<int> $picIds
     */
    public function create(array $data, array $picIds): Kegiatan;

    /**
     * Perbarui kegiatan beserta PIC.
     *
     * @param array<string, mixed> $data
     * @param list<int> $picIds
     */
    public function update(Kegiatan $kegiatan, array $data, array $picIds): Kegiatan;

    /**
     * Hapus kegiatan.
     */
    public function delete(Kegiatan $kegiatan): bool;

    /**
     * Tandai seluruh kegiatan pada tanggal tertentu sebagai sudah dilihat oleh user.
     */
    public function markDateAsSeen(string $tanggal, int $userId): void;

    /**
     * Tandai satu kegiatan sebagai sudah dilihat oleh user.
     */
    public function markSingleAsSeen(int $kegiatanId, int $userId): void;
}
