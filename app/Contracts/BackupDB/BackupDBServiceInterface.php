<?php

declare(strict_types=1);

namespace App\Contracts\BackupDB;

use App\Models\BackupDB\BackupDatabase;
use Illuminate\Database\Eloquent\Collection;

interface BackupDBServiceInterface
{
    /**
     * Ambil seluruh riwayat backup database.
     *
     * @return Collection<int, BackupDatabase>
     */
    public function getAllBackups(): Collection;

    /**
     * Buat file dump struktur + seluruh data dan catat di database.
     */
    public function createBackup(int $creatorId): BackupDatabase;

    /**
     * Hapus file backup fisik dan catatan di database.
     */
    public function deleteBackup(BackupDatabase $backup): bool;

    /**
     * Ambil path file fisik backup di server.
     */
    public function getFilePath(BackupDatabase $backup): string;

    /**
     * Cari backup database berdasarkan ID.
     */
    public function findById(int $id): ?BackupDatabase;
}
