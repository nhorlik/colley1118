<?php

namespace App\Services\Terv;

/**
 * Pontozásos kiválasztás — tiszta függvény, DB nélkül egységtesztelhető.
 *
 *   fő fókuszizom                         +3
 *   egyéb kijelölt izom                   +2   (egyszer)
 *   rehab-ajánlott a felhasználó panaszára +4
 *   héten belüli ismétlés                 −4   (előfordulásonként)
 *   nem-rehab felhasználónál terápiás     −1,5
 *   erő-blokkban nem erő jellegű          −1
 */
final class Pontozo
{
    public const FO_FOKUSZ = 3.0;
    public const EGYEB_FOKUSZ = 2.0;
    public const REHAB_AJANLOTT = 4.0;
    public const HETI_ISMETLES = -4.0;
    public const TERAPIAS_NEM_REHAB = -1.5;
    public const NEM_ERO_ERO_BLOKKBAN = -1.0;

    public function pont(Jelolt $j, PontozasiKontextus $k): float
    {
        return array_sum($this->reszletek($j, $k));
    }

    /** @return array<string, float> tényező => pont (a 0-s tényezők nélkül) — az indokláshoz */
    public function reszletek(Jelolt $j, PontozasiKontextus $k): array
    {
        $r = [];

        if ($k->foFokusz !== null && array_key_exists($k->foFokusz, $j->izmok)) {
            $r['fo_fokusz'] = self::FO_FOKUSZ;
        }

        $egyeb = array_diff($k->fokuszok, $k->foFokusz === null ? [] : [$k->foFokusz]);
        if (array_intersect($egyeb, array_keys($j->izmok)) !== []) {
            $r['egyeb_fokusz'] = self::EGYEB_FOKUSZ;
        }

        if (array_intersect($j->ajanlottPanaszok, $k->panaszok) !== []) {
            $r['rehab_ajanlott'] = self::REHAB_AJANLOTT;
        }

        $hasznalt = $k->hetiHasznalat[$j->id] ?? 0;
        if ($hasznalt > 0) {
            $r['heti_ismetles'] = self::HETI_ISMETLES * $hasznalt;
        }

        if (! $k->rehabFelhasznalo && $j->terapias) {
            $r['terapias_nem_rehab'] = self::TERAPIAS_NEM_REHAB;
        }

        if ($k->blokk === Blokk::ERO && $j->tipus !== 'ero') {
            $r['nem_ero_ero_blokkban'] = self::NEM_ERO_ERO_BLOKKBAN;
        }

        return $r;
    }
}
