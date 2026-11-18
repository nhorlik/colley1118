<?php

namespace App\Services\Terv;

use App\Enums\Cel;
use App\Enums\Szint;

/**
 * Szett / ismétlés / idő / pihenő a gyakorlat alapértékeiből, cél és szint szerint módosítva.
 * Tiszta függvény. Garancia: a kimenetben MINDIG van ismétlés VAGY idő.
 */
final class ParameterSzamito
{
    /** Folyamatos kardió felső korlátja egy tételben (45 perc). */
    public const KARDIO_MAX_MP = 2700;

    public function szamol(Jelolt $j, Cel $cel, Szint $szint, bool $rehabTerv): Parameterek
    {
        $idoAlapu = $j->alapIdoMp !== null;

        if ($idoAlapu) {
            return $this->idoAlapu($j, $cel, $szint, $rehabTerv);
        }

        $szett = $j->alapSzett;
        $min = $j->alapIsmMin ?? 10;
        $max = $j->alapIsmMax ?? max($min, 12);
        $piheno = $j->alapPihenoMp;

        if ($rehabTerv) {
            $kimelo = $j->terapias || in_array($j->tipus, ['mobilizacio', 'terapias'], true);
            $szett = min($szett, $kimelo ? 2 : 3);
            $piheno = max(30, min($piheno, 90));

            return new Parameterek($szett, $min, $max, null, $piheno);
        }

        if ($j->tipus === 'ero') {
            [$min, $max, $piheno, $szett] = match ($cel) {
                Cel::ERO => $j->nehezseg >= 3
                    ? [4, 6, max($piheno, 150), $szett + 1]
                    : [$min, $max, $piheno, $szett],
                Cel::BULK => [$min, $max, max($piheno, 90), $szett],
                Cel::FOGYAS => [$min + 2, $max + 3, min($piheno, 60), $szett],
                Cel::ALLOKEPESSEG => [$min + 4, $max + 5, min($piheno, 45), $szett],
                Cel::REHABILITACIO => [$min, $max, $piheno, $szett],
            };

            $szett = match ($szint) {
                Szint::KEZDO => max(2, $szett - 1),
                Szint::HALADO => $szett,
                Szint::PROFI => min(5, $szett + 1),
            };
        }

        return new Parameterek(max(1, $szett), $min, max($min, $max), null, $piheno);
    }

    private function idoAlapu(Jelolt $j, Cel $cel, Szint $szint, bool $rehabTerv): Parameterek
    {
        $ido = $j->alapIdoMp;
        $szett = $j->alapSzett;

        if ($j->tipus === 'kardio') {
            $szorzo = match (true) {
                $rehabTerv => 0.75,
                in_array($cel, [Cel::FOGYAS, Cel::ALLOKEPESSEG], true) => 1.25,
                $szint === Szint::KEZDO => 0.75,
                default => 1.0,
            };
        } else {
            $szorzo = $rehabTerv ? 0.75 : match ($szint) {
                Szint::KEZDO => 0.75,
                Szint::HALADO => 1.0,
                Szint::PROFI => 1.25,
            };
            if ($rehabTerv) {
                $szett = min($szett, 2);
            }
        }

        $ido = max(15, (int) (round($ido * $szorzo / 5) * 5));
        if ($j->tipus === 'kardio') {
            $ido = min($ido, self::KARDIO_MAX_MP);
        }

        return new Parameterek(max(1, $szett), null, null, $ido, $j->alapPihenoMp);
    }
}
