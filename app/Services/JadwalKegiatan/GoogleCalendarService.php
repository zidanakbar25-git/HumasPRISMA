<?php

declare(strict_types=1);

namespace App\Services\JadwalKegiatan;

use App\Contracts\JadwalKegiatan\GoogleCalendarServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService implements GoogleCalendarServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function fetchEvents(string $accessToken): array
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $timeMin = $nowWib->startOfDay()->toRfc3339String();
        $timeMax = $nowWib->copy()->addMonths(6)->endOfDay()->toRfc3339String();

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get('https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                    'timeMin' => $timeMin,
                    'timeMax' => $timeMax,
                    'singleEvents' => 'true',
                    'orderBy' => 'startTime',
                    'maxResults' => 50,
                ]);

            if ($response->failed()) {
                Log::error('Google Calendar API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['events' => [], 'error' => 'Gagal mengambil data dari Google Calendar. Silakan coba lagi.'];
            }

            $items = $response->json('items', []);
            $events = [];

            foreach ($items as $item) {
                // Lewati event yang dibatalkan
                if (($item['status'] ?? '') === 'cancelled') {
                    continue;
                }

                $start = $item['start'] ?? [];
                $end = $item['end'] ?? [];
                $isAllDay = isset($start['date']) && !isset($start['dateTime']);

                if ($isAllDay) {
                    // Event seharian atau multi-hari: hanya ambil tanggal mulai, tanpa jam
                    $tanggal = $start['date'];
                    $waktuMulai = null;
                    $waktuSelesai = null;
                } else {
                    // Event dengan waktu spesifik
                    $startDt = Carbon::parse($start['dateTime'])->setTimezone('Asia/Jakarta');
                    $endDt = Carbon::parse($end['dateTime'])->setTimezone('Asia/Jakarta');
                    $tanggal = $startDt->format('Y-m-d');
                    $waktuMulai = $startDt->format('H:i');
                    // Jika multi-hari, patuhi aturan: hanya tanggal mulai (waktu selesai dikosongkan jika beda hari)
                    $waktuSelesai = ($startDt->format('Y-m-d') === $endDt->format('Y-m-d'))
                        ? $endDt->format('H:i')
                        : null;
                }

                $events[] = [
                    'id' => $item['id'] ?? '',
                    'judul' => $item['summary'] ?? '(Tanpa Judul)',
                    'tanggal' => $tanggal,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'tempat' => $item['location'] ?? '',
                    'deskripsi' => $item['description'] ?? '',
                ];
            }

            return ['events' => $events, 'error' => null];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Google Calendar API connection error', ['message' => $e->getMessage()]);

            return ['events' => [], 'error' => 'Tidak dapat terhubung ke Google Calendar. Periksa koneksi internet Anda.'];
        } catch (\Throwable $e) {
            Log::error('Google Calendar API unexpected error', ['message' => $e->getMessage()]);

            return ['events' => [], 'error' => 'Terjadi kesalahan saat mengambil data dari Google Calendar.'];
        }
    }
}
