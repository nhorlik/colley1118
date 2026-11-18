<?php

namespace App\Enums;

/** Régiós szűrés kimenete (felhasznalo_szures.eredmeny) */
enum SzuresEredmeny: string
{
    case RENDBEN = 'rendben';
    case TERHELESI_JELZO = 'terhelesi_jelzo';
    case ORVOSHOZ = 'orvoshoz';

    public function cimke(): string
    {
        return match ($this) {
            self::RENDBEN => 'Rendben',
            self::TERHELESI_JELZO => 'Terhelési korlátozás',
            self::ORVOSHOZ => 'Orvosi tisztázás szükséges',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
