<?php

namespace App\Enums;

/** Gyakorlat és panasz viszonya (gyakorlat_panasz.viszony) */
enum Viszony: string
{
    case TILTOTT = 'tiltott';
    case AJANLOTT = 'ajanlott';

    public function cimke(): string
    {
        return match ($this) {
            self::TILTOTT => 'Tiltott',
            self::AJANLOTT => 'Ajánlott',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
