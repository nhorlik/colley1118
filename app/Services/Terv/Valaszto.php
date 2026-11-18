<?php

namespace App\Services\Terv;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * A generátor algoritmikus magja (DB nélkül): napsablonok + jelöltlista → tervvázlat.
 *
 * Minden slotnál: a slot mozgásmintáiból (prioritási sorrendben) az első nem üres jelölthalmaz,
 * amelyből kiesik a napon már szereplő gyakorlat (és rehab tervben a 2-esnél nehezebb).
 * A jelölteket (mag szerint reprodukálhatóan) megkeverjük, majd stabilan rendezzük: elsődlegesen
 * pont szerint, másodlagosan a szinthez illő nehézség szerint → egyenlő pontnál szinthez illő,
 * de változatos, teszteknél determinisztikus a választás.
 */
final class Valaszto
{
    public const REHAB_MAX_NEHEZSEG = 2;

    public function __construct(
        private readonly Pontozo $pontozo = new Pontozo(),
        private readonly ParameterSzamito $parameterek = new ParameterSzamito(),
    ) {
    }

    public function osszeallit(ValasztoBemenet $b): TervVazlat
    {
        $randomizer = $b->mag === null ? new Randomizer() : new Randomizer(new Xoshiro256StarStar($b->mag));
        $celNehezseg = $b->rehabTerv ? 1 : $b->szint->celNehezseg();
        $hetiHasznalat = [];
        $napok = [];

        foreach ($b->napok as $i => $sablon) {
            $napHasznalt = [];
            $tetelek = [];

            foreach ($sablon->slotok as $slot) {
                $jeloltek = $this->slotJeloltjei($slot, $b, $napHasznalt);
                if ($jeloltek === []) {
                    continue;
                }

                $kontextus = new PontozasiKontextus(
                    $b->foFokusz, $b->fokuszok, $b->panaszok, $b->rehabFelhasznalo, $slot->blokk, $hetiHasznalat,
                );

                $jeloltek = $randomizer->shuffleArray($jeloltek);
                $pontok = [];
                foreach ($jeloltek as $j) {
                    $pontok[$j->id] = $this->pontozo->pont($j, $kontextus);
                }
                usort($jeloltek, fn (Jelolt $a, Jelolt $c) => [$pontok[$c->id], abs($a->nehezseg - $celNehezseg)]
                    <=> [$pontok[$a->id], abs($c->nehezseg - $celNehezseg)]);

                $valasztott = $jeloltek[0];
                $napHasznalt[$valasztott->id] = true;
                $hetiHasznalat[$valasztott->id] = ($hetiHasznalat[$valasztott->id] ?? 0) + 1;

                $tetelek[] = new TervTetel(
                    count($tetelek) + 1,
                    $valasztott,
                    $this->parameterek->szamol($valasztott, $b->cel, $b->szint, $b->rehabTerv),
                    $pontok[$valasztott->id],
                    $this->pontozo->reszletek($valasztott, $kontextus),
                );
            }

            $napok[] = new TervNap($i + 1, $sablon->nev, $tetelek);
        }

        return new TervVazlat($napok, $b->rehabTerv);
    }

    /** @return list<Jelolt> */
    private function slotJeloltjei(Slot $slot, ValasztoBemenet $b, array $napHasznalt): array
    {
        foreach ($slot->mintak as $minta) {
            $lista = array_values(array_filter(
                $b->jeloltekMintankent[$minta] ?? [],
                fn (Jelolt $j) => ! isset($napHasznalt[$j->id])
                    && (! $b->rehabTerv || $j->nehezseg <= self::REHAB_MAX_NEHEZSEG),
            ));
            if ($lista !== []) {
                return $lista;
            }
        }

        return [];
    }
}
