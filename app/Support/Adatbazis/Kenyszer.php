<?php

namespace App\Support\Adatbazis;

use Illuminate\Support\Facades\DB;

/**
 * Idempotens séma-segédek a migrációkhoz.
 *
 * A CHECK-kényszereket csak MySQL/MariaDB alatt hozzuk létre (SQLite-on — a tesztekben —
 * az ALTER TABLE ADD CONSTRAINT nem támogatott). Minden DDL előtt az information_schema
 * alapján ellenőrzünk, így a migráció biztonságosan újrafuttatható.
 */
final class Kenyszer
{
    public static function mysqlCsalad(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    public static function checkLetezik(string $tabla, string $nev): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $tabla)
            ->where('CONSTRAINT_NAME', $nev)
            ->where('CONSTRAINT_TYPE', 'CHECK')
            ->exists();
    }

    public static function checkHozzaad(string $tabla, string $nev, string $kifejezes): void
    {
        if (! self::mysqlCsalad() || self::checkLetezik($tabla, $nev)) {
            return;
        }
        DB::statement("ALTER TABLE `{$tabla}` ADD CONSTRAINT `{$nev}` CHECK ({$kifejezes})");
    }

    public static function checkTorol(string $tabla, string $nev): void
    {
        if (! self::mysqlCsalad() || ! self::checkLetezik($tabla, $nev)) {
            return;
        }
        // MariaDB: DROP CONSTRAINT, MySQL 8: DROP CHECK — mindkettő érti a DROP CONSTRAINT-et (MySQL 8.0.19+)
        DB::statement("ALTER TABLE `{$tabla}` DROP CONSTRAINT `{$nev}`");
    }

    /** IN-lista CHECK-kifejezéshez: ('A','B','C') */
    public static function lista(array $ertekek): string
    {
        return '(' . implode(',', array_map(fn ($e) => "'" . str_replace("'", "''", $e) . "'", $ertekek)) . ')';
    }
}
