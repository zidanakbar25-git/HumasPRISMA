<div>
    <div class="page-header">
        <h1 class="page-title">Akun Saya</h1>
    </div>

    <div class="card" style="max-width: 640px;">
        <form wire:submit.prevent="simpan">
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="name" 
                    wire:model="name" 
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" 
                    placeholder="Masukkan nama lengkap"
                >
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    wire:model="username" 
                    class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" 
                    placeholder="Masukkan username"
                >
                @error('username')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin: 28px 0 16px; padding-top: 18px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text); margin-bottom: 4px;">Ganti Password</h3>
                <p style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 16px;">
                    Biarkan kolom di bawah ini kosong jika Anda tidak ingin mengganti password akun Anda.
                </p>

                <div class="form-group">
                    <label for="current_password" class="form-label">Password Lama</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        wire:model="current_password" 
                        class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" 
                        placeholder="Masukkan password saat ini"
                    >
                    @error('current_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Password Baru (Minimal 6 karakter)</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        wire:model="new_password" 
                        class="form-control {{ $errors->has('new_password') ? 'is-invalid' : '' }}" 
                        placeholder="Masukkan password baru"
                    >
                    @error('new_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">Ulangi Password Baru</label>
                    <input 
                        type="password" 
                        id="new_password_confirmation" 
                        wire:model="new_password_confirmation" 
                        class="form-control" 
                        placeholder="Ulangi password baru"
                    >
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
