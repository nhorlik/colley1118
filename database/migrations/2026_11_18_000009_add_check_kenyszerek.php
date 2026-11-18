<?php

/*
 * CHECK-kényszerek az enum-jellegű oszlopokra (csak MySQL/MariaDB; SQLite-on kimarad).
 * Az értéklisták az App\Enums osztályokból jönnek → a kód és az adatbázis nem csúszhat szét.
 * Minden hozzáadás előtt information_schema-ellenőrzés → újrafuttatható.
 */

use App\Enums\Cel;
use App\Enums\GyakorlatTipus;
use App\Enums\PanaszEredet;
use App\Enums\SessionStatusz;
use App\Enums\SzuresEredmeny;
use App\Enums\Szint;
use App\Enums\Viszony;
use App\Support\Adatbazis\Kenyszer as K;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private function kenyszerek(): array
    {
        return [
            ['felhasznalo', 'chk_felh_cel', 'cel IN ' . K::lista(Cel::ertekek())],
            ['felhasznalo', 'chk_felh_szint', 'szint IN ' . K::lista(Szint::ertekek())],
            ['felhasznalo', 'chk_felh_heti', 'heti_edzesszam BETWEEN 1 AND 6'],
            ['felhasznalo', 'chk_felh_nem', "nem IS NULL OR nem IN ('ferfi','no','egyeb')"],
            ['felhasznalo', 'chk_felh_alkat', "testalkat IS NULL OR testalkat IN ('EKTOMORF','MEZOMORF','ENDOMORF')"],
            ['gyakorlat', 'chk_gyak_tipus', 'tipus IN ' . K::lista(GyakorlatTipus::ertekek())],
            ['gyakorlat', 'chk_gyak_nehezseg', 'nehezseg BETWEEN 1 AND 5'],
            ['gyakorlat', 'chk_gyak_ism_vagy_ido', 'alap_ism_min IS NOT NULL OR alap_ido_mp IS NOT NULL'],
            ['gyakorlat_panasz', 'chk_gyp_viszony', 'viszony IN ' . K::lista(Viszony::ertekek())],
            ['panasz_tipus', 'chk_pt_eredet', 'eredet IN ' . K::lista(PanaszEredet::ertekek())],
            ['panasz_tipus', 'chk_pt_terulet', "terulet IN ('gerinc','izulet','altalanos')"],
            ['edzesnap_gyakorlat', 'chk_eng_ism_vagy_ido', 'ism_min IS NOT NULL OR ido_mp IS NOT NULL'],
            ['edzes_session', 'chk_session_statusz', 'statusz IN ' . K::lista(SessionStatusz::ertekek())],
            ['felhasznalo_szures', 'chk_szures_eredmeny', 'eredmeny IN ' . K::lista(SzuresEredmeny::ertekek())],
        ];
    }

    public function up(): void
    {
        foreach ($this->kenyszerek() as [$tabla, $nev, $kifejezes]) {
            K::checkHozzaad($tabla, $nev, $kifejezes);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->kenyszerek()) as [$tabla, $nev]) {
            K::checkTorol($tabla, $nev);
        }
    }
};
