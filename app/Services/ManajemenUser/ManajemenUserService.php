<?php

declare(strict_types=1);

namespace App\Services\ManajemenUser;

use App\Contracts\ManajemenUser\ManajemenUserServiceInterface;
use App\Enums\Role;
use App\Models\ManajemenUser\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class ManajemenUserService implements ManajemenUserServiceInterface
{
    /**
     * Ambil seluruh user non-kabag yang dikelompokkan (staff dan intern), diurutkan alfabet nama.
     */
    public function getGroupedUsers(): array
    {
        $staff = User::where('role', Role::Staff->value)
            ->orderBy('name', 'asc')
            ->get();

        $intern = User::where('role', Role::Intern->value)
            ->orderBy('name', 'asc')
            ->get();

        return [
            'staff' => $staff,
            'intern' => $intern,
        ];
    }

    /**
     * Buat user baru (staff / intern).
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = Role::from($data['role']);

            if ($role === Role::Kabag) {
                throw new InvalidArgumentException('Tidak dapat menambah user dengan role Kabag.');
            }

            return User::create([
                'name' => trim($data['name']),
                'username' => trim($data['username']),
                'password' => Hash::make($data['password']),
                'role' => $role,
                'is_active' => true,
                'is_trusted' => false,
            ]);
        });
    }

    /**
     * Perbarui data user (staff / intern).
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $role = Role::from($data['role']);

            if ($role === Role::Kabag) {
                throw new InvalidArgumentException('Role tidak dapat diubah menjadi Kabag.');
            }

            $updateData = [
                'name' => trim($data['name']),
                'username' => trim($data['username']),
                'role' => $role,
            ];

            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            // Jika role diubah menjadi staff, reset is_trusted di kolom database ke false
            // (karena staff otomatis trusted via accessor)
            if ($role === Role::Staff) {
                $updateData['is_trusted'] = false;
            }

            $user->update($updateData);

            return $user->fresh();
        });
    }

    /**
     * Toggle status aktif/nonaktif user.
     */
    public function toggleActive(User $user): bool
    {
        if ($user->role === Role::Kabag) {
            throw new InvalidArgumentException('Akun Kabag tidak dapat dinonaktifkan.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return $user->is_active;
    }

    /**
     * Toggle status trusted untuk user intern.
     */
    public function toggleTrusted(User $user, bool $isTrusted): bool
    {
        if ($user->role !== Role::Intern) {
            throw new InvalidArgumentException('Status trusted hanya dapat diubah untuk role Intern.');
        }

        $user->is_trusted = $isTrusted;
        $user->save();

        return $user->is_trusted;
    }

    /**
     * Perbarui akun kabag (nama, username, dan password).
     */
    public function updateKabag(User $kabag, array $data): User
    {
        return DB::transaction(function () use ($kabag, $data): User {
            $updateData = [
                'name' => trim($data['name']),
                'username' => trim($data['username']),
            ];

            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $kabag->update($updateData);

            return $kabag->fresh();
        });
    }
}
