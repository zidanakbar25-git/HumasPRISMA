<?php

declare(strict_types=1);

namespace App\Services\ManajemenAkun;

use App\Contracts\ManajemenAkun\ManajemenAkunServiceInterface;
use App\Models\ManajemenUser\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class ManajemenAkunService implements ManajemenAkunServiceInterface
{
    /**
     * Perbarui data profil akun sendiri (nama, username, password opsional).
     */
    public function updateSelf(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $updateData = [
                'name' => trim($data['name']),
                'username' => trim($data['username']),
            ];

            if (! empty($data['password'])) {
                if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                    throw new InvalidArgumentException('Password lama yang Anda masukkan salah.');
                }

                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            return $user->fresh();
        });
    }
}
