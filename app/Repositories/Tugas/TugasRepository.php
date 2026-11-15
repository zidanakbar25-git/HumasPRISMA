<?php

declare(strict_types=1);

namespace App\Repositories\Tugas;

use App\Contracts\Tugas\TugasRepositoryInterface;
use App\Enums\Role;
use App\Enums\TahapTugas;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use Illuminate\Database\Eloquent\Collection;

class TugasRepository implements TugasRepositoryInterface
{
    /**
     * Ambil koleksi tugas untuk Kabag berdasarkan tab.
     */
    public function getForKabag(string $tab = 'daftar_tugas'): Collection
    {
        $query = Tugas::with(['pic', 'kegiatan', 'pemegang', 'staffPenerima', 'pengumpulan.files', 'pengumpulan.editLogs.user', 'revisiTerbaru.dariUser']);

        return match ($tab) {
            'selesai' => $query->where('tahap', TahapTugas::MenungguKabag)
                ->orderBy('updated_at', 'desc')
                ->get(),
            'riwayat' => $query->where('tahap', TahapTugas::Diterima)
                ->orderByDesc('diterima_at')
                ->orderByDesc('updated_at')
                ->get(),
            default => $query->whereIn('tahap', [
                TahapTugas::BelumDikumpulkan,
                TahapTugas::MenungguStaff,
                TahapTugas::Revisi,
            ])
                ->orderBy('deadline', 'asc')
                ->get(),
        };
    }

    /**
     * Ambil koleksi tugas untuk Staff/Intern berdasarkan tab.
     */
    public function getForUser(int $userId, string $tab = 'tugas_saya'): Collection
    {
        $query = Tugas::with(['pic', 'kegiatan', 'pemegang', 'staffPenerima', 'pengumpulan.files', 'pengumpulan.editLogs.user', 'revisiTerbaru.dariUser']);

        return match ($tab) {
            'revisi' => $query->where('pemegang_id', $userId)
                ->where('tahap', TahapTugas::Revisi)
                ->orderBy('deadline', 'asc')
                ->get(),
            'pengumpulan_intern' => $query->where('pemegang_id', $userId)
                ->where('pic_id', '!=', $userId)
                ->whereIn('tahap', [TahapTugas::MenungguStaff, TahapTugas::Revisi])
                ->orderBy('deadline', 'asc')
                ->get(),
            'riwayat' => $query->where('pic_id', $userId)
                ->where('tahap', TahapTugas::Diterima)
                ->orderByDesc('diterima_at')
                ->orderByDesc('updated_at')
                ->get(),
            default => $query->where('pemegang_id', $userId)
                ->where(function ($q) use ($userId) {
                    $q->where('tahap', TahapTugas::BelumDikumpulkan)
                      ->orWhere(function ($q2) use ($userId) {
                          $q2->where('pic_id', $userId)
                             ->where('tahap', TahapTugas::Revisi);
                      });
                })
                ->orderBy('deadline', 'asc')
                ->get(),
        };
    }

    public function findWithDetails(int $id): ?Tugas
    {
        return Tugas::with(['pic', 'kegiatan', 'pemegang', 'staffPenerima', 'creator', 'pengumpulan.files', 'pengumpulan.editLogs.user', 'revisiTerbaru.dariUser', 'revisiCatatan.dariUser'])->find($id);
    }

    public function findById(int $id): ?Tugas
    {
        return Tugas::find($id);
    }

    public function create(array $data): Tugas
    {
        return Tugas::create($data);
    }

    public function update(Tugas $tugas, array $data): bool
    {
        return $tugas->update($data);
    }

    public function delete(Tugas $tugas): bool
    {
        return (bool) $tugas->delete();
    }

    public function getAvailablePics(): Collection
    {
        return User::whereIn('role', [Role::Staff, Role::Intern])
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getAvailableKegiatan(): Collection
    {
        return Kegiatan::orderBy('tanggal', 'desc')
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();
    }

    public function getByKegiatanId(int $kegiatanId): Collection
    {
        return Tugas::where('kegiatan_id', $kegiatanId)
            ->with(['pic', 'pemegang'])
            ->orderBy('deadline', 'asc')
            ->get();
    }
}
