<div class="tugas-wrapper">
    {{-- Header Halaman Tugas --}}
    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title">Tugas</h1>
    </div>

    {{-- Baris Navigasi Tab & Tombol Aksi --}}
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
        <div class="tab-list" style="margin-bottom: -1px; display: flex; gap: 8px;">
            @if($isKabag)
                {{-- Tabs Kabag --}}
                <button type="button" 
                        class="tab-item {{ $active_tab === 'daftar_tugas' ? 'active' : '' }}" 
                        wire:click="setTab('daftar_tugas')">
                    <x-icon name="tugas" :size="16" />
                    <span>Daftar Tugas</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'selesai' ? 'active' : '' }}" 
                        wire:click="setTab('selesai')">
                    <x-icon name="clock" :size="16" />
                    <span>Selesai</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'riwayat' ? 'active' : '' }}" 
                        wire:click="setTab('riwayat')">
                    <x-icon name="clock" :size="16" />
                    <span>Riwayat</span>
                </button>
            @elseif($user_role === 'intern')
                {{-- Tabs Intern --}}
                <button type="button" 
                        class="tab-item {{ $active_tab === 'tugas_saya' ? 'active' : '' }}" 
                        wire:click="setTab('tugas_saya')">
                    <x-icon name="tugas" :size="16" />
                    <span>Tugas Saya</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'revisi' ? 'active' : '' }}" 
                        wire:click="setTab('revisi')">
                    <x-icon name="edit" :size="16" />
                    <span>Revisi</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'riwayat' ? 'active' : '' }}" 
                        wire:click="setTab('riwayat')">
                    <x-icon name="clock" :size="16" />
                    <span>Riwayat</span>
                </button>
            @else
                {{-- Tabs Staff --}}
                <button type="button" 
                        class="tab-item {{ $active_tab === 'tugas_saya' ? 'active' : '' }}" 
                        wire:click="setTab('tugas_saya')">
                    <x-icon name="tugas" :size="16" />
                    <span>Tugas Saya</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'pengumpulan_intern' ? 'active' : '' }}" 
                        wire:click="setTab('pengumpulan_intern')">
                    <x-icon name="upload" :size="16" />
                    <span>Pengumpulan Intern</span>
                </button>
                <button type="button" 
                        class="tab-item {{ $active_tab === 'riwayat' ? 'active' : '' }}" 
                        wire:click="setTab('riwayat')">
                    <x-icon name="clock" :size="16" />
                    <span>Riwayat</span>
                </button>
            @endif
        </div>

        {{-- Tombol Tambah Tugas (Hanya Kabag) --}}
        @if($isKabag)
            <div style="margin-bottom: 8px;">
                <button type="button" class="btn btn-primary" wire:click="openCreateModal()">
                    <x-icon name="plus" :size="16" />
                    <span>Tambah Tugas</span>
                </button>
            </div>
        @endif
    </div>

    {{-- BILAH PENCARIAN & FILTER (Seragam untuk semua tab tugas) --}}
    <div class="task-filter-bar">
        <div class="filter-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="filter-search-icon">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="form-control filter-input" 
                   placeholder="Cari tugas, PIC, atau kegiatan..."
                   autocomplete="off">
        </div>

        <div class="filter-dropdowns-wrap">
            {{-- Filter Prioritas --}}
            <select wire:model.live="filter_prioritas" class="form-control filter-select">
                <option value="">Semua Prioritas</option>
                <option value="rendah">Rendah</option>
                <option value="sedang">Sedang</option>
                <option value="tinggi">Tinggi</option>
            </select>

            {{-- Filter Status (Disesuaikan per tab di sisi backend) --}}
            <select wire:model.live="filter_status" class="form-control filter-select">
                <option value="">Semua Status</option>
                <option value="belum_dikerjakan">Belum Dikerjakan</option>
                <option value="sedang_dikerjakan">Sedang Dikerjakan</option>
                <option value="selesai">Selesai</option>
            </select>
        </div>
    </div>

    {{-- Kontainer Daftar Kartu Tugas --}}
    @php
        $isModalOpen = $show_form_modal || $show_detail_modal || $show_delete_modal || $show_kumpulkan_modal || $show_hasil_modal || $show_lightbox || $show_review_modal || $show_revisi_modal;
    @endphp

    <div @if(! $isModalOpen) wire:poll.8s @endif>
        @if($tugasList->isEmpty())
            <div class="empty-state card">
                <x-icon name="tugas" :size="48" />
                <p class="empty-state-text">
                    @if($active_tab === 'daftar_tugas' || $active_tab === 'tugas_saya')
                        Belum ada tugas yang sedang aktif.
                    @elseif($active_tab === 'selesai')
                        Belum ada tugas yang selesai menunggu persetujuan Kabag.
                    @elseif($active_tab === 'revisi')
                        Tidak ada tugas yang memerlukan revisi.
                    @elseif($active_tab === 'pengumpulan_intern')
                        Belum ada pengumpulan dari intern.
                    @else
                        Belum ada riwayat tugas yang selesai.
                    @endif
                </p>
            </div>
        @else
            {{-- Ubah dari grid menjadi flex vertikal 1 kolom ke bawah --}}
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($tugasList as $t)
                    @php
                        // Tentukan aksi klik kartu berdasarkan tab & role
                        $clickAction = match(true) {
                            $active_tab === 'riwayat' => "openDetailModal({$t->id})",
                            $isKabag && $active_tab === 'selesai' => "openHasilModal({$t->id})",
                            !$isKabag && $user_role === 'staff' && $active_tab === 'pengumpulan_intern' => "openReviewModal({$t->id})",
                            !$isKabag && $t->tahap->value === 'revisi' => "openRevisiModal({$t->id})",
                            default => "openDetailModal({$t->id})",
                        };
                    @endphp

                    <div wire:key="tugas-card-{{ $t->id }}" 
                         class="card card-clickable task-card-horizontal" 
                         wire:click="{{ $clickAction }}">
                        
                        {{-- Sisi Kiri: Judul, Kegiatan, Deadline, & PIC --}}
                        <div class="task-card-left">
                            <div class="task-header-row">
                                {{-- Judul Tugas --}}
                                <h3 class="task-title-text">{{ $t->judul }}</h3>

                                {{-- Badge Kegiatan Terkait --}}
                                @if($t->kegiatan)
                                    <span class="badge badge-kegiatan" title="Kegiatan: {{ $t->kegiatan->judul }}">
                                        <x-icon name="calendar" :size="12" />
                                        <span class="task-kegiatan-text">{{ $t->kegiatan->judul }}</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Informasi Bawah (Deadline & PIC) --}}
                            <div class="task-meta-row">
                                <div class="task-meta-item">
                                    <x-icon name="clock" :size="14" />
                                    <span>Tenggat: {{ $t->deadline_formatted }}</span>
                                </div>

                                @if($isKabag || $active_tab === 'pengumpulan_intern')
                                    <div class="task-meta-item task-pic-text">
                                        <x-icon name="user" :size="14" />
                                        <span>{{ $t->pic?->name ?? '-' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Sisi Kanan: Badge Status, Prioritas, & Tombol Aksi Sesuai Referensi --}}
                        <div class="task-card-right">
                            <div class="task-badges-right">
                                {{-- Badge Baru --}}
                                @if($t->isBaruUntuk($user_id))
                                    <span class="badge badge-baru">Baru</span>
                                @endif

                                {{-- Badge Revisi --}}
                                @if($t->tahap->value === 'revisi')
                                    @if($user_role === 'staff')
                                        <span class="badge badge-revisi" style="background-color: var(--warning-bg); color: var(--warning);">
                                            Revisi dari Kabag
                                        </span>
                                    @else
                                        <span class="badge badge-revisi">Revisi</span>
                                    @endif
                                @endif

                                {{-- Badge Menunggu Staff (Kabag) --}}
                                @if($isKabag && $t->tahap->value === 'menunggu_staff')
                                    <span class="badge badge-di-staff">Di staff: {{ $t->staffPenerima?->name ?? 'Staff' }}</span>
                                @endif

                                {{-- Badge Status --}}
                                @php
                                    $statusClass = match($t->status->value) {
                                        'selesai' => 'badge-status-selesai',
                                        'sedang_dikerjakan' => 'badge-status-sedang',
                                        default => 'badge-status-belum',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ $t->status->label() }}
                                </span>

                                {{-- Badge Prioritas --}}
                                <span class="badge badge-prioritas-{{ $t->prioritas->value }}">
                                    {{ $t->prioritas->label() }}
                                </span>
                            </div>

                            {{-- Tombol Detail / Aksi seperti di Referensi --}}
                            <div class="task-action-btn-wrap">
                                <span class="task-detail-btn-custom">
                                    @if($active_tab === 'riwayat')
                                        Lihat Riwayat
                                    @elseif($isKabag && $active_tab === 'selesai')
                                        Lihat Pengumpulan
                                    @elseif(! $isKabag && $user_role === 'staff' && $active_tab === 'pengumpulan_intern')
                                        Review
                                    @elseif(! $isKabag && $t->tahap->value === 'revisi')
                                        Perbaiki
                                    @else
                                        Detail Tugas
                                    @endif
                                    <x-icon name="chevron-right" :size="14" />
                                </span>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- MODAL POPUP RINCIAN TUGAS --}}
    @if($show_detail_modal && $detail_tugas)
        <div class="modal-backdrop open" wire:click.self="closeDetailModal">
            <div class="modal-box" style="max-width: 620px;">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h2 class="modal-title">
                            {{ $detail_tugas->tahap->value === 'diterima' ? 'Riwayat Tugas' : 'Rincian Tugas' }}
                        </h2>
                        <span class="badge badge-prioritas-{{ $detail_tugas->prioritas->value }}">
                            {{ $detail_tugas->prioritas->label() }}
                        </span>
                    </div>
                    <button type="button" class="modal-close" wire:click="closeDetailModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                    {{-- Judul Tugas --}}
                    <div>
                        <div style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 4px;">Judul Tugas</div>
                        <div style="font-size: 1.125rem; font-weight: 700; color: var(--text); line-height: 1.4;">
                            {{ $detail_tugas->judul }}
                        </div>
                    </div>

                    {{-- Info Grid: Deadline & Status --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm);">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Tenggat Waktu</div>
                            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 6px;">
                                <x-icon name="clock" :size="15" />
                                <span>{{ $detail_tugas->deadline_formatted }}</span>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Status Saat Ini</div>
                            <div>
                                @php
                                    $detailStatusClass = match($detail_tugas->status->value) {
                                        'selesai' => 'badge-status-selesai',
                                        'sedang_dikerjakan' => 'badge-status-sedang',
                                        default => 'badge-status-belum',
                                    };
                                @endphp
                                <span class="badge {{ $detailStatusClass }}">
                                    {{ $detail_tugas->status->label() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Info PIC & Tahap --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <div style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 4px;">Penanggung Jawab (PIC)</div>
                            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 6px;">
                                <x-icon name="user" :size="16" />
                                <span>{{ $detail_tugas->pic?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 4px;">Tahap Pengumpulan</div>
                            <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text);">
                                {{ $detail_tugas->tahap->label() }}
                            </div>
                        </div>
                    </div>

                    {{-- Kegiatan Terkait --}}
                    @if($detail_tugas->kegiatan)
                        <div>
                            <div style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 6px;">Kegiatan Terkait</div>
                            <div style="padding: 10px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); display: flex; align-items: center; gap: 10px;">
                                <span class="badge badge-kegiatan">Kegiatan</span>
                                <span style="font-weight: 600; color: var(--green-900);">{{ $detail_tugas->kegiatan->judul }}</span>
                                <span style="font-size: 0.8125rem; color: var(--muted); margin-left: auto;">
                                    {{ \Carbon\Carbon::parse($detail_tugas->kegiatan->tanggal)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- BAGIAN READ-ONLY UNTUK TUGAS YANG TELAH DITERIMA (TAB RIWAYAT) --}}
                    @if($detail_tugas->tahap->value === 'diterima')
                        <div style="padding: 12px 14px; background-color: var(--success-bg); border: 1px solid rgba(63, 125, 58, 0.2); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: var(--success); display: flex; align-items: center; gap: 8px;">
                                <x-icon name="check" :size="18" />
                                <span>Tugas telah disetujui & diterima oleh Kabag</span>
                            </div>
                            @if($detail_tugas->diterima_at)
                                <span style="font-size: 0.75rem; color: var(--muted);">
                                    {{ \Carbon\Carbon::parse($detail_tugas->diterima_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            @endif
                        </div>

                        @if($detail_tugas->pengumpulan)
                            @php $dp = $detail_tugas->pengumpulan; @endphp
                            @if($dp->tipe->value === 'file')
                                <div>
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <span style="font-size: 0.875rem; font-weight: 600; color: var(--text);">
                                            File Hasil Pengumpulan ({{ $dp->files->count() }} file gambar)
                                        </span>
                                        @if($dp->files->count() > 1)
                                            <a href="{{ route('pengumpulan.file.download-zip', ['tugasId' => $detail_tugas->id]) }}" 
                                               class="btn btn-secondary" 
                                               style="height: 30px; font-size: 0.75rem; padding: 0 10px;">
                                                <x-icon name="download" :size="13" />
                                                <span>Download Semua (ZIP)</span>
                                            </a>
                                        @endif
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($dp->files as $f)
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); gap: 10px;">
                                                <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                                    <x-icon name="image" :size="18" />
                                                    <div style="overflow: hidden;">
                                                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--text); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                            {{ $f->nama_asli }}
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: var(--muted);">
                                                            {{ $f->ukuran_formatted }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                    <button type="button" 
                                                            class="btn btn-secondary" 
                                                            style="height: 30px; padding: 0 8px; font-size: 0.75rem;" 
                                                            wire:click="openLightbox({{ $f->id }})">
                                                        <x-icon name="eye" :size="13" />
                                                        <span>View</span>
                                                    </button>
                                                    <a href="{{ route('pengumpulan.file.download', ['id' => $f->id]) }}" 
                                                       class="btn btn-secondary" 
                                                       style="height: 30px; padding: 0 8px; font-size: 0.75rem;">
                                                        <x-icon name="download" :size="13" />
                                                        <span>Download</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($dp->tipe->value === 'link')
                                <div>
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--text); display: block; margin-bottom: 6px;">
                                        Tautan Hasil Pengumpulan
                                    </span>
                                    <div style="padding: 12px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                        <span style="font-size: 0.875rem; color: var(--text); word-break: break-all;">{{ $dp->link }}</span>
                                        <a href="{{ $dp->link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="height: 32px; padding: 0 10px; font-size: 0.75rem; flex-shrink: 0;">
                                            <span>Buka Link</span>
                                            <x-icon name="external-link" :size="13" />
                                        </a>
                                    </div>
                                </div>
                            @elseif($dp->tipe->value === 'teks')
                                <div>
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--text); display: block; margin-bottom: 6px;">
                                        Teks Narasi Hasil Pengumpulan
                                    </span>
                                    <div style="padding: 12px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9375rem; line-height: 1.6; color: var(--text); white-space: pre-wrap;">
                                        {{ $dp->teks }}
                                    </div>
                                </div>
                            @endif

                            @if($dp->editLogs->isNotEmpty())
                                @php
                                    $editorNames = $dp->editLogs->where('user.role', \App\Enums\Role::Staff)->pluck('user.name')->unique()->filter()->implode(', ');
                                @endphp
                                @if(! empty($editorNames))
                                    <div style="font-size: 0.8125rem; color: var(--muted); font-style: italic;">
                                        Diedit oleh: {{ $editorNames }}
                                    </div>
                                @endif
                            @endif
                        @endif
                    @endif

                    {{-- Kontrol Ubah Status Manual oleh PIC (Tahap Belum Dikumpulkan) --}}
                    @if($user_id === (int) $detail_tugas->pic_id && $detail_tugas->tahap->value === 'belum_dikumpulkan')
                        <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                            <label class="form-label" style="margin-bottom: 8px;">Ubah Status Pengerjaan (Manual)</label>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                <button type="button" 
                                        wire:click="updateStatusManual('belum_dikerjakan')"
                                        class="btn {{ $detail_tugas->status->value === 'belum_dikerjakan' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem; padding: 0 8px;">
                                    Belum Dikerjakan
                                </button>
                                <button type="button" 
                                        wire:click="updateStatusManual('sedang_dikerjakan')"
                                        class="btn {{ $detail_tugas->status->value === 'sedang_dikerjakan' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem; padding: 0 8px;">
                                    Sedang Dikerjakan
                                </button>
                                <button type="button" 
                                        wire:click="updateStatusManual('selesai')"
                                        class="btn {{ $detail_tugas->status->value === 'selesai' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem; padding: 0 8px;">
                                    Selesai
                                </button>
                            </div>

                            {{-- Tombol Kumpulkan Muncul Begitu Status Selesai --}}
                            @if($detail_tugas->status->value === 'selesai')
                                <div style="margin-top: 16px; padding: 14px; background-color: var(--success-bg); border-radius: var(--radius-sm); border: 1px solid rgba(63, 125, 58, 0.2);">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 8px; color: var(--success); font-size: 0.875rem; font-weight: 600;">
                                            <x-icon name="check" :size="18" />
                                            <span>Tugas telah selesai dikerjakan!</span>
                                        </div>
                                        <button type="button" class="btn btn-primary" wire:click="openKumpulkanModal" style="background-color: var(--success); border-color: var(--success);">
                                            <x-icon name="upload" :size="16" />
                                            <span>Kumpulkan Tugas</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Tombol Aksi Cepat Jika Tugas Sedang Dalam Revisi --}}
                    @if($user_id === (int) $detail_tugas->pic_id && $detail_tugas->tahap->value === 'revisi')
                        <div style="margin-top: 10px; padding: 14px; background-color: var(--warning-bg); border-radius: var(--radius-sm); border: 1px solid rgba(168, 105, 15, 0.2); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                            <div style="font-size: 0.875rem; color: var(--warning); font-weight: 600;">
                                @if($user_role === 'staff')
                                    Tugas ini memerlukan perbaikan revisi dari Kepala Bagian.
                                @else
                                    Tugas ini memerlukan perbaikan revisi.
                                @endif
                            </div>
                            <button type="button" class="btn btn-primary" wire:click="openRevisiModal({{ $detail_tugas->id }})">
                                <x-icon name="edit" :size="15" />
                                <span>Buka Form Revisi & Kumpulkan Kembali</span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer">
                    @if($isKabag && $detail_tugas->tahap->value !== 'diterima')
                        <button type="button" class="btn btn-secondary" wire:click="openEditModal({{ $detail_tugas->id }})">
                            <x-icon name="edit" :size="16" />
                            <span>Edit Tugas</span>
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="confirmDelete({{ $detail_tugas->id }})">
                            <x-icon name="trash" :size="16" />
                            <span>Hapus</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary" wire:click="closeDetailModal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL POPUP KUMPULKAN TUGAS (PIC PERTAMA KALI) --}}
    @if($show_kumpulkan_modal && $detail_tugas)
        <div class="modal-backdrop open" wire:click.self="closeKumpulkanModal">
            <div class="modal-box" style="max-width: 600px;">
                <div class="modal-header">
                    <h2 class="modal-title">Kumpulkan Tugas</h2>
                    <button type="button" class="modal-close" wire:click="closeKumpulkanModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <form wire:submit.prevent="submitPengumpulan">
                    <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                        {{-- Ringkasan Tugas --}}
                        <div style="padding: 12px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm);">
                            <div style="font-size: 0.75rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Mengumpulkan untuk:</div>
                            <div style="font-size: 0.9375rem; font-weight: 700; color: var(--green-900); margin-top: 2px;">
                                {{ $detail_tugas->judul }}
                            </div>
                        </div>

                        {{-- Pilihan Jenis Pengumpulan (File / Link / Teks) --}}
                        <div>
                            <label class="form-label">Jenis Pengumpulan <span style="color: var(--danger);">*</span></label>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                <button type="button" 
                                        wire:click="$set('kumpul_tipe', 'file')"
                                        class="btn {{ $kumpul_tipe === 'file' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem;">
                                    File Gambar
                                </button>
                                <button type="button" 
                                        wire:click="$set('kumpul_tipe', 'link')"
                                        class="btn {{ $kumpul_tipe === 'link' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem;">
                                    Tautan / Link
                                </button>
                                <button type="button" 
                                        wire:click="$set('kumpul_tipe', 'teks')"
                                        class="btn {{ $kumpul_tipe === 'teks' ? 'btn-primary' : 'btn-secondary' }}"
                                        style="font-size: 0.8125rem;">
                                    Teks / Narasi
                                </button>
                            </div>
                        </div>

                        {{-- Form Sesuai Jenis --}}
                        @if($kumpul_tipe === 'file')
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="kumpul_files">
                                    Unggah File (JPG, JPEG, PNG — Maks 50 MB per file) <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="file" 
                                       id="kumpul_files" 
                                       wire:model="kumpul_files" 
                                       class="form-control @error('kumpul_files') is-invalid @enderror @error('kumpul_files.*') is-invalid @enderror" 
                                       accept="image/jpeg,image/png,image/jpg" 
                                       multiple>
                                
                                <div wire:loading wire:target="kumpul_files" style="font-size: 0.8125rem; color: var(--green-700); margin-top: 6px;">
                                    Mengunggah file... Harap tunggu.
                                </div>

                                @error('kumpul_files')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                                @error('kumpul_files.*')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror

                                {{-- Daftar File Terpilih --}}
                                @if(! empty($kumpul_files))
                                    <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($kumpul_files as $idx => $f)
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.8125rem;">
                                                <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                                    <x-icon name="image" :size="16" />
                                                    <span style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                        {{ $f->getClientOriginalName() }}
                                                    </span>
                                                </div>
                                                <button type="button" class="btn btn-danger" style="height: 24px; padding: 0 6px; font-size: 0.75rem;" wire:click="removeKumpulFile({{ $idx }})">
                                                    Hapus
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @elseif($kumpul_tipe === 'link')
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="kumpul_link">
                                    Tautan / Link URL (Google Drive, Dropbox, dll.) <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="url" 
                                       id="kumpul_link" 
                                       wire:model="kumpul_link" 
                                       class="form-control @error('kumpul_link') is-invalid @enderror" 
                                       placeholder="https://drive.google.com/..." 
                                       maxlength="500">
                                @error('kumpul_link')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif($kumpul_tipe === 'teks')
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="kumpul_teks">
                                    Teks / Narasi Berita <span style="color: var(--danger);">*</span>
                                </label>
                                <textarea id="kumpul_teks" 
                                          wire:model="kumpul_teks" 
                                          class="form-control @error('kumpul_teks') is-invalid @enderror" 
                                          rows="6" 
                                          placeholder="Tuliskan draf rilis berita atau narasi kegiatan di sini..." 
                                          style="height: auto; padding: 10px 14px;"></textarea>
                                @error('kumpul_teks')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Pilihan Tujuan Pengumpulan --}}
                        @if($user_role === 'intern')
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="kumpul_tujuan_user_id">
                                    Tujuan Pengumpulan <span style="color: var(--danger);">*</span>
                                </label>
                                <select id="kumpul_tujuan_user_id" 
                                        wire:model="kumpul_tujuan_user_id" 
                                        class="form-control @error('kumpul_tujuan_user_id') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Pilih Penerima Hasil --</option>
                                    @foreach($pilihanTujuan as $tujuan)
                                        <option value="{{ $tujuan->id }}">
                                            {{ $tujuan->name }} ({{ ucfirst($tujuan->role->value ?? (string) $tujuan->role) }})
                                            @if($tujuan->role === \App\Enums\Role::Kabag) - Langsung ke Kabag @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('kumpul_tujuan_user_id')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            {{-- Untuk Staff: Otomatis ke Kabag --}}
                            <div style="padding: 10px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.875rem; color: var(--green-900); display: flex; align-items: center; gap: 8px;">
                                <x-icon name="user" :size="16" />
                                <span>Hasil pengumpulan akan langsung dikirimkan kepada <strong>Kepala Bagian (Kabag)</strong>.</span>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeKumpulkanModal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Kirim Pengumpulan</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL POPUP REVIEW PENGUMPULAN INTERN (STAFF) --}}
    @if($show_review_modal && $review_tugas)
        <div class="modal-backdrop open" wire:click.self="closeReviewModal">
            <div class="modal-box" style="max-width: 680px;">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Review Pengumpulan Intern</h2>
                        <div style="font-size: 0.8125rem; color: var(--muted); margin-top: 2px;">
                            {{ $review_tugas->judul }}
                        </div>
                    </div>
                    <button type="button" class="modal-close" wire:click="closeReviewModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                    {{-- Info Identitas Pengumpulan & Status --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 12px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm);">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600;">PIC (Intern)</div>
                            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--text); margin-top: 2px;">
                                {{ $review_tugas->pic?->name ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600;">Status Tahap</div>
                            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--green-900); margin-top: 2px;">
                                @if($review_tugas->tahap->value === 'revisi')
                                    <span style="color: var(--warning);">Revisi dari Kabag</span>
                                @else
                                    <span>Menunggu Review Staf</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(! $review_mode_revisi)
                        {{-- MODE 1: TAMPILAN REVIEW & EDIT PENGUMPULAN --}}
                        @if($review_tugas->pengumpulan)
                            @php
                                $p = $review_tugas->pengumpulan;
                            @endphp

                            {{-- Pengumpulan Tipe File --}}
                            @if($p->tipe->value === 'file')
                                <div>
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <span style="font-size: 0.875rem; font-weight: 600; color: var(--text);">
                                            File Gambar Terlampir ({{ $p->files->count() }} file)
                                        </span>
                                        @if($p->files->count() > 1)
                                            <a href="{{ route('pengumpulan.file.download-zip', ['tugasId' => $review_tugas->id]) }}" 
                                               class="btn btn-secondary" 
                                               style="height: 30px; font-size: 0.75rem; padding: 0 10px;">
                                                <x-icon name="download" :size="13" />
                                                <span>Download Semua (ZIP)</span>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Daftar File dengan Tombol Hapus per File --}}
                                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
                                        @forelse($p->files as $file)
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); gap: 10px;">
                                                <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                                    <x-icon name="image" :size="18" />
                                                    <div style="overflow: hidden;">
                                                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--text); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                            {{ $file->nama_asli }}
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: var(--muted);">
                                                            {{ $file->ukuran_formatted }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    <button type="button" 
                                                            class="btn btn-secondary" 
                                                            style="height: 30px; padding: 0 8px; font-size: 0.75rem;" 
                                                            wire:click="openLightbox({{ $file->id }})">
                                                        <x-icon name="eye" :size="13" />
                                                        <span>View</span>
                                                    </button>
                                                    <a href="{{ route('pengumpulan.file.download', ['id' => $file->id]) }}" 
                                                       class="btn btn-secondary" 
                                                       style="height: 30px; padding: 0 8px; font-size: 0.75rem;">
                                                        <x-icon name="download" :size="13" />
                                                        <span>Unduh</span>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger" 
                                                            style="height: 30px; padding: 0 8px; font-size: 0.75rem;" 
                                                            wire:click="hapusFileReviewAction({{ $file->id }})"
                                                            wire:confirm="Yakin ingin menghapus file ini dari pengumpulan?">
                                                        <x-icon name="trash" :size="13" />
                                                        <span>Hapus</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div style="font-size: 0.8125rem; color: var(--danger); padding: 8px;">
                                                Semua file telah dihapus. Harap unggah minimal 1 file gambar.
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Box Tambah File Baru oleh Staff --}}
                                    <div style="padding: 12px; background-color: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-sm);">
                                        <label class="form-label" style="margin-bottom: 6px; font-size: 0.8125rem;">
                                            Tambah File Baru (JPG, JPEG, PNG maks 50 MB):
                                        </label>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <input type="file" 
                                                   wire:model="review_new_files" 
                                                   class="form-control" 
                                                   accept="image/jpeg,image/png,image/jpg" 
                                                   multiple>
                                            <button type="button" 
                                                    class="btn btn-primary" 
                                                    style="height: 38px; flex-shrink: 0;"
                                                    wire:click="tambahFileReviewAction"
                                                    wire:loading.attr="disabled"
                                                    wire:target="review_new_files, tambahFileReviewAction">
                                                <x-icon name="upload" :size="14" />
                                                <span>Unggah</span>
                                            </button>
                                        </div>
                                        @error('review_new_files') <span class="form-error">{{ $message }}</span> @enderror
                                        @error('review_new_files.*') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @elseif($p->tipe->value === 'link')
                                {{-- Pengumpulan Tipe Link --}}
                                <div>
                                    <label class="form-label" for="review_link">
                                        Tautan / Link Google Drive atau Eksternal (Edit Langsung)
                                    </label>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="url" 
                                               id="review_link" 
                                               wire:model="review_link" 
                                               class="form-control @error('review_link') is-invalid @enderror" 
                                               placeholder="https://..." 
                                               maxlength="500">
                                        @if(! empty($review_link))
                                            <a href="{{ $review_link }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="btn btn-secondary" 
                                               style="height: 38px; flex-shrink: 0; padding: 0 10px;"
                                               title="Buka Link">
                                                <x-icon name="external-link" :size="15" />
                                            </a>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-primary" 
                                                style="height: 38px; flex-shrink: 0;"
                                                wire:click="simpanLinkReviewAction">
                                            <span>Simpan</span>
                                        </button>
                                    </div>
                                    @error('review_link') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                            @elseif($p->tipe->value === 'teks')
                                {{-- Pengumpulan Tipe Teks --}}
                                <div>
                                    <label class="form-label" for="review_teks">
                                        Teks Narasi Berita (Edit Langsung di Tempat)
                                    </label>
                                    <textarea id="review_teks" 
                                              wire:model="review_teks" 
                                              class="form-control @error('review_teks') is-invalid @enderror" 
                                              rows="7" 
                                              style="height: auto; padding: 10px 14px; margin-bottom: 8px;"></textarea>
                                    @error('review_teks') <span class="form-error">{{ $message }}</span> @enderror
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button type="button" 
                                                class="btn btn-secondary" 
                                                wire:click="simpanTeksReviewAction">
                                            <span>Simpan Perubahan Teks</span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan Log Diedit oleh Staff --}}
                            @if($p->editLogs->isNotEmpty())
                                @php
                                    $editorNames = $p->editLogs->where('user.role', \App\Enums\Role::Staff)->pluck('user.name')->unique()->filter()->implode(', ');
                                @endphp
                                @if(! empty($editorNames))
                                    <div style="font-size: 0.8125rem; color: var(--muted); font-style: italic; border-top: 1px dashed var(--border); padding-top: 10px;">
                                        Diedit oleh: {{ $editorNames }}
                                    </div>
                                @endif
                            @endif
                        @endif

                    @else
                        {{-- MODE 2: FORM CATATAN REVISI (POIN-POIN) --}}
                        <div style="padding: 14px; background-color: var(--warning-bg); border-radius: var(--radius-sm); border: 1px solid rgba(168, 105, 15, 0.2);">
                            <div style="font-size: 0.9375rem; font-weight: 700; color: var(--warning); margin-bottom: 4px;">
                                Buat Catatan Revisi untuk Intern
                            </div>
                            <p style="font-size: 0.8125rem; color: var(--text); line-height: 1.4; margin: 0;">
                                Masukkan poin-poin perbaikan yang jelas. Tugas akan dikembalikan ke intern untuk diperbaiki.
                            </p>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <label class="form-label" style="margin-bottom: 0;">Poin-Poin Catatan Revisi <span style="color: var(--danger);">*</span></label>
                            
                            @foreach($poin_revisi as $index => $poin)
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--muted); width: 24px; text-align: center;">
                                        {{ $index + 1 }}.
                                    </span>
                                    <input type="text" 
                                           wire:model="poin_revisi.{{ $index }}" 
                                           class="form-control @error('poin_revisi.'.$index) is-invalid @enderror" 
                                           placeholder="Contoh: Perbaiki resolusi foto utama dan tambahkan kutipan narasumber...">
                                    
                                    @if(count($poin_revisi) > 1)
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                style="height: 38px; width: 38px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                                                wire:click="hapusPoinRevisi({{ $index }})"
                                                title="Hapus baris poin ini">
                                            <x-icon name="trash" :size="15" />
                                        </button>
                                    @endif
                                </div>
                                @error('poin_revisi.'.$index)
                                    <span class="form-error" style="margin-left: 32px;">{{ $message }}</span>
                                @enderror
                            @endforeach

                            <div>
                                <button type="button" 
                                        class="btn btn-secondary" 
                                        style="font-size: 0.8125rem; height: 32px;" 
                                        wire:click="tambahPoinRevisi">
                                    <x-icon name="plus" :size="14" />
                                    <span>Tambah Poin Revisi</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer Review --}}
                <div class="modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    @if(! $review_mode_revisi)
                        <button type="button" class="btn btn-secondary" wire:click="closeReviewModal">
                            Tutup
                        </button>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    style="color: var(--warning); border-color: rgba(168, 105, 15, 0.4);" 
                                    wire:click="switchModeRevisi(true)">
                                <x-icon name="edit" :size="15" />
                                <span>Minta Revisi</span>
                            </button>
                            <button type="button" 
                                    class="btn btn-primary" 
                                    wire:click="kirimKeKabagAction"
                                    wire:loading.attr="disabled">
                                <x-icon name="send" :size="15" />
                                <span>Kirimkan ke Kabag</span>
                            </button>
                        </div>
                    @else
                        <button type="button" class="btn btn-secondary" wire:click="switchModeRevisi(false)">
                            Kembali ke Review
                        </button>
                        <button type="button" 
                                class="btn btn-primary" 
                                style="background-color: var(--warning); border-color: var(--warning);"
                                wire:click="kirimRevisiKeInternAction"
                                wire:loading.attr="disabled">
                            <x-icon name="send" :size="15" />
                            <span>Kirim ke Intern</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL POPUP REVISI TUGAS (INTERN ATAU STAFF PIC PADA TAB REVISI / TUGAS SAYA) --}}
    @if($show_revisi_modal && $revisi_tugas)
        <div class="modal-backdrop open" wire:click.self="closeRevisiModal">
            <div class="modal-box" style="max-width: 680px;">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Perbaikan Revisi Tugas</h2>
                        <div style="font-size: 0.8125rem; color: var(--muted); margin-top: 2px;">
                            {{ $revisi_tugas->judul }}
                        </div>
                    </div>
                    <button type="button" class="modal-close" wire:click="closeRevisiModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                    {{-- Kotak Catatan Revisi dari Staf/Kabag --}}
                    @if($revisi_tugas->revisiTerbaru)
                        <div style="padding: 14px; background-color: var(--warning-bg); border-radius: var(--radius-sm); border: 1px solid rgba(168, 105, 15, 0.3);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <div style="font-size: 0.875rem; font-weight: 700; color: var(--warning); display: flex; align-items: center; gap: 6px;">
                                    <x-icon name="alert-circle" :size="16" />
                                    <span>
                                        @if($revisi_tugas->revisiTerbaru->dariUser?->role === \App\Enums\Role::Kabag)
                                            Catatan Revisi dari Kepala Bagian (Kabag)
                                        @else
                                            Catatan Revisi dari {{ $revisi_tugas->revisiTerbaru->dariUser?->name ?? 'Staf' }}
                                        @endif
                                    </span>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--muted);">
                                    {{ \Carbon\Carbon::parse($revisi_tugas->revisiTerbaru->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            </div>

                            <ol style="margin: 0; padding-left: 20px; font-size: 0.875rem; color: var(--text); line-height: 1.6;">
                                @foreach($revisi_tugas->revisiTerbaru->poin_list as $poin)
                                    <li style="margin-bottom: 4px;">{{ $poin }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    {{-- Form Edit Pengumpulan Berdasarkan Tipe --}}
                    @if($revisi_tugas->pengumpulan)
                        @php
                            $rp = $revisi_tugas->pengumpulan;
                        @endphp

                        @if($rp->tipe->value === 'file')
                            <div>
                                <label class="form-label" style="margin-bottom: 8px;">
                                    File Pengumpulan Saat Ini ({{ $rp->files->count() }} file)
                                </label>

                                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                                    @forelse($rp->files as $file)
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); gap: 10px;">
                                            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                                <x-icon name="image" :size="18" />
                                                <div style="overflow: hidden;">
                                                    <div style="font-size: 0.875rem; font-weight: 600; color: var(--text); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                        {{ $file->nama_asli }}
                                                    </div>
                                                    <div style="font-size: 0.75rem; color: var(--muted);">
                                                        {{ $file->ukuran_formatted }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                <button type="button" 
                                                        class="btn btn-secondary" 
                                                        style="height: 30px; padding: 0 8px; font-size: 0.75rem;" 
                                                        wire:click="openLightbox({{ $file->id }})">
                                                    <x-icon name="eye" :size="13" />
                                                    <span>View</span>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        style="height: 30px; padding: 0 8px; font-size: 0.75rem;" 
                                                        wire:click="hapusFileRevisiAction({{ $file->id }})"
                                                        wire:confirm="Yakin ingin menghapus file ini?">
                                                    <x-icon name="trash" :size="13" />
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div style="font-size: 0.8125rem; color: var(--danger); padding: 6px;">
                                            Semua file telah dihapus. Unggah minimal 1 file gambar untuk mengumpulkan kembali.
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Tambah File Baru untuk PIC --}}
                                <div style="padding: 12px; background-color: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-sm);">
                                    <label class="form-label" style="margin-bottom: 6px; font-size: 0.8125rem;">
                                        Unggah File Perbaikan Baru (JPG, JPEG, PNG maks 50 MB):
                                    </label>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="file" 
                                               wire:model="revisi_new_files" 
                                               class="form-control" 
                                               accept="image/jpeg,image/png,image/jpg" 
                                               multiple>
                                        <button type="button" 
                                                class="btn btn-primary" 
                                                style="height: 38px; flex-shrink: 0;"
                                                wire:click="tambahFileRevisiAction"
                                                wire:loading.attr="disabled"
                                                wire:target="revisi_new_files, tambahFileRevisiAction">
                                            <x-icon name="upload" :size="14" />
                                            <span>Unggah</span>
                                        </button>
                                    </div>
                                    @error('revisi_new_files') <span class="form-error">{{ $message }}</span> @enderror
                                    @error('revisi_new_files.*') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($rp->tipe->value === 'link')
                            <div>
                                <label class="form-label" for="revisi_link">
                                    Perbaiki Tautan / Link (URL) <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="url" 
                                       id="revisi_link" 
                                       wire:model="revisi_link" 
                                       class="form-control @error('revisi_link') is-invalid @enderror" 
                                       placeholder="https://..." 
                                       maxlength="500">
                                @error('revisi_link') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                        @elseif($rp->tipe->value === 'teks')
                            <div>
                                <label class="form-label" for="revisi_teks">
                                    Perbaiki Teks Narasi Berita <span style="color: var(--danger);">*</span>
                                </label>
                                <textarea id="revisi_teks" 
                                          wire:model="revisi_teks" 
                                          class="form-control @error('revisi_teks') is-invalid @enderror" 
                                          rows="8" 
                                          style="height: auto; padding: 10px 14px;"></textarea>
                                @error('revisi_teks') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endif

                    {{-- Pilihan Tujuan Kumpulkan Kembali --}}
                    @if($user_role === 'staff')
                        {{-- Staff PIC selalu otomatis kembali langsung ke Kabag --}}
                        <div style="padding: 10px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.875rem; color: var(--green-900); display: flex; align-items: center; gap: 8px;">
                            <x-icon name="user" :size="16" />
                            <span>Tugas akan dikumpulkan kembali langsung kepada: <strong>Kepala Bagian (Kabag)</strong></span>
                        </div>
                    @elseif($revisi_staff_penerima_nonaktif)
                        <div style="padding: 12px 14px; background-color: var(--danger-bg); border: 1px solid rgba(179, 56, 44, 0.3); border-radius: var(--radius-sm);">
                            <label class="form-label" for="revisi_tujuan_user_id" style="color: var(--danger); margin-bottom: 6px;">
                                Staf penerima sebelumnya telah nonaktif. Silakan pilih staf aktif penerima tugas:
                            </label>
                            <select id="revisi_tujuan_user_id" 
                                    wire:model="revisi_tujuan_user_id" 
                                    class="form-control @error('revisi_tujuan_user_id') is-invalid @enderror">
                                <option value="">-- Pilih Staf Penerima Pengganti --</option>
                                @foreach($pilihanTujuan as $tujuan)
                                    <option value="{{ $tujuan->id }}">
                                        {{ $tujuan->name }} ({{ ucfirst($tujuan->role->value ?? (string) $tujuan->role) }})
                                        @if($tujuan->role === \App\Enums\Role::Kabag) - Langsung ke Kabag @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('revisi_tujuan_user_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div style="padding: 10px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.875rem; color: var(--green-900); display: flex; align-items: center; gap: 8px;">
                            <x-icon name="user" :size="16" />
                            @if($revisi_tugas->staff_penerima_id && $revisi_tugas->staffPenerima)
                                <span>Tugas akan dikumpulkan kembali ke: <strong>{{ $revisi_tugas->staffPenerima->name }}</strong></span>
                            @else
                                <span>Tugas akan dikumpulkan kembali langsung kepada: <strong>Kepala Bagian (Kabag)</strong></span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Modal Footer Revisi --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRevisiModal">
                        Batal
                    </button>
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="submitKumpulkanKembali"
                            wire:loading.attr="disabled">
                        <x-icon name="upload" :size="15" />
                        <span>Kumpulkan Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL POPUP HASIL PENGUMPULAN (KABAG PADA TAB SELESAI) --}}
    @if($show_hasil_modal && $hasil_tugas)
        <div class="modal-backdrop open" wire:click.self="closeHasilModal">
            <div class="modal-box" style="max-width: 680px;">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Hasil Pengumpulan Tugas</h2>
                        <div style="font-size: 0.8125rem; color: var(--muted); margin-top: 2px;">
                            {{ $hasil_tugas->judul }}
                        </div>
                    </div>
                    <button type="button" class="modal-close" wire:click="closeHasilModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                    {{-- Info Identitas Pengumpulan --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 12px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm);">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600;">PIC Penugas</div>
                            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--text); margin-top: 2px;">
                                {{ $hasil_tugas->pic?->name ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; font-weight: 600;">Waktu Dikumpulkan</div>
                            <div style="font-size: 0.9375rem; font-weight: 500; color: var(--text); margin-top: 2px;">
                                {{ $hasil_tugas->pengumpulan?->dikumpulkan_at ? \Carbon\Carbon::parse($hasil_tugas->pengumpulan->dikumpulkan_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                            </div>
                        </div>
                    </div>

                    @if(! $hasil_mode_revisi)
                        {{-- MODE 1: TAMPILAN HASIL PENGUMPULAN & EDIT TEKS --}}
                        @if($hasil_tugas->pengumpulan)
                            @php
                                $p = $hasil_tugas->pengumpulan;
                            @endphp

                            @if($p->tipe->value === 'file')
                                <div>
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <span style="font-size: 0.875rem; font-weight: 600; color: var(--text);">
                                            File Terlampir ({{ $p->files->count() }} file gambar)
                                        </span>
                                        @if($p->files->count() > 1)
                                            <a href="{{ route('pengumpulan.file.download-zip', ['tugasId' => $hasil_tugas->id]) }}" 
                                               class="btn btn-secondary" 
                                               style="height: 30px; font-size: 0.75rem; padding: 0 10px;">
                                                <x-icon name="download" :size="13" />
                                                <span>Download Semua (ZIP)</span>
                                            </a>
                                        @endif
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($p->files as $file)
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); gap: 10px;">
                                                <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                                    <x-icon name="image" :size="18" />
                                                    <div style="overflow: hidden;">
                                                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--text); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                            {{ $file->nama_asli }}
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: var(--muted);">
                                                            {{ $file->ukuran_formatted }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                    <button type="button" 
                                                            class="btn btn-secondary" 
                                                            style="height: 32px; padding: 0 10px; font-size: 0.8125rem;" 
                                                            wire:click="openLightbox({{ $file->id }})">
                                                        <x-icon name="eye" :size="14" />
                                                        <span>View</span>
                                                    </button>
                                                    <a href="{{ route('pengumpulan.file.download', ['id' => $file->id]) }}" 
                                                       class="btn btn-secondary" 
                                                       style="height: 32px; padding: 0 10px; font-size: 0.8125rem;">
                                                        <x-icon name="download" :size="14" />
                                                        <span>Download</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($p->tipe->value === 'link')
                                <div>
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--text); display: block; margin-bottom: 6px;">
                                        Tautan / Link Google Drive atau Eksternal
                                    </span>
                                    <div style="padding: 14px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                        <span style="font-size: 0.875rem; color: var(--text); word-break: break-all;">
                                            {{ $p->link }}
                                        </span>
                                        <a href="{{ $p->link }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           class="btn btn-primary" 
                                           style="height: 34px; padding: 0 12px; font-size: 0.8125rem; flex-shrink: 0;">
                                            <span>Buka Link</span>
                                            <x-icon name="external-link" :size="14" />
                                        </a>
                                    </div>
                                </div>
                            @elseif($p->tipe->value === 'teks')
                                <div>
                                    <label class="form-label" for="hasil_teks">
                                        Teks Narasi Berita (Kabag dapat mengedit langsung di tempat)
                                    </label>
                                    <textarea id="hasil_teks" 
                                              wire:model="hasil_teks" 
                                              class="form-control @error('hasil_teks') is-invalid @enderror" 
                                              rows="7" 
                                              style="height: auto; padding: 10px 14px; margin-bottom: 8px;">{{ $hasil_teks }}</textarea>
                                    @error('hasil_teks') <span class="form-error">{{ $message }}</span> @enderror
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button type="button" 
                                                class="btn btn-secondary" 
                                                wire:click="simpanTeksKabagAction">
                                            <span>Simpan Perubahan Teks</span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Log Edit oleh Staff (jika ada) --}}
                            @if($p->editLogs->isNotEmpty())
                                @php
                                    $editorNames = $p->editLogs->where('user.role', \App\Enums\Role::Staff)->pluck('user.name')->unique()->filter()->implode(', ');
                                @endphp
                                @if(! empty($editorNames))
                                    <div style="font-size: 0.8125rem; color: var(--muted); font-style: italic; border-top: 1px dashed var(--border); padding-top: 8px;">
                                        Diedit oleh: {{ $editorNames }}
                                    </div>
                                @endif
                            @endif
                        @else
                            <div style="font-size: 0.875rem; color: var(--muted); font-style: italic; text-align: center; padding: 20px;">
                                Data pengumpulan belum tersedia.
                            </div>
                        @endif
                    @else
                        {{-- MODE 2: FORM CATATAN REVISI OLEH KABAG --}}
                        <div style="padding: 14px; background-color: var(--warning-bg); border-radius: var(--radius-sm); border: 1px solid rgba(168, 105, 15, 0.2);">
                            <div style="font-size: 0.9375rem; font-weight: 700; color: var(--warning); margin-bottom: 4px;">
                                Catatan Revisi dari Kepala Bagian
                            </div>
                            <p style="font-size: 0.8125rem; color: var(--text); line-height: 1.4; margin: 0;">
                                Masukkan poin-poin revisi yang jelas. Tugas akan dikembalikan ke
                                @if($hasil_tugas->staff_penerima_id && $hasil_tugas->staffPenerima && $hasil_tugas->staffPenerima->is_active)
                                    staf penerima (<strong>{{ $hasil_tugas->staffPenerima->name }}</strong>).
                                @elseif($hasil_tugas->staff_penerima_id)
                                    staf pengganti yang Anda pilih di bawah.
                                @else
                                    penanggung jawab tugas (<strong>{{ $hasil_tugas->pic?->name ?? 'PIC' }}</strong>).
                                @endif
                            </p>
                        </div>

                        {{-- Jika staf penerima sebelumnya dinonaktifkan, Kabag memilih staf pengganti aktif --}}
                        @if($hasil_staff_penerima_nonaktif)
                            <div style="padding: 12px 14px; background-color: var(--danger-bg); border: 1px solid rgba(179, 56, 44, 0.3); border-radius: var(--radius-sm);">
                                <label class="form-label" for="hasil_staff_pengganti_id" style="color: var(--danger); margin-bottom: 6px;">
                                    Staf penerima tugas sebelumnya telah nonaktif. Silakan pilih staf aktif pengganti untuk menerima revisi ini:
                                </label>
                                <select id="hasil_staff_pengganti_id" 
                                        wire:model="hasil_staff_pengganti_id" 
                                        class="form-control @error('hasil_staff_pengganti_id') is-invalid @enderror">
                                    <option value="">-- Pilih Staf Pengganti --</option>
                                    @foreach($availableStaff as $staf)
                                        <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                                    @endforeach
                                </select>
                                @error('hasil_staff_pengganti_id') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <label class="form-label" style="margin-bottom: 0;">Poin-Poin Catatan Revisi <span style="color: var(--danger);">*</span></label>
                            @foreach($hasil_poin_revisi as $idx => $hp)
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 0.875rem; font-weight: 600; color: var(--muted); width: 24px; text-align: center;">
                                        {{ $idx + 1 }}.
                                    </span>
                                    <input type="text" 
                                           wire:model="hasil_poin_revisi.{{ $idx }}" 
                                           class="form-control @error('hasil_poin_revisi.'.$idx) is-invalid @enderror" 
                                           placeholder="Contoh: Perjelas latar belakang isu dan lampirkan izin kegiatan...">
                                    @if(count($hasil_poin_revisi) > 1)
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                style="height: 38px; width: 38px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" 
                                                wire:click="hapusHasilPoinRevisi({{ $idx }})">
                                            <x-icon name="trash" :size="15" />
                                        </button>
                                    @endif
                                </div>
                                @error('hasil_poin_revisi.'.$idx) <span class="form-error" style="margin-left: 32px;">{{ $message }}</span> @enderror
                            @endforeach

                            <div>
                                <button type="button" 
                                        class="btn btn-secondary" 
                                        style="font-size: 0.8125rem; height: 32px;" 
                                        wire:click="tambahHasilPoinRevisi">
                                    <x-icon name="plus" :size="14" />
                                    <span>Tambah Poin Revisi</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    @if(! $hasil_mode_revisi)
                        <button type="button" class="btn btn-secondary" wire:click="closeHasilModal">
                            Tutup
                        </button>
                        @if($hasil_tugas->tahap->value === 'menunggu_kabag')
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" 
                                        class="btn btn-secondary" 
                                        style="color: var(--warning); border-color: rgba(168, 105, 15, 0.4);" 
                                        wire:click="switchHasilModeRevisi(true)">
                                    <x-icon name="edit" :size="15" />
                                    <span>Minta Revisi</span>
                                </button>
                                <button type="button" 
                                        class="btn btn-primary" 
                                        wire:click="openTerimaTugasConfirm">
                                    <x-icon name="check" :size="15" />
                                    <span>Terima Tugas</span>
                                </button>
                            </div>
                        @endif
                    @else
                        <button type="button" class="btn btn-secondary" wire:click="switchHasilModeRevisi(false)">
                            Kembali
                        </button>
                        <button type="button" 
                                class="btn btn-primary" 
                                style="background-color: var(--warning); border-color: var(--warning);"
                                wire:click="kirimRevisiKabagAction"
                                wire:loading.attr="disabled">
                            <x-icon name="send" :size="15" />
                            <span>Kirim Revisi</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- LIGHTBOX PENAMPIL GAMBAR FULLSCREEN (VIEW) --}}
    @if($show_lightbox && ! empty($lightbox_images))
        @php
            $currentImg = $lightbox_images[$lightbox_current_index] ?? null;
        @endphp

        <div class="modal-backdrop open" 
             style="background-color: rgba(10, 15, 8, 0.92); z-index: 10000; align-items: center; justify-content: center;"
             x-data
             @keydown.escape.window="$wire.closeLightbox()"
             @keydown.arrow-left.window="$wire.prevLightboxImage()"
             @keydown.arrow-right.window="$wire.nextLightboxImage()">
            
            {{-- Tombol Tutup --}}
            <button type="button" 
                    wire:click="closeLightbox" 
                    style="position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: #FFF; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10002; transition: background 200ms;"
                    title="Tutup (Esc)">
                <x-icon name="x" :size="24" />
            </button>

            {{-- Indikator Gambar & Nama --}}
            <div style="position: absolute; top: 24px; left: 24px; color: #FFFFFF; font-size: 0.875rem; z-index: 10002; background: rgba(0,0,0,0.5); padding: 6px 14px; border-radius: 20px;">
                <span>{{ $lightbox_current_index + 1 }} / {{ count($lightbox_images) }}</span>
                <span style="margin: 0 8px;">&bull;</span>
                <span>{{ $currentImg['nama_asli'] ?? '' }}</span>
            </div>

            {{-- Tombol Navigasi Kiri --}}
            @if(count($lightbox_images) > 1)
                <button type="button" 
                        wire:click="prevLightboxImage" 
                        style="position: absolute; left: 24px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #FFF; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10002; transition: background 200ms;"
                        title="Sebelumnya (Panah Kiri)">
                    <x-icon name="chevron-left" :size="28" />
                </button>
            @endif

            {{-- Kontainer Gambar --}}
            <div style="max-width: 90vw; max-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative;" wire:click.self="closeLightbox">
                @if($currentImg)
                    <img src="{{ $currentImg['url'] }}" 
                         alt="{{ $currentImg['nama_asli'] }}" 
                         style="max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                @endif
            </div>

            {{-- Tombol Navigasi Kanan --}}
            @if(count($lightbox_images) > 1)
                <button type="button" 
                        wire:click="nextLightboxImage" 
                        style="position: absolute; right: 24px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #FFF; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10002; transition: background 200ms;"
                        title="Berikutnya (Panah Kanan)">
                    <x-icon name="chevron-right" :size="28" />
                </button>
            @endif
        </div>
    @endif

    {{-- MODAL POPUP FORM TAMBAH / EDIT TUGAS (HANYA KABAG) --}}
    @if($show_form_modal)
        <div class="modal-backdrop open" wire:click.self="closeFormModal">
            <div class="modal-box" style="max-width: 600px;">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $form_tugas_id ? 'Edit Tugas' : 'Tambah Tugas Baru' }}</h2>
                    <button type="button" class="modal-close" wire:click="closeFormModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <form wire:submit.prevent="simpanTugas">
                    <div class="modal-body">
                        <x-tugas.form-fields 
                            :pics="$availablePics" 
                            :kegiatan-list="$availableKegiatan" 
                            prefix="form_" 
                            :is-edit="$form_tugas_id !== null" 
                            :can-change-pic="$form_can_change_pic" 
                        />
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeFormModal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $form_tugas_id ? 'Perbarui Tugas' : 'Simpan Tugas' }}</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL KONFIRMASI HAPUS TUGAS (HANYA KABAG) --}}
    @if($show_delete_modal)
        <div class="modal-backdrop open" wire:click.self="closeDeleteModal">
            <div class="modal-box" style="max-width: 440px;">
                <div class="modal-header">
                    <h2 class="modal-title" style="color: var(--danger);">Hapus Tugas</h2>
                    <button type="button" class="modal-close" wire:click="closeDeleteModal" aria-label="Tutup">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>

                <div class="modal-body">
                    <p style="font-size: 0.9375rem; color: var(--text); line-height: 1.5; margin-bottom: 12px;">
                        Apakah Anda yakin ingin menghapus tugas berikut?
                    </p>
                    <div style="padding: 12px; background-color: var(--danger-bg); border-radius: var(--radius-sm); font-weight: 600; color: var(--danger);">
                        {{ $delete_tugas_judul }}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="deleteTugas" wire:loading.attr="disabled">
                        <span wire:loading.remove>Ya, Hapus</span>
                        <span wire:loading>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Terima Tugas --}}
    @if($show_terima_confirm_modal)
        <div class="modal-backdrop open" wire:click.self="closeTerimaTugasConfirm">
            <div class="modal-box" style="max-width: 400px;">
                <div class="modal-header">
                    <h3 class="modal-title">Terima Tugas</h3>
                    <button type="button" class="modal-close" wire:click="closeTerimaTugasConfirm">
                        <x-icon name="x" :size="20" />
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 0.9375rem; color: var(--text); margin: 0;">
                        Terima hasil pengumpulan ini dan pindahkan tugas ke Riwayat?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeTerimaTugasConfirm">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="terimaTugasAction" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="terimaTugasAction">Ya, Terima</span>
                        <span wire:loading wire:target="terimaTugasAction">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
