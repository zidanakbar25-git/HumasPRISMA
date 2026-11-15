<?php

declare(strict_types=1);

namespace App\Contracts\ManajemenAkun;

use App\Models\ManajemenUser\User;

interface ManajemenAkunServiceInterface
{
    /**
     * Perbarui data profil akun sendiri (nama, username, password opsional).
     *
     * @param array{name: string, username: string, current_password?: string|null, password?: string|null} $data
     */
    public function updateSelf(User $user, array $data): User;
}
