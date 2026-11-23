<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kabag\JadwalKegiatan;

use App\Contracts\JadwalKegiatan\GoogleCalendarServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleCalendarController extends Controller
{
    /**
     * Redirect ke halaman Google OAuth untuk memilih akun dan memberi izin calendar.readonly.
     */
    public function redirect(): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['https://www.googleapis.com/auth/calendar.readonly'])
            ->with(['prompt' => 'select_account', 'access_type' => 'online'])
            ->redirect();
    }

    /**
     * Handle callback dari Google OAuth. Ambil event, simpan ke session flash, redirect kembali.
     */
    public function callback(Request $request, GoogleCalendarServiceInterface $calendarService): RedirectResponse
    {
        // Pengguna membatalkan izin
        if ($request->has('error')) {
            return redirect()
                ->route('kabag.jadwal-kegiatan.index')
                ->with('gcal_error', 'Akses ke Google Calendar dibatalkan.');
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->stateless()->user();
            $accessToken = $googleUser->token;
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback error', ['message' => $e->getMessage()]);

            return redirect()
                ->route('kabag.jadwal-kegiatan.index')
                ->with('gcal_error', 'Gagal mendapatkan otorisasi dari Google. Silakan coba lagi.');
        }

        // Token digunakan hanya pada request ini, tidak disimpan
        $result = $calendarService->fetchEvents($accessToken);

        if ($result['error'] !== null) {
            return redirect()
                ->route('kabag.jadwal-kegiatan.index')
                ->with('gcal_error', $result['error']);
        }

        if (empty($result['events'])) {
            return redirect()
                ->route('kabag.jadwal-kegiatan.index')
                ->with('gcal_error', 'Tidak ada event mendatang yang ditemukan di Google Calendar Anda (6 bulan ke depan).');
        }

        return redirect()
            ->route('kabag.jadwal-kegiatan.index')
            ->with('gcal_events', $result['events']);
    }
}
