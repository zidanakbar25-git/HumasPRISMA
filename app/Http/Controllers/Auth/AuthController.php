<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('Auth.login');
    }

    /**
     * Proses autentikasi user dengan throttle rate limiting.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('username'))->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Kepala Bagian.',
                ]);
            }

            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, 'google-calendar/callback')) {
                session()->forget('url.intended');
            }

            return $this->redirectBasedOnRole($user->role);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => 'Username atau password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Proses logout user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Buat throttle key berdasarkan username dan IP.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip());
    }

    /**
     * Redirect berdasarkan role user.
     */
    private function redirectBasedOnRole(Role|string $role): RedirectResponse
    {
        $roleValue = $role instanceof Role ? $role : Role::tryFrom($role);

        if ($roleValue === Role::Kabag) {
            return redirect()->intended(route('kabag.jadwal-kegiatan.index'));
        }

        return redirect()->intended(route('staff.jadwal-kegiatan.index'));
    }
}
