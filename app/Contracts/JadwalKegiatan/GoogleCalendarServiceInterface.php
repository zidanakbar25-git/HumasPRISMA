<?php

declare(strict_types=1);

namespace App\Contracts\JadwalKegiatan;

interface GoogleCalendarServiceInterface
{
    /**
     * Ambil daftar event dari Google Calendar API menggunakan access token.
     *
     * @return array{events: list<array{id: string, judul: string, tanggal: string, waktu_mulai: ?string, waktu_selesai: ?string, tempat: string, deskripsi: string}>, error: ?string}
     */
    public function fetchEvents(string $accessToken): array;
}
