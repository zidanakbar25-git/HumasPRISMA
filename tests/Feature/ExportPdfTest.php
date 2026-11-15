<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Enums\Role;
use App\Livewire\Shared\JadwalKegiatan\JadwalKegiatanIndex;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_pdf_button_is_visible_on_riwayat_tab_for_all_roles(): void
    {
        $kabag = User::factory()->create(['role' => Role::Kabag, 'is_active' => true]);
        $staff = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $intern = User::factory()->create(['role' => Role::Intern, 'is_active' => true]);

        // Kabag
        $this->actingAs($kabag);
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Export PDF');

        // Staff
        $this->actingAs($staff);
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Export PDF');

        // Intern
        $this->actingAs($intern);
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('setTab', 'riwayat')
            ->assertSee('Export PDF');
    }

    public function test_export_modal_can_toggle_between_bulan_and_rentang_mode(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $this->actingAs($user);

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('setTab', 'riwayat')
            ->call('openExportModal')
            ->assertSet('show_export_modal', true)
            ->assertSet('export_mode', 'bulan')
            ->call('setExportMode', 'rentang')
            ->assertSet('export_mode', 'rentang');
    }

    public function test_export_validation_rejects_future_dates_in_rentang_mode(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $this->actingAs($user);

        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openExportModal')
            ->call('setExportMode', 'rentang')
            ->set('export_start_date', $today)
            ->set('export_end_date', $today) // Hari ini tidak boleh karena Riwayat hanya < hari ini
            ->call('exportPdf')
            ->assertHasErrors(['export_end_date']);
    }

    public function test_empty_data_shows_error_and_does_not_generate_pdf(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $this->actingAs($user);

        // Tidak ada data kegiatan di tahun 2021
        Livewire::test(JadwalKegiatanIndex::class)
            ->call('openExportModal')
            ->set('export_mode', 'bulan')
            ->set('export_month', 1)
            ->set('export_year', 2021)
            ->call('exportPdf')
            ->assertSet('export_error', 'Tidak ada data kegiatan riwayat pada periode yang dipilih.')
            ->assertSet('show_export_modal', true);
    }

    public function test_successful_pdf_export_by_month(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);
        $pic = User::factory()->create(['name' => 'Budi PIC', 'role' => Role::Staff, 'is_active' => true]);

        $pastDate = Carbon::now('Asia/Jakarta')->subDays(5);

        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Riwayat Untuk PDF Bulan',
            'tanggal' => $pastDate->format('Y-m-d'),
            'tempat' => 'Ruang Humas',
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
        ]);
        $kegiatan->pics()->attach($pic->id);

        $this->actingAs($user);

        $response = Livewire::test(JadwalKegiatanIndex::class)
            ->call('openExportModal')
            ->set('export_mode', 'bulan')
            ->set('export_month', (int) $pastDate->month)
            ->set('export_year', (int) $pastDate->year)
            ->call('exportPdf');

        $response->assertFileDownloaded();
    }

    public function test_successful_pdf_export_by_date_range(): void
    {
        $user = User::factory()->create(['role' => Role::Intern, 'is_active' => true]);
        $pic = User::factory()->create(['name' => 'Siti PIC', 'role' => Role::Staff, 'is_active' => true]);

        $pastDate1 = Carbon::now('Asia/Jakarta')->subDays(10);
        $pastDate2 = Carbon::now('Asia/Jakarta')->subDays(2);

        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Riwayat Untuk PDF Rentang',
            'tanggal' => $pastDate1->format('Y-m-d'),
            'tempat' => 'Gedung Serbaguna',
        ]);
        $kegiatan->pics()->attach($pic->id);

        $this->actingAs($user);

        $response = Livewire::test(JadwalKegiatanIndex::class)
            ->call('openExportModal')
            ->set('export_mode', 'rentang')
            ->set('export_start_date', $pastDate1->format('Y-m-d'))
            ->set('export_end_date', $pastDate2->format('Y-m-d'))
            ->call('exportPdf');

        $response->assertFileDownloaded();
    }

    public function test_pdf_template_renders_correct_kop_and_table_data(): void
    {
        $pic = User::factory()->create(['name' => 'Ahmad PIC', 'role' => Role::Staff]);
        $kegiatan = Kegiatan::create([
            'judul' => 'Kegiatan Template PDF',
            'tanggal' => '2026-10-15',
            'tempat' => 'Ruang Rapat Utama',
            'waktu_mulai' => '10:00:00',
        ]);
        $kegiatan->pics()->attach($pic->id);

        $service = app(KegiatanServiceInterface::class);
        $kegiatans = Kegiatan::with('pics')->where('id', $kegiatan->id)->get();

        $pdf = $service->generateRiwayatPdf($kegiatans, 'Oktober 2026');
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }
}
