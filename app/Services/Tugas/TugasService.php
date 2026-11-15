<?php

declare(strict_types=1);

namespace App\Services\Tugas;

use App\Contracts\Tugas\TugasRepositoryInterface;
use App\Contracts\Tugas\TugasServiceInterface;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TugasService implements TugasServiceInterface
{
    public function __construct(
        protected TugasRepositoryInterface $tugasRepo
    ) {}

    public function getTugasForKabag(string $tab = 'daftar_tugas'): Collection
    {
        return $this->tugasRepo->getForKabag($tab);
    }

    public function getTugasForUser(int $userId, string $tab = 'tugas_saya'): Collection
    {
        return $this->tugasRepo->getForUser($userId, $tab);
    }

    public function getTugasDetailAndMarkAsSeen(int $tugasId, int $userId): Tugas
    {
        $tugas = $this->tugasRepo->findWithDetails($tugasId);

        if (! $tugas) {
            throw new InvalidArgumentException("Tugas dengan ID {$tugasId} tidak ditemukan.");
        }

        // Tandai sebagai sudah dilihat jika user adalah pemegang saat ini
        if ($tugas->pemegang_id === $userId && $tugas->dilihat_pemegang_at === null) {
            $this->markAsSeenByPemegang($tugasId, $userId);
            $tugas->dilihat_pemegang_at = Carbon::now('Asia/Jakarta');
        }

        return $tugas;
    }

    public function createTugas(array $data, int $creatorId): Tugas
    {
        $pic = User::findOrFail((int) $data['pic_id']);

        if (! $pic->is_active || $pic->role === Role::Kabag) {
            throw new InvalidArgumentException('PIC harus merupakan staf atau intern aktif dan bukan Kabag.');
        }

        $payload = [
            'judul' => (string) $data['judul'],
            'deadline' => $data['deadline'],
            'prioritas' => $data['prioritas'],
            'pic_id' => $pic->id,
            'kegiatan_id' => ! empty($data['kegiatan_id']) ? (int) $data['kegiatan_id'] : null,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $pic->id,
            'dilihat_pemegang_at' => null, // Badge Baru muncul untuk pemegang
            'created_by' => $creatorId,
        ];

        return $this->tugasRepo->create($payload);
    }

    public function createMultipleTugasForKegiatan(int $kegiatanId, array $tasksData, int $creatorId): array
    {
        return DB::transaction(function () use ($kegiatanId, $tasksData, $creatorId): array {
            $createdTasks = [];

            foreach ($tasksData as $taskData) {
                if (empty($taskData['judul'])) {
                    continue;
                }

                $taskData['kegiatan_id'] = $kegiatanId;
                $createdTasks[] = $this->createTugas($taskData, $creatorId);
            }

            return $createdTasks;
        });
    }

    public function updateTugas(Tugas $tugas, array $data): Tugas
    {
        $payload = [];

        if (array_key_exists('judul', $data)) {
            $payload['judul'] = (string) $data['judul'];
        }

        if (array_key_exists('deadline', $data)) {
            $payload['deadline'] = $data['deadline'];
        }

        if (array_key_exists('prioritas', $data)) {
            $payload['prioritas'] = $data['prioritas'];
        }

        if (array_key_exists('kegiatan_id', $data)) {
            $payload['kegiatan_id'] = ! empty($data['kegiatan_id']) ? (int) $data['kegiatan_id'] : null;
        }

        // Aturan pergantian PIC: hanya bisa jika tahap belum_dikumpulkan
        if (array_key_exists('pic_id', $data) && (int) $data['pic_id'] !== (int) $tugas->pic_id) {
            if ($tugas->tahap !== TahapTugas::BelumDikumpulkan) {
                throw new InvalidArgumentException('PIC hanya dapat diganti saat tugas belum dikumpulkan.');
            }

            $newPic = User::findOrFail((int) $data['pic_id']);
            if (! $newPic->is_active || $newPic->role === Role::Kabag) {
                throw new InvalidArgumentException('PIC harus merupakan staf atau intern aktif dan bukan Kabag.');
            }

            $payload['pic_id'] = $newPic->id;

            // Jika pemegang saat ini adalah PIC lama, alihkan pemegang ke PIC baru dan reset dilihat_pemegang_at
            if ($tugas->pemegang_id === $tugas->pic_id) {
                $payload['pemegang_id'] = $newPic->id;
                $payload['dilihat_pemegang_at'] = null;
            }
        }

        $this->tugasRepo->update($tugas, $payload);

        return $tugas->fresh(['pic', 'kegiatan', 'pemegang', 'staffPenerima']);
    }

    public function deleteTugas(Tugas $tugas): void
    {
        $this->tugasRepo->delete($tugas);
    }

    public function updateStatusByPic(Tugas $tugas, StatusTugas $status, int $userId): Tugas
    {
        if ($tugas->pic_id !== $userId) {
            throw new InvalidArgumentException('Hanya PIC yang dapat mengubah status tugas.');
        }

        $payload = [
            'status' => $status,
        ];

        if ($status === StatusTugas::Selesai) {
            $payload['selesai_at'] = Carbon::now('Asia/Jakarta');
        } else {
            $payload['selesai_at'] = null;
        }

        $this->tugasRepo->update($tugas, $payload);

        return $tugas->fresh();
    }

    public function markAsSeenByPemegang(int $tugasId, int $userId): void
    {
        $tugas = $this->tugasRepo->findById($tugasId);

        if ($tugas && $tugas->pemegang_id === $userId && $tugas->dilihat_pemegang_at === null) {
            $this->tugasRepo->update($tugas, [
                'dilihat_pemegang_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }

    public function getAvailablePics(): Collection
    {
        return $this->tugasRepo->getAvailablePics();
    }

    public function getAvailableKegiatan(): Collection
    {
        return $this->tugasRepo->getAvailableKegiatan();
    }

    public function getTugasByKegiatan(int $kegiatanId): Collection
    {
        return $this->tugasRepo->getByKegiatanId($kegiatanId);
    }
}
