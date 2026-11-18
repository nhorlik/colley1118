<?php

namespace App\Enums;

/** Az EdzesKapu döntése */
enum KapuAllapot: string
{
    case TILTVA = 'TILTVA';
    case REHAB = 'REHAB';
    case SZABAD = 'SZABAD';

    public function cimke(): string
    {
        return match ($this) {
            self::TILTVA => 'Tiltva',
            self::REHAB => 'Csak rehabilitációs terv',
            self::SZABAD => 'Normál terv',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
