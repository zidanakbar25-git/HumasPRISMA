<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Kabag = 'kabag';
    case Staff = 'staff';
    case Intern = 'intern';

    public function label(): string
    {
        return match ($this) {
            self::Kabag => 'Kepala Bagian',
            self::Staff => 'Staff',
            self::Intern => 'Intern / Magang',
        };
    }

    public function isKabag(): bool
    {
        return $this === self::Kabag;
    }

    public function isStaff(): bool
    {
        return $this === self::Staff;
    }

    public function isIntern(): bool
    {
        return $this === self::Intern;
    }
}
