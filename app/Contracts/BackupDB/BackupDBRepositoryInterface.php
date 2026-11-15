<?php

declare(strict_types=1);

namespace App\Contracts\BackupDB;

use App\Models\BackupDB\BackupDatabase;
use Illuminate\Database\Eloquent\Collection;

interface BackupDBRepositoryInterface
{
    /**
     * Ambil seluruh riwayat backup terurut dari yang terbaru.
     *
     * @return Collection<int, BackupDatabase>
     */
    public function getAll(): Collection;

    /**
     * Cari riwayat backup berdasarkan ID.
     */
    public function findById(int $id): ?BackupDatabase;

    /**
     * Simpan catatan riwayat backup database baru.
     *
     * @param array{nama_file: string, ukuran: int, created_by: int} $data
     */
    public function create(array $data): BackupDatabase;

    /**
     * Hapus catatan riwayat backup database.
     */
    public function delete(BackupDatabase $backup): bool;
}
