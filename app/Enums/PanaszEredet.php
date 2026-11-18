<?php

namespace App\Enums;

/** Panasz forrása (panasz_tipus.eredet) */
enum PanaszEredet: string
{
    case DIAGNOZIS = 'diagnozis';
    case TERHELESI_JELZO = 'terhelesi_jelzo';
    case ORVOSI_ENGEDELY = 'orvosi_engedely';

    public function cimke(): string
    {
        return match ($this) {
            self::DIAGNOZIS => 'Diagnózis',
            self::TERHELESI_JELZO => 'Terhelési jelző',
            self::ORVOSI_ENGEDELY => 'Orvosi engedélyhez kötött',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
