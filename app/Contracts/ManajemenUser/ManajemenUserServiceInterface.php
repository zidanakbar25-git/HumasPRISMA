<?php

declare(strict_types=1);

namespace App\Contracts\ManajemenUser;

use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Collection;

interface ManajemenUserServiceInterface
{
    /**
     * Ambil seluruh user non-kabag yang dikelompokkan (staff dan intern), diurutkan alfabet nama.
     *
     * @return array{staff: Collection<int, User>, intern: Collection<int, User>}
     */
    public function getGroupedUsers(): array;

    /**
     * Buat user baru (staff / intern).
     *
     * @param array{name: string, username: string, password: string, role: string} $data
     */
    public function createUser(array $data): User;

    /**
     * Perbarui data user (staff / intern).
     *
     * @param array{name: string, username: string, password?: string|null, role: string} $data
     */
    public function updateUser(User $user, array $data): User;

    /**
     * Toggle status aktif/nonaktif user.
     */
    public function toggleActive(User $user): bool;

    /**
     * Toggle status trusted untuk user intern.
     */
    public function toggleTrusted(User $user, bool $isTrusted): bool;

    /**
     * Perbarui akun kabag (nama, username, dan password).
     *
     * @param array{name: string, username: string, password?: string|null} $data
     */
    public function updateKabag(User $kabag, array $data): User;
}
