<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\ManajemenUser\User;

class UserPolicy
{
    /**
     * Menentukan apakah user dapat melihat daftar manajemen user.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Kabag;
    }

    /**
     * Menentukan apakah user dapat menambah user baru.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::Kabag;
    }

    /**
     * Menentukan apakah user dapat mengubah user lain.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === Role::Kabag;
    }

    /**
     * Menentukan apakah user dapat mengubah status aktif/nonaktif user lain.
     * Kabag tidak bisa menonaktifkan akunnya sendiri.
     */
    public function toggleActive(User $user, User $model): bool
    {
        if ($user->role !== Role::Kabag) {
            return false;
        }

        // Kabag tidak dapat menonaktifkan dirinya sendiri
        return $user->id !== $model->id;
    }

    /**
     * Menentukan apakah user dapat mengubah status trusted (khusus intern).
     */
    public function toggleTrusted(User $user, User $model): bool
    {
        return $user->role === Role::Kabag && $model->role === Role::Intern;
    }

    /**
     * Menentukan apakah user dapat memperbarui profil/akun miliknya sendiri.
     */
    public function updateSelf(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
