<?php

namespace App\Enums;

/** Edzettségi szint (felhasznalo.szint) */
enum Szint: string
{
    case KEZDO = 'KEZDO';
    case HALADO = 'HALADO';
    case PROFI = 'PROFI';

    public function cimke(): string
    {
        return match ($this) {
            self::KEZDO => 'Kezdő',
            self::HALADO => 'Haladó',
            self::PROFI => 'Profi',
        };
    }

    /** A szinthez megengedett legnagyobb gyakorlat-nehézség (egyezik a v_valaszthato_gyakorlat nézettel). */
    public function maxNehezseg(): int
    {
        return match ($this) {
            self::KEZDO => 2,
            self::HALADO => 4,
            self::PROFI => 5,
        };
    }

    /**
     * Egyenlő pontszám esetén ehhez a nehézséghez legközelebbi gyakorlat nyer (másodlagos rendezés,
     * nem pontozási tényező) — így haladó nem kap térdelő fekvőtámaszt, ha van fekvenyomás.
     */
    public function celNehezseg(): int
    {
        return match ($this) {
            self::KEZDO => 1,
            self::HALADO => 3,
            self::PROFI => 4,
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
