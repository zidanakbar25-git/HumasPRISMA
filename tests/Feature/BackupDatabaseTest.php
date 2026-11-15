<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Enums\Role;
use App\Livewire\Kabag\BackupDB\BackupDBIndex;
use App\Models\BackupDB\BackupDatabase;
use App\Models\ManajemenUser\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private User $kabag;
    private User $staff;
    private User $intern;

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
            'name' => 'Budi Staff',
            'username' => 'budi',
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
            'name' => 'Rina Intern',
            'username' => 'rina',
            'role' => Role::Intern,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        // Bersihkan file backup uji yang mungkin dibuat
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

    public function test_only_kabag_can_access_backup_page(): void
    {
        // Guest redirects to login
        $this->get('/kabag/backup-db')
            ->assertRedirect(route('login'));

        // Staff gets 403
        $this->actingAs($this->staff)
            ->get('/kabag/backup-db')
            ->assertStatus(403);

        // Intern gets 403
        $this->actingAs($this->intern)
            ->get('/kabag/backup-db')
            ->assertStatus(403);

        // Kabag can view
        $response = $this->actingAs($this->kabag)
            ->get('/kabag/backup-db');

        $response->assertStatus(200);
        $response->assertSeeLivewire(BackupDBIndex::class);
        $response->assertSee('Backup Database');
        $response->assertSee('Backup Database Sekarang');
    }

    public function test_kabag_can_trigger_database_backup_and_creates_valid_sql_dump(): void
    {
        $this->actingAs($this->kabag);

        $test = Livewire::test(BackupDBIndex::class)
            ->call('createBackup')
            ->assertDispatched('toast', function ($event, $params) {
                return $params['type'] === 'success' && str_contains($params['message'], 'berhasil dibuat');
            });

        // Verifikasi database record tercatat
        $this->assertDatabaseCount('backup_database', 1);

        $backup = BackupDatabase::first();
        $this->assertNotNull($backup);
        $this->assertSame($this->kabag->id, $backup->created_by);
        $this->assertStringStartsWith('backup_', $backup->nama_file);
        $this->assertStringEndsWith('.sql', $backup->nama_file);
        $this->assertGreaterThan(0, $backup->ukuran);

        // Verifikasi file fisik ada di storage/app/backups
        $filePath = storage_path('app/backups/' . $backup->nama_file);
        $this->assertFileExists($filePath);

        // Verifikasi isi file memuat struktur tabel SQL
        $content = File::get($filePath);
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('users', $content);

        // Verifikasi Livewire menampilkan nama file dan tombol unduh/hapus
        $test->assertSee($backup->nama_file)
            ->assertSee('Unduh')
            ->assertSee('Hapus');
    }

    public function test_kabag_can_download_backup_file(): void
    {
        $this->actingAs($this->kabag);

        /** @var BackupDBServiceInterface $service */
        $service = app(BackupDBServiceInterface::class);
        $backup = $service->createBackup($this->kabag->id);

        $response = $this->get(route('kabag.backup-db.download', $backup->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/sql');
        $this->assertStringContainsString($backup->nama_file, (string) $response->headers->get('content-disposition'));
    }

    public function test_staff_and_guest_cannot_download_backup_file(): void
    {
        $this->actingAs($this->kabag);

        /** @var BackupDBServiceInterface $service */
        $service = app(BackupDBServiceInterface::class);
        $backup = $service->createBackup($this->kabag->id);

        // Guest redirects
        auth()->logout();
        $this->get(route('kabag.backup-db.download', $backup->id))
            ->assertRedirect(route('login'));

        // Staff gets 403
        $this->actingAs($this->staff)
            ->get(route('kabag.backup-db.download', $backup->id))
            ->assertStatus(403);

        // Intern gets 403
        $this->actingAs($this->intern)
            ->get(route('kabag.backup-db.download', $backup->id))
            ->assertStatus(403);
    }

    public function test_download_non_existent_backup_returns_404(): void
    {
        $this->actingAs($this->kabag);

        $this->get(route('kabag.backup-db.download', 999999))
            ->assertStatus(404);
    }

    public function test_kabag_can_delete_backup_physical_file_and_record(): void
    {
        $this->actingAs($this->kabag);

        /** @var BackupDBServiceInterface $service */
        $service = app(BackupDBServiceInterface::class);
        $backup = $service->createBackup($this->kabag->id);
        $filePath = storage_path('app/backups/' . $backup->nama_file);

        $this->assertFileExists($filePath);
        $this->assertDatabaseHas('backup_database', ['id' => $backup->id]);

        Livewire::test(BackupDBIndex::class)
            ->call('confirmDelete', $backup->id)
            ->assertSet('show_delete_modal', true)
            ->assertSet('delete_id', $backup->id)
            ->call('deleteBackup')
            ->assertSet('show_delete_modal', false)
            ->assertDispatched('toast', message: 'File backup berhasil dihapus.', type: 'success');

        // File fisik terhapus
        $this->assertFileDoesNotExist($filePath);

        // Record database terhapus
        $this->assertDatabaseMissing('backup_database', ['id' => $backup->id]);
    }
}
