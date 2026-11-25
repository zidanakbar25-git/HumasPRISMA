<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kabag\BackupDB\BackupDBController as KabagBackupDBController;
use App\Http\Controllers\Kabag\JadwalKegiatan\GoogleCalendarController;
use App\Http\Controllers\Kabag\JadwalKegiatan\JadwalKegiatanController as KabagJadwalKegiatanController;
use App\Http\Controllers\Kabag\ManajemenUser\ManajemenUserController as KabagManajemenUserController;
use App\Http\Controllers\Kabag\Tugas\PengumpulanFileController;
use App\Http\Controllers\Kabag\Tugas\TugasController as KabagTugasController;
use App\Http\Controllers\Staff\JadwalKegiatan\JadwalKegiatanController as StaffJadwalKegiatanController;
use App\Http\Controllers\Staff\ManajemenAkun\ManajemenAkunController as StaffManajemenAkunController;
use App\Http\Controllers\Staff\Tugas\TugasController as StaffTugasController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman utama / redirector: halaman default adalah login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi (tanpa middleware guest agar dapat login role lain di tab baru)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute File Pengumpulan (Akses Privat dengan Policy)
Route::middleware(['auth:kabag,staff,intern', 'active'])->group(function () {
    Route::get('/pengumpulan/file/{id}/view', [PengumpulanFileController::class, 'view'])->name('pengumpulan.file.view');
    Route::get('/pengumpulan/file/{id}/download', [PengumpulanFileController::class, 'download'])->name('pengumpulan.file.download');
    Route::get('/pengumpulan/tugas/{tugasId}/download-zip', [PengumpulanFileController::class, 'downloadZip'])->name('pengumpulan.file.download-zip');
});

// Grup Kabag
Route::middleware(['auth:kabag', 'active', 'role:kabag'])->prefix('kabag')->name('kabag.')->group(function () {
    Route::get('/jadwal-kegiatan', [KabagJadwalKegiatanController::class, 'index'])->name('jadwal-kegiatan.index');
    Route::get('/google-calendar/redirect', [GoogleCalendarController::class, 'redirect'])->name('google-calendar.redirect');
    Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google-calendar.callback');
    Route::get('/tugas', [KabagTugasController::class, 'index'])->name('tugas.index');
    Route::get('/manajemen-user', [KabagManajemenUserController::class, 'index'])->name('manajemen-user.index');
    Route::get('/backup-db', [KabagBackupDBController::class, 'index'])->name('backup-db.index');
    Route::get('/backup-db/{id}/download', [KabagBackupDBController::class, 'download'])->name('backup-db.download');
});

// Grup Staff (Staff & Intern)
Route::middleware(['auth:staff,intern', 'active', 'role:staff,intern'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/jadwal-kegiatan', [StaffJadwalKegiatanController::class, 'index'])->name('jadwal-kegiatan.index');
    Route::get('/tugas', [StaffTugasController::class, 'index'])->name('tugas.index');
    Route::get('/manajemen-akun', [StaffManajemenAkunController::class, 'index'])->name('manajemen-akun.index');
});
