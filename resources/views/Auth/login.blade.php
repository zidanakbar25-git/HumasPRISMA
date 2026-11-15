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
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: linear-gradient(135deg, var(--green-900) 0%, var(--green-800) 100%);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 36px 32px;
            border: 1px solid var(--border);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo {
            width: 52px;
            height: 52px;
            background-color: var(--green-50);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--green-700);
            margin-bottom: 16px;
            border: 1px solid var(--green-200);
        }

        .login-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .login-subtitle {
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
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <x-icon name="briefcase" :size="28" />
                </div>
                <h1 class="login-title">Biro Humas</h1>
                <p class="login-subtitle">Masuk menggunakan username dan password Anda</p>
            </div>

            @if($errors->has('username') && !old('username'))
                <div class="alert alert-danger">
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
                    Masuk ke Sistem
                </button>
            </form>
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
