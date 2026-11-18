<?php

namespace App\Enums;

/** Gyakorlat jellege (gyakorlat.tipus) */
enum GyakorlatTipus: string
{
    case ERO = 'ero';
    case IDO_ALAPU = 'ido_alapu';
    case KARDIO = 'kardio';
    case MOBILIZACIO = 'mobilizacio';
    case TERAPIAS = 'terapias';

    public function cimke(): string
    {
        return match ($this) {
            self::ERO => 'Erő',
            self::IDO_ALAPU => 'Időalapú',
            self::KARDIO => 'Kardió',
            self::MOBILIZACIO => 'Mobilizáció',
            self::TERAPIAS => 'Terápiás',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
