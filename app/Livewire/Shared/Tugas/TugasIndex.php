<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Tugas;

use App\Contracts\Tugas\TugasAlurServiceInterface;
use App\Contracts\Tugas\TugasServiceInterface;
use App\Enums\Prioritas;
use App\Enums\Role;
use App\Enums\StatusTugas;
use App\Enums\TahapTugas;
use App\Enums\TipePengumpulan;
use App\Models\ManajemenUser\User;
use App\Models\Tugas\Tugas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class TugasIndex extends Component
{
    use WithFileUploads;

    // Role & Identitas Pengguna
    public string $user_role;
    public int $user_id;

    // Tab Aktif:
    // Kabag: 'daftar_tugas', 'selesai', 'riwayat'
    // Staff: 'tugas_saya', 'pengumpulan_intern', 'riwayat'
    // Intern: 'tugas_saya', 'revisi', 'riwayat'
    public string $active_tab = 'daftar_tugas';

    // Modal Popup Rincian Tugas
    public bool $show_detail_modal = false;
    public ?int $detail_tugas_id = null;
    public ?Tugas $detail_tugas = null;

    // Modal Popup Form Tambah/Edit Tugas (Kabag)
    public bool $show_form_modal = false;
    public ?int $form_tugas_id = null;
    public string $form_judul = '';
    public string $form_deadline = '';
    public string $form_prioritas = 'sedang';
    public ?int $form_pic_id = null;
    public ?int $form_kegiatan_id = null;
    public bool $form_can_change_pic = true;

    // Modal Konfirmasi Hapus Tugas (Kabag)
    public bool $show_delete_modal = false;
    public ?int $delete_tugas_id = null;
    public string $delete_tugas_judul = '';

    // Modal Popup Kumpulkan Tugas (PIC)
    public bool $show_kumpulkan_modal = false;
    public string $kumpul_tipe = 'file'; // 'file' | 'link' | 'teks'
    public array $kumpul_files = []; // UploadedFile[]
    public string $kumpul_link = '';
    public string $kumpul_teks = '';
    public ?int $kumpul_tujuan_user_id = null;

    // Modal Popup Hasil Pengumpulan (Kabag pada Tab Selesai)
    public bool $show_hasil_modal = false;
    public ?Tugas $hasil_tugas = null;
    public bool $hasil_mode_revisi = false;
    public array $hasil_poin_revisi = [''];
    public ?int $hasil_staff_pengganti_id = null;
    public bool $hasil_staff_penerima_nonaktif = false;
    public string $hasil_teks = '';

    // Modal Review Pengumpulan Intern (Staff pada Tab Pengumpulan Intern)
    public bool $show_review_modal = false;
    public ?Tugas $review_tugas = null;
    public bool $review_mode_revisi = false; // false = tampilan review, true = form catatan revisi
    public array $poin_revisi = [''];
    public array $review_new_files = []; // UploadedFile[]
    public string $review_link = '';
    public string $review_teks = '';

    // Modal Revisi Tugas (Intern pada Tab Revisi)
    public bool $show_revisi_modal = false;
    public ?Tugas $revisi_tugas = null;
    public array $revisi_new_files = []; // UploadedFile[]
    public string $revisi_link = '';
    public string $revisi_teks = '';
    public bool $revisi_staff_penerima_nonaktif = false;
    public ?int $revisi_tujuan_user_id = null;

    // Lightbox / Penampil Gambar Fullscreen
    public bool $show_lightbox = false;
    public int $lightbox_current_index = 0;
    public array $lightbox_images = []; // array of ['id' => ..., 'nama_asli' => ..., 'url' => ...]

    public function mount(): void
    {
        $user = Auth::user();
        $this->user_id = (int) $user->id;
        $this->user_role = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($this->user_role === Role::Kabag->value) {
            $this->active_tab = 'daftar_tugas';
        } else {
            $this->active_tab = 'tugas_saya';
        }
    }

    public function setTab(string $tab): void
    {
        $this->active_tab = $tab;
    }

    /**
     * Buka Popup Rincian Tugas.
     */
    public function openDetailModal(int $id, TugasServiceInterface $service): void
    {
        $tugas = $service->getTugasDetailAndMarkAsSeen($id, $this->user_id);
        Gate::authorize('view', $tugas);

        $this->detail_tugas_id = $id;
        $this->detail_tugas = $tugas;
        $this->show_detail_modal = true;
    }

    public function closeDetailModal(): void
    {
        $this->show_detail_modal = false;
        $this->detail_tugas = null;
        $this->detail_tugas_id = null;
    }

    /**
     * Ubah status pengerjaan secara manual oleh PIC.
     */
    public function updateStatusManual(string $statusValue, TugasServiceInterface $service): void
    {
        if (! $this->detail_tugas) {
            return;
        }

        Gate::authorize('updateStatus', $this->detail_tugas);

        $status = StatusTugas::tryFrom($statusValue);
        if (! $status) {
            return;
        }

        $updated = $service->updateStatusByPic($this->detail_tugas, $status, $this->user_id);
        $this->detail_tugas = $updated;

        $this->dispatch('toast', message: 'Status tugas berhasil diperbarui.', type: 'success');
    }

    /**
     * Buka Modal Popup Kumpulkan Tugas (PIC).
     */
    public function openKumpulkanModal(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->detail_tugas) {
            return;
        }

        Gate::authorize('submit', $this->detail_tugas);

        $user = Auth::user();
        $this->kumpul_tipe = 'file';
        $this->kumpul_files = [];
        $this->kumpul_link = '';
        $this->kumpul_teks = '';
        $this->kumpul_tujuan_user_id = null;

        // Jika intern, set default tujuan pilihan pertama bila ada
        if ($user->role === Role::Intern) {
            $pilihan = $alurService->getPilihanTujuanPengumpulan($user);
            if ($pilihan->isNotEmpty()) {
                $this->kumpul_tujuan_user_id = (int) $pilihan->first()->id;
            }
        }

        $this->resetErrorBag();
        $this->show_detail_modal = false;
        $this->show_kumpulkan_modal = true;
    }

    public function closeKumpulkanModal(): void
    {
        $this->show_kumpulkan_modal = false;
        $this->kumpul_files = [];
        $this->kumpul_link = '';
        $this->kumpul_teks = '';
        $this->resetErrorBag();
    }

    /**
     * Hapus satu file dari antrean upload modal kumpulkan.
     */
    public function removeKumpulFile(int $index): void
    {
        if (isset($this->kumpul_files[$index])) {
            unset($this->kumpul_files[$index]);
            $this->kumpul_files = array_values($this->kumpul_files);
        }
    }

    /**
     * Eksekusi Pengumpulan Tugas.
     */
    public function submitPengumpulan(TugasAlurServiceInterface $alurService, TugasServiceInterface $tugasService): void
    {
        if (! $this->detail_tugas_id) {
            return;
        }

        $tugas = $tugasService->getTugasDetailAndMarkAsSeen($this->detail_tugas_id, $this->user_id);
        Gate::authorize('submit', $tugas);

        $user = Auth::user();

        // Validasi form kumpulkan
        $rules = [
            'kumpul_tipe' => ['required', 'in:file,link,teks'],
        ];

        $messages = [
            'kumpul_tipe.required' => 'Pilih jenis pengumpulan.',
        ];

        if ($this->kumpul_tipe === 'file') {
            $rules['kumpul_files'] = ['required', 'array', 'min:1'];
            $rules['kumpul_files.*'] = ['file', 'mimes:jpg,jpeg,png', 'max:51200']; // Maks 50 MB = 51200 KB
            $messages['kumpul_files.required'] = 'Minimal unggah 1 file gambar.';
            $messages['kumpul_files.min'] = 'Minimal unggah 1 file gambar.';
            $messages['kumpul_files.*.mimes'] = 'Format file harus berupa JPG, JPEG, atau PNG.';
            $messages['kumpul_files.*.max'] = 'Ukuran tiap file maksimal 50 MB.';
        } elseif ($this->kumpul_tipe === 'link') {
            $rules['kumpul_link'] = ['required', 'url', 'max:500'];
            $messages['kumpul_link.required'] = 'Tautan / link wajib diisi.';
            $messages['kumpul_link.url'] = 'Format URL tidak valid (harus diawali http:// atau https://).';
        } elseif ($this->kumpul_tipe === 'teks') {
            $rules['kumpul_teks'] = ['required', 'string'];
            $messages['kumpul_teks.required'] = 'Teks narasi berita wajib diisi.';
        }

        if ($user->role === Role::Intern) {
            $rules['kumpul_tujuan_user_id'] = ['required', 'integer', 'exists:users,id'];
            $messages['kumpul_tujuan_user_id.required'] = 'Tujuan pengumpulan wajib dipilih.';
        }

        $this->validate($rules, $messages);

        $payload = [
            'tipe' => TipePengumpulan::from($this->kumpul_tipe),
            'files' => $this->kumpul_files,
            'link' => $this->kumpul_link,
            'teks' => $this->kumpul_teks,
        ];

        $alurService->kumpulkan($tugas, $user, $payload, $this->kumpul_tujuan_user_id);

        $this->closeKumpulkanModal();
        $this->dispatch('toast', message: 'Tugas berhasil dikumpulkan.', type: 'success');
    }

    /**
     * Buka Modal Hasil Pengumpulan (Untuk Kabag pada Tab Selesai / Riwayat).
     */
    public function openHasilModal(int $id, TugasServiceInterface $service): void
    {
        $tugas = $service->getTugasDetailAndMarkAsSeen($id, $this->user_id);
        Gate::authorize('viewPengumpulan', $tugas);

        $this->hasil_tugas = $tugas;
        $this->hasil_mode_revisi = false;
        $this->hasil_poin_revisi = [''];
        $this->hasil_staff_pengganti_id = null;
        $this->hasil_staff_penerima_nonaktif = false;

        // Cek apakah ada staf penerima dan apakah telah nonaktif
        if ($tugas->staff_penerima_id) {
            $st = User::find($tugas->staff_penerima_id);
            if (! $st || ! $st->is_active) {
                $this->hasil_staff_penerima_nonaktif = true;
            }
        }

        $this->hasil_teks = (string) ($tugas->pengumpulan?->teks ?? '');

        $this->resetErrorBag();
        $this->show_hasil_modal = true;
    }

    public function closeHasilModal(): void
    {
        $this->show_hasil_modal = false;
        $this->hasil_tugas = null;
        $this->hasil_mode_revisi = false;
        $this->hasil_poin_revisi = [''];
        $this->hasil_staff_pengganti_id = null;
        $this->hasil_staff_penerima_nonaktif = false;
        $this->hasil_teks = '';
        $this->resetErrorBag();
    }

    public function switchHasilModeRevisi(bool $isRevisi): void
    {
        $this->hasil_mode_revisi = $isRevisi;
        if ($isRevisi && empty(array_filter($this->hasil_poin_revisi, fn ($p) => trim((string) $p) !== ''))) {
            $this->hasil_poin_revisi = [''];
        }
        $this->resetErrorBag();
    }

    public function tambahHasilPoinRevisi(): void
    {
        $this->hasil_poin_revisi[] = '';
    }

    public function hapusHasilPoinRevisi(int $index): void
    {
        if (count($this->hasil_poin_revisi) > 1) {
            unset($this->hasil_poin_revisi[$index]);
            $this->hasil_poin_revisi = array_values($this->hasil_poin_revisi);
        }
    }

    public function kirimRevisiKabagAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->hasil_tugas) {
            return;
        }

        Gate::authorize('mintaRevisi', $this->hasil_tugas);

        $poinBersih = array_values(array_filter(
            array_map('trim', $this->hasil_poin_revisi),
            fn ($p) => $p !== ''
        ));

        if (empty($poinBersih)) {
            $this->addError('hasil_poin_revisi.0', 'Tuliskan minimal 1 poin catatan revisi.');
            return;
        }

        if ($this->hasil_staff_penerima_nonaktif) {
            $this->validate([
                'hasil_staff_pengganti_id' => ['required', 'integer', 'exists:users,id'],
            ], [
                'hasil_staff_pengganti_id.required' => 'Pilih staf pengganti penerima tugas.',
            ]);
        }

        $alurService->mintaRevisi($this->hasil_tugas, Auth::user(), $poinBersih, $this->hasil_staff_pengganti_id);

        $this->closeHasilModal();
        $this->dispatch('toast', message: 'Catatan revisi telah dikirimkan.', type: 'success');
    }

    public function terimaTugasAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->hasil_tugas) {
            return;
        }

        Gate::authorize('terima', $this->hasil_tugas);

        $alurService->terima($this->hasil_tugas, Auth::user());

        $this->closeHasilModal();
        $this->dispatch('toast', message: 'Tugas telah diterima dan dipindahkan ke Riwayat.', type: 'success');
    }

    public function simpanTeksKabagAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->hasil_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->hasil_tugas);

        $this->validate([
            'hasil_teks' => ['required', 'string'],
        ], [
            'hasil_teks.required' => 'Teks narasi berita wajib diisi.',
        ]);

        $alurService->editTeks($this->hasil_tugas, Auth::user(), $this->hasil_teks);

        $this->hasil_tugas = $this->hasil_tugas->fresh(['pengumpulan']);
        $this->dispatch('toast', message: 'Teks narasi berhasil diperbarui.', type: 'success');
    }

    // ==========================================
    // REVIEW PENGUMPULAN INTERN (STAFF)
    // ==========================================

    /**
     * Buka Modal Review Pengumpulan Intern untuk Staff.
     */
    public function openReviewModal(int $id, TugasServiceInterface $service): void
    {
        $tugas = $service->getTugasDetailAndMarkAsSeen($id, $this->user_id);
        Gate::authorize('reviewStaff', $tugas);

        $this->review_tugas = $tugas;
        $this->review_mode_revisi = false;
        $this->poin_revisi = [''];
        $this->review_new_files = [];
        $this->review_link = (string) ($tugas->pengumpulan?->link ?? '');
        $this->review_teks = (string) ($tugas->pengumpulan?->teks ?? '');

        $this->resetErrorBag();
        $this->show_detail_modal = false;
        $this->show_review_modal = true;
    }

    public function closeReviewModal(): void
    {
        $this->show_review_modal = false;
        $this->review_tugas = null;
        $this->review_mode_revisi = false;
        $this->poin_revisi = [''];
        $this->review_new_files = [];
        $this->resetErrorBag();
    }

    public function switchModeRevisi(bool $isRevisi): void
    {
        $this->review_mode_revisi = $isRevisi;
        if ($isRevisi && empty(array_filter($this->poin_revisi, fn ($p) => trim((string) $p) !== ''))) {
            $this->poin_revisi = [''];
        }
        $this->resetErrorBag();
    }

    public function tambahPoinRevisi(): void
    {
        $this->poin_revisi[] = '';
    }

    public function hapusPoinRevisi(int $index): void
    {
        if (count($this->poin_revisi) > 1) {
            unset($this->poin_revisi[$index]);
            $this->poin_revisi = array_values($this->poin_revisi);
        }
    }

    public function kirimKeKabagAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('kirimKeKabag', $this->review_tugas);

        $alurService->kirimKeKabag($this->review_tugas, Auth::user());

        $this->closeReviewModal();
        $this->dispatch('toast', message: 'Tugas berhasil dikirimkan ke Kabag.', type: 'success');
    }

    public function kirimRevisiKeInternAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('mintaRevisi', $this->review_tugas);

        $poinBersih = array_values(array_filter(
            array_map('trim', $this->poin_revisi),
            fn ($p) => $p !== ''
        ));

        if (empty($poinBersih)) {
            $this->addError('poin_revisi.0', 'Tuliskan minimal 1 poin catatan revisi.');
            return;
        }

        $alurService->mintaRevisi($this->review_tugas, Auth::user(), $poinBersih);

        $this->closeReviewModal();
        $this->dispatch('toast', message: 'Catatan revisi telah dikirimkan ke intern.', type: 'success');
    }

    public function tambahFileReviewAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->review_tugas);

        $this->validate([
            'review_new_files' => ['required', 'array', 'min:1'],
            'review_new_files.*' => ['file', 'mimes:jpg,jpeg,png', 'max:51200'],
        ], [
            'review_new_files.required' => 'Pilih file gambar terlebih dahulu.',
            'review_new_files.min' => 'Pilih minimal 1 file gambar.',
            'review_new_files.*.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
            'review_new_files.*.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        foreach ($this->review_new_files as $f) {
            if ($f instanceof UploadedFile) {
                $alurService->tambahFile($this->review_tugas, Auth::user(), $f);
            }
        }

        $this->review_new_files = [];
        $this->review_tugas = $this->review_tugas->fresh(['pengumpulan.files', 'pengumpulan.editLogs.user']);
        $this->dispatch('toast', message: 'File baru berhasil ditambahkan.', type: 'success');
    }

    public function hapusFileReviewAction(int $fileId, TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->review_tugas);

        $alurService->hapusFile($this->review_tugas, Auth::user(), $fileId);

        $this->review_tugas = $this->review_tugas->fresh(['pengumpulan.files', 'pengumpulan.editLogs.user']);
        $this->dispatch('toast', message: 'File berhasil dihapus.', type: 'success');
    }

    public function simpanLinkReviewAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->review_tugas);

        $this->validate([
            'review_link' => ['required', 'url', 'max:500'],
        ], [
            'review_link.required' => 'Tautan / URL wajib diisi.',
            'review_link.url' => 'Format URL tidak valid (harus diawali http:// atau https://).',
        ]);

        $alurService->editLink($this->review_tugas, Auth::user(), $this->review_link);

        $this->review_tugas = $this->review_tugas->fresh(['pengumpulan.files', 'pengumpulan.editLogs.user']);
        $this->dispatch('toast', message: 'Tautan pengumpulan berhasil diperbarui.', type: 'success');
    }

    public function simpanTeksReviewAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->review_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->review_tugas);

        $this->validate([
            'review_teks' => ['required', 'string'],
        ], [
            'review_teks.required' => 'Teks narasi berita wajib diisi.',
        ]);

        $alurService->editTeks($this->review_tugas, Auth::user(), $this->review_teks);

        $this->review_tugas = $this->review_tugas->fresh(['pengumpulan.files', 'pengumpulan.editLogs.user']);
        $this->dispatch('toast', message: 'Teks narasi berhasil diperbarui.', type: 'success');
    }

    // ==========================================
    // REVISI TUGAS OLEH INTERN
    // ==========================================

    /**
     * Buka Modal Revisi Tugas untuk Intern.
     */
    public function openRevisiModal(int $id, TugasServiceInterface $service): void
    {
        $tugas = $service->getTugasDetailAndMarkAsSeen($id, $this->user_id);
        Gate::authorize('view', $tugas);

        $this->revisi_tugas = $tugas;
        $this->revisi_new_files = [];
        $this->revisi_link = (string) ($tugas->pengumpulan?->link ?? '');
        $this->revisi_teks = (string) ($tugas->pengumpulan?->teks ?? '');

        // Periksa apakah staff penerima sebelumnya telah dinonaktifkan
        $this->revisi_staff_penerima_nonaktif = false;
        if ($tugas->staff_penerima_id) {
            $staff = User::find($tugas->staff_penerima_id);
            if (! $staff || ! $staff->is_active) {
                $this->revisi_staff_penerima_nonaktif = true;
            }
        }
        $this->revisi_tujuan_user_id = null;

        $this->resetErrorBag();
        $this->show_detail_modal = false;
        $this->show_revisi_modal = true;
    }

    public function closeRevisiModal(): void
    {
        $this->show_revisi_modal = false;
        $this->revisi_tugas = null;
        $this->revisi_new_files = [];
        $this->revisi_link = '';
        $this->revisi_teks = '';
        $this->revisi_staff_penerima_nonaktif = false;
        $this->revisi_tujuan_user_id = null;
        $this->resetErrorBag();
    }

    public function tambahFileRevisiAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->revisi_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->revisi_tugas);

        $this->validate([
            'revisi_new_files' => ['required', 'array', 'min:1'],
            'revisi_new_files.*' => ['file', 'mimes:jpg,jpeg,png', 'max:51200'],
        ], [
            'revisi_new_files.required' => 'Pilih file gambar terlebih dahulu.',
            'revisi_new_files.min' => 'Pilih minimal 1 file gambar.',
            'revisi_new_files.*.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
            'revisi_new_files.*.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        foreach ($this->revisi_new_files as $f) {
            if ($f instanceof UploadedFile) {
                $alurService->tambahFile($this->revisi_tugas, Auth::user(), $f);
            }
        }

        $this->revisi_new_files = [];
        $this->revisi_tugas = $this->revisi_tugas->fresh(['pengumpulan.files', 'revisiTerbaru.dariUser']);
        $this->dispatch('toast', message: 'File berhasil ditambahkan.', type: 'success');
    }

    public function hapusFileRevisiAction(int $fileId, TugasAlurServiceInterface $alurService): void
    {
        if (! $this->revisi_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->revisi_tugas);

        $alurService->hapusFile($this->revisi_tugas, Auth::user(), $fileId);

        $this->revisi_tugas = $this->revisi_tugas->fresh(['pengumpulan.files', 'revisiTerbaru.dariUser']);
        $this->dispatch('toast', message: 'File berhasil dihapus.', type: 'success');
    }

    public function simpanLinkRevisiAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->revisi_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->revisi_tugas);

        $this->validate([
            'revisi_link' => ['required', 'url', 'max:500'],
        ], [
            'revisi_link.required' => 'Tautan / URL wajib diisi.',
            'revisi_link.url' => 'Format URL tidak valid (harus diawali http:// atau https://).',
        ]);

        $alurService->editLink($this->revisi_tugas, Auth::user(), $this->revisi_link);

        $this->revisi_tugas = $this->revisi_tugas->fresh(['pengumpulan.files', 'revisiTerbaru.dariUser']);
        $this->dispatch('toast', message: 'Tautan pengumpulan berhasil disimpan.', type: 'success');
    }

    public function simpanTeksRevisiAction(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->revisi_tugas) {
            return;
        }

        Gate::authorize('editPengumpulan', $this->revisi_tugas);

        $this->validate([
            'revisi_teks' => ['required', 'string'],
        ], [
            'revisi_teks.required' => 'Teks narasi berita wajib diisi.',
        ]);

        $alurService->editTeks($this->revisi_tugas, Auth::user(), $this->revisi_teks);

        $this->revisi_tugas = $this->revisi_tugas->fresh(['pengumpulan.files', 'revisiTerbaru.dariUser']);
        $this->dispatch('toast', message: 'Teks narasi berhasil disimpan.', type: 'success');
    }

    public function submitKumpulkanKembali(TugasAlurServiceInterface $alurService): void
    {
        if (! $this->revisi_tugas) {
            return;
        }

        Gate::authorize('kumpulkanKembali', $this->revisi_tugas);

        $tipe = $this->revisi_tugas->pengumpulan?->tipe->value;

        if ($tipe === 'file') {
            if ($this->revisi_tugas->pengumpulan->files->isEmpty()) {
                $this->addError('revisi_files', 'Minimal harus ada 1 file gambar yang terunggah.');
                return;
            }
        } elseif ($tipe === 'link') {
            $this->validate([
                'revisi_link' => ['required', 'url', 'max:500'],
            ], [
                'revisi_link.required' => 'Tautan / URL wajib diisi.',
                'revisi_link.url' => 'Format URL tidak valid.',
            ]);
            $alurService->editLink($this->revisi_tugas, Auth::user(), $this->revisi_link);
        } elseif ($tipe === 'teks') {
            $this->validate([
                'revisi_teks' => ['required', 'string'],
            ], [
                'revisi_teks.required' => 'Teks narasi berita wajib diisi.',
            ]);
            $alurService->editTeks($this->revisi_tugas, Auth::user(), $this->revisi_teks);
        }

        if ($this->revisi_staff_penerima_nonaktif) {
            $this->validate([
                'revisi_tujuan_user_id' => ['required', 'integer', 'exists:users,id'],
            ], [
                'revisi_tujuan_user_id.required' => 'Pilih staf penerima pengganti.',
            ]);
        }

        $alurService->kumpulkanKembali($this->revisi_tugas, Auth::user(), $this->revisi_tujuan_user_id);

        $this->closeRevisiModal();
        $this->dispatch('toast', message: 'Tugas berhasil dikumpulkan kembali.', type: 'success');
    }

    // ==========================================
    // LIGHTBOX / PENAMPIL GAMBAR
    // ==========================================

    /**
     * Buka Lightbox Penampil Gambar Layar Penuh.
     */
    public function openLightbox(int $initialFileId): void
    {
        $target = $this->hasil_tugas ?? $this->review_tugas ?? $this->revisi_tugas;
        if (! $target || ! $target->pengumpulan) {
            return;
        }

        $this->lightbox_images = [];
        $currentIndex = 0;

        foreach ($target->pengumpulan->files as $idx => $f) {
            $this->lightbox_images[] = [
                'id' => $f->id,
                'nama_asli' => $f->nama_asli,
                'url' => route('pengumpulan.file.view', ['id' => $f->id]),
            ];

            if ($f->id === $initialFileId) {
                $currentIndex = $idx;
            }
        }

        $this->lightbox_current_index = $currentIndex;
        $this->show_lightbox = true;
    }

    public function closeLightbox(): void
    {
        $this->show_lightbox = false;
    }

    public function prevLightboxImage(): void
    {
        if (empty($this->lightbox_images)) {
            return;
        }

        $this->lightbox_current_index = ($this->lightbox_current_index - 1 + count($this->lightbox_images)) % count($this->lightbox_images);
    }

    public function nextLightboxImage(): void
    {
        if (empty($this->lightbox_images)) {
            return;
        }

        $this->lightbox_current_index = ($this->lightbox_current_index + 1) % count($this->lightbox_images);
    }

    /**
     * Buka Modal Form Tambah Tugas (Kabag).
     */
    public function openCreateModal(?int $kegiatanId = null): void
    {
        Gate::authorize('create', Tugas::class);

        $this->form_tugas_id = null;
        $this->form_judul = '';
        $this->form_deadline = Carbon::now('Asia/Jakarta')->addDay()->setTime(17, 0)->format('Y-m-d\TH:i');
        $this->form_prioritas = 'sedang';
        $this->form_pic_id = null;
        $this->form_kegiatan_id = $kegiatanId;
        $this->form_can_change_pic = true;

        $this->resetErrorBag();
        $this->show_detail_modal = false;
        $this->show_form_modal = true;
    }

    /**
     * Buka Modal Form Edit Tugas (Kabag).
     */
    public function openEditModal(int $id, TugasServiceInterface $service): void
    {
        $tugas = $service->getTugasDetailAndMarkAsSeen($id, $this->user_id);
        Gate::authorize('update', $tugas);

        $this->form_tugas_id = $tugas->id;
        $this->form_judul = $tugas->judul;
        $this->form_deadline = Carbon::parse($tugas->deadline)->format('Y-m-d\TH:i');
        $this->form_prioritas = $tugas->prioritas->value ?? (string) $tugas->prioritas;
        $this->form_pic_id = (int) $tugas->pic_id;
        $this->form_kegiatan_id = $tugas->kegiatan_id ? (int) $tugas->kegiatan_id : null;
        $this->form_can_change_pic = ($tugas->tahap === TahapTugas::BelumDikumpulkan);

        $this->resetErrorBag();
        $this->show_detail_modal = false;
        $this->show_form_modal = true;
    }

    public function closeFormModal(): void
    {
        $this->show_form_modal = false;
        $this->form_tugas_id = null;
        $this->resetErrorBag();
    }

    /**
     * Simpan Tugas (Tambah / Edit).
     */
    public function simpanTugas(TugasServiceInterface $service): void
    {
        if ($this->form_tugas_id) {
            $tugas = Tugas::findOrFail($this->form_tugas_id);
            Gate::authorize('update', $tugas);
        } else {
            Gate::authorize('create', Tugas::class);
        }

        $rules = [
            'form_judul' => ['required', 'string', 'max:150'],
            'form_deadline' => ['required', 'date'],
            'form_prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'form_pic_id' => ['required', 'integer', 'exists:users,id'],
            'form_kegiatan_id' => ['nullable', 'integer', 'exists:kegiatan,id'],
        ];

        $messages = [
            'form_judul.required' => 'Judul tugas wajib diisi.',
            'form_judul.max' => 'Judul tugas maksimal 150 karakter.',
            'form_deadline.required' => 'Tenggat waktu (deadline) wajib diisi.',
            'form_prioritas.required' => 'Prioritas wajib dipilih.',
            'form_pic_id.required' => 'PIC tugas wajib dipilih.',
            'form_pic_id.exists' => 'PIC tugas tidak valid.',
            'form_kegiatan_id.exists' => 'Kegiatan terkait tidak valid.',
        ];

        $this->validate($rules, $messages);

        $prioritasEnum = Prioritas::from($this->form_prioritas);

        $data = [
            'judul' => $this->form_judul,
            'deadline' => $this->form_deadline,
            'prioritas' => $prioritasEnum,
            'pic_id' => (int) $this->form_pic_id,
            'kegiatan_id' => $this->form_kegiatan_id ? (int) $this->form_kegiatan_id : null,
        ];

        if ($this->form_tugas_id) {
            $service->updateTugas($tugas, $data);
            $pesan = 'Tugas berhasil diperbarui.';
        } else {
            $service->createTugas($data, $this->user_id);
            $pesan = 'Tugas baru berhasil ditambahkan.';
        }

        $this->closeFormModal();
        $this->dispatch('toast', message: $pesan, type: 'success');
    }

    /**
     * Konfirmasi Hapus Tugas.
     */
    public function confirmDelete(int $id): void
    {
        $tugas = Tugas::findOrFail($id);
        Gate::authorize('delete', $tugas);

        $this->delete_tugas_id = $id;
        $this->delete_tugas_judul = $tugas->judul;
        $this->show_detail_modal = false;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->delete_tugas_id = null;
        $this->delete_tugas_judul = '';
    }

    public function deleteTugas(TugasServiceInterface $service): void
    {
        $tugas = Tugas::findOrFail($this->delete_tugas_id);
        Gate::authorize('delete', $tugas);

        $service->deleteTugas($tugas);

        $this->closeDeleteModal();
        $this->dispatch('toast', message: 'Tugas berhasil dihapus.', type: 'success');
    }

    public function render(TugasServiceInterface $service, TugasAlurServiceInterface $alurService): View
    {
        $isKabag = $this->user_role === Role::Kabag->value;

        if ($isKabag) {
            $tugasList = $service->getTugasForKabag($this->active_tab);
        } else {
            $tugasList = $service->getTugasForUser($this->user_id, $this->active_tab);
        }

        $availablePics = $isKabag ? $service->getAvailablePics() : collect();
        $availableKegiatan = $isKabag ? $service->getAvailableKegiatan() : collect();
        $availableStaff = User::where('role', Role::Staff)->where('is_active', true)->orderBy('name', 'asc')->get();

        $currentUser = Auth::user();
        $pilihanTujuan = (! $isKabag && $currentUser)
            ? $alurService->getPilihanTujuanPengumpulan($currentUser)
            : collect();

        return view('livewire.shared.tugas.tugas-index', [
            'isKabag' => $isKabag,
            'tugasList' => $tugasList,
            'availablePics' => $availablePics,
            'availableKegiatan' => $availableKegiatan,
            'availableStaff' => $availableStaff,
            'pilihanTujuan' => $pilihanTujuan,
        ]);
    }
}
