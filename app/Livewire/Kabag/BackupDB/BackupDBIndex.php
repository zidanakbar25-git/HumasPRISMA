<?php

declare(strict_types=1);

namespace App\Livewire\Kabag\BackupDB;

use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Models\BackupDB\BackupDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class BackupDBIndex extends Component
{
    public bool $show_delete_modal = false;
    public ?int $delete_id = null;
    public string $delete_nama_file = '';

    /**
     * Jalankan proses backup database (dump struktur + seluruh data).
     */
    public function createBackup(BackupDBServiceInterface $service): void
    {
        Gate::authorize('create', BackupDatabase::class);

        try {
            $backup = $service->createBackup((int) Auth::id());

            $this->dispatch('toast', 
                message: "Backup database '{$backup->nama_file}' berhasil dibuat.", 
                type: 'success'
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', 
                message: 'Gagal membuat backup: ' . $e->getMessage(), 
                type: 'error'
            );
        }
    }

    /**
     * Buka modal konfirmasi hapus file backup.
     */
    public function confirmDelete(int $id, BackupDBServiceInterface $service): void
    {
        $backup = $service->findById($id);

        if (! $backup) {
            $this->dispatch('toast', message: 'Data backup tidak ditemukan.', type: 'error');
            return;
        }

        Gate::authorize('delete', $backup);

        $this->delete_id = $id;
        $this->delete_nama_file = $backup->nama_file;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->delete_id = null;
        $this->delete_nama_file = '';
    }

    /**
     * Eksekusi penghapusan file backup fisik dan catatan database.
     */
    public function deleteBackup(BackupDBServiceInterface $service): void
    {
        if (! $this->delete_id) {
            return;
        }

        $backup = $service->findById($this->delete_id);

        if (! $backup) {
            $this->closeDeleteModal();
            $this->dispatch('toast', message: 'Data backup tidak ditemukan.', type: 'error');
            return;
        }

        Gate::authorize('delete', $backup);

        $service->deleteBackup($backup);

        $this->closeDeleteModal();
        $this->dispatch('toast', message: 'File backup berhasil dihapus.', type: 'success');
    }

    public function render(BackupDBServiceInterface $service): View
    {
        $backups = $service->getAllBackups();

        return view('livewire.kabag.backup-db.index', [
            'backups' => $backups,
        ]);
    }
}
