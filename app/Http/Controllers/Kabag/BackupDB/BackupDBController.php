<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\BackupDB;

use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupDBController extends Controller
{
    public function index(): View
    {
        return view('Kabag.BackupDB.index');
    }

    /**
     * Unduh file backup database (.sql) melalui controller privat berotorisasi.
     */
    public function download(int $id, BackupDBServiceInterface $service): BinaryFileResponse
    {
        $backup = $service->findById($id);

        if (! $backup) {
            abort(404, 'Data backup tidak ditemukan.');
        }

        Gate::authorize('download', $backup);

        $filePath = $service->getFilePath($backup);

        if (! File::exists($filePath)) {
            abort(404, 'File fisik backup database tidak ditemukan di server.');
        }

        return response()->download(
            $filePath,
            $backup->nama_file,
            ['Content-Type' => 'application/sql']
        );
    }
}
