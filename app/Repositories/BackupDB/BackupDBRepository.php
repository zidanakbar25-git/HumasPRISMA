<?php

declare(strict_types=1);

namespace App\Repositories\BackupDB;

use App\Contracts\BackupDB\BackupDBRepositoryInterface;
use App\Models\BackupDB\BackupDatabase;
use Illuminate\Database\Eloquent\Collection;

class BackupDBRepository implements BackupDBRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAll(): Collection
    {
        return BackupDatabase::with('creator')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?BackupDatabase
    {
        return BackupDatabase::with('creator')->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): BackupDatabase
    {
        return BackupDatabase::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(BackupDatabase $backup): bool
    {
        return (bool) $backup->delete();
    }
}
