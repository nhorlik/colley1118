<?php

namespace Tests\Support;

use App\Enums\Szint;
use App\Services\Terv\Jelolt;

/**
 * DB nélküli jelöltlista a seed-adatfájlokból — a v_valaszthato_gyakorlat nézet szűrését
 * PHP-ben utánozza, így a generátor-mag egységtesztekkel, valós gyakorlat-adatokon futtatható.
 */
final class SeedJeloltek
{
    private static function adat(string $tabla): array
    {
        return require __DIR__ . "/../../database/seeders/adat/{$tabla}.php";
    }

    /** @return array{0: array<string, list<Jelolt>>, 1: list<int>} [jelöltek mintánként, tiltott id-k] */
    public static function pool(Szint $szint, bool $otthon, array $panaszok): array
    {
        $tiltott = [];
        $ajanlott = [];
        foreach (self::adat('gyakorlat_panasz') as $r) {
            if ($r['viszony'] === 'tiltott' && in_array($r['panasz_tipus_id'], $panaszok, true)) {
                $tiltott[$r['gyakorlat_id']] = true;
            }
            if ($r['viszony'] === 'ajanlott') {
                $ajanlott[$r['gyakorlat_id']][] = $r['panasz_tipus_id'];
            }
        }

        $izmok = [];
        foreach (self::adat('gyakorlat_izomcsoport') as $r) {
            $izmok[$r['gyakorlat_id']][$r['izomcsoport_id']] = (bool) $r['elsodleges'];
        }

        $pool = [];
        foreach (self::adat('gyakorlat') as $g) {
            if (! $g['aktiv'] || $g['nehezseg'] > $szint->maxNehezseg()
                || ($otthon && ! $g['otthon_vegezheto']) || isset($tiltott[$g['gyakorlat_id']])) {
                continue;
            }
            $pool[$g['mozgasminta']][] = new Jelolt(
                $g['gyakorlat_id'], $g['kod'], $g['nev'], $g['tipus'], $g['mozgasminta'], $g['nehezseg'],
                (bool) $g['terapias'], (bool) $g['otthon_vegezheto'], $izmok[$g['gyakorlat_id']] ?? [],
                $ajanlott[$g['gyakorlat_id']] ?? [], $g['alap_szett'], $g['alap_ism_min'], $g['alap_ism_max'],
                $g['alap_ido_mp'], $g['alap_piheno_mp'],
            );
        }

        return [$pool, array_keys($tiltott)];
    }

    public static function jelolt(array $felulir = []): Jelolt
    {
        $a = $felulir + [
            'id' => 1, 'kod' => 'TESZT', 'nev' => 'Teszt', 'tipus' => 'ero', 'mozgasminta' => 'NYOMAS_VIZ',
            'nehezseg' => 2, 'terapias' => false, 'otthonVegezheto' => true, 'izmok' => [1 => true],
            'ajanlottPanaszok' => [], 'alapSzett' => 3, 'alapIsmMin' => 8, 'alapIsmMax' => 12,
            'alapIdoMp' => null, 'alapPihenoMp' => 90,
        ];

        return new Jelolt(...$a);
    }
}
