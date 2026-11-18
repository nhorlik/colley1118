<?php

namespace App\Services\Terv;

/**
 * Független utóellenőrzés a nézet-szűrésen felül (védelem a jövőbeli regresszió ellen):
 *  - tiltott gyakorlat sosem kerül be
 *  - otthon edző nem kap edzőtermi gyakorlatot
 *  - szinthez (rehabnál 2-höz) képest túl nehéz gyakorlat kiszűrve
 *  - minden tervsorban van ismétlés vagy idő
 *  - nincs üres nap, és egy napon belül nincs ismétlődő gyakorlat
 */
final class TervValidator
{
    /** @return list<string> emberileg olvasható hibák (üres = rendben) */
    public function hibak(TervVazlat $v, ValidaciosKorlatok $k): array
    {
        $hibak = [];
        $tiltott = array_flip($k->tiltottGyakorlatok);

        if ($v->napok === []) {
            $hibak[] = 'A tervben nincs egyetlen edzésnap sem.';
        }

        foreach ($v->napok as $nap) {
            $hely = "{$nap->sorszam}. nap ({$nap->nev})";

            if ($nap->tetelek === []) {
                $hibak[] = "{$hely}: nincs választható gyakorlat.";
            }

            $napon = [];
            foreach ($nap->tetelek as $t) {
                $j = $t->jelolt;
                $p = $t->parameterek;

                if (isset($tiltott[$j->id])) {
                    $hibak[] = "{$hely}: tiltott gyakorlat került a tervbe: {$j->nev}.";
                }
                if ($k->otthonEdz && ! $j->otthonVegezheto) {
                    $hibak[] = "{$hely}: otthon nem végezhető gyakorlat: {$j->nev}.";
                }
                if ($j->nehezseg > $k->maxNehezseg) {
                    $hibak[] = "{$hely}: túl nehéz gyakorlat ({$j->nehezseg} > {$k->maxNehezseg}): {$j->nev}.";
                }
                if ($p->ismMin === null && $p->idoMp === null) {
                    $hibak[] = "{$hely}: a(z) {$j->nev} tételnél nincs sem ismétlés, sem idő.";
                }
                if (isset($napon[$j->id])) {
                    $hibak[] = "{$hely}: a(z) {$j->nev} kétszer szerepel ugyanazon a napon.";
                }
                $napon[$j->id] = true;
            }
        }

        return $hibak;
    }
}
