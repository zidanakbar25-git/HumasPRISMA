<?php

declare(strict_types=1);

namespace App\Services\BackupDB;

use App\Contracts\BackupDB\BackupDBRepositoryInterface;
use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Models\BackupDB\BackupDatabase;
use Carbon\Carbon;
use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDBService implements BackupDBServiceInterface
{
    public function __construct(
        protected BackupDBRepositoryInterface $repository
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getAllBackups(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?BackupDatabase
    {
        return $this->repository->findById($id);
    }

    /**
     * {@inheritDoc}
     */
    public function createBackup(int $creatorId): BackupDatabase
    {
        $backupDir = storage_path('app/backups');

        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $nowWib = Carbon::now('Asia/Jakarta');
        $namaFile = 'backup_' . $nowWib->format('Ymd_His') . '.sql';
        $targetPath = $backupDir . DIRECTORY_SEPARATOR . $namaFile;

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        try {
            if ($connection === 'sqlite') {
                $dsn = 'sqlite:' . $dbConfig['database'];
                $dump = new Mysqldump($dsn);
            } else {
                $host = $dbConfig['host'] ?? '127.0.0.1';
                $port = $dbConfig['port'] ?? 3306;
                $database = $dbConfig['database'];
                $username = $dbConfig['username'] ?? 'root';
                $password = $dbConfig['password'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$database}";

                $dumpSettings = [
                    'add-drop-table' => true,
                    'single-transaction' => true,
                    'lock-tables' => false,
                    'add-locks' => true,
                ];

                $dump = new Mysqldump($dsn, $username, $password, $dumpSettings);
            }

            $dump->start($targetPath);

            $ukuran = File::size($targetPath);

            return $this->repository->create([
                'nama_file' => $namaFile,
                'ukuran' => $ukuran,
                'created_by' => $creatorId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Backup database failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Bersihkan file parsial jika sempat dibuat
            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }

            throw new \RuntimeException('Gagal membuat backup database: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteBackup(BackupDatabase $backup): bool
    {
        $filePath = $this->getFilePath($backup);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return $this->repository->delete($backup);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilePath(BackupDatabase $backup): string
    {
        return storage_path('app/backups' . DIRECTORY_SEPARATOR . $backup->nama_file);
    }
}
