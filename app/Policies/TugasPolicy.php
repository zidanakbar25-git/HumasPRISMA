<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Enums\TahapTugas;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;

class TugasPolicy
{
    /**
     * Semua user aktif (Kabag, Staff, Intern) dapat melihat halaman daftar tugas mereka.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Otorisasi melihat rincian tugas.
     */
    public function view(User $user, Tugas $tugas): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === Role::Kabag) {
            return true;
        }

        return $tugas->pic_id === $user->id
            || $tugas->pemegang_id === $user->id
            || $tugas->staff_penerima_id === $user->id;
    }

    /**
     * Hanya Kabag yang dapat membuat tugas baru.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag yang dapat mengedit tugas.
     */
    public function update(User $user, Tugas $tugas): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya Kabag yang dapat menghapus tugas.
     */
    public function delete(User $user, Tugas $tugas): bool
    {
        return $user->is_active && $user->role === Role::Kabag;
    }

    /**
     * Hanya PIC yang dapat mengubah status tugas (belum/sedang/selesai).
     */
    public function updateStatus(User $user, Tugas $tugas): bool
    {
        return $user->is_active && $tugas->pic_id === $user->id;
    }

    /**
     * PIC hanya bisa diganti jika tahap belum_dikumpulkan.
     */
    public function changePic(User $user, Tugas $tugas): bool
    {
        return $user->is_active
            && $user->role === Role::Kabag
            && $tugas->tahap === TahapTugas::BelumDikumpulkan;
    }

    /**
     * Otorisasi pengumpulan tugas oleh PIC.
     */
    public function submit(User $user, Tugas $tugas): bool
    {
        return $user->is_active
            && $tugas->pic_id === $user->id
            && $tugas->tahap === TahapTugas::BelumDikumpulkan;
    }

    /**
     * Otorisasi melihat dan mendownload file hasil pengumpulan.
     */
    public function viewPengumpulan(User $user, Tugas $tugas): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === Role::Kabag) {
            return true;
        }

        return $tugas->pic_id === $user->id
            || $tugas->pemegang_id === $user->id
            || $tugas->staff_penerima_id === $user->id;
    }

    /**
     * Otorisasi review oleh Staff penerima tugas intern.
     */
    public function reviewStaff(User $user, Tugas $tugas): bool
    {
        return $user->is_active
            && $user->role === Role::Staff
            && $tugas->pemegang_id === $user->id
            && in_array($tugas->tahap, [TahapTugas::MenungguStaff, TahapTugas::Revisi], true);
    }

    /**
     * Otorisasi meneruskan tugas ke Kabag oleh staff penerima.
     */
    public function kirimKeKabag(User $user, Tugas $tugas): bool
    {
        return $this->reviewStaff($user, $tugas);
    }

    /**
     * Otorisasi meminta revisi ke Intern oleh staff penerima.
     */
    public function mintaRevisi(User $user, Tugas $tugas): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === Role::Staff) {
            return $tugas->pemegang_id === $user->id
                && in_array($tugas->tahap, [TahapTugas::MenungguStaff, TahapTugas::Revisi], true);
        }

        if ($user->role === Role::Kabag) {
            return $tugas->tahap === TahapTugas::MenungguKabag;
        }

        return false;
    }

    /**
     * Otorisasi mengumpulkan kembali tugas revisi oleh PIC.
     */
    public function kumpulkanKembali(User $user, Tugas $tugas): bool
    {
        return $user->is_active
            && $tugas->pic_id === $user->id
            && $tugas->pemegang_id === $user->id
            && $tugas->tahap === TahapTugas::Revisi;
    }

    /**
     * Otorisasi menerima hasil pengumpulan oleh Kabag.
     */
    public function terima(User $user, Tugas $tugas): bool
    {
        return $user->is_active
            && $user->role === Role::Kabag
            && $tugas->tahap === TahapTugas::MenungguKabag;
    }

    /**
     * Otorisasi mengedit pengumpulan (menambah/menghapus file, ubah link/teks).
     */
    public function editPengumpulan(User $user, Tugas $tugas): bool
    {
        if (! $user->is_active) {
            return false;
        }

        // Staff yang sedang me-review tugas intern
        if ($user->role === Role::Staff && $tugas->pemegang_id === $user->id && in_array($tugas->tahap, [TahapTugas::MenungguStaff, TahapTugas::Revisi], true)) {
            return true;
        }

        // PIC (Intern atau Staff) yang sedang memperbaiki tugas revisi miliknya
        if ($tugas->pic_id === $user->id && $tugas->pemegang_id === $user->id && $tugas->tahap === TahapTugas::Revisi) {
            return true;
        }

        // Kabag yang sedang melihat pengumpulan di tahap menunggu kabag (hanya untuk teks)
        if ($user->role === Role::Kabag && $tugas->tahap === TahapTugas::MenungguKabag) {
            return true;
        }

        return false;
    }
}
