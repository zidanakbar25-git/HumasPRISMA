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

    {{-- Kegiatan Terkait --}}
    @if(! $hideKegiatan)
        <div class="form-group">
            <label class="form-label" for="{{ str_replace('.', '_', $prefix) }}kegiatan_id">Kegiatan Terkait (Opsional)</label>
            <select id="{{ str_replace('.', '_', $prefix) }}kegiatan_id" 
                    wire:model="{{ $prefix }}kegiatan_id" 
                    class="form-control @error($prefix . 'kegiatan_id') is-invalid @enderror">
                <option value="">Tidak terkait kegiatan</option>
                @foreach($kegiatanList as $keg)
                    <option value="{{ $keg->id }}">{{ $keg->judul }} ({{ \Carbon\Carbon::parse($keg->tanggal)->translatedFormat('d M Y') }})</option>
                @endforeach
            </select>
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
