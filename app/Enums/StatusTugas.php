<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusTugas: string
{
    case BelumDikerjakan = 'belum_dikerjakan';
    case SedangDikerjakan = 'sedang_dikerjakan';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::BelumDikerjakan => 'Belum Dikerjakan',
            self::SedangDikerjakan => 'Sedang Dikerjakan',
            self::Selesai => 'Selesai',
        };
    }
}
