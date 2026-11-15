<?php

declare(strict_types=1);

namespace App\Enums;

enum SumberKegiatan: string
{
    case Manual = 'manual';
    case GoogleCalendar = 'google_calendar';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::GoogleCalendar => 'Google Calendar',
        };
    }
}
