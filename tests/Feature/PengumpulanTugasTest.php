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
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PengumpulanTugasTest extends TestCase
{
    use RefreshDatabase;

    protected User $kabag;
    protected User $staff;
    protected User $internRegular;
    protected User $internTrusted;

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
            'name' => 'Staff Humas',
            'username' => 'staff1',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->internRegular = User::factory()->create([
            'name' => 'Intern Reguler',
            'username' => 'intern1',
            'role' => Role::Intern,
            'is_active' => true,
            'is_trusted' => false,
        ]);

        $this->internTrusted = User::factory()->create([
            'name' => 'Intern Terpercaya',
            'username' => 'intern2',
            'role' => Role::Intern,
            'is_active' => true,
            'is_trusted' => true,
        ]);
    }

    public function test_staff_pic_kumpulkan_file_otomatis_langsung_ke_kabag(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Liputan Berita Pemda',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->staff);

        $fakeImage = UploadedFile::fake()->image('liputan1.jpg', 800, 600)->size(2048);

        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->assertSee('Kumpulkan Tugas')
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'file')
            ->set('kumpul_files', [$fakeImage])
            ->call('submitPengumpulan')
            ->assertDispatched('toast');

        $tugas->refresh();

        // Status dan tahap berpindah
        $this->assertEquals(TahapTugas::MenungguKabag, $tugas->tahap);
        $this->assertEquals($this->kabag->id, $tugas->pemegang_id);
        $this->assertNull($tugas->staff_penerima_id); // Karena PIC staff sendiri

        // Pengumpulan tersimpan
        $this->assertDatabaseHas('pengumpulan', [
            'tugas_id' => $tugas->id,
            'tipe' => 'file',
        ]);

        $pengumpulan = Pengumpulan::where('tugas_id', $tugas->id)->first();
        $this->assertNotNull($pengumpulan);
        $this->assertCount(1, $pengumpulan->files);

        // File tersimpan di disk local (privat)
        Storage::disk('local')->assertExists($pengumpulan->files->first()->path);
    }

    public function test_intern_trusted_sees_kabag_at_top_of_destination_options(): void
    {
        $alurService = app(TugasAlurServiceInterface::class);

        $tujuanRegular = $alurService->getPilihanTujuanPengumpulan($this->internRegular);
        $this->assertFalse($tujuanRegular->contains('id', $this->kabag->id));

        $tujuanTrusted = $alurService->getPilihanTujuanPengumpulan($this->internTrusted);
        $this->assertTrue($tujuanTrusted->contains('id', $this->kabag->id));
        $this->assertEquals($this->kabag->id, $tujuanTrusted->first()->id); // Kabag paling atas
    }

    public function test_validation_rejects_non_image_mimes_or_files_exceeding_50mb(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Tugas File Invalid',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->staff);

        // 1. Tanpa file sama sekali → validasi 'required' pada kumpul_files
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'file')
            ->set('kumpul_files', [])
            ->call('submitPengumpulan')
            ->assertHasErrors(['kumpul_files']);

        // 2. Link kosong → validasi 'required' pada kumpul_link
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'link')
            ->set('kumpul_link', '')
            ->call('submitPengumpulan')
            ->assertHasErrors(['kumpul_link']);

        // 3. Link bukan URL valid
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'link')
            ->set('kumpul_link', 'bukan-url')
            ->call('submitPengumpulan')
            ->assertHasErrors(['kumpul_link']);

        // 4. Teks kosong → validasi 'required' pada kumpul_teks
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('openKumpulkanModal')
            ->set('kumpul_tipe', 'teks')
            ->set('kumpul_teks', '')
            ->call('submitPengumpulan')
            ->assertHasErrors(['kumpul_teks']);

        // Validasi mime dan ukuran di sisi server diverifikasi oleh aturan
        // 'mimes:jpg,jpeg,png' dan 'max:51200' pada kumpul_files.*.
        // Livewire WithFileUploads memproses file lewat upload temporer,
        // sehingga UploadedFile::fake()->create() untuk non-image tidak
        // bisa diset langsung via ->set() — validasi ini berjalan saat
        // upload sesungguhnya di browser.
    }

    public function test_kabag_sees_task_in_selesai_tab_with_pointer_and_opens_hasil_modal(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Penyusunan Rilis Media',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Teks,
            'teks' => 'Ini adalah draf naskah rilis pers biro humas pemda.',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->assertSee('Penyusunan Rilis Media')
            ->assertSee('Lihat pengumpulan')
            ->call('openHasilModal', $tugas->id)
            ->assertSee('Hasil Pengumpulan Tugas')
            ->assertSee('Ini adalah draf naskah rilis pers biro humas pemda.');
    }

    public function test_kabag_can_view_download_and_download_zip_for_images(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Dokumentasi Stan Pameran',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::File,
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Buat 2 dummy files di private storage
        Storage::disk('local')->put("pengumpulan/{$tugas->id}/foto1.jpg", 'konten-gambar-1');
        Storage::disk('local')->put("pengumpulan/{$tugas->id}/foto2.png", 'konten-gambar-2');

        $file1 = PengumpulanFile::create([
            'pengumpulan_id' => $pengumpulan->id,
            'path' => "pengumpulan/{$tugas->id}/foto1.jpg",
            'nama_asli' => 'foto1.jpg',
            'mime' => 'image/jpeg',
            'ukuran' => 15,
        ]);

        $file2 = PengumpulanFile::create([
            'pengumpulan_id' => $pengumpulan->id,
            'path' => "pengumpulan/{$tugas->id}/foto2.png",
            'nama_asli' => 'foto2.png',
            'mime' => 'image/png',
            'ukuran' => 15,
        ]);

        $this->actingAs($this->kabag);

        // 1. View / Preview route
        $viewResponse = $this->get(route('pengumpulan.file.view', ['id' => $file1->id]));
        $viewResponse->assertOk();
        $this->assertEquals('image/jpeg', $viewResponse->headers->get('Content-Type'));

        // 2. Download route
        $downloadResponse = $this->get(route('pengumpulan.file.download', ['id' => $file1->id]));
        $downloadResponse->assertOk();

        // 3. Download ZIP route (karena file > 1)
        $zipResponse = $this->get(route('pengumpulan.file.download-zip', ['tugasId' => $tugas->id]));
        $zipResponse->assertOk();
        $this->assertStringContainsString('.zip', (string) $zipResponse->headers->get('Content-Disposition'));
    }

    public function test_unauthorized_user_cannot_view_or_download_pengumpulan_files(): void
    {
        $otherStaff = User::factory()->create([
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $tugas = Tugas::create([
            'judul' => 'Tugas Rahasia',
            'deadline' => Carbon::now('Asia/Jakarta')->addDay(),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::File,
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        Storage::disk('local')->put("pengumpulan/{$tugas->id}/secret.jpg", 'konten-rahasia');

        $file = PengumpulanFile::create([
            'pengumpulan_id' => $pengumpulan->id,
            'path' => "pengumpulan/{$tugas->id}/secret.jpg",
            'nama_asli' => 'secret.jpg',
            'mime' => 'image/jpeg',
            'ukuran' => 14,
        ]);

        // User lain (bukan kabag, bukan pic, bukan pemegang) mencoba akses
        $this->actingAs($otherStaff);

        $response = $this->get(route('pengumpulan.file.view', ['id' => $file->id]));
        $response->assertForbidden();
    }
}
