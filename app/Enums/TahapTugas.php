<?php

declare(strict_types=1);

namespace App\Enums;

enum TahapTugas: string
{
    case BelumDikumpulkan = 'belum_dikumpulkan';
    case MenungguStaff = 'menunggu_staff';
    case MenungguKabag = 'menunggu_kabag';
    case Revisi = 'revisi';
    case Diterima = 'diterima';

    public function label(): string
    {
        return match ($this) {
            self::BelumDikumpulkan => 'Belum Dikumpulkan',
            self::MenungguStaff => 'Menunggu Review Staff',
            self::MenungguKabag => 'Menunggu Persetujuan Kabag',
            self::Revisi => 'Perlu Revisi',
            self::Diterima => 'Diterima',
        };
    }
}
