<div @if(!$show_delete_modal) wire:poll.8s @endif>
    {{-- HEADER HALAMAN & KARTU PENJELASAN --}}
    <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
        <h1 class="page-title" style="margin-bottom: 6px;">Backup Database</h1>
        <p style="font-size: 0.875rem; color: var(--muted); margin: 0;">
            Pencadangan seluruh struktur dan data database sistem internal Biro Humas.
        </p>
    </div>

    {{-- KARTU AKSI & PENJELASAN --}}
    <div class="card" style="padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
            <div style="max-width: 720px;">
                <h3 style="font-size: 1.0625rem; font-weight: 700; color: var(--green-900); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                    <span>Salinan Cadangan Mandiri (SQL Dump)</span>
                </h3>
                <p style="font-size: 0.875rem; color: var(--text); line-height: 1.6; margin: 0;">
                    Klik tombol di samping untuk menghasilkan dump <strong>struktur tabel dan seluruh data</strong> aplikasi saat ini ke dalam berkas <code>.sql</code>. File disimpan di disk privat berkeamanan tinggi (tidak dapat diakses publik) dan hanya dapat diunduh oleh akun Kepala Bagian.
                </p>
            </div>

            <div>
                <button 
                    type="button" 
                    wire:click="createBackup" 
                    class="btn btn-primary" 
                    wire:loading.attr="disabled"
                    style="height: 44px; padding: 0 20px; font-weight: 600; font-size: 0.9375rem; display: flex; align-items: center; gap: 8px; white-space: nowrap;"
                >
                    <span wire:loading.remove style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        <span>Backup Database Sekarang</span>
                    </span>
                    <span wire:loading style="display: flex; align-items: center; gap: 8px;">
                        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                        <span>Membuat Salinan Database...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- TABEL RIWAYAT BACKUP --}}
    <div class="card" style="padding: 20px;">
        <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text); margin: 0;">
                Riwayat Berkas Cadangan
            </h2>
            <span style="font-size: 0.8125rem; color: var(--muted);">
                Total: <strong>{{ $backups->count() }}</strong> berkas
            </span>
        </div>

        @if($backups->isEmpty())
            <div style="text-align: center; padding: 48px 20px; color: var(--muted);">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; color: var(--border);"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>
                <p style="font-size: 0.9375rem; margin: 0;">Belum ada riwayat backup database.</p>
                <p style="font-size: 0.8125rem; margin-top: 4px;">Klik tombol "Backup Database Sekarang" di atas untuk membuat cadangan data pertama.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                    <thead>
                        <tr style="background-color: var(--green-50); border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900); width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900);">Tanggal & Waktu</th>
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900);">Nama File</th>
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900);">Ukuran</th>
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900); text-align: center; width: 120px;">Download</th>
                            <th style="padding: 12px 14px; font-weight: 700; color: var(--green-900); text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $index => $b)
                            <tr style="border-bottom: 1px solid var(--border); transition: background-color var(--t-fast) var(--ease);" onmouseover="this.style.backgroundColor='var(--green-50)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding: 14px; text-align: center; color: var(--muted); font-weight: 600;">
                                    {{ $index + 1 }}
                                </td>
                                <td style="padding: 14px; color: var(--text); font-weight: 600;">
                                    {{ $b->tanggal_formatted }}
                                </td>
                                <td style="padding: 14px; color: var(--text);">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green-700)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        <code style="font-family: monospace; font-size: 0.8125rem; background-color: var(--green-50); padding: 2px 6px; border-radius: 4px; color: var(--green-900);">
                                            {{ $b->nama_file }}
                                        </code>
                                    </div>
                                </td>
                                <td style="padding: 14px; color: var(--muted); font-weight: 500;">
                                    {{ $b->ukuran_formatted }}
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <a 
                                        href="{{ route('kabag.backup-db.download', $b->id) }}" 
                                        class="btn btn-secondary" 
                                        style="height: 32px; padding: 0 12px; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;"
                                        title="Unduh Berkas SQL"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                        <span>Unduh</span>
                                    </a>
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <button 
                                        type="button" 
                                        wire:click="confirmDelete({{ $b->id }})" 
                                        class="btn btn-danger" 
                                        style="height: 32px; padding: 0 12px; font-size: 0.8125rem;"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div 
        class="modal-backdrop {{ $show_delete_modal ? 'open' : '' }}" 
        x-data 
        @keydown.escape.window="$wire.closeDeleteModal()"
        @if(!$show_delete_modal) style="display: none;" @endif
    >
        <div class="modal-box" style="max-width: 460px;">
            <div class="modal-header">
                <h3 class="modal-title" style="color: var(--danger);">Hapus File Backup</h3>
                <button type="button" class="modal-close" wire:click="closeDeleteModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <div class="modal-body">
                <p style="font-size: 0.9375rem; color: var(--text); margin-bottom: 8px;">
                    Apakah Anda yakin ingin menghapus file backup <strong>"{{ $delete_nama_file }}"</strong>?
                </p>
                <div style="padding: 10px 12px; background-color: var(--danger-bg); border-radius: var(--radius-sm); font-size: 0.8125rem; color: var(--danger);">
                    Tindakan ini akan menghapus catatan dan berkas fisik <code>.sql</code> dari server secara permanen.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                    Batal
                </button>
                <button 
                    type="button" 
                    class="btn btn-danger" 
                    wire:click="deleteBackup"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Ya, Hapus File</span>
                    <span wire:loading>Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
</div>
