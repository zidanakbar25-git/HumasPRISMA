<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Contracts\JadwalKegiatan\KegiatanRepositoryInterface;
use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Contracts\Tugas\TugasAlurServiceInterface;
use App\Enums\Prioritas;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Livewire\Shared\JadwalKegiatan\JadwalKegiatanIndex;
use App\Models\BackupDB\BackupDatabase;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Pengumpulan;
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AuditFase11Test extends TestCase
{
    use RefreshDatabase;

    private User $kabag;
    private User $staff;
    private User $otherStaff;
    private User $intern;
    private User $trustedIntern;

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
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->otherStaff = User::factory()->create([
            'name' => 'Siti Staff Lain',
            'username' => 'siti',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
            'name' => 'Rina Intern',
            'username' => 'rina',
            'role' => Role::Intern,
            'is_active' => true,
            'is_trusted' => false,
        ]);

        $this->trustedIntern = User::factory()->create([
            'name' => 'Doni Intern Trusted',
            'username' => 'doni',
            'role' => Role::Intern,
            'is_active' => true,
            'is_trusted' => true,
        ]);
    }

    protected function tearDown(): void
    {
        // Bersihkan file backup uji
        $backupDir = storage_path('app/backups');
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if (str_starts_with($file->getFilename(), 'backup_')) {
                    File::delete($file->getPathname());
                }
            }
        }

        parent::tearDown();
    }

    /**
     * 1. Audit Aturan Titik Merah & Badge Baru Sesuai Spesifikasi:
     * - Titik merah jika ada >=1 kegiatan belum dilihat
     * - Kartu belum dilihat menampilkan badge Baru
     * - Klik tanggal -> titik tanggal kembali hijau di DB, badge Baru tetap ada di kartu
     * - Klik kartu -> badge Baru hilang
     * - Tambah kegiatan baru di tanggal yang sama -> titik kembali merah, badge Baru hanya di kegiatan baru
     */
    public function test_aturan_titik_merah_dan_badge_baru_sesuai_spesifikasi(): void
    {
        $this->actingAs($this->staff);

        $targetDate = Carbon::now('Asia/Jakarta')->addDays(5)->format('Y-m-d');
        $year = (int) Carbon::parse($targetDate)->year;
        $month = (int) Carbon::parse($targetDate)->month;

        /** @var KegiatanRepositoryInterface $repo */
        $repo = app(KegiatanRepositoryInterface::class);

        // Awal: Tidak ada kegiatan -> tidak ada titik
        $dots = $repo->getCalendarMonthDots($year, $month, $this->staff->id);
        $this->assertArrayNotHasKey($targetDate, $dots);

        // 1. Buat kegiatan 1 di targetDate
        $kegiatan1 = Kegiatan::create([
            'judul' => 'Kegiatan Humas Pertama',
            'tanggal' => $targetDate,
            'tempat' => 'Ruang Rapat A',
            'created_by' => $this->kabag->id,
        ]);

        // Belum dilihat oleh staff -> Titik merah
        $dots = $repo->getCalendarMonthDots($year, $month, $this->staff->id);
        $this->assertArrayHasKey($targetDate, $dots);
        $this->assertSame('red', $dots[$targetDate]);

        // Buka halaman di Livewire dan pilih tanggal tersebut
        $test = Livewire::test(JadwalKegiatanIndex::class)
            ->call('selectDate', $targetDate)
            ->assertSee('Kegiatan Humas Pertama')
            ->assertSee('Baru');

        // Setelah klik tanggal, di database titik berubah kembali hijau
        $dotsAfterSelect = $repo->getCalendarMonthDots($year, $month, $this->staff->id);
        $this->assertSame('green', $dotsAfterSelect[$targetDate]);

        // Kartu masih menampilkan Baru sampai kartu diklik
        $this->assertTrue(isset($test->get('unread_card_ids')[$kegiatan1->id]));

        // Klik kartu untuk melihat rincian -> badge Baru hilang
        $test->call('openDetailModal', $kegiatan1->id)
            ->assertSet('show_detail_modal', true);
        $this->assertFalse(isset($test->get('unread_card_ids')[$kegiatan1->id]));

        // 2. Tambah kegiatan 2 di tanggal yang sama
        $kegiatan2 = Kegiatan::create([
            'judul' => 'Kegiatan Humas Kedua',
            'tanggal' => $targetDate,
            'tempat' => 'Aula Utama',
            'created_by' => $this->kabag->id,
        ]);

        // Titik kembali merah karena ada kegiatan baru yang belum pernah dilihat!
        $dotsWithNew = $repo->getCalendarMonthDots($year, $month, $this->staff->id);
        $this->assertSame('red', $dotsWithNew[$targetDate]);

        // Pilih tanggal lagi: kegiatan 1 sudah dilihat, kegiatan 2 berbadge Baru
        $test->call('selectDate', $targetDate);
        $unreadCards = $test->get('unread_card_ids');
        $this->assertFalse(isset($unreadCards[$kegiatan1->id]));
        $this->assertTrue(isset($unreadCards[$kegiatan2->id]));
    }

    /**
     * 2. Audit Seluruh Baris Transisi TugasAlurService Sesuai Tabel §3.7:
     * - Intern PIC kumpul ke staff -> menunggu_staff (pemegang staff)
     * - Intern trusted kumpul ke kabag -> menunggu_kabag (pemegang kabag)
     * - Staff PIC kumpul ke kabag -> menunggu_kabag (pemegang kabag)
     * - Staff review kirim ke kabag -> menunggu_kabag (pemegang kabag)
     * - Staff review revisi ke intern -> revisi (pemegang intern)
     * - Intern kumpul kembali -> menunggu_staff (staff penerima sebelumnya)
     * - Kabag revisi tugas intern -> revisi (pemegang staff penerima)
     * - Staff PIC revisi kumpul kembali -> menunggu_kabag
     * - Kabag terima tugas -> diterima (pemegang null, diterima_at diisi)
     */
    public function test_seluruh_baris_transisi_tugas_alur_service_sesuai_tabel_3_7(): void
    {
        /** @var TugasAlurServiceInterface $alurService */
        $alurService = app(TugasAlurServiceInterface::class);

        // Baris 1: PIC intern kumpul ke staff (status selesai) -> menunggu_staff
        $tugas1 = Tugas::create([
            'judul' => 'Tugas Liputan Intern',
            'deadline' => Carbon::now()->addDays(2),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->intern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->intern->id,
            'created_by' => $this->kabag->id,
        ]);

        $alurService->kumpulkan($tugas1, $this->intern, [
            'tipe' => \App\Enums\TipePengumpulan::Teks,
            'teks' => 'Naskah berita liputan intern',
        ], $this->staff->id);

        $tugas1->refresh();
        $this->assertSame(TahapTugas::MenungguStaff, $tugas1->tahap);
        $this->assertSame($this->staff->id, $tugas1->pemegang_id);
        $this->assertSame($this->staff->id, $tugas1->staff_penerima_id);

        // Baris 1b: PIC intern trusted kumpul langsung ke kabag -> menunggu_kabag
        $tugasTrusted = Tugas::create([
            'judul' => 'Tugas Intern Trusted',
            'deadline' => Carbon::now()->addDays(2),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->trustedIntern->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->trustedIntern->id,
            'created_by' => $this->kabag->id,
        ]);

        $alurService->kumpulkan($tugasTrusted, $this->trustedIntern, [
            'tipe' => \App\Enums\TipePengumpulan::Teks,
            'teks' => 'Naskah rilis pers terpercaya',
        ], $this->kabag->id);

        $tugasTrusted->refresh();
        $this->assertSame(TahapTugas::MenungguKabag, $tugasTrusted->tahap);
        $this->assertSame($this->kabag->id, $tugasTrusted->pemegang_id);
        $this->assertNull($tugasTrusted->staff_penerima_id);

        // Baris 2: PIC staff kumpul ke kabag -> menunggu_kabag
        $tugasStaff = Tugas::create([
            'judul' => 'Tugas Staff Mandiri',
            'deadline' => Carbon::now()->addDays(2),
            'prioritas' => Prioritas::Sedang,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::BelumDikumpulkan,
            'pemegang_id' => $this->staff->id,
            'created_by' => $this->kabag->id,
        ]);

        $alurService->kumpulkan($tugasStaff, $this->staff, [
            'tipe' => \App\Enums\TipePengumpulan::Teks,
            'teks' => 'Laporan staff selesai',
        ]);

        $tugasStaff->refresh();
        $this->assertSame(TahapTugas::MenungguKabag, $tugasStaff->tahap);
        $this->assertSame($this->kabag->id, $tugasStaff->pemegang_id);

        // Baris 3: Staff penerima kirim ke kabag (dari tugas 1) -> menunggu_kabag
        $alurService->kirimKeKabag($tugas1, $this->staff);
        $tugas1->refresh();
        $this->assertSame(TahapTugas::MenungguKabag, $tugas1->tahap);
        $this->assertSame($this->kabag->id, $tugas1->pemegang_id);

        // Baris 4: Staff minta revisi ke intern
        // Set kembali tahap ke menunggu_staff untuk menguji mintaRevisi staff
        $tugas1->update([
            'tahap' => TahapTugas::MenungguStaff,
            'pemegang_id' => $this->staff->id,
        ]);
        $alurService->mintaRevisi($tugas1, $this->staff, ['Perbaiki ejaan di alinea 1']);
        $tugas1->refresh();
        $this->assertSame(TahapTugas::Revisi, $tugas1->tahap);
        $this->assertSame($this->intern->id, $tugas1->pemegang_id);

        // Baris 5: Intern kumpulkan kembali -> kembali ke staff penerima sebelumnya
        $alurService->kumpulkanKembali($tugas1, $this->intern);
        $tugas1->refresh();
        $this->assertSame(TahapTugas::MenungguStaff, $tugas1->tahap);
        $this->assertSame($this->staff->id, $tugas1->pemegang_id);

        // Teruskan ke kabag
        $alurService->kirimKeKabag($tugas1, $this->staff);
        $tugas1->refresh();
        $this->assertSame(TahapTugas::MenungguKabag, $tugas1->tahap);

        // Baris 6: Kabag minta revisi -> kembali ke staff reviewer
        $alurService->mintaRevisi($tugas1, $this->kabag, ['Tambahkan kutipan narasumber']);
        $tugas1->refresh();
        $this->assertSame(TahapTugas::Revisi, $tugas1->tahap);
        $this->assertSame($this->staff->id, $tugas1->pemegang_id);

        // Baris 7: Staff PIC revisi kumpulkan kembali -> menunggu_kabag
        $tugasStaff->update([
            'tahap' => TahapTugas::Revisi,
            'pemegang_id' => $this->staff->id,
        ]);
        $alurService->kumpulkanKembali($tugasStaff, $this->staff);
        $tugasStaff->refresh();
        $this->assertSame(TahapTugas::MenungguKabag, $tugasStaff->tahap);
        $this->assertSame($this->kabag->id, $tugasStaff->pemegang_id);

        // Baris 8: Kabag terima tugas -> diterima (pemegang null, diterima_at not null)
        $alurService->terima($tugasStaff, $this->kabag);
        $tugasStaff->refresh();
        $this->assertSame(TahapTugas::Diterima, $tugasStaff->tahap);
        $this->assertNull($tugasStaff->pemegang_id);
        $this->assertNotNull($tugasStaff->diterima_at);
    }

    /**
     * 3. Audit Otorisasi Download File Privat:
     * - File pengumpulan hanya bisa diakses user terkait (Kabag, PIC, reviewer staff)
     * - Staff lain / guest ditolak (403 / redirect)
     * - File backup database hanya bisa diakses Kabag
     * - Staff / intern / guest ditolak (403 / redirect)
     */
    public function test_otorisasi_download_file_pengumpulan_dan_backup_database(): void
    {
        // 1. Setup file pengumpulan
        $tugas = Tugas::create([
            'judul' => 'Tugas File Rahasia',
            'deadline' => Carbon::now()->addDays(3),
            'prioritas' => Prioritas::Tinggi,
            'pic_id' => $this->staff->id,
            'status' => StatusTugas::Selesai,
            'tahap' => TahapTugas::MenungguKabag,
            'pemegang_id' => $this->kabag->id,
            'created_by' => $this->kabag->id,
        ]);

        $pengumpulan = Pengumpulan::create([
            'tugas_id' => $tugas->id,
            'tipe' => 'file',
            'dikumpulkan_at' => Carbon::now(),
        ]);

        $fakeFilePath = "pengumpulan/{$tugas->id}/dokumen_uji.png";
        Storage::disk('local')->put($fakeFilePath, 'FAKECONTENTIMAGEBYTES');

        $fileModel = PengumpulanFile::create([
            'pengumpulan_id' => $pengumpulan->id,
            'path' => $fakeFilePath,
            'nama_asli' => 'dokumen_uji.png',
            'mime' => 'image/png',
            'ukuran' => 20,
        ]);

        // A. Tes akses file pengumpulan:
        // Guest -> redirect login
        $this->get(route('pengumpulan.file.download', $fileModel->id))
            ->assertRedirect(route('login'));

        // Staff lain yang tidak terlibat -> 403 Forbidden
        $this->actingAs($this->otherStaff)
            ->get(route('pengumpulan.file.download', $fileModel->id))
            ->assertStatus(403);

        // Staff PIC -> 200 OK
        $this->actingAs($this->staff)
            ->get(route('pengumpulan.file.download', $fileModel->id))
            ->assertStatus(200);

        // Kabag -> 200 OK
        $this->actingAs($this->kabag)
            ->get(route('pengumpulan.file.download', $fileModel->id))
            ->assertStatus(200);

        // B. Tes akses file backup database:
        /** @var BackupDBServiceInterface $backupService */
        $backupService = app(BackupDBServiceInterface::class);
        $backup = $backupService->createBackup($this->kabag->id);

        // Guest -> redirect login
        auth()->logout();
        $this->get(route('kabag.backup-db.download', $backup->id))
            ->assertRedirect(route('login'));

        // Staff -> 403 Forbidden
        $this->actingAs($this->staff)
            ->get(route('kabag.backup-db.download', $backup->id))
            ->assertStatus(403);

        // Intern -> 403 Forbidden
        $this->actingAs($this->intern)
            ->get(route('kabag.backup-db.download', $backup->id))
            ->assertStatus(403);

        // Kabag -> 200 OK
        $this->actingAs($this->kabag)
            ->get(route('kabag.backup-db.download', $backup->id))
            ->assertStatus(200);

        // Cleanup storage
        Storage::disk('local')->delete($fakeFilePath);
    }
}
