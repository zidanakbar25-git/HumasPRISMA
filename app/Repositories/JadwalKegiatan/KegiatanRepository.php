<?php

declare(strict_types=1);

namespace App\Repositories\JadwalKegiatan;

use App\Contracts\JadwalKegiatan\KegiatanRepositoryInterface;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\JadwalKegiatan\KegiatanDilihat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KegiatanRepository implements KegiatanRepositoryInterface
{
    /**
     * Ambil kegiatan pada tanggal tertentu beserta status unread untuk user.
     */
    public function getKegiatanByTanggal(string $tanggal, int $userId): Collection
    {
        return Kegiatan::whereDate('tanggal', $tanggal)
            ->with(['pics' => fn ($q) => $q->orderBy('name', 'asc')])
            ->withExists(['views as is_dilihat' => fn ($q) => $q->where('user_id', $userId)])
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();
    }

    /**
     * Ambil kegiatan dalam satu bulan (tahun & bulan) terurut tanggal & waktu.
     */
    public function getKegiatanByBulan(int $year, int $month, int $userId): Collection
    {
        return Kegiatan::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with(['pics' => fn ($q) => $q->orderBy('name', 'asc')])
            ->withExists(['views as is_dilihat' => fn ($q) => $q->where('user_id', $userId)])
            ->orderBy('tanggal', 'asc')
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();
    }

    /**
     * Ambil kegiatan lampau sebelum hari ini (WIB) terurut tanggal terbaru.
     */
    public function getRiwayatKegiatan(string $todayDateWib, int $userId): Collection
    {
        return Kegiatan::whereDate('tanggal', '<', $todayDateWib)
            ->with(['pics' => fn ($q) => $q->orderBy('name', 'asc')])
            ->withExists(['views as is_dilihat' => fn ($q) => $q->where('user_id', $userId)])
            ->orderBy('tanggal', 'desc')
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai DESC')
            ->get();
    }

    /**
     * Ambil kegiatan riwayat pada bulan dan tahun tertentu (maksimal hari kemarin WIB).
     */
    public function getRiwayatByMonth(int $year, int $month, string $yesterdayWib): Collection
    {
        return Kegiatan::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->whereDate('tanggal', '<=', $yesterdayWib)
            ->with(['pics' => fn ($q) => $q->orderBy('name', 'asc')])
            ->orderBy('tanggal', 'asc')
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();
    }

    /**
     * Ambil kegiatan riwayat pada rentang tanggal tertentu (maksimal hari kemarin WIB).
     */
    public function getRiwayatByDateRange(string $startDate, string $endDate): Collection
    {
        return Kegiatan::whereBetween('tanggal', [$startDate, $endDate])
            ->with(['pics' => fn ($q) => $q->orderBy('name', 'asc')])
            ->orderBy('tanggal', 'asc')
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();
    }

    /**
     * Ambil status titik kalender untuk tanggal dalam satu bulan.
     */
    public function getCalendarMonthDots(int $year, int $month, int $userId): array
    {
        $kegiatans = Kegiatan::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->withExists(['views as is_dilihat' => fn ($q) => $q->where('user_id', $userId)])
            ->get();

        $dots = [];

        foreach ($kegiatans as $kegiatan) {
            $dateKey = Carbon::parse($kegiatan->tanggal)->format('Y-m-d');

            if (! isset($dots[$dateKey])) {
                $dots[$dateKey] = 'green';
            }

            // Jika ada minimal 1 kegiatan yang belum dilihat, titik menjadi merah
            if (! $kegiatan->is_dilihat) {
                $dots[$dateKey] = 'red';
            }
        }

        return $dots;
    }

    /**
     * Cari detail kegiatan beserta PIC dan relasi lainnya.
     */
    public function findWithDetails(int $id): ?Kegiatan
    {
        return Kegiatan::with([
            'pics' => fn ($q) => $q->orderBy('name', 'asc'),
            'creator',
            'tugas' => fn ($q) => $q->with('pic')->orderBy('deadline', 'asc'),
        ])->find($id);
    }

    /**
     * Simpan kegiatan baru beserta PIC.
     */
    public function create(array $data, array $picIds): Kegiatan
    {
        return DB::transaction(function () use ($data, $picIds): Kegiatan {
            $kegiatan = Kegiatan::create($data);

            if (! empty($picIds)) {
                $kegiatan->pics()->sync($picIds);
            }

            return $kegiatan;
        });
    }

    /**
     * Perbarui kegiatan beserta PIC.
     */
    public function update(Kegiatan $kegiatan, array $data, array $picIds): Kegiatan
    {
        return DB::transaction(function () use ($kegiatan, $data, $picIds): Kegiatan {
            $kegiatan->update($data);
            $kegiatan->pics()->sync($picIds);

            return $kegiatan->fresh();
        });
    }

    /**
     * Hapus kegiatan.
     */
    public function delete(Kegiatan $kegiatan): bool
    {
        return (bool) $kegiatan->delete();
    }

    /**
     * Tandai seluruh kegiatan pada tanggal tertentu sebagai sudah dilihat oleh user.
     */
    public function markDateAsSeen(string $tanggal, int $userId): void
    {
        $kegiatanIds = Kegiatan::whereDate('tanggal', $tanggal)->pluck('id');
        $now = Carbon::now('Asia/Jakarta');

        foreach ($kegiatanIds as $kegiatanId) {
            KegiatanDilihat::firstOrCreate(
                ['kegiatan_id' => $kegiatanId, 'user_id' => $userId],
                ['dilihat_at' => $now]
            );
        }
    }

    /**
     * Tandai satu kegiatan sebagai sudah dilihat oleh user.
     */
    public function markSingleAsSeen(int $kegiatanId, int $userId): void
    {
        KegiatanDilihat::firstOrCreate(
            ['kegiatan_id' => $kegiatanId, 'user_id' => $userId],
            ['dilihat_at' => Carbon::now('Asia/Jakarta')]
        );
    }
}
