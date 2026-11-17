<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login — Sistem Alur Kerja Biro Humas</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Murni Sesuai UI_GUIDE -->
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    <style>
        .login-layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 992px) {
            .login-layout {
                grid-template-columns: 1.2fr 0.8fr;
            }
        }

        /* Sisi Kiri (Background Kampus dengan Overlay Senada Warna Tombol/Tema) */
        .login-brand-side {
            position: relative;
            background-image: url('{{ asset("images/Kampus-Unjani-3.jpg") }}');
            background-size: cover;
            background-position: center;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
        }

        /* Overlay Gradasi disamakan dengan nuansa hijau tema tombol (menggunakan variabel --green-800 / --green-900) */
        .login-brand-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(35, 73, 54, 0.88) 0%, rgba(25, 54, 39, 0.92) 100%);
            z-index: 1;
        }

        .login-brand-side > * {
            position: relative;
            z-index: 2;
        }

        @media (max-width: 991.98px) {
            .login-brand-side {
                display: none;
            }
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-box {
            width: 44px;
            height: 44px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
        }

        .brand-app-name {
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #ffffff;
        }

        .brand-content {
            max-width: 500px;
            margin: auto 0;
        }

        .brand-heading {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
            color: #ffffff;
        }

        .brand-description {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.5;
        }

        /* Sisi Kanan (Form Masuk) */
        .login-form-side {
            background-color: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
        }

        .login-form-container {
            width: 100%;
            max-width: 380px;
        }

        .login-form-header {
            margin-bottom: 28px;
        }

        .login-form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }

        .login-form-subtitle {
            font-size: 0.875rem;
            color: var(--muted);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--text);
        }

        .login-checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            cursor: pointer;
        }

        .login-checkbox-group input {
            accent-color: var(--green-700);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .login-checkbox-label {
            font-size: 0.875rem;
            color: var(--text);
            cursor: pointer;
            user-select: none;
        }

        .login-info-box {
            margin-top: 24px;
            padding: 12px 16px;
            background-color: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.775rem;
            color: var(--muted);
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="login-layout">
        <!-- Sisi Kiri: Gambar Kampus dengan Nuansa Hijau yang Disamakan -->
        <div class="login-brand-side">
            <div class="brand-top">
                <div class="brand-logo-box">
                    <x-icon name="briefcase" :size="24" />
                </div>
                <span class="brand-app-name">PRISMA Humas</span>
            </div>
            
            <div class="brand-content">
                <h1 class="brand-heading">Sistem Manajemen Tugas &amp; Jadwal Kegiatan Kantor</h1>
                <p class="brand-description">Satu portal terpadu untuk pengelolaan penugasan staf, pemantauan progres pekerjaan, dan koordinasi biro secara transparan.</p>
            </div>

            <div style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.7);">
                &copy; {{ date('Y') }} Biro Humas Universitas Jenderal Achmad Yani.
            </div>
        </div>

        <!-- Sisi Kanan: Form Masuk -->
        <div class="login-form-side">
            <div class="login-form-container">
                <div class="login-form-header">
                    <h2 class="login-form-title">Masuk ke akun Anda</h2>
                    <p class="login-form-subtitle">Gunakan username dan kata sandi yang terdaftar.</p>
                </div>

                @if($errors->has('username') && !old('username'))
                    <div class="alert alert-danger" style="margin-bottom: 20px;">
                        <span>{{ $errors->first('username') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" 
                            value="{{ old('username') }}" 
                            placeholder="Masukkan username"
                            required 
                            autocomplete="username"
                            autofocus
                        >
                        @error('username')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" 
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Password">
                                <span id="eye-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <label class="login-checkbox-group">
                        <input type="checkbox" name="remember" value="1">
                        <span class="login-checkbox-label">Ingat sesi saya</span>
                    </label>

                    <button type="submit" class="btn btn-primary btn-block" style="height: 44px; font-weight: 600;">
                        Masuk
                    </button>
                </form>

                <div class="login-info-box">
                    Pembuatan akun baru dikelola langsung di dalam sistem oleh Kabag atau Staff yang berwenang.
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (!passwordInput || !eyeIcon) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                `;
            }
        }
    </script>
</body>
</html>