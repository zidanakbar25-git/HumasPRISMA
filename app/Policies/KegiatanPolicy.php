<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;

class KegiatanPolicy
{
    /**
     * Semua user aktif (Kabag, Staff, Intern) dapat melihat jadwal kegiatan.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Kegiatan $kegiatan): bool
    {
        return $user->is_active;
    }

    /**
     * Hanya Kabag yang dapat menambah kegiatan.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag yang dapat mengubah kegiatan.
     */
    public function update(User $user, Kegiatan $kegiatan): bool
    {
        return $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag yang dapat menghapus kegiatan.
     */
    public function delete(User $user, Kegiatan $kegiatan): bool
    {
        return $user->role === Role::Kabag;
    }
}
