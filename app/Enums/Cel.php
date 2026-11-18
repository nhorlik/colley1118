<?php

namespace App\Enums;

/** Edzési cél (felhasznalo.cel) */
enum Cel: string
{
    case BULK = 'BULK';
    case ERO = 'ERO';
    case FOGYAS = 'FOGYAS';
    case ALLOKEPESSEG = 'ALLOKEPESSEG';
    case REHABILITACIO = 'REHABILITACIO';

    public function cimke(): string
    {
        return match ($this) {
            self::BULK => 'Tömegnövelés',
            self::ERO => 'Erőfejlesztés',
            self::FOGYAS => 'Zsírégetés',
            self::ALLOKEPESSEG => 'Állóképesség',
            self::REHABILITACIO => 'Rehabilitáció',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
