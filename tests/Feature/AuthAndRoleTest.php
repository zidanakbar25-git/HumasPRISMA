<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ManajemenUser\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Biro Humas');
        $response->assertSee('Username');
        $response->assertSee('Password');
    }

    public function test_kabag_can_login_and_is_redirected_to_kabag_dashboard(): void
    {
        $kabag = User::factory()->create([
            'username' => 'kabagtest',
            'password' => bcrypt('password'),
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'kabagtest',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('kabag.jadwal-kegiatan.index'));
        $this->assertAuthenticatedAs($kabag);
    }

    public function test_staff_can_login_and_is_redirected_to_staff_dashboard(): void
    {
        $staff = User::factory()->create([
            'username' => 'stafftest',
            'password' => bcrypt('password'),
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'stafftest',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('staff.jadwal-kegiatan.index'));
        $this->assertAuthenticatedAs($staff);
    }

    public function test_intern_can_login_and_is_redirected_to_staff_dashboard(): void
    {
        $intern = User::factory()->create([
            'username' => 'interntest',
            'password' => bcrypt('password'),
            'role' => Role::Intern,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'interntest',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('staff.jadwal-kegiatan.index'));
        $this->assertAuthenticatedAs($intern);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'inactivetest',
            'password' => bcrypt('password'),
            'role' => Role::Staff,
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'inactivetest',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_active_user_automatically_logged_out_when_deactivated(): void
    {
        $user = User::factory()->create([
            'username' => 'will_deactivate',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // Akses pertama kali saat aktif: Berhasil
        $response = $this->get('/staff/jadwal-kegiatan');
        $response->assertStatus(200);

        // Dinonaktifkan
        $user->update(['is_active' => false]);

        // Akses berikutnya: Di-logout otomatis oleh EnsureActive middleware
        $response = $this->get('/staff/jadwal-kegiatan');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_staff_cannot_access_kabag_routes(): void
    {
        $staff = User::factory()->create([
            'username' => 'staff_forbidden',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $response = $this->get('/kabag/jadwal-kegiatan');
        $response->assertStatus(403);
    }

    public function test_kabag_cannot_access_staff_routes(): void
    {
        $kabag = User::factory()->create([
            'username' => 'kabag_forbidden',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        $response = $this->get('/staff/jadwal-kegiatan');
        $response->assertStatus(403);
    }

    public function test_sidebar_renders_correct_menu_for_kabag(): void
    {
        $kabag = User::factory()->create([
            'name' => 'Pak Kabag',
            'username' => 'kabag_menu',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        $response = $this->get('/kabag/jadwal-kegiatan');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Kegiatan');
        $response->assertSee('Tugas');
        $response->assertSee('Manajemen User');
        $response->assertSee('Backup Database');
        $response->assertDontSee('Akun Saya');
    }

    public function test_sidebar_renders_correct_menu_for_staff(): void
    {
        $staff = User::factory()->create([
            'name' => 'Ibu Staff',
            'username' => 'staff_menu',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $response = $this->get('/staff/jadwal-kegiatan');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Kegiatan');
        $response->assertSee('Tugas');
        $response->assertSee('Akun Saya');
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('Backup Database');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'user_logout',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
