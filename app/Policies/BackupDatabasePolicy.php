<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\BackupDB\BackupDatabase;
use App\Models\ManajemenUser\User;

class BackupDatabasePolicy
{
    /**
     * Hanya Kabag aktif yang dapat melihat halaman backup database.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag aktif yang dapat membuat backup database.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag aktif yang dapat mengunduh file backup database.
     */
    public function download(User $user, BackupDatabase $backup): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag aktif yang dapat menghapus file backup database.
     */
    public function delete(User $user, BackupDatabase $backup): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }
}
