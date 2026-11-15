<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\Tugas;

use App\Http\Controllers\Controller;
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\Tugas;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class PengumpulanFileController extends Controller
{
    /**
     * Tampilkan gambar langsung (preview inline) untuk penampil gambar popup.
     */
    public function view(int $fileId): Response|BinaryFileResponse
    {
        $file = PengumpulanFile::with('pengumpulan.tugas')->findOrFail($fileId);
        $tugas = $file->pengumpulan->tugas;

        Gate::authorize('viewPengumpulan', $tugas);

        if (! Storage::disk('local')->exists($file->path)) {
            abort(404, 'File tidak ditemukan di penyimpanan.');
        }

        $fullPath = Storage::disk('local')->path($file->path);

        return response()->file($fullPath, [
            'Content-Type' => $file->mime,
            'Content-Disposition' => 'inline; filename="' . $file->nama_asli . '"',
        ]);
    }

    /**
     * Download file individual.
     */
    public function download(int $fileId): StreamedResponse
    {
        $file = PengumpulanFile::with('pengumpulan.tugas')->findOrFail($fileId);
        $tugas = $file->pengumpulan->tugas;

        Gate::authorize('viewPengumpulan', $tugas);

        if (! Storage::disk('local')->exists($file->path)) {
            abort(404, 'File tidak ditemukan di penyimpanan.');
        }

        return Storage::disk('local')->download($file->path, $file->nama_asli);
    }

    /**
     * Download seluruh file pengumpulan dalam format ZIP.
     */
    public function downloadZip(int $tugasId): BinaryFileResponse
    {
        $tugas = Tugas::with('pengumpulan.files')->findOrFail($tugasId);

        Gate::authorize('viewPengumpulan', $tugas);

        $pengumpulan = $tugas->pengumpulan;
        if (! $pengumpulan || $pengumpulan->files->isEmpty()) {
            abort(404, 'Tidak ada file untuk diunduh.');
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tugas->judul);
        $zipFilename = "Pengumpulan_{$tugas->id}_{$safeTitle}.zip";
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file ZIP.');
        }

        foreach ($pengumpulan->files as $file) {
            if (Storage::disk('local')->exists($file->path)) {
                $zip->addFile(Storage::disk('local')->path($file->path), $file->nama_asli);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
    }
}
