<?php

namespace App\Services\Terv;

final readonly class PontozasiKontextus
{
    /**
     * @param list<int>       $fokuszok        kijelölt izomcsoportok (a fő fókusszal együtt)
     * @param list<int>       $panaszok        a felhasználó panasz_tipus_id-i
     * @param array<int, int> $hetiHasznalat   gyakorlat_id => hányszor szerepelt már a héten
     */
    public function __construct(
        public ?int $foFokusz,
        public array $fokuszok,
        public array $panaszok,
        public bool $rehabFelhasznalo,
        public Blokk $blokk,
        public array $hetiHasznalat = [],
    ) {
    }
}
