<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ManajemenUser\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Kabag (Default atau dari ENV)
        User::updateOrCreate(
            ['username' => env('KABAG_USERNAME', 'kabag')],
            [
                'name' => env('KABAG_NAME', 'Kepala Bagian Humas'),
                'password' => Hash::make(env('KABAG_PASSWORD', 'password')),
                'role' => Role::Kabag,
                'is_active' => true,
                'is_trusted' => false,
            ]
        );

        // 2. Akun contoh Staff & Intern hanya di environment lokal
        if (App::environment('local')) {
            // Staff 1
            User::updateOrCreate(
                ['username' => 'staff1'],
                [
                    'name' => 'Budi Santoso',
                    'password' => Hash::make('password'),
                    'role' => Role::Staff,
                    'is_active' => true,
                    'is_trusted' => false,
                ]
            );

            // Staff 2
            User::updateOrCreate(
                ['username' => 'staff2'],
                [
                    'name' => 'Siti Rahma',
                    'password' => Hash::make('password'),
                    'role' => Role::Staff,
                    'is_active' => true,
                    'is_trusted' => false,
                ]
            );

            // Intern 1 (Reguler)
            User::updateOrCreate(
                ['username' => 'intern1'],
                [
                    'name' => 'Ahmad Farhan',
                    'password' => Hash::make('password'),
                    'role' => Role::Intern,
                    'is_active' => true,
                    'is_trusted' => false,
                ]
            );

            // Intern 2 (Trusted)
            User::updateOrCreate(
                ['username' => 'intern2'],
                [
                    'name' => 'Dewi Lestari',
                    'password' => Hash::make('password'),
                    'role' => Role::Intern,
                    'is_active' => true,
                    'is_trusted' => true,
                ]
            );

            // Contoh akun nonaktif untuk pengujian keamanan
            User::updateOrCreate(
                ['username' => 'nonaktif'],
                [
                    'name' => 'User Nonaktif',
                    'password' => Hash::make('password'),
                    'role' => Role::Intern,
                    'is_active' => false,
                    'is_trusted' => false,
                ]
            );

            // 3. Contoh beberapa kegiatan untuk environment lokal
            $kabagUser = User::where('role', Role::Kabag->value)->first();
            $staff1 = User::where('username', 'staff1')->first();
            $staff2 = User::where('username', 'staff2')->first();
            $intern1 = User::where('username', 'intern1')->first();
            $intern2 = User::where('username', 'intern2')->first();

            $nowWib = \Carbon\Carbon::now('Asia/Jakarta');

            // Kegiatan 1: Lampau (Riwayat)
            $kegiatanLampau = \App\Models\JadwalKegiatan\Kegiatan::updateOrCreate(
                ['judul' => 'Rapat Koordinasi Publikasi Media Daerah'],
                [
                    'deskripsi' => 'Pembahasan agenda rilis berita bulanan bersama rekan-rekan media daerah.',
                    'tanggal' => (clone $nowWib)->subDays(4)->format('Y-m-d'),
                    'waktu_mulai' => '09:00:00',
                    'waktu_selesai' => '11:30:00',
                    'tempat' => 'Ruang Rapat Utama Biro Humas',
                    'sumber' => \App\Enums\SumberKegiatan::Manual,
                    'created_by' => $kabagUser?->id,
                ]
            );
            if ($staff1 && $intern1) {
                $kegiatanLampau->pics()->sync([$staff1->id, $intern1->id]);
            }

            // Kegiatan 2: Hari Ini (Berjadwal jam)
            $kegiatanHariIni1 = \App\Models\JadwalKegiatan\Kegiatan::updateOrCreate(
                ['judul' => 'Konferensi Pers Mingguan Pemda'],
                [
                    'deskripsi' => 'Liputan langsung dan penyiapan siaran pers mingguan terkait capaian kinerja dinas.',
                    'tanggal' => $nowWib->format('Y-m-d'),
                    'waktu_mulai' => '10:00:00',
                    'waktu_selesai' => '12:00:00',
                    'tempat' => 'Press Room Media Center',
                    'sumber' => \App\Enums\SumberKegiatan::Manual,
                    'created_by' => $kabagUser?->id,
                ]
            );
            if ($staff1 && $staff2) {
                $kegiatanHariIni1->pics()->sync([$staff1->id, $staff2->id]);
            }

            // Kegiatan 3: Hari Ini (Seharian - tanpa jam)
            $kegiatanHariIni2 = \App\Models\JadwalKegiatan\Kegiatan::updateOrCreate(
                ['judul' => 'Pameran Foto Dokumentasi Pembangunan Daerah'],
                [
                    'deskripsi' => 'Penjagaan stan dokumentasi visual dan peliputan pengunjung pameran.',
                    'tanggal' => $nowWib->format('Y-m-d'),
                    'waktu_mulai' => null,
                    'waktu_selesai' => null,
                    'tempat' => 'Lobby Gedung Pertemuan Pusat',
                    'sumber' => \App\Enums\SumberKegiatan::Manual,
                    'created_by' => $kabagUser?->id,
                ]
            );
            if ($intern1 && $intern2) {
                $kegiatanHariIni2->pics()->sync([$intern1->id, $intern2->id]);
            }

            // Kegiatan 4: Mendatang (Besok)
            $kegiatanBesok = \App\Models\JadwalKegiatan\Kegiatan::updateOrCreate(
                ['judul' => 'Peliputan Kunjungan Kerja Kementerian'],
                [
                    'deskripsi' => 'Dokumentasi foto dan video kunjungan kerja pejabat kementerian pusat.',
                    'tanggal' => (clone $nowWib)->addDay()->format('Y-m-d'),
                    'waktu_mulai' => '13:30:00',
                    'waktu_selesai' => '16:00:00',
                    'tempat' => 'Bandara & Kawasan Industri Terpadu',
                    'sumber' => \App\Enums\SumberKegiatan::Manual,
                    'created_by' => $kabagUser?->id,
                ]
            );
            if ($staff2 && $intern2) {
                $kegiatanBesok->pics()->sync([$staff2->id, $intern2->id]);
            }

            // Tandai beberapa kegiatan sebagai sudah dilihat oleh kabag sendiri agar rapi
            if ($kabagUser) {
                \App\Models\JadwalKegiatan\KegiatanDilihat::firstOrCreate(
                    ['kegiatan_id' => $kegiatanLampau->id, 'user_id' => $kabagUser->id],
                    ['dilihat_at' => $nowWib]
                );
            }

            // 4. Contoh Tugas untuk environment lokal
            if ($staff1 && $intern1 && $staff2) {
                \App\Models\Tugas\Tugas::updateOrCreate(
                    ['judul' => 'Rilis Berita Konferensi Pers'],
                    [
                        'deadline' => (clone $nowWib)->setTime(16, 0),
                        'prioritas' => \App\Enums\Prioritas::Tinggi,
                        'pic_id' => $staff1->id,
                        'kegiatan_id' => $kegiatanHariIni1->id,
                        'status' => \App\Enums\StatusTugas::SedangDikerjakan,
                        'tahap' => \App\Enums\TahapTugas::BelumDikumpulkan,
                        'pemegang_id' => $staff1->id,
                        'dilihat_pemegang_at' => (clone $nowWib)->subHours(2),
                        'created_by' => $kabagUser?->id,
                    ]
                );

                \App\Models\Tugas\Tugas::updateOrCreate(
                    ['judul' => 'Dokumentasi Foto Stan Pameran'],
                    [
                        'deadline' => (clone $nowWib)->setTime(17, 30),
                        'prioritas' => \App\Enums\Prioritas::Sedang,
                        'pic_id' => $intern1->id,
                        'kegiatan_id' => $kegiatanHariIni2->id,
                        'status' => \App\Enums\StatusTugas::BelumDikerjakan,
                        'tahap' => \App\Enums\TahapTugas::BelumDikumpulkan,
                        'pemegang_id' => $intern1->id,
                        'dilihat_pemegang_at' => null, // Menampilkan badge "Baru" untuk intern1
                        'created_by' => $kabagUser?->id,
                    ]
                );

                \App\Models\Tugas\Tugas::updateOrCreate(
                    ['judul' => 'Penyusunan Jadwal Liputan Mingguan'],
                    [
                        'deadline' => (clone $nowWib)->addDays(2)->setTime(12, 0),
                        'prioritas' => \App\Enums\Prioritas::Rendah,
                        'pic_id' => $staff2->id,
                        'kegiatan_id' => null,
                        'status' => \App\Enums\StatusTugas::BelumDikerjakan,
                        'tahap' => \App\Enums\TahapTugas::BelumDikumpulkan,
                        'pemegang_id' => $staff2->id,
                        'dilihat_pemegang_at' => null,
                        'created_by' => $kabagUser?->id,
                    ]
                );
            }
        }
    }
}
