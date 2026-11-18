<?php

namespace App\Services\Terv;

/**
 * Egy gyakorlat-hely a napsablonban. A mozgásminták prioritási sorrendben állnak:
 * ha az elsőre nincs választható jelölt (pl. otthon edzőnél nincs függőleges húzás),
 * a következővel próbálkozunk.
 */
final readonly class Slot
{
    /** @param list<string> $mintak */
    public function __construct(
        public array $mintak,
        public Blokk $blokk = Blokk::ERO,
    ) {
    }
}
