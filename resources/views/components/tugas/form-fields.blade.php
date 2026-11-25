@props([
'pics' => collect(),
'kegiatanList' => collect(),
'prefix' => 'form_',
'hideKegiatan' => false,
'kegiatanJudul' => null,
'isEdit' => false,
'canChangePic' => true,
])

<div class="tugas-form-fields">
    {{-- Judul Tugas --}}
    <div class="form-group">
        <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}judul">
            Judul Tugas <span style="color: var(--danger);">*</span>
        </label>
        <input type="text"
            id="{{ str_replace('.', '_', $prefix) }}judul"
            wire:model="{{ $prefix }}judul"
            class="form-control @error($prefix . 'judul') is-invalid @enderror"
            placeholder="Contoh: Liputan Foto & Berita, Pembuatan Rilis Pers, dll."
            maxlength="150"
            required>
        @error($prefix . 'judul')
        <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        {{-- Tenggat Waktu (Deadline) --}}
        <div class="form-group">
            <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}deadline">
                Tenggat Waktu (Deadline) <span style="color: var(--danger);">*</span>
            </label>
            <input type="datetime-local"
                id="{{ str_replace('.', '_', $prefix) }}deadline"
                wire:model="{{ $prefix }}deadline"
                class="form-control @error($prefix . 'deadline') is-invalid @enderror"
                required>
            @error($prefix . 'deadline')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Prioritas --}}
        <div class="form-group">
            <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}prioritas">
                Prioritas <span style="color: var(--danger);">*</span>
            </label>
            <select id="{{ str_replace('.', '_', $prefix) }}prioritas"
                wire:model="{{ $prefix }}prioritas"
                class="form-control @error($prefix . 'prioritas') is-invalid @enderror"
                required>
                <option value="rendah">Rendah</option>
                <option value="sedang">Sedang</option>
                <option value="tinggi">Tinggi</option>
            </select>
            @error($prefix . 'prioritas')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- PIC Tugas (Satu orang, staff/intern aktif, tanpa kabag) --}}
    <div class="form-group">
        <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}pic_id">
            PIC Tugas <span style="color: var(--danger);">*</span>
            @if($isEdit && ! $canChangePic)
            <span style="font-size: 0.75rem; font-weight: normal; color: var(--muted); margin-left: 6px;">
                (Tidak dapat diubah karena tugas sudah diproses)
            </span>
            @endif
        </label>
        <select id="{{ str_replace('.', '_', $prefix) }}pic_id"
            wire:model="{{ $prefix }}pic_id"
            class="form-control @error($prefix . 'pic_id') is-invalid @enderror"
            @if($isEdit && ! $canChangePic) disabled @endif
            required>
            <option value="">-- Pilih PIC (Staff / Intern) --</option>
            @foreach($pics as $p)
            <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->role->value ?? (string) $p->role) }})</option>
            @endforeach
        </select>
        @error($prefix . 'pic_id')
        <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Kegiatan Terkait (Searchable Select dengan Alpine.js) --}}
    @if(! $hideKegiatan)
    <div class="form-group"
        x-data="{ 
                 open: false, 
                 search: '',
                 selectedId: $wire.entangle('{{ $prefix }}kegiatan_id'),
                 kegiatanList: {{ Js::from($kegiatanList->map(fn($k) => ['id' => $k->id, 'judul' => $k->judul, 'tanggal' => \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y')])->values()) }},
                 get selectedJudul() {
                     if (!this.selectedId) return null;
                     const found = this.kegiatanList.find(k => k.id == this.selectedId);
                     return found ? found.judul : null;
                 },
                 get filteredKegiatan() {
                     if (!this.search || this.search.trim() === '') return this.kegiatanList;
                     const s = this.search.toLowerCase();
                     return this.kegiatanList.filter(k => k.judul && k.judul.toLowerCase().includes(s));
                 },
                 selectKegiatan(id) {
                     this.selectedId = id;
                     $wire.set('{{ $prefix }}kegiatan_id', id);
                     this.open = false;
                     this.search = '';
                 }
             }">
        <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}kegiatan_id">Kegiatan Terkait (Opsional)</label>

        <div style="position: relative;">
            {{-- Kotak Pilihan Utama (Trigger) --}}
            <div @click="open = !open"
                class="form-control @error($prefix . 'kegiatan_id') is-invalid @enderror"
                style="display: flex; justify-content: space-between; align-items: center; background-color: var(--surface); cursor: pointer; user-select: none;">
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <span x-show="selectedJudul" x-text="selectedJudul" style="font-weight: 500; color: var(--text);"></span>
                    <span x-show="!selectedJudul" style="color: var(--muted);">-- Tidak terkait kegiatan --</span>
                </span>
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; min-width: 16px; flex-shrink: 0; color: var(--muted); transition: transform 0.2s;" :style="open ? 'transform: rotate(180deg);' : 'transform: rotate(0deg);'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px; display: block;">
                        <path d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </div>

            {{-- Panel Dropdown Pencarian --}}
            <div x-show="open"
                x-cloak
                @click.away="open = false"
                style="display: none; position: absolute; z-index: 50; width: 100%; margin-top: 4px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-height: 240px; overflow-y: auto; padding: 8px;">
                {{-- Kotak Ketik Pencarian --}}
                <input type="text"
                    x-model="search"
                    placeholder="Ketik judul kegiatan..."
                    class="form-control"
                    style="width: 100%; height: 36px; font-size: 0.8125rem; margin-bottom: 8px;"
                    autocomplete="off">

                <div style="display: flex; flex-direction: column; gap: 2px;">
                    {{-- Opsi Kosong / Tanpa Kegiatan --}}
                    <div @click="selectKegiatan(null)"
                        style="padding: 8px 10px; border-radius: 4px; cursor: pointer; font-size: 0.875rem; display: flex; justify-content: space-between; align-items: center;"
                        :style="!selectedId ? 'background-color: var(--bg); font-weight: 600; color: var(--text);' : 'color: var(--muted);'"
                        onmouseover="this.style.backgroundColor='var(--bg)'" 
                        onmouseout="if (selectedId) this.style.backgroundColor='transparent'">
                        <span>-- Tidak terkait kegiatan --</span>
                        <template x-if="!selectedId">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--green-700);">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </template>
                    </div>

                    {{-- Daftar Hasil Filter --}}
                    <template x-for="k in filteredKegiatan" :key="k.id">
                        <div @click="selectKegiatan(k.id)"
                            style="padding: 8px 10px; border-radius: 4px; cursor: pointer; font-size: 0.875rem; display: flex; justify-content: space-between; align-items: center;"
                            :style="selectedId == k.id ? 'background-color: var(--green-50); color: var(--green-900); font-weight: 600;' : ''"
                            onmouseover="this.style.backgroundColor='var(--green-50)'; this.style.color='var(--green-900)'"
                            onmouseout="if (selectedId != k.id) { this.style.backgroundColor='transparent'; this.style.color='inherit'; }">
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span x-text="k.judul" style="font-weight: 500;"></span>
                                <span x-text="k.tanggal" style="font-size: 0.75rem; color: var(--muted);"></span>
                            </div>
                            <template x-if="selectedId == k.id">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--green-700); flex-shrink: 0;">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </template>
                        </div>
                    </template>

                    {{-- Pesan jika tidak ditemukan --}}
                    <div x-show="filteredKegiatan.length === 0" style="padding: 12px; text-align: center; font-size: 0.8125rem; color: var(--muted);">
                        Kegiatan tidak ditemukan.
                    </div>
                </div>
            </div>
        </div>

        @error($prefix . 'kegiatan_id')
        <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
    @elseif(! empty($kegiatanJudul))
    <div class="form-group">
        <label class="form-label">Kegiatan Terkait</label>
        <div style="padding: 10px 14px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9375rem; font-weight: 500; color: var(--green-800); display: flex; align-items: center; gap: 8px;">
            <span class="badge badge-kegiatan">Kegiatan</span>
            <span>{{ $kegiatanJudul }}</span>
        </div>
    </div>
    @endif
</div>