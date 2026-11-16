<div class="user-page">
    <div class="header-page" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">Manajemen User</h1>
    </div>

        {{-- 1. AKUN KABAG  --}}
    <div
        class="card akun-card"
        x-data="{ showPass: @js($errors->has('kabag_current_password') || $errors->has('kabag_new_password')), showText: false }">
        <form wire:submit.prevent="simpanAkunKabag">
            <div class="akun-head">
                <div class="akun-id">
                    <span class="akun-avatar" aria-hidden="true"><x-icon name="user" :size="18" /></span>
                    <div>
                        <h2 class="akun-title">Akun Kepala Bagian</h2>
                        <p class="akun-sub">Nama, username, dan password akun utama.</p>
                    </div>
                </div>

                <button
                    type="button"
                    class="akun-link"
                    x-on:click="
                        showPass = !showPass;
                        if (!showPass) { $wire.kabag_current_password = ''; $wire.kabag_new_password = ''; showText = false; }
                    ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span x-text="showPass ? 'Batal ganti password' : 'Ganti password'">Ganti password</span>
                </button>
            </div>

            <div class="akun-grid">
                <div class="form-group">
                    <label for="kabag_name" class="form-label">Nama Lengkap</label>
                    <input
                        type="text"
                        id="kabag_name"
                        wire:model="kabag_name"
                        class="form-control {{ $errors->has('kabag_name') ? 'is-invalid' : '' }}"
                        placeholder="Nama Kepala Bagian">
                    @error('kabag_name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kabag_username" class="form-label">Username</label>
                    <input
                        type="text"
                        id="kabag_username"
                        wire:model="kabag_username"
                        class="form-control {{ $errors->has('kabag_username') ? 'is-invalid' : '' }}"
                        placeholder="Username login">
                    @error('kabag_username')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary akun-save" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>

            {{-- Password (muncul hanya saat "Ganti password" diklik) --}}
            <div class="akun-pass" x-show="showPass" x-cloak x-transition.opacity.duration.200ms>
                <div class="form-group">
                    <label for="kabag_current_password" class="form-label">Password Lama</label>
                    <input
                        type="password"
                        id="kabag_current_password"
                        wire:model="kabag_current_password"
                        class="form-control {{ $errors->has('kabag_current_password') ? 'is-invalid' : '' }}"
                        placeholder="Wajib diisi untuk mengganti password"
                        autocomplete="current-password">
                    @error('kabag_current_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kabag_new_password" class="form-label">Password Baru</label>
                    <div class="akun-pw-wrap">
                        <input
                            :type="showText ? 'text' : 'password'"
                            id="kabag_new_password"
                            wire:model="kabag_new_password"
                            class="form-control {{ $errors->has('kabag_new_password') ? 'is-invalid' : '' }}"
                            placeholder="Minimal 6 karakter"
                            autocomplete="new-password">
                        <button
                            type="button"
                            class="akun-eye"
                            x-on:click="showText = !showText"
                            :aria-label="showText ? 'Sembunyikan password' : 'Lihat password'">
                            <svg x-show="!showText" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showText" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                    @error('kabag_new_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </form>
    </div>

        <div class="user-toolbar">
        <div class="user-toolbar-info">
            <h2 class="user-toolbar-title">Daftar User</h2>
            <span class="user-toolbar-count">{{ $staffUsers->count() + $internUsers->count() }} akun</span>
        </div>
        <button type="button" class="btn btn-primary" wire:click="openCreateModal">
            <x-icon name="plus" :size="16" />
            <span>Tambah User</span>
        </button>
    </div>
    
    {{-- 2. TABEL DAFTAR PENGGUNA --}}
    <div class="table-container">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">Nama</th>
                        <th style="width: 22%;">Username</th>
                        <th style="width: 14%;">Role</th>
                        <th style="width: 12%; text-align: center;">Status</th>
                        <th style="width: 12%; text-align: center;">Trusted</th>
                        <th style="width: 12%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- KELOMPOK 1: STAFF --}}
                    @forelse($staffUsers as $staff)
                        <tr wire:key="user-staff-{{ $staff->id }}">
                            <td style="font-weight: 600; color: var(--text);">
                                {{ $staff->name }}
                            </td>
                            <td style="color: var(--muted); font-family: monospace;">
                                &#64;{{ $staff->username }}
                            </td>
                            <td>
                                <span class="badge badge-role-staff">Staff</span>
                            </td>
                            <td style="text-align: center;">
                                <label class="toggle-switch" title="{{ $staff->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}">
                                    <input 
                                        type="checkbox" 
                                        {{ $staff->is_active ? 'checked' : '' }}
                                        wire:click="toggleStatus({{ $staff->id }})"
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td style="text-align: center;">
                                {{-- Checkbox Trusted Staff: selalu tercentang, abu buram, disabled --}}
                                <label class="custom-checkbox" title="Staff selalu otomatis berstatus trusted">
                                    <input type="checkbox" checked disabled>
                                </label>
                            </td>
                            <td style="text-align: right;">
                                <button 
                                    type="button" 
                                    class="btn btn-secondary" 
                                    style="height: 32px; padding: 0 12px; font-size: 0.8125rem;"
                                    wire:click="openEditModal({{ $staff->id }})"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--muted); padding: 24px;">
                                Belum ada staf terdaftar.
                            </td>
                        </tr>
                    @endforelse

                    {{-- GARIS PEMBATAS PEMISAH KELOMPOK DENGAN LABEL KECIL --}}
                    <tr class="table-divider-row">
                        <td colspan="6">
                            <span>Intern / Mahasiswa Magang</span>
                        </td>
                    </tr>

                    {{-- KELOMPOK 2: INTERN --}}
                    @forelse($internUsers as $intern)
                        <tr wire:key="user-intern-{{ $intern->id }}">
                            <td style="font-weight: 600; color: var(--text);">
                                {{ $intern->name }}
                            </td>
                            <td style="color: var(--muted); font-family: monospace;">
                                &#64;{{ $intern->username }}
                            </td>
                            <td>
                                <span class="badge badge-role-intern">Intern</span>
                            </td>
                            <td style="text-align: center;">
                                <label class="toggle-switch" title="{{ $intern->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}">
                                    <input 
                                        type="checkbox" 
                                        {{ $intern->is_active ? 'checked' : '' }}
                                        wire:click="toggleStatus({{ $intern->id }})"
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td style="text-align: center;">
                                {{-- Checkbox Trusted Intern: solid, bisa dicentang/dilepas otomatis --}}
                                <label class="custom-checkbox" title="{{ $intern->is_trusted ? 'Cabut akses trusted' : 'Beri akses trusted (langsung ke Kabag)' }}">
                                    <input 
                                        type="checkbox" 
                                        {{ $intern->is_trusted ? 'checked' : '' }}
                                        wire:change="toggleTrusted({{ $intern->id }}, $event.target.checked)"
                                    >
                                </label>
                            </td>
                            <td style="text-align: right;">
                                <button 
                                    type="button" 
                                    class="btn btn-secondary" 
                                    style="height: 32px; padding: 0 12px; font-size: 0.8125rem;"
                                    wire:click="openEditModal({{ $intern->id }})"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--muted); padding: 24px;">
                                Belum ada intern / anak magang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. POPUP MODAL TAMBAH USER --}}
    <div 
        class="modal-backdrop {{ $show_create_modal ? 'open' : '' }}" 
        x-data 
        @keydown.escape.window="$wire.closeCreateModal()"
        @if(!$show_create_modal) style="display: none;" @endif
    >
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Tambah User Baru</h3>
                <button type="button" class="modal-close" wire:click="closeCreateModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <form wire:submit.prevent="simpanUserBaru">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create_name" class="form-label">Nama Lengkap</label>
                        <input 
                            type="text" 
                            id="create_name" 
                            wire:model="create_name" 
                            class="form-control {{ $errors->has('create_name') ? 'is-invalid' : '' }}" 
                            placeholder="Contoh: Budi Santoso"
                        >
                        @error('create_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="create_username" 
                            wire:model="create_username" 
                            class="form-control {{ $errors->has('create_username') ? 'is-invalid' : '' }}" 
                            placeholder="Contoh: budi_s"
                        >
                        @error('create_username')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_password" class="form-label">Password (Minimal 6 karakter)</label>
                        <input 
                            type="password" 
                            id="create_password" 
                            wire:model="create_password" 
                            class="form-control {{ $errors->has('create_password') ? 'is-invalid' : '' }}" 
                            placeholder="Masukkan password akun"
                        >
                        @error('create_password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Role Pengguna</label>
                        <div style="display: flex; gap: 20px; margin-top: 6px;">
                            <label class="custom-checkbox" style="gap: 6px;">
                                <input type="radio" value="staff" wire:model="create_role" name="create_role">
                                <span>Staff</span>
                            </label>
                            <label class="custom-checkbox" style="gap: 6px;">
                                <input type="radio" value="intern" wire:model="create_role" name="create_role">
                                <span>Intern / Magang</span>
                            </label>
                        </div>
                        @error('create_role')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan User</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. POPUP MODAL EDIT USER --}}
    <div 
        class="modal-backdrop {{ $show_edit_modal ? 'open' : '' }}" 
        x-data 
        @keydown.escape.window="$wire.closeEditModal()"
        @if(!$show_edit_modal) style="display: none;" @endif
    >
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button type="button" class="modal-close" wire:click="closeEditModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <form wire:submit.prevent="simpanEditUser">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Nama Lengkap</label>
                        <input 
                            type="text" 
                            id="edit_name" 
                            wire:model="edit_name" 
                            class="form-control {{ $errors->has('edit_name') ? 'is-invalid' : '' }}" 
                            placeholder="Nama Lengkap"
                        >
                        @error('edit_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="edit_username" 
                            wire:model="edit_username" 
                            class="form-control {{ $errors->has('edit_username') ? 'is-invalid' : '' }}" 
                            placeholder="Username"
                        >
                        @error('edit_username')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_password" class="form-label">Password Baru (Kosongkan bila tidak diubah)</label>
                        <input 
                            type="password" 
                            id="edit_password" 
                            wire:model="edit_password" 
                            class="form-control {{ $errors->has('edit_password') ? 'is-invalid' : '' }}" 
                            placeholder="Biarkan kosong jika tetap"
                        >
                        @error('edit_password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Role Pengguna</label>
                        <div style="display: flex; gap: 20px; margin-top: 6px;">
                            <label class="custom-checkbox" style="gap: 6px;">
                                <input type="radio" value="staff" wire:model="edit_role" name="edit_role">
                                <span>Staff</span>
                            </label>
                            <label class="custom-checkbox" style="gap: 6px;">
                                <input type="radio" value="intern" wire:model="edit_role" name="edit_role">
                                <span>Intern / Magang</span>
                            </label>
                        </div>
                        @error('edit_role')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan Perubahan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
