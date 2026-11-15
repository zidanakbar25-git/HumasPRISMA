<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Tugas\TugasServiceInterface;
use App\Enums\Prioritas;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Livewire\Shared\JadwalKegiatan\JadwalKegiatanIndex;
use App\Livewire\Shared\Tugas\TugasIndex;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TugasTest extends TestCase
{
    use RefreshDatabase;

    protected User $kabag;
    protected User $staff;
    protected User $intern;
    protected User $staffLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kabag = User::factory()->create([
            'name' => 'Kepala Bagian',
            'username' => 'kabag',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff Humas',
            'username' => 'staff1',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
            'name' => 'Intern Humas',
            'username' => 'intern1',
            'role' => Role::Intern,
            'is_active' => true,
        ]);

        $this->staffLain = User::factory()->create([
            'name' => 'Staff Lain',
            'username' => 'staff2',
            'role' => Role::Staff,
            'is_active' => true,
        ]);
    }

    public function test_kabag_can_access_tugas_page_and_see_tabs_and_add_button(): void
    {
        $this->actingAs($this->kabag);

        $response = $this->get(route('kabag.tugas.index'));
        $response->assertOk();

        Livewire::test(TugasIndex::class)
            ->assertSee('Daftar Tugas')
            ->assertSee('Selesai')
            ->assertSee('Riwayat')
            ->assertSee('Tambah Tugas');
    }

    public function test_kabag_can_create_tugas(): void
    {
        $this->actingAs($this->kabag);

        $kegiatan = Kegiatan::create([
            'judul' => 'Konferensi Pers Pemda',
            'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
            'tempat' => 'Ruang Humas',
            'created_by' => $this->kabag->id,
        ]);

        $deadline = Carbon::now('Asia/Jakarta')->addDays(2)->format('Y-m-d\TH:i');

        Livewire::test(TugasIndex::class)
            ->call('openCreateModal')
            ->set('form_judul', 'Peliputan Foto & Berita')
            ->set('form_deadline', $deadline)
            ->set('form_prioritas', 'tinggi')
            ->set('form_pic_id', $this->staff->id)
            ->set('form_kegiatan_id', $kegiatan->id)
            ->call('simpanTugas')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Peliputan Foto & Berita',
            'prioritas' => 'tinggi',
            'pic_id' => $this->staff->id,
            'kegiatan_id' => $kegiatan->id,
            'status' => 'belum_dikerjakan',
            'tahap' => 'belum_dikumpulkan',
            'pemegang_id' => $this->staff->id,
            'dilihat_pemegang_at' => null,
            'created_by' => $this->kabag->id,
        ]);
    }

    public function test_kabag_cannot_set_kabag_as_pic(): void
    {
        $this->actingAs($this->kabag);

        $tugasService = app(TugasServiceInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        $tugasService->createTugas([
            'judul' => 'Tugas Invalid',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay()->toDateTimeString(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->kabag->id, // Kabag tidak boleh jadi PIC
            'kegiatan_id' => null,
        ], $this->kabag->id);
    }

    public function test_kabag_can_update_tugas(): void
    {
        $this->actingAs($this->kabag);

        $tugas = Tugas::create([
            'judul' => 'Judul Lama',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Rendah,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        Livewire::test(TugasIndex::class)
            ->call('openEditModal', $tugas->id)
            ->set('form_judul', 'Judul Baru Diperbarui')
            ->set('form_prioritas', 'tinggi')
            ->call('simpanTugas')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('tugas', [
            'id' => $tugas->id,
            'judul' => 'Judul Baru Diperbarui',
            'prioritas' => 'tinggi',
        ]);
    }

    public function test_kabag_can_change_pic_only_when_belum_dikumpulkan(): void
    {
        $this->actingAs($this->kabag);

        $tugasBelumKumpul = Tugas::create([
            'judul' => 'Tugas Belum Kumpul',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        // Dapat mengganti PIC saat belum dikumpulkan
        Livewire::test(TugasIndex::class)
            ->call('openEditModal', $tugasBelumKumpul->id)
            ->assertSet('form_can_change_pic', true)
            ->set('form_pic_id', $this->intern->id)
            ->call('simpanTugas');

        $this->assertDatabaseHas('tugas', [
            'id' => $tugasBelumKumpul->id,
            'pic_id' => $this->intern->id,
            'pemegang_id' => $this->intern->id,
        ]);

        // Tidak dapat mengganti PIC saat sudah masuk tahap lain (misal menunggu_staff)
        $tugasSudahKumpul = Tugas::create([
            'judul' => 'Tugas Sudah Kumpul',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguStaff,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $tugasService = app(TugasServiceInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $tugasService->updateTugas($tugasSudahKumpul, [
            'pic_id' => $this->staffLain->id,
        ]);
    }

    public function test_kabag_can_delete_tugas(): void
    {
        $this->actingAs($this->kabag);

        $tugas = Tugas::create([
            'judul' => 'Tugas Akan Dihapus',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Rendah,
            'pic_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        Livewire::test(TugasIndex::class)
            ->call('confirmDelete', $tugas->id)
            ->call('deleteTugas')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('tugas', [
            'id' => $tugas->id,
        ]);
    }

    public function test_staff_and_intern_see_own_tasks_only_and_shows_badge_baru(): void
    {
        // Tugas milik staff
        $tugasStaff = Tugas::create([
            'judul' => 'Tugas Khusus Staff',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'dilihat_pemegang_at' => null, // Badge Baru
            'created_by' => $this->kabag->id,
        ]);

        // Tugas milik intern
        $tugasIntern = Tugas::create([
            'judul' => 'Tugas Khusus Intern',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->intern->id,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->staff);

        Livewire::test(TugasIndex::class)
            ->assertSee('Tugas Khusus Staff')
            ->assertSee('Baru')
            ->assertDontSee('Tugas Khusus Intern')
            ->assertDontSee('Tambah Tugas'); // Staff tidak punya tombol Tambah Tugas
    }

    public function test_opening_tugas_detail_marks_it_as_seen_and_removes_badge_baru(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Tugas Baru Belum Dibuka',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'dilihat_pemegang_at' => null,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->staff);

        $component = Livewire::test(TugasIndex::class)
            ->assertSee('Baru');

        // Buka rincian tugas
        $component->call('openDetailModal', $tugas->id);

        $tugas->refresh();
        $this->assertNotNull($tugas->dilihat_pemegang_at);

        // Setelah ditandai dilihat, badge Baru hilang
        $component->assertDontSee('badge-baru');
    }

    public function test_pic_can_manually_update_status(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Tugas Pengerjaan',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->staff);

        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('updateStatusManual', 'sedang_dikerjakan')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(StatusTugas::SedangDikerjakan, $tugas->status);

        // Ubah status ke Selesai
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('updateStatusManual', 'selesai')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(StatusTugas::Selesai, $tugas->status);
        $this->assertNotNull($tugas->selesai_at);
    }

    public function test_kegiatan_form_can_add_multiple_tasks_and_save_in_single_transaction(): void
    {
        $this->actingAs($this->kabag);

        $tgl = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openCreateModal')
            ->set('form_judul', 'Kegiatan dengan Banyak Tugas')
            ->set('form_tanggal', $tgl)
            ->set('form_tempat', 'Aula Utama')
            // Tambah tugas pertama
            ->call('addDraftTask')
            ->set('draft_tasks.0.judul', 'Tugas Pertama Dokumentasi')
            ->set('draft_tasks.0.deadline', $tgl . 'T15:00')
            ->set('draft_tasks.0.prioritas', 'tinggi')
            ->set('draft_tasks.0.pic_id', $this->staff->id)
            // Tambah tugas kedua
            ->call('addDraftTask')
            ->set('draft_tasks.1.judul', 'Tugas Kedua Notulensi')
            ->set('draft_tasks.1.deadline', $tgl . 'T17:00')
            ->set('draft_tasks.1.prioritas', 'sedang')
            ->set('draft_tasks.1.pic_id', $this->intern->id)
            // Simpan kegiatan beserta tugasnya
            ->call('simpanKegiatan')
            ->assertDispatched('toast');

        $kegiatan = Kegiatan::where('judul', 'Kegiatan dengan Banyak Tugas')->first();
        $this->assertNotNull($kegiatan);

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Tugas Pertama Dokumentasi',
            'kegiatan_id' => $kegiatan->id,
            'pic_id' => $this->staff->id,
            'prioritas' => 'tinggi',
        ]);

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Tugas Kedua Notulensi',
            'kegiatan_id' => $kegiatan->id,
            'pic_id' => $this->intern->id,
            'prioritas' => 'sedang',
        ]);
    }

    public function test_kegiatan_detail_popup_displays_linked_tasks_and_allows_kabag_to_add_task(): void
    {
        $this->actingAs($this->kabag);

        $kegiatan = Kegiatan::create([
            'judul' => 'Workshop Media Digital',
            'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
            'tempat' => 'Lab Komputer',
            'created_by' => $this->kabag->id,
        ]);

        $tugas1 = Tugas::create([
            'judul' => 'Persiapan Materi Workshop',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'kegiatan_id' => $kegiatan->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openDetailModal', $kegiatan->id)
            ->assertSee('Persiapan Materi Workshop')
            ->assertSee('Tambah Tugas')
            // Tambah tugas baru dari popup rincian
            ->call('openKegiatanTugasModal')
            ->set('kegiatan_tugas_judul', 'Tugas Baru dari Popup Rincian')
            ->set('kegiatan_tugas_deadline', Carbon::now('Asia/Jakarta')->addDays(2)->format('Y-m-d\TH:i'))
            ->set('kegiatan_tugas_prioritas', 'sedang')
            ->set('kegiatan_tugas_pic_id', $this->intern->id)
            ->call('simpanKegiatanTugas')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('tugas', [
            'judul' => 'Tugas Baru dari Popup Rincian',
            'kegiatan_id' => $kegiatan->id,
            'pic_id' => $this->intern->id,
        ]);
    }

    public function test_deleting_kegiatan_does_not_delete_associated_tugas(): void
    {
        $this->actingAs($this->kabag);

        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan yang Akan Dihapus',
            'tanggal' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
            'tempat' => 'Ruang Humas',
            'created_by' => $this->kabag->id,
        ]);

        $tugas = Tugas::create([
            'judul' => 'Tugas Mandiri Tetap Ada',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'kegiatan_id' => $kegiatan->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        // Hapus kegiatan
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('confirmDelete', $kegiatan->id)
            ->call('deleteKegiatan')
            ->assertDispatched('toast');

        // Kegiatan terhapus
        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);

        // Tugas tetap ada, namun kegiatan_id menjadi null
        $this->assertDatabaseHas('tugas', [
            'id' => $tugas->id,
            'judul' => 'Tugas Mandiri Tetap Ada',
            'kegiatan_id' => null,
        ]);
    }
}
