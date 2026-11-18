<?php

/*
 * Nézetek. Szabványos SQL (MySQL/MariaDB és SQLite alatt is fut), DROP + CREATE → újrafuttatható.
 *
 * v_gyakorlat: a régi változat LEFT JOIN-nal kötötte az elsődleges izmot, ezért a több elsődleges
 *   izmú gyakorlatok (pl. tolódzkodás, felhúzás) duplán/triplán jelentek meg. Most korrelált
 *   al-lekérdezés adja a sorrend szerinti első elsődleges izmot → gyakorlatonként pontosan 1 sor.
 *
 * v_valaszthato_gyakorlat: a generátor jelöltlistája — szint szerinti nehézség-plafon, otthoni
 *   eszközszűrés és ellenjavallat (tiltott viszony) egyetlen nézetben.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_gyakorlat');
        DB::statement(<<<'SQL'
            CREATE VIEW v_gyakorlat AS
            SELECT g.gyakorlat_id, g.kod, g.nev, g.tipus, g.mozgasminta, g.eszkoz,
                   g.otthon_vegezheto, g.nehezseg, g.terapias,
                   (SELECT ic.nev
                      FROM gyakorlat_izomcsoport gi
                      JOIN izomcsoport ic ON ic.izomcsoport_id = gi.izomcsoport_id
                     WHERE gi.gyakorlat_id = g.gyakorlat_id AND gi.elsodleges = 1
                     ORDER BY ic.sorrend
                     LIMIT 1) AS fo_izomcsoport,
                   g.alap_szett, g.alap_ism_min, g.alap_ism_max, g.alap_ido_mp, g.alap_piheno_mp
              FROM gyakorlat g
             WHERE g.aktiv = 1
            SQL);

        DB::statement('DROP VIEW IF EXISTS v_valaszthato_gyakorlat');
        DB::statement(<<<'SQL'
            CREATE VIEW v_valaszthato_gyakorlat AS
            SELECT f.felhasznalo_id, g.gyakorlat_id, g.kod, g.nev, g.tipus, g.mozgasminta,
                   g.eszkoz, g.otthon_vegezheto, g.nehezseg, g.terapias
              FROM felhasznalo f
              JOIN gyakorlat g
                ON g.aktiv = 1
               AND g.nehezseg <= CASE f.szint WHEN 'KEZDO' THEN 2 WHEN 'HALADO' THEN 4 ELSE 5 END
             WHERE (f.otthon_edz = 0 OR g.otthon_vegezheto = 1)
               AND NOT EXISTS (
                     SELECT 1
                       FROM felhasznalo_panasz fp
                       JOIN gyakorlat_panasz gp
                         ON gp.panasz_tipus_id = fp.panasz_tipus_id
                        AND gp.gyakorlat_id = g.gyakorlat_id
                      WHERE fp.felhasznalo_id = f.felhasznalo_id
                        AND gp.viszony = 'tiltott')
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_valaszthato_gyakorlat');
        DB::statement('DROP VIEW IF EXISTS v_gyakorlat');
    }
};
