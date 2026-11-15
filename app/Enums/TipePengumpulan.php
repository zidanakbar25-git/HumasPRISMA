<?php

declare(strict_types=1);

namespace App\Enums;

enum TipePengumpulan: string
{
    case File = 'file';
    case Link = 'link';
    case Teks = 'teks';

    public function label(): string
    {
        return match ($this) {
            self::File => 'File Gambar',
            self::Link => 'Tautan / Link',
            self::Teks => 'Teks / Narasi',
        };
    }
}
