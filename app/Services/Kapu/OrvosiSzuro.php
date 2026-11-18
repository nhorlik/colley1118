<?php

namespace App\Services\Kapu;

use App\Enums\KapuAllapot;
use App\Enums\SzuresEredmeny;
use DateTimeImmutable;

/**
 * PAR-Q+ ihlette döntési logika — tiszta függvény, DB nélkül tesztelhető.
 *
 * TILTVA, ha
 *   (a) van orvosi engedélyhez kötött állapot (pl. szívritmuszavar, terheléses mellkasi fájdalom)
 *       és nincs rögzített orvosi engedély, VAGY
 *   (b) valamelyik régiós szűrés piros zászlós (eredmény: orvoshoz), és nincs az adott szűrés
 *       kitöltése UTÁN rögzített orvosi engedély.
 * REHAB, ha nem tiltott és a rehab mód aktív (rehabilitációs cél vagy rögzített panasz).
 * SZABAD egyébként.
 *
 * Nem diagnosztikai eszköz: a cél a kockázatos esetek kiszűrése és a továbbirányítás.
 */
final class OrvosiSzuro
{
    /**
     * @param list<string>        $orvosiAllapotok  az 'orvosi_engedely' eredetű panaszok nevei
     * @param list<SzuresAllapot> $szuresek
     */
    public function dont(
        array $orvosiAllapotok,
        array $szuresek,
        ?DateTimeImmutable $orvosiEngedely,
        bool $rehabMod,
    ): KapuDontes {
        $indokok = [];
        $teendok = [];

        if ($orvosiAllapotok !== [] && $orvosiEngedely === null) {
            $indokok[] = sprintf(
                'A megadott állapot(ok) (%s) miatt edzés előtt orvosi tisztázás szükséges.',
                implode(', ', $orvosiAllapotok),
            );
            $teendok[] = 'Kérj orvosi véleményt, majd rögzítsd a profilodban az orvosi engedélyt.';
        }

        foreach ($szuresek as $s) {
            if ($s->eredmeny !== SzuresEredmeny::ORVOSHOZ) {
                continue;
            }
            if ($orvosiEngedely !== null && $orvosiEngedely >= $s->kitoltve) {
                continue;
            }
            $indokok[] = sprintf(
                'A(z) %s szűrésében olyan tünetet jeleztél, amit szakembernek kell megvizsgálnia.',
                mb_strtolower($s->teruletNev),
            );
            $teendok[] = sprintf(
                'A(z) %s panaszt nézesd meg szakemberrel; orvosi engedély után a terv elkészülhet.',
                mb_strtolower($s->teruletNev),
            );
        }

        if ($indokok !== []) {
            return new KapuDontes(KapuAllapot::TILTVA, $indokok, array_values(array_unique($teendok)));
        }

        if ($rehabMod) {
            return new KapuDontes(KapuAllapot::REHAB, [
                'rehabilitációs cél vagy rögzített panasz miatt csak kímélő, rehabilitációs terv engedélyezett.',
            ]);
        }

        return new KapuDontes(KapuAllapot::SZABAD);
    }
}
