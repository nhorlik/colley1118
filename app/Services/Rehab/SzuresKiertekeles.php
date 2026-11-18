<?php

namespace App\Services\Rehab;

use App\Enums\SzuresEredmeny;

final readonly class SzuresKiertekeles
{
    /** @param list<int> $panaszok a válaszok által beállított terhelési jelzők (panasz_tipus_id) */
    public function __construct(
        public SzuresEredmeny $eredmeny,
        public array $panaszok,
        public string $osszegzes,
    ) {
    }
}
