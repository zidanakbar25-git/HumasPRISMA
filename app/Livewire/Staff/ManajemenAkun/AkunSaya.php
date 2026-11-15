<?php

declare(strict_types=1);

namespace App\Livewire\Staff\ManajemenAkun;

use App\Contracts\ManajemenAkun\ManajemenAkunServiceInterface;
use App\Models\ManajemenUser\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class AkunSaya extends Component
{
    public string $name = '';
    public string $username = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'current_password' => [
                'nullable',
                'required_with:new_password',
                'current_password',
            ],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh pengguna lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda strip, dan underscore.',
            'current_password.required_with' => 'Password lama wajib diisi saat mengganti password.',
            'current_password.current_password' => 'Password lama yang Anda masukkan salah.',
            'new_password.min' => 'Password baru minimal harus 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }

    public function simpan(ManajemenAkunServiceInterface $service): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $service->updateSelf($user, [
            'name' => $this->name,
            'username' => $this->username,
            'current_password' => $this->current_password ?: null,
            'password' => $this->new_password ?: null,
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->dispatch('toast', message: 'Data akun Anda berhasil diperbarui!', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.staff.manajemen-akun.akun-saya');
    }
}
