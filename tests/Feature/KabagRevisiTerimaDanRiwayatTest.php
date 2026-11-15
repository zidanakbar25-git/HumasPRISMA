<?php

declare(strict_types=1);

namespace Tests\Feature;

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

class KabagRevisiTerimaDanRiwayatTest extends TestCase
{
    use RefreshDatabase;

    protected User $kabag;
    protected User $staff1;
    protected User $staff2;
    protected User $intern;
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

        $this->staff1 = User::factory()->create([
            'name' => 'Staf Humas 1',
            'username' => 'staff1',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->staff2 = User::factory()->create([
            'name' => 'Staf Humas 2',
            'username' => 'staff2',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
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

    public function test_kabag_minta_revisi_ke_staff_penerima_dan_staff_melihat_badge_revisi_dari_kabag(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Liputan Kunjungan Kerja',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(2),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'staff_penerima_id' => $this->staff1->id,
            'created_by' => $this->kabag->id,
        ]);

        Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Teks,
            'teks' => 'Teks naskah berita kunjungan kerja.',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->kabag);

        // Kabag membuka tab Selesai, membuka hasil modal, lalu meminta revisi
        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->call('switchHasilModeRevisi', true)
            ->set('hasil_poin_revisi.0', 'Tolong periksa kembali nama pejabat yang hadir.')
            ->call('kirimRevisiKabagAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Revisi, $tugas->tahap);
        $this->assertEquals($this->staff1->id, $tugas->pemegang_id);

        // Staf penerima melihat tugas di tab Pengumpulan Intern dengan badge "Revisi dari Kabag"
        $this->actingAs($this->staff1);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'pengumpulan_intern')
            ->assertSee('Liputan Kunjungan Kerja')
            ->assertSee('Revisi dari Kabag');
    }

    public function test_kabag_minta_revisi_ke_staff_pic_dan_staff_pic_kumpulkan_kembali(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Penyusunan Rilis Tahunan',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(3),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff1->id, // Staff adalah PIC
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'staff_penerima_id' => null, // PIC staff sendiri
            'created_by' => $this->kabag->id,
        ]);

        Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Link,
            'link' => 'https://drive.google.com/rilis-draft',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Kabag minta revisi ke staff PIC
        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->call('switchHasilModeRevisi', true)
            ->set('hasil_poin_revisi.0', 'Tolong buka izin akses link Google Drive.')
            ->call('kirimRevisiKabagAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Revisi, $tugas->tahap);
        $this->assertEquals($this->staff1->id, $tugas->pemegang_id);

        // Staff PIC login: melihat tugas di tab Tugas Saya dengan badge "Revisi dari Kabag"
        $this->actingAs($this->staff1);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'tugas_saya')
            ->assertSee('Penyusunan Rilis Tahunan')
            ->assertSee('Revisi dari Kabag')
            ->call('openRevisiModal', $tugas->id)
            ->assertSee('Tolong buka izin akses link Google Drive.')
            ->set('revisi_link', 'https://drive.google.com/rilis-public')
            ->call('submitKumpulkanKembali')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguKabag, $tugas->tahap);
        $this->assertEquals($this->kabag->id, $tugas->pemegang_id);
    }

    public function test_kabag_terima_tugas_dan_masuk_ke_tab_riwayat_read_only(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Liputan Seminar Nasional',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(1),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff1->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Teks,
            'teks' => 'Liputan seminar nasional selesai disusun.',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->kabag);

        // Kabag menerima tugas
        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->call('terimaTugasAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Diterima, $tugas->tahap);
        $this->assertNull($tugas->pemegang_id);
        $this->assertNotNull($tugas->diterima_at);

        // Kabag melihat tugas di tab Riwayat
        Livewire::test(TugasIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Liputan Seminar Nasional')
            ->call('openDetailModal', $tugas->id)
            ->assertSee('Riwayat Tugas')
            ->assertSee('Tugas telah disetujui')
            ->assertDontSee('Edit Tugas')
            ->assertDontSee('Hapus');

        // Staff PIC juga melihat tugas di tab Riwayat
        $this->actingAs($this->staff1);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Liputan Seminar Nasional')
            ->call('openDetailModal', $tugas->id)
            ->assertSee('Riwayat Tugas')
            ->assertDontSee('Ubah Status Pengerjaan');
    }

    public function test_kabag_bisa_edit_teks_di_tempat_pada_popup_hasil(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Rilis Pers Pelantikan Pejabat',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(1),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff1->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Teks,
            'teks' => 'Teks awal sebelum diedit kabag.',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->set('hasil_teks', 'Teks yang telah disempurnakan oleh Kepala Bagian.')
            ->call('simpanTeksKabagAction')
            ->assertDispatched('toast');

        $pengumpulan->refresh();
        $this->assertEquals('Teks yang telah disempurnakan oleh Kepala Bagian.', $pengumpulan->teks);
    }

    public function test_kabag_pilih_staff_pengganti_saat_staff_penerima_nonaktif(): void
    {
        $this->staff1->update(['is_active' => false]); // Staff penerima dinonaktifkan

        $tugas = Tugas::create([
            'judul' => 'Tugas dengan Staf Penerima Nonaktif',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(2),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'staff_penerima_id' => $this->staff1->id,
            'created_by' => $this->kabag->id,
        ]);

        Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => TipePengumpulan::Teks,
            'teks' => 'Naskah berita.',
            'dikumpulkan_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->call('switchHasilModeRevisi', true)
            ->assertSee('Staf penerima tugas sebelumnya telah nonaktif')
            ->set('hasil_poin_revisi.0', 'Tolong perbaiki data.')
            ->call('kirimRevisiKabagAction')
            ->assertHasErrors(['hasil_staff_pengganti_id'])
            ->set('hasil_staff_pengganti_id', $this->staff2->id)
            ->call('kirimRevisiKabagAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Revisi, $tugas->tahap);
        $this->assertEquals($this->staff2->id, $tugas->pemegang_id);
        $this->assertEquals($this->staff2->id, $tugas->staff_penerima_id);
    }

    public function test_intern_trusted_bisa_kumpulkan_langsung_ke_kabag_dan_revisi_langsung_ke_intern(): void
    {
        $tugas = Tugas::create([
            'judul' => 'Tugas Intern Terpercaya',
            'deadline' => Carbon::now('Asia/Jakarta')->addDays(2),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->internTrusted->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->internTrusted->id,
            'created_by' => $this->kabag->id,
        ]);

        $this->actingAs($this->internTrusted);

        // Intern trusted melihat Kabag di pilihan teratas dan mengumpulkan ke Kabag
        Livewire::test(TugasIndex::class)
            ->call('openDetailModal', $tugas->id)
            ->call('openKumpulkanModal')
            ->assertSee('Langsung ke Kabag')
            ->set('kumpul_tipe', 'teks')
            ->set('kumpul_teks', 'Naskah langsung untuk Kabag.')
            ->set('kumpul_tujuan_user_id', $this->kabag->id)
            ->call('submitPengumpulan')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguKabag, $tugas->tahap);
        $this->assertEquals($this->kabag->id, $tugas->pemegang_id);
        $this->assertNull($tugas->staff_penerima_id);

        // Kabag minta revisi: karena staff_penerima_id null, revisi langsung ke intern
        $this->actingAs($this->kabag);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'selesai')
            ->call('openHasilModal', $tugas->id)
            ->call('switchHasilModeRevisi', true)
            ->set('hasil_poin_revisi.0', 'Tolong tambahkan kesimpulan rilis.')
            ->call('kirimRevisiKabagAction')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::Revisi, $tugas->tahap);
        $this->assertEquals($this->internTrusted->id, $tugas->pemegang_id);

        // Intern kumpulkan kembali: kembali langsung ke Kabag
        $this->actingAs($this->internTrusted);

        Livewire::test(TugasIndex::class)
            ->call('setTab', 'revisi')
            ->call('openRevisiModal', $tugas->id)
            ->assertSee('Tolong tambahkan kesimpulan rilis.')
            ->set('revisi_teks', 'Naskah dengan kesimpulan lengkap untuk Kabag.')
            ->call('submitKumpulkanKembali')
            ->assertDispatched('toast');

        $tugas->refresh();
        $this->assertEquals(TahapTugas::MenungguKabag, $tugas->tahap);
        $this->assertEquals($this->kabag->id, $tugas->pemegang_id);
    }
}
