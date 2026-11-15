<?php

declare(strict_types=1);

namespace App\Services\JadwalKegiatan;

use App\Contracts\JadwalKegiatan\KegiatanRepositoryInterface;
use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Contracts\Tugas\TugasServiceInterface;
use App\Enums\Role;
use App\Enums\SumberKegiatan;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KegiatanService implements KegiatanServiceInterface
{
    public function __construct(
        protected KegiatanRepositoryInterface $repository
    ) {}

    public function getKalenderViewData(int $year, int $month, string $selectedDate, int $userId): array
    {
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $dots = $this->repository->getCalendarMonthDots($year, $month, $userId);
        $kegiatans = $this->repository->getKegiatanByTanggal($selectedDate, $userId);

        return [
            'dots' => $dots,
            'kegiatans' => $kegiatans,
            'today' => $today,
        ];
    }

    public function getDaftarBulananViewData(int $year, int $month, int $userId): Collection
    {
        return $this->repository->getKegiatanByBulan($year, $month, $userId);
    }

    public function getRiwayatViewData(int $userId): Collection
    {
        $todayWib = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        return $this->repository->getRiwayatKegiatan($todayWib, $userId);
    }

    public function getRiwayatForExport(string $mode, array $params): Collection
    {
        $yesterdayWib = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d');

        if ($mode === 'bulan') {
            $year = (int) ($params['year'] ?? Carbon::now('Asia/Jakarta')->year);
            $month = (int) ($params['month'] ?? Carbon::now('Asia/Jakarta')->month);

            return $this->repository->getRiwayatByMonth($year, $month, $yesterdayWib);
        }

        $startDate = (string) $params['start_date'];
        $endDate = (string) $params['end_date'];

        // Batasi tanggal akhir maksimal hari kemarin (WIB)
        if ($endDate > $yesterdayWib) {
            $endDate = $yesterdayWib;
        }

        return $this->repository->getRiwayatByDateRange($startDate, $endDate);
    }

    public function generateRiwayatPdf(Collection $kegiatans, string $periodeText): \Barryvdh\DomPDF\PDF
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $tanggalCetak = $nowWib->translatedFormat('d F Y, H:i') . ' WIB';

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap-kegiatan', [
            'kegiatans' => $kegiatans,
            'periodeText' => $periodeText,
            'tanggalCetak' => $tanggalCetak,
        ])->setPaper('a4', 'portrait');
    }

    public function getDetailAndMarkAsSeen(int $kegiatanId, int $userId): ?Kegiatan
    {
        $kegiatan = $this->repository->findWithDetails($kegiatanId);

        if ($kegiatan) {
            $this->repository->markSingleAsSeen($kegiatanId, $userId);
        }

        return $kegiatan;
    }

    public function markDateAsSeen(string $tanggal, int $userId): void
    {
        $this->repository->markDateAsSeen($tanggal, $userId);
    }

    public function createKegiatan(array $data, array $picIds, int $creatorId): Kegiatan
    {
        $payload = [
            'judul' => trim($data['judul']),
            'deskripsi' => ! empty($data['deskripsi']) ? trim($data['deskripsi']) : null,
            'tanggal' => $data['tanggal'],
            'waktu_mulai' => ! empty($data['waktu_mulai']) ? $data['waktu_mulai'] : null,
            'waktu_selesai' => ! empty($data['waktu_selesai']) ? $data['waktu_selesai'] : null,
            'tempat' => trim($data['tempat']),
            'sumber' => $data['sumber'] ?? SumberKegiatan::Manual->value,
            'google_event_id' => $data['google_event_id'] ?? null,
            'created_by' => $creatorId,
        ];

        return $this->repository->create($payload, $picIds);
    }

    public function createKegiatanWithTasks(array $data, array $picIds, int $creatorId, array $draftTasks = []): Kegiatan
    {
        return DB::transaction(function () use ($data, $picIds, $creatorId, $draftTasks): Kegiatan {
            $kegiatan = $this->createKegiatan($data, $picIds, $creatorId);

            if (! empty($draftTasks)) {
                $tugasService = app(TugasServiceInterface::class);
                $tugasService->createMultipleTugasForKegiatan($kegiatan->id, $draftTasks, $creatorId);
            }

            return $kegiatan;
        });
    }

    public function updateKegiatan(Kegiatan $kegiatan, array $data, array $picIds): Kegiatan
    {
        $payload = [
            'judul' => trim($data['judul']),
            'deskripsi' => ! empty($data['deskripsi']) ? trim($data['deskripsi']) : null,
            'tanggal' => $data['tanggal'],
            'waktu_mulai' => ! empty($data['waktu_mulai']) ? $data['waktu_mulai'] : null,
            'waktu_selesai' => ! empty($data['waktu_selesai']) ? $data['waktu_selesai'] : null,
            'tempat' => trim($data['tempat']),
        ];

        return $this->repository->update($kegiatan, $payload, $picIds);
    }

    public function deleteKegiatan(Kegiatan $kegiatan): bool
    {
        return $this->repository->delete($kegiatan);
    }

    public function getAvailablePics(): Collection
    {
        // Hanya staff & intern aktif, tanpa kabag
        return User::whereIn('role', [Role::Staff->value, Role::Intern->value])
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }
}
