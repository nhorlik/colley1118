<?php

namespace Database\Seeders;

use App\Models\Felhasznalo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Bemutató/teszt fiókok (jelszó mindegyiknél: colley123). Csak fejlesztői környezetbe!
 * Valós személyek adatai szándékosan NEM kerülnek át a régi dumpból.
 */
class TesztFelhasznaloSeeder extends Seeder
{
    public const JELSZO = 'colley123';

    public function run(): void
    {
        $most = now();

        $fiokok = [
            [
                'adat' => ['email' => 'teszt.bulk@colley.hu', 'nev' => 'Kovács Bence', 'cel' => 'BULK', 'szint' => 'HALADO',
                    'testalkat' => 'EKTOMORF', 'nem' => 'ferfi', 'szuletesi_ev' => 1998, 'magassag_cm' => 182,
                    'testsuly_kg' => 74.5, 'heti_edzesszam' => 4, 'otthon_edz' => false],
                'panaszok' => [],
            ],
            [
                'adat' => ['email' => 'teszt.rehab@colley.hu', 'nev' => 'Tóth Márta', 'cel' => 'REHABILITACIO', 'szint' => 'KEZDO',
                    'testalkat' => 'MEZOMORF', 'nem' => 'no', 'szuletesi_ev' => 1975, 'magassag_cm' => 164,
                    'testsuly_kg' => 69.0, 'heti_edzesszam' => 3, 'otthon_edz' => true],
                'panaszok' => [2, 4],          // porckorongsérv, isiász
            ],
            [
                'adat' => ['email' => 'teszt.otthon@colley.hu', 'nev' => 'Szabó Anna', 'cel' => 'FOGYAS', 'szint' => 'KEZDO',
                    'testalkat' => 'ENDOMORF', 'nem' => 'no', 'szuletesi_ev' => 1990, 'magassag_cm' => 168,
                    'testsuly_kg' => 78.0, 'heti_edzesszam' => 3, 'otthon_edz' => true],
                'panaszok' => [],
            ],
        ];

        foreach ($fiokok as ['adat' => $adat, 'panaszok' => $panaszok]) {
            $f = Felhasznalo::updateOrCreate(
                ['email' => $adat['email']],
                $adat + [
                    'jelszo_hash' => Hash::make(self::JELSZO),
                    'gdpr_hozzajarulas' => $most,
                    'egeszseg_hozzajarulas' => $panaszok ? $most : null,
                ],
            );
            $f->panaszok()->sync($panaszok);
            $f->rehabModFrissit();
        }
    }
}
