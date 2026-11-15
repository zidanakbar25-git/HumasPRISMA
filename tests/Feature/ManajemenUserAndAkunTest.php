<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\ManajemenAkun\ManajemenAkunServiceInterface;
use App\Contracts\ManajemenUser\ManajemenUserServiceInterface;
use App\Enums\Role;
use App\Livewire\Kabag\ManajemenUser\ManajemenUserIndex;
use App\Livewire\Staff\ManajemenAkun\AkunSaya;
use App\Models\ManajemenUser\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ManajemenUserAndAkunTest extends TestCase
{
    use RefreshDatabase;

    // --- MANAJEMEN AKUN (STAFF & INTERN) ---

    public function test_staff_can_view_akun_saya_page(): void
    {
        $staff = User::factory()->create([
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $response = $this->get('/staff/manajemen-akun');
        $response->assertStatus(200);
        $response->assertSeeLivewire(AkunSaya::class);
    }

    public function test_user_can_update_name_and_username_without_changing_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'username' => 'user_lama',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(AkunSaya::class)
            ->set('name', 'Nama Baru')
            ->set('username', 'user_baru')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('user_baru', $user->username);
    }

    public function test_user_cannot_update_username_to_duplicate_username(): void
    {
        User::factory()->create([
            'username' => 'user_ada',
            'role' => Role::Intern,
        ]);

        $user = User::factory()->create([
            'username' => 'user_saya',
            'role' => Role::Staff,
        ]);

        $this->actingAs($user);

        Livewire::test(AkunSaya::class)
            ->set('username', 'user_ada')
            ->call('simpan')
            ->assertHasErrors(['username' => 'unique']);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'username' => 'user_ganti_pass',
            'password' => Hash::make('password_lama_123'),
            'role' => Role::Staff,
        ]);

        $this->actingAs($user);

        Livewire::test(AkunSaya::class)
            ->set('current_password', 'password_lama_123')
            ->set('new_password', 'password_baru_123')
            ->set('new_password_confirmation', 'password_baru_123')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru_123', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'username' => 'user_wrong_pass',
            'password' => Hash::make('password_lama_123'),
            'role' => Role::Staff,
        ]);

        $this->actingAs($user);

        Livewire::test(AkunSaya::class)
            ->set('current_password', 'password_salah')
            ->set('new_password', 'password_baru_123')
            ->set('new_password_confirmation', 'password_baru_123')
            ->call('simpan')
            ->assertHasErrors(['current_password']);
    }

    // --- MANAJEMEN USER (KABAG) ---

    public function test_kabag_can_view_manajemen_user_page(): void
    {
        $kabag = User::factory()->create([
            'name' => 'Kepala Bagian',
            'username' => 'kabag_utama',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        $response = $this->get('/kabag/manajemen-user');
        $response->assertStatus(200);
        $response->assertSeeLivewire(ManajemenUserIndex::class);
    }

    public function test_kabag_can_update_own_account_in_top_form(): void
    {
        $kabag = User::factory()->create([
            'name' => 'Kabag Awal',
            'username' => 'kabag_awal',
            'role' => Role::Kabag,
            'password' => Hash::make('password_kabag_1'),
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->set('kabag_name', 'Kabag Baru')
            ->set('kabag_username', 'kabag_baru')
            ->set('kabag_current_password', 'password_kabag_1')
            ->set('kabag_new_password', 'password_kabag_2')
            ->call('simpanAkunKabag')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $kabag->refresh();
        $this->assertSame('Kabag Baru', $kabag->name);
        $this->assertSame('kabag_baru', $kabag->username);
        $this->assertTrue(Hash::check('password_kabag_2', $kabag->password));
    }

    public function test_kabag_can_create_new_staff_and_intern(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->call('openCreateModal')
            ->set('create_name', 'Staff Tambahan')
            ->set('create_username', 'staff_plus')
            ->set('create_password', 'password123')
            ->set('create_role', 'staff')
            ->call('simpanUserBaru')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('users', [
            'name' => 'Staff Tambahan',
            'username' => 'staff_plus',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_kabag_can_edit_existing_user(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $intern = User::factory()->create([
            'name' => 'Intern Mau Diedit',
            'username' => 'intern_edit',
            'role' => Role::Intern,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->call('openEditModal', $intern->id)
            ->set('edit_name', 'Intern Terubah')
            ->set('edit_username', 'intern_ganti')
            ->set('edit_role', 'staff')
            ->set('edit_password', 'newpass123')
            ->call('simpanEditUser')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $intern->refresh();
        $this->assertSame('Intern Terubah', $intern->name);
        $this->assertSame('intern_ganti', $intern->username);
        $this->assertSame(Role::Staff, $intern->role);
        $this->assertTrue(Hash::check('newpass123', $intern->password));
    }

    public function test_kabag_can_toggle_active_status_of_user(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->call('toggleStatus', $staff->id)
            ->assertDispatched('toast');

        $staff->refresh();
        $this->assertFalse($staff->is_active);

        // Toggle kembali aktif
        Livewire::test(ManajemenUserIndex::class)
            ->call('toggleStatus', $staff->id)
            ->assertDispatched('toast');

        $staff->refresh();
        $this->assertTrue($staff->is_active);
    }

    public function test_kabag_cannot_deactivate_own_account(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->call('toggleStatus', $kabag->id)
            ->assertForbidden();
    }

    public function test_kabag_can_toggle_trusted_for_intern(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $intern = User::factory()->create([
            'role' => Role::Intern,
            'is_trusted' => false,
        ]);

        $this->actingAs($kabag);

        Livewire::test(ManajemenUserIndex::class)
            ->call('toggleTrusted', $intern->id, true)
            ->assertDispatched('toast');

        $intern->refresh();
        $this->assertTrue($intern->is_trusted);
    }
}
