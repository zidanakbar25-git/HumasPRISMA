<?php

declare(strict_types=1);

namespace App\Livewire\Shared\JadwalKegiatan;

use App\Contracts\JadwalKegiatan\KegiatanServiceInterface;
use App\Contracts\Tugas\TugasServiceInterface;
use App\Enums\Prioritas;
use App\Models\JadwalKegiatan\Kegiatan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class JadwalKegiatanIndex extends Component
{
    // Navigasi Tab: 'kalender', 'kegiatan', 'riwayat'
    public string $active_tab = 'kalender';

    // State Kalender (WIB)
    public int $year;
    public int $month;
    public string $selected_date;
    public string $today_wib;

    // State Unread Cards untuk Tanggal Terpilih saat ini
    public array $unread_card_ids = [];

    // Modal Popup Rincian Kegiatan
    public bool $show_detail_modal = false;
    public ?int $detail_kegiatan_id = null;
    public ?Kegiatan $detail_kegiatan = null;

    // Modal Popup Form Tambah/Edit Kegiatan (Hanya Kabag)
    public bool $show_form_modal = false;
    public ?int $form_kegiatan_id = null;
    public string $form_judul = '';
    public string $form_tanggal = '';
    public string $form_waktu_mulai = '';
    public string $form_waktu_selesai = '';
    public string $form_tempat = '';
    public string $form_deskripsi = '';
    public array $form_pic_ids = [];

    // Form Tambah Tugas Mengembang (di dalam Form Tambah Kegiatan)
    public array $draft_tasks = [];

    // Modal Tambah Tugas untuk Kegiatan yang Sudah Ada (di Popup Rincian)
    public bool $show_kegiatan_tugas_modal = false;
    public string $kegiatan_tugas_judul = '';
    public string $kegiatan_tugas_deadline = '';
    public string $kegiatan_tugas_prioritas = 'sedang';
    public ?int $kegiatan_tugas_pic_id = null;

    // Modal Konfirmasi Hapus (Hanya Kabag)
    public bool $show_delete_modal = false;
    public ?int $delete_kegiatan_id = null;
    public string $delete_kegiatan_judul = '';

    // Modal Popup Export PDF (Semua Role di Tab Riwayat)
    public bool $show_export_modal = false;
    public string $export_mode = 'bulan'; // 'bulan' | 'rentang'
    public int $export_month;
    public int $export_year;
    public string $export_start_date = '';
    public string $export_end_date = '';
    public ?string $export_error = null;

    // Google Calendar Import (Hanya Kabag, hanya saat tambah)
    public bool $show_gcal_picker = false;
    public array $gcal_events = [];
    public ?string $gcal_error = null;
    public ?string $form_google_event_id = null;
    public bool $form_from_gcal = false;
    public ?int $selected_gcal_index = null;

    public function mount(KegiatanServiceInterface $service): void
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $this->year = (int) $nowWib->year;
        $this->month = (int) $nowWib->month;
        $this->selected_date = $nowWib->format('Y-m-d');
        $this->today_wib = $this->selected_date;

        // Inisialisasi default filter export PDF
        $this->export_year = (int) $nowWib->year;
        $this->export_month = (int) $nowWib->month;
        $this->export_start_date = (clone $nowWib)->startOfMonth()->format('Y-m-d');
        $this->export_end_date = (clone $nowWib)->subDay()->format('Y-m-d');

        // Inisialisasi awal unread kegiatan untuk tanggal hari ini
        $this->initDateUnreadCards($this->selected_date, $service);

        // Tangkap session flash dari Google Calendar callback
        if (session()->has('gcal_events')) {
            $this->gcal_events = session('gcal_events');
            $this->show_gcal_picker = true;
        } elseif (session()->has('gcal_error')) {
            $this->gcal_error = session('gcal_error');
            $this->dispatch('toast', message: $this->gcal_error, type: 'error');
        }
    }

    public function setTab(string $tab): void
    {
        $this->active_tab = $tab;
    }

    public function prevMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1, 'Asia/Jakarta')->subMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1, 'Asia/Jakarta')->addMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function goToToday(KegiatanServiceInterface $service): void
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $this->year = (int) $nowWib->year;
        $this->month = (int) $nowWib->month;
        $this->selectDate($nowWib->format('Y-m-d'), $service);
    }

    public function selectDate(string $dateString, KegiatanServiceInterface $service): void
    {
        $this->selected_date = $dateString;
        $targetDate = Carbon::parse($dateString, 'Asia/Jakarta');
        $this->year = (int) $targetDate->year;
        $this->month = (int) $targetDate->month;

        $this->initDateUnreadCards($dateString, $service);
    }

    /**
     * Saat tanggal diklik:
     * 1. Rekam kegiatan yang belum dilihat ke $unread_card_ids agar kartu tetap menampilkan badge Baru.
     * 2. Tandai seluruh kegiatan di database sebagai sudah dilihat agar titik tanggal langsung berubah default (hijau).
     */
    private function initDateUnreadCards(string $dateString, KegiatanServiceInterface $service): void
    {
        $userId = (int) Auth::id();
        $kegiatans = Kegiatan::whereDate('tanggal', $dateString)
            ->withExists(['views as is_dilihat' => fn ($q) => $q->where('user_id', $userId)])
            ->get();

        foreach ($kegiatans as $k) {
            if (! $k->is_dilihat) {
                $this->unread_card_ids[$k->id] = true;
            }
        }

        // Tandai di database agar titik merah langsung kembali hijau
        $service->markDateAsSeen($dateString, $userId);
    }

    /**
     * Buka popup rincian kegiatan dan hilangkan badge Baru dari kartu.
     */
    public function openDetailModal(int $id, KegiatanServiceInterface $service): void
    {
        $userId = (int) Auth::id();
        $this->detail_kegiatan = $service->getDetailAndMarkAsSeen($id, $userId);

        if ($this->detail_kegiatan) {
            $this->detail_kegiatan_id = $id;
            // Hilangkan badge Baru saat kartu diklik
            unset($this->unread_card_ids[$id]);
            $this->show_detail_modal = true;
        }
    }

    public function closeDetailModal(): void
    {
        $this->show_detail_modal = false;
        $this->detail_kegiatan = null;
        $this->detail_kegiatan_id = null;
    }

    /**
     * Buka modal Tambah Kegiatan (Hanya Kabag).
     */
    public function openCreateModal(): void
    {
        Gate::authorize('create', Kegiatan::class);

        $this->form_kegiatan_id = null;
        $this->form_judul = '';
        $this->form_tanggal = $this->selected_date ?: $this->today_wib;
        $this->form_waktu_mulai = '';
        $this->form_waktu_selesai = '';
        $this->form_tempat = '';
        $this->form_deskripsi = '';
        $this->form_pic_ids = [];
        $this->form_google_event_id = null;
        $this->form_from_gcal = false;

        $this->resetErrorBag();
        $this->draft_tasks = [];
        $this->show_detail_modal = false;
        $this->show_form_modal = true;
    }

    /**
     * Pilih event dari daftar Google Calendar, isi form, tutup picker.
     */
    public function selectGcalEvent(int $index): void
    {
        Gate::authorize('create', Kegiatan::class);

        if (! isset($this->gcal_events[$index])) {
            return;
        }

        $event = $this->gcal_events[$index];

        // Cek apakah event ini sudah pernah diimpor
        $alreadyImported = Kegiatan::where('google_event_id', $event['id'])->exists();
        if ($alreadyImported) {
            $this->dispatch('toast', message: 'Event ini sudah pernah ditambahkan.', type: 'error');

            return;
        }

        // Isi form dengan data event
        $this->form_kegiatan_id = null;
        $this->form_judul = $event['judul'];
        $this->form_tanggal = $event['tanggal'];
        $this->form_waktu_mulai = $event['waktu_mulai'] ?? '';
        $this->form_waktu_selesai = $event['waktu_selesai'] ?? '';
        $this->form_tempat = $event['tempat'];
        $this->form_deskripsi = $event['deskripsi'];
        $this->form_pic_ids = [];
        $this->form_google_event_id = $event['id'];
        $this->form_from_gcal = true;

        $this->resetErrorBag();
        $this->draft_tasks = [];
        $this->show_gcal_picker = false;
        $this->selected_gcal_index = null;
        $this->show_form_modal = true;
    }

    /**
     * Konfirmasi event terpilih dan isi form tambah kegiatan.
     */
    public function confirmGcalSelection(): void
    {
        if ($this->selected_gcal_index === null) {
            $this->dispatch('toast', message: 'Silakan pilih salah satu event terlebih dahulu.', type: 'warning');
            return;
        }

        $this->selectGcalEvent($this->selected_gcal_index);
    }

    /**
     * Tutup popup pilih event Google Calendar.
     */
    public function closeGcalPicker(): void
    {
        $this->show_gcal_picker = false;
        $this->gcal_events = [];
        $this->gcal_error = null;
        $this->selected_gcal_index = null;
    }

    /**
     * Tambah form tugas mengembang ke bawah pada form tambah kegiatan.
     */
    public function addDraftTask(): void
    {
        $defaultDeadline = $this->form_tanggal
            ? $this->form_tanggal . 'T17:00'
            : Carbon::now('Asia/Jakarta')->addDay()->setTime(17, 0)->format('Y-m-d\TH:i');

        $this->draft_tasks[] = [
            'judul' => '',
            'deadline' => $defaultDeadline,
            'prioritas' => 'sedang',
            'pic_id' => '',
        ];
    }

    /**
     * Hapus form tugas dari form kegiatan.
     */
    public function removeDraftTask(int $index): void
    {
        if (isset($this->draft_tasks[$index])) {
            unset($this->draft_tasks[$index]);
            $this->draft_tasks = array_values($this->draft_tasks);
        }
    }

    /**
     * Buka modal Tambah Tugas untuk kegiatan yang sudah ada dari popup rincian.
     */
    public function openKegiatanTugasModal(): void
    {
        Gate::authorize('create', Kegiatan::class);

        $defaultDeadline = $this->detail_kegiatan?->tanggal
            ? Carbon::parse($this->detail_kegiatan->tanggal)->setTime(17, 0)->format('Y-m-d\TH:i')
            : Carbon::now('Asia/Jakarta')->addDay()->setTime(17, 0)->format('Y-m-d\TH:i');

        $this->kegiatan_tugas_judul = '';
        $this->kegiatan_tugas_deadline = $defaultDeadline;
        $this->kegiatan_tugas_prioritas = 'sedang';
        $this->kegiatan_tugas_pic_id = null;

        $this->resetErrorBag();
        $this->show_kegiatan_tugas_modal = true;
    }

    public function closeKegiatanTugasModal(): void
    {
        $this->show_kegiatan_tugas_modal = false;
        $this->resetErrorBag();
    }

    /**
     * Simpan tugas yang ditambahkan dari popup rincian kegiatan.
     */
    public function simpanKegiatanTugas(TugasServiceInterface $tugasService, KegiatanServiceInterface $kegiatanService): void
    {
        Gate::authorize('create', Kegiatan::class);

        $this->validate([
            'kegiatan_tugas_judul' => ['required', 'string', 'max:150'],
            'kegiatan_tugas_deadline' => ['required', 'date'],
            'kegiatan_tugas_prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'kegiatan_tugas_pic_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'kegiatan_tugas_judul.required' => 'Judul tugas wajib diisi.',
            'kegiatan_tugas_deadline.required' => 'Tenggat waktu tugas wajib diisi.',
            'kegiatan_tugas_prioritas.required' => 'Prioritas tugas wajib dipilih.',
            'kegiatan_tugas_pic_id.required' => 'PIC tugas wajib dipilih.',
            'kegiatan_tugas_pic_id.exists' => 'PIC tugas tidak valid.',
        ]);

        $tugasService->createTugas([
            'judul' => $this->kegiatan_tugas_judul,
            'deadline' => $this->kegiatan_tugas_deadline,
            'prioritas' => Prioritas::from($this->kegiatan_tugas_prioritas),
            'pic_id' => (int) $this->kegiatan_tugas_pic_id,
            'kegiatan_id' => $this->detail_kegiatan_id,
        ], (int) Auth::id());

        // Refresh detail kegiatan agar tugas baru langsung tampil
        if ($this->detail_kegiatan_id) {
            $this->detail_kegiatan = $kegiatanService->getDetailAndMarkAsSeen($this->detail_kegiatan_id, (int) Auth::id());
        }

        $this->closeKegiatanTugasModal();
        $this->dispatch('toast', message: 'Tugas berhasil ditambahkan ke kegiatan.', type: 'success');
    }

    /**
     * Buka modal Edit Kegiatan (Hanya Kabag).
     */
    public function openEditModal(int $id, KegiatanServiceInterface $service): void
    {
        $kegiatan = Kegiatan::with('pics')->findOrFail($id);
        Gate::authorize('update', $kegiatan);

        $this->form_kegiatan_id = $kegiatan->id;
        $this->form_judul = $kegiatan->judul;
        $this->form_tanggal = Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
        $this->form_waktu_mulai = $kegiatan->waktu_mulai ? substr((string) $kegiatan->waktu_mulai, 0, 5) : '';
        $this->form_waktu_selesai = $kegiatan->waktu_selesai ? substr((string) $kegiatan->waktu_selesai, 0, 5) : '';
        $this->form_tempat = $kegiatan->tempat;
        $this->form_deskripsi = $kegiatan->deskripsi ?? '';
        $this->form_pic_ids = $kegiatan->pics->pluck('id')->map(fn ($id) => (int) $id)->toArray();

        $this->resetErrorBag();
        $this->draft_tasks = [];
        $this->show_detail_modal = false;
        $this->show_form_modal = true;
    }

    public function closeFormModal(): void
    {
        $this->show_form_modal = false;
        $this->form_kegiatan_id = null;
        $this->form_google_event_id = null;
        $this->form_from_gcal = false;
        $this->resetErrorBag();
    }

    /**
     * Toggle Pilih Semua PIC pada form kegiatan.
     */
    public function toggleSelectAllPics(KegiatanServiceInterface $service): void
    {
        $availablePics = $service->getAvailablePics();
        $allIds = $availablePics->pluck('id')->map(fn ($id) => (int) $id)->toArray();

        if (count($this->form_pic_ids) === count($allIds)) {
            $this->form_pic_ids = [];
        } else {
            $this->form_pic_ids = $allIds;
        }
    }

    /**
     * Simpan Kegiatan (Tambah atau Edit).
     */
    public function simpanKegiatan(KegiatanServiceInterface $service): void
    {
        if ($this->form_kegiatan_id) {
            $kegiatan = Kegiatan::findOrFail($this->form_kegiatan_id);
            Gate::authorize('update', $kegiatan);
        } else {
            Gate::authorize('create', Kegiatan::class);
        }

        $this->validate([
            'form_judul' => ['required', 'string', 'max:150'],
            'form_tanggal' => ['required', 'date'],
            'form_waktu_mulai' => ['nullable', 'date_format:H:i'],
            'form_waktu_selesai' => ['nullable', 'date_format:H:i', 'after:form_waktu_mulai'],
            'form_tempat' => ['required', 'string', 'max:150'],
            'form_deskripsi' => ['nullable', 'string'],
            'form_pic_ids' => ['nullable', 'array'],
            'form_pic_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'form_judul.required' => 'Judul kegiatan wajib diisi.',
            'form_tanggal.required' => 'Tanggal kegiatan wajib diisi.',
            'form_tempat.required' => 'Tempat kegiatan wajib diisi.',
            'form_waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $data = [
            'judul' => $this->form_judul,
            'tanggal' => $this->form_tanggal,
            'waktu_mulai' => $this->form_waktu_mulai ?: null,
            'waktu_selesai' => $this->form_waktu_selesai ?: null,
            'tempat' => $this->form_tempat,
            'deskripsi' => $this->form_deskripsi ?: null,
        ];

        // Google Calendar import: tandai sumber dan simpan event ID
        if ($this->form_from_gcal && $this->form_google_event_id) {
            $data['sumber'] = \App\Enums\SumberKegiatan::GoogleCalendar->value;
            $data['google_event_id'] = $this->form_google_event_id;
        }

        $picIds = array_map(fn ($id) => (int) $id, $this->form_pic_ids);

        if ($this->form_kegiatan_id) {
            $kegiatan = Kegiatan::findOrFail($this->form_kegiatan_id);
            $service->updateKegiatan($kegiatan, $data, $picIds);
            $pesan = 'Kegiatan berhasil diperbarui.';
        } else {
            // Validasi draft tasks jika ada tugas yang ditambahkan di form kegiatan
            if (! empty($this->draft_tasks)) {
                $this->validate([
                    'draft_tasks.*.judul' => ['required', 'string', 'max:150'],
                    'draft_tasks.*.deadline' => ['required', 'date'],
                    'draft_tasks.*.prioritas' => ['required', 'in:rendah,sedang,tinggi'],
                    'draft_tasks.*.pic_id' => ['required', 'integer', 'exists:users,id'],
                ], [
                    'draft_tasks.*.judul.required' => 'Judul tugas wajib diisi.',
                    'draft_tasks.*.deadline.required' => 'Tenggat waktu tugas wajib diisi.',
                    'draft_tasks.*.prioritas.required' => 'Prioritas tugas wajib dipilih.',
                    'draft_tasks.*.pic_id.required' => 'PIC tugas wajib dipilih.',
                    'draft_tasks.*.pic_id.exists' => 'PIC tugas tidak valid.',
                ]);

                $kegiatan = $service->createKegiatanWithTasks($data, $picIds, (int) Auth::id(), $this->draft_tasks);
            } else {
                $kegiatan = $service->createKegiatan($data, $picIds, (int) Auth::id());
            }

            $pesan = 'Kegiatan baru berhasil ditambahkan.';
            // Karena pembuat adalah kabag, otomatis tandai kegiatan ini sebagai sudah dilihat oleh kabag sendiri
            $service->getDetailAndMarkAsSeen($kegiatan->id, (int) Auth::id());
        }

        $this->selected_date = $this->form_tanggal;
        $targetDate = Carbon::parse($this->form_tanggal, 'Asia/Jakarta');
        $this->year = (int) $targetDate->year;
        $this->month = (int) $targetDate->month;

        $this->closeFormModal();
        $this->dispatch('toast', message: $pesan, type: 'success');
    }

    /**
     * Konfirmasi Hapus Kegiatan.
     */
    public function confirmDelete(int $id): void
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            $this->closeDetailModal();
            $this->closeDeleteModal();
            $this->dispatch('toast', message: 'Kegiatan tidak ditemukan atau telah dihapus.', type: 'error');
            return;
        }

        Gate::authorize('delete', $kegiatan);

        $this->delete_kegiatan_id = $id;
        $this->delete_kegiatan_judul = $kegiatan->judul;
        $this->show_detail_modal = false;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->delete_kegiatan_id = null;
        $this->delete_kegiatan_judul = '';
    }

    public function deleteKegiatan(KegiatanServiceInterface $service, ?int $id = null): void
    {
        $targetId = $id ?? $this->delete_kegiatan_id ?? $this->detail_kegiatan_id;

        if (! $targetId) {
            $this->closeDeleteModal();
            $this->closeDetailModal();
            return;
        }

        $kegiatan = Kegiatan::find($targetId);

        if ($kegiatan) {
            Gate::authorize('delete', $kegiatan);
            $service->deleteKegiatan($kegiatan);
        }

        $this->closeDeleteModal();
        $this->closeDetailModal();
        $this->dispatch('toast', message: 'Kegiatan berhasil dihapus.', type: 'success');
    }

    public function openExportModal(): void
    {
        $this->export_error = null;
        $this->show_export_modal = true;
    }

    public function closeExportModal(): void
    {
        $this->show_export_modal = false;
        $this->export_error = null;
        $this->resetErrorBag();
    }

    public function setExportMode(string $mode): void
    {
        $this->export_mode = $mode;
        $this->export_error = null;
        $this->resetErrorBag();
    }

    public function exportPdf(KegiatanServiceInterface $service): mixed
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $yesterdayWib = (clone $nowWib)->subDay()->format('Y-m-d');

        if ($this->export_mode === 'bulan') {
            $this->validate([
                'export_month' => ['required', 'integer', 'between:1,12'],
                'export_year' => ['required', 'integer', 'min:2020', 'max:2099'],
            ]);

            $periodeDate = Carbon::createFromDate($this->export_year, $this->export_month, 1, 'Asia/Jakarta');
            $periodeText = $periodeDate->translatedFormat('F Y');
            $filename = 'rekap-kegiatan-' . sprintf('%04d-%02d', $this->export_year, $this->export_month) . '.pdf';
        } else {
            $this->validate([
                'export_start_date' => ['required', 'date'],
                'export_end_date' => [
                    'required',
                    'date',
                    'after_or_equal:export_start_date',
                    'before_or_equal:' . $yesterdayWib,
                ],
            ], [
                'export_start_date.required' => 'Tanggal awal wajib diisi.',
                'export_end_date.required' => 'Tanggal akhir wajib diisi.',
                'export_end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
                'export_end_date.before_or_equal' => 'Tanggal akhir dibatasi maksimal hari kemarin (sebelum hari ini WIB).',
            ]);

            $startDateFormatted = Carbon::parse($this->export_start_date, 'Asia/Jakarta')->translatedFormat('d M Y');
            $endDateFormatted = Carbon::parse($this->export_end_date, 'Asia/Jakarta')->translatedFormat('d M Y');
            $periodeText = "{$startDateFormatted} s/d {$endDateFormatted}";
            $filename = "rekap-kegiatan-{$this->export_start_date}-sd-{$this->export_end_date}.pdf";
        }

        $kegiatans = $service->getRiwayatForExport($this->export_mode, [
            'year' => $this->export_year,
            'month' => $this->export_month,
            'start_date' => $this->export_start_date,
            'end_date' => $this->export_end_date,
        ]);

        if ($kegiatans->isEmpty()) {
            $this->export_error = 'Tidak ada data kegiatan riwayat pada periode yang dipilih.';
            return null;
        }

        $pdf = $service->generateRiwayatPdf($kegiatans, $periodeText);
        $this->closeExportModal();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Bangun grid kalender bulanan (7 kolom, awal Senin).
     *
     * @param array<string, string> $dots
     * @return list<array{date: string, day: int, is_current_month: bool, is_today: bool, is_selected: bool, dot: ?string}>
     */
    private function buildCalendarGrid(array $dots): array
    {
        $firstDay = Carbon::createFromDate($this->year, $this->month, 1, 'Asia/Jakarta');
        $daysInMonth = (int) $firstDay->daysInMonth;
        $dayOfWeekIso = (int) $firstDay->dayOfWeekIso; // 1 = Senin, 7 = Minggu

        $grid = [];

        // 1. Hari dari bulan sebelumnya (padding)
        $prevMonthDaysCount = $dayOfWeekIso - 1;
        if ($prevMonthDaysCount > 0) {
            $prevMonthDate = (clone $firstDay)->subMonth();
            $prevDaysInMonth = (int) $prevMonthDate->daysInMonth;
            $startDay = $prevDaysInMonth - $prevMonthDaysCount + 1;

            for ($d = $startDay; $d <= $prevDaysInMonth; $d++) {
                $curDate = Carbon::createFromDate($prevMonthDate->year, $prevMonthDate->month, $d, 'Asia/Jakarta')->format('Y-m-d');
                $grid[] = [
                    'date' => $curDate,
                    'day' => $d,
                    'is_current_month' => false,
                    'is_today' => $curDate === $this->today_wib,
                    'is_selected' => $curDate === $this->selected_date,
                    'dot' => $dots[$curDate] ?? null,
                ];
            }
        }

        // 2. Hari dalam bulan berjalan
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $curDate = Carbon::createFromDate($this->year, $this->month, $d, 'Asia/Jakarta')->format('Y-m-d');
            $grid[] = [
                'date' => $curDate,
                'day' => $d,
                'is_current_month' => true,
                'is_today' => $curDate === $this->today_wib,
                'is_selected' => $curDate === $this->selected_date,
                'dot' => $dots[$curDate] ?? null,
            ];
        }

        // 3. Hari dari bulan berikutnya (padding hingga kelipatan 7)
        $remaining = (7 - (count($grid) % 7)) % 7;
        if ($remaining > 0) {
            $nextMonthDate = (clone $firstDay)->addMonth();
            for ($d = 1; $d <= $remaining; $d++) {
                $curDate = Carbon::createFromDate($nextMonthDate->year, $nextMonthDate->month, $d, 'Asia/Jakarta')->format('Y-m-d');
                $grid[] = [
                    'date' => $curDate,
                    'day' => $d,
                    'is_current_month' => false,
                    'is_today' => $curDate === $this->today_wib,
                    'is_selected' => $curDate === $this->selected_date,
                    'dot' => $dots[$curDate] ?? null,
                ];
            }
        }

        return $grid;
    }

    public function render(KegiatanServiceInterface $service): View
    {
        $userId = (int) Auth::id();
        $isKabag = Auth::user()->role === \App\Enums\Role::Kabag;

        $monthName = Carbon::createFromDate($this->year, $this->month, 1, 'Asia/Jakarta')->translatedFormat('F Y');

        // Data berdasarkan Tab Aktif
        $kalenderGrid = [];
        $kalenderKegiatans = new Collection();
        $daftarBulananGrouped = collect();
        $riwayatKegiatans = new Collection();

        if ($this->active_tab === 'kalender') {
            $kalenderData = $service->getKalenderViewData($this->year, $this->month, $this->selected_date, $userId);
            $kalenderGrid = $this->buildCalendarGrid($kalenderData['dots']);
            $kalenderKegiatans = $kalenderData['kegiatans'];
        } elseif ($this->active_tab === 'kegiatan') {
            $daftarBulanan = $service->getDaftarBulananViewData($this->year, $this->month, $userId);
            $daftarBulananGrouped = $daftarBulanan->groupBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));
        } elseif ($this->active_tab === 'riwayat') {
            $riwayatKegiatans = $service->getRiwayatViewData($userId);
        }

        $availablePics = $isKabag ? $service->getAvailablePics() : collect();

        // Kumpulkan google_event_id yang sudah diimpor untuk menandai di picker
        $importedGcalIds = [];
        if ($isKabag && $this->show_gcal_picker && ! empty($this->gcal_events)) {
            $eventIds = array_column($this->gcal_events, 'id');
            $importedGcalIds = Kegiatan::whereIn('google_event_id', $eventIds)
                ->pluck('google_event_id')
                ->toArray();
        }

        return view('livewire.shared.jadwal-kegiatan.index', [
            'isKabag' => $isKabag,
            'monthName' => $monthName,
            'kalenderGrid' => $kalenderGrid,
            'kalenderKegiatans' => $kalenderKegiatans,
            'daftarBulananGrouped' => $daftarBulananGrouped,
            'riwayatKegiatans' => $riwayatKegiatans,
            'availablePics' => $availablePics,
            'importedGcalIds' => $importedGcalIds,
            'selectedDateFormatted' => Carbon::parse($this->selected_date, 'Asia/Jakarta')->translatedFormat('l, d F Y'),
        ]);
    }
}
