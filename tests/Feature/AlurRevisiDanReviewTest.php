<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Tugas\TugasAlurServiceInterface;
use App\Enums\Prioritas;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Enums\TipePengumpulan;
use App\Livewire\Shared\Tugas\TugasIndex;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Pengumpulan;
use App\Models\Tugas\PengumpulanEditLog;
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\RevisiCatatan;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AlurRevisiDanReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $kabag;
    protected User $staff;
    protected User $intern;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->kabag = User::factory()->create([
            'name' => 'Kepala Bagian',
            'username' => 'kabag',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staf Humas',
            'username' => 'staff1',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
            'name' => 'Intern Pratama',
            'username' => 'intern1',
            'role' => Role::Intern,
            'is_active' => true,
            'is_trusted' => false,
        ]);
    }

    public function test_alur_lengkap_intern_staff_revisi_intern_staff_kabag(): void
    {
        // 1. Kabag membuat tugas untuk Intern
        $tugas = Tugas::create([
            'judul' => 'Liputan Kegiatan Bakti Sosial',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(2),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::BelumDikerjakan,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->intern->id,
            'created_by' => $this->kabag->id,
        ]);

        // 2. Intern membuka tugas, menyelesaikan, dan mengumpulkan ke Staf
        $this->actingAs($this->intern);

        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('updateStatusManual', 'selesai')
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'teks')
            ->set('kumpul_teks', 'Draf rilis pertama bakti sosial oleh pemda.')
            ->set('kumpul_tujuan_user_id', $this->staff->id)
            ->call('submitPengumpulan')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguStaff, $tugas->tahap);
        $this->assertEquals($this->staff->id, $tugas->pemegang_id);
        $this->assertEquals($this->staff->id, $tugas->staff_penerima_id);

        // 3. Staf membuka tab pengumpulan_intern, melihat tugas, mengedit narasi teks, lalu meminta revisi
        $this->actingAs($this->staff);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'pengumpulan_intern')
            ->assertSee('Liputan Kegiatan Bakti Sosial')
            ->call('openReviewModal', $tugas->id)
            ->assertSee('Review Pengumpulan Intern')
            ->set('review_teks', 'Draf rilis pertama yang diedit oleh staf.')
            ->call('simpanTeksReviewAction')
            ->assertDispatched('toast')
            ->call('switchModeRevisi', true)
            ->set('poin_revisi.0', 'Tolong tambahkan kutipan dari Kepala Dinas Sosial.')
            ->call('tambahPoinRevisi')
            ->set('poin_revisi.1', 'Koreksi typo pada paragraf kedua.')
            ->call('kirimRevisiKeInternAction')
            ->assertDispatched('toast');

        // Verifikasi log edit oleh staf tersimpan
        $this->assertDatabaseHas('pengumpulan_edit_log', [
            'user_id' => $this->staff->id,
            'aksi' => 'edit_teks',
        ]);

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Revisi, $tugas->tahap);
        $this->assertEquals($this->intern->id, $tugas->pemegang_id);

        // Verifikasi catatan revisi tersimpan
        $revisi = RevisiCatatan::where('tugas_id', $tugas->id)->latest('created_at')->first();
        $this->assertNotNull($revisi);
        $this->assertEquals($this->staff->id, $revisi->dari_user_id);
        $this->assertEquals($this->intern->id, $revisi->ke_user_id);
        $this->assertCount(2, $revisi->poin_list);

        // 4. Intern membuka tab revisi, melihat catatan revisi dan kumpulkan kembali
        $this->actingAs($this->intern);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'revisi')
            ->assertSee('Liputan Kegiatan Bakti Sosial')
            ->assertSee('Revisi')
            ->call('openRevisiModal', $tugas->id)
            ->assertSee('Catatan Revisi dari Staf Humas')
            ->assertSee('Tolong tambahkan kutipan dari Kepala Dinas Sosial.')
            ->set('revisi_teks', 'Draf rilis lengkap dengan kutipan Kepala Dinas Sosial dan perbaikan typo.')
            ->call('submitKumpulkanKembali')
            ->assertDispatched('toast');

        $tugas->refresh();
        // Tugas harus kembali ke Staf penerima sebelumnya
        $this->assertEquals(TahapTugas::MenungguStaff, $tugas->tahap);
        $this->assertEquals($this->staff->id, $tugas->pemegang_id);

        // 5. Staf membuka kembali tugas dan meneruskan ke Kabag
        $this->actingAs($this->staff);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'pengumpulan_intern')
            ->call('openReviewModal', $tugas->id)
            ->call('kirimKeKabagAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguKabag, $tugas->tahap);
        $this->assertEquals($this->kabag->id, $tugas->pemegang_id);

        // 6. Kabag melihat tugas di tab Selesai
        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->assertSee('Liputan Kegiatan Bakti Sosial')
            ->call('openHasilModal', $tugas->id)
            ->assertSee('Hasil Pengumpulan Tugas')
            ->assertSee('Draf rilis lengkap dengan kutipan Kepala Dinas Sosial')
            ->assertSee('Diedit oleh: Staf Humas');
    }

    public function test_staff_bisa_menambah_dan_menghapus_file_pada_popup_review(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Dokumentasi Apel Pagi',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(1),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguStaff,
            'pemegang_id' => $this->staff->id,
            'staff_penerima_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::File,
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        Storage::disk('local')->put("pengumpulan/{$tugas->id}/foto_intern.jpg", 'konten-1');

        $fileIntern = PengumpulanFile::create([
            'pengumpulan_id' => $pengumpulan->id,
            'path' => "pengumpulan/{$tugas->id}/foto_intern.jpg",
            'nama_asli' => 'foto_intern.jpg',
            'mime' => 'image/jpeg',
            'ukuran' => 1024,
        ]);

        $this->actingAs($this->staff);

        $fakeNewFile = UploadedFile::fake()->image('foto_tambahan_staff.png', 600, 600)->size(1500);

        Livewire::test(TugasIndex::class)
            ->call('openReviewModal', $tugas->id)
            ->assertSee('foto_intern.jpg')
            ->set('review_new_files', [$fakeNewFile])
            ->call('tambahFileReviewAction')
            ->assertDispatched('toast')
            ->call('hapusFileReviewAction', $fileIntern->id)
            ->assertDispatched('toast');

        // File intern terhapus dari DB dan storage
        $this->assertDatabaseMissing('pengumpulan_file', ['id' => $fileIntern->id]);
        Storage::disk('local')->assertMissing("pengumpulan/{$tugas->id}/foto_intern.jpg");

        // File baru dari staf tersimpan
        $this->assertDatabaseHas('pengumpulan_file', [
            'pengumpulan_id' => $pengumpulan->id,
            'nama_asli' => 'foto_tambahan_staff.png',
        ]);

        // Edit log mencatat tambah_file dan hapus_file oleh staf
        $this->assertDatabaseHas('pengumpulan_edit_log', [
            'user_id' => $this->staff->id,
            'aksi' => 'tambah_file',
        ]);
        $this->assertDatabaseHas('pengumpulan_edit_log', [
            'user_id' => $this->staff->id,
            'aksi' => 'hapus_file',
        ]);
    }

    public function test_intern_memilih_staff_pengganti_jika_staff_sebelumnya_nonaktif(): void
    {
        // Staf penerima pertama dinonaktifkan
        $this->staff->update(['is_active' => false]);

        $stafPengganti = User::factory()->create([
            'name' => 'Staf Baru',
            'username' => 'staff2',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $tugas = Tugas::create([
            'judul' => 'Tugas Revisi Staf Nonaktif',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(1),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::Revisi,
            'pemegang_id' => $this->intern->id,
            'staff_penerima_id' => $this->staff->id, // Staf nonaktif
            'created_by' => $this->kabag->id,
        ]);

        Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Link,
            'link' => 'https://drive.google.com/test-link',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        RevisiCatatan::create([
            'tugas_id' => $tugas->id,
            'dari_user_id' => $this->staff->id,
            'ke_user_id' => $this->intern->id,
            'catatan' => 'Perbaiki link akses agar publik.',
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->intern);

        // Jika belum memilih staf pengganti, submit gagal
        Livewire::test(TugasIndex::class)
            ->call('openRevisiModal', $tugas->id)
            ->assertSee('Staf penerima sebelumnya telah nonaktif')
            ->call('submitKumpulkanKembali')
            ->assertHasErrors(['revisi_tujuan_user_id'])
            ->set('revisi_tujuan_user_id', $stafPengganti->id)
            ->call('submitKumpulkanKembali')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguStaff, $tugas->tahap);
        $this->assertEquals($stafPengganti->id, $tugas->pemegang_id);
        $this->assertEquals($stafPengganti->id, $tugas->staff_penerima_id);
    }
}
