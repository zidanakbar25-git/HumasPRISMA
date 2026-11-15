<?php

declare(strict_types=1);

namespace App\Livewire\Kabag\ManajemenUser;

use App\Contracts\ManajemenUser\ManajemenUserServiceInterface;
use App\Enums\Role;
use App\Models\ManajemenUser\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class ManajemenUserIndex extends Component
{
    // Form Akun Kabag
    public string $kabag_name = '';
    public string $kabag_username = '';
    public string $kabag_current_password = '';
    public string $kabag_new_password = '';
    public bool $show_kabag_password = false;

    // Modal Tambah User
    public bool $show_create_modal = false;
    public string $create_name = '';
    public string $create_username = '';
    public string $create_password = '';
    public string $create_role = 'staff';

    // Modal Edit User
    public bool $show_edit_modal = false;
    public ?int $edit_user_id = null;
    public string $edit_name = '';
    public string $edit_username = '';
    public string $edit_password = '';
    public string $edit_role = 'staff';

    public function mount(): void
    {
        /** @var User $kabag */
        $kabag = Auth::user();
        $this->kabag_name = $kabag->name;
        $this->kabag_username = $kabag->username;
    }

    public function simpanAkunKabag(ManajemenUserServiceInterface $service): void
    {
        /** @var User $kabag */
        $kabag = Auth::user();
        Gate::authorize('updateSelf', $kabag);

        $this->validate([
            'kabag_name' => ['required', 'string', 'max:100'],
            'kabag_username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($kabag->id),
            ],
            'kabag_current_password' => [
                'nullable',
                'required_with:kabag_new_password',
                'current_password',
            ],
            'kabag_new_password' => ['nullable', 'string', 'min:6'],
        ], [
            'kabag_name.required' => 'Nama lengkap wajib diisi.',
            'kabag_username.required' => 'Username wajib diisi.',
            'kabag_username.unique' => 'Username sudah digunakan.',
            'kabag_current_password.required_with' => 'Password lama wajib diisi saat mengganti password.',
            'kabag_current_password.current_password' => 'Password lama salah.',
            'kabag_new_password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $service->updateKabag($kabag, [
            'name' => $this->kabag_name,
            'username' => $this->kabag_username,
            'password' => $this->kabag_new_password ?: null,
        ]);

        $this->kabag_current_password = '';
        $this->kabag_new_password = '';

        $this->dispatch('toast', message: 'Akun Kabag berhasil diperbarui!', type: 'success');
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', User::class);

        $this->reset(['create_name', 'create_username', 'create_password']);
        $this->create_role = 'staff';
        $this->resetErrorBag();
        $this->show_create_modal = true;
    }

    public function closeCreateModal(): void
    {
        $this->show_create_modal = false;
        $this->resetErrorBag();
    }

    public function simpanUserBaru(ManajemenUserServiceInterface $service): void
    {
        Gate::authorize('create', User::class);

        $this->validate([
            'create_name' => ['required', 'string', 'max:100'],
            'create_username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'create_password' => ['required', 'string', 'min:6'],
            'create_role' => ['required', Rule::in(['staff', 'intern'])],
        ], [
            'create_name.required' => 'Nama lengkap wajib diisi.',
            'create_username.required' => 'Username wajib diisi.',
            'create_username.unique' => 'Username sudah digunakan.',
            'create_password.required' => 'Password wajib diisi.',
            'create_password.min' => 'Password minimal 6 karakter.',
            'create_role.required' => 'Pilih role user.',
        ]);

        $service->createUser([
            'name' => $this->create_name,
            'username' => $this->create_username,
            'password' => $this->create_password,
            'role' => $this->create_role,
        ]);

        $this->show_create_modal = false;
        $this->reset(['create_name', 'create_username', 'create_password']);

        $this->dispatch('toast', message: 'User baru berhasil ditambahkan!', type: 'success');
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        Gate::authorize('update', $user);

        $this->edit_user_id = $user->id;
        $this->edit_name = $user->name;
        $this->edit_username = $user->username;
        $this->edit_role = $user->role->value;
        $this->edit_password = '';

        $this->resetErrorBag();
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->edit_user_id = null;
        $this->resetErrorBag();
    }

    public function simpanEditUser(ManajemenUserServiceInterface $service): void
    {
        $user = User::findOrFail($this->edit_user_id);
        Gate::authorize('update', $user);

        $this->validate([
            'edit_name' => ['required', 'string', 'max:100'],
            'edit_username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'edit_role' => ['required', Rule::in(['staff', 'intern'])],
            'edit_password' => ['nullable', 'string', 'min:6'],
        ], [
            'edit_name.required' => 'Nama lengkap wajib diisi.',
            'edit_username.required' => 'Username wajib diisi.',
            'edit_username.unique' => 'Username sudah digunakan.',
            'edit_role.required' => 'Pilih role user.',
            'edit_password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $service->updateUser($user, [
            'name' => $this->edit_name,
            'username' => $this->edit_username,
            'role' => $this->edit_role,
            'password' => $this->edit_password ?: null,
        ]);

        $this->show_edit_modal = false;
        $this->edit_user_id = null;

        $this->dispatch('toast', message: 'Data user berhasil diperbarui!', type: 'success');
    }

    public function toggleStatus(int $userId, ManajemenUserServiceInterface $service): void
    {
        $user = User::findOrFail($userId);
        Gate::authorize('toggleActive', $user);

        $status = $service->toggleActive($user);

        $label = $status ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('toast', message: "Akun {$user->name} berhasil {$label}.", type: 'success');
    }

    public function toggleTrusted(int $userId, bool $isTrusted, ManajemenUserServiceInterface $service): void
    {
        $user = User::findOrFail($userId);
        Gate::authorize('toggleTrusted', $user);

        $service->toggleTrusted($user, $isTrusted);

        $label = $isTrusted ? 'diberikan akses trusted' : 'dicabut akses trusted-nya';
        $this->dispatch('toast', message: "Intern {$user->name} berhasil {$label}.", type: 'success');
    }

    public function render(ManajemenUserServiceInterface $service): View
    {
        Gate::authorize('viewAny', User::class);

        $groupedUsers = $service->getGroupedUsers();

        return view('livewire.kabag.manajemen-user.index', [
            'staffUsers' => $groupedUsers['staff'],
            'internUsers' => $groupedUsers['intern'],
        ]);
    }
}
