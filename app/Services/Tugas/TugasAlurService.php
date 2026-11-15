<?php

declare(strict_types=1);

namespace App\Services\Tugas;

use App\Contracts\Tugas\TugasAlurServiceInterface;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Enums\TipePengumpulan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Pengumpulan;
use App\Models\Tugas\PengumpulanEditLog;
use App\Models\Tugas\PengumpulanFile;
use App\Models\Tugas\RevisiCatatan;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TugasAlurService implements TugasAlurServiceInterface
{
    public function getPilihanTujuanPengumpulan(User $aktor): Collection
    {
        // Jika aktor adalah Staff: otomatis ke Kabag, tidak perlu pilihan
        if ($aktor->role === Role::Staff) {
            return new Collection();
        }

        // Jika aktor adalah Intern:
        // Jika is_trusted = true: Kabag paling atas, lalu daftar staff aktif
        // Jika is_trusted = false: hanya daftar staff aktif
        $staffs = User::where('role', Role::Staff)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        if ($aktor->is_trusted) {
            $kabag = User::where('role', Role::Kabag)
                ->where('is_active', true)
                ->first();

            if ($kabag) {
                return (new Collection([$kabag]))->concat($staffs);
            }
        }

        return $staffs;
    }

    public function kumpulkan(Tugas $tugas, User $aktor, array $payload, ?int $tujuanUserId = null): Pengumpulan
    {
        // 1. Validasi PIC & Tahap asal
        if ($tugas->pic_id !== $aktor->id) {
            throw new InvalidArgumentException('Hanya PIC yang dapat mengumpulkan tugas ini.');
        }

        if ($tugas->tahap !== TahapTugas::BelumDikumpulkan) {
            throw new InvalidArgumentException('Tugas hanya dapat dikumpulkan saat berada pada tahap belum dikumpulkan.');
        }

        if ($tugas->status !== StatusTugas::Selesai) {
            throw new InvalidArgumentException('Tugas harus berstatus selesai sebelum dikumpulkan.');
        }

        $now = Carbon::now('Asia/Jakarta');

        // 2. Tentukan tujuan, tahap baru, dan staff penerima
        $pemegangBaruId = null;
        $staffPenerimaId = null;
        $tahapBaru = null;

        if ($aktor->role === Role::Staff) {
            // Staff otomatis ke Kabag
            $kabag = User::where('role', Role::Kabag)->where('is_active', true)->first();
            if (! $kabag) {
                throw new InvalidArgumentException('Akun Kabag aktif tidak ditemukan.');
            }
            $pemegangBaruId = $kabag->id;
            $tahapBaru = TahapTugas::MenungguKabag;
            $staffPenerimaId = null; // Karena PIC adalah staff sendiri
        } else {
            // Intern: harus memilih tujuan dari daftar valid
            if (! $tujuanUserId) {
                throw new InvalidArgumentException('Tujuan pengumpulan harus dipilih.');
            }

            $tujuanUser = User::find($tujuanUserId);
            if (! $tujuanUser || ! $tujuanUser->is_active) {
                throw new InvalidArgumentException('Tujuan pengumpulan tidak valid atau tidak aktif.');
            }

            if ($tujuanUser->role === Role::Kabag) {
                if (! $aktor->is_trusted) {
                    throw new InvalidArgumentException('Intern belum memiliki izin trusted untuk mengumpulkan langsung ke Kabag.');
                }
                $pemegangBaruId = $tujuanUser->id;
                $tahapBaru = TahapTugas::MenungguKabag;
                $staffPenerimaId = null; // Langsung ke Kabag
            } elseif ($tujuanUser->role === Role::Staff) {
                $pemegangBaruId = $tujuanUser->id;
                $tahapBaru = TahapTugas::MenungguStaff;
                $staffPenerimaId = $tujuanUser->id;
            } else {
                throw new InvalidArgumentException('Tujuan pengumpulan harus staf atau Kabag.');
            }
        }

        // 3. Simpan data pengumpulan & perbarui tugas dalam database transaction
        return DB::transaction(function () use ($tugas, $payload, $tahapBaru, $pemegangBaruId, $staffPenerimaId, $now): Pengumpulan {
            $tipe = $payload['tipe'] instanceof TipePengumpulan
                ? $payload['tipe']
                : TipePengumpulan::from($payload['tipe']);

            $pengumpulan = Pengumpulan::updateOrCreate(
                ['tugas_id' => $tugas->id],
                [
                    'tipe' => $tipe,
                    'link' => $tipe === TipePengumpulan::Link ? ($payload['link'] ?? null) : null,
                    'teks' => $tipe === TipePengumpulan::Teks ? ($payload['teks'] ?? null) : null,
                    'dikumpulkan_at' => $now,
                ]
            );

            // Simpan file jika tipe file
            if ($tipe === TipePengumpulan::File && ! empty($payload['files'])) {
                foreach ($payload['files'] as $uploadedFile) {
                    if ($uploadedFile instanceof UploadedFile) {
                        $filename = $uploadedFile->getClientOriginalName();
                        $mime = $uploadedFile->getMimeType() ?: $uploadedFile->getClientMimeType();
                        $size = $uploadedFile->getSize();

                        // Simpan di disk private: 'pengumpulan/{tugas_id}'
                        $storedPath = $uploadedFile->store("pengumpulan/{$tugas->id}", 'local');

                        PengumpulanFile::create([
                            'pengumpulan_id' => $pengumpulan->id,
                            'path' => $storedPath,
                            'nama_asli' => $filename,
                            'mime' => $mime,
                            'ukuran' => $size,
                        ]);
                    }
                }
            }

            // Perbarui tugas: tahap baru, pemegang baru, staff penerima, dan reset dilihat_pemegang_at agar badge Baru muncul untuk pemegang baru
            $tugas->update([
                'tahap' => $tahapBaru,
                'pemegang_id' => $pemegangBaruId,
                'staff_penerima_id' => $staffPenerimaId,
                'dilihat_pemegang_at' => null,
            ]);

            return $pengumpulan->fresh(['files', 'editLogs']);
        });
    }

    public function kirimKeKabag(Tugas $tugas, User $aktor): Tugas
    {
        if ($tugas->pemegang_id !== $aktor->id) {
            throw new InvalidArgumentException('Hanya pemegang tugas saat ini yang dapat meneruskan tugas ke Kabag.');
        }

        if (! in_array($tugas->tahap, [TahapTugas::MenungguStaff, TahapTugas::Revisi], true)) {
            throw new InvalidArgumentException('Tugas tidak dalam tahap yang dapat diteruskan ke Kabag.');
        }

        if ($aktor->role !== Role::Staff) {
            throw new InvalidArgumentException('Hanya staf yang dapat meneruskan tugas ke Kabag.');
        }

        $kabag = User::where('role', Role::Kabag)->where('is_active', true)->first();
        if (! $kabag) {
            throw new InvalidArgumentException('Akun Kabag aktif tidak ditemukan.');
        }

        return DB::transaction(function () use ($tugas, $kabag): Tugas {
            $tugas->update([
                'tahap' => TahapTugas::MenungguKabag,
                'pemegang_id' => $kabag->id,
                'dilihat_pemegang_at' => null,
            ]);

            return $tugas->fresh(['pic', 'pemegang', 'staffPenerima', 'pengumpulan.files', 'pengumpulan.editLogs.user']);
        });
    }

    public function mintaRevisi(Tugas $tugas, User $aktor, array $poinRevisi, ?int $targetUserId = null): RevisiCatatan
    {
        if ($tugas->pemegang_id !== $aktor->id) {
            throw new InvalidArgumentException('Hanya pemegang tugas saat ini yang dapat meminta revisi.');
        }

        // Filter poin revisi yang tidak kosong
        $poinBersih = array_values(array_filter(
            array_map('trim', $poinRevisi),
            fn ($p) => $p !== ''
        ));

        if (empty($poinBersih)) {
            throw new InvalidArgumentException('Catatan revisi minimal harus memiliki 1 poin yang tidak kosong.');
        }

        // Tentukan target penerima revisi
        $targetId = null;
        if ($aktor->role === Role::Staff) {
            if (! in_array($tugas->tahap, [TahapTugas::MenungguStaff, TahapTugas::Revisi], true)) {
                throw new InvalidArgumentException('Tugas tidak dalam tahap yang dapat direvisi oleh staf.');
            }
            $targetId = $tugas->pic_id;
        } elseif ($aktor->role === Role::Kabag) {
            if ($tugas->tahap !== TahapTugas::MenungguKabag) {
                throw new InvalidArgumentException('Tugas tidak dalam tahap yang dapat direvisi oleh Kabag.');
            }

            // Jika staff penerima ada dan aktif, ke staff penerima. Bila nonaktif, pilih targetUserId
            if ($tugas->staff_penerima_id) {
                $staffPenerima = User::find($tugas->staff_penerima_id);
                if ($staffPenerima && $staffPenerima->is_active) {
                    $targetId = $staffPenerima->id;
                } else {
                    if (! $targetUserId) {
                        throw new InvalidArgumentException('Staf penerima sebelumnya tidak aktif. Silakan pilih staf pengganti.');
                    }
                    $targetId = $targetUserId;
                }
            } else {
                // Pengumpulan intern trusted langsung ke kabag atau PIC staff sendiri
                $targetId = $tugas->pic_id;
            }
        } else {
            throw new InvalidArgumentException('Aktor tidak memiliki hak meminta revisi.');
        }

        return DB::transaction(function () use ($tugas, $aktor, $targetId, $poinBersih): RevisiCatatan {
            $updateData = [
                'tahap' => TahapTugas::Revisi,
                'pemegang_id' => $targetId,
                'dilihat_pemegang_at' => null,
            ];

            if ($aktor->role === Role::Kabag && $tugas->staff_penerima_id) {
                $updateData['staff_penerima_id'] = $targetId;
            }

            $tugas->update($updateData);

            return RevisiCatatan::create([
                'tugas_id' => $tugas->id,
                'dari_user_id' => $aktor->id,
                'ke_user_id' => $targetId,
                'catatan' => implode("\n", $poinBersih),
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
        });
    }

    public function terima(Tugas $tugas, User $aktor): Tugas
    {
        if ($aktor->role !== Role::Kabag) {
            throw new InvalidArgumentException('Hanya Kabag yang dapat menerima tugas.');
        }

        if ($tugas->tahap !== TahapTugas::MenungguKabag) {
            throw new InvalidArgumentException('Tugas tidak dalam tahap menunggu Kabag.');
        }

        $now = Carbon::now('Asia/Jakarta');

        return DB::transaction(function () use ($tugas, $now): Tugas {
            $tugas->update([
                'tahap' => TahapTugas::Diterima,
                'pemegang_id' => null,
                'diterima_at' => $now,
                'dilihat_pemegang_at' => null,
            ]);

            return $tugas->fresh(['pic', 'pemegang', 'staffPenerima', 'pengumpulan.files', 'pengumpulan.editLogs.user']);
        });
    }

    public function kumpulkanKembali(Tugas $tugas, User $aktor, ?int $tujuanUserId = null): Tugas
    {
        if ($tugas->pic_id !== $aktor->id) {
            throw new InvalidArgumentException('Hanya PIC yang dapat mengumpulkan kembali tugas.');
        }

        if ($tugas->pemegang_id !== $aktor->id) {
            throw new InvalidArgumentException('Anda bukan pemegang tugas saat ini.');
        }

        if ($tugas->tahap !== TahapTugas::Revisi) {
            throw new InvalidArgumentException('Tugas tidak sedang dalam tahap revisi.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $tahapBaru = null;
        $pemegangBaruId = null;
        $staffPenerimaId = $tugas->staff_penerima_id;

        if ($aktor->role === Role::Staff) {
            // Staff PIC otomatis langsung ke Kabag
            $kabag = User::where('role', Role::Kabag)->where('is_active', true)->first();
            if (! $kabag) {
                throw new InvalidArgumentException('Akun Kabag aktif tidak ditemukan.');
            }
            $tahapBaru = TahapTugas::MenungguKabag;
            $pemegangBaruId = $kabag->id;
        } else {
            // Intern PIC
            if ($tugas->staff_penerima_id) {
                $staffPenerima = User::find($tugas->staff_penerima_id);
                if ($staffPenerima && $staffPenerima->is_active) {
                    $tahapBaru = TahapTugas::MenungguStaff;
                    $pemegangBaruId = $staffPenerima->id;
                } else {
                    // Staff penerima sebelumnya dinonaktifkan: intern harus memilih ulang
                    if (! $tujuanUserId) {
                        throw new InvalidArgumentException('Staf penerima sebelumnya nonaktif. Silakan pilih staf penerima pengganti.');
                    }
                    $tujuanUser = User::find($tujuanUserId);
                    if (! $tujuanUser || ! $tujuanUser->is_active) {
                        throw new InvalidArgumentException('Pilihan staf penerima tidak valid atau tidak aktif.');
                    }

                    if ($tujuanUser->role === Role::Kabag) {
                        if (! $aktor->is_trusted) {
                            throw new InvalidArgumentException('Intern belum memiliki izin trusted untuk mengumpulkan langsung ke Kabag.');
                        }
                        $tahapBaru = TahapTugas::MenungguKabag;
                        $pemegangBaruId = $tujuanUser->id;
                        $staffPenerimaId = null;
                    } elseif ($tujuanUser->role === Role::Staff) {
                        $tahapBaru = TahapTugas::MenungguStaff;
                        $pemegangBaruId = $tujuanUser->id;
                        $staffPenerimaId = $tujuanUser->id;
                    } else {
                        throw new InvalidArgumentException('Tujuan pengumpulan harus staf atau Kabag.');
                    }
                }
            } else {
                // Sebelumnya langsung ke Kabag (trusted intern)
                $kabag = User::where('role', Role::Kabag)->where('is_active', true)->first();
                if (! $kabag) {
                    throw new InvalidArgumentException('Akun Kabag aktif tidak ditemukan.');
                }
                $tahapBaru = TahapTugas::MenungguKabag;
                $pemegangBaruId = $kabag->id;
            }
        }

        return DB::transaction(function () use ($tugas, $tahapBaru, $pemegangBaruId, $staffPenerimaId, $now): Tugas {
            if ($tugas->pengumpulan) {
                $tugas->pengumpulan->update(['dikumpulkan_at' => $now]);
            }

            $tugas->update([
                'tahap' => $tahapBaru,
                'pemegang_id' => $pemegangBaruId,
                'staff_penerima_id' => $staffPenerimaId,
                'dilihat_pemegang_at' => null,
            ]);

            return $tugas->fresh(['pic', 'pemegang', 'staffPenerima', 'pengumpulan.files', 'pengumpulan.editLogs.user', 'revisiTerbaru']);
        });
    }

    public function tambahFile(Tugas $tugas, User $aktor, UploadedFile $file): PengumpulanFile
    {
        $pengumpulan = $tugas->pengumpulan;
        if (! $pengumpulan) {
            throw new InvalidArgumentException('Data pengumpulan tugas belum dibuat.');
        }

        $filename = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $size = $file->getSize();

        return DB::transaction(function () use ($pengumpulan, $tugas, $aktor, $file, $filename, $mime, $size): PengumpulanFile {
            $storedPath = $file->store("pengumpulan/{$tugas->id}", 'local');

            $createdFile = PengumpulanFile::create([
                'pengumpulan_id' => $pengumpulan->id,
                'path' => $storedPath,
                'nama_asli' => $filename,
                'mime' => $mime,
                'ukuran' => $size,
            ]);

            if ($aktor->role === Role::Staff) {
                PengumpulanEditLog::create([
                    'pengumpulan_id' => $pengumpulan->id,
                    'user_id' => $aktor->id,
                    'aksi' => 'tambah_file',
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            return $createdFile;
        });
    }

    public function hapusFile(Tugas $tugas, User $aktor, int $fileId): bool
    {
        $pengumpulan = $tugas->pengumpulan;
        if (! $pengumpulan) {
            throw new InvalidArgumentException('Data pengumpulan tugas belum dibuat.');
        }

        $file = PengumpulanFile::where('pengumpulan_id', $pengumpulan->id)->findOrFail($fileId);

        return DB::transaction(function () use ($pengumpulan, $file, $aktor): bool {
            if (Storage::disk('local')->exists($file->path)) {
                Storage::disk('local')->delete($file->path);
            }

            $deleted = (bool) $file->delete();

            if ($deleted && $aktor->role === Role::Staff) {
                PengumpulanEditLog::create([
                    'pengumpulan_id' => $pengumpulan->id,
                    'user_id' => $aktor->id,
                    'aksi' => 'hapus_file',
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            return $deleted;
        });
    }

    public function editLink(Tugas $tugas, User $aktor, string $link): void
    {
        $pengumpulan = $tugas->pengumpulan;
        if (! $pengumpulan) {
            throw new InvalidArgumentException('Data pengumpulan tugas belum dibuat.');
        }

        DB::transaction(function () use ($pengumpulan, $aktor, $link): void {
            $pengumpulan->update(['link' => $link]);

            if ($aktor->role === Role::Staff) {
                PengumpulanEditLog::create([
                    'pengumpulan_id' => $pengumpulan->id,
                    'user_id' => $aktor->id,
                    'aksi' => 'edit_link',
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        });
    }

    public function editTeks(Tugas $tugas, User $aktor, string $teks): void
    {
        $pengumpulan = $tugas->pengumpulan;
        if (! $pengumpulan) {
            throw new InvalidArgumentException('Data pengumpulan tugas belum dibuat.');
        }

        DB::transaction(function () use ($pengumpulan, $aktor, $teks): void {
            $pengumpulan->update(['teks' => $teks]);

            if ($aktor->role === Role::Staff) {
                PengumpulanEditLog::create([
                    'pengumpulan_id' => $pengumpulan->id,
                    'user_id' => $aktor->id,
                    'aksi' => 'edit_teks',
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        });
    }
}
