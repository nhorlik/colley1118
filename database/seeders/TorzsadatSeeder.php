<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederValidator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Törzsadatok (gyakorlat-adatbázis, izomcsoportok, panasztípusok, szűrőkérdőív).
 * Forrás: database/seeders/adat/*.php — a colley SQL-dumpból generálva, a javításokkal.
 *
 * Idempotens: upsert az elsődleges kulcsra, tehát újrafuttatva frissít, nem duplikál.
 * Előbb MINDEN táblát validál, csak utána ír — egy tranzakcióban.
 */
class TorzsadatSeeder extends Seeder
{
    /** tábla => [kulcsoszlopok] — FK-függőségi sorrendben */
    public const TABLAK = [
        'testterulet' => ['testterulet_id'],
        'izomcsoport' => ['izomcsoport_id'],
        'panasz_tipus' => ['panasz_tipus_id'],
        'gyakorlat' => ['gyakorlat_id'],
        'gyakorlat_izomcsoport' => ['gyakorlat_id', 'izomcsoport_id'],
        'gyakorlat_panasz' => ['gyakorlat_id', 'panasz_tipus_id'],
        'szures_kerdes' => ['szures_kerdes_id'],
        'szures_valasz' => ['szures_valasz_id'],
    ];

    public static function adat(string $tabla): array
    {
        return require __DIR__ . "/adat/{$tabla}.php";
    }

    public function run(): void
    {
        $validator = new SeederValidator();
        $adatok = [];
        foreach (array_keys(self::TABLAK) as $tabla) {
            $adatok[$tabla] = self::adat($tabla);
            $validator->ellenoriz($tabla, $adatok[$tabla]);
        }
        $validator->dobjHaHibas();

        DB::transaction(function () use ($adatok) {
            foreach (self::TABLAK as $tabla => $kulcs) {
                $frissit = array_values(array_diff(array_keys($adatok[$tabla][0]), $kulcs));
                foreach (array_chunk($adatok[$tabla], 200) as $csomag) {
                    DB::table($tabla)->upsert($csomag, $kulcs, $frissit);
                }
                $this->command?->info(sprintf('  %-24s %4d sor', $tabla, count($adatok[$tabla])));
            }
        });
    }
}
