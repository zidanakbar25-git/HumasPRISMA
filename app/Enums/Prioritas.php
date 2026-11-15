<?php

declare(strict_types=1);

namespace App\Enums;

enum Prioritas: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';

    public function label(): string
    {
        return match ($this) {
            self::Rendah => 'Rendah',
            self::Sedang => 'Sedang',
            self::Tinggi => 'Tinggi',
        };
    }
}
