<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Enums\Role;
use App\Livewire\Shared\JadwalKegiatan\JadwalKegiatanIndex;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\JadwalKegiatan\KegiatanDilihat;
use App\Models\ManajemenUser\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JadwalKegiatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kabag_can_view_jadwal_kegiatan_page(): void
    {
        $kabag = User::factory()->create([
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->actingAs($kabag);

        $response = $this->get('/kabag/jadwal-kegiatan');
        $response->assertStatus(200);
        $response->assertSeeLivewire(JadwalKegiatanIndex::class);
        $response->assertSee('Tambah Kegiatan');
    }

    public function test_staff_can_view_jadwal_kegiatan_page_without_create_button(): void
    {
        $staff = User::factory()->create([
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $response = $this->get('/staff/jadwal-kegiatan');
        $response->assertStatus(200);
        $response->assertSeeLivewire(JadwalKegiatanIndex::class);
        $response->assertDontSee('Tambah Kegiatan');
    }

    public function test_kabag_can_create_kegiatan_with_multiple_pics(): void
    {
        $kabag = User::factory()->create(['role' => Role::Kabag, 'is_active' => true]);
        $staff1 = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $intern1 = User::factory()->create(['role' => Role::Intern, 'is_active' => true]);

        $this->actingAs($kabag);

        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openCreateModal')
            ->set('form_judul', 'Rapat Koordinasi Humas')
            ->set('form_tanggal', $today)
            ->set('form_waktu_mulai', '09:00')
            ->set('form_waktu_selesai', '11:00')
            ->set('form_tempat', 'Ruang Rapat 1')
            ->set('form_deskripsi', 'Pembahasan rilis pers')
            ->set('form_pic_ids', [$staff1->id, $intern1->id])
            ->call('simpanKegiatan')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('kegiatan', [
            'judul' => 'Rapat Koordinasi Humas',
            'tanggal' => $today,
            'tempat' => 'Ruang Rapat 1',
        ]);

        $kegiatan = Kegiatan::where('judul', 'Rapat Koordinasi Humas')->firstOrFail();
        $this->assertCount(2, $kegiatan->pics);
        $this->assertTrue($kegiatan->pics->contains($staff1));
        $this->assertTrue($kegiatan->pics->contains($intern1));
    }

    public function test_staff_cannot_create_or_update_or_delete_kegiatan(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Test',
            'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
            'tempat' => 'Ruang A',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff);

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openCreateModal')
            ->assertForbidden();

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openEditModal', $kegiatan->id)
            ->assertForbidden();

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('confirmDelete', $kegiatan->id)
            ->assertForbidden();
    }

    public function test_kabag_can_edit_and_delete_kegiatan(): void
    {
        $kabag = User::factory()->create(['role' => Role::Kabag, 'is_active' => true]);
        $staff = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);

        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Sebelum Diedit',
            'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
            'tempat' => 'Tempat Awal',
            'created_by' => $kabag->id,
        ]);
        $kegiatan->pics()->attach($staff->id);

        $this->actingAs($kabag);

        // Edit
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openEditModal', $kegiatan->id)
            ->set('form_judul', 'Kegiatan Sudah Diedit')
            ->set('form_tempat', 'Tempat Baru')
            ->call('simpanKegiatan')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $kegiatan->refresh();
        $this->assertSame('Kegiatan Sudah Diedit', $kegiatan->judul);
        $this->assertSame('Tempat Baru', $kegiatan->tempat);

        // Delete
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('confirmDelete', $kegiatan->id)
            ->call('deleteKegiatan')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
    }

    public function test_calendar_dot_and_baru_badge_flow_between_two_users(): void
    {
        $kabag = User::factory()->create(['role' => Role::Kabag, 'is_active' => true]);
        $staff = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);

        $todayCarbon = Carbon::now('Asia/Jakarta');
        $today = $todayCarbon->format('Y-m-d');

        // 1. Kabag membuat kegiatan baru pada hari ini
        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Baru Belum Dilihat Staff',
            'tanggal' => $today,
            'tempat' => 'Ruang Humas',
            'created_by' => $kabag->id,
        ]);

        // 2. Staff login dan memeriksa titik kalender
        $this->actingAs($staff);

        $service = app(KegiatanServiceInterface::class);
        $data = $service->getKalenderViewData((int) $todayCarbon->year, (int) $todayCarbon->month, $today, $staff->id);

        // Aturan 1: Titik merah jika ada kegiatan yang belum dilihat
        $this->assertSame('red', $data['dots'][$today]);

        // Aturan 2: Kartu kegiatan menampilkan badge Baru
        $testLivewire = Livewire::test(JadwalKegiatanIndex::class)
            ->call('selectDate', $today);

        $testLivewire->assertSee('Baru');

        // Aturan 3: Begitu tanggal diklik, titik langsung berubah ke warna default (hijau)
        $dataAfterClick = $service->getKalenderViewData((int) $todayCarbon->year, (int) $todayCarbon->month, $today, $staff->id);
        $this->assertSame('green', $dataAfterClick['dots'][$today]);

        // Kartu yang sedang tampil masih memiliki badge Baru sebelum kartunya diklik
        $testLivewire->assertSee('Baru');

        // Saat kartu diklik (openDetailModal), badge Baru hilang
        $testLivewire->call('openDetailModal', $kegiatan->id);
        $this->assertArrayNotHasKey($kegiatan->id, $testLivewire->get('unread_card_ids'));

        // 4. Jika kegiatan kedua ditambahkan pada tanggal yang sama oleh kabag
        $kegiatanBaru2 = Kegiatan::create([
            'judul' => 'Kegiatan Tambahan Hari Ini',
            'tanggal' => $today,
            'tempat' => 'Ruang Rapat',
            'created_by' => $kabag->id,
        ]);

        // Aturan 4: Titik kembali merah dan badge Baru hanya muncul di kartu baru
        $dataWithSecondKegiatan = $service->getKalenderViewData((int) $todayCarbon->year, (int) $todayCarbon->month, $today, $staff->id);
        $this->assertSame('red', $dataWithSecondKegiatan['dots'][$today]);
    }

    public function test_riwayat_tab_contains_only_past_activities(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $nowWib = Carbon::now('Asia/Jakarta');

        $kegiatanLampau = Kegiatan::create([
            'judul' => 'Kegiatan Masa Lalu',
            'tanggal' => (clone $nowWib)->subDays(3)->format('Y-m-d'),
            'tempat' => 'Auditorium',
        ]);

        $kegiatanHariIni = Kegiatan::create([
            'judul' => 'Kegiatan Hari Ini',
            'tanggal' => $nowWib->format('Y-m-d'),
            'tempat' => 'Ruang Rapat',
        ]);

        $this->actingAs($user);

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Kegiatan Masa Lalu')
            ->assertDontSee('Kegiatan Hari Ini');
    }
}
