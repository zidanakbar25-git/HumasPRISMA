<?php

namespace App\Providers;

use App\Contracts\BackupDB\BackupDBRepositoryInterface;
use App\Contracts\BackupDB\BackupDBServiceInterface;
use App\Contracts\JadwalKegiatan\GoogleCalendarServiceInterface;
use App\Contracts\JadwalKegiatan\KegiatanRepositoryInterface;
use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Contracts\ManajemenAkun\ManajemenAkunServiceInterface;
use App\Contracts\ManajemenUser\ManajemenUserServiceInterface;
use App\Contracts\Tugas\TugasAlurServiceInterface;
use App\Contracts\Tugas\TugasRepositoryInterface;
use App\Contracts\Tugas\TugasServiceInterface;
use App\Models\BackupDB\BackupDatabase;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use App\Policies\BackupDatabasePolicy;
use App\Policies\KegiatanPolicy;
use App\Policies\TugasPolicy;
use App\Policies\UserPolicy;
use App\Repositories\BackupDB\BackupDBRepository;
use App\Repositories\JadwalKegiatan\KegiatanRepository;
use App\Repositories\Tugas\TugasRepository;
use App\Services\BackupDB\BackupDBService;
use App\Services\JadwalKegiatan\GoogleCalendarService;
use App\Services\JadwalKegiatan\KegiatanService;
use App\Services\ManajemenAkun\ManajemenAkunService;
use App\Services\ManajemenUser\ManajemenUserService;
use App\Services\Tugas\TugasAlurService;
use App\Services\Tugas\TugasService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ManajemenUserServiceInterface::class, ManajemenUserService::class);
        $this->app->bind(ManajemenAkunServiceInterface::class, ManajemenAkunService::class);
        $this->app->bind(KegiatanRepositoryInterface::class, KegiatanRepository::class);
        $this->app->bind(KegiatanServiceInterface::class, KegiatanService::class);
        $this->app->bind(GoogleCalendarServiceInterface::class, GoogleCalendarService::class);
        $this->app->bind(TugasRepositoryInterface::class, TugasRepository::class);
        $this->app->bind(TugasServiceInterface::class, TugasService::class);
        $this->app->bind(TugasAlurServiceInterface::class, TugasAlurService::class);
        $this->app->bind(BackupDBRepositoryInterface::class, BackupDBRepository::class);
        $this->app->bind(BackupDBServiceInterface::class, BackupDBService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Kegiatan::class, KegiatanPolicy::class);
        Gate::policy(Tugas::class, TugasPolicy::class);
        Gate::policy(BackupDatabase::class, BackupDatabasePolicy::class);
    }
}
