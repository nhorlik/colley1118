<?php

namespace App\Enums;

/** Edzés-alkalom állapota (edzes_session.statusz) */
enum SessionStatusz: string
{
    case FOLYAMATBAN = 'folyamatban';
    case BEFEJEZETT = 'befejezett';
    case MEGSZAKITOTT = 'megszakitott';

    public function cimke(): string
    {
        return match ($this) {
            self::FOLYAMATBAN => 'Folyamatban',
            self::BEFEJEZETT => 'Befejezett',
            self::MEGSZAKITOTT => 'Megszakított',
        };
    }

    /** @return list<string> */
    public static function ertekek(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
